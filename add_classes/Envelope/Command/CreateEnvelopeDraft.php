<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeSigningLocations;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Event\CreateEnvelopeRecipients;
use App\Envelope\Event\RemoveEnvelope;
use App\Envelope\Event\StoreTemporaryFiles;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\ExpirationAwareTrait;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CreateEnvelopeDraftMessage;
use App\Envelope\Message\NewRecipientsMessage;
use App\Envelope\Message\RemoveEnvelopeMessage;
use App\Envelope\Message\StoreTemporaryFilesMessage;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

class CreateEnvelopeDraft implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use ExpirationAwareTrait;

    /**
     * The envelopes repository.
     */
    protected Model $envelopesRepository;

    /**
     * The envelope recipients repository.
     */
    protected Model $recipientsRepository;

    /**
     * The envelope documents repository.
     */
    protected Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    protected FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, FileStorageInterface $fileStorage, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier;
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->documentsRepository = $envelopesRepository->getRelation('documents')->getRelated();
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CreateEnvelopeDraftMessage $message)
    {
        // Create the draft
        $envelopeId = $this->createDraft(
            $message->getSenderId(),
            null,
            $message->getEnvelopeType(),
            $message->getType(),
            $message->getTitle(),
            $message->getDescription(),
            $this->getMaxExpiringDate($message->getRecipients()),
            $message->getSigningMechanism() ?? SigningMecahisms::NATIVE,
            (new ArrayCollection($message->getRecipients()))->exists(fn (int $i, array $recipient) => ($recipient['type'] ?? null) === RecipientTypes::SIGNER),
            SigningMecahisms::NATIVE !== $message->getSigningMechanism()
        );
        // Finalize the draft
        $this->finalizeDraft(
            $envelopeId,
            $message->getSenderId(),
            $message->getRecipients(),
            \array_map(fn (string $id) => \base64_decode($id), $message->getTemporaryFiles()),
        );

        // Send notification
        $this->sendNotifications($envelopeId, $message->getTitle(), $message);
        // Write history
        $this->addHistoryEvent(new HistoryEvent(HistoryEvent::CREATE), $envelopeId, $message->getSenderId());

        return $envelopeId;
    }

    /**
     * Creates the envelope draft.
     *
     * @throws WriteEnvelopeException if faile to create the draft
     */
    protected function createDraft(
        ?int $senderId,
        ?int $parentEnvelopeId,
        string $type,
        string $displayType,
        string $displayTitle,
        string $displayDescription,
        ?DateTimeImmutable $expirationDate,
        string $signingMechanism,
        bool $isSignable = false,
        bool $isDigital = false
    ): int {
        //region Collect Envelope
        $envelope = [
            'id_original_envelope' => $parentEnvelopeId,
            'id_sender'            => $senderId,
            'uuid'                 => Uuid::uuid6(),
            'type'                 => $type,
            'display_type'         => $displayType,
            'display_title'        => $displayTitle,
            'display_description'  => $displayDescription,
            'expires_at_date'      => $expirationDate,
            'status'               => EnvelopeStatuses::CREATED,
            'signing_enabled'      => $isSignable,
            'signing_location'     => $isDigital ? EnvelopeSigningLocations::ONLINE : EnvelopeSigningLocations::IN_PERSON,
            'signing_mechanism'    => $signingMechanism,
        ];
        //endregion Collect Envelope

        //region Write to Storage
        try {
            $envelopeId = (int) $this->envelopesRepository->insertOne($envelope);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$envelopeId) {
            throw new WriteEnvelopeException('Failed to write the envelope into database', 0, $e ?? null);
        }
        //endregion Write to Storage

        return $envelopeId;
    }

    /**
     * Finalizes the envelope draft.
     */
    protected function finalizeDraft(
        int $envelopeId,
        ?int $senderId,
        array $recipients,
        array $files = []
    ): void {
        try {
            // Create evnelope recipients
            (new CreateEnvelopeRecipients($this->recipientsRepository))->__invoke(
                new NewRecipientsMessage(
                    $envelopeId,
                    $recipients
                )
            );

            // Create envelope documents
            $this->storeFiles(
                $envelopeId,
                $senderId,
                $files,
                \array_unique(\array_map(fn ($id) => (int) $id, \array_column($recipients, 'assignee'))),
                \array_column($this->recipientsRepository->findAllBy(['conditions' => ['envelope' => $envelopeId]]) ?? [], 'id')
            );
        } catch (Throwable $e) {
            // Remove envelope
            (new RemoveEnvelope($this->envelopesRepository, $this->fileStorage))->__invoke(new RemoveEnvelopeMessage($envelopeId));

            // ...and roll exception forward
            throw new WriteEnvelopeException('Failed to finalize the envelope draft', 0, $e);
        }
    }

    /**
     * Stores the files.
     */
    protected function storeFiles(int $envelopeId, int $senderId, array $files, array $assignees, array $recipients): void
    {
        if (empty($files)) {
            return;
        }

        (new StoreTemporaryFiles($this->documentsRepository, $this->fileStorage))->__invoke(
            new StoreTemporaryFilesMessage(
                $envelopeId,
                $senderId,
                $files,
                $assignees,
                $recipients,
                'original',
                true
            )
        );
    }

    /**
     * Sends the notifications for this command.
     *
     * @param CreateEnvelopeDraftMessage $message
     */
    protected function sendNotifications(int $envelopeId, ?string $envelopeTitle, $message): void
    {
        if (!$message instanceof CreateEnvelopeDraftMessage) {
            throw new InvalidArgumentException(\sprintf('The message must be instance of %s', CreateEnvelopeDraftMessage::class));
        }
        if (empty($accessRules = $message->getAccessRulesList())) {
            return;
        }

        $this->notifier->send(
            new OrderDocumentEnvelopeNotification(
                Type::CREATED_ENVELOPE_FOR_MANAGER,
                null,
                $envelopeId,
                $envelopeTitle,
                $this->getNotificationSender($envelopeId, $message->getSenderId()),
                [(string) SystemChannel::STORAGE()]
            ),
            new RightfulRecipient($accessRules)
        );
    }

    /**
     * Get the sender for notification.
     */
    protected function getNotificationSender(int $envelopeId, ?int $senderId): ?Sender
    {
        $sender = null;
        if (null !== $senderId) {
            $senderRelation = $this->envelopesRepository->getRelation('extendedSender');
            $senderRelation->addEagerConstraints($envelopesmatchList = [['id' => $envelopeId, 'id_sender' => $senderId]]);
            $senderRelation->match($envelopesmatchList, $senderRelation->getEager(), 'sender');
            $sender = \with($envelopesmatchList[0]['sender'] ?? null, fn ($sender) => null === $sender ? null : new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            ));
        }

        return $sender;
    }
}

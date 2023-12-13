<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeSigningLocations;
use App\Envelope\Event\BindDocumentsAndRecipients;
use App\Envelope\Event\CreateEnvelopeRecipients;
use App\Envelope\Event\RemoveEnvelopeDocuments;
use App\Envelope\Event\RemoveEnvelopeRecipients;
use App\Envelope\Event\StoreTemporaryFiles;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\ExpirationAwareTrait;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\BindDocumentsAndRecipientsMessage;
use App\Envelope\Message\NewRecipientsMessage;
use App\Envelope\Message\RemoveEnvelopeComponentsMessage;
use App\Envelope\Message\StoreTemporaryFilesMessage;
use App\Envelope\Message\UpdateEnvelopeDraftMessage;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

class UpdateEnvelopeDraft implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;
    use EnvelopeAccessTrait;
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
        $this->notifier = $notifier ?? new Notifier([]);
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
    public function __invoke(UpdateEnvelopeDraftMessage $message)
    {
        $originalEnvelope = $this->findEnvelopeInStorage($envelopeId = $message->getEnvelopeId());

        //region Access Check
        // Check if sender has access to envelope
        $this->assertSenderIsEnvelopeOwner($message->getSenderId(), $originalEnvelope);
        $this->assertEnvelopeIsEditable($originalEnvelope);
        //endregion Access Check

        //region Update envelope
        $this->updateDraft(
            $message->getEnvelopeId(),
            $message->getType(),
            $message->getTitle(),
            $message->getDescription(),
            $this->getMaxExpiringDate($message->getRecipients()),
            $originalEnvelope['signing_mechanism'] ?? SigningMecahisms::NATIVE,
            $message->getSigningMechanism(),
            (new ArrayCollection($message->getRecipients()))->exists(fn (int $i, array $recipient) => ($recipient['type'] ?? null) === RecipientTypes::SIGNER),
            SigningMecahisms::NATIVE !== $message->getSigningMechanism()
        );
        //endregion Update envelope

        //region Finalize draft
        $this->finalizeDraft(
            $envelopeId,
            $message->getSenderId(),
            $message->getRecipients(),
            \array_map(fn (string $id) => \base64_decode($id), $message->getTemporaryFiles())
        );
        //endregion Finalize draft

        // Send notification
        $this->sendNotifications(
            $envelopeId,
            \array_merge(
                $originalEnvelope,
                ['display_title' => $message->getTitle(), 'display_type' => $message->getType(), 'display_description' => $message->getDescription()]
            ),
            $message,
            \with(
                $originalEnvelope['sender'] ?? null,
                fn ($sender) => null === $sender ? null : new Sender(
                    $sender['id'],
                    $sender['full_name'],
                    $sender['legal_name'],
                    $sender['group_type']
                )
            )
        );
        // Write history
        $this->addHistoryEvent(new HistoryEvent(HistoryEvent::UPDATE), $envelopeId, $message->getSenderId());

        return $envelopeId;
    }

    /**
     * Updates the envelope draft.
     *
     * @throws WriteEnvelopeException if faile to update the draft
     */
    protected function updateDraft(
        int $envelopeId,
        string $displayType,
        string $displayTitle,
        string $displayDescription,
        ?DateTimeImmutable $expirationDate,
        string $originalSigningMechanism,
        string $newSigningMechanism,
        bool $isSignable = false,
        bool $isDigital = false
    ): void {
        //region Collect Envelope
        $envelope = [
            'display_title'           => $displayTitle,
            'display_type'            => $displayType,
            'display_description'     => $displayDescription,
            'signing_enabled'         => $isSignable,
            'signing_location'        => $isDigital ? EnvelopeSigningLocations::ONLINE : EnvelopeSigningLocations::IN_PERSON,
            'expires_at_date'         => $expirationDate,
            'signing_mechanism'       => $newSigningMechanism,
        ];
        if ($newSigningMechanism !== $originalSigningMechanism) {
            $envelope['remote_envelope'] = null;
        }

        foreach (['display_type', 'display_title', 'display_description'] as $key) {
            if (($originalEnvelope[$key] ?? null) !== ($envelope[$key] ?? null)) {
                $envelope['dispaly_info_updated_at_date'] = new DateTimeImmutable();

                break;
            }
        }
        //endregion Collect Envelope

        //region Write to Storage
        try {
            $isSaved = (bool) $this->envelopesRepository->updateOne($envelopeId, $envelope);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isSaved) {
            throw new WriteEnvelopeException('Failed to write the envelope into database', 0, $e ?? null);
        }
    }

    /**
     * Finalizes the envelope draft.
     *
     * @throws WriteEnvelopeException if faile to finalize the draft
     */
    protected function finalizeDraft(
        int $envelopeId,
        ?int $senderId,
        array $recipients,
        array $files = []
    ): void {
        try {
            // Remove old recipients and create new
            (new RemoveEnvelopeRecipients($this->recipientsRepository))->__invoke(new RemoveEnvelopeComponentsMessage($envelopeId));
            (new CreateEnvelopeRecipients($this->recipientsRepository))->__invoke(
                new NewRecipientsMessage(
                    $envelopeId,
                    $recipients
                )
            );

            // Update documents if new ones were supplied
            if (!empty($files)) {
                // Remove old documents
                (new RemoveEnvelopeDocuments($this->documentsRepository, $this->fileStorage))->__invoke(
                    new RemoveEnvelopeComponentsMessage($envelopeId)
                );
                // Create envelope documents
                (new StoreTemporaryFiles($this->documentsRepository, $this->fileStorage))->__invoke(
                    new StoreTemporaryFilesMessage(
                        $envelopeId,
                        $senderId,
                        $files,
                        \array_unique(\array_map(fn ($id) => (int) $id, \array_column($recipients, 'assignee'))),
                        [],
                        'original',
                        true
                    )
                );
            }

            // Re-bind recipients and documents
            (new BindDocumentsAndRecipients($this->documentsRepository))->__invoke(
                new BindDocumentsAndRecipientsMessage(
                    $envelopeId,
                    \array_column($this->documentsRepository->findAllBy(['conditions' => ['envelope' => $envelopeId]]) ?? [], 'id'),
                    \array_column($this->recipientsRepository->findAllBy(['conditions' => ['envelope' => $envelopeId]]) ?? [], 'id'),
                )
            );
        } catch (Throwable $e) {
            // ...and roll exception forward
            throw new WriteEnvelopeException('Failed to finalize the envelope draft', 0, $e);
        }
    }

    /**
     * Sends the notifications for this command.
     */
    protected function sendNotifications(int $envelopeId, array $envelope, UpdateEnvelopeDraftMessage $message, ?Sender $sender): void
    {
        if (empty($accessRules = $message->getAccessRulesList())) {
            return;
        }

        $this->notifier->send(
            new OrderDocumentEnvelopeNotification(Type::UPDATED_ENVELOPE_FOR_MANAGER, null, $envelopeId, $envelope['display_title'], $sender, [
                (string) SystemChannel::STORAGE(),
            ]),
            new RightfulRecipient($accessRules)
        );
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    protected function findEnvelopeInStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, [
                'with' => ['extended_sender as sender'],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}

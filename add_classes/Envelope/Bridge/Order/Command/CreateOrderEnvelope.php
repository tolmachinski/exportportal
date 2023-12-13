<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Bridge\Order\DocumentMakerInterface;
use App\Envelope\Bridge\Order\Event\BindEnvelopeAndOrder;
use App\Envelope\Bridge\Order\Event\StoreOrderDocument;
use App\Envelope\Bridge\Order\Message\BindEnvelopeAndOrderMessage;
use App\Envelope\Bridge\Order\Message\StoreOrderDocumentMessage;
use App\Envelope\Command\CommandInterface;
use App\Envelope\EnvelopeSigningLocations;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Event\CompleteRouting;
use App\Envelope\Event\CreateEnvelopeRecipients;
use App\Envelope\Event\SendEnvelopeToRecipients;
use App\Envelope\Event\StartRoutingOrder;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CompleteRoutingMessage;
use App\Envelope\Message\NewRecipientsMessage;
use App\Envelope\Message\SendEnvelopeToRecipientsMessage;
use App\Envelope\Message\StartRoutingOrderMessage;
use App\Envelope\RecipientStatuses;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

abstract class CreateOrderEnvelope implements CommandInterface
{
    use NotifierAwareTrait;
    use HistoryAwareTrait;

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
     * The contract maker instance.
     */
    protected DocumentMakerInterface $documentMaker;

    /**
     * Create instance of the command.
     */
    public function __construct(
        Model $envelopesRepository,
        DocumentMakerInterface $documentMaker,
        FileStorageInterface $fileStorage,
        ?NotifierInterface $notifier = null
    ) {
        $this->setNotifier($notifier ?? new Notifier([]));
        $this->fileStorage = $fileStorage;
        $this->documentMaker = $documentMaker;
        $this->envelopesRepository = $envelopesRepository;
        $this->documentsRepository = $envelopesRepository->getRelation('documents')->getRelated();
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * Creates the envelope.
     */
    protected function createEnvelope(
        ?int $senderId,
        string $title,
        string $type,
        string $description,
        ?DateTimeImmutable $expirationDate,
        bool $isDigital = false,
        bool $isSignable = false
    ): int {
        //region Create envelope
        $envelope = [
            'id_sender'           => $senderId,
            'uuid'                => Uuid::uuid6(),
            'type'                => EnvelopeTypes::INTERNAL,
            'display_title'       => $title,
            'display_type'        => $type,
            'display_description' => $description,
            'expires_at_date'     => $expirationDate,
            'status'              => EnvelopeStatuses::SENT,
            'signing_enabled'     => $isSignable,
            'signing_location'    => $isDigital
                ? EnvelopeSigningLocations::ONLINE
                : EnvelopeSigningLocations::IN_PERSON,
        ];

        //region Write to Storage
        try {
            $envelopeId = (int) $this->envelopesRepository->insertOne($envelope);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$envelopeId) {
            throw new WriteEnvelopeException('Failed to write the envelope into database', 0, $e ?? null);
        }

        return $envelopeId;
    }

    /**
     * Finalizes the envelope.
     */
    protected function finalizeEnvelope(int $envelopeId, int $orderId, string $userId, array $recipientsList, string $fileName, bool $isCompleted = false): void
    {
        // Bind envelope to the order
        (new BindEnvelopeAndOrder($this->envelopesRepository->getRelation('orderReference')->getRelated()))->__invoke(
            new BindEnvelopeAndOrderMessage($envelopeId, $orderId)
        );

        //region Recipients
        // Create evnelope recipients
        $recipients = new ArrayCollection();
        if (!empty($recipientsList)) {
            (new CreateEnvelopeRecipients($this->recipientsRepository))->__invoke(
                new NewRecipientsMessage(
                    $envelopeId,
                    $recipientsList
                )
            );

            $recipients = new ArrayCollection(
                $this->recipientsRepository->findAllBy(['conditions' => ['envelope' => $envelopeId]]) ?? []
            );
        }
        //endregion Recipients

        // Create envelope documents
        (new StoreOrderDocument($this->documentsRepository, $this->fileStorage, $this->documentMaker))->__invoke(
            new StoreOrderDocumentMessage(
                $envelopeId,
                $orderId,
                $userId,
                $fileName,
                'document',
                \array_unique(\array_map(fn ($id)      => (int) $id, \array_column($recipients->toArray(), 'id_user'))),
                $recipients->map(fn (array $recipient) => $recipient['id'])->toArray(),
                'system',
                true
            )
        );

        //region Routing
        // Send envelope to the all recipients
        (new SendEnvelopeToRecipients($this->envelopesRepository, $this->getNotifier()))->__invoke(
            new SendEnvelopeToRecipientsMessage(
                $envelopeId,
                null,
                $recipients
                    ->filter(fn (array $recipient) => RecipientStatuses::COMPLETED !== $recipient['status'])
                    ->map(fn (array $recipient)    => ['id' => $recipient['id'], 'user' => $recipient['id_user']])
                    ->toArray(),
                false
            )
        );

        if ($isCompleted) {
            // Complete envelope if nothing on the next step
            (new CompleteRouting($this->envelopesRepository, $this->getNotifier()))->__invoke(
                new CompleteRoutingMessage($envelopeId, null, true)
            );
        } else {
            // Start envelope routing
            (new StartRoutingOrder($this->envelopesRepository))->__invoke(
                new StartRoutingOrderMessage(
                    $envelopeId,
                    1,
                    null,
                    \array_column([$recipients->first()], 'id')
                )
            );
        }
    }

    /**
     * Sends the notifications for this command.
     */
    protected function sendNotifications(int $orderId, int $envelopeId, ?string $envelopeTitle, Collection $recipients, array $accessRules = []): void
    {
        $this->getNotifier()->send(
            new OrderDocumentEnvelopeNotification(Type::SENT_ORDER_ENVELOPE, $orderId, $envelopeId, $envelopeTitle, null, [
                (string) SystemChannel::STORAGE(),
                (string) SystemChannel::MATRIX(),
            ]),
            ...$recipients->map(fn (int $recipient) => (new Recipient((int) $recipient))->withRoomType(RoomType::CARGO()))->toArray()
        );
        if (!empty($accessRules)) {
            $this->getNotifier()->send(
                new OrderDocumentEnvelopeNotification(Type::SENT_ORDER_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $envelopeTitle, null, [
                    (string) SystemChannel::STORAGE(),
                ]),
                new RightfulRecipient($accessRules)
            );
        }
    }

    /**
     * Add one history event to the storage.
     */
    protected function addHistoryEvent(HistoryEvent $event, int $envelopeId, ?int $userId = null, ?array $context = null): void
    {
        $this->historyRepository->insertOne([
            'id_user'     => $userId,
            'id_envelope' => $envelopeId,
            'context'     => $context,
            'event'       => $event,
        ]);
    }
}

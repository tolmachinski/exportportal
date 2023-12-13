<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentChildEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Event\CopyFiles;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\CopyEnvelopeMessage;
use App\Envelope\Message\CopyFilesMessage;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use InvalidArgumentException;

class CopyEnvelope extends CreateEnvelopeDraft
{
    use EnvelopeAccessTrait;

    /**
     * The flag that indicates if the copied files were overriden.
     */
    protected bool $overridesFiles = false;

    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CopyEnvelopeMessage $message)
    {
        $parentEnvelope = $this->findEnvelopeInStorage($message->getEnvelopeId());
        $targetFiles = $this->resolveFileReferences($parentEnvelope, $message);

        //region Access Check
        // Check if sender has access to parent envelope
        $this->assertSenderIsEnvelopeOwner($message->getSenderId(), $parentEnvelope);
        //endregion Access Check

        // Create the draft
        $envelopeId = $this->createDraft(
            $message->getSenderId(),
            $message->getEnvelopeId(),
            EnvelopeTypes::PERSONAL,
            $message->getType(),
            $message->getTitle(),
            $message->getDescription(),
            $this->getMaxExpiringDate($message->getRecipients()),
            $message->getSigningMechanism(),
            (new ArrayCollection($message->getRecipients()))->exists(fn (int $i, array $recipient) => ($recipient['type'] ?? null) === RecipientTypes::SIGNER),
            SigningMecahisms::NATIVE !== $message->getSigningMechanism()
        );

        // Finalize the draft
        $this->finalizeDraft(
            $envelopeId,
            $message->getSenderId(),
            $message->getRecipients(),
            $targetFiles
        );

        // Send notification
        $this->sendNotifications($envelopeId, $parentEnvelope['display_title'] ?? null, $message);
        // Write history
        $this->addHistoryEvent(new HistoryEvent(HistoryEvent::COPY), $envelopeId, $message->getSenderId(), [
            'originalEnvelopeId' => $message->getEnvelopeId(),
        ]);

        return $envelopeId;
    }

    /**
     * Stores the files.
     */
    protected function storeFiles(int $envelopeId, int $senderId, array $files, array $assignees, array $recipients): void
    {
        if (empty($files)) {
            return;
        }

        if ($this->overridesFiles) {
            parent::storeFiles($envelopeId, $senderId, $files, $assignees, $recipients);

            return;
        }

        (new CopyFiles($this->documentsRepository, $this->fileStorage))->__invoke(
            new CopyFilesMessage(
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
     * @param CopyEnvelopeMessage $message
     */
    protected function sendNotifications(int $envelopeId, ?string $parentEnvelopeTitle, $message): void
    {
        if (!$message instanceof CopyEnvelopeMessage) {
            throw new InvalidArgumentException(\sprintf('The message must be instance of %s', CopyEnvelopeMessage::class));
        }
        if (empty($accessRules = $message->getAccessRulesList())) {
            return;
        }

        $this->notifier->send(
            new OrderDocumentChildEnvelopeNotification(
                Type::COPY_ENVELOPE_FOR_MANAGER,
                null,
                $envelopeId,
                $message->getTitle(),
                $message->getEnvelopeId(),
                $parentEnvelopeTitle,
                $this->getNotificationSender($envelopeId, $message->getSenderId()),
                [(string) SystemChannel::STORAGE()]
            ),
            new RightfulRecipient($accessRules)
        );
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function findEnvelopeInStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, [
                'with' => ['documents'],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Resolves from message the files that will be copied, be it either files that belongs to envelope or are overriden.
     *
     * @throws AccessDeniedException when files are not overriden and doesn't belong to the envelope
     */
    private function resolveFileReferences(array $parentEnvelope, CopyEnvelopeMessage $message): array
    {
        $files = [];
        $copiedFiles = \array_map(fn ($id) => (int) $id, $message->getCopiedFiles());
        $temporaryFiles = $message->getTemporaryFiles();
        if (!empty($temporaryFiles)) {
            $files = \array_map(fn (string $id) => \base64_decode($id), $temporaryFiles);
            $this->overridesFiles = true;
        } else {
            $parentDocuments = $parentEnvelope['documents'] ?? [];
            if ($parentDocuments instanceof Collection) {
                $parentDocuments = $parentDocuments->toArray();
            }
            if (\count(\array_intersect($copiedFiles, \array_column($parentDocuments, 'id'))) !== \count($copiedFiles)) {
                throw new AccessDeniedException("The provided files IDs doesn't belongs to the envelope");
            }
            $files = \array_map(
                fn (array $document) => $document['remote_uuid'] ?? $document['uuid'],
                \array_filter(
                    $parentDocuments,
                    fn (array $document) => \in_array($document['id'], $copiedFiles)
                )
            );
        }

        return $files;
    }
}

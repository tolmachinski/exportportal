<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Command;

use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\DigitalSignature\Message\CommitEnvelopeEditModeMessage;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use DateTimeImmutable;
use Exception;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class CommitEnvelopeEditMode
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->envelopesRepository = $envelopesRepository;
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * Execute the command.
     */
    public function __invoke(CommitEnvelopeEditModeMessage $message)
    {
        //region Entities resolution
        // Get envelope by its UUID
        $envelope = $this->getEnvelopeFromStorage($message->getOriginalEnvelopeUuid());
        if (EnvelopeStatuses::NOT_PROCESSED !== $envelope['status']) {
            throw new AccessDeniedException(
                \sprintf('Only envelopes in the status "%s" are accepted', EnvelopeStatuses::NOT_PROCESSED)
            );
        }
        //endregion Entities resolution

        //region Update envelope
        try {
            // Update the status to approved
            $isSaved = (bool) $this->envelopesRepository->updateOne($envelope['id'], [
                'status'                 => EnvelopeStatuses::PROCESSED,
                'processed_at_date'      => new DateTimeImmutable(),
                'status_changed_at_date' => new DateTimeImmutable(),
            ]);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isSaved) {
            throw new WriteEnvelopeException('Failed to update the envelope.', 0, $e ?? null);
        }
        //endregion Update envelope

        // Write history
        $this->addHistoryEvent(new HistoryEvent(HistoryEvent::PROCESS), $envelope['id'], null, ['isInternal' => true]);
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function getEnvelopeFromStorage(?string $envelopeUuid): array
    {
        if (null === $envelopeUuid || null === $envelope = $this->envelopesRepository->findOneBy([
            'conditions' => [
                'uuid' => $envelopeUuid,
            ],
        ])) {
            throw new NotFoundException(\sprintf('The envelope with UUID "%s" is not found', \varToString($envelopeUuid)));
        }

        return $envelope;
    }
}

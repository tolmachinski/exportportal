<?php

declare(strict_types=1);

namespace App\Envelope;

use App\Common\Database\Model;

trait HistoryAwareTrait
{
    /**
     * The history repository.
     */
    private Model $historyRepository;

    /**
     * Get the history repository.
     */
    public function getHistoryRepository(): Model
    {
        return $this->historyRepository;
    }

    /**
     * Set the history repository.
     *
     * @return self
     */
    public function setHistoryRepository(Model $historyRepository)
    {
        $this->historyRepository = $historyRepository;

        return $this;
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

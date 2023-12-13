<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use Sample_Orders_Model;

trait SampleOrdersEntitiesAwareTrait
{
    /**
     * The sample orders repository.
     *
     * @var Sample_Orders_Model
     */
    private $sampleOrdersRepository;

    /**
     * Ensures that the sample order exists.
     *
     * @param int $sampleOrderId
     *
     * @throws NotFoundException
     */
    private function ensureSampleOrderExists(?int $sampleOrderId): void
    {
        if (null === $sampleOrderId) {
            return;
        }

        if (!$this->sampleOrdersRepository->has($sampleOrderId)) {
            throw new NotFoundException("The sample order with ID '{$sampleOrderId}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }
    }

    /**
     * Ensures that sample order belongs to the user.
     *
     * @param int $sampleOrderId
     * @param int $userId
     *
     * @throws OwnershipException
     */
    private function ensureSampleOrderOwnership(?int $sampleOrderId, ?int $userId): void
    {
        if (null === $sampleOrderId || null === $userId) {
            return;
        }

        if (!$this->sampleOrdersRepository->is_accessible_for($sampleOrderId, $userId)) {
            throw new OwnershipException("The sample with ID '{$sampleOrderId}' doesn't belong to the user '{$userId}'.", SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }
    }
}

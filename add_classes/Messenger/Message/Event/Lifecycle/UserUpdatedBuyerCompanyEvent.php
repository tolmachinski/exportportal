<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Lifecycle;

use App\Messenger\Message\Event\AbstractUserAccountEvent;

/**
 * Event triggered when user's company (buyer) was updated.
 */
final class UserUpdatedBuyerCompanyEvent extends AbstractUserAccountEvent
{
    /**
     * The company's ID value.
     */
    protected int $companyId;

    /**
     * @param int $userId    the user ID value
     * @param int $companyId the company ID value
     */
    public function __construct(int $userId, int $companyId)
    {
        parent::__construct($userId);

        $this->companyId = $companyId;
    }

    /**
     * Get the company ID value.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Set the company ID value.
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }
}

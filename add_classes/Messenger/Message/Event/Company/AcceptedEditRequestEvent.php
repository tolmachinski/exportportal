<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Company;

use App\Messenger\Message\Event\EditRequest\AbstractEditRequestEvent;

/**
 * Event triggered when user company edit request was accepted.
 *
 * @author Anton Zencenco
 */
final class AcceptedEditRequestEvent extends AbstractEditRequestEvent
{
    /**
     * The company ID value.
     */
    private int $companyId;

    /**
     * {@inheritDoc}
     *
     * @param int $requestId the request ID value
     */
    public function __construct(int $requestId, int $userId, int $companyId)
    {
        parent::__construct($requestId, $userId);

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

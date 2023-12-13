<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Profile;

use App\Messenger\Message\Event\AbstractUserAccountEvent;

/**
 * Event triggered when user profile edit request was accepted.
 *
 * @author Anton Zencenco
 */
final class AcceptedEditRequestEvent extends AbstractUserAccountEvent
{
    /**
     * The request ID value.
     */
    private int $requestId;

    /**
     * {@inheritDoc}
     *
     * @param int $requestId the request ID value
     */
    public function __construct(int $requestId, int $userId)
    {
        parent::__construct($userId);

        $this->requestId = $requestId;
    }

    /**
     * Get the request ID value.
     */
    public function getRequestId(): int
    {
        return $this->requestId;
    }

    /**
     * Set the request ID value.
     */
    public function setRequestId(int $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }
}

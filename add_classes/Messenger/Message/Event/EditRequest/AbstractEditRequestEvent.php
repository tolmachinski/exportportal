<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\EditRequest;

/**
 * Base event for any event that represents the event request changes.
 *
 * @author Anton Zencenco
 */
abstract class AbstractEditRequestEvent
{
    /**
     * The request ID value.
     */
    private int $requestId;

    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * @param int $userId    the user ID value
     * @param int $requestId the request ID value
     */
    public function __construct(int $requestId, int $userId)
    {
        $this->userId = $userId;
        $this->requestId = $requestId;
    }

    /**
     * Get user ID value.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set user ID value.
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
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

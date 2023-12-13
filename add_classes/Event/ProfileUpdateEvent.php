<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Anton Zencenco
 */
class ProfileUpdateEvent extends Event
{
    /**
     * The user ID.
     */
    protected int $userId;

    /**
     * The completed option.
     */
    protected ?string $option;

    public function __construct(int $userId, ?string $option = null)
    {
        $this->userId = $userId;
        $this->option = $option;
    }

    /**
     * Get user ID.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the completed option.
     */
    public function getOption()
    {
        return $this->option;
    }
}

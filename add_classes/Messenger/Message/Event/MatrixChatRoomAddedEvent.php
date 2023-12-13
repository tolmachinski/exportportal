<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event;

use App\Bridge\Matrix\Message\RoomMessageOptions;
use InvalidArgumentException;

/**
 * Event triggered when matrix room was created.
 *
 * @author Anton Zencenco
 */
final class MatrixChatRoomAddedEvent
{
    /**
     * The profile room id value.
     */
    private string $roomId;

    /**
     * The new chat options.
     */
    private ?RoomMessageOptions $options;

    /**
     * @param string $roomId the profile room ID value
     */
    public function __construct(string $roomId, ?RoomMessageOptions $options)
    {
        $this->roomId = $roomId;
        $this->options = $options;
    }

    /**
     * Get the profile room id value.
     */
    public function getRoomId(): string
    {
        return $this->roomId;
    }

    /**
     * Set the profile room id value.
     */
    public function setRoomId(string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Set the options.
     *
     * @param null|array|RoomMessageOptions $options
     */
    public function setOptions($options): self
    {
        if (is_array($options)) {
            $options = new RoomMessageOptions($options);
        }
        if (!$options instanceof RoomMessageOptions) {
            throw new InvalidArgumentException(
                \sprintf('The option "%s" must be an instance of the %s', 'options', RoomMessageOptions::class)
            );
        }
        $this->options = $options;

        return $this;
    }

    /**
     * Get the options.
     */
    public function getOptions(): ?RoomMessageOptions
    {
        return $this->options;
    }
}

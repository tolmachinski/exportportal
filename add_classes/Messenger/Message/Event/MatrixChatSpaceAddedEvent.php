<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event;

use App\Bridge\Matrix\Message\SpaceMessageOptions;
use InvalidArgumentException;

/**
 * Event triggered when matrix space was created.
 *
 * @author Anton Zencenco
 */
final class MatrixChatSpaceAddedEvent
{
    /**
     * The profile room id value.
     */
    private string $roomId;

    /**
     * The new chat options.
     */
    private ?SpaceMessageOptions $options;

    /**
     * @param string $roomId the profile room ID value
     */
    public function __construct(string $roomId, ?SpaceMessageOptions $options)
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
     * @param null|array|SpaceMessageOptions $options
     */
    public function setOptions($options): self
    {
        if (is_array($options)) {
            $options = new SpaceMessageOptions($options);
        }
        if (!$options instanceof SpaceMessageOptions) {
            throw new InvalidArgumentException(
                \sprintf('The option "%s" must be an instance of the %s', 'options', SpaceMessageOptions::class)
            );
        }
        $this->options = $options;

        return $this;
    }

    /**
     * Get the options.
     */
    public function getOptions(): ?SpaceMessageOptions
    {
        return $this->options;
    }
}

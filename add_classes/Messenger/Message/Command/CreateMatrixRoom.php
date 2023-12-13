<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

use App\Bridge\Matrix\Message\RoomMessageOptions;
use ExportPortal\Contracts\Chat\Message\RoomMessageInterface;
use InvalidArgumentException;

/**
 * Command that creates configurable matrix room.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixRoom implements RoomMessageInterface
{
    /**
     * The new chat subject.
     */
    private ?string $subject;

    /**
     * The new chat options.
     */
    private ?RoomMessageOptions $options;

    public function __construct(?string $subject = null, ?RoomMessageOptions $options = null)
    {
        $this->subject = $subject;
        $this->options = $options;
    }

    /**
     * Get the subject.
     */
    public function getSubject(): ?string
    {
        return $this->subject ?? null;
    }

    /**
     * Set the new chat subject.
     */
    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the sender ID.
     */
    public function getSenderId(): ?string
    {
        return $this->options ? $this->options->getSenderId() : null;
    }

    /**
     * Get the recipient ID.
     */
    public function getRecipientId(): ?string
    {
        return $this->options ? $this->options->getRecipientId() : null;
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

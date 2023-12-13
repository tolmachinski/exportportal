<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

use App\Bridge\Matrix\Message\SpaceMessageOptions;
use ExportPortal\Contracts\Chat\Message\RoomMessageInterface;
use InvalidArgumentException;

/**
 * Command that creates configurable matrix space.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixSpace implements RoomMessageInterface
{
    /**
     * The new chat subject.
     */
    private ?string $subject;

    /**
     * The new chat options.
     */
    private ?SpaceMessageOptions $options;

    public function __construct(?string $subject = null, ?SpaceMessageOptions $options = null)
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

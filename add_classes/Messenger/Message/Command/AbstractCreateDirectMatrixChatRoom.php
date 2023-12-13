<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceOptionsInterface;
use InvalidArgumentException;

/**
 * Base command for creating direct matrix chat rooms/.
 *
 * @author Anton Zencenco
 */
abstract class AbstractCreateDirectMatrixChatRoom
{
    /**
     * The new chat subject.
     */
    private ?string $subject;

    /**
     * The sender ID.
     */
    private int $senderId;

    /**
     * The recipient ID.
     */
    private int $recipientId;

    /**
     * The resource options.
     */
    private ?ResourceOptionsInterface $resourceOptions;

    /**
     * @param null|array|ResourceOptionsInterface $resourceOptions
     */
    public function __construct(?string $subject, int $senderId, int $recipientId, $resourceOptions = null)
    {
        $this->subject = $subject;
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
        $this->setResourceOptions($resourceOptions);
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
    public function getSenderId(): int
    {
        return $this->senderId;
    }

    /**
     * Set the sender ID.
     *
     * @return $this
     */
    public function setSenderId(int $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * Get the recipient ID.
     */
    public function getRecipientId(): int
    {
        return $this->recipientId;
    }

    /**
     * Set the recipient ID.
     *
     * @return $this
     */
    public function setRecipientId(int $recipientId): self
    {
        $this->recipientId = $recipientId;

        return $this;
    }

    /**
     * Set the options.
     *
     * @param mixed $resourceOptions
     */
    public function setResourceOptions($resourceOptions): self
    {
        if (is_array($resourceOptions)) {
            $className = $resource['type'] ?? ResourceOptions::class;
            $resourceOptions = new $className($resourceOptions['options']);
        }
        if (!$resourceOptions instanceof ResourceOptionsInterface) {
            throw new InvalidArgumentException(
                \sprintf('The option "%s" must be an instance of the %s', 'resourceOptions', ResourceOptionsInterface::class)
            );
        }

        $this->resourceOptions = $resourceOptions;

        return $this;
    }

    /**
     * Get the options.
     */
    public function getResourceOptions(): ?ResourceOptionsInterface
    {
        return $this->resourceOptions;
    }
}

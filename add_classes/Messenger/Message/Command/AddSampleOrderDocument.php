<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that adds the document to the sample order.
 *
 * @author Anton Zencenco
 */
final class AddSampleOrderDocument
{
    /**
     * The room ID.
     */
    private ?string $roomId;

    /**
     * The sample order ID.
     */
    private int $orderId;

    /**
     * The seller ID.
     */
    private int $sellerId;

    /**
     * The buyer ID.
     */
    private int $buyerId;

    /**
     * The document filename.
     */
    private string $filename;

    /**
     * The message shown after document is created.
     */
    private string $message;

    /**
     * The document type.
     */
    private string $type;

    public function __construct(int $orderId, int $sellerId, int $buyerId, string $type, string $filename, string $message, ?string $roomId = null)
    {
        $this->type = $type;
        $this->roomId = $roomId;
        $this->orderId = $orderId;
        $this->buyerId = $buyerId;
        $this->sellerId = $sellerId;
        $this->filename = $filename;
        $this->message = $message;
    }

    /**
     * Get the sample order ID.
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Set the sample order ID.
     *
     * @return $this
     */
    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get the seller ID.
     */
    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    /**
     * Set the seller ID.
     *
     * @return $this
     */
    public function setSellerId(int $sellerId): self
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    /**
     * Get the buyer ID.
     */
    public function getBuyerId(): int
    {
        return $this->buyerId;
    }

    /**
     * Set the buyer ID.
     *
     * @return $this
     */
    public function setBuyerId(int $buyerId): self
    {
        $this->buyerId = $buyerId;

        return $this;
    }

    /**
     * Get the document filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Set the document filename.
     *
     * @return $this
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the message shown after document is created.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set the message shown after document is created.
     *
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the document type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the document type.
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the room ID.
     */
    public function getRoomId(): ?string
    {
        return $this->roomId;
    }

    /**
     * Set the room ID.
     *
     * @return $this
     */
    public function setRoomId(?string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }
}

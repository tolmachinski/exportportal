<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Message;

use App\Envelope\Message\AccessRulesAwareTrait;

/**
 * Message that is send to bind the envelope and order.
 */
final class CreateOrderContractMessage
{
    use AccessRulesAwareTrait;

    /**
     * The generic user ID.
     */
    private string $genericUserId;

    /**
     * The order ID.
     */
    private int $orderId;

    /**
     * The seller ID.
     */
    private ?int $sellerId;

    /**
     * The buyer ID.
     */
    private ?int $buyerId;

    /**
     * The shipper ID.
     */
    private ?int $shipperId;

    /**
     * The envelope title.
     */
    private string $title;

    /**
     * The envelope type.
     */
    private string $type;

    /**
     * The envelope description.
     */
    private string $description;

    /**
     * The contract name.
     */
    private string $contractName;

    /**
     * The flag that indicates if envelope is digital.
     */
    private bool $digital;

    /**
     * Creates instance of the message.
     */
    public function __construct(
        string $genericUserId,
        int $orderId,
        ?int $sellerId,
        ?int $buyerId,
        ?int $shipperId,
        string $title,
        string $type,
        string $description,
        string $contractName,
        bool $digital = false
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->orderId = $orderId;
        $this->buyerId = $buyerId;
        $this->sellerId = $sellerId;
        $this->shipperId = $shipperId;
        $this->description = $description;
        $this->contractName = $contractName;
        $this->genericUserId = $genericUserId;
        $this->digital = $digital;
    }

    /**
     * Get the generic user ID.
     */
    public function getGenericUserId(): string
    {
        return $this->genericUserId;
    }

    /**
     * Determines if the envelope is digital.
     */
    public function isDigital(): bool
    {
        return $this->digital;
    }

    /**
     * Get the order ID.
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Get the seller ID.
     */
    public function getSellerId(): ?int
    {
        return $this->sellerId;
    }

    /**
     * Get the buyer ID.
     */
    public function getBuyerId(): ?int
    {
        return $this->buyerId;
    }

    /**
     * Get the shipper ID.
     */
    public function getShipperId(): ?int
    {
        return $this->shipperId;
    }

    /**
     * Get the envelope title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the envelope type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the envelope description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the contract name.
     */
    public function getContractName(): string
    {
        return $this->contractName;
    }
}

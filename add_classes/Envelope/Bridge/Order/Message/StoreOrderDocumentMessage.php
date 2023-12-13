<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Message;

final class StoreOrderDocumentMessage
{
    /**
     * The user ID.
     */
    private string $userId;

    /**
     * The order ID.
     */
    private int $orderId;

    /**
     * The file name.
     */
    private string $name;

    /**
     * The file type.
     */
    private ?string $type;

    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The parent document ID.
     */
    private ?int $parentDocumentId;

    /**
     * The list of assignees.
     *
     * @var int[]
     */
    private array $assignees;

    /**
     * The list of recipients.
     *
     * @var int[]
     */
    private array $recipients;

    /**
     * The label for the documents.
     */
    private ?string $label;

    /**
     * The flag that indicates if files are authoriative copies (originals).
     */
    private bool $authoriative;

    /**
     * Creates the instance of message.
     */
    public function __construct(
        int $envelopeId,
        int $orderId,
        string $userId,
        string $name,
        ?string $type,
        array $assignees = [],
        array $recipients = [],
        ?string $label = null,
        bool $authoriative = false,
        ?int $parentDocumentId = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label;
        $this->userId = $userId;
        $this->orderId = $orderId;
        $this->assignees = $assignees;
        $this->recipients = $recipients;
        $this->envelopeId = $envelopeId;
        $this->authoriative = $authoriative;
        $this->parentDocumentId = $parentDocumentId;
    }

    /**
     * Determines if files are authoriative copies.
     */
    public function isAuthoriative(): bool
    {
        return $this->authoriative;
    }

    /**
     * Get the file name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the file type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the user ID.
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Get the order ID.
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Get the draft envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the list of assignees.
     *
     * @return int[]
     */
    public function getAssignees(): array
    {
        return $this->assignees;
    }

    /**
     * Get the list of recipients.
     *
     * @return int[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Get the label for the documents.
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Get the parent document ID.
     */
    public function getParentDocumentId(): ?int
    {
        return $this->parentDocumentId;
    }
}

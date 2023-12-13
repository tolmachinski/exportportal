<?php

declare(strict_types=1);

namespace App\Envelope\Message;

final class CopyFilesMessage
{
    /**
     * The envelope ID.
     */
    private int $senderId;

    /**
     * The envelope ID.
     */
    private int $envelopeId;

    /**
     * The parent document ID.
     */
    private ?int $parentDocumentId;

    /**
     * The files UUIDs.
     */
    private array $files;

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
     *
     * @param int[] $assignees
     * @param int[] $recipients
     */
    public function __construct(
        int $envelopeId,
        int $senderId,
        array $files,
        array $assignees = [],
        array $recipients = [],
        ?string $label = null,
        bool $authoriative = false,
        ?int $parentDocumentId = null
    ) {
        $this->label = $label;
        $this->files = $files;
        $this->senderId = $senderId;
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
     * Get the envelope ID.
     */
    public function getSenderId(): int
    {
        return $this->senderId;
    }

    /**
     * Get the draft envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the files UUIDs.
     */
    public function getFiles(): array
    {
        return $this->files;
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

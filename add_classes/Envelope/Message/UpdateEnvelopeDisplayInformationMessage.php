<?php

declare(strict_types=1);

namespace App\Envelope\Message;

/**
 * Message that is send to update the envelope draft.
 */
final class UpdateEnvelopeDisplayInformationMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private ?int $envelopeId;

    /**
     * The sender ID.
     */
    private int $senderId;

    /**
     * The draft envelope title.
     */
    private string $title;

    /**
     * The draft envelope type.
     */
    private string $type;

    /**
     * The draft envelope description.
     */
    private string $description;

    public function __construct(
        ?int $envelopeId,
        int $senderId,
        string $title,
        string $type,
        string $description
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->description = $description;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): ?int
    {
        return $this->envelopeId;
    }

    /**
     * Get the sender ID.
     */
    public function getSenderId(): int
    {
        return $this->senderId;
    }

    /**
     * Get the draft envelope title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the draft envelope type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the draft envelope description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}

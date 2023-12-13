<?php

declare(strict_types=1);

namespace App\Envelope\Message;

/**
 * Message that is send to update the envelope draft.
 */
class UpdateEnvelopeDraftMessage
{
    use AccessRulesAwareTrait;

    /**
     * The envelope ID.
     */
    private int $envelopeId;

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

    /**
     * The signing mechanism type.
     */
    private string $signingMechanism;

    /**
     * The draft envelope properties.
     */
    private array $properties;

    /**
     * The draft envelope recipients.
     */
    private array $recipients;

    /**
     * The draft envelope temporary files.
     */
    private array $temporaryFiles;

    public function __construct(
        int $envelopeId,
        int $senderId,
        string $title,
        string $type,
        string $description,
        string $signingMechanism,
        array $properties = [],
        array $recipients = [],
        array $temporaryFiles = []
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->senderId = $senderId;
        $this->envelopeId = $envelopeId;
        $this->description = $description;
        $this->recipients = $recipients;
        $this->properties = $properties;
        $this->temporaryFiles = $temporaryFiles;
        $this->signingMechanism = $signingMechanism;
    }

    /**
     * Get the envelope ID.
     */
    public function getEnvelopeId(): int
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

    /**
     * Get the signing mechanism type.
     */
    public function getSigningMechanism(): string
    {
        return $this->signingMechanism;
    }

    /**
     * Get the draft envelope properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get the draft envelope recipients.
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Get the draft envelope temporary files.
     */
    public function getTemporaryFiles(): array
    {
        return $this->temporaryFiles;
    }
}

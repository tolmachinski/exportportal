<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Notifier;

final class Sender
{
    /**
     * The ID of the sender.
     */
    public int $id;

    /**
     * The name of the sender.
     */
    public ?string $name;

    /**
     * The group of the sender.
     */
    public ?string $group;

    /**
     * The legal name of the sender.
     */
    private ?string $legalName;

    public function __construct(int $id, ?string $name, ?string $legalName, ?string $group)
    {
        $this->id = $id;
        $this->name = $name;
        $this->group = $group;
        $this->legalName = $legalName;
    }

    /**
     * Get the ID of the sender.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the name of the sender.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the group of the sender.
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Get the legal name of the sender.
     */
    public function getLegalName(): ?string
    {
        return $this->legalName;
    }
}

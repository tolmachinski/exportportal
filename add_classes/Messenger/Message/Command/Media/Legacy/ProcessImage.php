<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Media\Legacy;

/**
 * Command that begins the image processing using legacy image handler.
 *
 * @author Anton Zencenco
 */
final class ProcessImage
{
    /**
     * The path to the file.
     */
    private string $filePath;

    /**
     * The destination directory.
     */
    private string $destination;

    /**
     * The original name of the file.
     */
    private string $originalName;

    /**
     * The processing configurations.
     */
    private array $configurations;

    public function __construct(string $filePath, ?string $originalName = null, string $destination, array $configurations = [])
    {
        $this->filePath = $filePath;
        $this->destination = $destination;
        $this->originalName = $originalName ?? \basename($filePath);
        $this->configurations = $configurations;
    }

    /**
     * Get the path to the file.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Set the path to the file.
     */
    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get the destination directory.
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Set the destination directory.
     */
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get the original name of the file.
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * Set the original name of the file.
     */
    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName ?? \basename($this->getFilePath());

        return $this;
    }

    /**
     * Get the processing configurations.
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * Set the processing configurations.
     */
    public function setConfigurations(array $configurations): self
    {
        $this->configurations = $configurations;

        return $this;
    }
}

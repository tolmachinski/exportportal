<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Company;

/**
 * Command that is used to remove seller's company files (image, thumbs, etc.).
 *
 * @author Anton Zencenco
 */
class RemoveSellerFiles
{
    /**
     * The company's ID value.
     */
    private int $companyId;

    /**
     * The list for files.
     */
    private array $files = [];

    /**
     * Create the message.
     *
     * @param int   $companyId the company's ID value
     * @param array $files     the list of files to delete
     */
    public function __construct(int $companyId, array $files = [])
    {
        $this->files = $files;
        $this->companyId = $companyId;
    }

    /**
     * Get the company's ID value.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Set the company's ID value.
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get the list for files.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Set the list for files.
     */
    public function setFiles(array $files): self
    {
        $this->files = $files;

        return $this;
    }
}

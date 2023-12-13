<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Company;

/**
 * Command that is used to update seller's company logo.
 *
 * @author Anton Zencenco
 */
final class UpdateSellerLogo
{
    /**
     * The company's ID value.
     */
    private int $companyId;

    /**
     * The path to the new logo.
     */
    private ?string $path;

    /**
     * The flag that indicates if the logo temporary.
     */
    private bool $temporary;

    /**
     * @param int    $companyId the company's ID value
     * @param string $path      the path to the new logo
     */
    public function __construct(int $companyId, ?string $path, bool $temporary = false)
    {
        $this->path = $path;
        $this->companyId = $companyId;
        $this->temporary = $temporary;
    }

    /**
     * Determine if the original logo is temporary.
     */
    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    /**
     * Get the company ID value.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Set the company ID value.
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get the path to the new logo.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Set the path to the new logo.
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }
}

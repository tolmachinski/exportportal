<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Anton Zencenco
 */
class SellerCompanyLogoUpdateEvent extends Event
{
    /**
     * The update company ID.
     */
    protected int $companyId;

    /**
     * The company information.
     */
    protected array $company;

    /**
     * The path to the logo.
     */
    protected ?string $logoPath;

    public function __construct(int $companyId, array $company, ?string $logoPath = null)
    {
        $this->company = $company;
        $this->logoPath = $logoPath;
        $this->companyId = $companyId;
    }

    /**
     * Get company ID.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Get the company information.
     */
    public function getCompany(): array
    {
        return $this->company;
    }

    /**
     * Get the path to the logo.
     */
    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }
}

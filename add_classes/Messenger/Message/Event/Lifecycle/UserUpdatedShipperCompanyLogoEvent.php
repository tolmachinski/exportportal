<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Lifecycle;

use App\Messenger\Message\Event\AbstractUserAccountEvent;

/**
 * Event triggered when user updated the shipper company logo.
 */
final class UserUpdatedShipperCompanyLogoEvent extends AbstractUserAccountEvent
{
    /**
     * The company's ID value.
     */
    protected int $companyId;

    /**
     * The logo path.
     */
    protected ?string $logoPath;

    /**
     * @param int $userId the user ID value
     */
    public function __construct(int $userId, int $companyId, ?string $logoPath = null)
    {
        parent::__construct($userId);

        $this->companyId = $companyId;
        $this->logoPath = $logoPath;
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
     * Get the logo path.
     */
    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    /**
     * Set the logo path.
     */
    public function setLogoPath(?string $logoPath): self
    {
        $this->logoPath = $logoPath;

        return $this;
    }
}

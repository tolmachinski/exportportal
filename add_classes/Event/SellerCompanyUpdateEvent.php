<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Anton Zencenco
 */
class SellerCompanyUpdateEvent extends Event
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
     * The update information.
     */
    protected array $changes;

    /**
     * The flag that indicates if the main information stack was updated or only addendum.
     */
    protected bool $addendumUpdate;

    public function __construct(int $companyId, array $company, array $changes, bool $isAddendumUpdate)
    {
        $this->company = $company;
        $this->changes = $changes;
        $this->companyId = $companyId;
        $this->addendumUpdate = $isAddendumUpdate;
    }

    /**
     * Determine if the main company information was updated or just and addendum.
     */
    public function isAddendumUpdate(): bool
    {
        return $this->addendumUpdate;
    }

    /**
     * Get company ID.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Get update information.
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Get the company information.
     */
    public function getCompany(): array
    {
        return $this->company;
    }
}

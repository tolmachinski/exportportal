<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\ElasticSearch;

/**
 * Command that starts the removing of the seller company.
 */
final class RemoveSellerCompany
{
    /**
     * The seller company ID.
     */
    private int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Get the seller company ID.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Set the seller company ID.
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }
}

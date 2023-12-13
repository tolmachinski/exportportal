<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\DataProvider\CompanyProvider;

/**
 * @author Bendiucov Tatiana
 */
final class CompanyGuardService
{
    /**
     * The company data provider.
     */
    private CompanyProvider $companyProvider;

    /**
     * Create the service.
     */
    public function __construct(CompanyProvider $companyProvider)
    {
        $this->companyProvider = $companyProvider;
    }

    /**
     * Check if the the user id belongs to the a company
     */
    public function checkOwnsCompany(int $companyId, int $userId): bool
    {
        if(null === $companyId || null === $userId){
            return false;
        }
        $companyRespository = $this->companyProvider->getRepository();

        return (bool) $companyRespository->countAllBy([
            'scopes'=> ['userId' => $userId, 'id' => $companyId]
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\CompanyNotFoundException;

/**
 * The company data provider service.
 *
 * @author Anton Zencenco
 */
final class CompanyProvider
{
    /**
     * The requests repository.
     */
    private Model $companyRepository;

    /**
     * Create the company data provider service class.
     *
     * @param ModelLocator $modelLocator the models locator
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->companyRepository = $modelLocator->get(\Seller_Companies_Model::class);
    }

    /**
     * Get the company repository.
     */
    public function getRepository(): Model
    {
        return $this->companyRepository;
    }

    /**
     * Get company.
     */
    public function getCompany(?int $companyId): array
    {
        if (
            null === $companyId
            || null === ($company = $this->companyRepository->findOne($companyId, ['with' => ['type']]))
        ) {
            throw new CompanyNotFoundException(sprintf('The company with ID "%s" is not found.', $companyId));
        }
        $company['user'] = $this->companyRepository->getRelation('user')->getRelated()->findOne($company['id_user'], [
            'with' => ['group'],
        ]);

        return $company;
    }

    /**
     * Get company.
     */
    public function getUserSourceCompany(?int $sourceAccountId): array
    {
        if (
            null === $sourceAccountId
            || null === ($company = $this->companyRepository->findOneBy([
                'with'   => ['type', 'industries', 'categories'],
                'scopes' => ['userId' => $sourceAccountId],
            ]))
        ) {
            throw new CompanyNotFoundException(sprintf('The company for user account with ID "%s" is not found.', $sourceAccountId));
        }
        $company['user'] = $this->companyRepository->getRelation('user')->getRelated()->findOne($company['id_user'], [
            'with' => ['group'],
        ]);

        return $company;
    }

    /**
     * Get company for details popups.
     */
    public function getDetailedCompany(?int $companyId): array
    {
        if (
            null === $companyId
            || null === ($company = $this->companyRepository->findOne($companyId, [
                'with'   => [
                    'type',
                    'country',
                    'state',
                    'city',
                    'phoneCode as stored_phone_code',
                    'faxCode as stored_fax_code',
                ],
            ]))
        ) {
            throw new CompanyNotFoundException(sprintf('The company with ID "%s" is not found.', $companyId));
        }

        return $company;
    }

    /**
     * Get user profile for details popups.
     */
    public function getCompanyForEditPage(?int $companyId): array
    {
        if (
            null === $companyId
            || null === ($company = $this->companyRepository->findOne($companyId, [
                'with'   => [
                    'user',
                    'type',
                    'country',
                    'state',
                    'city',
                    'industries',
                    'categories',
                    'phoneCode as stored_phone_code',
                    'faxCode as stored_fax_code',
                ],
            ]))
        ) {
            throw new CompanyNotFoundException(sprintf('The company with ID "%s" is not found.', $companyId));
        }

        return $company;
    }

    /**
     * Get user profile for details popups.
     */
    public function getCompanyForEditForm(?int $companyId): array
    {
        if (
            null === $companyId
            || null === ($company = $this->companyRepository->findOne($companyId, [
                'with'   => [
                    'user',
                    'city',
                    'phoneCode as stored_phone_code',
                    'faxCode as stored_fax_code',
                ],
            ]))
        ) {
            throw new CompanyNotFoundException(sprintf('The company with ID "%s" is not found.', $companyId));
        }

        return $company;
    }

    /**
     * Get the list of companies related to the users.
     */
    public function getRelatedCompanies(array $usersIds): array
    {
        if (empty($usersIds)) {
            return [];
        }

        return $this->companyRepository->findAllBy([
            'with'   => ['ownerGroup'],
            'scopes' => [
                'usersIds' => $usersIds,
                'isBranch' => false,
            ],
        ]);
    }
}

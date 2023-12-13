<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\NotFoundException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * The company edit requests data provider service.
 *
 * @author Anton Zencenco
 */
final class CompanyEditRequestProvider
{
    /**
     * The requests repository.
     */
    private Model $requestsRepository;

    /**
     * Create the company edit data proivder class.
     *
     * @param ModelLocator $modelLocator the models locator
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->requestsRepository = $modelLocator->get(\Seller_Company_Edit_Requests_Model::class);
    }

    /**
     * Get the company edit request.
     *
     * @throws NotFoundException if company edit request is not found
     */
    public function getRequest(?int $editRequestId): array
    {
        if (null === $editRequestId || null === ($editRequest = $this->requestsRepository->findOne($editRequestId))) {
            throw new NotFoundException(
                sprintf('The company edit request with ID "%s" is not found', (string) ($editRequestId ?? 'NULL'))
            );
        }

        return $editRequest;
    }

    /**
     * Get the company edit request for declining action.
     *
     * @throws NotFoundException if company edit request is not found
     */
    public function getDecliningRequest(?int $editRequestId): array
    {
        return $this->getRequest($editRequestId);
    }

    /**
     * Get the company edit request for accept action.
     *
     * @throws NotFoundException if company edit request is not found
     */
    public function getPendingRequest(?int $editRequestId): array
    {
        if (null === $editRequestId || null === (
            $editRequest = $this->requestsRepository->findOne($editRequestId, ['with' => ['phoneCode', 'faxCode', 'city']])
        )) {
            throw new NotFoundException(
                sprintf('The company edit request with ID "%s" is not found', (string) ($editRequestId ?? 'NULL'))
            );
        }

        return $editRequest;
    }

    /**
     * Get the detailed company edit request.
     *
     * @throws NotFoundException if company edit request is not found
     */
    public function getDetailedRequest(?int $editRequestId): array
    {
        if (
            null === $editRequestId
            || null === (
                $editRequest = $this->requestsRepository->findOne($editRequestId, [
                    'with' => ['user', 'country', 'state', 'city', 'phoneCode', 'faxCode', 'type'],
                ])
            )
        ) {
            throw new NotFoundException(sprintf('The company edit request with ID "%s" is not found', (string) ($editRequestId ?? 'NULL')));
        }
        $editRequest['documents'] = new ArrayCollection(
            $this->requestsRepository
                ->getRelation('documents')
                ->getRelated()
                ->findAllBy(['scopes' => ['request' => $editRequestId], 'with' => ['type']])
        );

        return $editRequest;
    }

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForGrid(?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->requestsRepository->getPaginator(['scopes' => $filters], $perPage, $page);
        $paginator['all'] = $this->requestsRepository->countAllBy(['scopes' => $commonFilters ?? []]);
        $paginator['data'] = $this->requestsRepository->findAllBy([
            'with'   => ['extendedUser as user', 'extendedCompany as company'],
            'scopes' => $filters,
            'order'  => $ordering ?? [],
            'limit'  => $perPage,
            'skip'   => (($page ?? 1) - 1) * $perPage,
        ]);

        return $paginator;
    }
}

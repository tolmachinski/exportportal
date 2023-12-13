<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\Exceptions\B2bRequestNotFoundException;
use App\Common\Transformers\B2bIndexedDataTransformer;
use Elasticsearch_B2b_Model;
use Spatie\Fractalistic\Fractal;

/**
 * The b2b data provider service.
 *
 * @author Bendiucov Tatiana
 */
final class B2bIndexedRequestProvider
{
    /**
     * The requests repository.
     */
    private Elasticsearch_B2b_Model $b2bElasticRepository;

    /**
     * Create the b2b data provider service class.
     *
     * @param Elasticsearch_B2b_Model $b2bElasticRepository the b2b elasticsearch model
     */
    public function __construct(Elasticsearch_B2b_Model $b2bElasticRepository)
    {
        $this->b2bElasticRepository = $b2bElasticRepository;
    }

    /**
     * Get the b2b repository.
     */
    public function getRepository(): Model
    {
        return $this->b2bElasticRepository;
    }

    /**
     * Get b2b request from elastic.
     *
     * @throws B2bRequestNotFoundException
     *
     * @return array with one request
     */
    public function getRequest(?int $b2bRequestId): array
    {
        if (empty($b2bRequestId)) {
            throw new B2bRequestNotFoundException();
        }

        //get requests with the id
        $this->b2bElasticRepository->getB2bRequests(['id' => $b2bRequestId]);
        $b2bRequest = (array) $this->b2bElasticRepository->records;
        if (empty($b2bRequest)) {
            throw new B2bRequestNotFoundException($b2bRequestId, sprintf('The b2b request with ID "%s" is not found.', $b2bRequestId));
        }

        return $b2bRequest[0];
    }

    /**
     * Get latest b2b requests.
     *
     * @param int  $numberOfRequests - per page
     * @param bool $normalize        - to transform data or not
     */
    public function getLatestRequests(?int $numberOfRequests, bool $normalize = true): array
    {
        $b2bRequests = $this->b2bElasticRepository->getB2bRequests([
            'sortBy'  => ['registerDate' => 'desc'],
            'perPage' => $numberOfRequests,
        ]);

        return $normalize ? $this->prepareData($b2bRequests) : $b2bRequests;
    }

    /**
     * Get all b2b requests by filters.
     */
    public function getAllRequestsByFilters(array $conditions, bool $normalize = true): array
    {
        $b2bRequests = $this->b2bElasticRepository->getB2bRequests($conditions);
        $aggregations = $this->b2bElasticRepository->getAllAggregations();

        return [
            'allCategoriesAggregation' => $aggregations['aggregateAllCategories'],
            'allCountriesAggregation'  => $aggregations['aggregateAllCountries'],
            'allIndustriesAggregation' => $aggregations['aggregateAllIndustries'],
            'requests'                 => $normalize ? $this->prepareData($b2bRequests) : (array) $b2bRequests,
            'total'                    => $this->b2bElasticRepository->getRecordsCount(),
        ];
    }

    /**
     * Process all requests to get data like in database through Fractal.
     */
    private function prepareData(?array $dataRequests)
    {
        return empty($dataRequests) ? [] : Fractal::create()
            ->collection($dataRequests)
            ->transformWith(new B2bIndexedDataTransformer())
            ->toArray()['data'];
    }
}

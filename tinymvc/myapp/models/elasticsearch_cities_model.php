<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;

/**
 * Elasticsearch_Cities model
 */
final class Elasticsearch_Cities_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "zips";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ZIPS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * {@inheritdoc}
     */
    private $type = 'cities';

    /**
     * {@inheritdoc}
     */
    public $cities = [];

    /**
     * {@inheritdoc}
     */
    public $countCities = 0;

    /**
     * {@inheritdoc}
     */
    public $aggregates = null;

    /**
     * @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary
     */
    protected $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * The method created for indexing cities data in elasticsearch
     *
     * @param int|null $cityId
     *
     * @return void
     */
    public function index(?int $cityId = null): void
    {
        if (null !== $cityId) {
            $cities = $this->getCitiesFromMySql(['id' => $cityId], 1);
            if (!empty($city = array_shift($cities))) {
                $this->elasticsearchLibrary->index($this->type, $city['id'], $city);
            }

            return;
        }

        ini_set('max_execution_time', '0');
        ini_set('request_terminate_timeout', '0');

        $countCities = $this->countAll();
        $countIndexedCities = 0;
        $limit = 100000;
        $skip = 0;

        if (PHP_SAPI === 'cli') {
            $this->showIndexingStatus(0, $countCities);
        }

        while (!empty($cities = $this->getCitiesFromMySql(null, $limit, $skip))) {
            $this->elasticsearchLibrary->indexBulk($this->type, $cities);
            $skip += $limit;
            $countIndexedCities += count($cities);
            unset($cities);

            if (PHP_SAPI === 'cli') {
                $this->showIndexingStatus($countIndexedCities, $countCities);
            }
        }
    }

    public function getCities(array $params = [], int $page = 1, int $perPage = 20): array
    {
        $page = $page > 1 ? $page : 1;
        $perPage = $perPage > 0 ? $perPage : 10;

        $filterMust = $filterMustNot = [];

        if (!empty($params['id'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_term('id', (int) $params['id']);
        }

        if (!empty($params['stateId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_term('state.id', (int) $params['stateId']);
        }

        if (isset($params['search'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_multi_match(
                ['name', 'ascii_name'],
                $params['search']
            );
        }

        if (!empty($params['cityName'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_multi_match(['name', 'ascii_name'], $params['cityName']);
        }

        if (!empty($params['stateName'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_multi_match(['state.name', 'state.ascii_name'], $params['stateName']);
        }

        if (!empty($params['countryName'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_multi_match(['country.name', 'country.ascii_name'], $params['countryName']);
        }

        $elasticQuery = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'bool' => [
                            'must' => $filterMust,
                            'must_not' => $filterMustNot
                        ]
                    ]
                ]
            ],
            'sort'  => ['_score'],
            'size'  => $perPage,
            'from'  => $perPage * ($page - 1)
        ];

        if (!empty($params['columns'])) {
            $elasticQuery['_source'] = $params['columns'];
        }

        $elasticResult = $this->elasticsearchLibrary->get($this->type, $elasticQuery);

        if (isset($elasticResult['hits']['hits'])) {
            $this->cities = array_map(fn ($ar) => $ar['_source'], $elasticResult['hits']['hits']);
            $this->countCities = $elasticResult['hits']['total']['value'];
        }

        return $this->cities;
    }

    /**
     * @param int $countryId
     * @return bool
     */
    public function updateCitiesCountryByCountryId(int $countryId): bool
    {
        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);

        $country = $portCountryModel->findOne(
            $countryId,
            [
                'columns'   => [
                    'id',
                    'country AS name',
                    'country_ascii_name AS ascii_name',
                    'country_alias AS alias',
                    'abr',
                    'abr3',
                ],
            ],
        );

        if (empty($country)) {
            return false;
        }

        $res = $this->elasticsearchLibrary->update_by_query(
            $this->elasticsearchLibrary->get_term('country.id', $countryId),
            'ctx._source.country = params.country',
            $this->type,
            [
                'country' => $country
            ]
        );

        return empty($res['failures']);
    }

    /**
     * Get countries from MySQL
     *
     * @param null|array $params
     * @return array
     */
    private function getCitiesFromMySql(?array $params = [], int $limit = 100000, int $skip = 0): array
    {
        $mappingCities = [];
        $citiesTable = $this->getTable();

        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);
        $countryTable = $portCountryModel->getTable();

        /** @var States_Model $statesModel */
        $statesModel = model(States_Model::class);
        $stateTable = $statesModel->getTable();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->select(
                <<<COLUMNS
                    `{$citiesTable}`.`id`,
                    `{$citiesTable}`.`state`,
                    `{$citiesTable}`.`id_country`,
                    `{$citiesTable}`.`city`,
                    `{$citiesTable}`.`city_ascii`,
                    `{$citiesTable}`.`timezone`,
                    `{$countryTable}`.`country`,
                    `{$countryTable}`.`country_ascii_name`,
                    `{$countryTable}`.`country_alias`,
                    `{$countryTable}`.`abr`,
                    `{$countryTable}`.`abr3`,
                    `{$stateTable}`.`state_name`,
                    `{$stateTable}`.`state_ascii_name`,
                    `{$stateTable}`.`state_code`
                COLUMNS
            )
            ->from($citiesTable)
            ->leftJoin($citiesTable, $countryTable, $countryTable, "{$countryTable}.`id` = {$citiesTable}.`id_country`")
            ->leftJoin($citiesTable, $stateTable, $stateTable, "{$stateTable}.`id` = {$citiesTable}.`state`")
            ->setMaxResults($limit)
            ->setFirstResult($skip)
        ;

        if (!empty($params['id'])) {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    "`{$citiesTable}`.`id`",
                    $queryBuilder->createNamedParameter((int) $params['id'], ParameterType::INTEGER, $this->nameScopeParameter('cityId'))
                )
            );
        }

        $cities = $this->getConnection()->fetchAllAssociative(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            (array) $queryBuilder->getParameterTypes()
        );

        foreach ($cities as $city) {
            $mappingCities[] = [
                'id'         => (int) $city['id'],
                'name'       => $city['city'],
                'ascii_name' => $city['city_ascii'],
                'timezone'   => $city['timezone'],
                'state'      => [
                    'id'            => (int) $city['state'],
                    'name'          => $city['state_name'],
                    'ascii_name'    => $city['state_ascii_name'],
                    'code'          => $city['state_code'],
                ],
                'country'   => [
                    'id'            => (int) $city['id_country'],
                    'name'          => $city['country'],
                    'ascii_name'    => $city['country_ascii_name'],
                    'alias'         => $city['country_alias'],
                    'abr'           => $city['abr'],
                    'abr3'          => $city['abr3'],
                ],
            ];
        }

        return $mappingCities;
    }

    private function showIndexingStatus($done, $total, $size=50) {
        static $startTime;
        $startTime = $startTime ?: microtime(true);

        // if we go over our bound, just ignore it
        if ($done > $total || $done <= 0) {
            return;
        }

        $percent = (double) ($done / $total);
        $disp = number_format($percent * 100, 0);

        echo "\r $done/$total $disp% " . number_format(microtime(true) - $startTime, 2) . " sec";

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
        }
    }
}

/* End of file elasticsearch_cities_model.php */
/* Location: /tinymvc/myapp/models/elasticsearch_cities_model.php */

<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Traits\Elasticsearch\AutocompleteAnalyzerTrait;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Elasticsearch_B2b model.
 */
final class Elasticsearch_B2b_Model extends Model
{
    use AutocompleteAnalyzerTrait;

    /**
     * {@inheritdoc}
     */
    public $records;

    /**
     * {@inheritdoc}
     */
    public $recordsCount;

    /**
     * {@inheritdoc}
     */
    public $aggregates;

    /**
     * {@inheritdoc}
     */
    public $aggregateAllIndustries;

    /**
     * {@inheritdoc}
     */
    public $aggregateAllCategories;

    /**
     * {@inheritdoc}
     */
    public $aggregateAllCountries;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_request';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_REQUEST';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_request';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_request'        => Types::INTEGER,
        'id_company'        => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        'id_country'        => Types::INTEGER,
        'id_state'          => Types::INTEGER,
        'id_city'           => Types::INTEGER,
        'id_type'           => Types::INTEGER,
        'b2b_radius'        => Types::INTEGER,
        'b2b_date_register' => Types::DATETIME_IMMUTABLE,
        'b2b_date_update'   => Types::DATETIME_IMMUTABLE,
        'viewed_count'      => Types::INTEGER,
    ];

    /**
     * @var TinyMVC_Library_Elasticsearch
     */
    protected $elasticsearchLibrary;

    /**
     * @var Company_Model
     */
    protected $companyModel;

    /**
     * @var Country_Model
     */
    protected $countryModel;

    /**
     * @var B2b_Model
     */
    protected $b2bModel;
    /**
     * {@inheritdoc}
     */
    private $type = 'b2b_requests';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
        $this->companyModel = model(Company_Model::class);
        $this->countryModel = model(Country_Model::class);
        $this->b2bModel = model(B2b_Model::class);
    }

    public function getRecordsCount()
    {
        return $this->recordsCount;
    }

    /**
     * The method created for indexing b2b requests data in elasticsearch.
     */
    public function index(?int $b2bRequestId = null)
    {
        $b2bRequests = $this->getB2bFromMySql(['id' => $b2bRequestId]);
        $chunkedB2bRequests = array_chunk($b2bRequests, 500);
        $bulkQueries = [];

        foreach ($chunkedB2bRequests as $chunk) {
            foreach ($chunk as $b2bRequest) {
                $tokens = $this->analyzeAutocompleteText($b2bRequest['title']);
                foreach ($tokens as $token) {
                    $b2bRequest['suggest_autocomplete'][] = [
                        'input'  => $token['token'],
                        'weight' => 30,
                    ];
                }

                $b2bRequest['suggest_autocomplete'][] = [
                    'input'  => strtolower($b2bRequest['title']),
                    'weight' => 35,
                ];

                foreach ($b2bRequest['categories'] as $key => $category) {
                    $categoriesTokens = $this->analyzeAutocompleteText($category['name']);
                    foreach ($categoriesTokens as $categotyToken) {
                        if ($categotyToken['token'] === array_key_last($b2bRequest['categories'])) {
                            $b2bRequest['suggest_autocomplete'][] = [
                                'input'  => strtolower($categotyToken['token']),
                                'weight' => 15,
                            ];
                        } else {
                            $b2bRequest['suggest_autocomplete'][] = [
                                'input'  => strtolower($categotyToken['token']),
                                'weight' => 10,
                            ];
                        }
                    }
                }


                foreach ($b2bRequest['industries'] as $key => $industry) {
                    $industriesTokens = $this->analyzeAutocompleteText($industry['name']);
                    foreach ($industriesTokens as $industryToken) {
                        if ($industryToken['token'] === array_key_last($b2bRequest['industries'])) {
                            $b2bRequest['suggest_autocomplete'][] = [
                                'input'  => strtolower($industryToken['token']),
                                'weight' => 20,
                            ];
                        } else {
                            $b2bRequest['suggest_autocomplete'][] = [
                                'input'  => strtolower($industryToken['token']),
                                'weight' => 15,
                            ];
                        }
                    }
                }

                foreach ($b2bRequest['tags'] as $tag) {
                    $b2bRequest['suggest_autocomplete'][] = [
                        'input'  => strtolower($tag),
                        'weight' => 3,
                    ];
                }

                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $b2bRequest['id'],
                        $b2bRequest
                    )
                );
            }
        }

        $this->elasticsearchLibrary->bulk($bulkQueries);
    }

    /**
     * The method created for indexing b2b requests data in elasticsearch by conditions.
     */
    public function indexByConditions(array $conditions)
    {
        $b2bRequests = $this->getB2bFromMySql($conditions);
        $bulkSize = $i = 0;
        $bulkLimit = 500;
        $bulkQueries = [];
        $countRows = count($b2bRequests);
        $lastIndexKey = array_key_last($b2bRequests);

        foreach ($b2bRequests as $key => $b2bRequest) {
            ++$bulkSize;
            ++$i;

            $tokens = $this->analyzeAutocompleteText($b2bRequest['title']);
            foreach ($tokens as $token) {
                $b2bRequest['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 30,
                ];
            }

            $b2bRequest['suggest_autocomplete'][] = [
                'input'  => strtolower($b2bRequest['title']),
                'weight' => 35,
            ];

            foreach ($b2bRequest['categories'] as $key => $category) {
                $categoriesTokens = $this->analyzeAutocompleteText($category['name']);
                foreach ($categoriesTokens as $categotyToken) {
                    if ($categotyToken['token'] === array_key_last($b2bRequest['categories'])) {
                        $b2bRequest['suggest_autocomplete'][] = [
                            'input'  => strtolower($categotyToken['token']),
                            'weight' => 15,
                        ];
                    } else {
                        $b2bRequest['suggest_autocomplete'][] = [
                            'input'  => strtolower($categotyToken['token']),
                            'weight' => 10,
                        ];
                    }
                }
            }

            foreach ($b2bRequest['industries'] as $key => $industry) {
                $industriesTokens = $this->analyzeAutocompleteText($industry['name']);
                foreach ($industriesTokens as $industryToken) {
                    if ($industryToken['token'] === array_key_last($b2bRequest['industries'])) {
                        $b2bRequest['suggest_autocomplete'][] = [
                            'input'  => strtolower($industryToken['token']),
                            'weight' => 20,
                        ];
                    } else {
                        $b2bRequest['suggest_autocomplete'][] = [
                            'input'  => strtolower($industryToken['token']),
                            'weight' => 15,
                        ];
                    }
                }
            }

            foreach ($b2bRequest['tags'] as $tag) {
                $b2bRequest['suggest_autocomplete'][] = [
                    'input'  => strtolower($tag),
                    'weight' => 3,
                ];
            }

            array_push(
                $bulkQueries,
                ...$this->elasticsearchLibrary->bulk_index_query(
                    $this->type,
                    $b2bRequest['id'],
                    $b2bRequest
                )
            );

            if ($bulkLimit == $bulkSize || $lastIndexKey == $key) {
                $this->elasticsearchLibrary->bulk($bulkQueries);

                $bulkSize = 0;
                $bulkQueries = [];
            }

            if ('cli' === PHP_SAPI) {
                $this->showIndexingStatus($i, $countRows);
            }
        }
    }

    /**
     * The method created for getting b2b requests.
     */
    public function getB2bRequests(array $conditions = []): array
    {
        $must = $should = $filterMust = $filterMustNot = [];

        $page = $conditions['page'] ?? 1;
        $perPage = $conditions['perPage'] ?? 20;

        if (!empty($conditions['id'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_term('id', $conditions['id']);
        }

        if (!empty($conditions['industryId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'industries',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('industries.id', $conditions['industryId']),
                    ],
                ]
            );
        }
        if(!empty($conditions['industryIds'])){
            $must[] = $this->elasticsearchLibrary->get_nested(
                'industries',
                [
                    'bool' => [
                        'filter' => [
                            $this->elasticsearchLibrary->get_terms('industries.id', $conditions['industryIds'])
                        ],
                    ],
                ]
            );
        }

        if (!empty($conditions['categoryId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'categories',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('categories.id', $conditions['categoryId']),
                    ],
                ]
            );
        }

        if (!empty($conditions['countryId'])) {
            //if type location is country then seach in countries
            $should[] = [
                'bool' => [
                    'must' => [
                        $this->elasticsearchLibrary->get_nested(
                            'countries',
                            [
                                'bool'  => [
                                    'must'  => $this->elasticsearchLibrary->get_term('countries.id', $conditions['countryId']),
                                ],
                            ]
                        ),
                        $this->elasticsearchLibrary->get_term('type_location', 'country'),
                    ],
                ],
            ];
            //else type location as globally
            $should[] = $this->elasticsearchLibrary->get_term('type_location', 'globally');
            //else if type location is radius then search in country of the company
            $should[] = [
                'bool' => [
                    'must' => [
                        $this->elasticsearchLibrary->get_nested(
                            'company.country',
                            [
                                'bool'  => [
                                    'must'  => $this->elasticsearchLibrary->get_term('company.country.id', $conditions['countryId']),
                                ],
                            ]
                        ),
                        $this->elasticsearchLibrary->get_term('type_location', 'radius'),
                    ],
                ],
            ];
        }

        if (!empty($conditions['partnerTypeId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'partnerType',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('partnerType.id', $conditions['partnerTypeId']),
                    ],
                ]
            );
        }

        if (isset($conditions['keywords'])) {
            $filterMust[] = [
                'bool' => [
                    'should' => [
                        $this->elasticsearchLibrary->get_match('title', $conditions['keywords']),
                        $this->elasticsearchLibrary->get_nested(
                            'industries',
                            [
                                'bool'  => [
                                    'should'  => $this->elasticsearchLibrary->get_match('industries.name', $conditions['keywords'])
                                ]
                            ]
                        ),
                        $this->elasticsearchLibrary->get_nested(
                            'categories',
                            [
                                'bool'  => [
                                    'should'  => $this->elasticsearchLibrary->get_match('categories.name', $conditions['keywords'])
                                ]
                            ]
                        ),
                        $this->elasticsearchLibrary->get_match('message', $conditions['keywords']),
                        $this->elasticsearchLibrary->get_match('tags', $conditions['keywords']),
                    ],
                ],
            ];
        }

        $elasticQuery = [
            'query' => [
                'bool' => [
                    'must'   => $must,
                    'should' => $should,
                    'filter' => [
                        'bool' => [
                            'must'     => $filterMust,
                            'must_not' => $filterMustNot,
                        ],
                    ],
                ],
            ],
            'sort'  => $conditions['sortBy'] ?? [['registerDate' => 'desc'], '_score'],
            'size'  => $perPage,
            'from'  => $perPage * ($page - 1),
        ];

        if (!empty($should)) {
            $elasticQuery['query']['bool']['minimum_should_match'] = 1;
        }

        $elasticResult = $this->elasticsearchLibrary->get($this->type, $elasticQuery);

        if (isset($elasticResult['hits']['hits'])) {
            $this->records = array_map(fn ($ar) => $ar['_source'], $elasticResult['hits']['hits']);
            $this->recordsCount = $elasticResult['hits']['total']['value'];
        }

        return (array) $this->records;
    }

    /**
     * The method created for getting aggregations by industry, category and country.
     */
    public function getAllAggregations(): array
    {
        $elasticQuery = [
            'aggs' => [
                'byCategory' => [
                    'nested' => [
                        'path' => 'categories',
                    ],
                    'aggs' => [
                        'counters' => [
                            'terms' => [
                                'field' => 'categories.id',
                                'size'  => 100000, // count categories
                            ],
                        ],
                    ],
                ],
                'byIndustry' => [
                    'nested' => [
                        'path' => 'industries',
                    ],
                    'aggs' => [
                        'counters' => [
                            'terms' => [
                                'field' => 'industries.id',
                                'size'  => 1000, // count industries
                            ],
                        ],
                    ],
                ],
                'byCountry' => [
                    'nested' => [
                        'path' => 'countries',
                    ],
                    'aggs' => [
                        'counters' => [
                            'terms' => [
                                'field' => 'countries.id',
                                'size'  => 1000, // count countries
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $elasticResults = $this->elasticsearchLibrary->get($this->type, $elasticQuery);
        $aggregations = $elasticResults['aggregations'];

        if (isset($aggregations['byIndustry']) || isset($aggregations['byCategory'])) {
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);
            $allCategories = array_column(
                $categoryModel->getCategories(['columns' => 'category_id, name, parent, industry_id AS industryId']),
                null,
                'category_id'
            );

            foreach ((array) $aggregations['byIndustry']['counters']['buckets'] as $statistic) {
                $this->aggregateAllIndustries[$statistic['key']] = [
                    'counter'   => $statistic['doc_count'],
                    'name'      => $allCategories[$statistic['key']]['name'],
                    'id'        => $statistic['key'],
                ];
            }

            if (!empty($this->aggregateAllIndustries)) {
                uasort($this->aggregateAllIndustries, fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
            }

            foreach ((array) $aggregations['byCategory']['counters']['buckets'] as $statistic) {
                $this->aggregateAllCategories[$statistic['key']] = [
                    'industryId'    => (int) $allCategories[$statistic['key']]['industryId'],
                    'counter'       => $statistic['doc_count'],
                    'parent'        => (int) $allCategories[$statistic['key']]['parent'],
                    'name'          => $allCategories[$statistic['key']]['name'],
                    'id'            => $statistic['key'],
                ];
            }

            if (!empty($this->aggregateAllCategories)) {
                uasort($this->aggregateAllCategories, fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
            }
        }

        if (isset($aggregations['byCountry'])) {
            /** @var Country_Model $countryModel */
            $countryModel = model(Country_Model::class);
            $allCountries = $countryModel->getAllCountries();

            foreach ((array) $aggregations['byCountry']['counters']['buckets'] as $statistic) {
                $this->aggregateAllCountries[$statistic['key']] = [
                    'counter'   => $statistic['doc_count'],
                    'slug'      => $allCountries[$statistic['key']]['country_alias'],
                    'name'      => $allCountries[$statistic['key']]['country'],
                    'id'        => $statistic['key'],
                ];
            }

            if (!empty($this->aggregateAllCountries)) {
                uasort($this->aggregateAllCountries, fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
            }
        }

        return [
            'aggregateAllCountries'  => $this->aggregateAllCountries,
            'aggregateAllCategories' => $this->aggregateAllCategories,
            'aggregateAllIndustries' => $this->aggregateAllIndustries,
        ];
    }

    /**
     * The method created to update a b2b request in ElasticSearch by the request id.
     *
     * @param array $b2bRequest
     */
    public function updateB2bRequestById(int $b2bRequestId, array $b2bRequest = null): bool
    {
        if (null === $b2bRequest) {
            $b2bRequests = $this->getB2bFromMySql(['id' => $b2bRequestId]);
            $b2bRequest = array_shift($b2bRequests);
        }

        $curlResult = $this->elasticsearchLibrary->update($this->type, $b2bRequestId, $b2bRequest);

        return 'updated' === ($curlResult['result'] ?? null);
    }

    /**
     * The method created to update a b2b request company in ElasticSearch by the company id.
     */
    public function updateB2bByConditions(array $conditions): void
    {
        $b2bRequests = $this->getB2bFromMySql($conditions);
        if (!empty($b2bRequests)) {
            foreach ($b2bRequests as $b2bRequest) {
                $this->updateB2bRequestById($b2bRequest['id'], $b2bRequest);
            }
        }
    }

    /**
     * Method created for increment event views.
     */
    public function updateB2bCompanyLogoByCompanyId(int $companyId, string $companyLogo): void
    {
        $this->elasticsearchLibrary->update_by_query(
            $this->elasticsearchLibrary->get_nested(
                'company',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('company.id', $companyId),
                    ],
                ]
            ),
            'ctx._source.company.logo = params.logo',
            $this->type,
            [
                'logo' => $companyLogo,
            ]
        );
    }

    /**
     * The method created to increment views count of b2b request.
     */
    public function incrementViews(int $b2bRequestId): void
    {
        $this->elasticsearchLibrary->update_by_query(
            [
                'term' => [
                    'id' => $b2bRequestId,
                ],
            ],
            'ctx._source.countViews += params.increment',
            $this->type,
            [
                'increment' => 1,
            ]
        );
    }

    /**
     * The method created for removing a b2b request from ElasticSearch by the request id.
     */
    public function removeB2bRequestById(int $b2bRequestId): bool
    {
        $curlResult = $this->elasticsearchLibrary->deleteById($this->type, $b2bRequestId);

        return 'deleted' === $curlResult['result'];
    }

    /**
     * The method created for removing a b2b request from ElasticSearch by conditions.
     */
    public function removeB2bRequestsByConditions(array $conditions): void
    {
        $condition = [];
        if (!empty($conditions['companyId'])) {
            $condition = $this->elasticsearchLibrary->get_nested(
                'company',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('company.id', $conditions['companyId']),
                    ],
                ]
            );
        }

        if (!empty($conditions['userId'])) {
            $condition = $this->elasticsearchLibrary->get_term('userId', $conditions['userId']);
        }

        $this->elasticsearchLibrary->delete_by_query($this->type, $condition);
    }
    // endregion belongs

    // endregion MySQL

    /**
     * Get suggestions for search input.
     */
    public function getSuggestions(string $text): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
        $melasticResults = $elasticsearchLibrary->mget('b2b_requests', [
            [
                '_source' => 'suggest',
                'suggest' => [
                    'b2b-suggest' => [
                        'text'       => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => config('b2b_suggestion_search_size', 10),
                            'skip_duplicates' => true,
                        ],
                    ],
                ],
            ],
        ]);

        return (array) $melasticResults['responses'][0]['suggest']['b2b-suggest'][0];
    }

    // region MySQL

    // region scopes

    /**
     * Scope by b2b request id.
     *
     * @var int
     */
    protected function scopeId(QueryBuilder $builder, int $b2bRequestId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`{$this->getPrimaryKey()}`",
                $builder->createNamedParameter($b2bRequestId, ParameterType::INTEGER, $this->nameScopeParameter('b2bRequestId'))
            )
        );
    }

    /**
     * Scope by b2b active.
     */
    protected function scopeActiveB2bRequest(QueryBuilder $builder, int $isActiveB2bRequest): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`b2b_active`",
                $builder->createNamedParameter($isActiveB2bRequest, ParameterType::INTEGER, $this->nameScopeParameter('isActiveB2bRequest'))
            )
        );
    }

    /**
     * Scope by b2b request status.
     */
    protected function scopeB2bRequestStatus(QueryBuilder $builder, string $b2bRequestStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`status`",
                $builder->createNamedParameter($b2bRequestStatus, ParameterType::STRING, $this->nameScopeParameter('b2bRequestStatus'))
            )
        );
    }

    /**
     * Scope by b2b request blocked.
     */
    protected function scopeB2bRequestBlocked(QueryBuilder $builder, int $isBlockedB2bRequest): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`blocked`",
                $builder->createNamedParameter($isBlockedB2bRequest, ParameterType::INTEGER, $this->nameScopeParameter('isBlockedB2bRequest'))
            )
        );
    }

    /**
     * Scope by b2b request user id.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope by b2b request user fake.
     */
    protected function scopeFakeUser(QueryBuilder $builder, int $fake): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getRelation('users')->getRelated()->getTable()}.`fake_user`",
                $builder->createNamedParameter($fake, ParameterType::INTEGER, $this->nameScopeParameter('isFake'))
            )
        );
    }

    /**
     * Scope by company id.
     */
    protected function scopeCompanyId(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->companyModel->get_company_table()}`.`{$this->companyModel->get_company_table_primary_key()}`",
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('companyId'))
            )
        );
    }

    /**
     * Scope by company blocked.
     *
     * @var int
     */
    protected function scopeCompanyBlocked(QueryBuilder $builder, int $isBlockedCompany): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->companyModel->get_company_table()}`.`blocked`",
                $builder->createNamedParameter($isBlockedCompany, ParameterType::INTEGER, $this->nameScopeParameter('companyBlocked'))
            )
        );
    }

    /**
     * Scope by company visibility.
     *
     * @var int
     */
    protected function scopeCompanyVisible(QueryBuilder $builder, int $isVisibleCompany): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->companyModel->get_company_table()}`.`visible_company`",
                $builder->createNamedParameter($isVisibleCompany, ParameterType::INTEGER, $this->nameScopeParameter('companyVisible'))
            )
        );
    }
    // endregion scopes

    // region binds

    /**
     * Scope for join with advices.
     */
    protected function bindAdvices(QueryBuilder $builder): void
    {
        $requestAdvicesTable = $this->b2bModel->getB2bAdvicesTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $requestAdvicesTable,
                $requestAdvicesTable,
                "`{$requestAdvicesTable}`.`id_request` = `{$this->getTable()}`.`{$this->getPrimaryKey()}`"
            )
        ;
    }

    /**
     * Scope for join with company.
     */
    protected function bindCompany(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->getTable(),
                $this->companyModel->get_company_table(),
                $this->companyModel->get_company_table(),
                "`{$this->companyModel->get_company_table()}`.`{$this->companyModel->get_company_table_primary_key()}` = `{$this->getTable()}`.`id_company`"
            )
        ;
    }

    //endregion binds

    // region belongs

    /**
     * Resolves static relationships with company.
     */
    protected function company(): RelationInterface
    {
        $companyTable = $this->companyModel->get_company_table();
        $countryTable = $this->countryModel->get_countries_table();
        $stateTable = $this->countryModel->get_regions_table();
        $cityTable = $this->countryModel->get_cities_table();

        $relation = $this->belongsTo($companyTable, 'id_company', 'id_company');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$companyTable}`.`id_company`",
                "`{$companyTable}`.`type_company`",
                "`{$companyTable}`.`name_company`",
                "`{$companyTable}`.`legal_name_company`",
                "`{$companyTable}`.`index_name`",
                "`{$companyTable}`.`latitude`",
                "`{$companyTable}`.`longitude`",
                "`{$companyTable}`.`parent_company`",
                "`{$companyTable}`.`logo_company`",
                "`{$companyTable}`.`address_company`",
                "`{$companyTable}`.`zip_company`",
                "`{$companyTable}`.`id_country`",
                "`{$companyTable}`.`id_state`",
                "`{$companyTable}`.`id_city`",
                "`{$countryTable}`.`country`",
                "`{$countryTable}`.`country_alias`",
                "`{$stateTable}`.`state_name`",
                "`{$cityTable}`.`city`"
            )
            ->leftJoin($companyTable, $countryTable, $countryTable, "`{$companyTable}`.`id_country` = `{$countryTable}`.`{$this->countryModel->get_countries_table_primary_key()}`")
            ->leftJoin($companyTable, $stateTable, $stateTable, "`{$companyTable}`.`id_state` = `{$stateTable}`.`{$this->countryModel->get_regions_table_primary_key()}`")
            ->leftJoin($companyTable, $cityTable, $cityTable, "`{$companyTable}`.`id_city` = `{$cityTable}`.`{$this->countryModel->get_cities_table_primary_key()}`")
        ;

        return $relation;
    }

    /**
     * Relation with categories.
     */
    protected function countries(): RelationInterface
    {
        return $this->hasManyThrough(
            Countries_Model::class,
            B2b_Request_Country_Pivot_Model::class,
            'request_id',
            'id',
            $this->getPrimaryKey(),
            'country_id'
        );
    }

    /**
     * Relation with the industries.
     */
    protected function industries(): RelationInterface
    {
        return $this->hasMany(B2b_Request_Industry_Pivot_Model::class, 'id_request')->enableNativeCast();
    }

    /**
     * Relation with the categories.
     */
    protected function categories(): RelationInterface
    {
        return $this->hasMany(B2b_Request_Category_Pivot_Model::class, 'id_request')->enableNativeCast();
    }

    /**
     * Relation with the categories.
     */
    protected function photos(): RelationInterface
    {
        return $this->hasMany(B2b_Request_Photo_Model::class, 'request_id')->enableNativeCast();
    }

    /**
     * Resolves static relationships with partner type.
     */
    protected function partnerType(): RelationInterface
    {
        $partnersTypeModel = $this->b2bModel->getB2bPartnersTypeTable();
        $relation = $this->belongsTo($partnersTypeModel, 'id_type', 'id_type');
        $relation->disableNativeCast();
        $relation->getQuery();

        return $relation;
    }
    /**
     * Resolves static relationships with users
     */
    protected function users(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user', 'idu');
    }

    /**
     * Scope for join with users.
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $users */
        $users = model(Users_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $users->getTable(),
                $users->getTable(),
                "`{$users->getTable()}`.`idu` = {$this->qualifyColumn('id_user')}"
            );
    }
    /**
     * This method is intended for getting data from Mysql.
     *
     * @param array $params
     */
    private function getB2bFromMySql(array $params = null): array
    {
        $dataForElasticsearch = [];

        $conditions = array_filter(
            [
                'id'                => $params['id'] ?? null,
                'companyId'         => $params['companyId'] ?? null,
                'userId'            => $params['userId'] ?? null,
                'companyBlocked'    => 0,
                'companyVisible'    => 1,
                'activeB2bRequest'  => 1,
                'fakeUser'          => 0,
                'b2bRequestStatus'  => 'enabled',
                'b2bRequestBlocked' => 0,
            ],
            fn ($value) => null !== $value
        );

        $columns = [
            $this->getTable() . '.*',
            "COUNT(`{$this->b2bModel->getB2bAdvicesTable()}`.`{$this->b2bModel->getB2bAdvicesPrimaryKey()}`) as countAdvices"
        ];

        $joins = ['advices', 'company', 'users'];
        $with = ['company', 'countries', 'industries', 'categories', 'photos', 'partnerType'];
        $group = ["`{$this->getTable()}`.`{$this->getPrimaryKey()}`"];

        $b2bRequests = $this->findAllBy(compact('with', 'conditions', 'joins', 'columns', 'group'));

        /** @var Categories_Model $categoriesModel */
        $categoriesModel = model(Categories_Model::class);
        $allCategories = array_column($categoriesModel->findAll(), null, 'category_id');

        foreach ($b2bRequests as $b2bRequest) {
            $request = [
                'id'            => $b2bRequest['id_request'],
                'companyId'     => $b2bRequest['id_company'],
                'userId'        => $b2bRequest['id_user'],
                'radius'        => $b2bRequest['b2b_radius'],
                'title'         => $b2bRequest['b2b_title'],
                'message'       => $b2bRequest['b2b_message'],
                'type_location' => $b2bRequest['type_location'],
                'tags'          => empty($b2bRequest['b2b_tags']) ? [] : array_values(array_filter(array_map('trim', explode(';', $b2bRequest['b2b_tags'])))),
                'registerDate'  => $b2bRequest['b2b_date_register']->format('Y-m-d H:i:s'),
                'updateDate'    => $b2bRequest['b2b_date_update']->format('Y-m-d H:i:s'),
                'categories'    => [],
                'industries'    => [],
                'countViews'    => $b2bRequest['viewed_count'],
                'countAdvices'  => (int) $b2bRequest['countAdvices'],
                'countries'     => [],
                'company'       => [
                    'id'            => (int) $b2bRequest['company']['id_company'],
                    'displayedName' => $b2bRequest['company']['name_company'],
                    'legalName'     => $b2bRequest['company']['legal_name_company'],
                    'indexName'     => $b2bRequest['company']['index_name'],
                    'type'          => $b2bRequest['company']['type_company'],
                    'latitude'      => $b2bRequest['company']['latitude'],
                    'longitude'     => $b2bRequest['company']['longitude'],
                    'parent'        => (int) $b2bRequest['company']['parent_company'],
                    'logo'          => $b2bRequest['company']['logo_company'],
                    'address'       => $b2bRequest['company']['address_company'],
                    'zip'           => $b2bRequest['company']['zip_company'],
                    'country'       => [
                        'id'    => (int) $b2bRequest['company']['id_country'],
                        'name'  => $b2bRequest['company']['country'],
                        'slug'  => $b2bRequest['company']['country_alias'],
                    ],
                    'state'         => [
                        'id'    => (int) $b2bRequest['company']['id_state'],
                        'name'  => $b2bRequest['company']['state_name'],
                    ],
                    'city'          => [
                        'id'    => (int) $b2bRequest['company']['id_city'],
                        'name'  => $b2bRequest['company']['city'],
                    ],
                ],
                'partnerType'   => [
                    'id'    => (int) $b2bRequest['partner_type']['id_type'],
                    'name'  => $b2bRequest['partner_type']['name'],
                ],
            ];
            //region categories and industries
            $categories = empty($b2bRequest['categories']) ? [] : $b2bRequest['categories']->toArray();
            foreach ($categories as $category) {
                $request['categories'][] = [
                    'id'            => (int) $category['id_category'],
                    'name'          => $allCategories[$category['id_category']]['name'],
                    'parent'        => (int) $allCategories[$category['id_category']]['parent'],
                    'industryId'    => (int) $allCategories[$category['id_category']]['industry_id'],
                ];
            }

            $industries = empty($b2bRequest['industries']) ? [] : $b2bRequest['industries']->toArray();
            foreach ($industries as $industry) {
                $request['industries'][] = [
                    'id'            => (int) $industry['id_industry'],
                    'name'          => $allCategories[$industry['id_industry']]['name'],
                ];
            }
            //endregion categories and industries
            //get countries
            $countries = empty($b2bRequest['countries']) ? [] : $b2bRequest['countries']->toArray();
            foreach ($countries as $country) {
                $request['countries'][] = [
                    'id'            => (int) $country['id'],
                    'name'          => $country['country'],
                ];
            }
            //get photos
            $photos = empty($b2bRequest['photos']) ? [] : $b2bRequest['photos']->toArray();
            foreach ($photos as $photo) {
                if ($photo['is_main']) {
                    $request['main_image'] = $photo['photo'];
                } else {
                    $request['photos'][] = ['name' => $photo['photo']];
                }
            }
            $dataForElasticsearch[] = $request;
        }

        return $dataForElasticsearch;
    }

    private function showIndexingStatus($done, $total, $size = 50)
    {
        // if we go over our bound, just ignore it
        if ($done > $total || $done <= 0) {
            return;
        }

        static $startTime;
        $startTime = $startTime ?: microtime(true);

        $percent = (float) ($done / $total);
        $disp = number_format($percent * 100, 0);

        echo "\r {$done}/{$total} {$disp}% " . number_format(microtime(true) - $startTime, 2) . ' sec';

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
        }
    }

    //endregion belongs
}

// End of file elasticsearch_b2b_model.php
// Location: /tinymvc/myapp/models/elasticsearch_b2b_model.php

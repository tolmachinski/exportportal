<?php

use App\Common\Database\Model;
use App\Common\Exceptions\Items\ItemNotFoundException;
use App\Common\Transformers\ItemsToElasticsearchTransformer;
use Spatie\Fractalistic\Fractal;

class Elasticsearch_Items_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    public $items = [];

    /**
     * {@inheritdoc}
     * @deprecated use $items
     */
    public $items_records = [];

    /**
     * {@inheritdoc}
     */
    public $itemsCount = 0;

    /**
     * {@inheritdoc}
     *  @deprecated use $itemsCount
     */
    public $items_count = 0;

    /**
     * {@inheritdoc}
     */
    public $aggregates = [];

    /**
     * {@inheritdoc}
     */
    protected string $table = 'items';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEMS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * @var TinyMVC_Library_Elasticsearch
     */
    protected $elasticsearchLibrary;

    /**
     * {@inheritdoc}
     */
    private $type = 'items';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * Increment item views.
     */
    public function incrementViews(int $id): void
    {
        $query = [
            'term' => [
                'id' => $id,
            ],
        ];

        $script = 'ctx._source.views++';

        $this->elasticsearchLibrary->update_by_query($query, $script, $this->type);
    }

    /**
     * Change item quantity.
     *
     * @param mixed $id
     */
    public function changeQuantity($id = 0, int $newQuantity = 0): void
    {
        $script_array = [
            'inline'    => 'ctx._source.quantity = params.value',
            'lang'      => 'painless',
            'params'    => [
                'value' => $newQuantity,
            ],
        ];

        $this->elasticsearchLibrary->update_by_script($this->type, $id, $script_array);
    }

    /**
     * Get items from elasticsearch.
     *
     * @param mixed $conditions
     */
    public function get_items($conditions = []): ?array
    {
        $must = [];
        $filterMustNot = [];
        $page = 0;

        extract($conditions);

        $filterMust = $this->getFiltersMust($conditions);

        $perPage = $per_p ?? 20;

        if (isset($list_exclude_item)) {
            $filterMustNot[] = $this->elasticsearchLibrary->get_terms('id', $list_exclude_item);
        }

        if (isset($list_exclude_seller)) {
            $filterMustNot[] = $this->elasticsearchLibrary->get_match('id_seller', $list_exclude_seller);
        }

        if (isset($keywords)) {
            $must[] = array_merge_recursive(
                // $this->elasticsearchLibrary->boostQuery('title', $keywords, 5),
                // $this->elasticsearchLibrary->boostQuery('tags', $keywords, 1),
                // $this->elasticsearchLibrary->boostQuery('description', $keywords, 1),
                $this->elasticsearchLibrary->get_multi_match(
                    [
                        'title',
                        'tags',
                        'description',
                    ],
                    $keywords,
                    'best_fields'
                ),
                [
                    'multi_match' => [
                        'minimum_should_match' => '1',
                    ],
                ]
            );
        }

        $sort = [];
        if (isset($sort_by)) {
            foreach ($sort_by as $sortByElement) {
                $sortByComponents = explode('-', strtolower($sortByElement));
                if (2 == count($sortByComponents) && ('asc' === $sortByComponents[1] || 'desc' === $sortByComponents[1])) {
                    $sort[] = [
                        "{$sortByComponents[0]}" => $sortByComponents[1],
                    ];
                }
            }
        }

        if (isset($featured_order)) {
            $sort[] = [
                'featured' => ($featured_order ? 'desc' : 'asc'),
            ];
        }

        $sort[] = '_score';

        if (isset($random_score)) {
            $must[] = [
                'match_all' => new stdClass(),
            ];

            $elasticQuery = [
                'query' => [
                    'function_score' => [
                        'query' => [
                            'bool' => [
                                'must'   => $must,
                                'filter' => [
                                    'bool' => [
                                        'must'     => $filterMust,
                                        'must_not' => $filterMustNot,
                                    ],
                                ],
                            ],
                        ],
                        'functions' => [
                            [
                                'random_score' => new stdClass(),
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            $elasticQuery = [
                'query' => [
                    'bool' => [
                        'must'   => $must,
                        'filter' => [
                            'bool' => [
                                'must'     => $filterMust,
                                'must_not' => $filterMustNot,
                            ],
                        ],
                    ],
                ],
                'sort' => $sort,
            ];
        }

        if (isset($collapse_by_seller)) {
            $elasticQuery['collapse'] = [
                'field' => 'id_seller',
            ];
        }

        $aggregates = $this->getFiltersAggregation($conditions);
        if (!empty($aggregates)) {
            $elasticQuery['aggs'] = $aggregates;
        }

        if (isset($start, $limit)) {
            $elasticQuery['size'] = $limit;
            $elasticQuery['from'] = $start;
        } else {
            $elasticQuery['size'] = $perPage;
            $elasticQuery['from'] = $perPage * ($page > 0 ? ($page - 1) : $page);
        }

        
        $melasticResults = $this->elasticsearchLibrary->mget($this->type, [$elasticQuery]);

        $elasticResults = $melasticResults['responses'][0];
        if (isset($elasticResults['hits']['hits'])) {
            $this->items = $this->items_records = array_map(function ($hit) {
                $item = $hit['_source'];

                $item['tags'] = implode(',', $item['tags'] ?: []);
                $item['item_attr_select'] = implode(',', $item['item_attr_select'] ?: []);
                $item['item_categories'] = implode(',', $item['item_categories'] ?: []);
 
                return $item;
            }, $elasticResults['hits']['hits']);

            $this->itemsCount = $this->items_count = $elasticResults['hits']['total']['value'];
        }

        $this->setAggregations($conditions, $melasticResults);

        return $this->items;
    }

    /**
     * Completion suggester.
     */
    public function getSuggestions(string $text): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
        $melasticResults = $elasticsearchLibrary->mget('items', [
            [
                '_source' => 'suggest',
                'suggest' => [
                    'item-suggest' => [
                        'text'       => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => 20,
                            'skip_duplicates' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $rezult = $melasticResults['responses'][0]['suggest']['item-suggest'][0];
        $rezult['options'] = array_slice(
            $melasticResults['responses'][0]['suggest']['item-suggest'][0]['options'],
            0,
            config('item_suggestion_search_size', 5)
        );

        return $rezult;
    }

    /**
     * Get Count Items By Seller.
     */
    public function getCountItemsBySeller(array $conditions = []): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);

        if (!empty($conditions['industries']) && is_array($conditions['industries'])) {
            foreach ($conditions['industries'] as $industryId) {
                $should[] = $elasticsearchLibrary->get_term('industryId', $industryId);
            }
        }

        $elasticQuery = [
            'query' => [
                'bool' => [
                    'should' => $should,
                ],
            ],
            'aggs'  => [
                'uniqueSellers' => [
                    'terms' => [
                        'field' => 'id_seller',
                        'size'  => 10000,
                        'order' => [
                            '_count' => 'desc',
                        ]
                    ]
                ]
            ]
        ];

        $elasticResults = $elasticsearchLibrary->get($this->type, $elasticQuery);

        return empty(
            $elasticResults['aggregations']['uniqueSellers']['buckets']
        ) ? [] :
        array_column($elasticResults['aggregations']['uniqueSellers']['buckets'], 'doc_count', 'key');
    }

    /**
     * Delete item from elasticsearch.
     *
     * @param mixed $id
     */
    public function deleteItemFromIndex($id): void
    {
        if (is_array($id) || is_numeric($id) && $id > 0) {
            $this->elasticsearchLibrary->delete_by_query($this->type, [
                'term' => [
                    'id' => $id,
                ],
            ]);
        }
    }

    /**
     * The method created for removing an item from ElasticSearch by the item id.
     */
    public function removeItemById(int $itemId): bool
    {
        $curlResult = $this->elasticsearchLibrary->deleteById($this->type, $itemId);

        return 'deleted' === $curlResult['result'];
    }

    /**
     * Index item.
     *
     * @param null|mixed $id
     */
    public function index($id = null)
    {
        /** @var Products_Model $productsModel */
        $productsModel = model(\Products_Model::class);
        $items = Fractal::create()
            ->collection(
                $productsModel->getItemsForElastic($id)
            )
            ->transformWith(new ItemsToElasticsearchTransformer(
                library(\TinyMVC_Library_Elasticsearch::class),
                model(\Items_Variants_Model::class)
            ))
            ->toArray()['data']
        ;

        $countRows = count($items);
        $countIndexed = 0;

        $chunkedItemRequests = array_chunk($items, 500);
        foreach ($chunkedItemRequests as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $item) {
                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $item['id'],
                        $item
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);

            if ('cli' === PHP_SAPI) {
                $countIndexed += count($chunk);
                $this->showIndexingStatus($countIndexed, $countRows);
            }
        }
    }

    /**
     * Update item from DB in ElasticSearch.
     */
    public function update(int $id, array $updateData = []): bool
    {
        if (empty($updateData)) {
            return false;
        }

        $request = $this->elasticsearchLibrary->update($this->type, $id, $updateData);
        if (!empty($request) && 404 === $request['status']) {
            throw new ItemNotFoundException($id);
        }

        return empty($request['error'] ?: []);
    }

    /**
     * Get filters for request.
     */
    private function getFiltersMust(array $conditions = []): array
    {
        $filters = [];

        extract($conditions);

        if (isset($notOutOfStock)) {
            $filters[] = $this->elasticsearchLibrary->get_term('is_out_of_stock', 0);
        }

        if (isset($accreditation)) {
            $filters[] = $this->elasticsearchLibrary->get_match('accreditation', $accreditation);
        }

        if (isset($seller)) {
            $filters[] = $this->elasticsearchLibrary->get_match('id_seller', $seller);
        }

        if (isset($list_item)) {
            $filters[] = $this->elasticsearchLibrary->get_terms('id', $list_item);
        }

        if (!empty($itemsIndustries)) {
            $filters[] = $this->elasticsearchLibrary->get_terms('industryId', $itemsIndustries);
        }

        if (isset($category)) {
            $filters[] = $this->elasticsearchLibrary->get_match('item_categories', $category);
        }

        if (isset($categories)) {
            $filters[] = $this->elasticsearchLibrary->get_terms('item_categories', $categories);
        }

        if (isset($company)) {
            $filters[] = $this->elasticsearchLibrary->get_match('id_company', $company);
        }

        if (isset($country)) {
            $filters[] = $this->elasticsearchLibrary->get_match('p_country', $country);
        }

        if (isset($city)) {
            $filters[] = $this->elasticsearchLibrary->get_match('p_city', $city);
        }

        if (isset($handmade)) {
            $filters[] = $this->elasticsearchLibrary->get_match('is_handmade', $handmade);
        }

        if (isset($price_from)) {
            $filters[] = [
                'range' => [
                    'final_price' => [
                        'gte' => $price_from,
                    ]
                ]
            ];
        }

        if (isset($price_to)) {
            $filters[] = [
                'range' => [
                    'final_price' => [
                        'lte' => $price_to,
                    ]
                ]
            ];
        }

        if (isset($year_from)) {
            $filters[] = [
                'range' => [
                    'year' => [
                        'gte' => $year_from,
                    ]
                ]
            ];
        }

        if (isset($year_to)) {
            $filters[] = [
                'range' => [
                    'year' => [
                        'lte' => $year_to,
                    ]
                ]
            ];
        }

        if (isset($samples)) {
            $filters[] = $this->elasticsearchLibrary->get_term('samples', $samples);
        }

        if (isset($featured)) {
            $filters[] = $this->elasticsearchLibrary->get_term('featured', (int) $featured);
        }

        if (isset($is_restricted)) {
            $filters[] = $this->elasticsearchLibrary->get_term('is_restricted', 0);
        }

        if (isset($highlight)) {
            $filters[] = $this->elasticsearchLibrary->get_match('highlight', $highlight);
        }

        if (isset($partnered_item)) {
            $filters[] = $this->elasticsearchLibrary->get_match('is_partners_item', $partnered_item);
        }

        if (isset($start_item)) {
            $filters[] = [
                'range' => [
                    'id' => [
                        'gt' => $start_item,
                    ]
                ]
            ];
        }

        if (isset($motor)) {
            $filters[] = $this->elasticsearchLibrary->get_match('p_or_m', $motor);
        }

        if (isset($start_from)) {
            $filters[] = [
                'range' => [
                    'create_date' => [
                        'gte' => $start_from,
                    ],
                ],
            ];
        }

        if (isset($start_to)) {
            $filters[] = [
                'range' => [
                    'create_date' => [
                        'lte'    => $start_to,
                        'format' => 'yyyy-MM',
                    ]
                ]
            ];
        }

        if (isset($end_from)) {
            $filters[] = [
                'range' => [
                    'expire_date' => [
                        'lte'    => $end_from,
                        'format' => 'yyyy-MM',
                    ]
                ]
            ];
        }

        if (isset($end_to)) {
            $filters[] = [
                'range' => [
                    'expire_date' => [
                        'gte'    => $end_to,
                        'format' => 'yyyy-MM',
                    ]
                ]
            ];
        }

        if (isset($update_from)) {
            $filters[] = [
                'range' => [
                    'update_date' => [
                        'lte'    => $update_from,
                        'format' => 'yyyy-MM',
                    ]
                ]
            ];
        }

        if (isset($update_to)) {
            $filters[] = [
                'range' => [
                    'update_date' => [
                        'gte'    => $update_to,
                        'format' => 'yyyy-MM',
                    ]
                ]
            ];
        }

        return $filters;
    }

    /**
     * Return filters aggregations.
     */
    private function getFiltersAggregation(array $conditions = []): array
    {
        $aggregates = [];

        extract($conditions);

        if (isset($aggregate_category_counters)) {
            if (isset($aggregate_id_category)) {
                $aggregates['categories'] = [
                    'terms'   => [
                        'field'     => 'item_categories.path',
                        'include'   => "(.*\\,)?{$aggregate_id_category}(\\,[0-9]+){1}",
                        'size'      => 100,
                    ]
                ];
            } else {
                $aggregates['categories'] = [
                    'terms' => [
                        'field' => 'item_categories.path',
                        'size'  => 10000,
                    ]
                ];
            }
        }

        if (isset($aggregate_countries_counters)) {
            $aggregates['countries'] = [
                'terms' => [
                    'field' => 'p_country',
                    'size'  => 250,
                ]
            ];
        }

        if (isset($aggregate_cities_counters)) {
            $aggregates['cities'] = [
                'terms' => [
                    'field' => 'p_city',
                    'size'  => 1000,
                ]
            ];
        }

        if (isset($aggregate_attrs_select)) {
            $aggregates['attrs_select'] = [
                'terms' => [
                    'field'   => 'item_attr_select',
                    'size'    => 10000,
                    'exclude'   => (!empty($exclude_attrs_agregate)) ? "attr_(".implode('|', $exclude_attrs_agregate) . ")_.*" : ""
                ]
            ];
        }

        return $aggregates;
    }

    /**
     * Set aggregations data from elasticsearch response.
     */
    private function setAggregations(array $conditions = [], array $data = []): void
    {
        extract($conditions);

        if (isset($aggregate_category_counters)) {
            $this->aggregates['categories'] = [];
            if (!empty($data['responses'][0]['aggregations']['categories']['buckets'])) {
                if (isset($aggregate_id_category) || isset($aggregate_category_by_id)) {
                    foreach ($data['responses'][0]['aggregations']['categories']['buckets'] as $aggregationCategory) {
                        $categoryKeys = array_filter(explode(',', $aggregationCategory['key']));
                        if (!empty($categoryKeys)) {
                            $categoryKey = end($categoryKeys);
                            $this->aggregates['categories'][$categoryKey] = $aggregationCategory['doc_count'];
                        }
                    }
                } else {
                    foreach ($data['responses'][0]['aggregations']['categories']['buckets'] as $aggregationCategory) {
                        $this->aggregates['categories'][$aggregationCategory['key']] = $aggregationCategory['doc_count'];
                    }
                }
            }
        }

        if (isset($aggregate_countries_counters)) {
            $this->aggregates['countries'] = [];
            if (!empty($data['responses'][0]['aggregations']['countries']['buckets'])) {
                foreach ($data['responses'][0]['aggregations']['countries']['buckets'] as $aggregationCountry) {
                    $this->aggregates['countries'][$aggregationCountry['key']] = $aggregationCountry['doc_count'];
                }
            }
        }

        if (isset($aggregate_cities_counters)) {
            $this->aggregates['cities'] = [];
            if (!empty($data['responses'][0]['aggregations']['cities']['buckets'])) {
                foreach ($data['responses'][0]['aggregations']['cities']['buckets'] as $aggregationCity) {
                    $this->aggregates['cities'][$aggregationCity['key']] = $aggregationCity['doc_count'];
                }
            }
        }

        if (isset($aggregate_attrs_select)) {
            $this->aggregates['attrs_select'] = [];
            $this->aggregates['attrs_select_list'] = [];
            $this->aggregates['attrs_select_values'] = [];

            if (!empty($data['responses'][0]['aggregations']['attrs_select']['buckets'])) {
                foreach ($data['responses'][0]['aggregations']['attrs_select']['buckets'] as $aggregationAttrsSelect) {
                    $attrComponent = explode('_', $aggregationAttrsSelect['key']);
                    $this->aggregates['attrs_select'][$aggregationAttrsSelect['key']] = $aggregationAttrsSelect['doc_count'];
                    $this->aggregates['attrs_select_list'][$attrComponent[1]] = $attrComponent[1];
                    $this->aggregates['attrs_select_values'][$attrComponent[3]] = $attrComponent[3];
                }
            }

            $totalResults = !empty($data['responses']) ? count($data['responses']) : 0;
            for ($i = 1; $i < $totalResults; ++$i) {
                foreach ($data['responses'][$i]['aggregations']['attr_counter']['buckets'] as $aggregationAttrsSelect) {
                    $attrComponent = explode('_', $aggregationAttrsSelect['key']);
                    $this->aggregates['attrs_select'][$aggregationAttrsSelect['key']] = $aggregationAttrsSelect['doc_count'];
                    $this->aggregates['attrs_select_list'][$attrComponent[1]] = $attrComponent[1];
                    $this->aggregates['attrs_select_values'][$attrComponent[3]] = $attrComponent[3];
                }
            }
        }
    }

    /**
     * It prints a progress bar to the console.
     *
     * @param done the number of items that have been indexed
     * @param total the total number of items to be indexed
     * @param mixed $done
     * @param mixed $total
     *
     * @return the number of words in the string
     */
    private function showIndexingStatus($done, $total)
    {
        if ($done > $total || $done <= 0) {
            return;
        }

        static $startTime;
        $startTime = $startTime ?: microtime(true);

        $percent = (float) ($done / $total);
        $disp = number_format($percent * 100, 0);

        echo "\r {$done}/{$total} {$disp}% " . number_format(microtime(true) - $startTime, 2) . ' sec';

        flush();

        if ($done == $total) {
            echo "\n";
        }
    }
}

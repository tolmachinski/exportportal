<?php

class Elasticsearch_Faq_Model extends TinyMVC_Model
{
    public $faq_records = [];
    public $faq_count = 0;
    public $aggregates = [];
    private $type = 'faq';
    private $faq_table = 'faq';
    private $faq_primary_key = 'id_faq';
    private $faq_i18n_table = 'faq_i18n';
    private $faq_tags_relations_table = 'faq_tags_relation';
    private $faq_i18n_primary_key = 'id_faq_i18n';

    /** @var TinyMVC_Library_Elasticsearch */
    private $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    public function index()
    {
        $faq = $this->_get_data();
        $chunckedFaq = array_chunk($faq, 1000);

        foreach ($chunckedFaq as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $faq) {
                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $faq['id_faq'],
                        $faq
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);
        }
    }

    public function get_faq_list($conditions)
    {
        $page = 1;
        $per_p = 100;
        $sort = [];
        $lang = __SITE_LANG;

        extract($conditions);

        $filter_must = [];
        $should = [];
        $must = [];
        $must_not = [];

        if (isset($keywords)) {
            if ('en' === $lang) {
                $filter_must[] = [
                    'bool' => [
                        'should' => [
                            $this->elasticsearchLibrary->get_match('question', $keywords),
                            $this->elasticsearchLibrary->get_match('answer', $keywords),
                            $this->elasticsearchLibrary->get_nested(
                                'tags',
                                [
                                    'bool'  => [
                                        'should'  => $this->elasticsearchLibrary->get_match('tags.name', $keywords),
                                    ],
                                ]
                            ),
                        ],
                    ],
                ];
            } else {
                $filter_must[] = [
                    'bool' => [
                        'should' => [
                            $this->elasticsearchLibrary->get_match("faq_i18n.{$lang}.question", $keywords),
                            $this->elasticsearchLibrary->get_match("faq_i18n.{$lang}.answer", $keywords),
                            $this->elasticsearchLibrary->get_nested(
                                'tags',
                                [
                                    'bool'  => [
                                        'should'  => $this->elasticsearchLibrary->get_match('tags.name', $keywords),
                                    ],
                                ]
                            ),
                        ],
                    ],
                ];
            }
        }

        if (isset($id_tag)) {
            $nested_query = [
                'bool' => [
                    'filter' => [
                        $this->elasticsearchLibrary->get_term('tags.id', $id_tag),
                    ],
                ],
            ];
            $must[] = $this->elasticsearchLibrary->get_nested('tags', $nested_query);
        }

        if (!empty($tagsIds)) {
            $must[] = $this->elasticsearchLibrary->get_nested(
                'tags',
                [
                    'bool' => [
                        'filter' => [
                            $this->elasticsearchLibrary->get_terms('tags.id', $tagsIds),
                        ],
                    ],
                ]
            );
        }

        $sort['weight'] = ['order' => 'desc', 'mode' => 'avg'];
        if (isset($order_by)) {
            $explode = explode('-', $order_by);
            $sort[$explode[0]] = $explode[1];
        }

        $elastic_query = [
            'query' => [
                'bool' => [
                    'must'     => $must,
                    'must_not' => $must_not,
                    'should'   => $should,
                    'filter'   => [
                        'bool' => [
                            'must' => $filter_must,
                        ],
                    ],
                ],
            ],
            'sort' => $sort,
        ];

        if (isset($start, $limit)) {
            $elastic_query['size'] = $limit;
            $elastic_query['from'] = $start;
        } else {
            $elastic_query['size'] = $per_p;
            $elastic_query['from'] = $per_p * ($page > 0 ? ($page - 1) : $page);
        }

        $full_queries = [
            $elastic_query,
        ];

        $full_queries[] = [
            'aggs' => [
                'tags' => [
                    'nested' => [
                        'path' => 'tags',
                    ],
                    'aggs' => [
                        'counters' => [
                            'terms' => [
                                'field' => 'tags.id',
                            ],
                        ],
                    ],
                ],
            ],
            'size' => 0,
        ];

        $melastic_results = $this->elasticsearchLibrary->mget($this->type, $full_queries);

        $elastic_results = arrayGet($melastic_results, 'responses.0', []);
        if (isset($elastic_results['hits']['hits'])) {
            if ('en' !== $lang && empty($this->faq_records)) {
                $conditions['lang'] = 'en';

                return $this->get_faq_list($conditions);
            }

            $this->faq_records = array_map(
                function ($ar) use ($lang) {
                    return 'en' === $lang ? $ar['_source'] : $ar['_source']['faq_i18n'][$lang];
                },
                $elastic_results['hits']['hits']
            );
            $this->faq_count = $elastic_results['hits']['total']['value'];
        }

        $elastic_results = arrayGet($melastic_results, 'responses.1', []);

        if (isset($elastic_results['aggregations']['tags']['counters']['buckets'])) {
            foreach ($elastic_results['aggregations']['tags']['counters']['buckets'] as $aggregation_tag) {
                $this->aggregates['tags'][$aggregation_tag['key']] = [
                    'counter' => $aggregation_tag['doc_count'],
                ];
            }
            $this->aggregates['tags']['total_faqs'] = $elastic_results['hits']['total']['value'];
        }

        return $this->faq_records;
    }

    public function sync($param)
    {
        if (empty($param)) {
            return false;
        }

        $data = $this->_get_data($param);

        if (is_array($param)) {
            foreach ($param as $id_faq) {
                $this->_sync($id_faq, $data[$id_faq]);
            }

            return true;
        }

        return $this->_sync($param, $data);
    }

    private function _sync($primary_key, $data = false)
    {
        /** @var ElasticSearch_Help_Model */
        $elasticsearchHelpModel = model(ElasticSearch_Help_Model::class);

        $elasticsearchHelpModel->syncItem((int) $primary_key, 'getFaqItems', !$data);

        if (!$data) {
            return $this->elasticsearchLibrary->delete($this->type, $primary_key);
        }

        $elastic_result = $this->elasticsearchLibrary->get_by_id($this->type, $primary_key);
        if (!$elastic_result['found']) {
            return $this->elasticsearchLibrary->index($this->type, $primary_key, $data);
        }

        return $this->elasticsearchLibrary->update($this->type, $primary_key, $data);
    }

    private function _get_data($param = null)
    {
        $result = [];
        $id_faq = 0;
        $ids_faq = [];

        if (is_array($param)) {
            $ids_faq = $param;
        } else {
            $id_faq = $param;
        }

        $this->db->select('
            f.id_faq,
            f.question,
            f.answer,
            f.weight
        ');
        $this->db->from("{$this->faq_table} f ");
        if (!empty($id_faq)) {
            $this->db->where($this->faq_primary_key, $id_faq);
        } elseif (!empty($ids_faq)) {
            $this->db->in($this->faq_primary_key, $ids_faq);
        }
        $faq = $this->db->query_all();

        $this->db->select('
            f_i18n.id_faq,
            f_i18n.question,
            f_i18n.answer,
            f_i18n.lang_faq
        ');
        $this->db->from("{$this->faq_i18n_table} f_i18n");
        if (!empty($id_faq)) {
            $this->db->where($this->faq_primary_key, $id_faq);
        } elseif (!empty($ids_faq)) {
            $this->db->in($this->faq_primary_key, $ids_faq);
        }
        $faq_i18n = arrayByKey($this->db->query_all(), 'id_faq', true);

        $this->db->select('
            f_t_r.*,
            f_t.name, f_t.id_tag, f_t.slug, f_t.top_priority
        ');
        $this->db->from("{$this->faq_tags_relations_table} f_t_r");
        $this->db->join('faq_tags f_t', 'f_t_r.id_tag = f_t.id_tag', 'left');
        if (!empty($id_faq)) {
            $this->db->where($this->faq_primary_key, $id_faq);
        } elseif (!empty($ids_faq)) {
            $this->db->in($this->faq_primary_key, $ids_faq);
        }
        $faq_tags = arrayByKey($this->db->query_all(), 'id_faq', true);

        foreach ($faq as $row) {
            $row['faq_i18n'] = [];
            $i18n = isset($faq_i18n[$row['id_faq']]) ? arrayByKey($faq_i18n[$row['id_faq']], 'lang_faq') : [];

            foreach ($i18n as $lang => $i18n_row) {
                $row['faq_i18n'][$lang] = array_filter(
                    $i18n_row,
                    function ($k) {
                        return in_array($k, [
                            'question',
                            'answer',
                        ]);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }

            $row['tags'] = [];
            if (!empty($faq_tags[$row['id_faq']])) {
                foreach ($faq_tags[$row['id_faq']] as $tag) {
                    $row['tags'][] = [
                        'id'           => $tag['id_tag'],
                        'name'         => $tag['name'],
                        'slug'         => $tag['slug'],
                        'top_priority' => $tag['top_priority'],
                    ];
                }
            }

            if (!empty($id_faq)) {
                return $row;
            }

            $result[$row['id_faq']] = $row;
        }

        return $result;
    }
}

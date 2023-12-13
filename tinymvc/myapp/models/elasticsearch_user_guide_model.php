<?php

class Elasticsearch_User_Guide_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $type = "user_guide";
    private $user_guides_table = "user_guides";
    private $user_guides_relation_table = "user_guides_relation";
    private $user_guides_primary_key = "id_menu";
    public $user_guides_records = [];
    public $user_guides_count = 0;
    public $aggregates = [];

    /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
    private $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    public function index() {
        $userGuides = $this->_get_data();
        $chunckedUserGuides = array_chunk($userGuides, 1000);

        foreach ($chunckedUserGuides as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $userGuide) {
                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $userGuide['id_menu'],
                        $userGuide
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);
        }
    }

    public function get_user_guides($conditions){
        $page = 1;
        $per_p = 100;
        $sort = [];

        extract($conditions);

        $filter_must = [];

        $should = [];
        $must = [];

        if (isset($keywords)) {
            $must[] = $this->obj->elasticsearch->get_multi_match(
                array("menu_title.ngrams", "menu_description.ngrams"),
                $keywords,
                'most_fields'
            );
            $should[] = $this->obj->elasticsearch->get_multi_match(
                array("menu_intro.ngrams"),
                $keywords,
                'most_fields'
            );
        }

        if (isset($ids_user_guides)) {
            $filter_must[] = [
                "terms" => [
                    "id_menu" => $ids_user_guides
                ]
            ];
        }

        if (isset($onlyPublicGuides)) {
            $filter_must[] = [
                'bool'  => [
                    'should'    => [
                        $this->obj->elasticsearch->get_nested(
                            'rel_user_types',
                            [
                                'bool'  => [
                                    'must'  => $this->obj->elasticsearch->get_term('rel_user_types.buyer', 'buyer')
                                ]
                            ]
                        ),
                        $this->obj->elasticsearch->get_nested(
                            'rel_user_types',
                            [
                                'bool'  => [
                                    'must'  => $this->obj->elasticsearch->get_term('rel_user_types.seller', 'seller')
                                ]
                            ]
                        ),
                        $this->obj->elasticsearch->get_nested(
                            'rel_user_types',
                            [
                                'bool'  => [
                                    'must'  => $this->obj->elasticsearch->get_term('rel_user_types.shipper', 'shipper')
                                ]
                            ]
                        ),
                    ],
                ],
            ];
        }

        if (isset($order_by)) {
            $explode = explode("-", $order_by);
            $sort[$explode[0]] = $explode[1];
        }

        $elastic_query =  [
            "query" => [
                "bool" => [
                    "must" => $must,
                    "should" => $should,
                    "filter" => [
                        "bool" => [
                            "must" => $filter_must
                        ]
                    ]
                ]
            ],
            "aggs" => [
                "count_by_type" => [
                    "nested" => [
                        "path" => "rel_user_types"
                    ],
                    "aggs" => [
                        "sellers" => [
                            "filter" => [
                                "bool" => [
                                    "must" => [
                                        ["exists" => ["field" => "rel_user_types.seller"]]
                                    ]
                                ]
                            ]
                        ],
                        "buyers" => [
                            "filter" => [
                                "bool" => [
                                    "must" => [
                                        ["exists" => ["field" => "rel_user_types.buyer"]]
                                    ]
                                ]
                            ]
                        ],
                        "shippers" => [
                            "filter" => [
                                "bool" => [
                                    "must" => [
                                        ["exists" => ["field" => "rel_user_types.shipper"]]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            "sort" => $sort,
        ];

        if(isset($start, $limit)) {
            $elastic_query['size'] = $limit;
            $elastic_query['from'] = $start;
        } else {
            $elastic_query['size'] = $per_p ;
            $elastic_query['from'] = $per_p * ($page > 0 ? ($page - 1) : $page);
        }

        $elastic_results = $this->obj->elasticsearch->get($this->type, $elastic_query);

        if(isset($elastic_results['hits']['hits'])) {
            $this->user_guides_records = array_map(function($ar) {
                return $ar['_source'];
            }, $elastic_results['hits']['hits']);
            $this->user_guides_count = $elastic_results['hits']['total']['value'];
            $this->user_guides_count_by_type = [
                "buyers" => $elastic_results['aggregations']['count_by_type']['buyers']['doc_count'],
                "sellers" => $elastic_results['aggregations']['count_by_type']['sellers']['doc_count'],
                "shippers" => $elastic_results['aggregations']['count_by_type']['shippers']['doc_count'],
            ];
        }
    }

    public function sync($primary_key)
    {
        /** @var ElasticSearch_Help_Model $elasticsearchHelpModel */
        $elasticsearchHelpModel = model(ElasticSearch_Help_Model::class);

        $data = $this->_get_data($primary_key);

        if ( ! $data) {
            $elasticsearchHelpModel->syncItem((int) $primary_key, 'getUserGuidesItems', true);

            return $this->obj->elasticsearch->delete($this->type, $primary_key);
        }

        $elasticsearchHelpModel->syncItem((int) $primary_key, 'getUserGuidesItems', false);

        $elastic_result = $this->obj->elasticsearch->get_by_id($this->type, $primary_key);
        if ( ! $elastic_result['found']) {
            return $this->obj->elasticsearch->index($this->type, $primary_key, $data);
        }

        return $this->obj->elasticsearch->update($this->type, $primary_key, $data);
    }

    private function _get_data($id_menu = 0)
    {
        $this->db->from($this->user_guides_table . ' edm');

        if (!empty($id_menu)) {
            $this->db->where($this->user_guides_primary_key, $id_menu);
        }

        $user_guides = $this->db->get();

        if (!empty($id_menu)) {
            $this->db->where('rel_id_menu', $id_menu);
        }

        $user_guides_rel = arrayByKey($this->db->get('user_guides_relation'), 'rel_id_menu', true);

        foreach ($user_guides as &$user_guide) {
            $user_guide['rel_user_types'] = isset($user_guides_rel[$user_guide['id_menu']]) ? (object) array_column($user_guides_rel[$user_guide['id_menu']], 'rel_user_type', 'rel_user_type') : [];
        }

        return $id_menu ? arrayGet($user_guides, '0', array()) : $user_guides;
    }
}

<?php

class Elasticsearch_Topics_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $type = "topics";
    private $topics_table = "popular_topics";
    private $topics_primary_key = "id_topic";
    private $topics_i18n_table = "popular_topics_i18n";
    public $topics_records = [];
    public $topics_count = 0;
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
        $topics = $this->_get_data();
        $chunckedTopics = array_chunk($topics, 1000);

        foreach ($chunckedTopics as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $topic) {
                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $topic['id_topic'],
                        $topic
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);
        }
    }

    public function get_topics($conditions){
        $page = 1;
        $per_p = 10;
        $where = [];
        $params = [];
        $lang = __SITE_LANG;

        extract($conditions);

        $filter_must = [];
        $should = [];
        $must = [];
        $nested = [];

        if (isset($keywords)) {
            if ($lang === 'en') {
                $must[] = $this->obj->elasticsearch->get_multi_match(
                    array(
                        "title_topic.ngrams",
                        "text_topic_small.ngrams",
                        "text_topic.ngrams"
                    ),
                    $keywords,
                    "most_fields"
                );
            }
            else {
                $nested_query = [
                    "bool" => [
                        "must" => [
                            $this->obj->elasticsearch->get_multi_match(
                                array(
                                    "topics_i18n.{$lang}.title_topic",
                                    "topics_i18n.{$lang}.text_topic_small",
                                    "topics_i18n.{$lang}.text_topic"
                                ),
                                $keywords,
                                "most_fields"
                            )
                        ]
                    ]
                ];
                $must[] = $this->obj->elasticsearch->get_nested("topics_i18n", $nested_query);
            }
        }

        $sort = [];
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
            $this->topics_records = array_map(function($ar) use ($lang) {
                return $lang === 'en' ? $ar['_source'] : $ar['_source']['topics_i18n'][$lang];
            }, $elastic_results['hits']['hits']);
            $this->topics_count = $elastic_results['hits']['total']['value'];
        }
    }

    public function sync($primary_key)
    {
        /** @var ElasticSearch_Help_Model $elasticsearchHelpModel */
        $elasticsearchHelpModel = model(ElasticSearch_Help_Model::class);

        $data = $this->_get_data($primary_key);

        if (!$data) {
            $elasticsearchHelpModel->syncItem((int) $primary_key, 'getTopicsItems', true);

            return $this->obj->elasticsearch->delete($this->type, (int) $primary_key);
        }

        $elasticsearchHelpModel->syncItem((int) $primary_key, 'getTopicsItems', false);

        return $this->obj->elasticsearch->index($this->type, $primary_key, $data);
    }

    private function _get_data($id_topic = 0)
    {
        $result = [];
        $allowed_i18n_keys = [
            'title_topic',
            'text_topic_small',
            'text_topic',
        ];

        $this->db->select('t.id_topic, t.title_topic, t.text_topic_small, t.text_topic, t.date_topic');
        $this->db->from($this->topics_table . ' t');
        $this->db->where('t.visible_topic', 1);

        if (!empty($id_topic)) {
            $this->db->where($this->topics_primary_key, $id_topic);
        }

        $topics = $this->db->get();

        $this->db->select('t_i18n.id_topic, t_i18n.title_topic, t_i18n.text_topic_small, t_i18n.text_topic, t_i18n.lang_topic');
        $this->db->from($this->topics_i18n_table . ' t_i18n');

        if (!empty($id_topic)) {
            $this->db->where($this->topics_primary_key, $id_topic);
        }

        $topics_i18n = arrayByKey( $this->db->get(), 'id_topic', true);

        foreach($topics as &$row) {
            $row['topic_i18n'] = [];
            $i18n = isset($topics_i18n[$row['id_topic']]) ? arrayByKey($topics_i18n[$row['id_topic']], 'lang_topic') : [];

            foreach ($i18n as $lang => $i18n_row) {
                $row['topics_i18n'][$lang] = array_filter($i18n_row, function($k) use ($allowed_i18n_keys) {
                    return in_array($k, $allowed_i18n_keys);
                }, ARRAY_FILTER_USE_KEY);
            }

            if ($id_topic) {
                return $row;
            }

            $result[] = $row;
        }

        return $result;
    }
}

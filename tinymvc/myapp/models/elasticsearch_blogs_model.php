<?php

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;

class Elasticsearch_Blogs_Model extends TinyMVC_Model {

    private $type = "blogs";

    /**
     * @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary
     */
    protected $elasticsearchLibrary;

    public $records = [];
    public $records_total = 0;
    public $aggregates = [];

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        /** @var LibraryLocator */
        $libraryLocator = tmvc::instance()->getContainer()->get(LibraryLocator::class);
        $this->elasticsearchLibrary = $libraryLocator->get(\TinyMVC_Library_Elasticsearch::class);
    }

    public function index($id = 0) {
        $this->db->select("b.*, u.fname, u.lname, IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.name , bc.name) as category_name, IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.url , bc.url) as category_url, DATE_FORMAT(b.publish_on, '%m-%Y') as archive_date");
        $this->db->from('blogs b');
        $this->db->join('users u', 'b.id_user = u.idu', 'inner');
        $this->db->join('blogs_category bc', 'bc.id_category = b.id_category', 'inner');
        $this->db->join('blogs_category_i18n bc_i18n', 'bc.id_category = bc_i18n.id_category AND bc_i18n.lang_category = b.lang', 'left');
        $this->db->where('b.status', 'moderated');

        if (!empty($id)) {
            $this->db->where('b.id', $id);
        }

        $rows = $this->db->get();

        foreach($rows as $row) {
            $row['views'] = (int) $row['views'];
            $row['tags_uri'] = str_replace(' ', '_', strtolower($row['tags']));

            $row['suggest_autocomplete'][] = [
                'input' => htmlspecialchars_decode($row['title'], ENT_QUOTES),
                'weight' => 5
            ];

            $tokens = $this->elasticsearchLibrary->analyze($this->type, [
                'analyzer' => "autocomplete_shigle_analyzer",
                'text'     => htmlspecialchars_decode($row['title'], ENT_QUOTES),
            ]);

            foreach($tokens['tokens'] as $token) {
                $row['suggest_autocomplete'][] = [
                    'input' => $token['token'],
                    'weight' => 4,
                ];
            }

            $row['suggest_autocomplete'][] = [
                'input' => $row['category_name'],
                'weight' => 3,
            ];

            if (!empty($row['tags'])) {
                foreach (explode(',', $row['tags']) as $tag) {
                    $row['suggest_autocomplete'][] = [
                        'input' => $tag,
                        'weight' => 2,
                    ];
                }
            }

            $this->elasticsearchLibrary->index($this->type, $row['id'], $row);
        }
    }

    public function get_blogs($conditions) {
        $page = 0;
        $per_p = 20;
        $aggregate_archives_size = 5;
        $lang = __SITE_LANG;

        $filter_must = $filter_lang = [];
        $filter_should = [];
        $filter_must_not = [];
        $filter_visible = [];

        $must = [];
        $should = [];
        $must_not = [];

        extract($conditions);

        if (!isset($allLanguages)) {
            $filter_must[] = $filter_lang = $this->elasticsearchLibrary->get_term("lang", $lang);
        }

        $sort = array('publish_on' => 'desc');
		if(isset($sort_by)){
            $sort = [];
            require($_SERVER['DOCUMENT_ROOT']."/elasticsearch/mappings/blogs.php");
            $mapping_properties = $blogsMapping['properties'];

			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
                if($mapping_properties[$sort_item[0]]['type'] == "text") {
                    $sufix = ".keyword";
                } else {
                    $sufix = "";
                }

				$sort[] = array($sort_item[0].$sufix => $sort_item[1]);
			}
        }

        if(!empty($keywords)){
            if(empty($sort_by)){
                $sort = [];
            }

            $must[] = $this->elasticsearchLibrary->get_multi_match(array("title.ngrams^6", "short_description.ngrams", "content.ngrams", "tags^2", "category_name"), $keywords, "most_fields");
            $should[] = $this->elasticsearchLibrary->get_multi_match(array("title^6", "short_description", "content", "tags^2", "category_name"), $keywords, "most_fields");
        }

		if(isset($user)) {
            $filter_must[] = $this->elasticsearchLibrary->get_term("id_user", $user);
        }

		if(isset($author_type)) {
            $filter_must[] = $this->elasticsearchLibrary->get_term("author_type", $author_type);
        }

		if(isset($category)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("id_category", $category);
		}

		if(isset($country)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("id_country", $country);
		}

		if(isset($tags)){
            $filter_must[] = $this->elasticsearchLibrary->get_match("tags_uri", $tags);
		}

        if (isset($archived)) {
            list($month, $year) = explode('-', $archived);
            $filter_must[] = [
                'range' => [
                    'archive_date' => [
                        'format' => 'MM-yyyy',
                        'gte'    => sprintf('%s-%s', \str_pad($month, 2, '0', \STR_PAD_LEFT), \str_pad($year, 4, '0', \STR_PAD_LEFT)),
                        'lt'     => sprintf(
                            '%s-%s',
                            \str_pad(12 === (int) $month ? 1 : $month + 1, 2, '0', \STR_PAD_LEFT),
                            \str_pad(12 === (int) $month ? $year + 1 : $year, 4, '0', \STR_PAD_LEFT)
                        ),
                    ],
                ],
            ];
        }

		if(isset($status)){
            $filter_must[] = $this->elasticsearchLibrary->get_match("status", $status);
		}

		if(isset($start_from)){
            $filter_must[] = array(
                'range' => array(
                    "date" => array(
                        "gte" => $start_from
                    )
                )
            );
		}

		if(isset($start_to)){
            $filter_must[] = array(
                "range" => array(
                    "date" => array(
                        "lte" => $start_to
                    )
                )
            );
        }

        if(isset($published)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("published", $published);
        }

		if(isset($visible)){
            $filter_visible = $this->elasticsearchLibrary->get_term("visible", $visible);
        }
        $filter_must[] = $filter_visible;

		if(isset($id_blog)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("id", $id_blog);
		}

		if(isset($not_id_blog)){
            $filter_must_not[] = $this->elasticsearchLibrary->get_term("id", $not_id_blog);
        }

        $elastic_query = array(
            "query" => array(
                "bool" => array(
                    "must" => $must,
                    "should" => $should,
                    "must_not" => $must_not,
                    "filter" => array(
                        "bool" => array(
                            "must" => $filter_must,
                            "should" => $filter_should,
                            "must_not" => $filter_must_not,
                        )
                    )
                )
            ),
            "sort" => $sort
        );

        if(isset($start, $limit)) {
            $elastic_query['size'] = $limit;
            $elastic_query['from'] = $start;
        } else {
            $elastic_query['size'] = $per_p ;
            $elastic_query['from'] = $per_p * ($page > 0 ? ($page - 1) : $page);
        }

        $full_queries = array(
            $elastic_query
        );

        if(isset($aggregate_category_counters)){
            $full_queries[] = array(
                "query" => array(
                    "bool" => array(
                        "filter" => array(
                            "bool" => array(
                                "must" => array(
                                    $filter_lang,
                                    $filter_visible
                                )
                            )
                        )
                    )
                ),
                "aggs" => array(
                    "category_counter" => array(
                        "terms" => array(
                            "field" => "id_category"
                        )
                    )
                ),
                "size" => 0
            );
        }

        if(isset($aggregate_archives)){
            $full_queries[] = array(
                "query" => array(
                    "bool" => array(
                        "filter" => array(
                            "bool" => array(
                                "must" => array(
                                    $filter_lang,
                                    $filter_visible
                                )
                            )
                        )
                    )
                ),
                "aggs" => array(
                    "archives" => array(
                        "terms" => array(
                            "field" => "archive_date",
                            "order" => array(
                                "_term" => "desc"
                            ),
                            "size" => $aggregate_archives_size
                        )
                    )
                ),
                "size" => 0
            );
        }

        $melastic_results = $this->elasticsearchLibrary->mget($this->type, $full_queries);
        $elastic_results = !empty($melastic_results['responses']) ? array_shift($melastic_results['responses']) : [];
        if(isset($elastic_results['hits']['hits'])) {
            $this->records = array_map(function($ar) { return $ar['_source']; }, $elastic_results['hits']['hits']);
            $this->records_total = $elastic_results['hits']['total']['value'];
        }

        if(isset($aggregate_category_counters)){
            $results_category_counters = !empty($melastic_results['responses'])?array_shift($melastic_results['responses']):[];
            if(isset($results_category_counters['aggregations']['category_counter']['buckets'])){
                foreach($results_category_counters['aggregations']['category_counter']['buckets'] as $aggregation_category) {
                    $this->aggregates['category_counter'][] = array(
                        "id_category" => $aggregation_category['key'],
                        "counter" => $aggregation_category['doc_count']
                    );
                }
            }
        }

        if(isset($aggregate_archives)){
            $results_archives = !empty($melastic_results['responses'])?array_shift($melastic_results['responses']):[];
            if(isset($results_archives['aggregations']['archives']['buckets'])){
                foreach($results_archives['aggregations']['archives']['buckets'] as $aggregation_archive) {
                    $temp = explode('-', $aggregation_archive['key_as_string']);
                    $this->aggregates['archives'][$aggregation_archive['key_as_string']] = array(
                        "counter" => $aggregation_archive['doc_count'],
                        "blog_year" => $temp[1],
                        "blog_month" => $temp[0],
                        "month_name" => translate("calendar_m_{$temp[0]}")
                    );
                }
            }
        }

        return $this->records;
    }

    public function suggest_titles($needle) {
        $query = array(
            "multi_match" => array(
                "query" => trim($needle),
                "fields" => array("title^4", "content^2", "short_description^2", "title.ngrams", "content.ngrams^0.5", "short_description.ngrams^0.5"),
                "type" => "most_fields"
            )
        );

        $rez = $this->elasticsearchLibrary->get($this->type, array(
            "query" => $query,
            "_source" => array("title", "id")
        ));

        $hits = $rez['hits']['hits'];
        if(empty($hits)) return [];

        $output = [];
        foreach($hits as $hit) {
            $output[] = array(
                'title' => $hit["_source"]["title"],
                'id' => $hit["_source"]["id"]
            );
        }

        return $output;

    }

    public function update_category_blog($id_category = 0, $category_name = '', $lang_category = 'en') {
        $elastic_query = $this->elasticsearchLibrary->get_multi_term(array("id_category" => $id_category, "lang" => $lang_category));

        $this->elasticsearchLibrary->update_by_query($elastic_query, "ctx._source['category_name']='{$category_name}';", $this->type);
    }

    function delete($ids) {
        $this->elasticsearchLibrary->delete($this->type, $ids);
    }

    function change_published_status($id_blog = false) {
        $params = array(
            'published'     => 0,
            'publish_on'    => date('Y-m-d')
        );

        if($id_blog !== false){
            $params['id'] = (int) $id_blog;
        }

        $query = $this->elasticsearchLibrary->get_multi_term($params);

        $this->elasticsearchLibrary->update_by_query($query, "ctx._source.published = 1", $this->type);
    }

    function increment_blog_views($id) {
        $query = array(
            "term" => array(
                'id' => $id
            )
        );

        $this->elasticsearchLibrary->update_by_query($query, "ctx._source.views++", $this->type);
    }

    /**
     * @param array $conditions
     * @param int $limit
     *
     * @return array
     */
    public function getSmeSpotlightBlogs(array $conditions = [], int $limit = 3): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);

        $result = $elasticsearchLibrary->get(
            $this->type,
            [
                "query" => $elasticsearchLibrary->get_multi_term([
                    'id_category' => 20, //SME Spotlight
                    'visible'     => 1,
                    'status'      => 'moderated',
                    'published'   => "1",
                ]),
                "size"  => $limit,
                "sort"  => ['publish_on' => "desc"]
            ],
        );

        return array_column($result['hits']['hits'] ?? [], '_source');
    }

    public function getSuggestions (string $text): array
    {
        $melasticResults = $this->elasticsearchLibrary->mget('blogs', [
            [
                '_source' => 'suggest',
                'suggest' => [
                    'blog-suggest' => [
                        'text' => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => 20,
                            'skip_duplicates' => true,
                        ],
                    ],
                ],
            ]
        ]);

        $suggestions = $melasticResults['responses'][0]['suggest']['blog-suggest'][0];
        $rezult['options'] = array_slice(
            $melasticResults['responses'][0]['suggest']['blog-suggest'][0]['options'],
            0,
            config('blogs_suggestion_search_size', 5)
        );

        return (array) $suggestions;
    }
}

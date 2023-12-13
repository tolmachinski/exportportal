<?php
/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 */


class Elasticsearch_Category_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $type = "item_category";
    public $categories_records = null;

    /**
     * @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary
     */
    protected $elasticsearchLibrary;


    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    public function index() {
        $categories = $this->db->query_all('
            SELECT
                category_id,
                parent,
                name,
                breadcrumbs,
                cat_childrens,
                vin,
                is_restricted,
                REPLACE(keywords, "[LOCATION]", "") as keywords
            FROM item_category
        ');

        $chunkedCategories = array_chunk($categories, 1000);
        $countCategories = count($categories);
        $countIndexed = 0;

        foreach ($chunkedCategories as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $category) {
                $inputs = array_filter(array_map('trim', explode(',', $category['keywords'])));
                $categoryBreadcrumbs = json_decode('['. $category['breadcrumbs'] .']', true);
                $hasChildren = !empty(trim($category['cat_childrens']));

                if (!empty($hasChildren) && !empty($categoryBreadcrumbs)) {
                    $categoryBreadcrumbs = array_slice($categoryBreadcrumbs, -2);

                    $slugTitle = array_map(
                        function($breadcrumbRecord) {
                            return implode(' ', array_values($breadcrumbRecord));
                        },
                        $categoryBreadcrumbs
                    );

                    $slugTitle = implode(' ', $slugTitle);
                } else {
                    $slugTitle = $category['name'];
                }

                $category['breadcrumbs_data'] = [];
                if (!empty($category['breadcrumbs'])) {
                    $category['breadcrumbs_data'] = $this->prepareBreadcrumbs($category['breadcrumbs']);
                }

                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        (int) $category['category_id'],
                        [
                            'category_id'           => (int) $category['category_id'],
                            'parent'                => (int) $category['parent'],
                            'has_children'          => $hasChildren,
                            'has_vin'               => (bool) (int) $category['vin'],
                            'name'                  => $category['name'],
                            'name_to_lower'         => mb_strtolower($category['name']),
                            'slug'                  => strForURL($slugTitle) . '/' . $category['category_id'],
                            'breadcrumbs'           => $category['breadcrumbs'],
                            'breadcrumbs_data'      => $category['breadcrumbs_data'],
                            'completion'            => [
                                'input'             => array_values($inputs) ?? []
                            ],
                            'spellcheck'            => $category['keywords'],
                            'is_restricted'         => (int) $category['is_restricted'],
                            'suggest_autocomplete'  => [[
                                'input' => str_replace(['&amp;', '&'], 'and', $category['name']),
                                'weight' => 5,
                            ]]
                        ]
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);

            if ('cli' === PHP_SAPI) {
                $countIndexed += count($chunk);
                $this->show_status($countIndexed, $countCategories);
            }
        }
    }

    public function get_categories($conditions) {
        $filter_must = array();
        $must_not    = array();
        $should      = array();
        $sort        = array();
        $must        = array();
        $page        = 1;
        $per_p       = 10000;
        $lang        = __SITE_LANG;
        extract($conditions);

        if (isset($keywords)) {
            $must[] = $this->obj->elasticsearch->get_multi_match(
                array("name"),
                $keywords,
                'most_fields'
            );
            $should[] = $this->obj->elasticsearch->get_multi_match(
                array("spellcheck"),
                $keywords,
                'most_fields'
            );
        }

        if(isset($parent)){
            $filter_must[] = $this->obj->elasticsearch->get_terms("parent", $parent);
        }

        if(isset($has_children)){
            $filter_must[] = $this->obj->elasticsearch->get_terms("has_children", $has_children);
        }

        if(isset($sort_by)) {
            switch ($sort_by) {
                case 'name_asc': $sort[] = array( "name_to_lower" => "asc" ); break;
                case 'name_desc': $sort[] = array( "name_to_lower" => "desc" ); break;
                case 'relevance_desc': break;
            }
        }

        $elastic_query =  array(
            "query" => array(
                "bool" => array(
                    "must"   => $must,
                    "should" => $should,
                    "filter" => array(
                        "bool" => array(
                            "must" => $filter_must
                        )
                    )
                )
            ),
            "sort"  => $sort,
        );

        if(isset($start, $limit)) {
            $elastic_query['size'] = $limit;
            $elastic_query['from'] = $start;
        } else {
            $elastic_query['size'] = $per_p ;
            $elastic_query['from'] = $per_p * ($page > 0 ? ($page - 1) : $page);
        }

        $elastic_results = $this->obj->elasticsearch->get($this->type, $elastic_query);

        if(isset($elastic_results['hits']['hits'])) {
            $this->categories_records = array_map(function($ar) {
                return $ar['_source'];
            }, $elastic_results['hits']['hits']);

            $this->categories_count = $elastic_results['hits']['total']['value'];
        }
    }

    public function sync($primary_key)
    {
        if (!$data = $this->_get_data($primary_key)) {
            return $this->obj->elasticsearch->delete($this->type, $primary_key);
        }

        $elastic_result = $this->obj->elasticsearch->get_by_id($this->type, $primary_key);
        if (!$elastic_result['found']) {
            return $this->obj->elasticsearch->index($this->type, $primary_key, $data);
        }

        return $this->obj->elasticsearch->update($this->type, $primary_key, $data);
    }

    private function _get_data($category_id = 0)
    {
        $this->db->select('
            category_id,
            name,
            breadcrumbs,
            REPLACE(keywords, "[LOCATION]", "") as keywords
        ');
        $this->db->where('category_id', $category_id);
        $this->db->from('item_category');

        return $this->db->query_one();
    }

    private function show_status($done, $total, $size=30) {

        static $start_time;

        // if we go over our bound, just ignore it
        if($done > $total || $done <= 0) return;

        if(empty($start_time)) $start_time=time();
        $now = time();

        $perc=(double)($done/$total);

        $bar=floor($perc*$size);

        $status_bar="\r[";
        $status_bar.=str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="=";
        }

        $disp=number_format($perc*100, 0);

        $status_bar.="] $disp%  $done/$total";

        $rate = ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if($done == $total) {
            echo "\n";
        }

    }

    /**
     * Completion suggester
     *
     * @param string $text
     *
     * @return ?array
     */
    public function getSuggestions (string $text): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
        $melasticResults = $elasticsearchLibrary->mget('item_category', [
            [
                '_source' => [
                    'breadcrumbs_data'
                ],
                'suggest' => [
                    'categories-suggest' => [
                        'text' => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => config('categories_suggestion_search_size', 5),
                            'skip_duplicates' => true
                        ]
                    ]
                ]
            ]
        ]);

        return (array) $melasticResults['responses'][0]['suggest']['categories-suggest'][0];
    }

    /**
     * Prepare breadcrumbs for index into elasticsearch
     */
    private function prepareBreadcrumbs (string $breadcrumbs): array
    {
        /** @var \Categories_Model $categoriesModel */
        $categoriesModel = model(\Categories_Model::class);
        /** @var \Category_Model $categoryModel */
        $categoryModel = model(\Category_Model::class);
        $categories = array_map(fn ($item) => key($item), json_decode('[' . $breadcrumbs . ']', true));

        return with($categories, function ($items) use ($categoriesModel, $categoryModel) {
            $itemBreadcrumbsData = $categoriesModel->findAllBy(['scopes' => ['ids' => $items]]);
            return array_map(
                fn ($item) => [
                    'name'  => $item['name'],
                    'link'  => arrayPluck(
                        array_filter(
                            $categoryModel->breadcrumbs($item['category_id']),
                            function ($breadcrumbs) use ($item) {
                                if ($breadcrumbs['id_cat'] == $item['category_id']) {
                                    return $breadcrumbs['link'];
                                }
                            }
                        ),
                        'link',
                    )[0],
                ],
                $itemBreadcrumbsData
            );
        });
    }
}

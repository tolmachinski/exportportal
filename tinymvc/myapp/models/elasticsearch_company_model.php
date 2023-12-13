<?php
/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 */


class Elasticsearch_Company_Model extends TinyMVC_Model {

    // hold the current controller instance
    public $records = array();
    public $count = 0;
    public $aggregates = array();
    var $obj;
    private $type = "company";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
    }

    public function get_companies($conditions = array()) {
        $filter_must = array();

        $filter_industry = array();
        $filter_category = array();
        $filter_type = array();
        $filter_country = array();

        $query_must = array();
        $query_should = array();

        $aggregates = array();

        $sort = array();
        $from = 0;
        $per_p = 10;

        extract($conditions);

        if(isset($sort_by)) {
            switch ($sort_by) {
                case 'title_asc': $sort[] = array( "name_company.keyword" => "asc" ); break;
                case 'title_desc': $sort[] = array( "name_company.keyword" => "desc" ); break;
                case 'date_asc': $sort[] = array("registered_company" => "asc"); break;
                case 'date_desc': $sort[] = array("registered_company" => "desc"); break;
                case 'rating_asc': $sort[] = array("rating_company" => "asc"); break;
                case 'rating_desc': $sort[] = array("rating_company" => "desc"); break;
                case 'relevance_desc': break;
            }
        }

		if (isset($users_list)) {
            $filter_must[] = array(
                "terms" => array(
                    "id_user" => explode(",", $users_list)
                )
            );
		}

		if (isset($list_company_id)) {
            $filter_must[] = array(
                "terms" => array(
                    "id_company" => explode(",", $list_company_id)
                )
            );
		}

		if(isset($accreditation)){
            $filter_must[] = array(
                "term" => array(
                    "accreditation" => $accreditation
                )
            );
        }

		if(isset($no_accreditation)){
            $filter_must[] = array(
                "terms" => array(
                    "accreditation" => array(0,2)
                )
            );
        }

		if (isset($type)) {
            $filter_type[] = array(
                "term" => array(
                    "id_type" => $type
                )
            );
		}

		if (isset($industry)) {
            $filter_industry[] = $this->obj->elasticsearch->get_match("company_industries", $industry);
		}

		if (isset($category)) {
            $filter_category[] = $this->obj->elasticsearch->get_match("company_categories", $category);
		}

		if (isset($parent)) {
            $filter_must[] = array(
                "term" => array(
                    "parent_company" => $parent
                )
            );
		}

		if (isset($type_company) && $type_company != "all") {
            $filter_must[] = array(
                "term" => array(
                    "type_company" => $type_company
                )
            );
		}

		if (isset($country)) {
            $filter_country[] = array(
                "term" => array(
                    "id_country" => $country
                )
            );
		}

		if (isset($state)) {
            $filter_must[] = array(
                "term" => array(
                    "id_state" => $state
                )
            );
		}

		if (isset($city)) {
            $filter_must[] = array(
                "term" => array(
                    "id_city" => $city
                )
            );
		}

		if (isset($seller)) {
            $filter_must[] = array(
                "term" => array(
                    "id_user" => $seller
                )
            );
		}

		if (isset($user_status)) {
            $filter_must[] = array(
                "term" => array(
                    "status" => $user_status
                )
            );
        }

		if (isset($featured_company)) {
            $filter_must[] = array(
                "term" => array(
                    "is_featured" => $featured_company
                )
            );
		}

		if (isset($added_start)) {
            $filter_must[] = array(
                "range" => array(
                    "registered_company" => array(
                        "gte" >= $added_start
                    )
                )
            );
        }

		if (isset($added_finish)) {
            $filter_must[] = array(
                "range" => array(
                    "registered_company" => array(
                        "lte" => $added_finish
                    )
                )
            );
        }


        if (isset($last_indexed_from)) {
            $filter_must[] = array(
                "range" => array(
                    "last_indexed_date" => array(
                        "gte" >= $last_indexed_from
                    )
                )
            );
        }

        if (isset($last_indexed_to)) {
            $filter_must[] = array(
                "range" => array(
                    "last_indexed_date" => array(
                        "lte" => $last_indexed_to
                    )
                )
            );
		}

		if (isset($visibility)) {
            $filter_must[] = array(
                'term' => array(
                    'visible_company' => $visibility
                )
            );
		}

		if (isset($blocked)) {
            if($blocked == 0)  {
                $filter_must[] = array(
                    "term" => array(
                        "blocked" => 0
                    )
                );
            } else {
                $filter_must[] = array(
                    "range" => array(
                        "blocked" => array(
                            "gte" => 0
                        )
                    )
                );
            }
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $sort[] = array("{$sort_item[0]}" => $sort_item[1]);
            }
		}

		if (isset($keywords)) {
            $query_should[] = array(
                "multi_match" => array(
                    "query" => $keywords,
                    "fields" => array("name_company^2", "description_company"),
                    "type" => "most_fields"
                )
            );

            $query_must[] = array(
                "multi_match" => array(
                    "query" => $keywords,
                    "fields" => array("name_company.ngrams^2", "description_company.ngrams"),
                    "type" => "most_fields"
                )
            );
		}

		if (isset($company_name)){
            $query_should[] = array(
                "match" => array(
                    "name_company" => $company_name
                )
            );

            $query_must[] = array(
                "match" => array(
                    "name_company.ngrams" => $company_name
                )
            );
		}

		if (isset($page)) {
			$from = ($page - 1) * $per_p;

			if ($from < 0) $from = 0;
			if(isset($limit)) $per_p = $limit;
		} elseif(isset($start)) {
			$from = $start;

			if(isset($limit))
				$per_p = $limit;
		}

        $query_base = array(
            "query" => array(
                "bool" => array(
                    "should" => $query_should,
                    "must" => $query_must,
                    "filter" => array(
                        "bool" => array(
                            "must" => array_merge($filter_must, $filter_type, $filter_country, $filter_industry, $filter_category),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "size" => $per_p,
            "from" => $from,
        );

        // ADITIONAL QUERIES

        // 1 = countries_counters, industries_counters, categories_counters, types_counters
        $query_aditional[] = array(
            "query" => array(
                "bool" => array(
                    "filter" => array(
                        "bool" => array(
                            "should" => array(),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "aggs" => array(
                "countries" => array(
                    "terms" =>   array(
                        "field" =>  "id_country_country",
                        "size" => 250
                    )
                ),
                "industries" => array(
                    "terms" =>   array(
                        "field" =>  "company_industries",
                        "size" => 100
                    )
                ),
                "categories" => array(
                    "terms" =>   array(
                        "field" =>  "company_categories",
                        "size" => 10000
                    )
                ),
                "types" => array(
                    "terms" => array(
                        "field" => "id_type_name"
                    )
                ),
            ),
            "size" => 0
        );

        // 2 = countries_counters
        $query_aditional[] = array(
            "query" => array(
                "bool" => array(
                    "should" => $query_should,
                    "must" => $query_must,
                    "filter" => array(
                        "bool" => array(
                            "must" => array_merge($filter_must, $filter_type, $filter_industry, $filter_category),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "aggs" => array(
                "countries_counters" => array(
                    "terms" =>   array(
                        "field" =>  "id_country_country",
                        "size" => 250
                    )
                )
            ),
            "size" => 0
        );

        // 3 = industries_counters
        $query_aditional[] = array(
            "query" => array(
                "bool" => array(
                    "should" => $query_should,
                    "must" => $query_must,
                    "filter" => array(
                        "bool" => array(
                            "must" => array_merge($filter_must, $filter_type, $filter_country, $filter_category),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "aggs" => array(
                "industries_counters" => array(
                    "terms" =>   array(
                        "field" =>  "company_industries",
                        "size" => 100
                    )
                )
            ),
            "size" => 0
        );

        // 4 = categories_counters
        $query_aditional[] = array(
            "query" => array(
                "bool" => array(
                    "should" => $query_should,
                    "must" => $query_must,
                    "filter" => array(
                        "bool" => array(
                            "must" => array_merge($filter_must, $filter_type, $filter_country, $filter_industry),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "aggs" => array(
                "categories_counters" => array(
                    "terms" =>   array(
                        "field" =>  "company_categories",
                        "size" => 10000
                    )
                )
            ),
            "size" => 0
        );

        // 5 = types_counters
        $query_aditional[] = array(
            "query" => array(
                "bool" => array(
                    "should" => $query_should,
                    "must" => $query_must,
                    "filter" => array(
                        "bool" => array(
                            "must" => array_merge($filter_must, $filter_country, $filter_industry, $filter_category),
                        )
                    )
                )
            ),
            "sort" => $sort,
            "aggs" => array(
                "types_counters" => array(
                    "terms" => array(
                        "field" => "id_type_name"
                    )
                ),
            ),
            "size" => 0
        );

        $elastic_results = $this->obj->elasticsearch->mget($this->type, array_merge(array($query_base), $query_aditional));

        $this->count = $elastic_results['responses'][0]['hits']['total']['value'];
        foreach($elastic_results['responses'][0]['hits']['hits'] as $record){
            if($record['_source']['type_company'] === 'branch') {
                $record['_source']['main_company']  = array(
                    "id_company" => $record['_source']['parent_company'],
                    "index_name" => $record['_source']['parent_index_name'],
                    "name_company" => $record['_source']['parent_name_company'],
                    "type_company" => $record['_source']['parent_type_company']
                );
            }
            $this->records[] = $record['_source'];
        }

        $countries = $elastic_results['responses'][1]['aggregations']['countries']['buckets'];
        $industries = $elastic_results['responses'][1]['aggregations']['industries']['buckets'];
        $categories = $elastic_results['responses'][1]['aggregations']['categories']['buckets'];
        $types = $elastic_results['responses'][1]['aggregations']['types']['buckets'];

        // COUNTRIES AGREGATES && COUNTERS
        $this->aggregates['countries'] = array();
        foreach($countries as $country) {
            list($id_country, $country_name) = explode("_", $country['key'], 2);
            $this->aggregates['countries'][$id_country] = array(
                "id" => $id_country,
                "id_country" => $id_country,
                "country" => $country_name,
                "counter" => 0
            );
        }

        foreach ($elastic_results['responses'][2]['aggregations']['countries_counters']['buckets'] as $country_counter) {
            list($id_country, $country_name) = explode("_", $country_counter['key'], 2);
            $this->aggregates['countries'][$id_country]['counter'] = $country_counter['doc_count'];
        }

        // INDUSTRIES AGREGATES && COUNTERS
        $this->aggregates['industries'] = array();
        foreach($industries as $industry) {
            $this->aggregates['industries'][$industry['key']] = 0;
        }

        foreach ($elastic_results['responses'][3]['aggregations']['industries_counters']['buckets'] as $industry_counter) {
            $this->aggregates['industries'][$industry_counter['key']] = $industry_counter['doc_count'];
        }

        // CATEGORIES AGREGATES && COUNTERS
        $this->aggregates['categories'] = array();
        foreach($categories as $category) {
            $this->aggregates['categories'][$category['key']] = 0;
        }

        foreach ($elastic_results['responses'][4]['aggregations']['categories_counters']['buckets'] as $category_counter) {
            $this->aggregates['categories'][$category_counter['key']] = $category_counter['doc_count'];
        }

        // TYPES AGREGATES && COUNTERS
        $this->aggregates['types'] = array();
        foreach($types as $type) {
            list($id_type, $type_name) = explode("_", $type['key'], 2);
            $this->aggregates['types'][$id_type] = array(
                "id_type" => $id_type,
                "name_type" => $type_name,
                "counter" => 0
            );
        }

        foreach ($elastic_results['responses'][5]['aggregations']['types_counters']['buckets'] as $type) {
            list($id_type, $type_name) = explode("_", $type['key'], 2);
            $this->aggregates['types'][$id_type]['counter'] = $type['doc_count'];
        }
    }

    public function index_company($company_id = 0)
    {
        $id_companies = array();
        $company_info = model('company')->get_simple_company((int) $company_id);
        if(empty($company_info)){
            return false;
        }

        if($company_info['type_company'] == 'company'){
            $company_branches = model('branch')->get_company_branches($company_id, "cb.id_company");
            if(!empty($company_branches)){
                $id_companies = array_filter(array_map('intval', array_column($company_branches, 'id_company')));
            }
        } else{
            $id_companies[] = (int) $company_info['parent_company'];
        }

        $id_companies[] = (int) $company_id;

        $this->obj->elasticsearch->delete_by_query($this->type, $this->obj->elasticsearch->get_terms('id_company', $id_companies));

        return $this->index(['companiesIds' => $id_companies]);
    }

    /**
     * Removes company from index.
     */
    public function removeCompany(int $companyId): bool
    {
        $curlResult = $this->elasticsearchLibrary->deleteById($this->type, $companyId);

        return 'deleted' === $curlResult['result'];
    }

    public function index_companies($company_ids = array())
    {
        if(!is_array($company_ids) || empty($company_ids)){
            return false;
        }

        $company_ids = array_map('intval', (array) $company_ids);
        foreach ($company_ids as $id_company) {
            $this->index_company($id_company);
        }
    }

    public function index($conditions = array()) {
        $where = [
            " cb.visible_company = ? ",
            " cb.blocked = ? ",
            " u.fake_user = ? "
        ];
        $params = [1, 0, 0];

        extract($conditions);

        if (!empty($companiesIds)) {
            $where[] =  ' cb.id_company IN (' . implode(',', array_fill(0, count($companiesIds), '?')) . ')';
            array_push($params, ...$companiesIds);
        }

        $sql = "SELECT
                    cb.*,
                    ct.name_type, CONCAT(cb.id_type, '_', ct.name_type) as id_type_name,
                    pc.country, CONCAT(cb.id_country, '_', pc.country) as id_country_country,
                    GROUP_CONCAT(DISTINCT(ri.id_industry)) as company_industries, GROUP_CONCAT(DISTINCT(rc.id_category)) as company_categories,
                    u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name, u.`status`, u.user_group, u.is_verified, ug.gr_name as user_group_name,
                    ct.group_name_suffix as user_group_name_sufix
                FROM company_base cb
                INNER JOIN company_type ct ON cb.id_type=ct.id_type
                INNER JOIN company_relation_industry ri ON cb.id_company = ri.id_company
                INNER JOIN company_relation_category rc ON cb.id_company = rc.id_company
                INNER JOIN port_country pc ON cb.id_country = pc.id
                INNER JOIN users u ON cb.id_user = u.idu
                INNER JOIN user_groups ug ON u.user_group = ug.idgroup ";

        if (!empty($users_list)) {
            $users_list = getArrayFromString($users_list);
            $where[] = " cb.id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
        }

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .=" GROUP BY cb.id_company
                ORDER BY cb.id_company ASC";

        $rows = arrayByKey($this->db->query_all($sql, $params), 'id_company');

        $companies = array_filter(array_map(
            function($company) use ($rows){
                if($company['type_company'] == 'branch'){
                    if(empty($rows[$company['parent_company']])){
                        return;
                    }

                    $company['parent_index_name'] = $rows[$company['parent_company']]['index_name'];
                    $company['parent_name_company'] = $rows[$company['parent_company']]['name_company'];
                    $company['parent_type_company'] = $rows[$company['parent_company']]['type_company'];
                }

                $now = new DateTime("now", new DateTimeZone('UTC'));
                $company['last_indexed_date'] = $now->format(DATE_ATOM);

                return $company;
            }, $rows
        ));

        $queries = array();
        foreach($companies as $company) {
            array_push(
                $queries,
                ...$this->obj->elasticsearch->bulk_index_query(
                    $this->type,
                    $company["id_company"],
                    $company
                )
            );
        }

        $this->obj->elasticsearch->type = $this->type;
        return $this->obj->elasticsearch->bulk($queries);
    }
}

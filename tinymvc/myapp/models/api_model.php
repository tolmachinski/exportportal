<?php

class Api_Model extends TinyMVC_Model {

    var $obj;
    private $api_keys_table = "api_keys";
    private $users_table = "users u";
    private $request_body = array(
        'query' => array(
            'bool' => array(
                'filter' => array(
                    'bool' => array()
                )
            )
        )
    );
    private $conditions_for_item_category =  array(
        'source'            => 'category_id,name,slug',
        'parent'            => 0,
        'match'             => "parent",
        'order'             => "name",
        'order_type'        => "asc",
    );
    private $conditions_for_company = array(
        'source'            => 'id_user,user_name,user_group_name,id_company,
                                name_company,email_company,phone_code_company,company_name,
                                phone_company,id_country,country,id_city,
                                address_company,rating_company,rating_count_company,
                                company_industries,logo_company,type_company,index_name,last_indexed_date',
        'visible_company'   => 1,
        'blocked'           => 0,
        'type_company'      => "company",
        'match'             => "blocked,type_company,visible_company",
        'order'             => "id_company",
        'order_type'        => "desc",
    );

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function check_api_key($key)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->api_keys_table);

        $this->db->where('enable', 1);
        $this->db->where('api_key', $key);

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    function get_buyers($params)
    {
        $select_fields = "u.idu,u.fname,u.lname,u.email,u.phone_code,u.phone,
                          u.country,u.city,u.address,u.user_photo,u.user_group,
                          CONCAT_WS(', ', z.city, st.state_name) as user_city,CONCAT_WS(' ',u.fname, u.lname) as user_name,
                          gr.gr_type,pc.country as user_country ";
        $limit = 10;
        $start = 0;
        extract($params);

        $this->db->select($select_fields);
        $this->db->from($this->users_table);

		$this->db->join("user_groups gr", "u.user_group = gr.idgroup", "left");
		$this->db->join("port_country pc", "u.country = pc.id", "left");
        $this->db->join("zips z", "u.city = z.id", "left");
        $this->db->join("states st", "u.state = st.id", "left");

        //region Conditions
        $this->db->where('u.status', 'active');
        $this->db->where('u.user_group', 1);
        $this->db->where('u.is_verified', 1);
        if(isset($fake_user)){
            $this->db->where('u.fake_user', $fake_user);
        }
        if(isset($country_list)){
            $this->db->in("u.country", $country_list);
        }
        //endregion Conditions

        //OrderBy
        $this->db->orderby('u.idu DESC');

        //Limits
        $this->db->limit($limit, $start);

        return $this->db->query_all();
    }

    function count_buyers($params)
    {
        extract($params);

        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->users_table);

        //region Conditions
        $this->db->where('u.status', 'active');
        $this->db->where('u.user_group', 1);
        $this->db->where('u.is_verified', 1);
        if(isset($fake_user)){
            $this->db->where('u.fake_user', $fake_user);
        }
        if(isset($country_list)){
            $this->db->in("u.country", $country_list);
        }
        //endregion Conditions

        $data = $this->db->query_one();
        if (!$data || empty($data)) {
            return 0;
        }

        return isset($data['AGGREGATE']) ? (int) $data['AGGREGATE'] : 0;
    }

    function get_from_elastic($table, $request_data)
    {
        $conditions = array_merge($request_data, $this->{'conditions_for_' . $table});

        $query = $this->_create_elastic_request($conditions);

        $result = array();

        if (!empty($query)) {
            $this->obj->load->library("elasticsearch");

            $this->request_body['query']['bool']['filter']['bool']["must"] = $query;

            $result = $this->obj->elasticsearch->get($table, $this->request_body);
        }
        return $result;
    }

    private function _create_elastic_request($conditions)
    {
        $output = array();
        if (isset($conditions['start'])) {
            $this->request_body['from'] = $conditions['start'];
        }

        if (isset($conditions['limit'])) {
            $this->request_body['size'] = $conditions['limit'];
        }

        if (isset($conditions['order'])) {
            $order_column = (empty($conditions['order']) ? "id" : $conditions['order']);
            $order_type   = (empty($conditions['order_type']) ? "desc" : $conditions['order_type']);

            $this->request_body['sort'][] = array(
                $order_column => array(
                    'order' => $order_type
                )
            );
        }

        if (isset($conditions['source'])){
            $source = explode(',', $conditions['source']);
            $source = array_map('cleanInput', $source);

            if(!empty($source)){
                $this->request_body['_source'] = $source;
            }
        }

        // if (!empty($conditions['condition'])) {
        //     $where_condition = array();
        //     switch ($conditions['condition']) {
        //         case 'where_or':
        //             $where_condition['should'] = array();
        //             break;

        //         case 'where_and_not':
        //             $where_condition['must_not'] = array();
        //             break;

        //         default:
        //             $where_condition['must'] = array();
        //             break;
        //     }
        // }

        if (!empty($conditions['term'])) {
            $term = explode(',', $conditions['term']);
            $term = array_filter($term);
            $temp_term = $this->_create_condition($conditions, $term, "term");
            $output = array_merge($output, $temp_term);
        }

        if (!empty($conditions['match'])) {
            $match = explode(',', $conditions['match']);
            $match = array_filter($match);
            $temp_match = $this->_create_condition($conditions, $match, "match");
            $output = array_merge($output, $temp_match);
        }

        if (!empty($conditions['terms'])){
            $terms = explode(',', $conditions['terms']);
            $terms = array_filter($terms);
            $temp_terms = $this->_create_condition($conditions, $terms, "terms");
            // $output[] = $temp_terms;
            $output = array_merge($output, $temp_terms);
        }

        if (!empty($conditions['range'])) {

            $type = "greater";
            $range = explode(',', $conditions['range']);
            $range = array_filter($range);
            if (!empty($conditions['range_type'])) {
                $type = $conditions['range_type'];
            }

            $temp_range = $this->_create_condition($conditions, $range, "range_" . $type);
            $output = array_merge($output, $temp_range);
        }

        if (isset($conditions['last_indexed_from'])) {
            $output = array_merge($output, array(
                array(
                    "range" => array(
                        "last_indexed_date" => array(
                            "gte" => $conditions['last_indexed_from']
                        )
                    )
                )
            ));
        }

        if (isset($conditions['last_indexed_to'])) {
            $output = array_merge($output, array(
                array(
                    "range" => array(
                        "last_indexed_date" => array(
                            "lte" => $conditions['last_indexed_to']
                        )
                    )
                )
            ));
        }

        return $output;
    }


    private function _create_condition($soucre = array(), $fields = array(), $type = "")
    {
        $output = array();
        if (empty($fields)) {
            return $output;
        }

        foreach ($fields as $field) {
            if (isset($soucre[$field])) {
                switch ($type) {
                    case 'match':
                    case 'term' :
                        $output[] = array(
                            $type => array(
                                $field => $soucre[$field]
                            )
                        );
                        break;

                    case 'terms':
                        $temp = explode(',', $soucre[$field]);
                        $output[] = array(
                            $type => array(
                                $field => $temp
                            )
                        );
                        break;

                    case 'range_greater_equal':
                        $output[] = array(
                            'range' => array(
                                $field => array(
                                    "gte" => $soucre[$field]
                                )
                            )
                        );
                        break;

                    case 'range_greater':
                        $output[] = array(
                            'range' => array(
                                $field => array(
                                    "gt" => $soucre[$field]
                                )
                            )
                        );
                        break;

                    case 'range_less_equal':
                        $output[] = array(
                            'range' => array(
                                $field => array(
                                    "lte" => $soucre[$field]
                                )
                            )
                        );
                        break;

                    case 'range_less':
                        $output[] = array(
                            'range' => array(
                                $field => array(
                                    "lt" => $soucre[$field]
                                )
                            )
                        );
                        break;
                }
            }
        }
        return $output;
    }

}

<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Api_partners_Controller extends TinyMVC_Controller {
    private $request_body = array(
        'query' => array(
            'bool' => array(
                'filter' => array(
                    'bool' => array()
                )
            )
        )
    );

    private $response = array(
        200 => array(
            'status'  => 200,
            'message' => "Everything is working",
            'type'    => 'ok'
        ),
        400 => array(
            'status'  => 400,
            'message' => "Bad Request",
            'type'    => "bed_request"
        ),
        401 => array(
            'status'  => 401,
            'message' => "The request requires an user authentication",
            'type'    => "authentication"
        ),
        404 => array(
            'status'  => 404,
            'message' => "There is no resource behind the URI",
            'type'    => "no_resource_behind"
        )
    );

    private function _check_api_key()
    {
        $header = getallheaders();

        $this->load->model('Api_keys_Model', 'api_keys');

        if (empty($header['Token']) || !$this->api_keys->check_api_key($header['Token'])){
            $this->output(array(), 401);
        }
    }

    private function request_data($type = "")
    {
        switch ($type) {
            case 'POST':
                $output = $_POST;
                break;

            case 'GET':
                $output = $_GET;
                break;

            default:
                $output = $temp = file_get_contents('php://input');
                if (!empty($temp) && !is_array($temp)) {
                    parse_str($temp, $output);
                }
                break;
        }
        return $output;
    }

    private function output($additionals = array(), $status = 404)
    {
        http_response_code($status);
        header("Content-Type: application/json");

        if (empty($this->response[$status])) {
            $status = 404;
            $additionals = array();
        }

        $data = $output = array();
        if (!empty($_GET['debug_mode'])) {
            $data['request'] = $this->request_data();
        }

        if (!empty($additionals['type'])) {
            $this->response[$status]['type'] = $additionals['type'];
            unset($additionals['type']);
        }

        if (!empty($additionals['message'])) {
            $this->response[$status]['message'] = $additionals['message'];
            unset($additionals['message']);
        }

        if ($status !== 200 && empty($additionals)) {
            $output = array(
                'error' => array(
                    'type'  => $this->response[$status]['type'],
                    'reason'=> $this->response[$status]['message'],
                    'line'  => "",
                    'col'   => "",
                ),
                'status'=> $status,
            );
        }

        $data = array_merge($data, $output, $additionals);
        exit(json_encode($data));
    }

    private function _create_elastic_request($conditions = array())
    {
        $output = array();
        if (isset($conditions['start'])) {
            $this->request_body['from'] = $conditions['start'];
        }

        if (isset($conditions['length'])) {
            $this->request_body['size'] = $conditions['length'];
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

    private function get_sellers_by_condition($conditions = array())
    {
        if (empty($conditions)) {
            return array();
        }

        $query = $this->_create_elastic_request($conditions);

        $result = array();

        if (!empty($query)) {
            $this->load->library("elasticsearch");

            $this->request_body['query']['bool']['filter']['bool']["must"] = $query;

            $result = $this->elasticsearch->get("company", $this->request_body);
        }

        return $result;
    }

    public function get_companies()
    {
        $this->_check_api_key();

        $conditions = array(
            'status'            => "active",
            'accreditation'     => 1,
            'blocked'           => 0,
            'visible_company'   => 0,
            'term'              => "id_country",
            'match'             => "accreditation,blocked,status",
            'range'             => "visible_company"
        );

        $request_data = $this->request_data();

        if (empty($request_data['id_country'])) {
            $this->output(array(
                'message' => "Country is not specified"
            ), 400);
        }

        $conditions = array_merge($conditions, $request_data);

        $result = $this->get_sellers_by_condition($conditions);

        $status = 404;
        if (!empty($result['status'])) {
            $status = $result['status'];
        }

        $this->output($result, $status);
    }

    public function get_items()
    {
        $this->_check_api_key();

        $conditions = array(
            "terms" => "item_categories"
        );

        $request_data = $this->request_data();

        if (!empty($request_data['field'])) {
            $conditions['terms'] = $request_data['field'];
        }

        if (empty($request_data[$conditions['terms']])) {
            $this->output(array(
                'message' => "{$conditions['terms']} is not specified"
            ), 400);
        }

        $conditions = array_merge($conditions, $request_data);
        $query = $this->_create_elastic_request($conditions);
        $result = array();

        if (!empty($query)) {
            $this->load->library("elasticsearch");

            $this->request_body['query']['bool']['filter']['bool']["must"] = $query;

            $result = $this->elasticsearch->get("items", $this->request_body);
        }

        $status = 404;
        if (!empty($result['status'])) {
            $status = $result['status'];
        }

        $this->output($result, $status);
    }

    public function get_catogories_links()
    {
        $this->_check_api_key();

        $request_data = $this->request_data();

        if (empty($request_data['id_category'])) {
            $this->output(array(
                'message' => "Category is not specified"
            ), 400);
        }

        if (empty($request_data['id_countries'])) {
            $this->output(array(
                'message' => "Countries is not specified"
            ), 400);
        }

        $request_elastic = array(
            'aggs' => array(
                'attr_counter' => array(
                    'terms' => array(
                        'field'     => 'item_categories.path',
                        'include'   => "(.*\\,)?{$request_data['id_category']}(\\,[0-9]+){1}",
                        'size'      => 100
                    )
                )
            ),
            'size' => 0
        );

        $result = array();
        $this->load->library("elasticsearch");
        $elastic_categories = $this->elasticsearch->get("items", $request_elastic);

        if (!empty($elastic_categories['aggregations']['attr_counter']['buckets'])) {
            $id_categories = $temp_items = array();
            foreach ($elastic_categories['aggregations']['attr_counter']['buckets'] as $item) {
                $temp_items[] = $item['key'];
            }
            $id_categories = implode(',', $temp_items);
            $id_categories = explode(',', $id_categories);
            $id_categories = array_flip($id_categories);

            $id_categories = array_keys($id_categories);

            $this->load->model('Category_Model', 'category');

            $categories = $this->category->getCategories(array(
                'columns'   => "category_id, name, p_or_m, cat_type",
                'cat_list'  => implode(',', $id_categories)
            ));

            $category_name = "category-id";
            if (!empty($categories)) {
                foreach ($categories as $value) {
                    if ($value['category_id'] == $request_data['id_category']) {
                        $category_name = strForURL($value['name']);
                    } else {
                        $result['categories'][] = array(
                            'name'  => $value['name'],
                            'url'   => __SITE_URL . "category/" . strForURL($value['name']) . "/" . $value['category_id']
                        );
                    }
                }
            }

            $this->load->model('Country_Model', 'countries');

            $countries = $this->countries->get_simple_countries($request_data['id_countries']);

            if (!empty($countries)) {
                foreach ($countries as $value) {
                    $result['countries'][] = array(
                        'name'  => $value['country'],
                        'url'   => __SITE_URL . 'category/' . $category_name . '/' . $request_data['id_category'] . '/country/' . strForURL($value['country']) . "-" . $value['id']
                    );
                }
            }
        }

        $status = 200;
        if (!empty($elastic_categories['status'])) {
            $status = $elastic_categories['status'];
        }

        $this->output($result, $status);
    }

    public function get_countries_link()
    {
        $this->_check_api_key();

        $request_data = $this->request_data();

        if (empty($request_data['id_country'])) {
            $this->output(array(
                'message' => "Country is not specified"
            ), 400);
        }

        if (empty($request_data['items'])) {
            $this->output(array(
                'message' => "Items is not specified"
            ), 400);
        }

        $output = array();
        if (is_array($request_data['items'])) {
            $this->load->model('Country_Model', 'country');
            $this->load->model('Category_Model', 'categories');
            $country = $this->country->get_country($request_data['id_country']);

            $cities_list = array_column($request_data['items'], 'id_city');
            $cities_list = implode(',', $cities_list);
            $cities = $this->country->get_cities_by_list($cities_list);

            $categories_list = array_column($request_data['items'], 'id_category');
            $categories_list = implode(',', $categories_list);
            $categories = $this->categories->getCategories(array('cat_list' => $categories_list));
            $categories = arrayByKey($categories, 'category_id');

            $country_link = "/country/".strForURL($country['country'])."-{$country['id']}/city/";
            $output_cities = $output_categories = array();

            foreach ($request_data['items'] as $item) {
                if (!empty($categories[$item['id_category']])) {
                    $link_category = __SITE_URL."category/".strForURL($categories[$item['id_category']]['name'])."/{$item['id_category']}";
                    $output['categories'][] = array(
                        'name' => $categories[$item['id_category']]['name'],
                        'link' => $link_category
                    );


                    if (!empty($cities[$item['id_city']])) {
                        $output['cities'][] = array(
                            'name' => $cities[$item['id_city']],
                            'link' => $link_category.$country_link.strForURL($cities[$item['id_city']])."-{$item['id_city']}/"
                        );
                    }
                }
            }
        } else {
            $this->output(array(
                'message' => "Items contain incorrect values"
            ), 400);
        }

        $this->output($output, "200");
    }

    public function get_categories_by_country()
    {
        $this->_check_api_key();

        $request_data = $this->request_data();

        if (empty($request_data['id_country'])) {
            $this->output(array(
                'message' => "Country is not specified"
            ), 400);
        }

        $this->load->library("elasticsearch");

        $conditions = array(
            "terms"     => "p_country",
            "p_country" => $request_data['id_country']
        );

        $query = $this->_create_elastic_request($conditions);

        $this->request_body['query']['bool']['filter']['bool']["must"] = $query;

        $this->request_body['aggs'] = array(
            'attr_counter' => array(
                'terms' => array(
                    'field'     => 'item_categories.path',
                    'size'      => 100
                )
            )
        );

        $result = array();
        $this->load->library("elasticsearch");
        $elastic_categories = $this->elasticsearch->get("items", $this->request_body);

        if (!empty($elastic_categories['aggregations']['attr_counter']['buckets'])) {
            $id_categories = $temp_items = array();
            foreach ($elastic_categories['aggregations']['attr_counter']['buckets'] as $item) {
                $temp_items[] = $item['key'];
            }
            $id_categories = implode(',', $temp_items);
            $id_categories = explode(',', $id_categories);
            $id_categories = array_flip($id_categories);
            $id_categories = array_keys($id_categories);

            $this->load->model('Category_Model', 'category');

            $categories = $this->category->getCategories(array(
                'columns'           => "category_id, name, p_or_m, cat_type",
                'cat_list'          => implode(',', $id_categories),
                'industries_only'   => TRUE
            ));

            $category_name = "category-id";
            if (!empty($categories)) {
                foreach ($categories as $value) {
                    if ($value['category_id'] == $request_data['id_category']) {
                        $category_name = strForURL($value['name']);
                    } else {
                        $result['categories'][] = array(
                            'name'  => $value['name'],
                            'url'   => __SITE_URL . "category/" . strForURL($value['name']) . "/" . $value['category_id']
                        );
                    }
                }
            }
        }

        $status = 404;
        if (!empty($elastic_categories['status'])) {
            $status = $elastic_categories['status'];
        }

        $this->output($result, $status);
    }

    public function get_countries()
    {
        $this->load->model('Country_Model', 'country');
        $output = $this->country->get_countries();
        $this->output($output, 200);
    }

    public function get_buyers()
    {
        $this->_check_api_key();

        $conditions = array(
            'additional' => true,
            'city_detail' => true,
            'fake_user' => 0,
            'status' => 'active',
            'is_verified' => 1,
            'group' => 1,
            'sort_by' => array('u.idu-DESC'),
        );

        $request = $this->request_data('GET');

        $request_data = array();

        if (!empty($request['start'])) {
            $request_data['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $request_data['per_p'] = intval($request['limit']);
        }

        if (!empty($request['id_countries'])) {
            $temp_id_countries = explode(',', $request['id_countries']);
            $temp_id_countries = array_map('intval', $temp_id_countries);
            $temp_id_countries = array_filter($temp_id_countries);
            $request_data['country_list'] = implode(',', $temp_id_countries);
        }

        $conditions = array_merge($request_data, $conditions);

        $users = model('user')->getUsers($conditions);

        if (empty($users)) {
            $this->output(array(
                'notification' => 'No data on the specified parameters.',
                'status' => 200,
            ), 200);
        }
        $output['total'] = model('user')->count_users($conditions);
        foreach ($users as $user) {
            $output['data'][] = array(
                'idu'           => $user['idu'],
                'fname'         => $user['fname'],
                'lname'         => $user['lname'],
                'user_name'     => $user['user_name'],
                'email'         => $user['email'],
                'phone_code'    => $user['phone_code'],
                'phone'         => $user['phone'],
                'country'       => $user['country'],
                'user_country'  => $user['user_country'],
                'city'          => $user['city'],
                'user_city'     => $user['user_city'],
                'address'       => $user['address'],
                'user_photo'    => $user['user_photo'],
                'photo_full_path'=> getUserAvatar($user['idu'], $user['user_photo'], $user["user_group"]),
                'user_link'     => getUserLink($user['user_name'], $user['idu'], $user['gr_type']),
            );
        }

        $this->output($output, 200);
    }

    public function get_sellers()
    {
        $this->_check_api_key();

        $conditions = array(
            'visible_company'   => 1,
            'blocked'           => 0,
            'type_company'      => "company",
            'match'             => "accreditation,blocked,status,type_company,visible_company",
            'order'             => "id_company",
            'order_type'        => "desc",
        );

        $request = $this->request_data('GET');

        $request_data = array();

        if (!empty($request['start'])) {
            $request_data['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $request_data['length'] = intval($request['limit']);
        }

        if (!empty($request['id_country'])) {
            $request_data['id_country'] = intval($request['id_country']);
            $request_data['term'] = "id_country";
        }

        $conditions = array_merge($request_data, $conditions);
        $sellers = $this->get_sellers_by_condition($conditions);

        if (!empty($sellers['error'])) {
            $this->output(array(
                'error' => $sellers['error']['reason'],
                'status' => 404
            ), 404);
        }

        if (empty($sellers['hits']['hits'])) {
            $this->output(array(
                'notification' => 'No data on the specified parameters.',
                'status' => 200,
            ), 200);
        }

        $output['total'] = $sellers['hits']['total'];

        foreach ($sellers['hits']['hits'] as $item) {
            $seller = $item['_source'];

            $output['data'][] = array(
                'id_user'               => $seller['id_user'],
                'user_name'             => $seller['user_name'],
                'user_group_name'       => $seller['user_group_name'],
                'user_link'             => getUserLink($seller['user_name'], $seller['id_user'], 'seller'),
                'id_company'            => $seller['id_company'],
                'name_company'          => $seller['name_company'],
                'email_company'         => $seller['email_company'],
                'phone_code_company'    => $seller['phone_code_company'],
                'phone_company'         => $seller['phone_company'],
                'id_country'            => $seller['id_country'],
                'country'               => $seller['country'],
                'id_city'               => $seller['id_city'],
                'address_company'       => $seller['address_company'],
                'rating_company'        => $seller['rating_company'],
                'rating_count_company'  => $seller['rating_count_company'],
                'company_industries'    => $seller['company_industries'],
                'logo_company'          => $seller['logo_company'],
                'photo_company_full_path' => getDisplayImageLink(array('{ID}' => $seller['id_company'], '{FILE_NAME}' => $seller['logo_company']), 'companies.main'),
                'company_link'            => getCompanyURL($seller),
            );
        }

        $this->output($output, 200);
    }

    public function get_categories()
    {
        $this->_check_api_key();

        $request = $this->request_data('GET');
        $conditions = array(
            'columns' => 'category_id, name, link, title, translations_data',
            'parent'  => 0,
        );

        if (!empty($request['start'])) {
            $conditions['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $conditions['per_p'] = intval($request['limit']);
        }

        $categories = model('Category')->getCategories($conditions);
        $output['total'] = count($categories);
        $output['data'] = $categories;

        $this->output($output, 200);
    }
}

<?php

use App\Common\Database\Relations\RelationInterface;
use App\Common\Traits\DatatableRequestAwareTrait;
use Doctrine\DBAL\ParameterType;
use App\Common\Buttons\ChatButton;

/**
 * Shippers controller
 *
 * @property \Company_Model             $company
 * @property \Country_Model             $country
 * @property \Items_Model               $items
 * @property \Shippers_Model            $shippers
 * @property \Tinymvc_Library_Cleanhtml $clean
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Library_Wall      $wall
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Shippers_Controller extends TinyMVC_Controller
{
    use DatatableRequestAwareTrait;

	private $breadcrumbs = array();

    public function directory()
    {
        show_404();
        /**
         * @author Cravciuc Andrei
         * @todo Remove [16.03.2022]
         * The business decides that we do not need to have this page in order to avoid data parsing.
        */
        /*
		$this->load->model('Shippers_Model', 'shippers');
		$this->load->model('Country_Model', 'country');

		$uri = $this->uri->uri_to_assoc(4);
        checkURI($uri, array( 'work_in_country', 'country', 'page'));
        $links_map = array(
            'work_in_country' => array(
                'type' => 'uri',
                'deny' => array('page', 'work_in_country')
            ),
            'country' => array(
                'type' => 'uri',
                'deny' => array('page', 'country')
            ),
            'page' => array(
                'type' => 'uri' ,
                'deny' => array('page')
            ),
            'per_p' => array(
                'type' => 'get',
                'deny' => array('page', 'per_p')
            ),
            'sort_by' => array(
                'type' => 'get',
                'deny' => array('page', 'sort_by')
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('page', 'keywords')
            )
        );

        $data['links_tpl'] = $links_tpl = $this->uri->make_templates($links_map, $uri);
        $data['links_tpl_without'] = $links_tpl_without = $this->uri->make_templates($links_map, $uri, true);

        $main_cond = array();

        if (config('env.APP_MODE') === 'prod') {
            $main_cond['fake_user'] = 0;
        }

		$data['per_p'] = $main_cond['per_p'] = 20;
		$data['page'] = $main_cond['page'] = 1;
		$data['sort_by'] = 'date_desc';
		$data['meta_params'] = array();
		$id_user = privileged_user_id();

        if (isset($_GET['per_p'])) {
            $per_p = filter_var($_GET['per_p'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if($per_p === false) {
                exit('per_p is not positive int');
            }
			$data['per_p'] = $main_cond['per_p'] = abs(intVal($_GET['per_p']));
		}

		if (isset($_GET['sort_by'])) {
            $data['sort_by'] = cleanInput($_GET['sort_by']);
        }

        switch ($data['sort_by']) {
            case 'title_asc':
                $main_cond['sort_by'][] = 'os.co_name ASC';
            break;
            case 'title_desc':
                $main_cond['sort_by'][] = 'os.co_name DESC';
            break;
            case 'date_asc':
                $main_cond['sort_by'][] = 'os.create_date ASC';
            break;
            case 'date_desc':
                $main_cond['sort_by'][] = 'os.create_date DESC';
            break;
            case 'rand':
                $main_cond['sort_by'][] = ' RAND()';
            break;
        }

		if (isset($uri['work_in_country'])) {
			$main_cond['work_in_country'] = id_from_link($uri['work_in_country']);
			$work_in_country_info = $this->country->get_country($main_cond['work_in_country']);
			if(empty($work_in_country_info)){
				show_404();
			}
			$data['work_in_continent'] = $work_in_country_info['id_continent'];
		}

		if (isset($uri['country'])) {
			$main_cond['country'] = id_from_link($uri['country']);
			$country_info = $this->country->get_country($main_cond['country']);
			if(empty($country_info)){
				show_404();
			}
			$data['country_continent'] = $country_info['id_continent'];
		}

		if (isset($_GET['keywords'])) {
			$data['keywords'] = $main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));
		}

        $data['page_link'] = $links_tpl_without['page'];

		if (!empty($_SERVER['QUERY_STRING'])) {
			$data['get_params'] = cleanInput(arrayToGET($_GET));
			$get_parameters = $_GET;
			foreach($_GET as $key => $one_param){
				$get_parameters[$key] = cleanInput($one_param);
			}
		}

        if(empty($links_tpl_without['per_p']) || substr($links_tpl_without['per_p'], 0, 1) == '?') {
            $per_p_glue = '';
        } else {
            $per_p_glue = '/';
        }
        list($data['page_link'], $data['get_per_p']) = explode('?', get_dynamic_url('/shippers/directory' . $per_p_glue . $links_tpl_without['per_p']));

        if(empty($links_tpl_without['sort_by']) || substr($links_tpl_without['sort_by'], 0, 1) == '?') {
            $sort_by_glue = '';
        } else {
            $sort_by_glue = '/';
        }
        list($data['curr_link'], $data['get_sort_by']) = explode('?', get_dynamic_url('/shippers/directory' . $sort_by_glue . $links_tpl_without['sort_by']));

		if (isset($uri['page'])) {
			$data['page'] = $main_cond['page'] = $uri['page'];
			$data['meta_params']['[PAGE]'] = $uri['page'];
        } else {
            $data['meta_params']['[PAGE]'] = 1;
        }

		if(have_right('sell_item')){
			$data['id_partners_list'] = explode(',', $this->shippers->get_id_shippers_partnership($id_user));
			$data['id_partners_requests_list'] = explode(',', $this->shippers->get_id_shippers_partnership($id_user, 0));
		}

        $main_cond['count'] = $data['count'] = $this->shippers->count_shippers_by_conditions($main_cond);

		$shipper_list = $this->shippers->get_shippers_detail_by_conditions($main_cond);

        $data['shipper_list'] = [];
		if (!empty($shipper_list) && logged_in()) {
            $data['shipper_list'] = array_map(
                function ($shipperListItem) {
                    $chatBtn = new ChatButton(['recipient' => $shipperListItem['id_user'], 'recipientStatus' => $shipperListItem['user_status']]);
                    $shipperListItem['btnChat'] = $chatBtn->button();
                    return $shipperListItem;
                },
                $shipper_list
            );
        }elseif(!empty($shipper_list)){
			$data['shipper_list'] = $shipper_list;
		}

		$data['countries_by_continents'] = array(
			'1' => array('name' => 'Asia', 'id' => '1', 'count' => 0),
			'2' => array('name' => 'Africa', 'id' => '2', 'count' => 0),
			'3' => array('name' => 'Antarctica', 'id' => '3', 'count' => 0),
			'4' => array('name' => 'Europe', 'id' => '4', 'count' => 0),
			'5' => array('name' => 'North America', 'id' => '5', 'count' => 0),
			'6' => array('name' => 'Australia', 'id' => '6', 'count' => 0),
			'7' => array('name' => 'South America', 'id' => '7', 'count' => 0),
		);

		$countries_counts = arrayByKey($this->shippers->shippers_by_countries(), 'id');
		$data['count_countries_by_continents'] = array();

		foreach($countries_counts as $country_counts){
			if(!isset($data['count_countries_by_continents'][$country_counts['id_continent']])){
				$data['count_countries_by_continents'][$country_counts['id_continent']] = $data['countries_by_continents'][$country_counts['id_continent']];
			}

			$data['count_countries_by_continents'][$country_counts['id_continent']]['countries'][] = $country_counts;
			$data['count_countries_by_continents'][$country_counts['id_continent']]['count'] += $country_counts['counter'];
		}

		$countries = $this->country->get_countries();
		foreach($countries as $country){
			$data['countries_by_continents'][$country['id_continent']]['countries'][] = $country;
		}

        if(empty($links_tpl['page']) || substr($links_tpl['page'], 0, 1) == '?')
        {
            $page_glue = '';
        } else {
            $page_glue = '/';
        }

		$paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl['page'], '/shippers/directory'),
            'first_url'     => get_dynamic_url($links_tpl_without['page'], '/shippers/directory'),
			'total_rows'    => $main_cond['count'],
			'per_page'      => $data['per_p'],
            'replace_url'   => true,
		);


		if( !$this->is_pc ){
			$paginator_config['last_link'] = false;
			$paginator_config['first_link'] = false;
		}

		$this->load->library('Pagination', 'pagination');
		$this->pagination->initialize($paginator_config);
		$data['pagination'] = $this->pagination->create_links();

		$this->breadcrumbs[] = array(
			'link' => get_dynamic_url('/shippers/directory'),
			'title' => 'Freight Forwarders'
		);

		if (isset($uri['work_in_country'])) {
			$country = $work_in_country_info['country'];

			$data['search_params'][] = array(
				'link' => get_dynamic_url($links_tpl_without['work_in_country'], '/shippers/directory'),
				'title' => $country,
				'param' => 'Work in country',
			);
			$this->breadcrumbs[] = array(
				'link' => get_dynamic_url('shippers/directory/work_in_country/' . $uri['country']),
				'title' => $country
			);
			$data['meta_params']['[WORK_IN_COUNTRY]'] = $country;
		}

		if (isset($uri['country'])) {
			$country = $country_info['country'];

			$data['search_params'][] = array(
				'link' => get_dynamic_url($links_tpl_without['country'], '/shippers/directory'),
				'title' => $country,
				'param' => 'Origin country',
			);
			$this->breadcrumbs[] = array(
				'link' => get_dynamic_url('shippers/directory/country/' . $uri['country']),
				'title' => $country
			);
			$data['meta_params']['[COUNTRY]'] = $country;
		}

		if (isset($_GET['keywords'])) {
			$keywords = cleanInput(cut_str($_GET['keywords']));
			$data['search_params'][] = array(
				'link' => get_dynamic_url($links_tpl_without['keywords'], '/shippers/directory'),
				'title' => $keywords,
				'param' => 'Keywords',
			);
			$data['meta_params']['[KEYWORDS]'] = $keywords;
		}

		$data['breadcrumbs'] = $this->breadcrumbs;

        $data['header_content'] = 'new/shippers/directory/title_view';
        $data['sidebar_left_content'] = 'new/shippers/directory/sidebar_view';
        $data['main_content'] = 'new/shippers/directory/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
        */
	}

    public function administration()
    {
        checkAdmin('manage_content');

        $this->view->assign(array(
			'title' => 'Freight ForwarderS',
			'upload_folder' => encriptedFolderName()
		));
        $this->view->display('admin/header_view');
        $this->view->display('admin/shippers/index_view');
        $this->view->display('admin/footer_view');
    }

    public function my_partners()
    {
        checkIsLogged();
        checkPermision('manage_b2b_requests');
		checkGroupExpire();

        $this->load->model('Ishippers_Model', 'ishippers');
		$data = array(
			'title' => 'My freight forwarder partners',
			'ishippers' => $this->ishippers->get_shippers(),
			'ishippers_partners' => arrayByKey($this->ishippers->get_seller_shipper_ipartners(privileged_user_id()), 'id_shipper')
		);

		$data['tab'] = 'international_shippers';
		$tab = $this->uri->segment(3);

		if ($tab == 'ep_shippers') {
			$data['tab'] = 'ep_shippers';
		}

		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/shippers/my/partners');
		$this->view->display('new/footer_view');
    }

    public function estimates_requests()
    {
        checkIsLogged();
        checkPermision('buy_item');

        $uri = $this->uri->uri_to_assoc();
        $search = null;
        if(!empty($uri['search'])){
            $search = cleanInput($uri['search']);
        }
		if(!empty($uri['group'])){
            $group_key = cleanInput($uri['group']);
            $total_estimates = model('shipping_estimates')->count_shipping_estimates(array(
                'conditions' => array(
                    'group'  => $group_key
                )
            ));
            $seller = model('shipping_estimates')->find_estimate(array(
                'limit'      => 1,
                'columns'    => array('id_seller'),
                'with'       => array('company' => function(RelationInterface $relation) { $relation->getQuery()->select('id_user, name_company'); }),
                'conditions' => array(
                    'group'  => $group_key
                )
            ));

            $company_name = arrayGet($seller, 'company.name_company');
            if(null !== $company_name) {
                $filter_title = translate("shipping_estimates_dashboard_dt_filters_entity_group_company_title", array(
                    '{amount}' => $total_estimates, '{company}' => $company_name), true);
            } else {
                $filter_title = translate("shipping_estimates_dashboard_dt_filters_entity_group_unknown_company_title", array(
                    '{amount}' => $total_estimates), true);
            }

            $group_filter = array(
                'key'   => $group_key,
                'title' => $filter_title,
            );
        }

		$this->view->assign(array(
            'title'       => translate('shipping_estimates_dashboard_page_title_text', null, true),
            'countries'   => model('country')->get_countries(),
            'filters'     => array(
                'search' => $search,
                'group'  => $group_filter,
            ),
        ));
        $this->view->display('new/header_view');
        $this->view->display('new/shippers/estimates_requests/my/index_view');
        $this->view->display('new/footer_view');
    }

    public function estimates()
    {
        checkPermision('manage_shipper_estimates');
        checkDomainForGroup();

        $data = [
            'title' => 'Shipping estimates',
            'countries'   => model('country')->get_countries(),
        ];

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/estimates/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $data['errors'] = array();
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_estimate_response':

                $estimates = model('shipping_estimates');
                $shipper_id = my_shipper_company_id();
                $estimate_id = (int) $this->uri->segment(4);
                if (
                    empty($estimate_id) ||
                    empty($estimate = $estimates->get_estimate($estimate_id))
                ) {
                    messageInModal(translate('systmess_error_shipping_estimate_does_not_exist'));
                }

                if ($estimates->is_estimate_has_shipper_response($shipper_id, $estimate_id)) {
                    messageInModal(translate('systmess_error_already_responded_to_estimate'));
                }

                if ('expired' === arrayGet($estimate, 'current_countdown', 'expired')) {
                    messageInModal(translate('systmess_error_cannot_respond_to_expired_estimate'));
                }

                $this->view->display('new/shippers/estimates_requests/add_estimate_response_form_view', array(
                    'action' => __CURRENT_SUB_DOMAIN_URL . 'shippers/ajax_shippers_operation/add_estimate_response',
                    'estimate' => $estimate
                ));
            break;
            case 'add_shipper':
                $this->view->display('admin/shippers/shippers_form_view', array(
                    'upload_folder' => $this->uri->segment(4),
                ));
			break;
            case 'edit_shipper': // @deprecated
                messageInModal('This action is deprecated');
                checkPermisionAjaxModal('edit_company');

				$this->load->model('Category_Model', 'category');
                $this->load->model('Shippers_model', 'shippers');

                $id_shipper = intVal($this->uri->segment(5));
                $data['shipper'] = $this->shippers->get_shipper_details($id_shipper);
				if(empty($data['shipper'])){
					messageInModal('Error: This freight forwarder does not exist.');
				}

				$this->load->model("Country_model", 'country');
				$data['countries'] = $this->country->get_countries();

				//get industries
				$data['industries'] = model('category')->get_industries();

				//get industries selected
				$relation_industry = $this->shippers->get_relation_industry_by_company_id((int) $id_shipper);
				$data['shipper']['industry'] = array();

				if(!empty($relation_industry)){
					foreach($relation_industry as $item){
						$list_industries_selected[] = $item['id_industry'];
					}

					//industries selected
					$data['shipper']['industry'] = $list_industries_selected;

					//industries selected
					$data['industries_selected'] = $this->category->getCategories(
						array(
							'cat_list' => implode(',', $list_industries_selected),
							'columns' => "category_id, name"
						)
					);
				}

				if(!empty($data['shipper']['id_country']))
					$data['states'] = $this->country->get_states($data['shipper']['id_country']);

				$data['city_selected'] = $this->country->get_city($data['shipper']['id_city']);

				$data['upload_folder'] = $this->uri->segment(4);
                $this->view->display('admin/shippers/shippers_form_view', $data);
			break;
            case 'create_estimate':
                checkPermisionAjaxModal('buy_item');

                $type = uri()->segment(4);
                if (!in_array($type, ['item', 'basket'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $request = request()->request;

                //region Items
                $items = $itemsQuantities = $itemsIds = [];
                foreach ((array) $request->get('item') as $itemId => $quantity) {
                    $quantity = (int) $quantity;
                    if (empty($quantity)) {
                        messageInModal(translate('systmess_error_create_estimate_item_quantity_0'));
                    }

                    $itemsIds[] = (int) $itemId;
                    $itemsQuantities[$itemId] = $quantity;
                }

                if(
                    empty($itemsIds) ||
                    empty($items = $productsModel->findAllBy([
                        'conditions' => [
                            'ids'   => $itemsIds
                        ]
                    ]))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }
                //endregion Items

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                /** @var Countries_Model $countriesModel */
                $countriesModel = model(Countries_Model::class);

                $buyer = $usersModel->findOne(id_session());
                if (!empty($buyer['city'])) {
                    /** @var Cities_Model $citiesModel */
                    $citiesModel = model(Cities_Model::class);

                    $buyerCity = $citiesModel->findOne((int) $buyer['city']);
                }

                if (!empty($buyer['country'])) {
                    /** @var States_Model $statesModel */
                    $statesModel = model(States_Model::class);

                    $buyerCountryStates = $statesModel->findAllBy([
                        'conditions'    => [
                            'countryId' => (int) $buyer['country'],
                        ],
                    ]);
                }

                $company_id = arrayGet($items, "0.id_seller");

                //region Departure location
                $location = null;
                $location_parts = array();
                $departure_country = null;
                if (!empty($departure_country_id = (int) arrayGet($items, "0.p_country"))) {
                    $departure_country = array_filter((array) model('country')->get_country($departure_country_id));
                    $location_parts[] = arrayGet($departure_country, 'country');
                }
                $departure_region = null;
                if (!empty($departure_region_id = (int) arrayGet($items, "0.state"))) {
                    $departure_region = array_filter((array) model('country')->get_state($departure_region_id));
                    $location_parts[] = arrayGet($departure_region, 'state');
                }
                $departure_city = null;
                if (!empty($departure_city_id = (int) arrayGet($items, "0.p_city"))) {
                    $departure_city = model('country')->get_city($departure_city_id);
                    $location_parts[] = arrayGet($departure_city, 'city');
                }

                if (!empty($location_parts)) {
                    $location_parts[] = arrayGet($items, '0.item_zip');
                }

                $location_parts = array_filter($location_parts);
                $location = !empty($location_parts) ? implode(', ', $location_parts) : null;
                //endregion Departure location

                $this->view->assign(array(
					'type'               => $type,
                    'action'             => __SITE_URL . 'shippers/ajax_shippers_operation/send_shipping_estimate',
					'items_info'         => $items,
					'items_quantity'     => $itemsQuantities,
                    'user_info'          => $buyer,
                    'location'           => $location,
                    'company'            => !empty($company_id) ? model('company')->get_seller_base_company($company_id, 'cb.name_company'): null,
                    'states'             => $buyerCountryStates ?? [],
                    'port_country'       => $countriesModel->findAll(['order' => ["`{$countriesModel->getTable()}`.`country`" => 'ASC']]),
                    'city_selected'      => $buyerCity ?? null,
                ));

                $this->view->display('new/shippers/estimates_requests/shipping_estimate_by_items_form_view');

            break;
            case 'show_shipper_response':
                checkPermisionAjaxModal('manage_shipper_estimates');

                $response = model('shipping_estimates')->get_estimate_response((int) $this->uri->segment(4), array(
                    'conditions' => array(
                        'shipper' => (int) my_shipper_company_id()
                    )
                ));

                if (empty($response)) {
                    messageInModal(translate('systmess_error_estimate_response_not_found'));
                }

                $this->view->display('new/shippers/estimates_requests/my/popup_responses_view', array(
                    'response' => $response
                ));
            break;
            case 'show_responses':
                checkPermisionAjaxModal('buy_item');

                /**
                 * @var \Shipping_estimates_Model $estimates
                 */
                $shipping_estimates = model('shipping_estimates');
                $estimate_id = (int) $this->uri->segment(4);
                if (
                    empty($estimate_id) ||
                    empty($estimate = $shipping_estimates->get_estimate($estimate_id))
                ) {
                    messageInModal(translate('systmess_error_shipping_estimate_does_not_exist'));
                }

                if((int) $estimate['id_buyer'] !== (int) privileged_user_id()) {
					messageInModal(translate('systmess_error_estimate_is_not_yours'));
                }

                if (!$shipping_estimates->is_estimate_has_response($estimate_id)) {
                    messageInModal(translate('systmess_error_estimate_has_no_responses'));
                }

                $this->view->display(
                    'new/shippers/estimates_requests/my/responses_list_form_view',
                    array(
                        'estimate' => $estimate_id
                    )
                );
            break;
            case 'view_estimate':
                checkPermisionAjaxModal('buy_item, manage_shipper_estimates');

                /**
                 * @var \Shipping_estimates_Model $estimates
                 */
                $shipping_estimates = model('shipping_estimates');
                $estimate_id = (int) $this->uri->segment(4);
                if (
                    empty($estimate_id) ||
                    empty($estimate = $shipping_estimates->get_estimate($estimate_id, array(
                        'with' => array(
                            'buyer'               => function (RelationInterface $relation) { $relation->getQuery()->select('`idu`, `fname`, `lname`, `user_photo`'); },
                            'departure_country'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
                            'destination_country' => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
                            'departure_state'     => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
                            'destination_state'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
                            'departure_city'      => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
                            'destination_city'    => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
                        )
                    )))
                ) {
                    messageInModal(translate('systmess_error_shipping_estimate_does_not_exist'));
                }

                if((int) $estimate['id_buyer'] !== (int) privileged_user_id() && !check_group_type('Shipper')) {
					messageInModal(translate('systmess_error_estimate_is_not_yours'));
                }

                //region Departure
                $departure_country = arrayGet($estimate, 'departure_country.name');
                $departure_state = arrayGet($estimate, 'departure_state.name');
                $departure_city = arrayGet($estimate, 'departure_city.name');
                $departure_postal_code = arrayGet($estimate, 'postal_code_from');
                $departure_parts = array_filter(array(
                    $departure_city,
                    trim("{$departure_state} {$departure_postal_code}"),
                    $departure_country,
                ));
                $departure = !empty($departure_parts) ? implode(', ', $departure_parts) : '&mdash;';
                //endregion Departure

                //region Destination
                $destination_country = arrayGet($estimate, 'destination_country.name');
                $destination_state = arrayGet($estimate, 'destination_state.name');
                $destination_city = arrayGet($estimate, 'destination_city.name');
                $destination_postal_code = arrayGet($estimate, 'postal_code_to');
                $destination_parts = array_filter(array(
                    $destination_city,
                    trim("{$destination_state} {$destination_postal_code}"),
                    $destination_country,
                ));
                $destination = !empty($destination_parts) ? implode(', ', $destination_parts) : '&mdash;';
                //endregion Destination

                $buyer['full_name'] = arrayGet($estimate, 'buyer.fname') . ' ' . arrayGet($estimate, 'buyer.lname');
                $buyer['link'] = getUserLink($buyer['full_name'], $estimate['id_buyer'], 'Buyer');
                $company = model('company')->get_company(array('id_user' => $estimate['id_seller']));

                $chatBtn = new ChatButton(['recipient' => $company['id_user'], 'recipientStatus' => $company['status']]);
                $company['btnChat'] = $chatBtn->button();

                $this->view->display("new/shippers/estimates_requests/my/details_form_view", array(
                    'destination' => $destination,
                    'departure'   => $departure,
                    'estimate'    => $estimate,
                    'buyer'       => $buyer,
                    'company'     => $company,
                    'items'       => with(json_decode($estimate['items'], true), function($items) {
                        return null !== $items ? $items : array();
                    }),
                ));

            break;
            default:
                messageInModal(translate('sysmtess_provided_path_not_found'));
            break;
        }
    }

    public function ajax_estimates_dt()
    {
        checkIsAjax();
        checkPermisionAjaxDT('manage_shipper_estimates');

        $user_id = (int) privileged_user_id();
        $shipper_id = (int) my_shipper_company_id();
        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $with = array(
            'buyer'               => function (RelationInterface $relation) { $relation->getQuery()->select('`idu`, `fname`, `lname`, `user_photo`'); },
            'departure_country'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
            'destination_country' => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
            'departure_state'     => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
            'destination_state'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
            'departure_city'      => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
            'destination_city'    => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
            'responses'           => function (RelationInterface $relation) use ($shipper_id) {
                $builder = $relation->getQuery();
                $builder->andWhere(
                    $builder->expr()->eq('id_shipper', $builder->createNamedParameter((int) $shipper_id, ParameterType::INTEGER, ':shipperId'))
                );
            },
        );
        $order = array_column(dt_ordering($_POST, array(
            'dt_title'             => 'group_title',
            'dt_date_create'       => 'date_create',
            'dt_date_update'       => 'date_update',
            'estimate'             => 'group_title',
            'created_at'           => 'date_create',
            'updated_at'           => 'date_update',
            'countdown'            => 'max_response_date',
        )), 'direction', 'column');

        $conditions = array_merge(
            array('from_shipper_countries' => $user_id),
            dtConditions($_POST, array(
                array('as' => 'search',                             'key' => 'keywords',       'type' => 'cleanInput|strLimit:500,'),
                array('as' => 'group',                              'key' => 'group_key',      'type' => 'cleanInput|strLimit:255,'),
                array('as' => 'from_country',                       'key' => 'from_country',   'type' => 'int'),
                array('as' => 'from_state',                         'key' => 'from_state',     'type' => 'int'),
                array('as' => 'from_city',                          'key' => 'from_city',      'type' => 'int'),
                array('as' => 'to_country',                         'key' => 'to_country',     'type' => 'int'),
                array('as' => 'to_state',                           'key' => 'to_state',       'type' => 'int'),
                array('as' => 'to_city',                            'key' => 'to_city',        'type' => 'int'),
                array('as' => 'is_saved',                           'key' => 'is_saved',       'type' => 'int'),
                array('as' => 'created_from_date',                  'key' => 'created_from',   'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'created_to_date',                    'key' => 'created_to',     'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'updated_from_date',                  'key' => 'updated_from',   'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'updated_to_date',                    'key' => 'updated_to',     'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'countdown_from_date',                'key' => 'countdown_from', 'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'countdown_to_date',                  'key' => 'countdown_to',   'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'shipper_awaiting_responses',         'key' => 'status',         'type' => fn ($status) => 'new' === $status ? $shipper_id : null),
                array('as' => 'shipper_processed_responses',        'key' => 'status',         'type' => fn ($status) => 'processed' === $status ? $shipper_id : null),
                array('as' => 'shipper_expired_responses',          'key' => 'status',         'type' => fn ($status) => 'expired' === $status ? $shipper_id : null),
            ))
        );

        /** @var Shipping_estimates_Model $estimatesRepository */
        $estimatesRepository = model(Shipping_estimates_Model::class);
        $total = $estimatesRepository->count_shipping_estimates(compact('conditions'));
        $estimates = $estimatesRepository->get_shipping_estimates(compact('with', 'conditions', 'order', 'limit', 'skip'));
        $output = array(
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => array(),
        );

        $statuses = array(
            'new'       => array('title' => 'New',       'icon' => 'ep-icon_new txt-green'),
            'processed' => array('title' => 'Processed', 'icon' => 'ep-icon_ok-circle txt-green'),
            'expired'   => array('title' => 'Expired',   'icon' => 'ep-icon_minus-circle txt-red'),
        );
        $now = new \DateTime();
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);

        foreach ($estimates as $estimate) {
            $seller_id = (int) arrayGet($estimate, 'id_seller');
            $buyer_id = (int) arrayGet($estimate, 'id_buyer');
            $estimate_id = (int) arrayGet($estimate, 'id');
            $estimate_items = null !== ($decoded = json_decode(arrayGet($estimate, 'items'), true)) ? $decoded : array();
            $estimate_countdown = null !== ($countdown = arrayGet($estimate, 'max_response_date')) ? \DateTime::createFromFormat('Y-m-d H:i:s', $countdown) : null;
            $estimate_countdown_status = arrayGet($estimate, 'current_countdown', 'expired');
            $estimate_status = 'processed';

            $expire = strtotime($estimate['max_response_date']) - time();
            if ($expire > 0) {
                $dt_countdown = '<span class="order-status display-b w-100pr tac" data-expire="' . $estimate['max_response_date'] . '">' . $estimate['max_response_date'] . '</span>';
            } else {
                $dt_countdown = '<span class="display-b w-100pr tac txt-red">The time for this request has been expired.</span>';
            }

            $my_response_button = null;
            if (empty($estimate['responses'])) {
                $estimate_status = 'new';
                if (
                    null === $estimate_countdown ||
                    false === $estimate_countdown ||
                    $estimate_countdown <= $now ||
                    'expired' === $estimate_countdown_status
                ) {
                    $estimate_status = 'expired';
                }
            } else {
                $my_response_button_modal_title = 'Response detail';
                $my_response_button_title = 'Response detail';
                $my_response_button_url = __SITE_URL . "shippers/popup_forms/show_shipper_response/" . arrayGet($estimate, 'responses.0.id');
                $my_response_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$my_response_button_url}\"
                        data-title=\"{$my_response_button_modal_title}\">
                        <i class=\"ep-icon ep-icon_info-stroke\"></i>
                        <span>{$my_response_button_title}</span>
                    </a>
                ";
            }

            //region Estimate
            //region Status
            $status_name = translate("shipping_estimates_dashboard_dt_estimate_status_{$estimate_status}_label_text", null, true);
            //endregion Status

            $estimate_title = cleanOutput($estimate['group_title']);
            $estimate_label = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl txt-medium\">
                            {$estimate_title}
                        </div>
                        <div class=\"main-data-table__item-ttl\">
                            {$status_name}
                        </div>
                    </div>
                </div>
            ";
            //endregion Estimate

            //region Comment
            $comment_text = '&mdash;';
            if (!empty($estimate['comment_buyer'])) {
                $comment_text = cleanOutput(strLimit($estimate['comment_buyer'], 300));
            }
            $comment_label = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\">
                        <div>
                            {$comment_text}
                        </div>
                    </div>
                </div>
            ";
            //endregion Comment

            //region Destination
            //region From
            $departure_country = arrayGet($estimate, 'departure_country.name');
            $departure_state = arrayGet($estimate, 'departure_state.name');
            $departure_city = arrayGet($estimate, 'departure_city.name');
            $departure_postal_code = arrayGet($estimate, 'postal_code_from');
            $departure_parts = array_filter(array(
                $departure_city,
                trim("{$departure_state} {$departure_postal_code}"),
                $departure_country,
            ));
            $departure_label = !empty($departure_parts) ? implode(', ', $departure_parts) : '&mdash;';

            $estimate_destination_initial_text = translate("shipping_estimates_dashboard_dt_column_destination_initial_label_text", null, true);
            $estimate_destination_initial_title = translate("shipping_estimates_dashboard_dt_column_destination_initial_label_title", null, true);
            $estimate_destination_initial_label = "
                <div class=\"dtable-params__item\" title=\"{$estimate_destination_initial_title}\">
                    <span>
                        <strong>{$estimate_destination_initial_text}</strong> <span>{$departure_label}</span>
                    </span>
                </div>
            ";
            //endregion From

            //region To
            $finish_country = arrayGet($estimate, 'destination_country.name');
            $finish_state = arrayGet($estimate, 'destination_state.name');
            $finish_city = arrayGet($estimate, 'destination_city.name');
            $finish_postal_code = arrayGet($estimate, 'postal_code_to');
            $finish_parts = array_filter(array(
                $finish_city,
                trim("{$finish_state} {$finish_postal_code}"),
                $finish_country,
            ));
            $finish_label = !empty($finish_parts) ? implode(', ', $finish_parts) : '&mdash;';

            $estimate_destination_final_text = translate("shipping_estimates_dashboard_dt_column_destination_final_label_text", null, true);
            $estimate_destination_final_title = translate("shipping_estimates_dashboard_dt_column_destination_final_label_title", null, true);
            $estimate_destination_final_label = "
                <div class=\"dtable-params__item\" title=\"{$estimate_destination_final_title}\">
                    <span>
                        <strong>{$estimate_destination_final_text}</strong> <span>{$finish_label}</span>
                    </span>
                </div>
            ";
            //endregion To

            $destination_label = "
                <div class=\"dtable__params\">
                    {$estimate_destination_initial_label}
                    {$estimate_destination_final_label}
                </div>
            ";
            //endregion Destination

            //region Actions
            //region Contact seller button
            $contact_seller_button = null;
            if (0 !== $seller_id) {
                $contact_seller_button_text = translate('shipping_estimates_dashboard_dt_button_contact_seller_text', null, true);
                $sellerInfo = $usersRepository->getSimpleUser($seller_id);

                $chatBtnSeller = new ChatButton(['recipient' => $seller_id, 'recipientStatus' => $sellerInfo['status']], ['text' => 'Chat now with seller']);
                $contact_seller_button = $chatBtnSeller->button();
            }

            $contact_buyer_button = null;
            if (0 !== $buyer_id) {
                $contact_buyer_button_text = translate('shipping_estimates_dashboard_dt_button_contact_buyer_text', null, true);
                $buyerInfo = $usersRepository->getSimpleUser($buyer_id);

                $chatBtnBuyer = new ChatButton(['recipient' => $buyer_id, 'recipientStatus' => $buyerInfo['status']], ['text' => 'Chat now with buyer']);
                $contact_buyer_button = $chatBtnBuyer->button();
            }
            //endregion Contact seller button

            $response_button = null;
            if ('new' === $estimate_status) {
                $response_button_modal_title = 'Respond the estimate request';
                $response_button_title = 'Respond the estimate request';
                $response_button_url = __SITE_URL . "shippers/popup_forms/add_estimate_response/{$estimate_id}";
                $response_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$response_button_url}\"
                        data-title=\"{$response_button_modal_title}\">
                        <i class=\"ep-icon ep-icon_envelope\"></i>
                        <span>{$response_button_title}</span>
                    </a>
                ";
            }

            //region Details button
            $details_button_url = __CURRENT_SUB_DOMAIN_URL . "shippers/popup_forms/view_estimate/{$estimate_id}";
            $details_button_text = translate('shipping_estimates_dashboard_dt_button_details_text', null, true);
            $details_button_modal_title = translate('shipping_estimates_dashboard_dt_button_details_modal_title', null, true);
            $details_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$details_button_url}\"
                    data-title=\"{$details_button_modal_title}\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$details_button_text}</span>
                </a>
            ";
            //endregion Details button

            //region Save button
            $save_button = null;
            if (
                'processed' === $estimate_status &&
                0 === (int) $estimate['is_saved']
            ) {
                $save_button_text = translate('general_button_save_text', null, true);
                $save_button_message = translate('shipping_estimates_dashboard_dt_button_save_estimate_message', null, true);
                $save_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-message=\"{$save_button_message}\"
                        data-callback=\"saveEstimate\"
                        data-estimate=\"{$estimate_id}\">
                        <i class=\"ep-icon ep-icon_save\"></i>
                        <span>{$save_button_text}</span>
                    </a>
                ";
            }
            //endregion Save button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions_label = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$my_response_button}
                        {$response_button}
                        {$details_button}
                        {$contact_seller_button}
                        {$contact_buyer_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output['aaData'][] = array(
                "estimate"    => $estimate_label,
                "destination" => $destination_label,
                "comment"     => $comment_label,
                "created_at"  => getDateFormatIfNotEmpty(arrayGet($estimate, 'date_create')),
                "updated_at"  => getDateFormatIfNotEmpty(arrayGet($estimate, 'date_update')),
                "countdown"   => $dt_countdown,
                "actions"     => $actions_label,
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_shippers_dt()
    {
        $this->load->model('Shippers_Model', 'shippers');

        $params = array('limit' => intVal($_POST['iDisplayLength']), 'start' => intVal($_POST['iDisplayStart']));

        $sort_by = flat_dt_ordering($_POST, array(
            'dt_id' => 'os.id',
            'dt_co_name' => 'os.co_name',
            'dt_registered' => 'os.create_date',
            'dt_user' => 'user_name'
        ));

        if(!empty($sort_by)){
            $params['sort_by'] = $sort_by;
        }

        if(isset($_POST['keywords'])){
            $params['keywords'] = cleanInput(cut_str($_POST['keywords']));
        }

        $params['visible'] = 'all';

        $shippers = $this->shippers->get_shippers_detail_by_conditions($params);
        $shippers_count = $this->shippers->count_shippers_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $shippers_count,
            "iTotalDisplayRecords" => $shippers_count,
            "aaData" => array()
        );

		if(empty($shippers))
			jsonResponse('', 'success', $output);

        foreach ($shippers as $ship) {
            $shipper_img_url = getDisplayImageLink(array('{ID}' => $ship['id'], '{FILE_NAME}' => $ship['logo']), 'shippers.main', array( 'thumb_size' => 1 ));

            $editCompanyNameButton = sprintf(
                <<<EDIT_COMANY_NAME_BUTTON
                <a href="%s"
                    class="ep-icon ep-icon_file-edit fancybox.ajax fancyboxValidateModalDT"
                    title="Edit company name"
                    data-title="Edit company name">
                </a>
                EDIT_COMANY_NAME_BUTTON,
                getUrlForGroup("/company/popup_forms/edit_company_name/{$ship['id']}?type=shipper")
            );
            $visible = '<a class="ep-icon ep-icon_visible confirm-dialog" data-callback="change_visibility" title="Change to inactive" data-id="' . $ship['id'] . '" data-message="Are you sure want to change visibility?"></a>';
			if(!$ship['visible']){
				$visible = '<a class="ep-icon ep-icon_invisible confirm-dialog" data-callback="change_visibility" title="Change to active" data-id="' . $ship['id'] . '" data-message="Are you sure want to change visibility?"></a>';
			}

            //TODO: admin chat hidden
            $chatBtn = new ChatButton(['hide' => true, 'recipient' => $ship['id_user'], 'recipientStatus' => $ship['user_status']], ['text' => '', 'classes' => 'btn-chat-now']);

            $output['aaData'][] = array(
                'dt_id' => $ship['user_status'],
                'dt_logo' => '<img class="w-80" src="' . $shipper_img_url . '" alt="Logo"/>',
                'dt_co_name' => '<div class="tal">'
								.'<a class="ep-icon ep-icon_building" title="'.$ship['co_name'].'" href="'.__SITE_URL.'shipper/'.strForUrl($ship['co_name'] .' '. $ship['id']).'" aria-describedby="ui-tooltip-0"></a>'
								.'</div>'
                				.'<div class="clearfix tal"><strong class="pull-left lh-16 pr-5">Title: </strong>'.$ship['co_name'].'</div>',
                'dt_user' => "<div class=\"tal\">{$chatBtn->button()}</div>"
							  .$ship['user_name'],
                'dt_phone' => $ship['phone_code'].' '.$ship['phone'],
                'dt_fax' => $ship['fax_code'].' '.$ship['fax'],
                'dt_email' => $ship['email'],
                'dt_registered' => formatDate($ship['create_date']),
                'dt_actions' => [
                    // '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" href="shippers/popup_forms/edit_shipper/'.$upload_folder.'/' . $ship['id'] . '" data-title="Edit freight forwarder" title="Edit this category blog"></a>',
                    $editCompanyNameButton,
                    $visible
                ]
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_shippers_operation()
    {
        isAjaxRequest();
        checkIsLoggedAjax();

        $this->load->model('Shippers_model', 'shipper');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_estimate_response':
                checkPermisionAjaxModal('manage_shipper_estimates');

                $validator_rules = array(
                    array(
                        'field' => 'delivery_from',
                        'label' => 'Delivery days from',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[1000]' => ''),
                    ),
                    array(
                        'field' => 'delivery_to',
                        'label' => 'Delivery days to',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[1000]' => ''),
                    ),
                    array(
                        'field' => 'price',
                        'label' => 'Price',
                        'rules' => array('required' => '', 'number' => '', 'min[1]' => '', 'max[9999999999]' => ''),
                    ),
                    array(
                        'field' => 'comments',
                        'label' => 'Comments',
                        'rules' => array('max_len[500]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $estimates = model('shipping_estimates');
                $shipper_id = my_shipper_company_id();
                $estimate_id = (int) arrayGet($_POST, 'estimate');
                if (
                    empty($estimate_id) ||
                    empty($estimate = $estimates->get_estimate($estimate_id, array(
                        'columns' => array("*", "if(`max_response_date` > NOW(), 'active', 'expired') as `current_countdown`")
                    )))
                ) {
                    jsonResponse(translate('systmess_error_shipping_estimate_does_not_exist'));
                }

                if ($estimates->is_estimate_has_shipper_response($shipper_id, $estimate_id)) {
                    jsonResponse(translate('systmess_error_already_responded_to_estimate'));
                }

                if ('expired' === arrayGet($estimate, 'current_countdown', 'expired')) {
                    jsonResponse(translate('systmess_error_cannot_respond_to_expired_estimate'));
                }

                if (!$estimates->create_estimate_response($shipper_id, $estimate_id, array(
                    'delivery_days_from' => (int) arrayGet($_POST, 'delivery_from', 1),
                    'delivery_days_to'   => (int) arrayGet($_POST, 'delivery_to', 1),
                    'comment'            => cleanInput(library('cleanhtml')->sanitize(arrayGet($_POST, 'comment', ''))),
                    'price'              => arrayGet($_POST, 'price', 0),
                ))) {
                    jsonResponse(translate('systmess_error_failed_to_add_response_for_estimate'));
                }

                $buyer_id = (int) $estimate['id_buyer'];
                $estimate_number = orderNumber($estimate_id);
                $estimate_slug = strForURL(cut_str_with_dots($estimate['group_title'], 100));
                $estimate_url = __SITE_URL . "shippers/estimates_requests/group/{$estimate['group_key']}/{$estimate_slug}";
                $estimates_url = __SITE_URL . "shippers/estimates_requests";
                $shipper_name = cleanOutput(my_company_name());
                $shipper_url = __SITE_URL . "usr/" . strForURL("{$shipper_name} {$shipper_id}");

				model('notify')->send_notify([
					'mess_code' => 'shipping_estimate_response_created',
					'id_users'  => [$buyer_id],
					'systmess'  => true,
					'replace'   => [
						'[SHIPPING_ESTIMATE_ID]'    => $estimate_number,
						'[SHIPPING_ESTIMATE_LINK]'  => $estimate_url,
						'[SHIPPER_NAME]'            => $shipper_name,
						'[SHIPPER_LINK]'            => $shipper_url,
						'[LINK]'                    => $estimates_url
					],
				]);

                jsonResponse(translate('systmess_success_response_for_shipping_estimate_added'), 'success');

            break;
            case 'change_visibility':
                $id = intVal($_POST['id_shipper']);

				$shipper = $this->shipper->get_shipper_details($id);
				if(empty($shipper)){
					jsonResponse('Error: This freight forwarder does not exist.');
				}

				if($shipper['visible'] == 1){
					$update = array(
						'visible' => 0
					);
				} else{
					$update = array(
						'visible' => 1
					);
				}

				$this->shipper->update_shipper($update, $id);
				jsonResponse('The freight forwarder visibility has been changed.', 'success');
            break;
            case 'edit_shipper': // @deprecated
                jsonResponse('This action is deprecated');
                if(!have_right('edit_company')) {
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

				$validator_rules = array(
					array(
						'field' => 'co_name',
						'label' => 'Company name',
						'rules' => array('required' => '', 'max_len[100]' => '')
					),
					array(
						'field' => 'company_offices_number',
						'label' => 'Number of Office Locations',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'country',
						'label' => 'Country',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'port_city',
						'label' => 'City',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'address',
						'label' => 'Address',
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'zip',
						'label' => 'ZIP',
						'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
					),
					array(
						'field' => 'phone',
						'label' => 'Phone',
						'rules' => array('required' => '', 'max_len[25]' => '')
					),
					array(
						'field' => 'company_teu',
						'label' => 'Annual full container load volume (TEU\'s)',
						'rules' => array('required' => '','natural' => '')
					)

				);

				if(!empty($_POST['website'])){
					$validator_rules[] = array(
						'field' => 'website',
						'label' => 'Website',
						'rules' => array('required' => '', 'valid_url'=>'')
					);
				}

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$id_shipper = (int)$_POST['id_shipper'];

				$this->load->model('Shippers_Model', 'shippers');
				if(!$this->shippers->exist_shipper(array('id_shipper' => $id_shipper))){
					jsonResponse('Error: This freight forwarder does not exist.');
				}

				$this->load->library('Cleanhtml', 'clean');
				$this->clean->allowIframes();
				$update = array(
					'co_name' => cleanInput($_POST['co_name']),
					'id_country' => intVal($_POST['country']),
					'id_state' => intVal($_POST['states']),
					'id_city' => intVal($_POST['port_city']),
					'address' => cleanInput($_POST['address']),
					'zip' => cleanInput($_POST['zip']),
					'phone' => cleanInput($_POST['phone']),
					'offices_number' => intVal($_POST['company_offices_number']),
					'co_teu' => intVal($_POST['company_teu']),
					'description' => cleanInput($_POST['description']),
					'video' => $this->clean->sanitizeUserIframe($_POST['video']),
					'fax' => '',
					'tax_id' => '',
					'co_website' => '',
					'co_duns' => ''
				);

				if(!empty($_POST['fax']))
					$update['fax'] = cleanInput($_POST['fax']);

				if(!empty($_POST['company_tax_id']))
					$update['tax_id'] = cleanInput($_POST['company_tax_id']);

				if(!empty($_POST['website']))
					$update['co_website'] = cleanInput($_POST['website']);

				if(!empty($_POST['company_duns']))
					$update['co_duns'] = cleanInput($_POST['company_duns']);

				$this->shippers->delete_relation_industry_by_company($id_shipper);

				if(!empty($_POST['industries']))
					$this->shippers->set_relation_industry($id_shipper, $_POST['industries']);

				$this->shippers->update_shipper($update, $id_shipper);

				jsonResponse('Your changes have been saved.','success');
            break;
            case 'partnership':
                checkPermisionAjax('sell_item');

            	$id_shipper = intval($_POST['shipper']);
            	$id_seller = privileged_user_id();

				if (!$id_shipper) {
    				jsonResponse(translate('systmess_error_invalid_data'));
                }

                if(userStatus() !== \App\Common\Contracts\User\UserStatus::ACTIVE()){
                    jsonResponse(translate('systmess_info_partnership_request_active_status'), 'info');
                }

				if ($this->shipper->exist_partnership(array('id_seller' => $id_seller, 'id_shipper' => $id_shipper))) {
					jsonResponse(translate('systmess_info_partnership_request_already_exist'), 'info');
                }

				if (!$this->shipper->insert_partnership(array('id_shipper' => $id_shipper, 'id_seller' => $id_seller))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $company_info = model('company')->get_company(array('id_user' => $id_seller, 'type_company' => 'company'));

				$data_systmess = [
					'mess_code' => 'shipping_partnership_init',
					'id_users'  => [$id_shipper],
					'replace'   => [
						'[COMPANY_NAME]' => cleanOutput($company_info['name_company']),
						'[COMPANY_LINK]' => getCompanyURL($company_info),
						'[LINK]'         => __SHIPPER_URL . 'b2b/my_requests'
					],
					'systmess' => true
				];


                model('notify')->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_sent_partnership_request'), 'success');

			break;
            case 'remove_partnership':
                checkPermisionAjax('sell_item');

				$id_shipper = intval($_POST['shipper']);
				$id_partner = intval($_POST['partner']);
				$id_seller = privileged_user_id();

                if (!$id_shipper || !$id_partner || !model('shippers')->exist_partnership(array('id_partner' => $id_partner, 'id_seller' => $id_seller, 'id_shipper' => $id_shipper))) {
                    jsonResponse('systmess_error_invalid_data');
                }

				$company_info = model('company')->get_company(array('id_user' => $id_seller, 'type_company' => 'company'));

				if (!$this->shipper->delete_partnership($id_partner)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

				$data_systmess = [
					'mess_code' => 'shipping_partnership_deleted',
					'id_users'  => [$id_shipper],
					'replace'   => [
						'[COMPANY_NAME]' => cleanOutput($company_info['name_company']),
						'[COMPANY_LINK]' => getCompanyURL($company_info),
						'[LINK]'         => __SHIPPER_URL . 'b2b/partners'
					],
					'systmess' => true
				];


                model('notify')->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_removed_partnership'), 'success');

			break;
			case 'ishipper_partnership':
                checkPermisionAjax('manage_shipping_settings');

                // Get the request
                $request = request();

                //region Shippers
                $rawShipperId = $request->request->get('shipper');
                if (null == $rawShipperId || !is_numeric($rawShipperId) || (string) ($shipperId = (int) $rawShipperId) !== $rawShipperId) {
                    jsonResponse(translate('systmess_error_invalid_data'), null, null, 400);
                }
                /** @var Ishippers_Model $shippersRepository */
                $shippersRepository = model(Ishippers_Model::class);
                if (!$shipperId || empty($shipper = $shippersRepository->get_shipper($shipperId))) {
                    jsonResponse(translate('systmess_error_invalid_data'), null, null, 404);
                }
                //endregion Shippers

                //region Partnership
                $sellerId = privileged_user_id();
                $partnership = $shippersRepository->get_partnership($shipperId, $sellerId);
                if (!empty($partnership)) {
                    $shippersRepository->delete_partnership($partnership['id_partner']);

                    jsonResponse('Success: The parthership has been removed.', 'success', ['action' => 'cancel']);
                } else {
                    $shippersRepository->insert_partnership(['id_shipper' => $shipperId, 'id_seller' => $sellerId]);

                    jsonResponse('Success: The parthership has been confirmed.', 'success', ['action' => 'confirm']);
                }
                //endregion Partnership
			break;
			// SHIPPING
            case 'delete_estimate':
                checkPermisionAjax('buy_item');

                /**
                 * @var \Shipping_estimates_Model $shipping_estimates
                 */
                $shipping_estimates = model('shipping_estimates');
                $estimate_id = (int) arrayGet($_POST, 'id');
                $user_id = (int) privileged_user_id();
                if(
                    empty($estimate_id) ||
                    empty($estimate = $shipping_estimates->get_estimate($estimate_id))
                ) {
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

				if((int) $estimate['id_buyer'] !== $user_id) {
                    jsonResponse(translate('systmess_error_estimate_is_not_yours'));
                }

                if ($shipping_estimates->is_estimate_has_response($estimate_id)) {
                    jsonResponse(translate('systmess_error_estimate_cannot_be_deleted'));
                }

                if(!$shipping_estimates->delete_estimate($estimate_id)) {
                    jsonResponse(translate('systmess_error_cannot_delete_shipping_estimate'));
                }

                jsonResponse(translate('systmess_success_shipping_estimate_deleted'), 'success');

			break;
            case 'save_for_order':
                checkPermisionAjax('buy_item');

                /**
                 * @var \Shipping_estimates_Model   $shipping_estimates
                 */
                $shipping_estimates = model('shipping_estimates');
                $estimate_id = (int) arrayGet($_POST, 'id');
                $user_id = (int) privileged_user_id();
                if(
                    empty($estimate_id) ||
                    empty($estimate = $shipping_estimates->get_estimate($estimate_id, array(
                        'with' => array(
                            'responses' => function (RelationInterface $relation) {
                                $relation
                                    ->getQuery()
                                        ->select("`id_estimate`, COUNT(`id_shipper`) as `shippers`")
                                        ->groupBy('id_estimate')
                                ;
                            },
                        )
                    )))
                ) {
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                if((int) $estimate['id_buyer'] !== $user_id) {
                    jsonResponse(translate('systmess_error_estimate_is_not_yours'));
                }

                if((int) $estimate['is_saved']) {
					jsonResponse(translate('systmess_error_shipping_estimate_has_already_been_saved'));
                }

                if (!$shipping_estimates->is_estimate_has_response($estimate_id)) {
                    jsonResponse(translate('systmess_error_shipping_estimate_must_have_a_response'));
                }

                if (!$shipping_estimates->update_estimate($estimate_id, array('is_saved' => 1))) {
                    jsonResponse(translate('systmess_error_cannot_saved_this_request'));
                }

                jsonResponse(translate('systmess_success_request_has_been_saved'), 'success');

            break;
            case 'renew_estimate':
                checkPermisionAjax('buy_item');

                /**
                 * @var \Shipping_estimates_Model $shipping_estimates
                 */
                $shipping_estimates = model('shipping_estimates');
                $estimate_id = (int) arrayGet($_POST, 'id');
                $user_id = (int) privileged_user_id();
                if(
                    empty($estimate_id) ||
                    empty($estimate = $shipping_estimates->get_estimate($estimate_id))
                ) {
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                if((int) $estimate['id_buyer'] !== (int) privileged_user_id()) {
					jsonResponse(translate('systmess_error_estimate_is_not_yours'));
                }

                if ($shipping_estimates->is_estimate_has_response($estimate_id)) {
                    jsonResponse(translate('systmess_error_estimate_cannot_be_renewed'));
                }

                $estimate_status = 'new';
                $estimate_countdown = arrayGet($estimate, 'max_response_date');
                $estimate_countdown = null !== $estimate_countdown ? \DateTime::createFromFormat('Y-m-d H:i:s', $estimate_countdown) : null;
                $estimate_countdown_status = arrayGet($estimate, 'current_countdown', 'expired');
                if (
                    null === $estimate_countdown ||
                    false === $estimate_countdown ||
                    $estimate_countdown <= new \DateTime() ||
                    'expired' === $estimate_countdown_status
                ) {
                    $estimate_status = 'expired';
                }

                if('expired' !== $estimate_status) {
                    jsonResponse(translate('systmess_error_estimated_is_not_expired'));
                }

                if(!$shipping_estimates->update_estimate($estimate_id, array(
                    'max_response_date' => date_plus(config('max_response_days'))
                ))) {
                    jsonResponse(translate('systmess_error_cannot_renew_estimate'));
                }

                jsonResponse(translate('systmess_success_estimate_has_been_renewed'), 'success');
            break;
            case 'send_shipping_estimate':
                checkPermisionAjax('buy_item');

				$validator_rules = array(
					array(
						'field' => 'port_country_to',
						'label' => 'Shipping to country',
						'rules' => array('required' => '', 'integer' => '')
					),
                    array(
                        'field' => 'states_to',
                        'label' => 'Shipping to state',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
					array(
						'field' => 'port_city_to',
						'label' => 'Shipping to city',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'zip_code_to',
						'label' => 'Shipping to zip',
						'rules' => array('required' => '', 'max_len[20]' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Request type',
						'rules' => array('required' => '', 'string' => '')
                    ),
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '','max_len[250]' => '')
					),
					array(
						'field' => 'comments',
						'label' => 'Comments',
						'rules' => array('max_len[500]' => '')
                    ),
                    array(
						'field' => 'item_quantity',
						'label' => 'Items quantity',
						'rules' => [
                            function (string $attr, $itemQuantity, callable $fail, TinyMVC_Library_validator $validator) {
                                //If the quantity is 0, we expect to get an error about the minimum quantity and not about the required field #13303
                                if (!$validator->min($itemQuantity, 1)) {
                                    $fail(sprintf($validator->get_rule_message('min'), $attr, 1));

                                    return;
                                }

                                if (empty($itemQuantity)) {
                                    $fail(sprintf($validator->get_rule_message('required'), $attr));
                                }

                                if (!$validator->integer($itemQuantity)) {
                                    $fail(sprintf($validator->get_rule_message('integer'), $attr));

                                    return;
                                }
                            },
                        ]
					),
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }
				if(!in_array($_POST['type'], array('item', 'basket'))){
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                //region Location
                // Resolve if "From" and "To" locations are valid
                $to_postal_code = arrayGet($_POST, 'zip_code_to');
                $to_country_id = (int) arrayGet($_POST, 'port_country_to');
                $to_state_id = (int) arrayGet($_POST, 'states_to');
                $to_city_id = (int) arrayGet($_POST, 'port_city_to');
                $countries = model('country')->get_simple_countries($to_country_id);
                $states = model('country')->get_simple_states($to_state_id);
                $cities = model('country')->get_simple_cities($to_city_id);
                if (null === arrayGet($countries, $to_country_id)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                if (null === arrayGet($states, $to_state_id)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                if (null === arrayGet($cities, $to_city_id)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                //endregion Location

                //region Items
                $items_ids = array_keys(arrayGet($_POST, 'item_quantity', array()));
                if(empty($items_ids)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                $items_list = model('items')->get_items_simple($items_ids, implode(', ', array(
                    'id',
                    'id_cat',
                    'id_seller',
                    'title',
                    'weight',
                    'state',
                    'p_city',
                    'p_country',
                    'item_zip',
                    'item_width',
                    'item_height',
                    'item_length',
                    'item_categories',
                )));
                if(empty($items_ids)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                if(count($items_ids) !== count($items_list)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $items = array();
                $buyer_id = (int) id_session();
                $seller_id = (int) arrayGet($items_list, '0.id_seller');
				foreach($items_list as &$item) {
					if((int) $item['id_seller'] !== $seller_id) {
						jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $categories = array($item['id_cat']);
                    if(!empty($item['item_categories'])) {
                        $categories = array_merge($categories, explode(',', $item['item_categories']));
                    }

                    $items[] = array(
                        'id'              => (int) $item['id'],
                        'id_cat'          => (int) $item['id_cat'],
                        'title'           => $item['title'],
                        'state'           => (int) $item['state'],
                        'p_city'          => (int) $item['p_city'],
                        'p_country'       => (int) $item['p_country'],
                        'item_zip'        => $item['item_zip'],
                        'quantity'        => (int) arrayGet($_POST, "item_quantity.{$item['id']}", 1),
                        'weight'          => (double) $item['weight'],
                        'item_width'      => (double) $item['item_width'],
                        'item_height'     => (double) $item['item_height'],
                        'item_length'     => (double) $item['item_length'],
                        'item_categories' => array_map('intval', $categories),
                    );
                }
                //endregion Items

                //region Key
                $group_key = getBacketItemsKey($seller_id, array_column($items, 'id'));
                if(null === $group_key) {
                    jsonResponse(translate('systmess_error_create_estimate'));
                }
                //endregion Key

				$estimate = array(
                    'id_buyer'          => $buyer_id,
                    'id_seller'         => $seller_id,
                    'id_country_from'   => arrayGet($items, '0.p_country'),
                    'id_country_to'     => $to_country_id,
                    'id_state_from'     => arrayGet($items, '0.state'),
                    'id_state_to'       => $to_state_id,
                    'id_city_from'      => arrayGet($items, '0.p_city'),
                    'id_city_to'        => $to_city_id,
                    'postal_code_from'  => arrayGet($items, '0.item_zip'),
                    'postal_code_to'    => $to_postal_code,
                    'items'             => $items,
                    'type'              => cleanInput($_POST['type']),
                    'group_key'         => $group_key,
                    'group_title'       => cleanInput($_POST['title']),
                    'comment_buyer'     => cleanInput($_POST['comments']),
                    'max_response_date' => date_plus(config('max_response_days')),
                );
				if (!model('shipping_estimates')->create_estimate($estimate)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_create_estimate'), 'success');

			break;
            default:
                jsonResponse(translate('sysmtess_provided_path_not_found'));
            break;
        }
    }

    public function ajax_get_saved()
    {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model("Shippers_Model", 'shippers');

		$data = array(
			'curr_page' => abs(intVal($_POST['page'])),
			'per_page' => 8
		);

		$data['counter'] = $this->shippers->get_saved_shippers_count(array('id_shipper' => id_session()));
		$shippers = $this->shippers->get_saved_shippers_by_conditions(id_session(), array('page' => $data['curr_page'], 'per_p' => $data['per_page']));

        $data['shippers'] = [];
		if (!empty($shippers)) {
            $data['shippers'] = array_map(
                function ($shippersItem) {
                    $chatBtn = new ChatButton(['recipient' => $shippersItem['id_user'], 'recipientStatus' => $shippersItem['status']]);
                    $shippersItem['btnChat'] = $chatBtn->button();
                    return $shippersItem;
                },
                $shippers
            );
        }

		$content = $this->view->fetch('new/nav_header/saved/shippers_header_list_view', $data);

		jsonResponse($content, 'success', array('counter' => $data['counter']));
	}

    public function ajax_shipping_requests_list_dt()
    {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

        $this->load->model('Shippers_Model', 'shippers');

		$id_order = (int) $this->uri->segment(3);
		$params = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart']),
			'order' => $id_order
		);

		if ($_POST['iSortingCols'] > 0) {
			for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
				switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
					case 'dt_id':
						$params['sort_by'][] = 'id_quote-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_countdown':
						$params['sort_by'][] = 'max_response_date-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_shipper_name':
						$params['sort_by'][] = 'co_name-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_shipping_price':
						$params['sort_by'][] = 'shipping_price-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_delivery_time':
						$params['sort_by'][] = 'delivery_time-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_status':
						$params['sort_by'][] = 'status-' . $_POST['sSortDir_' . $i];
					break;
				}
			}
		}

		if(!empty($_POST['status']))
			$params['status'] = cleanInput($_POST['status']);

		$this->load->model('Orders_model', 'orders');
		$id_user = privileged_user_id();
		if(!$this->orders->isMyOrder($id_order, $id_user)){
			jsonDTResponse(translate("systmess_error_sended_data_not_valid"));
		}

		$order = $this->orders->get_order($id_order);
		$quotes = $this->shippers->get_quotes($order['id_seller'], $params);
		$quotes_counter = $this->shippers->get_quotes_count($order['id_seller'], $params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $quotes_counter,
			"iTotalDisplayRecords" => $quotes_counter,
			"aaData" => array()
		);

		if(empty($quotes)){
			jsonResponse('', 'success', $output);
		}

		$statuses = array(
			'new' => array(
				'icon' => 'ep-icon ep-icon_new txt-blue',
				'title' => 'New'
			),
			'accepted' => array(
				'icon' => 'ep-icon ep-icon_ok txt-green',
				'title' => 'Accepted'
			),
			'confirmed' => array(
				'icon' => 'ep-icon ep-icon_ok-circle txt-green',
				'title' => 'Confirmed'
			),
			'declined' => array(
				'icon' => 'ep-icon ep-icon_remove-circle txt-red',
				'title' => 'Declined'
			),
			'expired' => array(
				'icon' => 'ep-icon ep-icon_hourglass-timeout txt-red',
				'title' => 'Expired'
			)
		);

		foreach ($quotes as $quote) {
			$expire = strtotime($quote['max_response_date']) - time();
			$dt_status = '<div class="pull-left"><a class="dt_filter pull-left ep-icon ep-icon_filter txt-green" title="Filter by status" data-value="'.$quote['status'].'" data-name="status" data-title="Status" data-value-text="'.ucfirst($quote['status']).'"></a></div><div class="clearfix"></div><span><i class="'.$statuses[$quote['status']]['icon'].' fs-30"></i><br> '.$statuses[$quote['status']]['title'].'</span>';
			if($expire > 0){
				$dt_countdown = '<span class="order-status display-b w-100pr tac" data-expire="' . $quote['max_response_date'] . '">' . $quote['max_response_date'] . '</span>';
			} else{
				$dt_countdown = '<span class="display-b w-100pr tac txt-red">The time for this request has been expired.</span>';
				if($quote['status'] == 'new'){
					$dt_status = '<span><i class="'.$statuses['expired']['icon'].' fs-30"></i><br> '.$statuses['expired']['title'].'</span>';
				}
			}

			$actions = '';
			if($quote['order_status_alias'] == 'invoice_confirmed'){
				if($quote['status'] == 'new'){
					if(have_right('manage_seller_orders')){
						if($expire > 0){
							$actions .= '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to decline this request?" title="Decline request" href="#" data-callback="change_status" data-request="'.$quote['id_quote'].'" data-status="declined"></a>';
						} else{
							$actions .= '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to delete this request?" title="Delete request" href="#" data-callback="change_status" data-request="'.$quote['id_quote'].'" data-status="declined"></a>';
						}
					} else{
						$actions .= '<span class="ep-icon ep-icon_hourglass-processing txt-orange fs-30" title="Waiting response from shipping company"></span>';
					}
				}
				if($quote['status'] == 'accepted' && have_right('buy_item')){
					$actions .= '<a class="ep-icon ep-icon_ok-circle txt-green confirm-dialog" data-message="Are you sure you want to assign this freight forwarder to the order?" title="Assign the freight forwarder to the order" href="#" data-callback="change_status" data-request="'.$quote['id_quote'].'" data-status="confirmed"></a>';
				}
			}

            $shipper_logo = getDisplayImageLink(array('{ID}' => $quote['id_shipper_company'], '{FILE_NAME}' => $quote['logo']), 'shippers.main', array( 'thumb_size' => 1 ));

            $output['aaData'][] = array(
				'dt_id' => '<a rel="quote_details" class="ep-icon ep-icon_plus m-0" title="View details"></a>',
				'dt_shipper_logo' => '<img class="mw-80 mh-80" src="'. $shipper_logo .'" alt="logo">',
				'dt_shipper_name' => $quote['co_name'],
				'dt_shipping_price' => get_price($quote['shipping_price']),
				'dt_delivery_time' => ($quote['delivery_time'] ? $quote['delivery_time'].' days' : '---'),
				'dt_comment_user' => ($quote['comment_user'] ? $quote['comment_user'] : '---'),
				'dt_comment_shipper' => ($quote['comment_shipper'] ? $quote['comment_shipper'] : '---'),
				'dt_status' => $dt_status,
				'dt_create_date' => formatDate($quote['create_date']),
				'dt_countdown' => $dt_countdown,
				'dt_actions' => $actions,
			);
		}

		jsonResponse('', 'success', $output);
    }

    public function ajax_partners_list_dt()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonDTResponse(translate("systmess_error_should_be_logged"));
		}

		if(!have_right('manage_b2b_requests')) {
			jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
		}

		$this->load->model('Shippers_Model', 'shippers');
		$this->load->model('Country_Model', 'country');

		$user_params = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart']),
			'are_partners' => 1,
			'id_seller' => id_session()
		);

		$sort_by = flat_dt_ordering($_POST, array(
			'address_dt'=> 'os.co_name',
			'email_dt' 	=> 'os.email',
			'phone_dt' 	=> 'os.phone',
			'date_dt' 	=> 'sp.date_partner',
		));

		if(!empty($sort_by)){
			$user_params['sort_by'] = $sort_by;
		}

		$user_params['count'] = $this->shippers->count_seller_shipper_partners($user_params);
		$data['partners'] = $this->shippers->get_seller_shipper_partners($user_params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" =>  $user_params['count'],
			"iTotalDisplayRecords" => $user_params['count'],
			"aaData" => array()
		);

		if (empty($data['partners'])) {
			jsonResponse('', 'success', $output);
		}

		$output['aaData'] = $this->_my_partners($data);

		jsonResponse('', 'success', $output);
    }

    public function ajax_estimates_requests_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('buy_item');

        $buyer_id = (int) privileged_user_id();
        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $with = array(
            'departure_country'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
            'destination_country' => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `country` as `name`, `country_alias` as `alias`"); },
            'departure_state'     => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
            'destination_state'   => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `state` as `name`, `state_name` as `alias`"); },
            'departure_city'      => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
            'destination_city'    => function (RelationInterface $relation) { $relation->getQuery()->select("`id`, `city` as `name`, `city` as `alias`"); },
            'responses'           => function (RelationInterface $relation) {
                $relation
                    ->getQuery()
                        ->select("`id_estimate`, COUNT(`id_shipper`) as `shippers`")
                        ->groupBy('id_estimate')
                ;
            },
        );
        $order = array_column(dt_ordering($_POST, array(
            'dt_title'             => 'group_title',
            'dt_date_create'       => 'date_create',
            'dt_date_update'       => 'date_update',
            'estimate'             => 'group_title',
            'created_at'           => 'date_create',
            'updated_at'           => 'date_update',
        )), 'direction', 'column');
        $conditions = array_merge(
            array('buyer' => $buyer_id),
            dtConditions($_POST, array(
                array('as' => 'search',              'key' => 'keywords',       'type' => 'cleanInput|strLimit:500,'),
                array('as' => 'group',               'key' => 'group_key',      'type' => 'cleanInput|strLimit:255,'),
                array('as' => 'from_country',        'key' => 'from_country',   'type' => 'int'),
                array('as' => 'from_state',          'key' => 'from_state',     'type' => 'int'),
                array('as' => 'from_city',           'key' => 'from_city',      'type' => 'int'),
                array('as' => 'to_country',          'key' => 'to_country',     'type' => 'int'),
                array('as' => 'to_state',            'key' => 'to_state',       'type' => 'int'),
                array('as' => 'to_city',             'key' => 'to_city',        'type' => 'int'),
                array('as' => 'is_saved',            'key' => 'is_saved',       'type' => 'int'),
                array('as' => 'created_from_date',   'key' => 'created_from',   'type' => 'formatDate:Y-m-d'),
                array('as' => 'created_to_date',     'key' => 'created_to',     'type' => 'formatDate:Y-m-d'),
                array('as' => 'updated_from_date',   'key' => 'updated_from',   'type' => 'formatDate:Y-m-d'),
                array('as' => 'updated_to_date',     'key' => 'updated_to',     'type' => 'formatDate:Y-m-d'),
                array('as' => 'countdown_from_date', 'key' => 'countdown_from', 'type' => 'formatDate:Y-m-d'),
                array('as' => 'countdown_to_date',   'key' => 'countdown_to',   'type' => 'formatDate:Y-m-d'),
                array('as' => 'awaiting_responses',  'key' => 'status',         'type' => function ($status) {
                    return 'new' === $status ? true : null;
                }),
                array('as' => 'processed_responses', 'key' => 'status',         'type' => function ($status) {
                    return 'processed' === $status ? true : null;
                }),
                array('as' => 'expired_responses',   'key' => 'status',         'type' => function ($status) {
                    return 'expired' === $status ? true : null;
                }),
            ))
        );

        $total = model('shipping_estimates')->count_shipping_estimates(compact('conditions'));
        $estimates = model('shipping_estimates')->get_shipping_estimates(compact('with', 'conditions', 'order', 'limit', 'skip'));
        $output = array(
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => array(),
        );

		if(!empty($estimates)) {
            $output['aaData'] = $this->my_estimates_requests($estimates);
        }

		jsonResponse('', 'success', $output);
    }

    public function ajax_estimates_responses_list_dt()
    {
        checkPermisionAjaxModal('buy_item');

        /**
         * @var \Shipping_estimates_Model $estimates
         */
        $estimates = model('shipping_estimates');
        $estimate_id = (int) $this->uri->segment(3);
        if (
            empty($estimate_id) ||
            empty($estimate = $estimates->get_estimate($estimate_id))
        ) {
            jsonResponse(translate('systmess_error_shipping_estimate_does_not_exist'));
        }

        $buyer_id = (int) privileged_user_id();
        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $with = array(
            'shipper' => function(RelationInterface $relation) {
                $relation->getQuery()->select("`id`, `id_user` as `user_id`, `co_name` as `company`, `email`, `phone_code`, `phone`, `address`, `zip`, `logo`");
            }
        );
        $order = array_column(dt_ordering($_POST, array(
            'dt_price'       => 'price',
            'dt_responed_at' => 'created_at',
        )), 'direction', 'column');
        $conditions = array('request' => $estimate_id);

        $total = $estimates->count_estimate_responses(compact('conditions'));
        $responses = $estimates->get_estimate_responses(compact('with', 'conditions', 'order', 'limit', 'skip'));
        $output = array(
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => array(),
        );

        if(!empty($responses)) {
            $output['aaData'] = $this->my_estimate_request_responses($responses);
        }

		jsonResponse('', 'success', $output);
    }

    public function ajax_rates_requests_list_dt()
    {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model('Shippers_Model', 'shippers');

		$id_user = privileged_user_id();

		$params = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart'])
		);

		if ($_POST['iSortingCols'] > 0) {
			for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
				switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
					case 'dt_id':
						$params['sort_by'][] = 'id_order-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_countdown':
						$params['sort_by'][] = 'max_response_date-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_shipper_name':
						$params['sort_by'][] = 'co_name-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_shipping_price':
						$params['sort_by'][] = 'shipping_price-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_delivery_time':
						$params['sort_by'][] = 'delivery_time-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_status':
						$params['sort_by'][] = 'status-' . $_POST['sSortDir_' . $i];
					break;
				}
			}
		}

		if(!empty($_POST['status']))
			$params['status'] = cleanInput($_POST['status']);

		if(!empty($_POST['order']))
			$params['order'] = toId($_POST['order']);

		if(!empty($_POST['countdown_from']))
			$params['countdown_from'] = formatDate($_POST['countdown_from'], 'Y-m-d');

		if(!empty($_POST['countdown_to']))
			$params['countdown_to'] = formatDate($_POST['countdown_to'], 'Y-m-d');

		if(!empty($_POST['start_from']))
			$params['start_from'] = formatDate($_POST['start_from'], 'Y-m-d');

		if(!empty($_POST['start_to']))
			$params['start_to'] = formatDate($_POST['start_to'], 'Y-m-d');

		if(!empty($_POST['request_number']))
			$params['request_number'] = toId($_POST['request_number']);

		$data['quotes'] = $this->shippers->get_quotes($id_user, $params);
		$quotes_counter = $this->shippers->get_quotes_count($id_user, $params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $quotes_counter,
			"iTotalDisplayRecords" => $quotes_counter,
			"aaData" => array()
		);

		if(empty($data['quotes']))
			jsonResponse('', 'success', $output);

		if($this->is_pc)
			$output['aaData'] = $this->_my_rates_requests_pc($data);
		else
			$output['aaData'] = $this->_my_rates_requests_tablet($data);

		jsonResponse('', 'success', $output);
	}

    private function _my_partners($data)
    {
		$output = $partners = array();
		$names_state_cities = '';

		extract($data);

		foreach ($partners as $item) {
			$list_countries[$item['id_country']] = $item['id_country'];
			if (!empty($item['id_state']) && $item['id_state'] > 0) {
				$list_states[$item['id_state']] = $item['id_state'];
				$list_state_cities[$item['id_city']] = $item['id_city'];
			} else {
				$list_cities[$item['id_city']] = $item['id_city'];
			}
		}

		$list_countries = array_filter($list_countries);
		$list_states = array_filter($list_states);
		$list_state_cities = array_filter($list_state_cities);
		$list_cities = array_filter($list_cities);

		if (!empty($list_countries)) {
			$names_countries = $this->country->get_simple_countries(implode(',', $list_countries));
		}

		if (!empty($list_states)) {
			$names_states = $this->country->get_simple_states(implode(',', $list_states));
		}

		if (!empty($list_state_cities)) {
			$names_state_cities = $this->country->get_simple_cities_by_state(implode(',', $list_state_cities));
		}

		if (!empty($list_cities)) {
			$names_cities = $this->country->get_simple_cities(implode(',', $list_cities));
		}

		foreach ($partners as $partner) {
			$full_address = array();
			if (!empty($names_states[$partner['id_state']]['state'])) {
				$full_address[] = $names_states[$partner['id_state']]['state'];
				$full_address[] = $names_state_cities[$partner['id_city']];
			} elseif (!empty($names_cities[$partner['id_city']]['city'])) {
				$full_address[] = $names_cities[$partner['id_city']];
			}

			$partner_link = __SITE_URL . 'shipper/' . strForURL($partner['co_name']) . '-' . $partner['id'];
			$partner_image_url = getShipperLogo($partner['id'], $partner['logo'], 1);

            $chatBtn = new ChatButton(['recipient' => $partner['id_shipper'], 'recipientStatus' => $partner['user_status']]);

			$output[] = array(
				"seller_dt" => "<div class=\"flex-card\">
                                    <div class=\"flex-card__fixed main-data-table__item-img image-card-center\">
                                        <a class=\"link\" href=\"{$partner_link}\">
                                            <img class=\"image\" src=\"{$partner_image_url}\" alt=\"{$partner['name_company']}\"/>
                                        </a>
                                    </div>
                                    <div class=\"flex-card__float\">
                                        <div class=\"main-data-table__item-ttl\">
                                            <a href=\"{$partner_link}\"
                                                class=\"display-ib link-black txt-medium\"
                                                title=\"{$partner['name_company']}\">
                                                {$partner['co_name']}
                                            </a>
                                        </div>
                                        <div class=\"\">
                                        	<img width=\"24\" height=\"24\" src=\"".getCountryFlag($names_countries[$partner['id_country']]['country']) . "\" title=\"{$names_countries[$partner['id_country']]['country']}\" alt=\"{$names_countries[$partner['id_country']]['country']}\"/>
                                        	<span class=\"lh-25\">".implode(', ', $full_address)."</span>
										</div>
                                        <div class=\"\">
                                            {$partner['address']}
                                        </div>
                                    </div>
                                </div>",
				"contact_dt"=> "{$partner['email']}<br/><a class=\"link-black txt-medium\" href=\"tel:{$partner['phone']}\">{$partner['phone']}</a>",
				"date_dt" 	=> 	formatDate($partner['date_partner'], 'j M, Y H:i'),
				"actions_dt"=> '<div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>
                                    <div class="dropdown-menu">'
                                        . $chatBtn->button()
                                        . '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this partner?" data-callback="decline_request" data-shipper="'.$partner['id_shipper'].'" data-partner="'.$partner['id_partner'].'" title="Remove partner">
                                            <i class="ep-icon ep-icon_trash-stroke"></i>
                                            <span class="txt">Delete partner</span>
                                        </a>
                                    </div>
                                </div>'
			);
		}

		return $output;
	}

    private function my_estimates_requests($estimates)
    {
        $output = array();
        $now = new DateTime('NOW');

        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);

        foreach ($estimates as $estimate) {
            $seller_id = (int) arrayGet($estimate, 'id_seller');
            $estimate_id = (int) arrayGet($estimate, 'id');
            $estimate_items = null !== ($decoded = json_decode(arrayGet($estimate, 'items'), true)) ? $decoded : array();
            $estimate_countdown = null !== ($countdown = arrayGet($estimate, 'max_response_date')) ? \DateTime::createFromFormat('Y-m-d H:i:s', $countdown) : null;
            $estimate_countdown_status = arrayGet($estimate, 'current_countdown', 'expired');
            $estimate_status = 'processed';
            if (empty($estimate['responses'])) {
                $estimate_status = 'new';
                if (
                    null === $estimate_countdown ||
                    false === $estimate_countdown ||
                    $estimate_countdown <= $now ||
                    'expired' === $estimate_countdown_status
                ) {
                    $estimate_status = 'expired';
                }
            }

            //region Estimate
            //region Status
            $status_name = translate("shipping_estimates_dashboard_dt_estimate_status_{$estimate_status}_label_text", null, true);
            //endregion Status

            //region Countdown
            $countdown_label = '&mdash;';
            if ('new' === $estimate_status) {
                $countdown_date_inline = $estimate_countdown->format('Y-m-d H:i:s');
                $countdown_label = "
                    <span class=\"offer-status\"
                        data-expire=\"{$countdown_date_inline}\">
                        {$countdown_date_inline}
                    </span>
                ";
            }
            //endregion Countdown

            $estimate_title = cleanOutput($estimate['group_title']);
            $estimate_label = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl txt-medium\">
                            {$estimate_title}
                        </div>
                        <div class=\"main-data-table__item-ttl\">
                            {$status_name}
                        </div>
                        <div class=\"main-data-table__item-ttl txt-gray\">
                            {$countdown_label}
                        </div>
                    </div>
                </div>
            ";
            //endregion Estimate

            //region Comment
            $comment_text = '&mdash;';
            if(!empty($estimate['comment_buyer'])) {
                $comment_text = cleanOutput(strLimit($estimate['comment_buyer'], 300));
            }
            $comment_label = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\">
                        <div>
                            {$comment_text}
                        </div>
                    </div>
                </div>
            ";
            //endregion Comment

            //region Destination
            //region From
            $departure_country = arrayGet($estimate, 'departure_country.name');
            $departure_state = arrayGet($estimate, 'departure_state.name');
            $departure_city = arrayGet($estimate, 'departure_city.name');
            $departure_postal_code = arrayGet($estimate, 'postal_code_from');
            $departure_parts = array_filter(array(
                $departure_city,
                trim("{$departure_state} {$departure_postal_code}"),
                $departure_country,
            ));
            $departure_label = !empty($departure_parts) ? implode(', ', $departure_parts) : '&mdash;';

            $estimate_destination_initial_text = translate("shipping_estimates_dashboard_dt_column_destination_initial_label_text", null, true);
            $estimate_destination_initial_title = translate("shipping_estimates_dashboard_dt_column_destination_initial_label_title", null, true);
            $estimate_destination_initial_label = "
                <div class=\"dtable-params__item\" title=\"{$estimate_destination_initial_title}\">
                    <span>
                        <strong>{$estimate_destination_initial_text}</strong> <span>{$departure_label}</span>
                    </span>
                </div>
            ";
            //endregion From

            //region To
            $finish_country = arrayGet($estimate, 'destination_country.name');
            $finish_state = arrayGet($estimate, 'destination_state.name');
            $finish_city = arrayGet($estimate, 'destination_city.name');
            $finish_postal_code = arrayGet($estimate, 'postal_code_to');
            $finish_parts = array_filter(array(
                $finish_city,
                trim("{$finish_state} {$finish_postal_code}"),
                $finish_country,
            ));
            $finish_label = !empty($finish_parts) ? implode(', ', $finish_parts) : '&mdash;';

            $estimate_destination_final_text = translate("shipping_estimates_dashboard_dt_column_destination_final_label_text", null, true);
            $estimate_destination_final_title = translate("shipping_estimates_dashboard_dt_column_destination_final_label_title", null, true);
            $estimate_destination_final_label = "
                <div class=\"dtable-params__item\" title=\"{$estimate_destination_final_title}\">
                    <span>
                        <strong>{$estimate_destination_final_text}</strong> <span>{$finish_label}</span>
                    </span>
                </div>
            ";
            //endregion To

            $destination_label = "
                <div class=\"dtable__params\">
                    {$estimate_destination_initial_label}
                    {$estimate_destination_final_label}
                </div>
            ";
            //endregion Destination

            //region Actions
            //region Contact seller button
            $contact_seller_button = null;
            if (0 !== $seller_id) {
                $sellerInfo = $usersRepository->getSimpleUser($seller_id);
                $contact_seller_button_text = translate('shipping_estimates_dashboard_dt_button_contact_seller_text', null, true);
                $chatBtn = new ChatButton(['recipient' => $seller_id, 'recipientStatus' => $sellerInfo['status']]);
                $contact_seller_button = $chatBtn->button();
            }
            //endregion Contact seller button

            //region Details button
            $details_button_url = __CURRENT_SUB_DOMAIN_URL . "shippers/popup_forms/view_estimate/{$estimate_id}";
            $details_button_text = translate('shipping_estimates_dashboard_dt_button_details_text', null, true);
            $details_button_modal_title = translate('shipping_estimates_dashboard_dt_button_details_modal_title', null, true);
            $details_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$details_button_url}\"
                    data-title=\"{$details_button_modal_title}\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$details_button_text}</span>
                </a>
            ";
            //endregion Details button

            //region Responses button
            $responses_button = null;
            if ('processed' === $estimate_status) {
                $responses_button_url = __SITE_URL . "shippers/popup_forms/show_responses/{$estimate_id}";
                $responses_button_text = translate('shipping_estimates_dashboard_dt_button_responses_text', null, true);
                $responses_button_modal_title = translate('shipping_estimates_dashboard_dt_button_responses_modal_title', null, true);
                $responses_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$responses_button_url}\"
                        data-title=\"{$responses_button_modal_title}\"
                        data-mw=\"920\">
                        <i class=\"ep-icon ep-icon_comments-stroke\"></i>
                        <span>{$responses_button_text}</span>
                    </a>
                ";
            }
            //endregion Responses button

            //region Save button
            $save_button = null;
            if (
                'processed' === $estimate_status &&
                0 === (int) $estimate['is_saved']
            ) {
                $save_button_text = translate('general_button_save_text', null, true);
                $save_button_message = translate('shipping_estimates_dashboard_dt_button_save_estimate_message', null, true);
                $save_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-message=\"{$save_button_message}\"
                        data-callback=\"saveEstimate\"
                        data-estimate=\"{$estimate_id}\">
                        <i class=\"ep-icon ep-icon_save\"></i>
                        <span>{$save_button_text}</span>
                    </a>
                ";
            }
            //endregion Save button

            //region Renew button
            $renew_button = null;
            if ('expired' === $estimate_status) {
                $renew_button_text = translate('general_button_renew_text', null, true);
                $renew_button_message = translate('shipping_estimates_dashboard_dt_button_renew_estimate_message', null, true);
                $renew_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-message=\"{$renew_button_message}\"
                        data-callback=\"renewEstimate\"
                        data-estimate=\"{$estimate_id}\">
                        <i class=\"ep-icon ep-icon_updates\"></i>
                        <span>{$renew_button_text}</span>
                    </a>
                ";
            }
            //endregion Renew button

            //region Delete button
            $delete_button = null;
            if(
                'new' === $estimate_status ||
                'expired' === $estimate_status
            ) {
                $delete_button_text = translate('general_button_delete_text', null, true);
                $delete_button_message = translate('shipping_estimates_dashboard_dt_button_delete_estimate_message', null, true);
                $delete_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-message=\"{$delete_button_message}\"
                        data-callback=\"deleteEstimate\"
                        data-estimate=\"{$estimate_id}\">
                        <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                        <span>{$delete_button_text}</span>
                    </a>
                ";
            }
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions_label = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$details_button}
                        {$renew_button}
                        {$delete_button}
                        {$responses_button}
                        {$contact_seller_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = array(
                "estimate"    => $estimate_label,
                "destination" => $destination_label,
                "comment"     => $comment_label,
                "created_at"  => getDateFormatIfNotEmpty(arrayGet($estimate, 'date_create')),
                "updated_at"  => getDateFormatIfNotEmpty(arrayGet($estimate, 'date_update')),
                "actions"     => $actions_label,
            );
        }

        return $output;
    }

    private function my_estimate_request_responses($responses)
    {
        $output = array();
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);

        foreach ($responses as $index => $response) {
            $response_id = (int) $response['id'];
            $shipper_id = (int) $response['id_shipper'];
            $user_id = (int) arrayGet($response, 'shipper.user_id');

            //region Price
            $price_label = get_price(arrayGet($response, 'price', 0));
            //endregion Price

            //region Days
            $from_days = (int) arrayGet($response, 'delivery_days_from', 0);
            $to_days = (int) arrayGet($response, 'delivery_days_to', 0);
            $days_label = translate("shipping_estimates_dashboard_dt_column_shipper_delivery_label_text", array(
                '{from}' => $from_days,
                '{to}'   => $to_days,
            ), true);
            //endregion Days

            //region Shipper
            $shipper_name = cleanOutput(arrayGet($response, 'shipper.company', ''));
            $shipper_photo = rawurlencode(base64_encode(arrayGet($response, 'shipper.logo', '')));
            $shipper_avatar_url = __SHIPPER_URL . "images/logo/{$shipper_id}/{$shipper_photo}";
            $shipper_profile_url = getShipperURL(array('co_name' => $shipper_name, 'id' => $shipper_id));

            //region Date
            $date_label = getDateFormatIfNotEmpty(arrayGet($response, 'created_at'));
            //endregion Date

            //region Comment
            $comment_placeholder_alternate = translate('shipping_estimates_dashboard_dt_comment_text_alternate', null, true);
            $comment_label = null;
            if (!empty($response['comment'])) {
                $comment_text = cleanOutput($response['comment']);
                $comment_placeholder = translate('shipping_estimates_dashboard_dt_comment_text', null, true);
                $comment_label = "
                    <div class=\"dn-sm\">
                        <span>{$comment_placeholder}<span>
                        <a class=\"estimate-response-popover\"
                            data-trigger=\"hover\"
                            data-toggle=\"popover\"
                            data-placement=\"auto\"
                            data-content=\"{$comment_text}\">
                            <i class=\"ep-icon ep-icon_comments-stroke\"></i>
                        </a>
                    </div>
                ";
            }
            //endregion Comment

            $shipper_label = "
                <div id=\"estimate-response-{$index}\">
                    <div class=\"flex-card\">
                        <div class=\"flex-card__fixed main-data-table__item-img image-card\">
                            <span class=\"link\">
                                <img class=\"image\" src=\"{$shipper_avatar_url}\" alt=\"{$shipper_name}\"/>
                            </span>
                        </div>
                        <div class=\"flex-card__float\">
                            <div class=\"main-data-table__item-ttl\">
                                <a href=\"{$shipper_profile_url}\"
                                    class=\"display-ib link-black txt-medium\"
                                    title=\"View item\"
                                    target=\"_blank\">
                                    {$shipper_name}
                                </a>
                            </div>
                            <div class=\"main-data-table__item-ttl txt-gray\">
                                {$comment_label}
                            </div>
                            <div class=\"d-block d-sm-none\">
                                {$comment_placeholder_alternate}
                                <br>
                                {$comment_text}
                            </div>
                            <div class=\"main-data-table__item-ttl txt-gray\">
                                {$date_label}
                            </div>

                        </div>
                    </div>
                </div>
            ";
            //endregion Shipper

            //region Actions
            //region Contact shipper button
            $contact_shipper_button = null;
            if (0 !== $user_id) {
                $userInfo = $usersRepository->getSimpleUser($user_id);
                $chatBtn = new ChatButton(['recipient' => $user_id, 'recipientStatus' => $userInfo['status']]);
                $contact_shipper_button_text = translate('shipping_estimates_dashboard_dt_button_contact_shipper_text', null, true);
                $contact_shipper_button = $chatBtn->button();
            }
            //endregion Contact shipper button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions_label = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$contact_shipper_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = array(
                'shipper'      => $shipper_label,
                'comment'      => cleanOutput(arrayGet($response, 'comment', '&mdash;')),
                'price'        => $price_label,
                'delivery'     => $days_label,
                'actions'      => $actions_label,
            );
        }

        return $output;
    }

    private function _my_rates_requests_pc($data)
    {

		extract($data);

		$statuses = array(
			'new' => array(
				'icon' => 'ep-icon ep-icon_new txt-blue',
				'title' => 'New'
			),
			'accepted' => array(
				'icon' => 'ep-icon ep-icon_ok txt-green',
				'title' => 'Accepted'
			),
			'confirmed' => array(
				'icon' => 'ep-icon ep-icon_ok-circle txt-green',
				'title' => 'Confirmed'
			),
			'declined' => array(
				'icon' => 'ep-icon ep-icon_remove-circle txt-red',
				'title' => 'Declined'
			)
		);

        $shippers_logo = getDisplayImageLink(array('{ID}' => $quote['id_shipper_company'], '{FILE_NAME}' => $quote['logo']), 'shippers.main', array( 'thumb_size' => 0 ));

		foreach ($quotes as $quote) {
			$actions = '';
			if($quote['order_status_alias'] == 'invoice_confirmed'){
				if(($quote['status'] == 'new') && $quote['current_countdown'] == 'active'){
					$actions .= '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to decline this request?" title="Decline request" href="#" data-callback="change_status" data-request="'.$quote['id_quote'].'"></a>';
				}
			}

			if(($quote['status'] == 'declined') || ($quote['current_countdown'] == 'expired' && ($quote['status'] != 'confirmed') )){
				$actions .= '<a class="ep-icon ep-icon_remove confirm-dialog txt-red" data-callback="delete_request" data-message="Are you sure want delete this request?" title="Delete request" data-id="'.$quote['id_quote'].'" href="#"></a>';
			}

			$output[] = array(
				'dt_plus' => $quote['id_quote'].'<br><a rel="quote_details" class="ep-icon ep-icon_plus m-0" title="View details"></a>',
				'dt_id' => ' <div class="tal">
								<a class="dt_filter ep-icon ep-icon_filter txt-green" title="Filter by order" data-value="'.$quote['id_order'].'" data-name="order" data-title="Order" data-value-text="'.orderNumber($quote['id_order']).'"></a>
							</div>'
							.orderNumber($quote['id_order']),
				'dt_shipper_logo' => '<img class="mw-80 mh-80" src="'. $shippers_logo .'" alt="logo">',
				'dt_shipper_name' => $quote['co_name'],
				'dt_shipping_price' => get_price($quote['shipping_price']),
				'dt_delivery_time' => ($quote['delivery_time'] ? $quote['delivery_time'].' days' : '&mdash;'),
				'dt_comment_user' => ($quote['comment_user'] ? $quote['comment_user'] : '&mdash;'),
				'dt_comment_shipper' => ($quote['comment_shipper'] ? $quote['comment_shipper'] : '&mdash;'),
				'dt_status' => '<div class="tal">
									<a class="dt_filter ep-icon ep-icon_filter txt-green" title="Filter by status" data-value="'.$quote['status'].'" data-name="status" data-title="Status" data-value-text="'.ucfirst($quote['status']).'"></a>
								</div>
								<span><i class="'.$statuses[$quote['status']]['icon'].' fs-30"></i><br> '.$statuses[$quote['status']]['title'].'</span>',
				'dt_create_date' => formatDate($quote['create_date']),
				'dt_countdown' => '<span class="order-status display-b w-100pr tac" data-expire="' . $quote['max_response_date'] . '">' . $quote['max_response_date'] . '</span>',
				'dt_actions' => $actions
			);
		}

		return $output;
	}

    private function _my_rates_requests_tablet($data)
    {
		extract($data);

		$statuses = array(
			'new' => array(
				'icon' => 'ep-icon ep-icon_new txt-blue',
				'title' => 'New'
			),
			'accepted' => array(
				'icon' => 'ep-icon ep-icon_ok txt-green',
				'title' => 'Accepted'
			),
			'confirmed' => array(
				'icon' => 'ep-icon ep-icon_ok-circle txt-green',
				'title' => 'Confirmed'
			),
			'declined' => array(
				'icon' => 'ep-icon ep-icon_remove-circle txt-red',
				'title' => 'Declined'
			)
		);

		foreach ($quotes as $quote) {
			$actions = '';
			if($quote['order_status_alias'] == 'invoice_confirmed'){
				if(($quote['status'] == 'new') && $quote['current_countdown'] == 'active'){
					$actions .= '<li><a class="confirm-dialog txt-red" data-message="Are you sure you want to decline this request?" title="Decline request" href="#" data-callback="change_status" data-request="'.$quote['id_quote'].'"><i class="ep-icon ep-icon_remove"></i> Decline</a></li>';
				}
			}

			if(($quote['status'] == 'declined') || ($quote['current_countdown'] == 'expired' && ($quote['status'] != 'confirmed') )){
				$actions .= '<li><a class="confirm-dialog txt-red" data-message="Are you sure you want to delete this request?" title="Delete request" href="#" data-callback="delete_request" data-id="'.$quote['id_quote'].'"><i class="ep-icon ep-icon_remove "></i> Delete</a></li>';
			}

            $shippers_logo = getDisplayImageLink(array('{ID}' => $quote['id_shipper_company'], '{FILE_NAME}' => $quote['logo']), 'shippers.main', array( 'thumb_size' => 1 ));

			$output[] = array(
				'dt_actions' =>
					'<div class="btn-group">
						<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Actions
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">'
							.$actions
						.'</ul>
						<button type="button" class="btn btn-default">on '.orderNumber($question['id_q']).'</button>
					</div>',
				'dt_plus' => $quote['id_quote'],
				'dt_id' =>
					'<div>
						<a class="dt_filter btn btn-default btn-icon" title="Filter by order" data-value="'.$quote['id_order'].'" data-name="order" data-title="Order" data-value-text="'.orderNumber($quote['id_order']).'"><i class="ep-icon ep-icon_filter txt-green"></i> Filter by order</a>
					</div>'
					.orderNumber($quote['id_order']),
				'dt_shipper_logo' => '<img class="mw-80 mh-80" src="'. $shippers_logo .'" alt="logo">',
				'dt_shipper_name' => $quote['co_name'],
				'dt_shipping_price' => get_price($quote['shipping_price']),
				'dt_delivery_time' => ($quote['delivery_time'] ? $quote['delivery_time'].' days' : '---'),
				'dt_comment' => ($quote['comment'] ? $quote['comment'] : '---'),
				'dt_status' =>
					'<div>
						<a class="dt_filter btn btn-default btn-icon" title="Filter by status" data-value="'.$quote['status'].'" data-name="status" data-title="Status" data-value-text="'.ucfirst($quote['status']).'"><i class="ep-icon ep-icon_filter txt-green"></i> Filter by status</a>
					</div>
					<div><i class="'.$statuses[$quote['status']]['icon'].' fs-30"></i><br> '.$statuses[$quote['status']]['title'].'</div>',
				'dt_create_date' => formatDate($quote['create_date']),
				'dt_countdown' =>
					'<span class="order-status display-b w-100pr tac" data-expire="' . $quote['max_response_date'] . '">' . $quote['max_response_date'] . '</span>',
				'dt_details' =>
					'<a class="btn btn-default btn-icon" rel="quote_details"><i class="ep-icon ep-icon_plus txt-blue"></i> View detail</a>',
			);
		}

		return $output;
    }

    private function calculate_weight($sizes, $weight, $pieces)
    {
		$parts = explode('x', strtolower($sizes));
		if(3 === count($parts)) {
			$dims = ($parts[0] * $parts[1] * $parts[2]);
        }

		$airw = $weight * $pieces;
		$aird = $dims / 6000 * $pieces;
		$in_db = round($dims / 6000 * $pieces);
		$winp = $weight;
		if ($aird < $airw) {
			$in_db = $airw;
		}

		$air = round(max($aird, $airw));
		$vol = round($aird);
		$airwround = round($airw);

		return array(
			'sizes'             => $sizes,
			'actual_weight'     => $airwround,
			'volumetric_weight' => $vol,
			'chargeable_weight' => $air,
		);
    }
}

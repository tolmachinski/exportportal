<?php

use App\Common\Database\Relations\RelationInterface;
use App\Filesystem\BlogsPathGenerator;
use App\Filesystem\VacancyPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_Controller extends TinyMVC_Controller {

	private $breadcrumbs = array(
	    'home' => array(
            'link' => __SITE_URL,
            'title' => 'Export Portal'
        )
    );

	private function _load_main() {
        $this->load->model('Cr_users_Model', 'cr_users');
		$this->load->model('Cr_domains_Model', 'cr_domains');
        $this->load->model('Cr_events_Model', 'cr_events');
    }

    public function event() {
        $this->_load_main();
        global $tmvc;

        if(!in_array(__CURRENT_SUB_DOMAIN, $tmvc->config['cr_available'])){
            show_404();
        }

        $data['cr_domain'] = $this->cr_domains->get_cr_domain(array('country_alias' => __CURRENT_SUB_DOMAIN));

        if(empty($data['cr_domain'])){
            show_404();
        }

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('', __CURRENT_SUB_DOMAIN_URL),
            'title' => $data['cr_domain']['country']
        );

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('events', __CURRENT_SUB_DOMAIN_URL),
            'title' => 'Events'
        );

        $id_event = id_from_link($this->uri->segment(2));

        $data['event'] = $this->cr_events->get_event($id_event);
        if (empty($data['event']) || $data['event']['event_status'] != 'approved' || $data['event']['event_is_visible'] == 0) {
            show_404();
        }

        if (strtotime(date('Y-m-d')) > strtotime($data['event']['event_date_end'])) {
            show_404();
        }

        if ($data['cr_domain']['id_country'] != $data['event']['event_id_country']) {
            show_404();
        }

        $data['events_date'] = $this->cr_events->get_events_dates(array(
            'conditions' => array(
                'active_today' => true,
                'country'      => (int) $data['cr_domain']['id_country'],
                'status'       => 'approved',
                'visible'      => true,
            )
        ));
        $assigned_users = $this->cr_events->get_assigned_users($id_event);
        $assigned_users_id = array();

        foreach($assigned_users as $user_one){
            $assigned_users_id[] = $user_one['id_user'];
        }

        if(!empty($assigned_users_id)){
            $data['events_list'] = $this->cr_events->get_events(array(
                'limit'      => 10,
                'order'      => array('event_date_start' => 'DESC'),
                'conditions' => array(
                    'excluded_events' => array($id_event),
                    'assigned_users'  => $assigned_users_id,
                    'active_today'    => true,
                    'status'          => 'approved',
                    'visible'         => 1,
                ),
            ));
        }

        $data['ambassadors'] = $this->cr_users->cr_get_users(array(
            'events' => $id_event
        ));

        $data['allow_attend'] = true;
        if (logged_in()) {
            $attend_record = $this->cr_events->get_attend_record_by_user(privileged_user_id(), $id_event);
            $data['allow_attend'] = empty($attend_record);
        }

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('event/' . strForUrl($data['event']['event_name']) . '-' . $data['event']['id_event'], __CURRENT_SUB_DOMAIN_URL),
            'title' => truncWords($data['event']['event_name'], 5)
        );

        $data['meta_params']['[EVENT_NAME]'] = $data['event']['event_name'];
        $data['meta_params']['[COUNTRY]'] = $data['event']['country'];
        $event_image = 'public/img/cr_event_images/'.$data['event']['id_event'] . '/' . $data['event_image'];
        if(image_exist($event_image)){
            $data['meta_params']['[image]'] = $event_image;
        }

        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['sidebar_right_content'] = 'new/cr/events/detail_sidebar_view';
        $data['main_content'] = 'new/cr/events/detail_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function events() {
        $this->_load_main();
        global $tmvc;

        $uri = $this->uri->uri_to_assoc(3);
        if(!in_array(__CURRENT_SUB_DOMAIN, $tmvc->config['cr_available'])){
            show_404();
        }

        $data = array(
            'cr_domain' => $this->cr_domains->get_cr_domain(array('country_alias' => __CURRENT_SUB_DOMAIN))
        );

        if(empty($data['cr_domain'])){
            show_404();
        }

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('', __CURRENT_SUB_DOMAIN_URL),
            'title' => $data['cr_domain']['country']
        );

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('events', __CURRENT_SUB_DOMAIN_URL),
            'title' => 'Events'
        );


        if (isset($uri['page'])) {
            $uri['page'] = (int)$uri['page'];
            if ($uri['page'] <= 0) {
                show_404();
            }

            $data['page'] = $uri['page'];
        } else {
            $data['page'] = 1;
        }

        $data['cr_domain'] = $this->cr_domains->get_cr_domain(array('country_alias' => __CURRENT_SUB_DOMAIN));
        if (empty($data['cr_domain'])) {
            show_404();
        }

        $data['meta_params']['[COUNTRY]'] = $data['cr_domain']['country'];

        checkURI($uri, array('type', 'month', 'date', 'page', 'show', 'sort_by'));

        $links_map = array(
            'events' => array(
                'type' => 'uri',
                'deny' => array('type', 'month', 'date', 'page', 'keywords'),
            ),
            'type' => array(
                'type' => 'uri',
                'deny' => array('page'),
            ),
            'month' => array(
                'type' => 'uri',
                'deny' => array('page', 'date'),
            ),
            'date' => array(
                'type' => 'uri',
                'deny' => array('page', 'month'),
            ),
            'page' => array(
                'type' => 'uri',
                'deny' => array('page'),
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('keywords', 'page'),
            ),
            'sort_by' => array(
                'type' => 'get',
                'deny' => array('sort_by', 'page'),
            )
        );

        $search_params_links_map = array(
            'events' => array(
                'type' => 'get',
                'deny' => array('type', 'month', 'date', 'page', 'keywords', 'events'),
            ),
            'type' => array(
                'type' => 'get',
                'deny' => array('page', 'type'),
            ),
            'month' => array(
                'type' => 'get',
                'deny' => array('page', 'date', 'month'),
            ),
            'date' => array(
                'type' => 'get',
                'deny' => array('page', 'month', 'date'),
            ),
            'page' => array(
                'type' => 'get',
                'deny' => array('page'),
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('keywords', 'page'),
            ),
            'sort_by' => array(
                'type' => 'get',
                'deny' => array('sort_by', 'page'),
            )
        );

        $get_parameters = array();
        if (!empty($_SERVER['QUERY_STRING'])) {
            $data['get_params'] = cleanOutput(cleanInput(arrayToGET($_GET)));
            $get_parameters = $_GET;
            foreach($get_parameters as $key => $one_param){
                $get_parameters[$key] = cleanOutput(cleanInput($one_param));
            }
        }

        $data['links_tpl'] = $this->uri->make_templates($links_map, $uri);
        $data['search_params_links_tpl'] = $this->uri->make_templates($search_params_links_map, $uri, true);

        foreach ($data['links_tpl'] as $key_link_tpl => $value_link_tpl) {
            if($key_link_tpl != 'events'){
                $data['links_tpl'][$key_link_tpl] = normalize_url("events/{$value_link_tpl}");
            } else {
                $data['links_tpl'][$key_link_tpl] = 'events';
            }
        }

        foreach ($data['search_params_links_tpl'] as $key_link_tpl => $value_link_tpl) {
            if($key_link_tpl != 'events'){
                $data['search_params_links_tpl'][$key_link_tpl] = normalize_url("events/{$value_link_tpl}");
            } else {
                $data['search_params_links_tpl'][$key_link_tpl] = "events";
            }
        }

        $data['per_p'] = (int) config('cr_events_per_page', 10);
        $params = array(
            'limit'      => $data['per_p'],
            'skip'       => ($data['page'] - 1) * $data['per_p'],
            'with'       => array(
                'city' => function (RelationInterface $relation) { $relation->getQuery()->select('id, city as name'); },
            ),
            'conditions' => array(
                'active_today' => true,
                'country'      => (int) $data['cr_domain']['id_country'],
                'status'       => 'approved',
                'visible'      => 1,
            )
        );

        if (!empty($get_parameters['keywords'])) {
            $keywords = cleanInput(cut_str($get_parameters['keywords']));

            if (strlen($keywords) < 3) {
                $this->session->setMessages(translate("systmess_error_search_keywords_must_have_characters", ["[CHARACTERS]" => "3"]), 'errors');
            } else {
                $params['conditions']['search'] = $keywords;
                $data['search_params'][] = array(
                    'link' => get_dynamic_url($data['search_params_links_tpl']['keywords'], __CURRENT_SUB_DOMAIN_URL),
                    'title' => 'Keywords: ',
                    'param' => $keywords
                );
            }
        }

        $data['months'] = array(
            array('id' => '01', 'name' => translate('calendar_m_01')),
            array('id' => '02', 'name' => translate('calendar_m_02')),
            array('id' => '03', 'name' => translate('calendar_m_03')),
            array('id' => '04', 'name' => translate('calendar_m_04')),
            array('id' => '05', 'name' => translate('calendar_m_05')),
            array('id' => '06', 'name' => translate('calendar_m_06')),
            array('id' => '07', 'name' => translate('calendar_m_07')),
            array('id' => '08', 'name' => translate('calendar_m_08')),
            array('id' => '09', 'name' => translate('calendar_m_09')),
            array('id' => '10', 'name' => translate('calendar_m_10')),
            array('id' => '11', 'name' => translate('calendar_m_11')),
            array('id' => '12', 'name' => translate('calendar_m_12'))
        );

        if (!empty($uri['month'])) {
            $params['conditions']['started_at_month'] = $uri['month'];
            $months = arrayByKey($data['months'], 'id');
            $month_year = explode('-', $uri['month']);

            $data['search_params'][] = array(
                'link' => get_dynamic_url($data['search_params_links_tpl']['month'], __CURRENT_SUB_DOMAIN_URL),
                'title' => "Month: ",
                'param' => "{$months[$month_year[0]]['name']} {$month_year[1]}",
            );

            $data['meta_params']['[DATE]'] = "{$months[$month_year[0]]['name']} {$month_year[1]}";
        }

        if (!empty($uri['date'])) {
            $date = date('m/d/Y', strtotime($uri['date']));
            $params['conditions']['started_at_date'] = date('Y-m-d', strtotime($uri['date']));
            $data['search_params'][] = array(
                'link' => get_dynamic_url($data['search_params_links_tpl']['date'], __CURRENT_SUB_DOMAIN_URL),
                'title' => "Date: ",
                'param' => $date
            );
        }

        if (!empty($uri['type'])) {
            $type_data = explode('-', $uri['type']);

            $c = count($type_data) - 1;
            $params['conditions']['type'] = $type_data[$c];
            $type_name = ucfirst(implode(' ', array_slice($type_data, 0, $c)));

            $data['search_params'][] = array(
                'link' => get_dynamic_url($data['search_params_links_tpl']['type'], __CURRENT_SUB_DOMAIN_URL),
                'title' => "Type: ",
                'param' => $type_name,
            );

            $data['meta_params']['[TYPE]'] = $type_name;
        }

        $data['sort_by_links'] = array(
            'items' => array(
                'event_date_start-asc' => array(
                    'title' => 'Oldest',
                    'url'	=> replace_dynamic_uri('event_date_start-asc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
                ),
                'event_date_start-desc' => array(
                    'title' => 'Newest',
                    'url'	=> replace_dynamic_uri('event_date_start-desc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
                ),
                'event_count_ambassadors-asc' => array(
                    'title' => 'Less ambassadors',
                    'url'	=> replace_dynamic_uri('event_count_ambassadors-asc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
                ),
                'event_count_ambassadors-desc' => array(
                    'title' => 'More ambassadors',
                    'url'	=> replace_dynamic_uri('event_count_ambassadors-desc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
                )
            ),
            'selected' => 'event_date_start-asc'
        );

        $params['order'] = array('event_date_start' => 'asc');
        if (!empty($get_parameters['sort_by']) && array_key_exists($get_parameters['sort_by'], $data['sort_by_links']['items'])) {
            list($column, $direction) = explode('-', $get_parameters['sort_by']);
            $params['order'] = array($column => $direction);
            $data['sort_by_links']['selected'] = $get_parameters['sort_by'];
        }

        $data['types'] = $this->cr_events->get_types();
        $data['count'] = $this->cr_events->count_events($params);
        $data['events'] = $this->cr_events->get_events($params);
        $data['events_date'] = $this->cr_events->get_events_dates($params);
        $data['events_months_counters'] = arrayByKey($this->cr_events->get_events_month_counters($params), 'date_value');
        $data['events_types_counters'] = arrayByKey($this->cr_events->get_events_type_counters($params), 'event_id_type');
        $data['events_countries_counters'] = arrayByKey($this->cr_events->get_events_country_counters($params), 'event_id_country');
        $data['other_countries'] = $this->cr_domains->get_cr_domains(array(
            'not_domains_list' => (int) $data['cr_domain']['id_domain'],
            'order_by'         => "pc.country ASC"
        ));

        $event_ids = array();
        foreach ($data['events'] as $event) {
            $event_ids[] = $event['id_event'];
        }

        $ambassadors = array();
        if (!empty($event_ids)) {
            $ambassadors = $this->cr_users->cr_get_users(array(
                'events' => implode(',', $event_ids)
            ));
        }

        $data['ambassadors'] = arrayByKey($ambassadors, 'id_event', true);

        $pagination_config = array(
            'site_url' => __CURRENT_SUB_DOMAIN_URL,
            'base_url' => $data['links_tpl']['page'],
            'first_url' => get_dynamic_url($data['search_params_links_tpl']['page'], __CURRENT_SUB_DOMAIN_URL),
            'replace_url' => true,
            'total_rows' => $data['count'],
            'per_page' => $data['per_p'],
            'cur_page' => $data['page']
        );

        $this->load->library('Pagination', 'pagination');
        $this->pagination->initialize($pagination_config);
        $data['pagination'] = $this->pagination->create_links();

        $data['meta_params']['[image]'] = 'public/img/headers-info-pages/cr-events-india.png';

        $data['header_out_content'] = 'new/cr/events/header_view';
        $data['sidebar_left_content'] = 'new/cr/events/sidebar_view';
        $data['main_content'] = 'new/cr/events/index_view';
        $data['breadcrumbs'] = $this->breadcrumbs;
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

	public function index() {
		$this->_load_main();
		global $tmvc;
		$uri = $this->uri->uri_to_assoc(2);

		if(!in_array(__CURRENT_SUB_DOMAIN, $tmvc->config['cr_available'])){
			show_404();
		}

		$data['cr_domain'] = $this->cr_domains->get_cr_domain(array('country_alias' => __CURRENT_SUB_DOMAIN));
		$data['cr_domain_path'] = $this->cr_domains->path_folder;
		if(empty($data['cr_domain'])){
			show_404();
		}

		checkURI($uri, array('type','event','page'));

		$links_map = array(
			'type' => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			'page' => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('keywords', 'page'),
			),
			'ustatus' => array(
				'type' => 'get',
				'deny' => array('ustatus', 'page'),
			),
			'sort_by' => array(
				'type' => 'get',
				'deny' => array('sort_by', 'page'),
			)
		);

        $search_params_links_map = array(
			'type' => array(
				'type' => 'get',
				'deny' => array('type','page'),
			),
			'page' => array(
				'type' => 'get',
				'deny' => array('page'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('keywords', 'page'),
			),
			'ustatus' => array(
				'type' => 'get',
				'deny' => array('ustatus', 'page'),
			),
			'sort_by' => array(
				'type' => 'get',
				'deny' => array('sort_by', 'page'),
			)
		);

		$data['per_p'] = $tmvc->my_config['cr_users_per_page'];
		$users_params = array(
			'status' => 'active',
			'group_type' => "'CR Affiliate'",
			'domains' => $data['cr_domain']['id_domain']
		);

		$data['links_tpl'] = $this->uri->make_templates($links_map, $uri);
		$data['search_params_links_tpl'] = $this->uri->make_templates($search_params_links_map, $uri, true);

		$get_parameters = array();
		if (!empty($_SERVER['QUERY_STRING'])) {
            $data['get_params'] = cleanOutput(cleanInput(arrayToGET($_GET)));
            $get_parameters = $_GET;
            foreach($get_parameters as $key => $one_param){
                $get_parameters[$key] = cleanOutput(cleanInput($one_param));
            }
		}

		$data['filter_ustatus'] = array(
			'links' => array(
				'online' => array(
					'title' => 'Online',
					'url'	=> replace_dynamic_uri('online', $data['links_tpl']['ustatus'], __CURRENT_SUB_DOMAIN_URL)
				),
				'offline' => array(
					'title'	=> 'Offline',
					'url' 	=> replace_dynamic_uri('offline', $data['links_tpl']['ustatus'], __CURRENT_SUB_DOMAIN_URL)
				),
				'any' => array(
					'title'	=> 'Any',
					'url' 	=> get_dynamic_url($data['search_params_links_tpl']['ustatus'], __CURRENT_SUB_DOMAIN_URL)
				)
			),
			'current' => 'any'
		);

		if (!empty($get_parameters['ustatus']) && array_key_exists($get_parameters['ustatus'], $data['filter_ustatus']['links'])) {
			$data['filter_ustatus']['current'] = $get_parameters['ustatus'];
			if($get_parameters['ustatus'] == 'online'){
				$users_params['logged'] = 1;
			}

			if($get_parameters['ustatus'] == 'offline'){
				$users_params['logged'] = 0;
			}
		}

		$data['sort_by_links'] = array(
            'items' => array(
				'registration_date-desc' => array(
					'title' => 'Newest',
					'url'	=> replace_dynamic_uri('registration_date-desc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
				),
                'registration_date-asc' => array(
					'title' => 'Oldest',
					'url'	=> replace_dynamic_uri('registration_date-asc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
				),
                'user_name-asc' => array(
					'title' => 'Name A-Z',
					'url'	=> replace_dynamic_uri('user_name-asc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
				),
                'user_name-desc' => array(
					'title' => 'Name Z-A',
					'url'	=> replace_dynamic_uri('user_name-desc', $data['links_tpl']['sort_by'], __CURRENT_SUB_DOMAIN_URL)
				)
            ),
            'selected' => 'registration_date-desc'
		);

		if (!empty($get_parameters['sort_by']) && array_key_exists($get_parameters['sort_by'], $data['sort_by_links']['items'])) {
            $data['sort_by_links']['selected'] = $users_params['sort_by'][] = $get_parameters['sort_by'];
        }

		if (!empty($get_parameters['keywords'])) {
			$keywords = cleanInput(cut_str($get_parameters['keywords']));

			if(strlen($keywords) < 3){
				$this->session->setMessages(translate("systmess_error_search_keywords_must_have_characters", ["[CHARACTERS]" => "3"]), 'errors');
			} else{
				$users_params['keywords'] = $keywords;
				$data['search_params'][] = array(
					'link' => get_dynamic_url($data['search_params_links_tpl']['keywords'], __CURRENT_SUB_DOMAIN_URL),
					'title' => 'Keywords',
					'param' => $keywords,
					'sub_params' => array(
						array(
							'link' => get_dynamic_url($data['search_params_links_tpl']['keywords'], __CURRENT_SUB_DOMAIN_URL),
							'title' => $keywords
						)
					)
				);
			}
		}

		$data['cr_types'] = $this->cr_users->cr_users_groups_counters(array('id_domain' => $data['cr_domain']['id_domain'], 'user_status' => 'active'));
		if (isset($uri['type'])) {
			$cr_types = arrayByKey($data['cr_types'], 'gr_alias');

			if(!array_key_exists($uri['type'], $cr_types)){
				show_404();
			}

			$users_params['group'] = $cr_types[$uri['type']]['idgroup'];
			$data['search_params'][] = array(
				'link' => get_dynamic_url($data['search_params_links_tpl']['type'], __CURRENT_SUB_DOMAIN_URL),
				'title' => 'Type',
				'param' => $cr_types[$uri['type']]['gr_name'],
				'sub_params' => array(
					array(
						'link' => get_dynamic_url($data['search_params_links_tpl']['type'], __CURRENT_SUB_DOMAIN_URL),
						'title' => $cr_types[$uri['type']]['gr_name']
					)
				)
			);
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        /** @var Elasticsearch_Blogs_Model $elasticsearchBlogsModel */
        $elasticsearchBlogsModel = model(Elasticsearch_Blogs_Model::class);
        $this->load->model('Blog_Model', 'blogs');
        $data['blogs'] = array_map(
            function($blog) use ($publicDisk) {
                $blog['imagePath'] = $publicDisk->url(BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo']));

                return $blog;
            },
            $elasticsearchBlogsModel->get_blogs([
                'country'   => $data['cr_domain']['id_country'],
                'lang'      => 'en',
                'visible'   => 1,
                'published' => 1,
                'per_p'     => 5,
                'sort_by'   => ['publish_on-desc', 'date-desc']
            ]) ?? []
        );

        /* vacancies */
        $this->load->model('Hiring_Model', 'hiring');
		$data['vacancies_list'] = $this->hiring->get_vacancies(array('visible' => 1, 'id_country' => $data['cr_domain']['id_country']));
        foreach ($data['vacancies_list'] as &$vacancy) {
            $vacancy['imageUrl'] = $publicDisk->url(VacancyPathGenerator::imageUploadPath($vacancy['id_vacancy'], $vacancy['photo']));
        }
		/* end vacancies */

        $this->load->model('Cr_events_Model', 'cr_events');

		$data['cr_events'] = $this->cr_events->get_events(array(
            'limit'      => 5,
            'conditions' => array(
                'active_today' => true,
                'country'      => (int) $data['cr_domain']['id_country'],
                'status'       => 'approved',
                'visible'      => 1,
            ),
        ));
		$data['other_countries'] = $this->cr_domains->get_cr_domains(array('not_domains_list' => $data['cr_domain']['id_domain'], 'order_by' => "pc.country ASC"));

		$users_params['count'] = $data['count'] = $this->cr_users->cr_count_users($users_params);

        $data['cr_users'] = $this->cr_users->cr_get_users($users_params);
		$data['header_out_content'] = 'new/cr/header_view';
		$data['sidebar_left_content'] = 'new/cr/sidebar_view';
		$data['main_content'] = 'new/cr/index_view';
		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}
}

<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Community_Controller extends TinyMVC_Controller {

	private $_list_sort_by;
	private $_questions_uri_components = null;
	private $_uri_params = null;

	private function _get_categories()
	{
		$question_categories_method = __SITE_LANG === 'en' ?  'getCategories' : 'getCategories_i18n';
		return arrayByKey(model(Questions_Model::class)->$question_categories_method(array('visible' => 1)), "idcat");
	}

	private function _get_countries()
	{
		return arrayByKey(model(Country_Model::class)->fetch_port_country(), "id");
	}

	private function get_links_template_all()
	{
		$links_map = array(
			$this->_questions_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['page'], 'keywords', 'order_by'),
			),
			$this->_questions_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['page'], 'keywords', 'order_by'),
            ),
			$this->_questions_uri_components['page'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['page']),
            ),
            'keywords' => array(
				'type' => 'get',
				'deny' => array(),
			),
            'order_by' => array(
				'type' => 'get',
				'deny' => array(),
			)
		);

        return $this->uri->make_templates($links_map, $this->_uri_params);
	}

	private function get_links_template_detail()
	{
		$links_map = array(
			$this->_questions_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['category'], $this->_questions_uri_components['page']),
			),
			$this->_questions_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['category'], $this->_questions_uri_components['page']),
            )
		);

        return $this->uri->make_templates($links_map, $this->_uri_params);
	}

	private function get_links_template_detail_breadcrumbs()
	{
		$links_map = array(
			$this->_questions_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['page']),
			),
			$this->_questions_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['page']),
            )
		);

        return $this->uri->make_templates($links_map, $this->_uri_params);
	}

	private function _get_search_params_links_template()
	{
		$search_params_links_map = array(
			$this->_questions_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['category'], $this->_questions_uri_components['page']),
			),
			$this->_questions_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($this->_questions_uri_components['country'], $this->_questions_uri_components['page']),
            ),
            $this->_questions_uri_components['page'] => array(
				'type' => 'get',
				'deny' => array($this->_questions_uri_components['page']),
			),
            'order_by' => array(
				'type' => 'get',
				'deny' => array('order_by'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('keywords', $this->_questions_uri_components['page']),
			)
		);

		return $this->uri->make_templates($search_params_links_map, $this->_uri_params, true);

	}

	private function _get_recent_questions()
	{
		$conditions_recent = array(
			'limit' 	=> config('community_sidebar_questions_per_page', 10),
			'start' 	=> 0,
			'order_by' 	=> "date_question-desc"
		);

		model(Elasticsearch_Questions_Model::class)->getQuestions($conditions_recent);
		return model(Elasticsearch_Questions_Model::class)->questions_records;

	}

	private function _get_question_answers(&$question)
	{
		if (logged_in()) {
			$answers_ids = array();
			foreach ($question['answers'] as $answer) {
				if (!empty($answer['count_plus']) || !empty($answer['count_minus'])) {
					$answers_ids[] = $answer['id_answer'];
				}
			}

			if (!empty($answers_ids)) {
				$question['helpful_answers'] = model(Questions_Model::class)->get_helpful_by_answer(implode(',', $answers_ids), $this->session->id);
			}
		}

		// MAKE SUGESTED ANSWERS FOR JSON-LD
		usort($answers, function($a, $b){
			$a_rating = $a['count_plus'] - $a['count_minus'];
			$b_rating = $b['count_plus'] - $b['count_minus'];

			if($a_rating == $b_rating) {
				return 0;
			}

			return ($a_rating > $b_rating) ? -1 : 1;
		});

		return array_slice($answers, 0, 3);

	}

	private function _params_get_keywords($get_parameters, $search_params_links_tpl, &$parameters_data, &$conditions)
	{
		if (isset($get_parameters['keywords']) && !empty($get_parameters['keywords'])) {
			if(mb_strlen(decodeCleanInput($get_parameters['keywords'])) < config('help_search_min_keyword_length')){
				$this->session->setMessages(translate('community_keyword_validation_message', array('[[KEYWORD_LENGTH]]' => config('help_search_min_keyword_length'))), 'error');
				headerRedirect(get_dynamic_url($search_params_links_tpl['keywords'], __COMMUNITY_ALL_URL));
			}

			$keywords = cleanInput(cut_str($get_parameters['keywords']));
			model("Search_Log_Model")->log($keywords);

			$parameters_data['search_params']['keywords'] = array(
				'link' 	=> get_dynamic_url($search_params_links_tpl['keywords'], __COMMUNITY_ALL_URL),
				'title' => $keywords,
				'param' => translate('community_questions_search_params_keywords')
			);

			$parameters_data['meta_params']['[KEYWORDS]'] = $parameters_data['search_keywords'] = $conditions['keywords'] = $keywords;
		}
	}

	private function _params_get_categories($search_params_links_tpl, &$parameters_data, &$conditions, $tmvc)
	{
		$tlanguages = $tmvc->controller->translations->get_languages(array('lang_active' => 1));

		if (isset($this->_uri_params[$this->_questions_uri_components['category']])) {
			$category = intVal(id_from_link($this->_uri_params[$this->_questions_uri_components['category']]));

			$category_questions_en = model(Questions_Model::class)->getCategory($category);
			if(empty($category_questions_en)){
				show_404();
			}

			$categories_questions_i18n = arrayByKey(model(Questions_Model::class)->getCategories_i18n(array('id_category' => $category, 'use_lang' => false)), 'lang_category');
			foreach ($tlanguages as $tlanguage) {
				if(array_key_exists($tlanguage['lang_iso2'], $categories_questions_i18n) && $categories_questions_i18n[$tlanguage['lang_iso2']]['id_category'] == $category){
					$tmvc->routes_priority['category'][$tlanguage['lang_iso2']] = $categories_questions_i18n[$tlanguage['lang_iso2']]['url'];
				} else{
					$tmvc->routes_priority['category'][$tlanguage['lang_iso2']] = $category_questions_en['url'];
				}
			}

			$category_info = $category_questions_en;
			if(__SITE_LANG != 'en' && !empty($categories_questions_i18n[__SITE_LANG]) && $categories_questions_i18n[__SITE_LANG]['id_category'] == $category){
				$category_info = $categories_questions_i18n[__SITE_LANG];
			}

			$parameters_data['search_params']['category'] = array(
				'link' 	=> get_dynamic_url($search_params_links_tpl[$this->_questions_uri_components['category']], __COMMUNITY_ALL_URL),
				'title' => $category_info['title_cat'],
				'param' => translate('community_questions_search_params_category')
			);

			$parameters_data['search_category'] = $conditions['id_category'] = $category;
			$parameters_data['meta_params']['[CATEGORY]'] = $category_info['title_cat'];
		}
	}

	private function _params_get_country($search_params_links_tpl, &$parameters_data, &$conditions)
	{
		if (isset($this->_uri_params[$this->_questions_uri_components['country']])) {
			$country = intVal(id_from_link($this->_uri_params[$this->_questions_uri_components['country']]));

			$country_info = model(Country_Model::class)->get_country($country);
			if(empty($country_info)){
				show_404();
			}

			$parameters_data['search_params']['country'] = array(
				'link' 	=> get_dynamic_url($search_params_links_tpl[$this->_questions_uri_components['country']], __COMMUNITY_ALL_URL),
				'title' => $country_info['country'],
				'param' => translate('community_questions_search_params_country')
			);

			$parameters_data['search_country'] = $conditions['id_country'] = $country;
			$parameters_data['meta_params']['[COUNTRY]'] = $country_info['country'];
		}
	}

	private function _params_get_page(&$page, &$conditions)
	{
		if (isset($this->_uri_params[$this->_questions_uri_components['page']])) {
            $this->_uri_params[$this->_questions_uri_components['page']] = (int) $this->_uri_params[$this->_questions_uri_components['page']];
            if($this->_uri_params[$this->_questions_uri_components['page']] <= 0){
                show_404();
            }

            $page = $this->_uri_params[$this->_questions_uri_components['page']];
		}
		($page <= 1) ?: $data['meta_params']['[PAGE]'] = $page;
		$conditions['page'] = $page;
	}

	private function _params_get_sort($get_parameters, &$parameters_data, &$conditions)
	{
		if (isset($get_parameters['order_by']) && !empty($get_parameters['order_by'])) {
			if(array_key_exists($get_parameters['order_by'], $this->_list_sort_by)){
				$conditions['order_by'] = $get_parameters['order_by'];
				$parameters_data['order_by'] = $this->_list_sort_by[$get_parameters['order_by']];
			}
		}
	}

	private function _populate_sort_list()
	{
		$this->_list_sort_by = array(
			'popular-desc'   		=> translate('community_sort_most_popular'),
			'date_question-desc'  	=> translate('community_sort_newest'),
			'date_question-asc' 	=> translate('community_sort_oldest'),
			'count_answers-desc'  	=> translate('community_sort_most_answers'),
			'count_answers-asc'  	=> translate('community_sort_fewest_answers'),
		);
	}

	public function index()
	{
		$this->_populate_sort_list();

		$tmvc = tmvc::instance();

		//region get questions
        $conditions = array(
			"order_by" 						=> "popular-desc",
			"aggregate_counter_country" 	=> true,
			"aggregate_counter_category" 	=> true,
			'per_p' 						=> config('community_questions_main_per_page', 10),
			'page' 							=> 0
		);

        model(Elasticsearch_Questions_Model::class)->getQuestions($conditions);
		//endregion get questions

		$this->_uri_params = array_filter($this->uri->uri_to_assoc(1, $tmvc->route_url_segments));
		$this->_questions_uri_components = $tmvc->site_urls['questions/index']['replace_uri_components'];

		$data = array(
            'webpackData' 	=> [
				'styleCritical' => 'community'
			],
            'current_page' 				=> 'questions',
            'page_title'                => translate('community_popular_questions_page_title'),
			'header_out_content' 		=> 'new/questions/header_view',
			'header_content' 		    => 'new/questions/categories_view',
			'main_content' 				=> 'new/questions/questions_view',
			'sidebar_right_content' 	=> 'new/questions/sidebar_view',
			'list_sort_by' 				=> $this->_list_sort_by,
			'quest_cats' 				=> $this->_get_categories(),
			'countries' 				=> $this->_get_countries(),
			'questions' 				=> model(Elasticsearch_Questions_Model::class)->questions_records,
			'questions_counter' 		=> (int) model(Elasticsearch_Questions_Model::class)->questions_count,
        	'counter_country' 			=> model(Elasticsearch_Questions_Model::class)->aggregates['counter_country'],
			'counter_category' 			=> model(Elasticsearch_Questions_Model::class)->aggregates['counter_category'],
			'recent_questions'			=> $this->_get_recent_questions(),
			'links_tpl'					=> $this->get_links_template_all(),
            'questions_uri_components'	=> $this->_questions_uri_components,
            'complete_profile'          => session()->__get('completeProfile'),
            'googleAnalyticsEvents'     => true,
		);

		$this->view->assign($data);
		$this->view->display('new/questions/index_template_view');
    }

	public function questions()
	{
		$this->_populate_sort_list();
		$tmvc = tmvc::instance();
		$page = 1;
		$per_p = config('community_questions_all_per_page', 10);
		$parameters_data = array();

		//region get uri params and components
		$this->_uri_params = array_filter($this->uri->uri_to_assoc(1, $tmvc->route_url_segments));
		$this->_questions_uri_components = $tmvc->site_urls['questions/index']['replace_uri_components'];
		//endregion get uri params and components

		checkURI($this->_uri_params, array(
			$this->_questions_uri_components['category'],
			$this->_questions_uri_components['country'],
			$this->_questions_uri_components['page'])
		);

		$search_params_links_tpl = $this->_get_search_params_links_template();

		//conditions for elasticsearch
		$conditions = array(
            'order_by'					 => 'popular-desc',
			'aggregate_counter_country'  => true,
			'aggregate_counter_category' => true,
			'per_p' 					 => $per_p
		);

        $get_parameters = array();
		if (!empty($_SERVER['QUERY_STRING'])) {
			 $get_parameters = array_map(function($get){
				return cleanOutput(cleanInput($get));
			  }, $_GET);
		}

		//region filter and sort
		$this->_params_get_keywords($get_parameters, $search_params_links_tpl, $parameters_data, $conditions);

		$this->_params_get_categories($search_params_links_tpl, $parameters_data, $conditions, $tmvc);

		$this->_params_get_country($search_params_links_tpl, $parameters_data, $conditions);

		$this->_params_get_page($page, $conditions);

		$this->_params_get_sort($get_parameters, $parameters_data, $conditions);
		//endregion filter and sort

		model(Elasticsearch_Questions_Model::class)->getQuestions($conditions);
		$question_list = model(Elasticsearch_Questions_Model::class)->questions_records;
		$questions_count = (int) model(Elasticsearch_Questions_Model::class)->questions_count;
		$questions_counter_country = model(Elasticsearch_Questions_Model::class)->aggregates['counter_country'];
		$questions_counter_category = model(Elasticsearch_Questions_Model::class)->aggregates['counter_category'];

		$links_tpl = $this->get_links_template_all();

		//region pagination
		$paginator_config = array(
            'site_url'      => __COMMUNITY_ALL_URL,
			'prefix'		=> "{$this->_questions_uri_components['page']}/",
            'base_url'      => $links_tpl[$this->_questions_uri_components['page']],
            'first_url'     => get_dynamic_url($search_params_links_tpl[$this->_questions_uri_components['page']], __COMMUNITY_ALL_URL),
            'replace_url'   => true,
            'total_rows'    => (int) model(Elasticsearch_Questions_Model::class)->questions_count,
			'per_page'      => $per_p,
            'cur_page'		=> $page,
            'last_link'     => false,
            'first_link'    => false
		);

		$this->load->library('Pagination', 'pagination');
		$this->pagination->initialize($paginator_config);
		//endregion pagination

		//region increase searched counter and get related
		if(isset($parameters_data['search_params'])){
			$question_ids_list = array_column($question_list, 'id_question');

			model(Questions_Model::class)->modifySearched($question_ids_list);
			model(Elasticsearch_Questions_Model::class)->increment_searched($question_ids_list);

			if(!empty($question_list)){
				$related_conditions = array(
					'related_categories' => array_column($question_list, 'id_category'),
					'related_countries'	 => array_column($question_list, 'id_country'),
					'excluded_ids' 		 => $question_ids_list,
					'per_p' 			 => config('community_questions_related_limit', 3)
				);

				model(Elasticsearch_Questions_Model::class)->getQuestions($related_conditions);
				$parameters_data['related_questions'] = model(Elasticsearch_Questions_Model::class)->questions_records;
				$this->_list_sort_by = array('most-relevant-desc' => 'Most relevant') + $this->_list_sort_by;
			}
		}
		//endregion increase searched counter and get related

		$data = array(
			'webpackData' 	=> [
				'styleCritical' => 'questions.community'
			],
			'current_page' 				=> 'all',
			'page_title' 				=> translate('community_all_questions_page_title'),
			'header_out_content' 		=> 'new/questions/header_view',
			'main_content' 				=> 'new/questions/questions_view',
			'sidebar_right_content'		=> 'new/questions/sidebar_view',
			'questions' 				=> $question_list,
			'questions_counter' 		=> $questions_count,
			'counter_country' 			=> $questions_counter_country,
			'counter_category' 			=> $questions_counter_category,
			'questions_uri_components' 	=> $this->_questions_uri_components,
			'links_tpl' 				=> $links_tpl,
			'link_sort_by'				=> get_dynamic_url($search_params_links_tpl['order_by'], __COMMUNITY_ALL_URL),
			'list_sort_by' 				=> $this->_list_sort_by,
			'quest_cats' 				=> $this->_get_categories(),
			'countries' 				=> $this->_get_countries(),
			'pagination' 				=> $this->pagination->create_links(),
            'recent_questions'			=> $this->_get_recent_questions(),
            'complete_profile'          => session()->__get('completeProfile'),
		);

		$this->view->assign(array_merge($data, $parameters_data));
		$this->view->display('new/questions/index_template_view');
	}

	public function question()
	{
		$tmvc = tmvc::instance();

		$url = $this->uri->segment(3);
        $id_question = id_from_link($url);

		//region get question
		$conditions = array(
			'id_question'					=> $id_question,
			'show_answers'					=> true,
			'aggregate_counter_country' 	=> true,
			'aggregate_counter_category' 	=> true,
			'answers_limit'					=> config('community_answers_per_page', 5)
		);

		model(Elasticsearch_Questions_Model::class)->getQuestions($conditions);
		$elastic_result = model(Elasticsearch_Questions_Model::class)->questions_records;

		if(empty($elastic_result)){
			show_404();
        }

		$question = array_shift($elastic_result);
		//endregionregion get question

		//region check uri slug
		$question_slug = strForURL(cleanOutput($question['title_question'])) . '-' . $question['id_question'];

        if($question_slug !== $url) {
            show_404();
		}
		//endregion check uri slug

		//region country abr
		$origin_country = model(Country_Model::class)->get_country($question['id_country']);
		$question['country_abr'] = $origin_country['abr'];
		//endregion country abr

		$quest_cats = $this->_get_categories();

		$this->_questions_uri_components = $tmvc->site_urls['questions/index']['replace_uri_components'];

		$this->_uri_params = array_filter($this->uri->uri_to_assoc(3, $tmvc->route_url_segments));
		$this->_uri_params[$this->_questions_uri_components['category']] = $quest_cats[$question['id_category']]['url'];

		$links_tpl_breadcrumb = $this->get_links_template_detail_breadcrumbs();
		$links_tpl = $this->get_links_template_detail();

		//region increment views
		if (!is_privileged('user', $question['id_user'])) {
            if (is_null($this->session->__get('is_viewed_' . $id_question))) {
				$this->session->__set('is_viewed_' . $id_question, 1);
                model(Questions_Model::class)->increment_views($id_question, arrayGet($question, 'views', 0) + 1);
                model(Elasticsearch_Questions_Model::class)->counter_question_field_change($id_question, 'views', 1);
            }
        }
		//endregion increment views

		if($question['count_answers'] > 0)
		{
			$question['answers'] = model(Elasticsearch_Questions_Model::class)->aggregates['answers'];
			$sugested_answers = $this->_get_question_answers($question);
		}

		$breadcrumbs = array(
			array(
				'link' => __COMMUNITY_URL,
				'title' => 'Community Help'
			),
			array(
				'link' => replace_dynamic_uri($quest_cats[$question['id_category']]['url'], $links_tpl_breadcrumb[$this->_questions_uri_components['category']], __COMMUNITY_ALL_URL),
				'title' => $quest_cats[$question['id_category']]['title_cat']
			),
			array(
				'link' => replace_dynamic_uri(strForURL($question['country'] . ' ' . $question['id_country']), $links_tpl_breadcrumb[$this->_questions_uri_components['country']], __COMMUNITY_ALL_URL),
				'title' => $question['country']
			),
			array(
				'link' => __COMMUNITY_URL . 'question/' . $question_slug,
				'title' => truncWords(cleanOutput($question['title_question']), 5)
			),
		);

		$data = array(
			'webpackData' 	=> [
				'styleCritical' => 'detail.community'
			],
			'current_page' 				=> 'question_detail',
			'header_out_content' 		=> 'new/questions/header_view',
			'main_content' 				=> 'new/questions/question_detail_view',
			'sidebar_right_content' 	=> 'new/questions/sidebar_view',
			'meta_params' 				=> array(
				'[QUESTION_TITLE]' 			=> cleanOutput(mb_strlen($question['title_question']) > 70 ? trim(substr($question['title_question'], 0, 67)) . '...' : $question['title_question']),
				'[QUESTION_DESCRIPTION]' 	=> cleanOutput(mb_strlen($question['text_question']) > 156 ? trim(substr($question['text_question'], 0, 153)) . '...' : $question['text_question']),
			),
			'breadcrumbs'				=> $breadcrumbs,
			'questions_uri_components'	=> $this->_questions_uri_components,
			'quest_cats'				=> $quest_cats,
			'countries'				 	=> $this->_get_countries(),
			'question'					=> $question,
			'question_detail_link'		=> __COMMUNITY_URL . 'question/' . $question_slug,
			'questions_counter' 		=> (int) model(Elasticsearch_Questions_Model::class)->questions_count,
			'counter_country' 			=> model(Elasticsearch_Questions_Model::class)->aggregates['counter_country'],
			'counter_category' 			=> model(Elasticsearch_Questions_Model::class)->aggregates['counter_category'],
			'recent_questions'			=> $this->_get_recent_questions(),
			'links_tpl'					=> $links_tpl,
            'sugested_answers' 			=> $sugested_answers ?? array(),
            'complete_profile'          => session()->__get('completeProfile'),
		);

		$this->view->assign($data);
		$this->view->display('new/questions/index_template_view');
	}
}

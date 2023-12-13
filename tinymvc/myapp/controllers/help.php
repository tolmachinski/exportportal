<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Help_Controller extends TinyMVC_Controller{

	private $breadcrumbs = array();

	function index(){
		$data['googleAnalyticsEvents'] = true;
		$data['breadcrumbs'][] = array(
			'link' 	=> '',
			'title'	=> translate('breadcrumb_help'),
		);

		$this->view->assign($data);

		$this->view->display('new/header_view');
		$this->view->display('new/help/index_view');
		$this->view->display('new/footer_view');
	}

	public function search()
	{
		$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
		$this->load->model('Elasticsearch_Faq_Model', 'elasticfaq');
		$this->load->model('Elasticsearch_Topics_Model', 'elastictopics');
		$this->load->model('User_Model', 'user');
		$this->load->model('Questions_Model', 'questions');

		global $tmvc;
		$limit = 5 ;
		$data['keywords'] = cleanInput(cut_str($_GET['keywords']));
		$uri_params = array_filter($this->uri->uri_to_assoc(3,$tmvc->route_url_segments));
		model("Search_Log_Model")->log($data['keywords']);

		if (isset($data['keywords']) && strlen($data['keywords']) < config('help_search_min_keyword_length')) {
            $this->session->setMessages(translate("systmess_error_search_keywords_must_have_characters", ["[CHARACTERS]" => config('help_search_min_keyword_length')]), 'errors');
			headerRedirect(get_dynamic_url('help'));
		}

		$conditions = ['keywords' => $data['keywords'], 'start' => 0, 'limit' => $limit];

		$this->elastictopics->get_topics($conditions);
		$this->elasticquestions->getQuestions($conditions);
		$this->elasticfaq->get_faq_list($conditions);

		$data['limit_count'] = $limit;
		$data['topics_count'] = $this->elastictopics->topics_count;
		$data['faq_count'] = $this->elasticfaq->faq_count;
		$data['questions_count'] = $this->elasticquestions->questions_count;
		$data['total_count'] = $data['topics_count'] + $data['faq_count'] + $data['questions_count'];

        $data['faq_list'] = $this->elasticfaq->faq_records;
        $data['faq_tags_list'] = model('faq_tags')->get_list(array('limit' => false));
        usort($data['faq_tags_list'], function($value_1, $value_2){
            if ($value_1['top_priority'] == $value_2['top_priority']) {
                return 0;
            }
            return ($value_1['top_priority'] < $value_2['top_priority']) ? -1 : 1;
        });
        $data['faq_tags_list'] = arrayByKey($data['faq_tags_list'], 'id_tag');
        $data['faq_tags_attached'] = arrayByKey(
            model('faq_tags_relation')->get_list(),
            'id_faq',
            true
        );


		$data['topics'] = $this->elastictopics->topics_records;
		$data['questions'] = $this->elasticquestions->questions_records;

		$data['questions_uri_components'] = $questions_uri_components = $tmvc->site_urls['questions/index']['replace_uri_components'];
		$links_map = array(
			$questions_uri_components['questions'] => array(
				'type' => 'uri',
				'deny' => array($questions_uri_components['category'],$questions_uri_components['country'],$questions_uri_components['page']),
			),
			$questions_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($questions_uri_components['page']),
			),
			$questions_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($questions_uri_components['page']),
            ),
		);

        $data['links_tpl'] = $this->uri->make_templates($links_map, $uri_params);
		if (__SITE_LANG == 'en') {
			$data['quest_cats'] = arrayByKey($this->questions->getCategories(array('visible' => 1)), "idcat");
		} else{
			$data['quest_cats'] = arrayByKey($this->questions->getCategories_i18n(array('visible' => 1)), "idcat");
		}

        $user_ids = array();
        $questions_ids = array();
		foreach ($data['questions'] as $key => $question) {
            $user_ids[$question['id_user']] = $question['id_user'];
            $questions_ids[$question['id_question']] = $question['id_question'];
		}

        $questions_ids = array_filter($questions_ids, function($el) {
			return !empty($el);
		});

        $user_ids = array_filter($user_ids, function($el){
			return !empty($el);
		});

		$users = array();
		if (!empty($user_ids)) {
			$users = arrayByKey($this->user->getSimpleUsers(implode(",", $user_ids), "users.idu, users.user_group, users.user_type, users.`status`, CONCAT(users.fname, ' ', users.lname) as full_name, users.user_photo"), 'idu');
		}

		foreach ($data['questions'] as $key => $question) {
            $data['questions'][$key]['user_type'] = $users[$question['id_user']]['user_type'];
		}

		if (__SITE_LANG == 'en') {
			$data['quest_cats'] = arrayByKey($this->questions->getCategories(array('visible' => 1)), "idcat");
		} else{
			$data['quest_cats'] = arrayByKey($this->questions->getCategories_i18n(array('visible' => 1)), "idcat");
		}

		$this->breadcrumbs[] = [
			'link' 	=> __SITE_URL.'help',
			'title'	=> 'Help'
		];

		$this->breadcrumbs[] = [
			'link' 	=> '',
			'title'	=> 'Search'
		];

		$data['breadcrumbs'] = $this->breadcrumbs;
		$data['current_page'] = "help";
		$data['sidebar_right_content'] = 'new/help/sidebar_view';
		$data['main_content'] = 'new/help/search_page_view';
		$data['footer_out_content'] = 'new/about/bottom_become_partner_view';
		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

}

?>

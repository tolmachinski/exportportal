<?php

use App\Common\Buttons\ChatButton;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Community_questions_Controller extends TinyMVC_Controller {

	private $breadcrumbs = array();

	private function _load_main() {
		$this->load->model('Country_Model', 'country');
		$this->load->model('Questions_Model', 'questions');
		$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
		$this->load->model('User_Model', 'user');
	}

	// DONE
	function administration() {
		checkAdmin('community_questions_administration');

		$this->_load_main();

		$data['title'] = 'Comunity questions administration';
		$data['countries'] = $this->questions->get_countries_by_questions();
		$data['categories'] = $this->questions->getCategories();
		$data['admin_filter'] = 'questions';
		$data['last_questions_id'] = $this->questions->get_questions_last_id();

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/questions/questions_list_view');
		$this->view->display('admin/footer_view');
	}

	// DONE
	function ajax_administration_dt() {
        checkIsAjax();
        checkAdminAjaxDT('community_questions_administration');

        /**
         * @var Questions_Model $questionsModel
         */
        $questionsModel = model(Questions_Model::class);

		$type = $this->uri->segment(3);

		switch ($type) {
			// DONE
            case 'questions':
                $from = (int) $_POST['iDisplayStart'];
                $till = (int) $_POST['iDisplayLength'];

                $dtFilters = dtConditions($_POST, [
                    ['as' => 'added_start',         'key' => 'start_date',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'added_finish',        'key' => 'finish_date',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'id_country',          'key' => 'country',             'type' => 'int'],
                    ['as' => 'id_category',         'key' => 'category',            'type' => 'int'],
                    ['as' => 'id_user',             'key' => 'id_user',             'type' => 'int'],
                    ['as' => 'moderated',           'key' => 'moderated',           'type' => 'int'],
                    ['as' => 'keywords',            'key' => 'keywords',            'type' => 'cleanInput|cut_str'],
                ]);

                $sortBy = flat_dt_ordering($_POST, [
                    'dt_category'           => 'title_cat',
                    'dt_comments'           => 'nr',
                    'dt_answers'            => 'count_answers',
                    'dt_author'             => 'full_name',
                    'dt_county'             => 'country',
                    'dt_title'              => 'title_question',
                    'dt_date'               => 'date_question',
                ]);

                $conditions = array_merge(
                    $dtFilters,
                    [
                        'count_comments'    => 1,
                        'sort_by'           => empty($sortBy) ? ['id_question-desc'] : $sortBy,
                        'limit'             => $from . ',' . $till,
                    ],
                );

				$questions_list = $questionsModel->getQuestions($conditions);
				$records_total = $questionsModel->countQuestions($conditions);

				$output = [
					"iTotalDisplayRecords"  => $records_total,
					"iTotalRecords"         => $records_total,
					'aaData'                => [],
					"sEcho"                 => (int) $_POST['sEcho'],
                ];

				if (empty($questions_list)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($questions_list as $question) {
					$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-callback="moderate_question" data-message="Are you sure want to moderate this question?" title="Moderate question" data-question="' . $question['id_question'] . '"></a>';
					if ($question['moderated'])
						$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated question"/>';

                    if ($question['has_bad_words']) {
                        $bad_words = '<a class="ep-icon ep-icon_user-swear txt-green" title="Contains bad words"/>';
                    } else {
                        $bad_words = '';
                    }

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $question['id_user'], 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChat = $btnChatUser->button();

					$output['aaData'][] = array(
						'dt_id' => $question['id_question'] . '<input type="checkbox" class="check-question pull-left" data-id-question="' .$question['id_question'] . '">',
						'dt_author' => '<div class="tal">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by user" data-value-text="' . $question['full_name'] . '" data-value="' . $question['id_user'] . '" data-title="Author" data-name="id_user"></a>'
								. '<a class="ep-icon ep-icon_user" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($question['full_name']) .'-' . $question['id_user'] . '"' . '></a>'
								. $btnChat
							. "</div>
							<div>" . $question['full_name'] . "</div>",
						'dt_title' => cleanOutput($question['title_question']),
						'dt_text' => cleanOutput($question['text_question']),
						'dt_category' => $question['title_cat'],
						'dt_county' => '<div>'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-value-text="' . $question['country'] . '" data-value="' . $question['id_country'] . '" data-title="Country" data-name="country" title="Filter by country"></a>'
							. '</div>
							<img width="24" height="24" src="' . getCountryFlag($question['country']) . '" title="' . $question['country'] . '" alt="' . $question['country'] . '"/>',
						'dt_date' => getDateFormat($question['date_question']),
						'dt_answers' => $question['count_answers'],
						'dt_comments' => (int)$question['nr'],
						'dt_actions' =>
                            $bad_words .
							$moderate_btn .
							'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit Question" href="'.__SITE_URL.'community_questions/popup_forms/edit_question_admin/' . $question['id_question'] . '" data-title="Edit question" id="question-' . $question['id_question'] . '"></a>
							<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_question" data-message="Are you sure want delete this qustion?" title="Delete question" data-question="'.$question['id_question'].'"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			// DONE
			case "answers":
                $from = (int) $_POST['iDisplayStart'];
                $till = (int) $_POST['iDisplayLength'];

				$dtFilters = dtConditions($_POST, [
                    ['as' => 'added_start',         'key' => 'start_date',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'added_finish',        'key' => 'finish_date',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'id_category',         'key' => 'category',            'type' => 'int'],
                    ['as' => 'id_user',             'key' => 'id_user',             'type' => 'int'],
                    ['as' => 'moderated',           'key' => 'moderated',           'type' => 'int'],
                    ['as' => 'keywords',            'key' => 'keywords',            'type' => 'cleanInput|cut_str'],
                ]);

                $sortBy = flat_dt_ordering($_POST, [
                    'dt_author'             => 'full_name',
                    'dt_title'              => 'title_answer',
                    'dt_question'           => 'title_question',
                    'dt_date'               => 'date_answer',
                    'dt_comments'           => 'count_comments',
                    'dt_likes'              => 'count_plus',
                    'dt_dislike'            => 'count_minus',
                ]);

                $conditions = array_merge(
                    $dtFilters,
                    [
                        'details_question'  => 1,
                        'count_comments'    => 1,
                        'sort_by'           => empty($sortBy) ? ['id_question-desc'] : $sortBy,
                        'limit'             => $from . ',' . $till,
                    ],
                );

                $answers_list = $questionsModel->getAnswers($conditions);
				$records_total = $questionsModel->countAnswers($conditions);

				$output = [
					"iTotalDisplayRecords"  => $records_total,
					"iTotalRecords"         => $records_total,
					'aaData'                => [],
					"sEcho"                 => (int) $_POST['sEcho'],
                ];

				if (empty($answers_list)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($answers_list as $answer) {
					$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-callback="moderate_answer" data-message="Are you sure want moderate this item?" title="Moderate answer" data-answer="' . $answer['id_answer'] . '"></a>';
					if ($answer['moderated']){
						$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated answer"/>';
					}

					$bad_words = '';
                    if ($answer['has_bad_words']) {
                        $bad_words = '<a class="ep-icon ep-icon_user-swear txt-green" title="Contains bad words"/>';
                    }

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $answer['id_user'], 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChat = $btnChatUser->button();

					$output['aaData'][] = array(
						'dt_id' => $answer['id_answer'] . '<input type="checkbox" class="check-answer pull-left" data-answer="' .$answer['id_answer'] . '">',
						'dt_author' => '<div class="tal">
											<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by user" data-value-text="' . $answer['full_name'] . '" data-value="' . $answer['id_user'] . '" data-title="Author" data-name="id_user"></a>
											<a class="ep-icon ep-icon_user" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($answer['full_name']) .'-' . $answer['id_user'] . '"' . '></a>'
											. $btnChat
										.'</div>
										<div>'.$answer['full_name'].'</div>',
						'dt_title' => cut_str_with_dots(cleanOutput($answer['title_answer'], 70)),
						'dt_text' => cut_str_with_dots(cleanOutput($answer['text_answer']), 150),
						'dt_question' => '<p class="w-200 text-nowrap" title="' . cleanOutput($answer['title_question']) . '">' . cleanOutput($answer['title_question']) . '</p>
										  <p class="w-150 text-nowrap" title="' . $answer['title_cat'] . '">' . $answer['title_cat'] . '</p>
										  <img width="24" height="24" src="' . getCountryFlag($answer['country']) . '" title="' . $answer['country'] . '" alt="' . $answer['country'] . '"/>',
						'dt_date' => getDateFormat($answer['date_answer']),
						'dt_comments' => $answer['count_comments'],
						'dt_likes' => $answer['count_plus'],
						'dt_dislike' => $answer['count_minus'],
						'dt_actions' => $bad_words
										.'<a class="ep-icon ep-icon_visible fancybox fancybox.ajax" href="'.__SITE_URL.'community_questions/popup_forms/question_tree/' . $answer['id_question'] . '/answer-' . $answer['id_answer'] . '" data-title="View question" title="View question with answers and comments"></a>'
										.$moderate_btn
										.'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" data-title="Edit answer" title="Edit answer" href="'.__SITE_URL.'community_questions/popup_forms/edit_answer_admin/' . $answer['id_answer'] . '" title="Edit answer"></a>
										<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_answer" title="Delete answer" data-answer="' . $answer['id_answer'] . '" data-message="Are you sure want to delete this answer?"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			// DONE
            case "comments":
                $from = (int) $_POST['iDisplayStart'];
                $till = (int) $_POST['iDisplayLength'];

				$dtFilters = dtConditions($_POST, [
                    ['as' => 'added_start',         'key' => 'start_date',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'added_finish',        'key' => 'finish_date',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'id_user',             'key' => 'id_user',             'type' => 'int'],
                    ['as' => 'moderated',           'key' => 'moderated',           'type' => 'int'],
                    ['as' => 'id_answer',           'key' => 'id_answer',           'type' => 'int'],
                    ['as' => 'keywords',            'key' => 'keywords',            'type' => 'cleanInput|cut_str'],
                ]);

                $sortBy = flat_dt_ordering($_POST, [
                    'dt_answer'                 => 'id_answer',
                    'dt_date'             => 'date_comment',
                ]);

                $conditions = array_merge(
                    $dtFilters,
                    [
                        'details_answer'  => 1,
                        'sort_by'           => empty($sortBy) ? ['id_question-desc'] : $sortBy,
                        'limit'             => $from . ',' . $till,
                    ],
                );

				$comments_list = $questionsModel->getComments($conditions);
				$records_total = $questionsModel->countComments($conditions);

				$output = [
					"iTotalDisplayRecords"  => $records_total,
					"iTotalRecords"         => $records_total,
					"aaData"                => [],
					"sEcho"                 => (int) $_POST['sEcho'],
                ];

				if (empty($comments_list)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($comments_list as $comment) {
					$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" title="Moderate comment" data-callback="moderate_comment" data-message="Are you sure want moderate this comment?" data-comment="' . $comment['id_comment'] . '"></a>';
					if ($comment['moderated']){
						$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated comment"/>';
					}

					$bad_words = '';
                    if ($comment['has_bad_words']) {
                        $bad_words = '<a class="ep-icon ep-icon_user-swear txt-green" title="Contains bad words"/>';
					}

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $comment['id_user'], 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChat = $btnChatUser->button();

					$output['aaData'][] = array(
						'dt_id' 	=> '<input type="checkbox" class="check-comment pull-left" data-comment="' .$comment['id_comment'] . '">'.$comment['id_comment'],
						'dt_author' => '<div class="tal">
											<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by user" data-value-text="' . $comment['full_name'] . '" data-value="' . $comment['id_user'] . '" data-title="Author" data-name="id_user"></a>
											<a class="ep-icon ep-icon_user" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($comment['full_name']) .'-' . $comment['id_user'] . '"' . '></a>'
											. $btnChat
										.'</div>
										<div>'.$comment['full_name'].'</div>',
						'dt_text' 	=> cleanOutput($comment['text_comment']),
						'dt_answer' => '<div class="tal">
											<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by answer" data-value-text="' . cleanOutput($comment['title_answer']) . '" data-value="' . $comment['id_answer'] . '" data-title="Answer" data-name="id_answer"></a>
										</div>
										<div>'.cleanOutput($comment['title_answer']).'</div>',
						'dt_date' => getDateFormat($comment['date_comment']),
						'dt_actions' => $bad_words
										.'<a class="ep-icon ep-icon_visible fancybox fancybox.ajax" href="'.__SITE_URL.'community_questions/popup_forms/question_tree/' . $comment['id_question'] . '/comment-' . $comment['id_comment'] . '" data-title="View question" title="View question with answers and comments"></a>
										'.$moderate_btn.'
										<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" data-title="Edit comment" title="Edit comment" href="'.__SITE_URL.'community_questions/popup_forms/edit_comment_admin/' . $comment['id_comment'] . '" id="comment-' . $comment['id_comment'] . '"></a>
										<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure want to delete this comment?" title="Delete comment" data-callback="delete_comment" data-comment="' . $comment['id_comment'] . '"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			// DONE
            case "question_category":
                $from = (int) $_POST['iDisplayStart'];
                $till = (int) $_POST['iDisplayLength'];

                $sortBy = flat_dt_ordering($_POST, [
                    'dt_id'                 => 'idcat',
                    'dt_title_cat'          => 'title_cat',
                    'dt_visible'            => 'visible_cat',
                ]);

                $conditions = [
                    'sort_by'           => empty($sortBy) ? ['id_question-desc'] : $sortBy,
                    'limit'             => $from . ',' . $till,
                ];

				$quest_cats = $questionsModel->get_categories($conditions);
				$records_total = $questionsModel->get_categories_count($conditions);

				$output = [
					"iTotalDisplayRecords"  => $records_total,
					"iTotalRecords"         => $records_total,
					'aaData'                => [],
					"sEcho"                 => $_POST['sEcho'],
                ];

				if (empty($quest_cats)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($quest_cats as $category) {
					$langs = array();
					$langs_record = array_filter(json_decode($category['translations_data'], true));
					$langs_record_list = array('English');
					if(!empty($langs_record)){
						foreach ($langs_record as $lang_key => $lang_record) {
							if($lang_key == 'en'){
								continue;
							}

							$langs[] = '<li>
											<div>
												<span class="display-ib_i lh-30 pl-5 pr-10">'.$lang_record['lang_name'].'</span>
												<a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="remove_category_question_i18n" data-category="' . $category['idcat'] . '" data-lang="'.$lang_record['abbr_iso2'].'" title="Delete" data-message="Are you sure you want to delete category translation?" href="#" ></a>
												<a href="'.__SITE_URL.'community_questions/popup_forms/edit_question_category_i18n/'.$category['idcat'].'/'.$lang_record['abbr_iso2'].'" data-title="Edit category translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
											</div>
										</li>';
							$langs_record_list[] = $lang_record['lang_name'];
						}
						$langs[] = '<li role="separator" class="divider"></li>';
					}

					$langs_dropdown = '<div class="dropdown">
										<a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
										<ul class="dropdown-menu">
											'.implode('', $langs).'
											<li><a href="'.__SITE_URL.'community_questions/popup_forms/create_question_category_i18n/'.$category['idcat'].'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
										</ul>
									</div>';

					$output['aaData'][] = array(
						'dt_id' => $category['idcat'],
						'dt_title_cat' => $category['title_cat'],
						'dt_visible' => ($category['visible_cat'])?'Yes':'No',
						'dt_actions' => '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'community_questions/popup_forms/edit_question_category/' . $category['idcat'] . '" data-table="dtQuestionCategory" data-title="Edit category" title="Edit category"></a>
										<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeCategory" data-category="'.$category['idcat'].'" data-message="Are you sure you want to delete this category?" href="#" title="Delete category"></a>',
						'dt_tlangs' => $langs_dropdown,
						'dt_tlangs_list' => implode(', ', $langs_record_list)
					);
				}

				jsonResponse('', 'success', $output);
			break;
		}
	}

	// DONE
	function my() {
		if (!logged_in()) {
			$this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
			headerRedirect(__SITE_URL . 'login');
		}

		if(!have_right_or('manage_community_questions')){
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"), 'errors');
			headerRedirect(__SITE_URL);
		}

		$this->_load_main();
		$this->view->assign('title', 'Community questions');

		if (__SITE_LANG == 'en') {
			$data['question_categories'] = $this->questions->getCategories(array('visible' => 1));
		} else{
			$data['question_categories'] = $this->questions->getCategories_i18n(array('visible' => 1));
		}

		$data['question_countries'] = $this->country->get_countries();

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->questionsEpl($data);
        } else {
            $this->questionsAll($data);
        }
	}

    private function questionsEpl($data){
        $data['templateViews'] = [
            'mainOutContent'    => 'questions/my/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function questionsAll($data){
        views(["new/header_view", "new/questions/my/index_view", "new/footer_view"], $data);
    }

	// DONE
	public function ajax_my_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('manage_community_questions');

        /**
         * @var Questions_Model $questionsModel
         */
        $questionsModel = model(Questions_Model::class);

        $from = (int) $_POST['iDisplayStart'];
        $till = (int) $_POST['iDisplayLength'];

        $dtFilters = dtConditions($_POST, [
            ['as' => 'added_start',         'key' => 'start_from',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',        'key' => 'start_to',            'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'id_category',         'key' => 'category',            'type' => 'int'],
            ['as' => 'id_country',          'key' => 'country',             'type' => 'int'],
            ['as' => 'keywords',            'key' => 'keywords',            'type' => 'cleanInput|cut_str'],
        ]);

        $sortBy = flat_dt_ordering($_POST, [
			'dt_date'          => 'date_question',
			'dt_last_answer'   => 'last_date_answer',
			'dt_count_answers' => 'count_answers'
        ]);

        $conditions = array_merge(
            $dtFilters,
            [
                'with_last_answer'  => true,
                'id_user'           => privileged_user_id(),
                'sort_by'           => empty($sortBy) ? ['date_question-desc'] : $sortBy,
                'limit'             => $from . ',' . $till,
            ],
        );

		$records_total = $questionsModel->countQuestions($conditions);
		$questions = $questionsModel->getQuestions($conditions);

		$output = [
			"iTotalDisplayRecords"  => $records_total,
			"iTotalRecords"         => $records_total,
			"aaData"                => [],
			"sEcho"                 => (int) $_POST['sEcho'],
        ];

        if (empty($questions)) {
            jsonResponse('', 'success', $output);
        }

		foreach ($questions as $question) {
			$last_answer_date = getDateFormat($question['last_date_answer'], 'Y-m-d H:i:s', 'j M, Y H:i');
			$dt_last_answer = '&mdash;';
			if($question['count_answers'] > 0){
				$dt_last_answer = "{$last_answer_date} <br> by <strong>{$question['last_answerer_full_name']}</strong>";
			}

			$actions = array();
			//if($question['count_answers'] == 0 && $question['moderated'] == 0){
				$actions[] = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" ' . addQaUniqueIdentifier('community_questions_my_dropdown_edit') . ' href="' . __SITE_URL . 'community_questions/popup_forms/edit_question/' . $question['id_question'] . '" title="Edit question" data-title="Edit question">
									<i class="ep-icon ep-icon_pencil"></i>
									<span>Edit</span>
								</a>';
			//}

			//if($question['count_answers'] == 0){
				$actions[] = '<a class="dropdown-item confirm-dialog" ' . addQaUniqueIdentifier('community_questions_my_dropdown_delete') . ' data-callback="delete_question" data-question="'.$question['id_question'].'" data-message="Are you sure you want to delete this question?" href="#" title="Delete question">
									<i class="ep-icon ep-icon_trash-stroke"></i>
									<span>Delete</span>
								</a>';
			//}

			$actions[] = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" ' . addQaUniqueIdentifier('community_questions_my_dropdown_details') . ' href="' . __SITE_URL . 'community_questions/popup_forms/detail/' . $question['id_question'] . '" data-mw="740" title="Question details" data-title="Question details">
							<i class="ep-icon ep-icon_info-stroke"></i>
							<span>Question details</span>
						</a>';

            $output['aaData'][] = array(
				'dt_text' => '<div class="questions__detail flex-card__float">
								<div class="questions__row">
									<div class="questions__subject">
										<img width="24" height="24" class="questions__flag" src="'.getCountryFlag($question['country']).'" alt="'.$question['country'].'" title="'.$question['country'].'">
										<span class="questions__subject-name">
											<span class="grid-text">
												<span class="grid-text__item">
													'.cleanOutput($question['title_question']).'
												</span>
											</span>
										</span>
									</div>
									<span class="questions__category">'.$question['title_cat'].'</span>
								</div>
								<div class="questions__message">
									<span class="grid-text">
										<span class="grid-text__item">
											'.cleanOutput($question['text_question']).'
										</span>
									</span>
								</div>
							</div>',
                'dt_date' => getDateFormat($question['date_question']),
                'dt_country' => $question['country'],
				'dt_count_answers' => $question['count_answers'],
				'dt_last_answer' => $dt_last_answer,
				'dt_actions' => '<div class="dropdown tac">
									<a class="dropdown-toggle" ' . addQaUniqueIdentifier('community_questions_my_dropdown') . ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="ep-icon ep-icon_menu-circles"></i>
									</a>
									<div class="dropdown-menu dropdown-menu-right">
										'.implode('', $actions).'
									</div>
								</div>'
			);
		}

		jsonResponse('', 'success', $output);
	}

	// DONE
	function question_categories() {
		checkAdmin('community_questions_administration');

		$this->view->assign('title', 'Question\'s categories');
		$this->view->display('admin/header_view');
		$this->view->display('admin/questions/index_view');
		$this->view->display('admin/footer_view');
	}

	// DONE
	function ajax_questions_operation() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate('systmess_error_should_be_logged_in'));

		$this->load->model("Questions_Model", "questions");
		$this->load->model('User_Model', 'user');
		$op = $this->uri->segment(3);
		$id_user = privileged_user_id();
		switch ($op) {
			// DONE
            case 'add_question':
				if(!have_right('manage_community_questions')){
					jsonResponse(translate('general_no_permission_message'));
				}

                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => translate('community_add_question_country_label'),
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'category',
                        'label' => translate('community_add_question_category_label'),
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => translate('community_add_question_question_title_label'),
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('community_add_question_description_label'),
                        'rules' => array('required' => '', 'max_len[1000]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_country = (int) request()->get('country');
                $country_info = model(Country_Model::class)->get_country($id_country);
                if(empty($country_info)){
					jsonResponse(translate('community_country_not_exist_message'));
				}

				$id_category = (int) request()->get('category');
				$category_info = $this->questions->getCategory($id_category);
                if(empty($category_info)){
                    jsonResponse(translate('community_category_not_exist_message'));
				}

				$title = request()->get('title');
                $text = request()->get('text');

                $data = array(
                    'id_user' 			=> $id_user,
                    'id_country' 		=> $id_country,
                    'id_category' 		=> $id_category,
                    'title_question' 	=> $title,
					'text_question' 	=> $text,
					'has_bad_words' 	=> model(Elasticsearch_Badwords_Model::class)->is_clean($text . ' ' . $title, __SITE_LANG) ? 0 : 1
                );

                if ($id_question = $this->questions->setQuestion($data)) {
                    model(User_Statistic_Model::class)->set_users_statistic(array(
                        $id_user => array(
                            'ep_questions_wrote' => 1
                        )
                    ));

                    $responseContent = translate('systmess_success_community_question_add_content', ['{{START_LINK}}' => '<a class="link" href="mailto:support@exportportal.com" target="_blank">', '{{END_LINK}}' => '</a>']);
                    $questionLink =  __COMMUNITY_URL . 'question/' . strForURL(cleanOutput($data['title_question'])) . '-' . $id_question;

                    model(Elasticsearch_Questions_Model::class)->index($id_question);
                    jsonResponse(translate('systmess_success_community_question_add'), 'success', ['content' => $responseContent, 'link' => $questionLink]);
                } else {
                    jsonResponse(translate('systmess_error_db_insert_error'));
                }
			break;
			// DONE
			case 'edit_question':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('general_no_permission_message'));
				}

                $validator_rules = array(
                    array(
                        'field' => 'category',
                        'label' => translate('community_edit_question_category_info_label'),
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'country',
                        'label' => translate('community_edit_question_country_info_label'),
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => translate('community_add_question_question_title_label'),
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('community_comment_text_label'),
                        'rules' => array('required' => '', 'max_len[1000]' => '')
					),
                    array(
                        'field' => 'id_question',
                        'label' => translate('community_edit_question_question_info_label'),
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_question = (int) request()->get('id_question');
				$question_info = $this->questions->getQuestion($id_question);

				if (empty($question_info)) {
                    jsonResponse(translate('community_question_does_not_exist'));
				}

				if (!is_privileged('user', $question_info['id_user'], 'manage_community_questions') && !have_right('community_questions_administration')) {
					jsonResponse(translate('general_no_permission_message'));
				}

				if ($question_info['moderated'] == 1){
					jsonResponse(translate('community_edit_question_moderated'));
				}

				$id_category = (int) request()->get('category');
				$category_info = $this->questions->getCategory($id_category);
                if(empty($category_info)){
                    jsonResponse(translate('community_category_not_exist_message'));
				}

                $id_country = (int) request()->get('country');
                $country_info = model(Country_Model::class)->get_country($id_country);
                if(empty($country_info)){
					jsonResponse(translate('community_country_not_exist_message'));
				}

                $title = request()->get('title');
                $text = request()->get('text');

                $data = array(
                    'id_category' 		=> $id_category,
                    'id_country' 		=> $id_country,
                    'title_question' 	=> $title,
                    'text_question' 	=> $text,
                    'has_bad_words' 	=> model(Elasticsearch_Badwords_Model::class)->is_clean($text . ' ' . $title, $question_info['lang']) ? 0 : 1
                );

                if ($this->questions->updateQuestion($id_question, $data)) {
                    model(Elasticsearch_Questions_Model::class)->updateQuestion($id_question);

                    jsonResponse(translate('systmess_success_community_question_update'), 'success', array('question' => $id_question, 'title' => $title, 'description' => $text, 'category' => $category_info['title_cat']));
                } else {
                    jsonResponse(translate('systmess_error_db_insert_error'));
                }
			break;
			// DONE
            /** deleted on 2021.07.05 */
			/* case 'detail_question':
				if(!have_right('manage_community_questions')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_question = intval($_POST['question']);
				$question = $this->questions->getQuestion($id_question);
				if (empty($question)) {
					jsonResponse('Error: The question does not exist.');
				}

				if(!is_privileged('user', $question['id_user'])){
					jsonResponse('Error: The question does not exist.');
				}

				$question['answers'] = $this->questions->getAnswers(array('id_questions' => $id_question));
				if (!empty($question['answers'])) {
					$answers_keys = implode(',', array_keys(arrayByKey($question['answers'], 'id_answer')));
					$question['helpful_answers'] = $this->questions->get_helpful_by_answer($answers_keys, $id_user);
				}

				$this->view->assign(array('question' => $question));
				$content = $this->view->fetch($this->view_folder.'questions/my/question_view');
				jsonResponse('', 'success', array('content' => $content));
			break; */
			// DONE
            /** deleted on 2021.09.14 */
			/*case 'get_question_categories':
				$validator_rules = array(
					array(
						'field' => 'question_lang',
						'label' => 'Language',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$lang_category = cleanInput($_POST['question_lang']);
				if ($lang_category == 'en') {
					$question_categories = $this->questions->getCategories(array('visible' => 1));
				} else{
					$tlang = $this->translations->get_language_by_iso2($lang_category, array('lang_active' => 1, 'lang_url_type' => "domain"));
					if(empty($tlang)){
						jsonResponse('Error: Language does not exist.');
					}

					$question_categories = $this->questions->getCategories_i18n(array('visible' => 1, 'lang_category' => $lang_category));
				}

				jsonResponse('', 'success', array('categories' => $question_categories));
			break;
            */
			// DONE
			case 'check_new':
				checkAdminAjax('community_questions_administration');

				$lastId = (int)$_POST['lastId'];
				$questions_count = $this->questions->get_count_new_questions($lastId);

				if ($questions_count > 0) {
					$lastId = $this->questions->get_questions_last_id();
				}

				jsonResponse('', 'success', array('nr_new' => $questions_count, 'lastId' => $lastId));
			break;
			// DONE
			case 'delete':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$validator_rules = array(
                    array(
                        'field' => 'question',
                        'label' => translate('community_edit_question_question_info_label'),
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_question = intVal($_POST['question']);
				$question_info = $this->questions->getQuestion($id_question);

				if (empty($question_info)) {
                    jsonResponse(translate('community_question_does_not_exist'));
				}

				if (!is_privileged('user', $question_info['id_user'], 'manage_community_questions') && !have_right('community_questions_administration')) {
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if ($this->questions->deleteQuestion($id_question)) {
                    $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
                    $this->elasticquestions->delete($id_question);

					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
                        $question_info['id_user'] => array(
                            'ep_questions_wrote' => -1
                        )
					));

					jsonResponse(translate('community_question_deleted_message'), 'success');
				}

				jsonResponse(translate('systmess_error_db_insert_error'));
			break;
			// DONE
			case 'delete_multiple':
				if(!have_right('community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$checked_questions = cleanInput($_POST['question']);
				$checked_questions = explode(',', $checked_questions);
				$checked_questions = array_filter($checked_questions);

				if (empty($checked_questions)) {
					jsonResponse('Error: There are no question(s) to be deleted.');
				}

				$checked_questions = implode(',', $checked_questions);
				$questions = $this->questions->getSimpleQuestions(array(
					'questions_list' => $checked_questions,
					'columns' => 'id_user'
				));

				$this->questions->deleteQuestion($checked_questions);
				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				$this->elasticquestions->delete($checked_questions);

				$this->load->model('User_Statistic_Model', 'statistic');
				$statistic_array = $this->statistic->prepare_user_array(
						$questions, 'id_user', array('ep_questions_wrote' => -1)
				);
				$this->statistic->set_users_statistic($statistic_array);

				jsonResponse('The question(s) has been deleted', 'success');
			break;
			// DONE
			case 'moderate':
				if(!have_right('community_questions_administration')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$checked_questions = cleanInput($_POST['question']);
				$checked_questions = explode(',', $checked_questions);
				$checked_questions = array_filter($checked_questions);

				if (empty($checked_questions)) {
					jsonResponse('Error: There are no question(s) to be moderated.');
				}

                $this->questions->moderateQuestion(implode(',', $checked_questions));
				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				foreach($checked_questions as $id_question) {
					$this->elasticquestions->updateQuestion($id_question, array('moderated' => 1));
				}

				jsonResponse('The question(s) has been moderated successfully.', 'success');
			break;
		}
        jsonResponse('No operation found');
	}

	// DONE
	function ajax_question_categories_operation() {
		if (!isAjaxRequest())
			headerRedirect();

		checkAdminAjaxModal('community_questions_administration');

		$this->load->model('Questions_Model', 'questions');

		$type = $this->uri->segment(3);
		switch ($type){
			// DONE
			case "create_category":
				$validator_rules = array(
					array(
						'field' => 'title_cat',
						'label' => 'Category\'s title',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'visible_cat',
						'label' => 'Visible',
						'rules' => array('required' => '', 'alpha_numeric' => '')
					),
					array(
						'field' => 'on_main_page',
						'label' => 'On main page',
						'rules' => array('alpha_numeric' => '')
					),
					array(
						'field' => 'order',
						'label' => 'Order',
						'rules' => array('integer' => 0)
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$insert = array(
					'title_cat' 		=> cleanInput($_POST['title_cat']),
					'visible_cat' 		=> cleanInput($_POST['visible_cat']),
					'on_main_page' 		=> cleanInput($_POST['on_main_page']),
					'icon' 				=> $_POST['icon'],
					'order_number' 		=> (int) $_POST['order'],
					'translations_data' => json_encode(array('en' => array('lang_name' => 'English', 'abbr_iso2' => 'en')))
				);

				if($this->questions->existCategory(array('title_cat' => $insert['title_cat']))){
					jsonResponse('Error: This category already exists.');
				}

				if(cleanInput($_POST['on_main_page']) == 1){
					$count = $this->questions->get_categories_count(array('on_main_page' => 1));
					if($count >= 6){
						jsonResponse('Error: Masximum 6 main page categories!');
					}
				}

				if($id_category = $this->questions->setCategory($insert)){
					$update = array(
						'url' => strForUrl($insert['title_cat']) . "-" . $id_category
					);
					$this->questions->updateCategory($id_category, $update);
					jsonResponse('The category was added successfully.', 'success');
				}

				jsonResponse('Error: The category has not been added.');
			break;
			// DONE
			case "create_category_i18n":
				$validator_rules = array(
					array(
						'field' => 'id_category',
						'label' => 'Category info',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'lang_category',
						'label' => 'Language',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title_cat',
						'label' => 'Title',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_category = intval($_POST['id_category']);
				$category = $this->questions->getCategory($id_category);
				if (empty($category)){
					jsonResponse('Error: This category does not exist.');
				}

				$lang_category = cleanInput($_POST['lang_category']);
				$tlang = $this->translations->get_language_by_iso2($lang_category);
				if(empty($tlang)){
					jsonResponse('Error: Language does not exist.');
				}

				$translations_data = json_decode($category['translations_data'], true);
				if(array_key_exists($lang_category, $translations_data)){
					jsonResponse('Error: Category translation for this language already exist.');
				}

				$translations_data[$lang_category] = array(
					'lang_name' => $tlang['lang_name'],
					'abbr_iso2' => $tlang['lang_iso2']
				);

				$insert = array(
					'id_category' => $id_category,
					'title_cat' => cleanInput($_POST['title_cat']),
					'url' => strForUrl(cleanInput($_POST['title_cat'])) . "-" . $id_category,
					'lang_category' => $lang_category
				);

				if($id_category_i18n = $this->questions->setCategory_i18n($insert)){
					$update = array(
						'translations_data' => json_encode($translations_data)
					);
					$this->questions->updateCategory($id_category, $update);
					jsonResponse('The category translation has been added successfully.', 'success');
				}

				jsonResponse('Error: Could not add category translation now. Please try again later.');
			break;
			// DONE
			case "edit_category":
				$validator_rules = array(
					array(
						'field' => 'title_cat',
						'label' => 'Category\'s title',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'visible_cat',
						'label' => 'Visible',
						'rules' => array('required' => '', 'alpha_numeric' => '')
					),
					array(
						'field' => 'order',
						'label' => 'Order',
						'rules' => array('integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$idcat = intval($_POST['id_category']);
				if (!$this->questions->existCategory(array('idcat' => $idcat))){
					jsonResponse('Error: This category doesn\'t exist');
				}

				if(cleanInput($_POST['on_main_page']) == 1){
					$count = $this->questions->get_categories_count(array('on_main_page' => 1));
					$current = $this->questions->getCategory($idcat);
					if($count >= 6 && $current['on_main_page'] == 0){
						jsonResponse('Error: Masximum 6 main page categories!');
					}
				}

				$update = array(
					'title_cat' 		=> cleanInput($_POST['title_cat']),
					'url' 				=> strForUrl(cleanInput($_POST['title_cat'])) . "-" . $idcat,
					'on_main_page' 		=> cleanInput($_POST['on_main_page']),
					'icon' 				=> $_POST['icon'],
					'order_number' 		=> (int) $_POST['order'],
					'visible_cat' 		=> cleanInput($_POST['visible_cat']),
					'translations_data' => json_encode(array('en' => array('lang_name' => 'English', 'abbr_iso2' => 'en')))
				);

				$exist_category = $this->questions->existCategory(array('title_cat' => $update['title_cat']));
				if ( $exist_category && $idcat != $exist_category ){
					jsonResponse('Error: this category already exists');
				}

				if ($this->questions->updateCategory($idcat, $update)){
					jsonResponse('Category was successfully updated.', 'success');
				} else{
					jsonResponse('Error: The category wasn\'t updated.');
				}
			break;
			// DONE
			case "edit_category_i18n":
				$validator_rules = array(
					array(
						'field' => 'id_category_i18n',
						'label' => 'Category info',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title_cat',
						'label' => 'Title',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_category_i18n = intval($_POST['id_category_i18n']);
				$category = $this->questions->getCategory_i18n(array('id_category_i18n' => $id_category_i18n));
				if(empty($category)){
					jsonResponse('Error: The category translation does not exist.');
				}

				$update = array(
					'id_category' => $category['id_category'],
					'title_cat' => cleanInput($_POST['title_cat']),
					'url' => strForUrl(cleanInput($_POST['title_cat'])) . "-" . $category['id_category']
				);

				if($this->questions->updateCategory_i18n($id_category_i18n, $update)){
					jsonResponse('The translation has been successfully updated.', 'success');
				}

				jsonResponse('Error: Could not update translation now. Please try again later.');
			break;
			// DONE
			case 'remove_category':
				$validator_rules = array(
					array(
						'field' => 'category',
						'label' => 'Category info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_category = intval($_POST['category']);
				if($this->questions->countQuestions(array('id_category' => $id_category))){
					jsonResponse('Error: There are questions in this category. Please delete all questions from this category first.');
				}

				$this->questions->deleteCategory($id_category);
				jsonResponse('Category was successfully deleted.', 'success');
			break;
			// DONE
			case 'remove_category_i18n':
				$validator_rules = array(
					array(
						'field' => 'category',
						'label' => 'Category info',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'lang_category',
						'label' => 'Language',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_category = intval($_POST['category']);
				$category = $this->questions->getCategory($id_category);
				if (empty($category)){
					jsonResponse('Error: This category does not exist.');
				}

				$lang_category = cleanInput($_POST['lang_category']);
				$tlang = $this->translations->get_language_by_iso2($lang_category);
				if(empty($tlang)){
					jsonResponse('Error: Language does not exist.');
				}

				if($this->questions->countQuestions(array('id_category' => $id_category, 'lang_question' => $lang_category))){
					jsonResponse('Error: There are questions in this category. Please delete all questions from this category first.');
				}

				$translations_data = json_decode($category['translations_data'], true);
				unset($translations_data[$lang_category]);
				$update = array(
					'translations_data' => json_encode($translations_data)
				);
				$this->questions->updateCategory($id_category, $update);

				$this->questions->deleteCategory_i18n(array('id_category' => $id_category, 'lang_category' => $lang_category));
				jsonResponse('Category has been successfully deleted.', 'success');
			break;
		}
	}

	// DONE
	function answers_administration() {
		checkAdmin('community_questions_administration');

		$this->_load_main();

		$data['title'] = 'Comunity questions answers administration';
		$data['categories'] = $this->questions->getCategories();
		$data['admin_filter'] = 'answers';
		$data['last_answers_id'] = $this->questions->get_answers_last_id();

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/questions/answers_list_view');
		$this->view->display('admin/footer_view');
	}

	// DONE
	function ajax_answers_operation() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate('systmess_error_should_be_logged_in'));

		$this->load->model("Questions_Model", "questions");
		$this->load->model('User_Model', 'user');
		$op = $this->uri->segment(3);
		$id_user = privileged_user_id();

		switch ($op) {
			// DONE
			case 'check_new':
				checkAdminAjax('community_questions_administration');

				$lastId = (int)$_POST['lastId'];
				$answers_count = $this->questions->get_count_new_answers($lastId);

				if ($answers_count > 0) {
					$lastId = $this->questions->get_answers_last_id();
				}

				jsonResponse('', 'success', array('nr_new' => $answers_count, 'lastId' => $lastId));
			break;
			// DONE
			case 'add_answer':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('general_no_permission_message'));
				}

				$this->load->model('Notify_Model', 'notify');

				$validator_rules = array(
					array(
						'field' => 'text',
						'label' => translate('community_answer_text_field'),
						'rules' => array('required' => '', 'max_len[1000]' => '')
					)
				);
				$validator = $this->validator;
				$validator->set_rules($validator_rules);

				if (!$validator->validate())
					jsonResponse($validator->get_array_errors());

                $id_question = intVal($this->uri->segment(4));
				$question_info = $this->questions->getQuestion($id_question);
				if (empty($question_info)) {
					jsonResponse(translate('community_question_does_not_exist'));
				}

                $this->load->model('Elasticsearch_Badwords_Model', 'elastic_bad_words');

                $title = request()->get('title');
                $text = request()->get('text');

				$data = array(
					'id_user' => $id_user,
					'id_question' => $id_question,
					'title_answer' => 'default_title',
					'text_answer' => $text,
                    'has_bad_words' => $this->elastic_bad_words->is_clean($text . ' ' . $title, __SITE_LANG) ? 0 : 1
				);

				if ($id_answer = $this->questions->setAnswer($data)) {
                    $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
					$this->elasticquestions->indexAnswer($id_answer, $id_question);
					$this->elasticquestions->counter_question_field_change($id_question, 'count_answers', 1);

					$data_systmess = [
						'mess_code' => 'question_user_answered_question',
						'id_users'  => [$question_info['id_user']], //array
						'replace'   => [
							'[TITLE]' => cleanOutput($question_info['title_question']),
							'[USER]'  => cleanOutput(user_name_session()),
							'[LINK]'  => get_static_url('community_questions/my')
						],
						'systmess' => true
					];


					$this->notify->send_notify($data_systmess);

					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
						$id_user => array(
							'ep_answers_wrote' => 1
						)
					));

					$data['question'] = $question_info;
					$data['answer'] = $this->questions->getAnswer($id_answer);

                    /**
                     * @author Vasile Cristel
                     * @todo Remove $content [13.11.2021]
                     * old view that is no longer used
                    */
                    //$content = $this->view->fetch($this->view_folder.'new/questions/item_answer_response_view', $data);

                    $responseContent = translate('systmess_success_community_answer_add_content', ['{{START_LINK}}' => '<a class="link" href="mailto:support@exportportal.com" target="_blank">', '{{END_LINK}}' => '</a>']);

					jsonResponse(translate('systmess_success_community_answer_add', ['{{QUESTION_TITLE}}' => strtoupper(cleanOutput($question_info['title_question']))]), 'success', ['question' => $id_question, 'answers_count' => $question_info['count_answers']+1, 'responseContent' => $responseContent]);
				} else{
					jsonResponse(translate('systmess_error_db_insert_error'));
				}
			break;
			// DONE
			case 'edit_answer':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('general_no_permission_message'));
				}

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'answer',
						'label' => translate('community_answer_info_field'),
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'text',
						'label' => translate('community_answer_text_field'),
						'rules' => array('required' => '', 'max_len[5000]' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_edited_answer = (int) request()->get('answer');

				$data['answer'] = $this->questions->getAnswer($id_edited_answer);
				if (empty($data['answer'])) {
					jsonResponse(translate('community_answer_does_not_exist_message'));
				}

				if (have_right('manage_community_questions') && !is_privileged('user', $data['answer']['id_user'])){
					jsonResponse(translate('community_answer_does_not_exist_message'));
				}

				if ($data['answer']['moderated'] == 1){
					jsonResponse(translate('community_answer_was_moderated'));
				}

                $id_question = $data['answer']['id_question'];

                $this->load->model('Elasticsearch_Badwords_Model', 'elastic_bad_words');

                $title = request()->get('title');
                $text = request()->get('text');

				$data = array(
					'title_answer' => $title,
					'text_answer' => $text,
                    'has_bad_words' => $this->elastic_bad_words->is_clean($text . ' ' . $title, __SITE_LANG) ? 0 : 1
				);

				if ($this->questions->updateAnswer($id_edited_answer, $data)) {
                    $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
                    $this->elasticquestions->indexAnswer($id_edited_answer, $id_question, 'update');

					jsonResponse(translate('systmess_success_community_answer_update'), 'success', array('answer' => intVal($_POST['answer']),'title' => cleanInput($_POST['title']),'description' => cleanInput($_POST['text']),));
                } else {
					jsonResponse(translate('systmess_error_db_insert_error'));
                }
			break;
			// DONE
			case 'help':
				// is_allowed("freq_allowed_questions_opinion");

				$post_data = $_POST;

				$type = cleanInput($post_data['type']);
				if (!in_array($type, array('y', 'n'))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$type = $type == 'y' ? 1 : 0;

				$id_answer = (int) $post_data['id'];
				if (empty($id_answer)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$answer_info = $this->questions->get_simple_answer($id_answer);

				$response_data = array(
					'counter_plus' => $answer_info['count_plus'],
					'counter_minus' => $answer_info['count_minus']
				);

				if (empty($answer_info)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (is_privileged('user', $answer_info['id_user'])) {
					jsonResponse(translate('community_vote_yourself_error_message'));
				}

				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');

				$my_answer_helpful = $this->questions->get_helpful_by_answer($id_answer, $id_user);
				$action = $type ? 'plus' : 'minus';

				// If this is the first vote for this answer
				if (empty($my_answer_helpful)) {
					$insert = array(
						'id_answer'	=> $id_answer,
						'id_user'	=> $id_user,
						'help'		=> $type,
					);

					$columns['count_' . $action] = '+';

					if (!$this->questions->set_helpful($insert)) {
						jsonResponse(translate('systmess_error_db_insert_error'));
					}

					$this->questions->modify_counter_helpfull($id_answer, $columns);
					$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
					$response_data['select_' . $action] = true;

					$this->elasticquestions->answer_counter_change($answer_info['id_question'], $id_answer, 'count_' . $action, 1);

					jsonResponse(translate('community_thank_you_opinion_message'), 'success', $response_data);
				}

				// If it is a vote cancellation
				if ($my_answer_helpful[$id_answer] == $type) {
					$this->questions->remove_helpful($id_answer, $id_user);

					$columns['count_' . $action] = '-';
					$this->questions->modify_counter_helpfull($id_answer, $columns);

					$response_data['counter_' . $action] = --$response_data['counter_' . $action];
					$response_data['remove_' . $action] = true;

					$this->elasticquestions->answer_counter_change($answer_info['id_question'], $id_answer, 'count_' . $action, -1);

					jsonResponse(translate('community_thank_you_opinion_message'), 'success', $response_data);
				}

				// If a vote has been changed
				$update['help'] = $type;
				$columns = array(
					'count_plus' => $type ? '+' : '-',
					'count_minus' => $type ? '-' : '+'
				);

				if (!$this->questions->update_helpful($id_answer, $update, $id_user)) {
					jsonResponse(translate('systmess_error_db_insert_error'));
				}

				$this->questions->modify_counter_helpfull($id_answer, $columns);

				$opposite_action = $action == 'plus' ? 'minus' : 'plus';

				$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
				$response_data['counter_' . $opposite_action] = --$response_data['counter_' . $opposite_action];
				$response_data['select_' . $action] = true;
				$response_data['remove_' . $opposite_action] = true;

				$this->elasticquestions->answer_counter_change($answer_info['id_question'], $id_answer, 'count_plus', $type ? 1 : -1);
				$this->elasticquestions->answer_counter_change($answer_info['id_question'], $id_answer, 'count_minus', $type ? -1 : 1);

				jsonResponse(translate('community_thank_you_opinion_message'), 'success', $response_data);

			break;
			// DONE
			case 'delete':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$id_answer = intval($_POST['answer']);
				$answer_info = $this->questions->get_simple_answer($id_answer);
				if(empty($answer_info)){
					jsonResponse(translate('community_answer_does_not_exist_message'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $answer_info['id_user'])){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if(!$this->questions->deleteAnswer($id_answer)){
					jsonResponse(translate('systmess_error_db_insert_error'));
				}

                $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				$this->elasticquestions->deleteAnswer($id_answer, $answer_info['id_question']);
				$this->elasticquestions->counter_question_field_change($answer_info['id_question'], 'count_answers', -1);

				$this->load->model('User_Statistic_Model', 'statistic');
				$statistic_array = $this->statistic->prepare_user_array(
						array($answer_info), 'id_user', array('ep_answers_wrote' => -1)
				);
				$this->statistic->set_users_statistic($statistic_array);
				jsonResponse(translate('community_answer_was_deleted'), 'success');
			break;
			// DONE
			case 'delete_multiple':
				if(!have_right('community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$checked_answers = cleanInput($_POST['answer']);
				$checked_answers = explode(',', $checked_answers);
				$checked_answers = array_filter($checked_answers);

				if (empty($checked_answers)) {
					jsonResponse('Error: There are no answer(s) to be deleted.');
				}

				$checked_answers = implode(',', $checked_answers);
				$answers = $this->questions->getSimpleAnswers(array(
					'answers_list' => $checked_answers,
					'columns' => 'id_answer, id_user, id_question'
				));

				if (empty($answers)) {
					jsonResponse('Error: There are no answer(s) to be deleted.');
				}

				$this->questions->deleteAnswers($checked_answers);

				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				$questions_answers_count = array();
                foreach($answers as $answer) {
					if(empty($questions_answers_count[$answer['id_question']])){
						$questions_answers_count[$answer['id_question']] = 0;
					}

					$questions_answers_count[$answer['id_question']] -= 1;
                    $this->elasticquestions->deleteAnswer($answer['id_answer'], $answer['id_question']);
                }

				foreach ($questions_answers_count as $question_key => $value) {
					$this->questions->modifyCounterAnswer($question_key, $value);
					$this->elasticquestions->counter_question_field_change($question_key, 'count_answers', $value);
				}

				$this->load->model('User_Statistic_Model', 'statistic');
				$statistic_array = $this->statistic->prepare_user_array(
						$answers, 'id_user', array('ep_answers_wrote' => -1)
				);
				$this->statistic->set_users_statistic($statistic_array);

				jsonResponse('Answer(s) has(ve) been deleted.', 'success');
			break;
			// DONE
			case 'moderate':
				if(!have_right('community_questions_administration')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$checked_answers = cleanInput($_POST['answer']);
				$checked_answers = explode(',', $checked_answers);
				$checked_answers = array_filter($checked_answers);

				if (empty($checked_answers)) {
					jsonResponse('Error: There are no answer(s) to be deleted.');
				}

				$checked_answers = implode(',', $checked_answers);
				$this->questions->moderateAnswer($checked_answers);
				jsonResponse('The answer(s) has been moderated.', 'success');
			break;
		}
	}

	// DONE
	function comments_administration() {
		checkAdmin('community_questions_administration');

		$this->_load_main();

		$data['title'] = 'Comunity questions answers comments administration';
		$data['admin_filter'] = 'comments';
		$data['last_comments_id'] = $this->questions->get_comments_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Moderate comments');
		$this->view->display('admin/header_view');
		$this->view->display('admin/questions/comments_list_view');
		$this->view->display('admin/footer_view');
	}

	// DONE
	function ajax_comments_operation() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model("Questions_Model", "questions");
		$this->load->model('User_Model', 'user');
		$this->load->model('User_Statistic_Model', 'statistic');

		$op = $this->uri->segment(3);
		$id_user = privileged_user_id();

		switch ($op) {
			// DONE
			case 'check_new':
				checkAdminAjax('community_questions_administration');

				$lastId = (int)$_POST['lastId'];
				$comments_count = $this->questions->get_count_new_comments($lastId);

				if ($comments_count > 0) {
					$lastId = $this->questions->get_comments_last_id();
				}

				jsonResponse('', 'success', array('nr_new' => $comments_count, 'lastId' => $lastId));
			break;
			// DONE
			case 'edit_comment':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				is_allowed("freq_allowed_add_comment");

				$validator_rules = array(
					array(
						'field' => 'id_comment',
						'label' => translate('community_comment_info_label'),
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'text',
						'label' => translate('community_comment_text_label'),
						'rules' => array('required' => '', 'max_len[5000]' => '')
					)
				);
				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_comment = (int) request()->get('id_comment');
				$comment_info = $this->questions->getComment($id_comment);

				if(empty($comment_info)){
					jsonResponse(translate('community_comment_not_exist'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $comment_info['id_user'])){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if ($comment_info['moderated'] == 1){
					jsonResponse(translate('community_comment_was_moderated_message'));
				}

                $this->load->model('Elasticsearch_Badwords_Model', 'elastic_bad_words');

                $text = request()->get('text');
				$update = array(
					'text_comment' 	=> $text,
                    'has_bad_words' => $this->elastic_bad_words->is_clean($text, __SITE_LANG) ? 0 : 1
				);
				if(!$this->questions->updateComment($id_comment, $update)){
					jsonResponse(translate('systmess_error_db_insert_error'));
				}

				jsonResponse(translate('community_comment_was_updated'), 'success');
			break;
			// DONE
			case 'add_comment':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				is_allowed("freq_allowed_add_comment");

				$validator_rules = array(
					array(
						'field' => 'text',
						'label' => 'Text',
						'rules' => array('required' => '', 'max_len[1000]' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_answer = $this->uri->segment(4);
				$answer = $this->questions->getAnswer($id_answer);
				if (empty($answer)) {
					jsonResponse(translate('community_answer_does_not_exist_message'));
				}

				$this->load->model('Elasticsearch_Badwords_Model', 'elastic_bad_words');

				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				$countComments = $this->elasticquestions->answer_counter_change($answer['id_question'], $id_answer, 'count_comments', 1);

                $text = request()->get('text');
				$insert = array(
					'id_user' 		=> $id_user,
					'id_answer' 	=> $id_answer,
					'text_comment' 	=> $text,
                    'has_bad_words' => $this->elastic_bad_words->is_clean($text, __SITE_LANG) ? 0 : 1
				);

				if ($id_comment = $this->questions->setComment($insert)) {

					$data['comment'] = $this->questions->getComment($id_comment);
					$question = $this->questions->getQuestion($answer['id_question']);

					$this->load->model('Notify_Model', 'notify');

					$data_systmess = [
						'mess_code' => 'question_user_add_comment',
						'id_users' 	=> [$answer['id_user']], //array
						'replace' 	 => [
							'[USER]' 	=> cleanOutput(user_name_session()),
							'[LINK]' 	=> __COMMUNITY_URL . 'question/' . strForURL(cleanOutput($question['title_question'])) . '-' . $question['id_question']
						],
						'systmess' 	=> true
					];

					$this->notify->send_notify($data_systmess);

					$this->statistic->set_users_statistic(array(
						$id_user => array(
							'ep_answer_comm_wrote' => 1
						)
                    ));

                    $responseContent = translate('community_comment_saved_content', ['{{START_LINK}}' => '<a class="link" href="mailto:support@exportportal.com" target="_blank">', '{{END_LINK}}' => '</a>']);

					jsonResponse(translate('community_comment_saved', ['{{QUESTION_TITLE}}' => strtoupper(cleanOutput($question['title_question']))]), 'success', ['content' => $responseContent]);
				} else {
					jsonResponse(translate('systmess_error_db_insert_error'));
				}
			break;
			// DONE
			case 'delete':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$id_comment = intval($_POST['comment']);
				$comment_info = $this->questions->get_simple_comment($id_comment, 'id_comment, id_user, id_answer');

				$answer = $this->questions->getAnswer($comment_info['id_answer']);
				if(empty($comment_info)){
					jsonResponse(translate('community_comment_not_exist'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $comment_info['id_user'])){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if(!$this->questions->deleteComment($id_comment)){
					jsonResponse(translate('systmess_error_db_insert_error'));
				}

				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
				$this->elasticquestions->answer_counter_change($answer['id_question'], $answer['id_answer'], 'count_comments', -1);

				$this->statistic->set_users_statistic(array(
					$comment_info['id_user'] => array(
						'ep_answer_comm_wrote' => -1
					)
				));

				jsonResponse(translate('community_comment_deleted'), 'success');
			break;
			// DONE
			case 'delete_multiple':
				checkAdminAjax('community_questions_administration');

				$checked_comments = cleanInput($_POST['comment']);
				$checked_comments = explode(',', $checked_comments);
				$checked_comments = array_filter($checked_comments);

				if (empty($checked_comments)) {
					jsonResponse('Error: There are no comment(s) to be deleted.');
				}

				$checked_comments = implode(',', $checked_comments);
				$comments = $this->questions->getSimpleComments(array(
					'comments_list' => $checked_comments,
					'columns' => 'id_user,id_answer'
				));

				if (empty($comments)) {
					jsonResponse('Error: There are no comment(s) to be deleted.');
				}

				$this->questions->deleteComments($checked_comments);

				$answers_comments_count = array();

				$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');

                foreach($comments as $comment) {

					if(empty($answers_comments_count[$comment['id_answer']])){
						$answers_comments_count[$comment['id_answer']] = 0;
					}

					$answers_comments_count[$comment['id_answer']] -= 1;
                }

				foreach ($answers_comments_count as $answer_key => $value) {
					$this->questions->modifyCounterComments($answer_key, $value);

					$answer = $this->questions->getAnswer($answer_key);
					$this->elasticquestions->answer_counter_change($answer['id_question'], $answer['id_answer'], 'count_comments', $value);
				}

				$statistic_array = $this->statistic->prepare_user_array(
						$comments, 'id_user', array('ep_answer_comm_wrote' => -1)
				);
				$this->statistic->set_users_statistic($statistic_array);

				jsonResponse('The comment(s) has been deleted.', 'success');
			break;
			// DONE
			case 'moderate':
				checkAdminAjax('community_questions_administration');

				$checked_comments = cleanInput($_POST['comment']);
				$checked_comments = explode(',', $checked_comments);
				$checked_comments = array_filter($checked_comments);

				if (empty($checked_comments)) {
					jsonResponse('Error: There are no comment(s) to be moderated.');
				}

				$checked_comments = implode(',', $checked_comments);
				$this->questions->moderateComment($checked_comments);

				jsonResponse('The comment(s) has been moderated.', 'success');
			break;
		}
	}

	// DONE
	function ajax_comments_load_blocks() {
		if (!isAjaxRequest())
			headerRedirect();

		$this->load->model("Questions_Model", "questions");
		$this->load->model('User_Model', 'user');
		$op = $this->uri->segment(3);

		switch ($op) {
			case 'list':

				$conditions = array(
					'id_answer' => (int) $_POST['answer'],
					'start'		=> isset($_POST['start']) ? (int) $_POST['start'] : 0,
					'per_p'		=> config('community_answer_comments_per_page', 2)
				);

				$data = array(
					'comments'	=> $this->questions->getComments($conditions),
					'answer' 	=> $this->questions->getAnswer($conditions['id_answer'])
				);

				$content = $this->view->fetch('new/questions/comments_list_view', $data);

				if (!empty($data))
					jsonResponse('sucess', 'success', array('content' => $content, 'count' => $this->questions->countComments(array('id_answer' => $conditions['id_answer']))));
			break;
		}
	}

	function ajax_answers_more(){
		if (!isAjaxRequest())
			headerRedirect();

		$this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');
		$conditions = array(
			'by_id_question' 	=> (int) arrayGet($_POST, 'id_question'),
			'start' 			=> (int) arrayGet($_POST, 'start'),
			'limit' 			=> config('community_answers_per_page', 5)
		);

		$this->elasticquestions->getAnswers($conditions);
		$data = array(
			'answers' 		=> array_shift($this->elasticquestions->inner_answers_records),
			'is_logged_in' 	=> logged_in()
		);

		if (logged_in()) {
			$answers_ids = array();
			foreach ($data['answers'] as $answer) {
				if (!empty($answer['count_plus']) || !empty($answer['count_minus'])) {
					$answers_ids[] = $answer['id_answer'];
				}
			}

			if (!empty($answers_ids)) {
				$data['helpful_answers'] = model('questions')->get_helpful_by_answer(implode(',', $answers_ids), $this->session->id);
			}
		}
		$content = $this->view->fetch('new/questions/item_list_answer_view', $data);

		$resp = array(
			'count' => $this->elasticquestions->question_answers_count,
			'html' => $content
		);

		echo json_encode($resp);
	}

	// DONE
	function popup_forms() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		checkIsLoggedAjaxModal();

		$this->_load_main();
		$op = $this->uri->segment(3);
		$id = (int) $this->uri->segment(4);

		switch ($op) {
			// DONE
			case 'create_question_category':
				checkAdminAjaxModal('community_questions_administration');

				$this->view->display('admin/questions/question_category_form_view');
			break;
			// DONE
			case 'create_question_category_i18n':
				checkAdminAjaxModal('community_questions_administration');

				$id_category = intval($this->uri->segment(4));
				$data['category'] = $this->questions->getCategory($id_category);
				if (empty($data['category'])) {
					messageInModal('Error: Category does not exist.');
				}

				$data['tlanguages'] = $this->translations->get_languages();
				$this->view->display('admin/questions/question_category_form_i18n_view', $data);
			break;
			// DONE
			case 'edit_question_category':
				checkAdminAjaxModal('community_questions_administration');

				$id_category = intval($this->uri->segment(4));
				$data['category'] = $this->questions->getCategory($id_category);
				if (empty($data['category'])) {
					messageInModal('Error: Category does not exist.');
				}

				$this->view->display('admin/questions/question_category_form_view', $data);
			break;
			// DONE
			case 'edit_question_category_i18n':
				checkAdminAjaxModal('community_questions_administration');

				$id_category = intval($this->uri->segment(4));
				$lang_category = $this->uri->segment(5);

				$data['category_i18n'] = $this->questions->getCategory_i18n(array('id_category' => $id_category, 'lang_category' => $lang_category));
				if (empty($data['category_i18n'])) {
					messageInModal('Error: Category translation does not exist.');
				}

				$data['lang_block'] = $this->translations->get_language_by_iso2($lang_category);
				$this->view->display('admin/questions/question_category_form_i18n_view', $data);
			break;
			// DONE
			case 'add_question':
				if(!have_right_or('manage_community_questions')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				if (__SITE_LANG == 'en') {
					$quest_cats = $this->questions->getCategories(array('visible' => 1));
				} else{
					$quest_cats = $this->questions->getCategories_i18n(array('visible' => 1));
				}

				$categories_lang_iso2 = model('questions')->get_countries_langs();
				$data = array(
					'tlanguages' => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'", 'lang_iso2' => $categories_lang_iso2)),
					'quest_cats' => $quest_cats,
					'countries' => $this->country->get_countries()
				);

				$this->view->assign($data);
				$this->view->display('new/questions/form_view');
			break;
			// DONE
			case 'edit_question':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$data['question'] = $this->questions->getQuestion($id);
				if (empty($data['question'])){
					messageInModal(translate('community_question_does_not_exist'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $data['question']['id_user'])){
					messageInModal(translate('community_question_does_not_exist'));
				}

				if ($data['question']['moderated'] == 1){
					messageInModal(translate('community_edit_question_moderated'));
				}

				// if ($data['question']['lang'] == 'en') {
					$data['quest_cats'] = $this->questions->getCategories(array('visible' => 1));
				// } else{
				// 	$data['quest_cats'] = $this->questions->getCategories_i18n(array('visible' => 1, 'lang_category' => $data['question']['lang']));
				// }

				$data['countries'] = $this->country->get_countries();
				$data['tlanguage'] = $this->translations->get_language_by_iso2($data['question']['lang']);
				$this->view->assign($data);
				$this->view->display('new/questions/edit_question_form_view');
			break;
			// DONE
			case 'edit_question_admin':
				checkAdminAjaxModal('community_questions_administration');

				$data['question'] = $this->questions->getQuestion($id);
				if (empty($data['question'])){
					messageInModal(translate('community_question_does_not_exist'));
				}

				if ($data['question']['moderated'] == 1){
					messageInModal(translate('community_edit_question_moderated'));
				}

				if ($data['question']['lang'] == 'en') {
					$data['quest_cats'] = $this->questions->getCategories(array('visible' => 1));
				} else{
					$data['quest_cats'] = $this->questions->getCategories_i18n(array('visible' => 1, 'lang_category' => $data['question']['lang']));
				}

				$data['countries'] = $this->country->get_countries();
				$this->view->assign($data);
				$this->view->display('admin/questions/question_form_view');
			break;
			// DONE
			case 'add_answer':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$question = $this->questions->getQuestion($id);
				if (empty($question)){
					messageInModal(translate('community_question_does_not_exist'));
				}

				$data = array(
					'id_question' => $id
				);
				$this->view->assign($data);
				$this->view->display('new/questions/answer_form_view');
			break;
			// DONE
			case 'edit_answer':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$data['answer'] = $this->questions->getAnswer($id);
				if (empty($data['answer'])){
					messageInModal(translate('community_answer_does_not_exist_message'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $data['answer']['id_user'])){
					messageInModal(translate('community_answer_does_not_exist_message'));
				}

				if ($data['answer']['moderated'] == 1){
					messageInModal(translate('community_answer_was_moderated'));
				}

				$this->view->assign($data);
                $this->view->display('new/questions/answer_form_view');
			break;
			// DONE
			case 'edit_answer_admin':
				checkAdminAjaxModal('community_questions_administration');

				$data['answer'] = $this->questions->getAnswer($id);
				if (empty($data['answer'])){
					messageInModal('Error: The answer does not exist.');
				}

				if ($data['answer']['moderated'] == 1){
					messageInModal('Error: The answer has been moderated and can not be edited.');
				}

				$this->view->assign($data);
				$this->view->display('admin/questions/answer_form_view');
			break;
			// DONE
			case 'add_comment':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$answer = $this->questions->getAnswer($id);
				if (empty($answer)){
					messageInModal(translate('community_answer_does_not_exist_message'));
				}

				$data = array(
					'id_answer' => $id
				);
				$this->view->assign($data);
				$this->view->display('new/questions/comment_form_view');
			break;
			// DONE
			case 'edit_comment':
				if(!have_right_or('manage_community_questions,community_questions_administration')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$data['comment'] = $this->questions->getComment($id);
				if (empty($data['comment'])){
					messageInModal(translate('community_comment_not_exist'));
				}

				if(have_right('manage_community_questions') && !is_privileged('user', $data['comment']['id_user'])){
					messageInModal(translate('community_comment_not_exist'));
				}

				if ($data['comment']['moderated'] == 1){
					messageInModal(translate('community_comment_was_moderated_message'));
				}

				$this->view->assign($data);
				$this->view->display('new/questions/comment_form_view');
			break;
			// DONE
			case 'edit_comment_admin':
				checkAdminAjaxModal('community_questions_administration');

				$data['comment'] = $this->questions->getComment($id);
				if (empty($data['comment'])){
					messageInModal('Error: The comment does not exist.');
				}

				if ($data['comment']['moderated'] == 1){
					messageInModal('Error: The comment has been moderated and can not be edited.');
				}

				$this->view->assign($data);
				$this->view->display('admin/questions/comment_form_view');
			break;
			// DONE
			case 'share':
				if (!have_right('share_this')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$question_info = $this->questions->getQuestion($id);
				if (empty($question_info)){
					messageInModal(translate('community_question_does_not_exist'));
				}

				$data['question'] = $id;
				$this->view->assign($data);

				$this->view->display('new/questions/popup_share_view');
			break;
			// DONE
			case 'email':
				if (!have_right('email_this')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$question_info = $this->questions->getQuestion($id);
				if (empty($question_info)){
					messageInModal(translate('community_question_does_not_exist'));
				}

				$data['question'] = $id;
				$this->view->assign($data);

				$this->view->display('new/questions/popup_email_view');
			break;
			// DONE
			case 'question_tree':
				checkAdminAjaxModal('community_questions_administration');

				$data['question'] = $this->questions->getQuestion($id);
				if (empty($data['question'])){
					messageInModal(translate('community_question_does_not_exist'));
				}

				$data['question']['answers'] = arrayByKey($this->questions->getAnswers(array('id_question' => $id)), 'id_answer');
				if (!empty($data['question']['answers'])) {
					$conditions = array(
						'id_answer' => array_keys($data['question']['answers'])
					);
					$comments = $this->questions->getComments($conditions);

					if (!empty($comments)) {
						foreach ($comments as $comment) {
							$data['question']['answers'][$comment['id_answer']]['comments'][] = $comment;
						}
					}
				}

				$data['scroll_block'] = cleanInput($this->uri->segment(5));
				$this->view->display('admin/questions/popup_question_tree_form', $data);
			break;
			case 'detail':
				if(!have_right('manage_community_questions')){
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				$question = $this->questions->getQuestion($id);
				if (empty($question)) {
					messageInModal(translate('community_question_does_not_exist'));
				}

				if(!is_privileged('user', $question['id_user'])){
					messageInModal(translate('community_question_does_not_exist'));
				}

				global $tmvc;
				$id_user = privileged_user_id();
				$question['answers'] = $this->questions->getAnswers(array('id_questions' => $id));
				if (!empty($question['answers'])) {
					$answers_keys = implode(',', array_keys(arrayByKey($question['answers'], 'id_answer')));
					$question['helpful_answers'] = $this->questions->get_helpful_by_answer($answers_keys, $id_user);
				}

				$user_info = $this->user->getSimpleUser($id_user, "users.idu, users.user_group, users.user_type, users.`status`, CONCAT(users.fname, ' ', users.lname) as full_name, users.user_photo");
				$question = array_merge($question, $user_info);
				$_handler_categories = ($question['lang'] == 'en')?'getCategories':'getCategories_i18n';
				$data = array(
					'quest_cats' => arrayByKey($this->questions->$_handler_categories(array('visible' => 1)), "idcat"),
					'questions' => array($question)
				);

				$data['questions_uri_components'] = $tmvc->site_urls['community_questions/my']['replace_uri_components'];
				$uri_params = array();
				$links_map = array(
					$data['questions_uri_components']['questions'] => array(
						'type' => 'uri',
						'deny' => array($data['questions_uri_components']['category']),
					),
					$data['questions_uri_components']['category'] => array(
						'type' => 'uri',
						'deny' => array(),
					)
				);

				$data['links_tpl'] = $this->uri->make_templates($links_map, $uri_params);
				$data['links_tpl'][$data['questions_uri_components']['questions']] = str_replace("/{$tmvc->my_config['replace_uri_template']}/", '', $data['links_tpl'][$data['questions_uri_components']['questions']]);
				foreach ($data['links_tpl'] as $key_link_tpl => $value_link_tpl) {
					if($key_link_tpl != $data['questions_uri_components']['questions']){
						$data['links_tpl'][$key_link_tpl] = normalize_url("{$data['questions_uri_components']['questions']}/{$value_link_tpl}");
					}
				}

				$this->view->assign($data);
				$this->view->display('new/questions/detail_view');
            break;
		}
    }

    // DONE
	function popup_forms_all() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		$op = $this->uri->segment(3);

		switch ($op) {
            case 'show_filters':
                //region get questions
                $conditions = array(
                    "order_by" 						=> "popular-desc",
                    "aggregate_counter_country" 	=> true,
                    "aggregate_counter_category" 	=> true,
                    'per_p' 						=> config('community_questions_main_per_page', 10),
                    'page' 							=> 0
                );

                model('elasticsearch_questions')->getQuestions($conditions);
                //endregion get questions

				$tmvc = tmvc::instance();
				$questions_uri_components = $tmvc->site_urls['questions/index']['replace_uri_components'];
				$links_map = array(
					$questions_uri_components['category'] => array(
						'type' => 'uri',
						'deny' => array($questions_uri_components['page'], 'keywords', 'order_by'),
					),
					$questions_uri_components['country'] => array(
						'type' => 'uri',
						'deny' => array($questions_uri_components['page'], 'keywords', 'order_by'),
					),
					$questions_uri_components['page'] => array(
						'type' => 'uri',
						'deny' => array($questions_uri_components['page']),
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

                $question_categories_method = __SITE_LANG === 'en' ?  'getCategories' : 'getCategories_i18n';

				$data = array(
					'links_tpl' 				=> $this->uri->make_templates($links_map, array()),
					'questions_uri_components' 	=> $questions_uri_components,
                	'quest_cats' 				=> arrayByKey(model('questions')->$question_categories_method(array('visible' => 1)), "idcat"),
                	'countries' 				=> arrayByKey(model('country')->fetch_port_country(), "id"),
                	'counter_country' 			=> model('elasticsearch_questions')->aggregates['counter_country'],
			    	'counter_category' 			=> model('elasticsearch_questions')->aggregates['counter_category']
				);

                $this->view->assign($data);
                $this->view->display('new/questions/popup_show_filters_view');
            break;
        }
    }

    function ajax_operation() {
		if (!isAjaxRequest()){
			headerRedirect();
        }

		$op = $this->uri->segment(3);
		switch ($op) {
            case 'get_search':
                $data = array();
                $question_categories_method = __SITE_LANG === 'en' ?  'getCategories' : 'getCategories_i18n';
                $data['quest_cats'] = arrayByKey(model('questions')->$question_categories_method(array('visible' => 1)), "idcat");
                $data['countries'] = arrayByKey(model('country')->fetch_port_country(), "id");

                $html = $this->view->fetch('new/questions/search_form_view', $data);
				jsonResponse('', 'success', array('html' => $html));
                break;
        }
    }
}

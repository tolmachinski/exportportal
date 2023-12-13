<?php

use App\Common\Buttons\ChatButton;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Feedbacks_Controller extends TinyMVC_Controller {

	private function _load_main() {
		$this->load->model('Category_Model', 'category');
		$this->load->model('User_Model', 'user');
		$this->load->model('UserFeedback_Model', 'feedbacks');
	}

	function my(){
		if (!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if (!have_right('leave_feedback')) {
			$this->session->setMessages(translate("systmess_error_page_permision"), 'errors');
			headerRedirect(__SITE_URL);
		}

		if (!i_have_company() && !have_right('buy_item')) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		checkGroupExpire();

		$data = array();

		$uri = $this->uri->uri_to_assoc();
		$type = 'received';
		if (!empty($uri['type']) && $uri['type'] === 'written'){
			$type = 'written';
		}

		if(!empty($uri['feedback_number'])){
			$data['id_feedback'] = (int)$uri['feedback_number'];
		}

		if(!empty($uri['order_number'])){
			$data['id_order'] = (int)$uri['order_number'];
		}

		$this->_load_main();

		$this->view->assign('title', ucfirst($type) . ' feedback ');
		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/users_feedbacks/my/' . $type . '_view');
		$this->view->display('new/footer_view');
	}

	function ajax_my_list_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
		checkGroupExpire('dt');

        $request = request()->request;

		$userId = privileged_user_id();
		$data['type'] = uri()->segment(3) == 'written' ? 'written' : 'received';

        $conditions = array_filter(array_merge(
            dtConditions($request->all(), [
                ['as' => 'added_start',         'key' => 'start_from',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish',        'key' => 'start_to',            'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'id_order',            'key' => 'id_order',            'type' => 'int'],
                ['as' => 'feedback_number',     'key' => 'feedback_number',     'type' => 'int'],
                ['as' => 'keywords',            'key' => 'keywords',            'type' => 'cleanInput'],
            ]),
            [
                'company_details'   => true,
                'items_details'     => true,
                'limit'             => $request->getInt('iDisplayStart') . ',' . $request->getInt('iDisplayLength'),
                'user'              => 'received' === $data['type'] ? $userId : null,
                'poster'            => 'written' === $data['type'] ? $userId : null,
            ]
        ));

		//get items details and company details
        /** @var UserFeedback_Model $userFeedbackModel */
        $userFeedbackModel = model(UserFeedback_Model::class);

		$data['feedbacks'] = $userFeedbackModel->getFeedbacks($conditions);
		$totalFeedback = $userFeedbackModel->countFeedbacks($conditions);

		$output = [
			'sEcho'                 => $request->getInt('sEcho'),
			'iTotalRecords'         => $totalFeedback,
			'iTotalDisplayRecords'  => $totalFeedback,
			'aaData'                => [],
        ];

		if (empty($data['feedbacks'])) {
			jsonResponse('', 'success', $output);
        }

		$output['aaData'] = $this->_dt_feedback($data);

		jsonResponse('', 'success', $output);
	}

	private function _dt_feedback($data){
		$id_user = privileged_user_id();
		$output = array();

		foreach ($data['feedbacks'] as $feedback) {
			switch ($data['type']) {
				case 'received':
					if ($feedback['has_company'] == $feedback['id_poster']) {
						$user_info = '<a href="' . __SITE_URL . 'usr/' . strForURL($feedback['postername']) . '-' . $feedback['id_poster'] . '">' . $feedback['postername'] . '</a><br>(<a href="' . getCompanyURL($feedback) . '">'.$feedback['name_company'].'</a>)';
					} else {
						$user_info = '<a href="' . __SITE_URL . 'usr/' . strForURL($feedback['postername']) . '-' . $feedback['id_poster'] . '">' . $feedback['postername'] . '</span>';
					}
				break;
				case 'written':
					if ($feedback['has_company'] == $feedback['id_user']) {
						$user_info = '<a href="' . __SITE_URL . 'usr/' . strForURL($feedback['username']) . '-' . $feedback['id_user'] . '">' . $feedback['username'] . '</a><br>(<a href="' . getCompanyURL($feedback) . '">'.$feedback['name_company'].'</a>)';
					} else {
						$user_info = '<a href="' . __SITE_URL . 'usr/' . strForURL($feedback['username']) . '-' . $feedback['id_user'] . '">' . $feedback['username'] . '</span>';
					}
				break;
			}

			$edit_btn = "";
			if ($feedback['status'] == 'new' && $feedback['reply_text'] == '' && $feedback['id_poster'] == $id_user) {
				$edit_btn = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit feedback" title="Edit feedback" href="' . __SITE_URL . 'feedbacks/popup_forms/edit_user_feedback/'.$feedback['id_feedback'].'">
								<i class="ep-icon ep-icon_pencil"></i>
								<span>Edit</span>
							</a>';
			}

			$reply_btn = "";
			if ($feedback['id_user'] == $id_user && $feedback['status'] == 'new') {
				if ($feedback['reply_text'] == ''){
					$reply_btn = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add reply" title="Add reply" href="' . __SITE_URL . 'feedbacks/popup_forms/add_reply/'.$feedback['id_feedback'].'">
									<i class="ep-icon ep-icon_pencil"></i>
									<span>Add</span>
								</a>';
				} else{
					$reply_btn = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit reply" title="Edit reply" href="' . __SITE_URL . 'feedbacks/popup_forms/edit_reply/'.$feedback['id_feedback'].'">
									<i class="ep-icon ep-icon_pencil"></i>
									<span>Edit</span>
								</a>';
				}
			}

			$statistic = array();

			$output[] = array(
				"dt_user" 		=> $user_info,
				"dt_order" 		=> '<a class="fancybox.ajax fancyboxValidateModal" data-title="Order details" title="Order details" href="' . __SITE_URL . 'order/popups_order/order_detail/' . $feedback['id_order'] . '">' . orderNumber($feedback['id_order']) . '</a>',
				"dt_title" 		=> $feedback['title'],
				"dt_added" 		=> getDateFormat($feedback['create_date'], 'Y-m-d H:i:s', 'j M, Y H:i'),
				'dt_statistics' => '<div class="dtable-params">
										<div class="dtable-params__item">
											<span class="txt-gray">Rating: </span>
											<span class="link" title="Rating">'	. $feedback['rating'] . '</span>
										</div>
										<div class="dtable-params__item">
											<span class="txt-gray">Likes: </span>
											<span class="link" title="Likes">'	. $feedback['count_plus'] . '</span>
										</div>
										<div class="dtable-params__item">
											<span class="txt-gray">Dislikes: </span>
											<span class="link" title="Dislikes">'	. $feedback['count_minus'] . '</span>
										</div>
									</div>',
				"dt_actions" 	=> '<div class="dropdown">
										<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="ep-icon ep-icon_menu-circles"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right">'
											. $reply_btn
											. $edit_btn
											.'<a class="dropdown-item fancybox.ajax fancyboxValidateModal" href="' . __SITE_URL . 'feedbacks/popup_forms/detail/' . $feedback['id_feedback'] . '" data-mw="740" title="Feedback details" data-title="Feedback details">
												<i class="ep-icon ep-icon_info-stroke"></i>
												<span>Details</span>
											</a>
										</div>
									</div>'
			);
		}

		return $output;
	}

	function administration() {
		checkAdmin('manage_content');

		$this->_load_main();
		$data['last_feedbacks_id'] = $this->feedbacks->get_feedbacks_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Feedbacks');
		$this->view->display('admin/header_view');
		$this->view->display('admin/users_feedbacks/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_list_dt() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged_in"));

		if (!have_right('moderate_content'))
			jsonDTResponse(translate("systmess_error_rights_perform_this_action"));

		$this->_load_main();

		$conditions = array();

		// if (isset($_POST['iDisplayStart'])) {
		// 	$from = intval(cleanInput($_POST['iDisplayStart']));
		// 	$till = intval(cleanInput($_POST['iDisplayLength']));
		// 	$conditions['limit'] = $from . ',' . $till;
		// }

		// if (!empty($_POST['start_date'])) {
		// 	$start_date = cleanInput($_POST['start_date']);
		// 	$conditions['added_start'] = date('Y-m-d', strtotime($start_date));
		// }

		// if (!empty($_POST['finish_date'])) {
		// 	$added_finish = cleanInput($_POST['finish_date']);
		// 	$conditions['added_finish'] = date('Y-m-d', strtotime($added_finish));
		// }

		// if (!empty($_POST['id_user']))
		// 	$conditions['user'] = intval($_POST['id_user']);


		// if (!empty($_POST['id_poster']))
		// 	$conditions['poster'] = intval($_POST['id_poster']);


		// if (!empty($_POST['id_order']))
		// 	$conditions['id_order'] = intval($_POST['id_order']);


		// if (isset($_POST['moderated'])) {
		// 	$conditions['moderated'] = "new";
		// 	if ($_POST['moderated'] == 1) {
		// 		$conditions['moderated'] = "moderated";
		// 	}
		// }

		// if (isset($_POST['keywords']))
		// 	$conditions['keywords'] = cleanInput(cut_str($_POST['keywords']));

		// $sort_col = $_POST["mDataProp_" . intval($_POST['iSortCol_0'])];
		// switch ($sort_col) {
		// 	case 'user':$column = 'username';break;
		// 	case 'poster':$column = 'postername';break;
		// 	case 'item':$column = 'item_title';break;
		// 	case 'title':$column = 'title';break;
		// 	case 'text':$column = 'text';break;
		// 	case 'reply':$column = 'reply_text';break;
		// 	case 'added':$column = 'create_date';break;
		// 	case 'rating':$column = 'rating';break;
		// 	case 'plus':$column = 'count_plus';break;
		// 	case 'minus':$column = 'count_minus';break;
		// }

		// $conditions['order_by'] = $column . ' ' . $_POST['sSortDir_0'];

		//get items details and company details
		// $conditions['items_details'] = true;
        // $conditions['company_details'] = true;

        $sorting = [
            'limit' => intval(cleanInput($_POST['iDisplayStart'])). ',' . intval(cleanInput($_POST['iDisplayLength'])),
            'items_details' => true,
            'company_details' => true,
            'sort_by' => flat_dt_ordering($_POST, [
                'user'   => 'username',
                'poster' => 'postername',
                'item'   => 'item_title',
                'title'  => 'title',
                'text'   => 'text',
                'reply'  => 'reply_text',
                'added'  => 'create_date',
                'rating' => 'rating',
                'plus'   => 'count_plus',
                'minus'  => 'count_minus'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'added_start',  'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',  'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'user', 'key' => 'id_user', 'type' => 'cleanInput'],
            ['as' => 'poster', 'key' => 'id_poster', 'type' => 'int'],
            ['as' => 'id_order', 'key' => 'id_order', 'type' => 'int'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => $_POST['moderated'] == 1 ? 'moderated' : 'new', 'key' => 'moderated', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["username-asc"] : $sorting['sort_by'];

        $conditions = array_merge($conditions, $sorting, $filters);

		$feedbacks = $this->feedbacks->getFeedbacks($conditions);
		$records_total = $this->feedbacks->countFeedbacks($conditions);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $records_total,
			"iTotalDisplayRecords" => $records_total,
			'aaData' => array()
		);

		if(empty($feedbacks))
			jsonResponse('', 'success', $output);

		foreach ($feedbacks as $feedback) {
			$moderate_btn = '<a data-callback="moderate_feedback" class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-message="Are you sure want moderate this feedback?" title="Moderate feedback" data-feedback="' . $feedback['id_feedback'] . '"></a>';
			$reply = "-";

			$user_info = "</div><div class='clearfix'></div><span clas='tac'>" . $feedback['username'] . "</span>";
			$poster_info = "</div><div class='clearfix'></div><span clas='tac'>" . $feedback['postername'] . "</span>";

			if ($feedback['has_company'] == $feedback['id_user']) {
			$user_info = '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($feedback). '"' . '></a>'
				."</div><div class='clearfix'></div><span clas='tac'>" . $feedback['username'] . " (" . $feedback['name_company'] . ")</span>";
			} else {
			$poster_info = '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($feedback). '"' . '></a>'
				."</div><div class='clearfix'></div><span clas='tac'>" . $feedback['postername'] . " (" . $feedback['name_company'] . ")</span>";
			}


			if ($feedback['status'] == "moderated")
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green " title="Moderated feedback"/>';

			if (!empty($feedback['reply_text']))
				$reply = $feedback['reply_text'];

			// unserialize services
			$services = array();
			if (!empty($feedback['services'])) {
				$services = unserialize($feedback['services']);
			}

			$title_dots = "";
			if (strlen($feedback['title']) > 150) {
				$title_dots = "<a rel='feedback_details' title='View details'><p class='tac'>...</p></a>";
			}

			$text_dots = "";
			if (strlen($feedback['text']) > 150) {
				$text_dots = "<a rel='feedback_details' title='View details'><p class='tac'>...</p></a>";
			}

			$reply_dots = "";
			if (strlen($feedback['reply_text']) > 100) {
				$reply_dots = "<a rel='feedback_details' title='View details'><p class='tac'>...</p></a>";
			}

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $feedback['id_user'], 'recipientStatus' => 'active', 'module' => 26, 'item' => $feedback['id_feedback']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatUserView = $btnChatUser->button();

            //TODO: admin chat hidden
            $btnChatPoster = new ChatButton(['hide' => true, 'recipient' => $feedback['id_poster'], 'recipientStatus' => 'active', 'module' => 26, 'item' => $feedback['id_feedback']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatPosterView = $btnChatPoster->button();

			$output['aaData'][] = array(
				"checkboxes" => '<input type="checkbox" class="check-feedback mr-5 pull-left" data-id-feedback="' . $feedback['id_feedback'] . '">' .
				$feedback['id_feedback'] . "</br>" .
				"<a rel='feedback_details' title='View details' class='ep-icon ep-icon_plus'></a>",
				"user" =>
				'<div class="pull-left">'
				. '<a class="ep-icon ep-icon_filter dt_filter" title="Filter by user" data-value-text="' . $feedback['username'] . '" data-value="' . $feedback['id_user'] . '" data-title="User" data-name="id_user"></a>'
				. '<a class="ep-icon ep-icon_user txt-green" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($feedback['username']) .
				'-' . $feedback['id_user'] . '"></a>'
				. $btnChatUserView
				. $user_info,
				"poster" =>
				'<div class="pull-left">'
				. '<a class="ep-icon ep-icon_filter dt_filter" title="Filter by poster" data-value-text="' . $feedback['postername'] . '" data-value="' . $feedback['id_poster'] . '" data-title="Poster" data-name="id_poster"></a>'
				. '<a class="ep-icon ep-icon_user txt-green" title="View poster\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($feedback['postername']) .
				'-' . $feedback['id_poster'] . '"></a>'
				. $btnChatPosterView
				. $poster_info,
				"order" => '<div class="pull-left">'
				. '<a class="ep-icon ep-icon_filter dt_filter" title="Filter by order" data-value-text="' . orderNumber($feedback['id_order']) . '" data-value="' . $feedback['id_order'] . '" data-title="order" data-name="id_order"></a>'
				. '<a class="ep-icon ep-icon_item txt-orange fancybox.ajax fancybox" data-title="Order details" title="View item" href="' . __SITE_URL . 'order/popups_order/order_detail/' . $feedback['id_order'] . '"></a>' .
				"</div><div class='clearfix'></div><span>" . orderNumber($feedback['id_order']) . "</span>",
				"title" => "<p class='h-50 hidden-b' title='" . $feedback['title'] . "'>" . $feedback['title'] . "</p>" . $title_dots,
				"full_title" => $feedback['title'],
				"text" => "<p class='h-50 hidden-b'>" . $feedback['text'] . "</p>" . $text_dots,
				"full_text" => $feedback['text'],
				"reply" => "<p class='h-50 hidden-b'>" . $reply . "</p>" . $reply_dots,
				"full_reply" => $reply,
				"added" => formatDate($feedback['create_date']),
				"reply_date" => formatDate($feedback['reply_date']),
				"rating" => $feedback['rating'],
				"plus" => $feedback['count_plus'],
				"minus" => $feedback['count_minus'],
				"actions" => $moderate_btn .
							'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit feedback" href="feedbacks/popup_forms/edit_feedback/' . $feedback['id_feedback'] . '" data-title="Edit feedback" data-id="' . $feedback['id_feedback'] . '"></a>',
				"services" => $services
			);
		}

		jsonResponse('', 'success', $output);
	}

	function ajax_feedbacks_administration_operation() {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
		}

		if (!have_right('moderate_content')) {
			jsonResponse(translate('systmess_error_permission_not_granted'));
		}

		$action = $this->uri->segment(3);
		$this->_load_main();

		switch ($action) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$feedbacks_count = $this->feedbacks->get_count_new_feedbacks($lastId);

				if ($feedbacks_count) {
					$last_feedbacks_id = $this->feedbacks->get_feedbacks_last_id();
					jsonResponse('', 'success', array('nr_new' => $feedbacks_count, 'lastId' => $last_feedbacks_id));
				} else
					jsonResponse('Error: New feedback do not exist');
			break;
			case "moderate":
				$checked_feedbacks = $_POST['checked_feedbacks'];

				if (empty($checked_feedbacks)) {
					jsonResponse(translate('systmess_error_no_feedback_that_can_be_moderated'));
				}

				if (!$this->feedbacks->moderate_feedbacks(implode(',',$checked_feedbacks))) {
					jsonResponse('systmess_internal_server_error');
				}

				jsonResponse(translate('systmess_succes_moderated_feedback'), 'success');

			break;
			case 'edit':
				$validator = $this->validator;
				$validator_rules = array(
					array(
					'field' => 'text',
					'label' => 'Description',
					'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$data = array(
					'text' => cleanInput($_POST['text'])
				);

				$title = cleanInput($_POST['title']);
				if (!empty($title))
					$data['title'] = cleanInput($_POST['title']);

				$reply = cleanInput($_POST['reply']);
				if (!empty($reply))
					$data['reply_text'] = $reply;

				if ($this->feedbacks->update_feedback(intVal($_POST['feedback']), $data)) {
					jsonResponse("The feedback has been successfully updated!", 'success');
				} else {
					jsonResponse('Error: the feedback wasn\'t updated. Please try again later.');
				}
			break;
		}
	}

	function popup_forms() {
		if (!isAjaxRequest())
			headerRedirect();

		checkIsLoggedAjaxModal();

		$action = cleanInput($this->uri->segment(3));
        $id = intval($this->uri->segment(4));
		$this->_load_main();

		switch ($action) {
			case 'add_feedback':
				checkPermisionAjaxModal('leave_feedback');

				$data['page_type'] = $this->uri->segment(4);
				$params = array(
					'status' => 11
				);
				switch ($data['page_type']) {
					case 'user':
						$id_user = intval($this->uri->segment(5));
						if (check_group_type('Seller,Company Staff')) {
							$id_seller = $params['id_seller'] = privileged_user_id();
							$params['feedback_seller'] = true;
							if ($id_seller != $id_user && $id_user){
								$params['id_buyer'] = $id_user;
							}
						} elseif (have_right('buy_item')) {
							$id_buyer = $params['id_buyer'] = id_session();
							if ($id_buyer != $id_user && $id_user){
								$params['id_seller'] = $id_user;
							}
						}
					break;
					case 'order':
						$id_order = intval($this->uri->segment(5));
						if (check_group_type('Seller,Company Staff')){
							$params['id_seller'] = privileged_user_id();
							$params['feedback_seller'] = true;
						} elseif (have_right('buy_item')){
							$params['id_buyer'] = id_session();
						}

						$params['order'] = $id_order;
					break;
					default:
						if (check_group_type('Seller,Company Staff')) {
							$params['id_seller'] = privileged_user_id();
							$params['feedback_seller'] = true;
						} elseif (have_right('buy_item')) {
							$params['id_buyer'] = id_session();
						}
					break;
				}

				$data['user_ordered_for_feedback'] = $this->feedbacks->check_user_feedback($params);
				if (empty($data['user_ordered_for_feedback'])){
					messageInModal(translate('systmess_info_no_completed_orders_without_feedback'), 'info');
				}

				if(count($data['user_ordered_for_feedback']) == 1){
					$data['services'] = $this->_get_poster_services($data['user_ordered_for_feedback'][0]['id_order']);
				}

				$this->view->display('new/users_feedbacks/add_feedback_form_view', $data);
			break;
			case 'edit_user_feedback':
				checkPermisionAjaxModal('leave_feedback,moderate_content');

				$data['feedback'] = $this->feedbacks->getFeedback($id);

				if (empty($data['feedback'])) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				if (!have_right('moderate_content')) {
					if (!is_privileged('user', $data['feedback']['id_poster'], 'leave_feedback')) {
						messageInModal(translate('systmess_error_not_yours_feedback'));
					}

					if ($data['feedback']['status'] == 'moderated') {
						messageInModal(translate('systmess_error_already_moderated_feedback'));
					}

					if (!empty($data['feedback']['reply_text'])) {
						messageInModal(translate('systmess_error_edit_feedback_reply_exist'));
					}
				}

				$this->view->display('new/users_feedbacks/edit_feedback_form', $data);
			break;
			case 'edit_reply':
				checkPermisionAjaxModal('leave_feedback,moderate_content');

				$data['feedback'] = $this->feedbacks->getFeedback($id);

				if (empty($data['feedback'])) {
					messageInModal('systmess_error_invalid_data');
				}

				if (!have_right('moderate_content')) {
					if (!is_privileged('user', $data['feedback']['id_user'], 'leave_feedback')) {
						messageInModal('systmess_error_permission_not_granted');
					}

					if ($data['feedback']['status'] == 'moderated') {
						messageInModal('systmess_error_already_moderated_feedback');
					}
				}

				$this->view->display('new/users_feedbacks/edit_feedback_reply_form', $data);
			break;
			case 'add_reply':
				checkPermisionAjaxModal('leave_feedback,moderate_content');

				$data['feedback'] = $this->feedbacks->getFeedback($id);

				if (empty($data['feedback'])) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				if (have_right('leave_feedback')) {
					if (!is_privileged('user', $data['feedback']['id_user'], 'leave_feedback')) {
						messageInModal(translate('systmess_error_invalid_data'));
					}

					if ($data['feedback']['status'] == 'moderated') {
						messageInModal(translate('systmess_error_already_moderated_feedback'));
					}

					if (!empty($data['feedback']['reply_text'])) {
						messageInModal(translate('systmess_error_edit_feedback_reply_exist'));
					}
				}

				$this->view->display('new/users_feedbacks/add_feedback_reply_form', $data);
			break;
			case 'edit_feedback':
				if (!have_right('moderate_content'))
					messageInModal(translate("systmess_error_rights_perform_this_action"));

				$data['feedback'] = $this->feedbacks->getFeedback($id);
				$this->view->display('admin/users_feedbacks/edit_feedback_view', $data);
			break;
			case 'detail':
				if (!have_right('manage_seller_orders') && !have_right('buy_item')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

				$id_user = privileged_user_id();
				$id_feedback = (int) $this->uri->segment(4);
				$data['feedback'] = $this->feedbacks->get_feedback_details(array('id_feedback' => $id_feedback));
				if(empty($data['feedback'])){
					messageInModal('Feedback does not exist.');
				}

				if (!empty($data['feedback']['services'])){
					$data['feedback']['services'] = unserialize($data['feedback']['services']);
				}

				if (!empty($data['feedback']['order_summary'])){
					$data['feedback']['order_summary'] = unserialize($data['feedback']['order_summary']);
				}

				$data['user_services_form'] = $this->feedbacks->getServiceByGroup($data['feedback']['poster']['user_group']);
				$data['helpful_feedbacks'] = $this->feedbacks->get_helpful_by_feedback($id_feedback, $id_user);
				$data['feedback_written'] = is_privileged('user', $data['feedback']['id_poster']);

				$this->view->assign($data);
				$this->view->display('new/users_feedbacks/my/popup_details_view');
			break;
		}
	}

	function ajax_feedback_operation() {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
		}

		$this->_load_main();
		$id_user = privileged_user_id();
		$id = (int) $_POST['id'];
		$op = $this->uri->segment(3);
		switch ($op) {
			case 'add_feedback':
				if (!have_right('leave_helpfull') || !have_right('leave_feedback')) {
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => translate('feedback_form_title_label'),
						'rules' => array('required' => '', 'max_len[200]' => '')
					),
					array(
						'field' => 'description',
						'label' => translate('feedback_form_comment_label'),
						'rules' => array('required' => '', 'max_len[1000]' => '')
					),
					array(
						'field' => 'rating',
						'label' => translate('feedback_form_click_to_rate_label'),
						'rules' => array('required' => '')
					),
					array(
						'field' => 'order',
						'label' => translate('feedback_form_what_was_ordered'),
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_order = intVal($_POST['order']);
				if (!$id_order) {
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
				}

				$this->load->model('Orders_Model', 'orders');
				if (!$this->orders->isMyOrder($id_order, $id_user)) {
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }

                $order = $this->orders->get_order($id_order);
				if ($this->feedbacks->iWroteFeedback($id_user, $id_order)) {
					jsonResponse(translate('feedback_form_feedback_already_exist'));
				}

				$rating = intVal($_POST['rating']);
				if ($rating <= 0) {
					jsonResponse(translate('systmess_error_must_rate'));
				}

				$performances = TRUE;
				foreach ($_POST['services'] as $service) {
					if ($service <= 0) {
						$performances = FALSE;
						break;
					}
				}
				if (!$performances) {
					jsonResponse(translate('systmess_error_must_rate_all_order_performances'));
				}

				$services_array = $_POST['services'];

				$service = arrayByKey($this->feedbacks->getServices(array_keys($services_array), 'id_service, s_title'), 'id_service');

				foreach ($services_array as $id_service => $user_rating) {
					$new_array[$service[$id_service]['s_title']] = $user_rating;
				}

				$insert = array(
					'id_user' => user_group_type() == 'Seller' ? $order['id_buyer'] : $order['id_seller'],
					'id_poster' => $id_user,
					'id_order' => $id_order,
					'create_date' => date('Y-m-d H:i:s'),
					'title' => cleanInput($_POST['title']),
					'text' => cleanInput($_POST['description']),
					'rating' => $rating,
					'services' => serialize($new_array)
				);
				$ordered_list = $this->orders->get_ordered_items(array('id_order' => $id_order));

				$order_summary = array();
				foreach ($ordered_list as $order_item) {
					$order_summary[] = array('title' => $order_item['title'], 'id_ordered' => $order_item['id_ordered_item'], 'id_item' => $order_item['id_item']);
				}

				$insert['order_summary'] = serialize($order_summary);

				if (have_right('buy_item')) {
					$insert['poster_group'] = 'Buyer';
				} elseif (have_right('sell_item')) {
					$insert['poster_group'] = 'Seller';
				}

				$id_feedback = $this->feedbacks->set_feedback($insert);
				if (!$id_feedback) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->load->model('User_Statistic_Model', 'statistic');
				$users_statistic = array(
					$insert['id_poster'] => array('feedbacks_wrote' => 1),
					$insert['id_user'] => array('feedbacks_received' => 1)
				);
				$this->statistic->set_users_statistic($users_statistic);

				if (have_right('buy_item')){
					$company_info = model('Company')->get_seller_base_company($insert['id_user']);
					if(!empty($company_info)){
						$this->feedbacks->up_company_rating($id_order, $rating);
						model('Elasticsearch_Company')->index_company($company_info['id_company']);
					}
				}

				jsonResponse(translate('systmess_success_feedback_was_saved_successfully'), 'success', array('order' => $id_order));
			break;
			case 'edit_feedback':
				if (!have_right('leave_helpfull') || !have_right('leave_feedback')) {
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => translate('edit_feedback_form_title_label'),
						'rules' => array('required' => '', 'max_len[200]' => '')
					),
					array(
						'field' => 'description',
						'label' => translate('edit_feedback_form_description_label'),
						'rules' => array('required' => '', 'max_len[1000]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_feedback = (int) $_POST['feedback'];
				$feedback = $this->feedbacks->get_user_feedback($id_feedback);
				if (empty($feedback)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!have_right('moderate_content')) {
					if (!is_privileged('user', $feedback['id_poster'], true)) {
						jsonResponse(translate('systmess_error_not_yours_feedback'));
					}

					if (!empty($feedback['reply_text'])) {
						jsonResponse(translate('systmess_error_edit_feedback_reply_exist'));
					}

					if ($feedback['status'] == 'moderated') {
						jsonResponse(translate('systmess_error_already_moderated_feedback'));
					}
				}

				$update_column = array(
					'text' => cleanInput($_POST['description']),
					'title' => cleanInput($_POST['title'])
				);

				if (!$this->feedbacks->update_feedback(intVal($_POST['feedback']), $update_column)) {
					jsonResponse(translate('systmess_internal_server_error'), 'error');
				}

				$resp = array(
					'text' => $update_column['text'],
					'title' => $update_column['title'],
					'id_feedback' => (int) $_POST['feedback']
				);

				jsonResponse(translate('systmess_success_feedback_was_saved_successfully'), 'success', $resp);

			break;
			case 'edit_reply':
				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => translate('edit_feedback_reply_form_message_label'),
						'rules' => array('required' => '', 'max_len[1000]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_feedback_replied = (int) $_POST['reply'];
				if (empty($id_feedback_replied)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$feedback = $this->feedbacks->getFeedback($id_feedback_replied);
				if (empty($feedback)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!have_right('moderate_content')) {
					if (!have_right('leave_feedback')) {
						jsonResponse(translate('systmess_error_permission_not_granted'));
					}

					if ($feedback['status'] == 'moderated') {
						jsonResponse(translate('systmess_error_already_moderated_feedback'));
					}

					if (!is_privileged('user', $feedback['id_user'], 'leave_feedback')) {
						jsonResponse(translate('systmess_error_permission_not_granted'));
					}
				}

				$update_column = array(
					'reply_text' => cleanInput($_POST['description']),
					'reply_date' => date('Y-m-d H:i:s')
				);

				if (!$this->feedbacks->update_feedback($id_feedback_replied, $update_column)) {
					jsonResponse(translate('systmess_internal_server_error'), 'error', array('cancel' => FALSE));
				}

				jsonResponse(translate('systmess_success_edited_feedback_reply'), 'success');

			break;
			case 'help':
				$type = cleanInput($_POST['type']);
				if (!in_array($type, array('y', 'n'))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$type = $type == 'y' ? 1 : 0;

				if (empty($id)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$feedback_info = $this->feedbacks->get_simple_feedback($id);
				if (empty($feedback_info)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$response_data = array(
					'counter_plus' => $feedback_info['count_plus'],
					'counter_minus' => $feedback_info['count_minus']
				);

				if ($id_user == $feedback_info['id_user']) {
					jsonResponse(translate('systmess_error_helpful_vote_for_yourself'), 'info');
				}

				$my_feedback_helpful = $this->feedbacks->exist_helpful($id, $id_user);
				$action = $type ? 'plus' : 'minus';
				if (empty($my_feedback_helpful['counter'])) {
					unset($my_feedback_helpful);
				}

				// If this is the first vote for this feedback
				if (empty($my_feedback_helpful)) {
					$insert = array(
						'id_feedback' 	=> $id,
						'id_user' 		=> $id_user,
						'help'			=> $type
					);

					$columns['count_' . $action] = '+';

					if (!$this->feedbacks->set_helpful($insert)) {
						jsonResponse(translate('systmess_internal_server_error'));
					}

					$this->feedbacks->modify_counter_helpfull($id, $columns);
					$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
					$response_data['select_' . $action] = true;

					jsonResponse(translate('systmess_success_feedback_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If it is a vote cancellation
				if ($my_feedback_helpful['help'] == $type) {
					$this->feedbacks->remove_helpful($id, $id_user);

					$columns['count_' . $action] = '-';
					$this->feedbacks->modify_counter_helpfull($id, $columns);

					$response_data['counter_' . $action] = --$response_data['counter_' . $action];
					$response_data['remove_' . $action] = true;

					jsonResponse(translate('systmess_success_feedback_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If a vote has been changed
				$update['help'] = $type;
				$columns = array(
					'count_plus' => $type ? '+' : '-',
					'count_minus' => $type ? '-' : '+'
				);

				if (!$this->feedbacks->update_helpful($id, $update, $id_user)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->feedbacks->modify_counter_helpfull($id, $columns);

				$opposite_action = $action == 'plus' ? 'minus' : 'plus';

				$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
				$response_data['counter_' . $opposite_action] = --$response_data['counter_' . $opposite_action];
				$response_data['select_' . $action] = true;
				$response_data['remove_' . $opposite_action] = true;

				jsonResponse(translate('systmess_success_feedback_helpful_vote_successfully_saved'), 'success', $response_data);

			break;
			case 'items_list':
				$this->load->model('Orders_Model', 'order');
				$id_order = intVal($_POST['order']);

				if (!$this->order->isMyOrder($id_order, privileged_user_id())) {
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
				}

				$data['ordered_list'] = $this->order->get_ordered_items(array('id_order' => $id_order));

				if (empty($data['ordered_list'])) {
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
				}

				$content = $this->view->fetch('new/users_feedbacks/ordered_list_view', $data);

				jsonResponse('', 'success', array('content' => $content));

			break;
			case 'get_poster_services':
				$validator_rules = array(
					array(
					'field' => 'order',
					'label' => translate('feedback_form_what_was_ordered'),
					'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors(), 'error');
				}

				$id_order = (int) $_POST['order'];

				jsonResponse('', 'success', array('services' => $this->_get_poster_services($id_order)));
			break;
		}
	}

	private function _get_poster_services($id_order = 0){
		if (have_right('buy_item')){
			$user_type = 'id_seller';
		}

		if (have_right('sell_item') || (have_right('leave_feedback') && user_type('users_staff'))){
			$user_type = 'id_buyer';
		}

		$user_info = model('Orders')->get_user_by_order('u.user_group, u.idu', $id_order, $user_type);

		$content = '';
		if (!empty($user_info)){
			$data['user_services_form'] = $this->feedbacks->getServiceByGroup($user_info['user_group']);
			$data['user'] = $user_info['idu'];

			$content = $this->view->fetch('new/users_feedbacks/my/user_services_view', $data);
		}

		return $content;
	}
}

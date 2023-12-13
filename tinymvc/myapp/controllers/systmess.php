<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class SystMess_Controller extends TinyMVC_Controller {
    private $messages_per_page = 15;

	public function administration()
	{
		checkAdmin('manage_content');
		$type = empty(uri()->segment(3)) ? 'info' : uri()->segment(3);

		views()->assign([
			'systmessages' => $this->systmess->get_messages(['type' => $type]),
			'modules'      => model(Ep_Modules_Model::class)->get_all_modules(),
			'type'         => $type,
			'title'        => 'Notifications'
        ]);

		views()->display('admin/header_view');
		views()->display('admin/systmess/index_view');
		views()->display('admin/footer_view');
	}

	public function ajax_systmessages_administration()
	{
		checkIsLoggedAjax();

		$conditions = array_merge(
			[
				'per_p'   => request()->request->getInt('iDisplayLength'),
				'start'   => request()->request->getInt('iDisplayStart'),
				'sort_by' => flat_dt_ordering(request()->request->all(), [
					'dt_id'             => 'idmess',
					'dt_type'           => 'mess_type',
					'dt_code'           => 'mess_code',
					'dt_module'         => 'name_module',
					'dt_title'          => 'title',
					'dt_changed'        => 'date_changed',
					'dt_proofread'        => 'date_proofreading',
				]),
			],
			dtConditions(request()->request->all(), [
				['as' => 'type',                    'key' => 'type_mess',               'type' => 'string'],
				['as' => 'keywords',                'key' => 'sSearch',                 'type' => 'string'],
				['as' => 'module',                  'key' => 'module',                  'type' => 'int'],
				['as' => 'is_proofread',            'key' => 'is_proofread',            'type' => 'int'],
				['as' => 'proofreading_date_from',  'key' => 'date_proofread_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
				['as' => 'proofreading_date_to',    'key' => 'date_proofread_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
				['as' => 'date_changed_from',       'key' => 'date_changed_from',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
				['as' => 'date_changed_to',         'key' => 'date_changed_to',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
			])
		);

		$systmessages = $this->systmess->get_messages($conditions);
		$systmessages_count = $this->systmess->get_messages_count($conditions);


		$output = [
			'sEcho' 			   => request()->request->getInt('sEcho'),
			'iTotalRecords' 	   => $systmessages_count,
			'iTotalDisplayRecords' => $systmessages_count,
			'aaData' 			   => []
		];

		if (empty($systmessages)) {
			jsonResponse('', 'success', $output);
		}

		foreach ($systmessages as $mess) {
			$editBtn = <<<BUTTON
                    <a
                        data-title="Edit system message"
                        href="/systmess/ajax_systmessages_operation/edit_form/{$mess['idmess']}"
                        class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT fs-16"
                        id="emess-{$mess['idmess']}"
                        title="Edit Message"></a>
                    BUTTON;
			$deleteBtn = <<<BUTTON
                    <a
                        class="ep-icon ep-icon_remove txt-red fs-16 confirm-dialog"
                        data-callback="delete_systmess"
                        data-id="{$mess['idmess']}"
                        title="Remove this system message"
                        data-message="Are you sure you want to delete this system message?"
                        href="#"></a>
                    BUTTON;
			$viewLogBtn = <<<BUTTON
                    <a
                        data-title="View log"
                        class="ep-icon ep-icon_paper fancybox fancybox.ajax fs-16"
                        title="View log"
                        id="logs-{$mess['idmess']}"
                        href="/systmess/ajax_systmessages_operation/view_log/{$mess['idmess']}"></a>
                    BUTTON;

			$output['aaData'][] = [
				'dt_id'      => $mess['idmess'],
				'dt_type'    => ucfirst($mess['mess_type']) . '<a
                                    class="txt-green fs-16 ep-icon ep-icon_filter dt_filter pull-right"
                                    data-title="Type"
                                    title="Filter by ' . ucfirst($mess['mess_type']) . '"
                                    data-value-text="' . ucfirst($mess['mess_type']) . '"
                                    data-value="' . $mess['mess_type'] . '"
                                    data-name="type_mess"></a>',
				'dt_code'    => $mess['mess_code'] . ($mess['is_proofread'] == 0 ? '<br/><span class="label label-danger tooltipstered">Not proofread</span>' : ''),
				'dt_module'  => $mess['name_module'] . '<a
                                    class="txt-green fs-16 ep-icon ep-icon_filter dt_filter pull-right"
                                    data-title="Module"
                                    title="Filter by ' . $mess['name_module'] . '"
                                    data-value-text="' . $mess['name_module'] . '"
                                    data-value="' . $mess['id_module'] . '"
                                    data-name="module"></a>',
				'dt_title'   => $mess['title'],
				'dt_message' => $mess['message'],
				'dt_proofread' => getDateFormat($mess['date_proofreading'], 'Y-m-d H:i:s', 'j M, Y H:i'),
				'dt_changed' => getDateFormat($mess['date_changed'], 'Y-m-d H:i:s', 'j M, Y H:i'),
				'dt_actions' => $editBtn . $deleteBtn . $viewLogBtn,
			];
		}

		jsonResponse('', 'success', $output);
	}

	public function ajax_systmessages_operation()
	{
		checkIsLoggedAjax();

		checkAdminAjaxModal('manage_notifications');

		$type = cleanInput(uri()->segment(3));

		switch ($type) {
			case 'edit_form':
                $idMess = (int) uri()->segment(4);

                if(!$this->systmess->existMessageById($idMess)){
                    messageInModal('This message does not exist');
                }

                views()->assign([
                        'message' => $this->systmess->get_message(['id_mess' => $idMess]),
                        'modules' => model(Ep_Modules_Model::class)->get_all_modules()
                ]);
				views()->display('admin/systmess/form_view');
			break;
			case 'view_log':
                $idMess = (int) uri()->segment(4);

                if(!$this->systmess->existMessageById($idMess)){
                    messageInModal('This message does not exist');
                }

				$message = $this->systmess->get_message(['id_mess' => $idMess]);
				views()->display('admin/systmess/log_view', ['logs' => json_decode($message['log'], true)]);
			break;
			case 'add_form':
				views()->assign([
					'modules' => model(Ep_Modules_Model::class)->get_all_modules()
				]);
				views()->display('admin/systmess/form_view');
			break;
			case 'delete':
				$idMess = request()->request->getInt('idmess');

                if(!$this->systmess->existMessageById($idMess)){
                    jsonResponse('This message does not exist');
                }

				if ($this->systmess->delete_message($idMess)) {
					jsonResponse('The messages have been successfully deleted.', 'success');
				}

				jsonResponse('Error: The messages have not been deleted.');
			break;
			case 'edit':
				$idMess = request()->request->getInt('idmess');

                if(!$this->systmess->existMessageById($idMess)){
                    messageInModal('This message does not exist');
                }

				$validator = $this->validator;
				$validator_rules = [
					[
						'field' => 'mess_code',
						'label' => 'Message\'s code',
						'rules' => ['required' => '']
					],
					[
						'field' => 'message',
						'label' => 'Message',
						'rules' => ['required' => '']
					],
					[
						'field' => 'title',
						'label' => 'Title',
						'rules' => ['required' => '']
					],
					[
						'field' => 'module',
						'label' => 'Module',
						'rules' => ['required' => '']
					],
				];
				$validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}
				$systemss = $this->systmess->get_message(['id_mess' => $idMess]);

				$update = [
					'mess_type'         => cleanInput(request()->get('mess_type')),
					'mess_code'         => cleanInput(request()->get('mess_code')),
					'title'             => cleanInput(request()->get('title')),
					'triggered_actions' => request()->get('triggered_actions'),
					'message'           => request()->get('message'),
					'module'            => request()->request->getInt('module')
				];

				if ($this->systmess->existMessage($update['mess_code'], $idMess)) {
					jsonResponse('This Message\'s code already exists!');
                }

				$proofReading = request()->request->getInt('proofreaded');

                $date = date('Y-m-d H:i:s');
                if ($proofReading == 1 && have_right('manage_proofread')) {
					$update['date_proofreading'] = $date;
                }

                //get only the values that have changed
                $changedValues = array_diff_assoc(array_intersect_key($update, $systemss), $systemss);

                //check if the keys that changed are in this array
                $triggeredChanged = array('message', 'title', 'triggered_actions');
                if (
                    !empty(array_intersect($triggeredChanged, array_keys($changedValues)))
                    || ($proofReading == 0 && $proofReading != $systemss['is_proofread'])
                ) {
					$update['date_changed'] = $date;
				}

				$log = [];
				foreach ($changedValues as $changedKey => $changedValue) {
					$log[] = $this->getLogFor($changedKey, [
						'old'               => $systemss[$changedKey],
						'new'               => $update[$changedKey],
						'date_proofreading' => isset($update['date_proofreading']) ? $update['date_proofreading'] : $date,
						'date_changed'      => isset($update['date_changed']) ? $update['date_changed'] : $date,
					]);
                }

				if (!empty(array_filter($log))) {
					$existingLog = json_decode($systemss['log'], true);
					$existingLog[] = array_filter($log);
					$update['log'] = json_encode($existingLog, JSON_FORCE_OBJECT);
				}
				if ($this->systmess->update_message($idMess, $update)) {
					jsonResponse('The messages have been successfully updated.', 'success');
				}

				jsonResponse('Error: The messages have not been updated.');
			break;
			case 'add':
				$validator = $this->validator;
				$validator_rules = [
					[
						'field' => 'mess_code',
						'label' => 'Message\'s code',
						'rules' => ['required' => '']
					],
					[
						'field' => 'message',
						'label' => 'Message',
						'rules' => ['required' => '']
					],
					[
						'field' => 'title',
						'label' => 'Title',
						'rules' => ['required' => '']
					],
					[
						'field' => 'module',
						'label' => 'Module',
						'rules' => ['required' => '']
					],
				];
				$validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}

                $date = date('Y-m-d H:i:s');
				$insert = [
					'mess_type'         => cleanInput(request()->get('mess_type')),
					'mess_code'         => cleanInput(request()->get('mess_code')),
					'title'             => cleanInput(request()->get('title')),
					'triggered_actions' => request()->get('triggered_actions'),
					'message'           => request()->get('message'),
					'module'            => request()->request->getInt('module'),
                    'date_changed'      => $date,
                    'log'               => json_encode([getDateFormat($date) . ' Message was created.'], JSON_FORCE_OBJECT)
                ];

				if ($this->systmess->existMessage($insert['mess_code'])) {
					jsonResponse('This Message\'s code already exists!');
				}

				$proofReading = request()->request->getInt('proofreaded');

				if ($proofReading == 1 && have_right('manage_proofread')) {
					$insert['date_proofreading'] = $date;
				}

				if ($this->systmess->set_message($insert)) {
					jsonResponse('The message has been successfully added.', 'success');
				}

				jsonResponse('Error: The messages have not been added.');
			break;
		}
	}

	private function getLogFor($type, $changes)
	{
		switch ($type) {
			case 'title':
				return 'The title has changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
			case 'message':
				return 'The message has changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
			case 'date_proofreading':
				return 'The systmess has been proofread on ' . getDateFormat($changes['date_proofreading']) . ' by ' . user_name_session();
			break;
			case 'module':
				return 'The module of the message has been changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
			case 'type':
				return 'The type of the message has been changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
			case 'mess_code':
				return 'The message code has been changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
			case 'triggered_actions':
				return 'The triggered actions has been changed from "' . $changes['old'] . '" to - "' . $changes['new'] . '" on ' . getDateFormat($changes['date_changed']) . ' by ' . user_name_session();
			break;
		}
	}

	public function ajax_systmess_operation(){
		checkIsAjax();
		checkIsLoggedAjax();

		$id_user = privileged_user_id();

		$type = $this->uri->segment(3);
		switch($type){
            case 'save_notifications_settings':
                $id_user = id_session();

                #region Send subscription
                $id_check = (int) (!empty($_POST['subscription']));
                model('user')->updateUserMain($id_user, array('subscription_email' => $id_check));
                $this->session->subscription_email = $id_check;
                #endregion Send subscription

                #region Send Email notifications by module
                $modules = model('ep_modules')->get_ep_modules(array('email_notification' => 1));
                $user_settings = arrayByKey(model('users_systmess_settings')->get_settings($id_user), 'module');
                $post_modules = $_POST['modules'];

                foreach ($modules as $module) {
                    if (!empty($post_modules[$module['id_module']])) {
                        if (!empty($user_settings[$module['id_module']])) {
                            continue;
                        }
                        model('users_systmess_settings')->add($id_user, $module['id_module']);
                    } else {
                        model('users_systmess_settings')->remove($id_user, $module['id_module']);
                    }
                }
                #endregion Send Email notifications by module

                jsonResponse('Success: Notifications settings saved', 'success');
            break;
			case 'show_notification_nav':
				$data = array(
					'type' => 'all',
					'status' => 'all'
				);

				$data['status'] = 'new';

				$data['per_page'] = $this->messages_per_page;
				if(!$data['page'] = intval($_POST['page']))
					$data['page'] = 1;

				$data['type_notification'] = 'all';

				$data['messages'] = $this->systmess->get_user_messages($id_user, $data);
				$data['counters_all'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'new'));
				$data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'deleted'));
				$data['counters'] = $this->systmess->counter_user_notifications_by_user(array('user' => $id_user));

				$rez = array(
					'block' => $this->view->fetch('user/message/modal_systmess_nav_view', $data)
				);

				jsonResponse('', 'success', $rez);
			break;
			case 'show_notification_block_new':
				$type = cleanInput($_POST['type']);

				$data = array(
					'type' => $type,
					'status' => 'all'
				);

				if($type == 'all'){
					$data['status'] = 'new';
				} elseif($type == 'deleted'){
					$data['status'] = 'deleted';
				}

				$data['per_page'] = $this->messages_per_page;
				if(!$data['page'] = intval($_POST['page']))
					$data['page'] = 1;

				$data['type_notification'] = $type;

				$data['messages'] = $this->systmess->get_user_messages($id_user, $data);
				$data['counters_all'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'new'));
				$data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'deleted'));
				$data['counters'] = $this->systmess->counter_user_notifications_by_user(array('user' => $id_user));

				$rez = array(
					'block' => $this->view->fetch('new/nav_header/notifications/notifications_view', $data),
					'count_notifications' => $this->systmess->counter_user_notifications($id_user)
				);

				jsonResponse('', 'success', $rez);
			break;
			case 'show_notification_block2':
				$status = cleanInput($_POST['status']);
				$type = cleanInput($_POST['type']);

				if(!in_array($status, array('new', 'all', 'deleted'))){
					jsonResponse('This status doesn\'t exist');
				}

				if(!empty($type) && !in_array($type, array('all', 'notice', 'warning'))){
					jsonResponse('This type doesn\'t exist');
				}

				$data = array(
					'status' => $status
				);

				if(!empty($type)){
					$data['type'] = $type;
				}else{
					$data['type'] = 'all';
				}

				$data['counters'] = $this->systmess->counter_user_notifications_by_user(array('user' => $id_user, 'type' => $data['type'], 'status' => $data['status']));

				$data['per_page'] = $this->messages_per_page;
				$page_total = ceil($data['counters'][$data['type']] / $data['per_page']);

				if(!$data['page'] = intval($_POST['page'])){
					$data['page'] = 1;
				}

				if($page_total < $data['page'] && $data['page'] != 1){
					$data['page'] = $page_total;
				}

				$data['messages'] = $this->systmess->get_user_messages($id_user, $data);
				$data['counters_all'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status_active' => true));
				$data['counters_unread'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'new'));
				$data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'deleted'));

				// print_r($data['counters']);
				$rez = array(
					'block' => $this->view->fetch('new/nav_header/notifications/notifications2_block_view', $data),
					'count_notifications' => $this->systmess->counter_user_notifications($id_user)
				);

				jsonResponse('', 'success', $rez);
			break;
			case 'show_notification_block_tablet':
				$type = cleanInput($_POST['type']);

				$data = array(
					'type' => $type,
					'status' => 'all'
				);

				if($type == 'all'){
					$data['status'] = 'new';
				} elseif($type == 'deleted'){
					$data['status'] = 'deleted';
				}

				$data['per_page'] = $this->messages_per_page;
				if(!$data['page'] = intval($_POST['page']))
					$data['page'] = 1;

				$data['type_notification'] = $type;

				$data['messages'] = $this->systmess->get_user_messages($id_user, $data);
				$data['counters_all'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'new'));
				$data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'deleted'));
				$data['counters'] = $this->systmess->counter_user_notifications_by_user(array('user' => $id_user));

				$rez = array(
					'block' => $this->view->fetch('user/message/modal_systmess_new_view', $data),
					'count_notifications' => $this->systmess->counter_user_notifications($id_user)
				);

				jsonResponse('', 'success', $rez);
			break;
			//OLD
			case 'show_notification_block':
				$type = cleanInput($_POST['type']);

				$data = array(
					'type' => $type,
					'status' => 'all'
				);

				if($type == 'all'){
					$data['status'] = 'new';
				} elseif($type == 'deleted'){
					$data['status'] = 'deleted';
				}

				$data['per_page'] = $this->messages_per_page;
				if(!$data['page'] = intval($_POST['page']))
					$data['page'] = 1;

				$data['messages'] = $this->systmess->get_user_messages($id_user, $data);
				$data['counters_all'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'new'));
				$data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(array('type' => 'all', 'user' => $id_user, 'status' => 'deleted'));
				$data['counters'] = $this->systmess->counter_user_notifications_by_user(array('user' => $id_user));

				$rez = array(
                    'block' => $this->view->fetch('admin/user/notification_messages/modal_view', $data),
                    'count_notifications' => $this->systmess->counter_user_notifications($id_user)
                );

				jsonResponse('', 'success', $rez);
			break;
			//OLD END
			case 'notification_seen':
				$id_mess = intVal($_POST['message']);
				$mess_info = $this->systmess->get_user_message($id_mess);

				if(($mess_info['status'] == 'new') && ($mess_info['idu'] == $id_user))
					$this->systmess->update_user_message($id_mess, array('status' => 'seen'));

				jsonResponse('', 'success', array('count_notifications' => $this->systmess->counter_user_notifications($id_user)));
			break;
			case 'notification_readed':
				$id_messages = array();
				foreach($_POST['messages'] as $mess){
					$id_messages[] = intVal($mess);
				}

				$messages_info = $this->systmess->get_messages_multiple($id_messages, $id_user);
				$id_messages_cleaned = array();
				foreach($messages_info as $messages_info_item){
					if($messages_info_item['status'] == 'new'){
						$id_messages_cleaned[] = intVal($messages_info_item['id_um']);
					}
				}

				if(empty($id_messages_cleaned)){
					jsonResponse("Error: Please select your messages for mark as readed.");
				}

				$this->systmess->update_user_messages_status(implode(',', $id_messages_cleaned), 'seen');
				jsonResponse("The notification(s) has been marked as readed.", 'success',  array('count_notifications' => $this->systmess->counter_user_notifications($id_user)));
			break;
			case 'notification_deleted':
				is_allowed("freq_allowed_delete_systmess");
				$id_messages = array();
				foreach($_POST['messages'] as $mess){
					$id_messages[] = intVal($mess);
				}

				if(empty($id_messages) || empty($messages_info = $this->systmess->get_messages_multiple($id_messages, $id_user))) {
					jsonResponse("You did not check any notification(s).", "warning");
                }

				$for_delete = array();
				$for_trash = array();
				foreach($messages_info as $message){
					if($message['status'] == 'deleted')
						$for_delete[] = $message['id_um'];
					else
						$for_trash[] = $message['id_um'];
				}

				if(!empty($for_trash)){
					$this->systmess->update_user_messages_status(implode(',', $for_trash));
					jsonResponse("The notification(s) has been moved to trash.", 'success');
				}

				if(!empty($for_delete)){
					$this->systmess->delete_user_messages(implode(',', $for_delete));
					jsonResponse("The notification(s) has been deleted.", 'success');
				}
			break;
			case 'delete_all_from_trash':
				is_allowed("freq_allowed_delete_systmess");
				$this->systmess->delete_user_trash_messages($id_user);
				jsonResponse("The notification(s) has been deleted.", 'success');
		}
	}

	function popup_forms(){
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		$op = $this->uri->segment(3);

		switch($op){
			case 'notification':
                $popupType = request()->query->get("type") ?? "all";
                $idUser = privileged_user_id();

                $data = [
                    'status' => 'new',
                    'type' => $popupType,
                ];

                $data['per_page'] = $this->messages_per_page;
                if (!($data['page'] = request()->request->getInt('page'))) {
                    $data['page'] = 1;
                }

                $data['messages'] = $this->systmess->get_user_messages($idUser, $data);

                if (empty($data['messages'])) {
                    $data['status'] = 'all';
                    $data['messages'] = $this->systmess->get_user_messages($idUser, $data);
                }

                $data['counters_all'] = $this->systmess->counter_user_notifications_by_type(['type' => 'all', 'user' => $idUser, 'status_active' => true]);
                $data['counters_unread'] = $this->systmess->counter_user_notifications_by_type(['type' => 'all', 'user' => $idUser, 'status' => 'new']);
                $data['counters_deleted'] = $this->systmess->counter_user_notifications_by_type(['type' => 'all', 'user' => $idUser, 'status' => 'deleted']);
                $data['counters'] = $this->systmess->counter_user_notifications_by_user(['user' => $idUser, 'status' => $data['status']]);

                $this->view->assign($data);
                $this->view->display('new/nav_header/notifications/notifications2_view');
			break;
		}
	}

    public function export_syst_mess()
    {
        $now = date('Y-m-d-H_i');
        $this->returnReport($this->systmess->get_messages(['conditions' => ['is_not_used' => 0]]), "notifications_{$now}.xlsx");
    }

    /**
     * Get report
     *
     * @param array $data - log data
     * @param string $fileName - name of the file with extension
     *
     */
    private function returnReport($data, $fileName = 'notifications.xlsx')
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('User Activity');

        $headerColumns = [
            'A' => ['name' => 'Message Key',       'width' => 40],
            'B' => ['name' => 'Type',              'width' => 20],
            'C' => ['name' => 'Title',             'width' => 70],
            'D' => ['name' => 'Message',           'width' => 90],
            'E' => ['name' => 'Module',            'width' => 30],
            'F' => ['name' => 'Triggered actions', 'width' => 30],
            'G' => ['name' => 'Modified on',       'width' => 30],
            'H' => ['name' => 'Proofread',         'width' => 20]
        ];

		//region generate headings
		$rowIndex = 1;

        foreach($headerColumns as $letter => $heading)
        {
            $activeSheet->getColumnDimension($letter)->setWidth($heading['width']);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValue($letter . $rowIndex, $heading['name'])
                        ->getStyle($letter . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);
        }
        //endregion generate headings

        //region introduce data
        $rowIndex = 2;
        $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach($data as $one)
        {
            $activeSheet
                ->setCellValue("A$rowIndex", $one['mess_code'])
                ->setCellValue("B$rowIndex", $one['mess_type'])
                ->setCellValue("C$rowIndex", $one['title'])
                ->setCellValue("D$rowIndex", $one['message'])
                ->setCellValue("E$rowIndex", $one['name_module'])
                ->setCellValue("F$rowIndex", $one['triggered_actions'])
                ->setCellValue("G$rowIndex", getDateFormat($one['date_modified']))
                ->setCellValue("H$rowIndex", $one['is_proofread'] ? 'Yes' : 'No');

            $rowIndex++;
        }
        //endregion introduce data

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

}

?>

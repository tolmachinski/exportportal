<?php

use App\Filesystem\UserFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Sticker_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();

	private function _load_main(){
        $this->load->model('Category_Model', 'category');
        $this->load->model('Stickers_Model', 'stickers');
    }

    function my() {
		checkPermision('manage_personal_stickers');

        $this->_load_main();
		// GET SELECTED STATUS FROM URI - IF EXIST
		$data['status_select'] = $this->uri->segment(3);

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'sticker',
			'title'	=> 'My Sticker'
		);
		$data['breadcrumbs'] = $this->breadcrumbs;

		$counter_by_status = arrayByKey($this->stickers->get_counter_by(',status', 'status', array('id_user' => id_session())), 'status');
		$counter_by_priority = arrayByKey($this->stickers->get_counter_by(',priority', 'priority', array('id_user' => id_session(), 'not' => "'trash','archived'")), 'priority');
		$data['statuses'] = array_merge($counter_by_status, $counter_by_priority);

		// ARRAY WITH FULL STATUSES DETAILS
		$status_array = array('new','read','archived','trash','personal','important');

		// IF THE STATUS WAS NOT SETTED IN THE URI - DEFAULT STATUS IS "NEW"
		if(!in_array($data['status_select'], $status_array))
			$data['status_select'] = 'new';

		$conditions = array('id_user' => id_session());

		global $tmvc;
		$data['stickers_per_page'] = $conditions['limit'] = $tmvc->my_config['user_stickers_per_page'];

		if(!in_array($data['status_select'], array('personal','important')))
			$conditions['status'] = $data['status_select'];
		else
			$conditions['priority'] = $data['status_select'];

		$data['stickers'] = $this->stickers->get_stickers($conditions);
		$data['status_select_count'] = $this->stickers->counter_by_conditions($conditions);
		$this->view->assign($data);
        $this->view->display("admin/header_view");
        $this->view->display("admin/sticker/index_view");
        $this->view->display("admin/footer_view");
    }

	public function ajax_sticker_operation(){
		if (!isAjaxRequest()){
            headerRedirect();
		}

		checkPermisionAjax('manage_personal_stickers');

		$this->_load_main();
		$this->load->model('User_Model', 'users');

		$op = $this->uri->segment(3);
		switch($op){
			case 'search_stickers':
				$keywords = cleanInput(cut_str($_POST['keywords']));
				$status = cleanInput($_POST['status']);

				$status_array = array('new','read','archived','trash','personal','important', '');

				if(!in_array($status, $status_array))
					jsonResponse('Error: The status you selected is not correct.');

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'keywords',
						'label' => 'Keywords',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);

				if(!$validator->validate())
					jsonResponse($this->validator->get_array_errors());

				// CALCULATE LIMIT - FOR DASHBOARD PAGINATION
				global $tmvc;
				$per_page = $tmvc->my_config['user_stickers_per_page'];
				$page = 1;
				if(!empty($_POST['page']) && intVal($_POST['page']) > 1)
					$page = intVal($_POST['page']);

				$start_from = ($page  == 1) ? 0 : ($page * $per_page) - $per_page;

				$conditions = array('id_user' => id_session(), 'keywords' => $keywords);

				if($status !== ''){
					if(in_array($status, array('personal','important'))){
						$conditions['priority'] = $status;
						$conditions['not'] = "'trash','archived'";
					}else
						$conditions['status'] = $status;
				}

				if(!empty($_POST['sort']))
					$conditions['sort_by'][] = cleanInput($_POST['sort']);

				$conditions['limit'] = $start_from .", ".$per_page;
				$data['stickers'] = $this->stickers->get_stickers($conditions);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                foreach ($data['stickers'] as &$sticker) {
                    $sticker['userImageUrl'] = $publicDisk->url(UserFilePathGenerator::imagesThumbUploadFilePath($sticker['id_user_sender'], $sticker['user_photo']));
                }

				$content = $this->view->fetch("admin/sticker/sticker_item_view", $data);
				$status_select_count = $this->stickers->counter_by_conditions($conditions);

				if(!empty($data['stickers']))
					jsonResponse('','success', array('content' => $content, 'total_stickers_by_status' => $status_select_count));
				else
					jsonResponse('Error: No stickers have been found matching your seach request.');
			break;
			case 'load_stickers':
				$status = cleanInput($_POST['status']);

				$status_array = array('new','read','archived','trash','personal','important', '');

				if(!in_array($status, $status_array))
					jsonResponse('Error: The status you selected is not correct.');

				// CALCULATE LIMIT - FOR DASHBOARD PAGINATION
				global $tmvc;
				$per_page = $tmvc->my_config['user_stickers_per_page'];
				$page = 1;
				if(!empty($_POST['page']) && intVal($_POST['page']) > 1)
					$page = intVal($_POST['page']);

				$start_from = ($page  == 1) ? 0 : ($page * $per_page) - $per_page;

				$conditions = array('id_user' => id_session());

				if($status !== ''){
					if(in_array($status, array('personal','important'))){
						$conditions['priority'] = $status;
						$conditions['not'] = "'trash','archived'";
					}else
						$conditions['status'] = $status;
				}

				if(!empty($_POST['sort']))
					$conditions['sort_by'][] = cleanInput($_POST['sort']);

				$conditions['limit'] = $start_from .", ".$per_page;
				$data['stickers'] = $this->stickers->get_stickers($conditions);
				$status_select_count = $this->stickers->counter_by_conditions($conditions);
				$content = $this->view->fetch("admin/sticker/sticker_item_view", $data);

				jsonResponse('','success', array('content' => $content, 'total_stickers_by_status' => $status_select_count));
			break;
			case 'change_status':
				$sticker = cleanInput($_POST['sticker']);
				$status = cleanInput($_POST['status']);

				if(!$this->stickers->is_my_sticker($sticker))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$sticker_info = $this->stickers->get_sticker(array('id_sticker' => $sticker));

				if($status == 'trash' && $sticker_info['status'] == 'trash'){
					if($this->stickers->delete_sticker($sticker))
						jsonResponse('Your sticker has been successfully deleted.','success');
					else
						jsonResponse('Error: you cannot delete the sticker now. Please try again later.');
				}

				switch($status){
					case 'read':
						$finished_status = array('read','archived', 'trash');
						if(in_array($sticker_info['status'], $finished_status))
							jsonResponse(translate("systmess_error_rights_perform_this_action"));
					break;
					case 'archived':
						$finished_status = array('archived', 'trash');
						if(in_array($sticker_info['status'], $finished_status))
							jsonResponse(translate("systmess_error_rights_perform_this_action"));
					break;
				}

				if($this->stickers->update_sticker($sticker, array('status' => $status)))
					jsonResponse('Your sticker status has been successfully changed.','success');
				else
					jsonResponse('Error: you cannot change the status now. Please try again later.');
			break;
			case 'create_sticker':
				$data = $_POST;
				$id_user = id_session();

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'users',
						'label' => 'Recipients',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Type',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'subject',
						'label' => 'Subject',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);

				if(!$validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$users_info = arrayByKey($this->users->getUsers(array('users_list' => implode(',', $data['users']))), 'idu');

				$insert = array(
					'users_info' => $users_info,
					'users_list' => $data['users'],
					'id_user_sender' => id_session(),
					'name_user_sender' => user_name_session(),
					'subject' => cleanInput($data['subject']),
					'create_date' => date('Y-m-d H:i:s'),
					'message' => cleanInput($data['message']),
					'for_search' => date('m-d-Y H:i:s A'),
					'priority' => cleanInput($data['type']),
				);

				if($this->stickers->set_stickers($insert))
					jsonResponse('The sticker has been inserted successfully', 'success');
				else
					jsonResponse('Error: Stickers have not been inserted. Please try again later.');
			break;
			case 'update_sidebar_counters':
				$this->_load_main();

				$counter_by_status = arrayByKey($this->stickers->get_counter_by(',status', 'status', array('id_user' => id_session())), 'status');
				$counter_by_priority = arrayByKey($this->stickers->get_counter_by(',priority', 'priority', array('id_user' => id_session(), 'not' => "'trash','archived'")), 'priority');
				$statuses_counters = array_merge($counter_by_status, $counter_by_priority);

				// RETURN RESPONCE
				jsonResponse('','success', array('counters' => $statuses_counters));
			break;
		}
	}

	public function popup_forms(){
		if(!isAjaxRequest()){
		    headerRedirect();
		}

		checkPermisionAjax('manage_personal_stickers');

		$this->_load_main();
		$data['errors'] = array();
		$id_user = $this->session->id;

		$op = $this->uri->segment(3);
		switch($op){
			case 'create_sticker_form':
				$this->load->model('UserGroup_Model', 'usergroup');
				$this->load->model('User_Model', 'users');

				$data['breadcrumbs'] = $this->breadcrumbs;
				$data['users_group'] = arrayByKey($this->usergroup->getGroupsByType(array('type' => "'Admin','EP Staff'")), 'idgroup');
				$users_group_id = array_keys($data['users_group']);
				$data['users'] = $this->users->get_simple_users(array('group' => implode(',', $users_group_id), 'sort_by' => array('1' => 'user_group-desc'), 'pagination' => false));

				$this->view->display('admin/sticker/form_view', $data);
			break;
		}
	}
}

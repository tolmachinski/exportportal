<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Company_Staff_Controller extends TinyMVC_Controller
{
	function index() {
		header('location: ' . __SITE_URL);
	}

	private function load_main(){
		$this->load->model('User_Model', 'user');
		$this->load->model('Company_Model', 'company');
		$this->load->model('Category_Model', 'category');
		$this->load->model('Usergroup_Model', 'ugroup');
		$this->load->model('Company_Staff_Model', 'cstaff');
	}

    /** deleted on 2021.07.05 */
	/* function my(){
		if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if (!have_right('have_staff')){
			$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
			headerRedirect(__SITE_URL);
		}

		if (!i_have_company()) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		$this->load_main();

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'company_staff/my',
			'title'	=> 'Company staff'
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
		$this->view->assign('title', 'Company staff');
		$this->view->assign($data);
		$this->view->display($this->view_folder.'dashboard/header_view');
		$this->view->display($this->view_folder.'directory/staff/index_view');
		$this->view->display($this->view_folder.'dashboard/footer_view');
	} */

	function ajax_sgroup_list_dt() {
		if (!isAjaxRequest())
			show_404();

		if (!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged"));

		if (!have_right('have_staff'))
			jsonDTResponse(translate("systmess_error_page_permision"));

		$this->load->model('Company_Staff_Model', 'cstaff');

		$conditions = array();

		$conditions['id_company'] = my_company_id();

		$data['sgroup_list'] = $this->cstaff->get_staff_groups($conditions);
		$count_sgroup_list = $this->cstaff->count_staff_groups($conditions);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $count_sgroup_list,
			"iTotalDisplayRecords" => $count_sgroup_list,
			'aaData' => array()
		);

		if(empty($data['sgroup_list']))
			jsonResponse('', 'success', $output);

		if($this->is_pc)
			$output['aaData'] = $this->_my_company_staff_pc($data);
		else
			$output['aaData'] = $this->_my_company_staff_tablet($data);

		jsonResponse('', 'success', $output);
	}

	private function _my_company_staff_pc($data){
		extract($data);

		foreach ($sgroup_list as $sgroup) {
			$output[] = array(
				'id_dt' => $sgroup['id_sgroup'],
				"title_dt" => $sgroup['name_sgroup'],
				"description_dt" => $sgroup['description_sgroup'],
				"users_count_dt" => '<a href="/company_staff/users/group/'.strForURL($sgroup['name_sgroup']).'-'.$sgroup['id_sgroup'].'" >'.$sgroup['users_count'].'</a>',
				"actions_dt" =>
					'<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit staff group" title="Edit staff group" href="company_staff/popup_forms/edit_sgroup/' . $sgroup['id_sgroup'] . '"></a>
					<a class="ep-icon ep-icon_sheild-ok txt-green fancybox.ajax fancyboxValidateModal" data-title="Staff group rights" title="Staff group rights" href="company_staff/popup_forms/sgroup_rights/' . $sgroup['id_sgroup'] . '"></a>
					<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_sgroup" data-message="Are you sure you want to delete this group?" title="Delete staff group" data-sgroup="' . $sgroup['id_sgroup'] . '"></a>'
			);
		}

		return $output;
	}

	private function _my_company_staff_tablet($data){
		extract($data);

		foreach ($sgroup_list as $sgroup) {
			$output[] = array(
				"actions_dt" =>
					'<div class="btn-group">
						<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Actions
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a class="fancybox.ajax fancyboxValidateModal txt-green" data-title="Staff group rights" title="Staff group rights" href="company_staff/popup_forms/sgroup_rights/' . $sgroup['id_sgroup'] . '"><i class="ep-icon ep-icon_sheild-ok"></i> Rights</a></li>
							<li><a class="fancybox.ajax fancyboxValidateModal" data-title="Edit staff group" title="Edit staff group" href="company_staff/popup_forms/edit_sgroup/' . $sgroup['id_sgroup'] . '"><i class="ep-icon ep-icon_pencil txt-blue"></i> Edit</a></li>
							<li><a class="confirm-dialog txt-red" data-callback="delete_sgroup" data-message="Are you sure you want to delete this group?" title="Delete staff group" data-sgroup="' . $sgroup['id_sgroup'] . '"><i class="ep-icon ep-icon_remove"></i> Delete</a></li>
						</ul>
						<button type="button" class="btn btn-default">on '.orderNumber($sgroup['id_sgroup']).'</button>
					</div>',
				'id_dt' => $sgroup['id_sgroup'],
				"title_dt"   => $sgroup['name_sgroup'],
				"description_dt" => $sgroup['description_sgroup'],
				"users_count_dt" => '<a href="/company_staff/users/group/'.strForURL($sgroup['name_sgroup']).'-'.$sgroup['id_sgroup'].'" >'.$sgroup['users_count'].'</a>',
			);
		}

		return $output;
	}

	function users(){
		if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if (!have_right('have_staff')){
			$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
			headerRedirect(__SITE_URL);
		}

		if (!i_have_company()) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		$this->load_main();

		$conditions['id_company'] = my_company_id();

		$uri = $this->uri->uri_to_assoc();

		$data['sgroup_list']= $this->cstaff->get_staff_groups($conditions);
		$data['cur_group'] = id_from_link($uri['group']);

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'company_staff/users',
			'title'	=> 'Company staff users'
		);
		$data['breadcrumbs'] = $this->breadcrumbs;
		$this->view->assign('title', 'Company staff users');
		$this->view->assign($data);
		$this->view->display($this->view_folder.'dashboard/header_view');
		$this->view->display($this->view_folder.'directory/staff/users/index_view');
		$this->view->display($this->view_folder.'dashboard/footer_view');
	}

	function ajax_susers_list_dt() {
		if (!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged_in"));

		if(!have_right('have_staff'))
			jsonDTResponse(translate("systmess_error_rights_perform_this_action"));

		$this->load->model('User_Model', 'users');
		$this->load->model('Company_Staff_Model', 'cstaff');

		$user_params = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart']),
			'user_type' => "'users_staff'",
			'company' => my_company_id()
		);

		if(!empty($_POST['search']))
			$user_params['keywords'] = cleanInput($_POST['search']);

		if(isset($_POST['group']))
			$user_params['group'] = intval($_POST['group']);

		if(isset($_POST['status']))
			$user_params['status'] = cleanInput($_POST['status']);

		if(isset($_POST['online']))
			$user_params['logged'] = intval($_POST['online']);

		if(isset($_POST['reg_from']))
			$user_params['registration_start_date'] = $_POST['reg_from'];

		if(isset($_POST['reg_to']))
			$user_params['registration_end_date'] = $_POST['reg_to'];

		if(isset($_POST['active_from']))
			$user_params['activity_start_date'] = $_POST['active_from'];

		if(isset($_POST['end_date']))
			$user_params['activity_end_date'] = $_POST['end_date'];

		if($_POST['iSortingCols'] > 0){
			for($i = 0; $i < $_POST['iSortingCols']; $i++){
				switch($_POST['iSortCol_'.$i]){
					case '1':  $user_params['sort_by'][] = 'CONCAT(fname, lname)-' . $_POST['sSortDir_'.$i];break;
					case '2':  $user_params['sort_by'][] = 'email-' . $_POST['sSortDir_'.$i];break;
					case '5':  $user_params['sort_by'][] = 'last_active-' . $_POST['sSortDir_'.$i];break;
					case '4':
					default:
					$user_params['sort_by'][] = 'registration_date-' . $_POST['sSortDir_'.$i];break;
				}
			}
		}

		$user_params['count'] = $this->users->count_users($user_params);
		$data['users'] = $this->users->get_company_staff_users($user_params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" =>  $user_params['count'],
			"iTotalDisplayRecords" => $user_params['count'],
			"aaData" => array()
		);

		if(empty($data['users']))
			jsonResponse('', 'success', $output);

		if($this->is_pc)
			$output['aaData'] = $this->_my_users_staff_pc($data);
		else
			$output['aaData'] = $this->_my_users_staff_tablet($data);

		jsonResponse('', 'success', $output);
	}

	private function _my_users_staff_pc($data){
		extract($data);

		foreach ($users as $user) {
			$online_class =  ($user['logged']) ? "txt-green" : "txt-red" ;
			$online = ($user['logged']) ? "Online" : "Offline" ;


			$user_status_color = 'txt-red';
			$user_status_action = '';
			$user_status_title = 'You cannot change status of this user';

			if($user['status'] == 'active'){
				$user_status_color = 'txt-green';
				$user_status_title = 'set user inactive';
				$user_status_action = 'inactive';
			}elseif($user['status'] == 'inactive'){
				$user_status_color = 'txt-gray-light';
				$user_status_title = 'set user active';
				$user_status_action = 'active';
			}

			$output[] = array(
				"dt_idu" 		=> $user['idu'],
				"dt_fullname" 	=> "<p class='lh-22'><a class='lh-22 ep-icon ep-icon_onoff " . $online_class . " dt_filter' title='Filter just " . $online . "' data-title='OnLine/OffLine' data-value='" . $user['logged'] . "' data-value-text='" . $online . "' data-name='online'></a> ".$user['fname'] . " " . $user['lname']."</p>",
				"dt_email" 		=> $user['email']  ,
				"dt_gr_name" 	=> capitalWord($user['name_sgroup']) . "<a class='dt_filter pull-left ep-icon ep-icon_filter txt-green' title='Group " . $user['name_sgroup']  . "' data-value='" . $user['id_sgroup'] . "' data-name='group' data-title='Group' data-value-text='" . $user['name_sgroup'] . "'></a>",
				"dt_registered" => formatDate($user['registration_date']),
				"dt_activity" 	=> formatDate($user['last_active']),
				"dt_status" 	=> capitalWord($user['status']) . "<a class='dt_filter pull-left ep-icon ep-icon_filter txt-green' title='Filter just " . capitalWord($user['status'])  . "' data-title='Status' data-value-text='" . $user['status'] . "' data-value='" . $user['status'] . "' data-name='status'></a>",
				"dt_actions" 	=> '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit user" title="Edit user" href="company_staff/popup_forms/edit_staff_user/' . $user['idu'] . '"></a>'
								.'<a class="ep-icon ep-icon_user '.$user_status_color.' '.((in_array($user['status'], array('active', 'inactive')))? 'confirm-dialog' : '').'" data-callback="change_user_status" data-message="Are you sure you want to '.$user_status_title.'?" title="'.ucfirst($user_status_title).'" data-change_to="'.$user_status_action.'" data-user="' . $user['idu'] . '"></a>'
								.'<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_user" data-message="Are you sure you want to delete this user?" title="Remove user" data-user="' . $user['idu'] . '"></a>'
			);
		}

		return $output;
	}

	private function _my_users_staff_tablet($data){
		extract($data);

		foreach ($users as $user) {
			$online_class =  ($user['logged']) ? "txt-green" : "txt-red" ;
			$online = ($user['logged']) ? "Online" : "Offline" ;


			$user_status_color = 'txt-red';
			$user_status_action = '';
			$user_status_title = 'You cannot change status of this user';

			if($user['status'] == 'active'){
				$user_status_color = 'txt-green';
				$user_status_title = 'set user inactive';
				$user_status_action = 'inactive';
			}elseif($user['status'] == 'inactive'){
				$user_status_color = 'txt-gray-light';
				$user_status_title = 'set user active';
				$user_status_action = 'active';
			}

			$output[] = array(
				"dt_actions" =>
					'<div class="btn-group">
						<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Actions
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a class="fancybox.ajax fancyboxValidateModal" data-title="Edit user" title="Edit user" href="company_staff/popup_forms/edit_staff_user/' . $user['idu'] . '"><i class="ep-icon ep-icon_pencil txt-blue"></i> Edit</a></li>
							<li><a class="'.((in_array($user['status'], array('active', 'inactive')))? 'confirm-dialog' : '').'  '.$user_status_color.'" data-callback="change_user_status" data-message="Are you sure you want to '.$user_status_title.'?" title="'.ucfirst($user_status_title).'" data-change_to="'.$user_status_action.'" data-user="' . $user['idu'] . '"><i class="ep-icon ep-icon_user"></i> Change status</a></li>
							<li><a class="confirm-dialog txt-red" data-callback="delete_user" data-message="Are you sure you want to delete this user?" title="Remove user" data-user="' . $user['idu'] . '"><i class="ep-icon ep-icon_remove"></i> Delete</a></li>
						</ul>
						<button type="button" class="btn btn-default">on '.orderNumber($user['idu']).'</button>
					</div>',
				"dt_idu" 	=>	$user['idu'],
				"dt_fullname" 	=>
					'<a class="btn btn-default btn-icon dt_filter" title="Filter just' . $online . '" data-title="OnLine/OffLine" data-value="' . $user['logged'] . '" data-value-text="' . $online . '" data-name="online"><i class=" ep-icon ep-icon_filter txt-green"></i> Filter by online</a>
					<p class="lh-22">
						<i class="fs-16 ep-icon ep-icon_onoff ' . $online_class . '"></i> '
						.$user['fname'] . " " . $user['lname']
					.'</p>',
				"dt_email" => $user['email']  ,
				"dt_gr_name" 	=>
					"<div><a class='dt_filter btn btn-default btn-icon' title='Group " . $user['name_sgroup']  . "' data-value='" . $user['id_sgroup'] . "' data-name='group' data-title='Group' data-value-text='" . $user['name_sgroup'] . "'><i class='ep-icon ep-icon_filter txt-green'></i> Filter by group</a></div>"
					.capitalWord($user['name_sgroup']),
				"dt_registered" => formatDate($user['registration_date']),
				"dt_activity" 	=> formatDate($user['last_active']),
				"dt_status" =>
					"<div><a class='dt_filter btn btn-default btn-icon' title='Filter just " . capitalWord($user['status'])  . "' data-title='Status' data-value-text='" . $user['status'] . "' data-value='" . $user['status'] . "' data-name='status'><i class='ep-icon ep-icon_filter txt-green'></i> Filter by status</a></div>"
					.capitalWord($user['status'])
			);
		}

		return $output;
	}

	function popup_forms(){
		if(!isAjaxRequest()){
			headerRedirect();
		}

		if(!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		if(!have_right('have_staff')){
			messageInModal(translate("systmess_error_rights_perform_this_action"));
		}

		$this->load_main();
		$op = $this->uri->segment(3);
		$id = (int)$this->uri->segment(4);
		switch($op){
			case 'add_sgroup':
				if (!i_have_company())
					messageInModal('Error: To add staff user, you must have a company.');

				$this->view->display($this->view_folder.'directory/staff/add_staff_group_form_view');
			break;
			case 'edit_sgroup':
				if(!$id)
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				$data['sgroup'] = $this->cstaff->get_staff_group($id, my_company_id());
				$this->view->display($this->view_folder.'directory/staff/edit_staff_group_form_view', $data);
			break;
			case 'sgroup_rights':
				if(!$id)
					messageInModal(translate("systmess_error_rights_perform_this_action"));

				$data['sgroup'] = $this->cstaff->get_staff_group($id, my_company_id());
				$sgroup_rights = $this->cstaff->get_staff_group_rights($id);
				$data['sgroup_rights'] = array();

				foreach($sgroup_rights as $sgright){
					$data['sgroup_rights'][] = $sgright['id_right'];
				}
				$rights = $this->ugroup->getRightsByGroupForStaff($this->session->group);
				$data['rights'] = array();

				// foreach($rights as $right){
				// 	if(!isset($data['rights'][$right['r_cat']]))
				// 		$data['rights'][$right['r_cat']]['name'] = $right['name_module'];

				// 	$data['rights'][$right['r_cat']]['crights'][] = $right;
				// }
				$this->view->display($this->view_folder.'directory/staff/staff_group_rights_view', $data);
			break;
			case 'add_staff_user':
				$data['sgroup_list'] = $this->cstaff->get_staff_groups(array('id_company'=>my_company_id()));
				if(empty($data['sgroup_list']))
					messageInModal('Error: Please add staff group first.');
				$data['branches'] = $this->company->get_companies_main_info(array('parent'=>my_company_id(), 'type_company'=>'branch'));
				$this->view->display($this->view_folder.'directory/staff/users/add_staff_user_form_view', $data);
			break;
			case 'edit_staff_user':
				$data['staff_user'] = $this->user->get_staff_user($id, my_company_id());
				if(empty($data['staff_user']))
					messageInModal(translate("systmess_error_user_does_not_exist"));
				$data['sgroup_list'] = $this->cstaff->get_staff_groups(array('id_company'=>my_company_id()));
				if(empty($data['sgroup_list']))
					messageInModal('Error: Please add staff group first.');
				$data['branches'] = $this->company->get_companies_main_info(array('parent'=>my_company_id(), 'type_company'=>'branch'));
				$data['user_branches'] = $this->company->get_user_companies_rel(array('id_user'=>$id, 'type_company'=>'branch'));
				$data['user_branches'] = arrayByKey($data['user_branches'], 'id_company');
				$this->view->display($this->view_folder.'directory/staff/users/edit_staff_user_form_view', $data);
			break;
		}
	}

	function ajax_operation(){
		if(!isAjaxRequest()){
			headerRedirect();
		}

		if(!$this->session->loggedIn){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

		if(!have_right('have_staff')){
			jsonResponse(translate("systmess_error_rights_perform_this_action"));
		}

		$this->load_main();
		$op = $this->uri->segment(3);
		switch($op){
			case 'add_staff_group':
				if (!i_have_company()) {
					jsonResponse('Error: To add staff user, you must have a company.');
				}
				is_allowed("freq_allowed_staff_operations");

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Staff group name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '', 'max_len[250]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}
				$insertColumn = array(
					'id_company'     => my_company_id(),
					'name_sgroup'	 => cleanInput($_POST['title']),
					'description_sgroup'	 => cleanInput($_POST['description'])
				);
				if($this->cstaff->set_company_sgroup($insertColumn)){
					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
						id_session() => array('company_staff_group' => 1)
					));

					jsonResponse('The group was added successfully.','success');
				}else
					jsonResponse('Error: You cannot add groups now. Please try again later.');
			break;
			case 'edit_staff_group':
				is_allowed("freq_allowed_staff_operations");

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Staff group name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'sgroup',
						'label' => 'Staff group info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}
				$id_sgroup = intVal($_POST['sgroup']);
				$updateColumn = array(
					'name_sgroup'	 => cleanInput($_POST['title']),
					'description_sgroup'	 => cleanInput($_POST['description'])
				);
				if($this->cstaff->update_company_sgroup($id_sgroup, my_company_id(), $updateColumn))
					jsonResponse('The group has been successfully updated.','success');
				else
					jsonResponse('Error: You cannot update groups now. Please try again later.');
			break;
			case 'delete_staff_group':

				$id_group = intVal($_POST['sgroup']);
				$count = 1;

				if(!$id_group)
					jsonResponse('Error: Please select at least one group.');

				if(!$this->cstaff->exist_staff_group($id_group, my_company_id()))
					jsonResponse('Error: This group does not exist.');

				if($this->cstaff->exist_sgroup_rights($id_group))
					jsonResponse('Error: This group contains certain rights and cannot be deleted. Please cancel all rights before deleting it.');

				if($this->cstaff->exist_in_sgroup_users($id_group))
					jsonResponse('Error: This group contains users and cannot be deleted. Please remove all users from this group before deleting it.');

				if($this->cstaff->delete_company_sgroup($id_group, my_company_id())){
					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
						id_session() => array('company_staff_group' => -$count)
					));

					jsonResponse('Staff group was deleted.', 'success');
				} else
					jsonResponse('Error: Staff group(s) was not deleted. Please try again later.');
			break;
			case 'sgroup_right_op':
				$right = intVal($_POST['right']);
				$group = intVal($_POST['group']);
				if(!$this->cstaff->exist_staff_group($group, my_company_id()))
					jsonResponse('Error: This group does not exist.');
				$right_info = $this->ugroup->getSimpleRightForStaff($right);
				if(empty($right_info))
					jsonResponse('Error: This right does not exist.');
				if(!in_array($right_info['r_alias'], $this->session->rights))
					jsonResponse('Error: This right does not exist.');

				if($this->cstaff->exist_relation($group, $right)){
					$this->cstaff->delete_relation_gr($group, $right);
					jsonResponse('The rights of the staff group were removed.', 'success', array('action'=>'remove'));
				} else{
					$this->cstaff->set_relation_gr($group, $right);
					jsonResponse('The rights of the staff group were added successfully.', 'success', array('action'=>'set'));
				}
			break;
			case 'add_staff_user':
				is_allowed("freq_allowed_staff_operations");

				$validator_rules = array(
					array(
						'field' => 'fname',
						'label' => 'First Name',
						'rules' => array('required' => '')
					),array(
						'field' => 'lname',
						'label' => 'Last Name',
						'rules' => array('required' => '')
					),array(
						'field' => 'group',
						'label' => 'Group',
						'rules' => array('required' => '', 'integer'=>'')
					),array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
					),
					array(
						'field' => 'pwd',
						'label' => 'Password',
						'rules' => array('required' => '', 'valid_password' => '')
					),
					array(
						'field' => 'pwd_confirm',
						'label' => 'Retype password',
						'rules' => array('required' => '','matches[pwd]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				if($this->user->exist_user_by_email(cleanInput($_POST['email'], true)))
					jsonResponse('Error: The email already exists in the database. Please choose another one!');

				$data = array(
					'fname' => cleanInput($_POST['fname']),
					'lname' => cleanInput($_POST['lname']),
					'email' => cleanInput($_POST['email'], true),
					'user_group' => 25,
					'user_ip' => getVisitorIP(),
					'registration_date' => date('Y-m-d H:i:s'),
					'activation_code' => get_sha1_token(cleanInput($_POST['email'], true)),
					'status' => 'active',
					'user_type' => 'users_staff',
					'id_company' => my_company_id()
				);
				$id_user = $this->user->setUserMain($data);
				if($id_user){
					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
						id_session() => array('company_staff_users' => 1)
					));

					$user_companies = array();
					$user_companies[] = array(
						'id_company'=>my_company_id(),
						'id_user'=>$id_user,
						'company_type'=>'company'
					);
					if(!empty($_POST['branch']))
						$branches = $this->company->get_companies_main_info(array('parent'=>my_company_id(), 'companies_list'=>implode(',', $_POST['branch'])));
					if(!empty($branches)){
						foreach($branches as $branch){
							$user_companies[] = array(
								'id_company'=>$branch['id_company'],
								'id_user'=>$id_user,
								'company_type'=>'branch'
							);
						}
					}
					$this->company->set_company_user_rel($user_companies);
					$this->cstaff->set_user_group($id_user, intVal($_POST['group']));

					//region block user content
					$seller_info = model('user')->getSimpleUser(privileged_user_id());
					if(in_array($seller_info['status'], array('blocked', 'restricted'))){
						model('blocking')->change_blocked_users_staffs(array(
							'users_list' => array(privileged_user_id())
						), array('blocked' => 2));
					}
					//endregion block user content

					jsonResponse('New user was added successfully' , 'success');
				}else{
					jsonResponse('Error: You cannot add new users now. Please try again later');
				}
			break;
			case 'edit_staff_user':
				is_allowed("freq_allowed_staff_operations");

				$validator_rules = array(
					array(
						'field' => 'fname',
						'label' => 'First Name',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'lname',
						'label' => 'Last Name',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'group',
						'label' => 'Group',
						'rules' => array('required' => '', 'integer'=>'')
					),
					array(
						'field' => 'user',
						'label' => 'User info',
						'rules' => array('required' => '','integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$data = array(
					'fname' => cleanInput($_POST['fname']),
					'lname' => cleanInput($_POST['lname'])
				);
				$id_user = (int)$_POST['user'];
				$id_company = my_company_id();
				if(!$this->user->existStaffUser($id_user, $id_company)){
					jsonResponse(translate("systmess_error_user_does_not_exist"));
				}
				if($this->user->updateUserMain($id_user, $data)){
					$user_companies = array();
					$user_companies[] = array(
						'id_company'=>$id_company,
						'id_user'=>$id_user,
						'company_type'=>'company'
					);
					if(!empty($_POST['branch']))
						$branches = $this->company->get_companies_main_info(array('parent'=>$id_company, 'companies_list'=>implode(',', $_POST['branch'])));

					if(!empty($branches)){
						foreach($branches as $branch){
							$user_companies[] = array(
								'id_company'=>$branch['id_company'],
								'id_user'=>$id_user,
								'company_type'=>'branch'
							);
						}
					}
					$this->company->clear_company_user_rel($id_user);
					$this->company->set_company_user_rel($user_companies);
					$this->cstaff->set_user_group($id_user, intVal($_POST['group']));
					jsonResponse('User data was changed successfully' , 'success');
				}else{
					jsonResponse(translate("systmess_error_user_does_not_exist"));
				}
			break;
			case 'delete_staff_user':
				$id_user = intVal($_POST['user']);

				if(!$id_user)
					jsonResponse('Error: Please select at least one user.');

				$id_company = my_company_id();
				if(!$this->user->existStaffUser($id_user, $id_company))
					jsonResponse(translate("systmess_error_user_does_not_exist"));

				if($this->user->delete_staff_user($id_user)){
					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic(array(
						id_session() => array('company_staff_users' => -1)
					));

					$this->cstaff->delete_company_suser_group_relation($id_user);
					$this->company->clear_company_user_rel($id_user);
					jsonResponse('The user was deleted.', 'success');
				} else
					jsonResponse('Error: The user has not been deleted. Please try again later.');
			break;
			case 'change_staff_status':
				$id_user = intVal($_POST['user']);
				$change_to = cleanInput($_POST['change_to']);

				if(!$id_user)
					jsonResponse('Error: Please select at least one user.');

				$id_company = my_company_id();
				if(!$this->user->existStaffUser($id_user, $id_company))
					jsonResponse(translate("systmess_error_user_does_not_exist"));

				$user_info = $this->user->getSimpleUser($id_user, 'users.status');
				if(!in_array($user_info['status'], array('active', 'inactive')))
					jsonResponse('Error: You cannot change status of this user.');

				if(!in_array($change_to, array('active', 'inactive')))
					jsonResponse('Error: Please select at least one user.');

				if($this->user->updateUserMain($id_user, array('status' => $change_to)))
					jsonResponse('User was updated successfully.' , 'success');
				jsonResponse('Error: Cannot update user now. Please try again later.' , 'success');
			break;
		}
	}
}

?>

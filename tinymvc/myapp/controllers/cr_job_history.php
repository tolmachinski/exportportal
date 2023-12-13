<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_job_history_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();
	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('User_Model', 'user');
		$this->load->model('Cr_Job_History_Model', 'job_history');
	}

    /** deleted on 2021.07.05 */
	/* function my() {
		if (!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if (!have_right('manage_job_history')) {
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"), 'errors');
			headerRedirect(__SITE_URL);
		}

		$this->breadcrumbs[] = array(
			'link' => __SITE_URL . 'cr_job_history/my',
			'title' => 'Job history'
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
		$this->view->assign('title', 'Job history');

		$this->view->assign($data);
		$this->view->display('dashboard/header_view');
		$this->view->display('new/cr/user/job_history/index_view');
		$this->view->display('dashboard/footer_view');
	} */

	function ajax_my_jobs() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged"));

		if (!have_right('manage_job_history'))
			jsonDTResponse(translate("systmess_error_rights_perform_this_action"));

		$this->_load_main();

		$id_user = privileged_user_id();
		$params = array('id_user' => $id_user, 'per_p' => $_POST['iDisplayLength'], 'start' => $_POST['iDisplayStart']);

		if ($_POST['iSortingCols'] > 0) {
			for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
				switch ($_POST['iSortCol_' . $i]) {
					case '0': $params['sort_by'][] = 'id_job-' . $_POST['sSortDir_' . $i]; break;
					case '1': $params['sort_by'][] = 'job_place-' . $_POST['sSortDir_' . $i]; break;
					case '3': $params['sort_by'][] = 'date_from-' . $_POST['sSortDir_' . $i]; break;
					case '4': $params['sort_by'][] = 'date_to-' . $_POST['sSortDir_' . $i]; break;
				}
			}
		}

		$data['job_histoy'] = $this->job_history->get_jobs_histoy($params);
		$job_histoy_count = $this->job_history->count_jobs_histoy($params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $job_histoy_count,
			"iTotalDisplayRecords" => $job_histoy_count,
			'aaData' => array()
		);

		if(empty($data['job_histoy']))
			jsonResponse('', 'success', $output);

		$output['aaData'] = array();

		foreach ($data['job_histoy'] as $job_item) {

			$skills_info = '';

			if(!empty($job_item['job_skills'])){
				$job_item['job_skills'] = json_decode( $job_item['job_skills'], true);
				$skills_info .= '<ul>';
				foreach($job_item['job_skills'] as $skill_key => $skill_item){
					$skills_info .= '<li>- '.$skill_item.'</li>';
				}
				$skills_info .= '</ul>';
			}

			$date_to = strtotime($job_item['date_to']);

			$output['aaData'][] = array(
				'dt_job' => $job_item['id_job'],
				'dt_job_plase' => $job_item['job_place'],
				'dt_job_position' => $job_item['job_position'],
				'dt_date_from' => formatDate($job_item['date_from'],'M Y'),
				'dt_date_to' => ($date_to)?formatDate($job_item['date_to'],'M Y'):'Present',
				'dt_time_ago' => timeAgo($job_item['date_from'], 'Y,m,d', $date_to),
				'dt_skills' => $skills_info,
				'dt_actions' => '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" data-title="Edit job" href="cr_job_history/popup_forms/edit_job/'.$job_item['id_job'].'"></a>
								<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_job" data-job="'.$job_item['id_job'].'" data-message="Are you sure want to delete this job?"></a>'
			);
		}

		jsonResponse('', 'success', $output);
	}

	function popup_forms() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		if (!have_right('manage_job_history'))
			messageInModal(translate("systmess_error_rights_perform_this_action"));

		$op = $this->uri->segment(3);

		switch ($op) {
			case 'add_job':
				$this->view->display('new/cr/user/job_history/add_job_form_view');
			break;
			case 'edit_job':
				$id_job = intval($this->uri->segment(4));
				$this->load->model('Cr_Job_History_Model', 'job_history');

				$data['history_item'] = $this->job_history->get_job_histoy($id_job);
				$this->view->display('new/cr/user/job_history/add_job_form_view', $data);
			break;
		}
	}

	function ajax_job_operation() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		if (!have_right('manage_job_history'))
			jsonResponse(translate("systmess_error_rights_perform_this_action"));

		$this->_load_main();

		$type = $this->uri->segment(3);
		switch ($type) {
			case 'delete_job':
				$id_job = intval($_POST['job']);
				$id_user = id_session();

				if (!$this->job_history->my_job($id_job, $id_user))
					jsonResponse('Error: This job is not your.');

				if($this->job_history->delete_job($id_job))
					jsonResponse('Job has been successfully deleted.', 'success');
			break;
			case 'add_job':
				$validator_rules = array(
					array(
						'field' => 'place',
						'label' => 'Place',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'date_from',
						'label' => 'Date From',
						'rules' => array('required' => '', 'valid_date' => '')
					),
					array(
						'field' => 'position',
						'label' => 'Item',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[250]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_user = id_session();

				$data_job = array(
					'id_user' => $id_user,
					'job_place' => cleanInput($_POST['place']),
					'date_from' => formatDate($_POST['date_from'], 'Y-m-d h:i:s'),
					'job_position' => cleanInput($_POST['position'])
				);

				if(!empty($_POST['date_to']))
					$data_job['date_to'] = formatDate($_POST['date_to'], 'Y-m-d h:i:s');

				$skills = $_POST['skills'];
				if(!empty($skills)){
					$skills_data = array();

					foreach($skills as $skill_item){
						if(!empty($skill_item))
							$skills_data[] = cleanInput($skill_item);
					}

					if(!empty($skills_data))
						$data_job['job_skills'] = json_encode($skills_data);
				}

				if($this->job_history->insert_job_histoy($data_job)) {
					jsonResponse('Job has been successfully saved.', 'success');
				} else {
					jsonResponse('Job has not been saved.');
				}
			break;
			case 'edit_job':
				$validator_rules = array(
					array(
						'field' => 'place',
						'label' => 'Place',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'date_from',
						'label' => 'Date From',
						'rules' => array('required' => '', 'valid_date' => '')
					),
					array(
						'field' => 'date_to',
						'label' => 'Date to',
						'rules' => array('required' => '', 'valid_date' => '')
					),
					array(
						'field' => 'position',
						'label' => 'Item',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[250]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_user = id_session();
				$id_job = intVal($_POST['id_job']);


				$data_job = array(
					'job_place' => cleanInput($_POST['place']),
					'date_from' => formatDate($_POST['date_from'], 'Y-m-d h:i:s'),
					'date_to' => formatDate($_POST['date_to'], 'Y-m-d h:i:s'),
					'job_position' => cleanInput($_POST['position'])
				);

				$skills = $_POST['skills'];
				if(!empty($skills)){
					$skills_data = array();

					foreach($skills as $skill_item){
						if(!empty($skill_item))
							$skills_data[] = cleanInput($skill_item);
					}

					if(!empty($skills_data))
						$data_job['job_skills'] = json_encode($skills_data);
				}

				if($this->job_history->update_job_histoy($id_job, $data_job)) {
					jsonResponse('Job has been successfully saved.', 'success');
				} else {
					jsonResponse('Job has not been saved.');
				}
			break;
		}
	}
}

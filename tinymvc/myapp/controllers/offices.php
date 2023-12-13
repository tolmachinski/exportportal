<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Offices_Controller extends TinyMVC_Controller {

	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Offices_Model', 'offices');
		$this->load->model('Country_Model', 'country');
	}

	function index() {
		$this->administration();
	}

	function administration() {
		checkAdmin('manage_content');

		$this->_load_main();
		$data['offices_list'] = $this->offices->get_offices();

		$this->view->assign('title', 'Offices');
		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/offices/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_offices_operation(){
		if (!isAjaxRequest()){
			headerRedirect();
		}

		$this->_load_main();
		$op = $this->uri->segment(3);

		switch($op){
			case 'update_office':
				checkPermisionAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'address',
						'label' => 'Address',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'tel',
						'label' => 'Telephone',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'fax',
						'label' => 'Fax',
						'rules' => array('min_len[12]' => '', 'max_len[25]' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'info',
						'label' => 'Information',
						'rules' => array('required' => '', 'html_max_len[255]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->library('Cleanhtml', 'clean');

				$update = array(
					'name_office' => cleanInput($_POST['name']),
					'address_office' => cleanInput($_POST['address']),
					'phone_office' => cleanInput($_POST['tel']),
					'fax_office' => cleanInput($_POST['fax']),
					'email_office' => cleanInput($_POST['email'], true),
					'text_office' => $this->clean->sanitizeUserInput($_POST['info']),
					'id_country' => intval($_POST['country']),
					'latitude' => cleanInput($_POST['lat']),
					'longitude' => cleanInput($_POST['long']),
				);

				$id_office = intval($_POST['id_office']);

				if(!$this->offices->exist_office($id_office))
					jsonResponse('Error: This office doesn\'t exist.');

				if($this->offices->update_office($id_office, $update)){
					$country_info = $this->country->get_country($update['id_country']);
					$update['country_name'] = $country_info['country'];
					$update['id_office'] = $id_office;
					jsonResponse('The office was successfully updated.', 'success', $update);
				}else
					jsonResponse('Error: You could not updated office now. Please try again late');
			break;
			case 'create_office':
				checkPermisionAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'address',
						'label' => 'Address',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'tel',
						'label' => 'Telephone',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'fax',
						'label' => 'Fax',
						'rules' => array('min_len[12]' => '', 'max_len[25]' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'info',
						'label' => 'Information',
						'rules' => array('required' => '', 'html_max_len[255]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->library('Cleanhtml', 'clean');

				$insert = array(
					'name_office' => cleanInput($_POST['name']),
					'address_office' => cleanInput($_POST['address']),
					'phone_office' => cleanInput($_POST['tel']),
					'fax_office' => cleanInput($_POST['fax']),
					'email_office' => cleanInput($_POST['email'], true),
					'text_office' => $this->clean->sanitizeUserInput($_POST['info']),
					'id_country' => intval($_POST['country']),
					'latitude' => cleanInput($_POST['lat']),
					'longitude' => cleanInput($_POST['long']),
				);

				$id_office = $this->offices->set_office($insert);

				if($id_office){
					$country_info = $this->country->get_country($insert['id_country']);
					$insert['country_name'] = $country_info['country'];
					$insert['id_office'] = $id_office;
					jsonResponse('The office has been successfully inserted.','success',$insert);
				}else
					jsonResponse('Error: You could not inserted office now. Please try again late.');
			break;
			case 'delete_office':
				checkPermisionAjax('manage_content');

				$id_office = intval($_POST['office']);

				if(!$this->offices->exist_office($id_office))
					jsonResponse('Error: This office doesn\'t exist.');

				if($this->offices->delete_office($id_office))
					jsonResponse('The office was successfully deleted.','success');
				else
					jsonResponse('Error: You could not delete this office now. Please try again late.');
			break;
//			case 'get_country_list':
//				$id_continent = intval($_POST['id']);
//				$data['countries'] = $this->offices->get_office_location(array('id_continent' => $id_continent));
//
//				if(!empty($data['countries']))
//					jsonResponse('','success', array('html' => $this->view->fetch('offices/offices_countries_list_view', $data)));
//				else
//					jsonResponse('','error', array('html' => '<li class="w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>We don\'t have offices in this continent.</span></div></li>'));
//			break;
		}
	}

	function popup_forms() {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			messageInModal(translate('systmess_error_should_be_logged_in'));
		}

		$op = $this->uri->segment(3);

		switch ($op) {
			case 'contact_office':
				$this->load->model('Offices_Model', 'offices');
				$data['id_office'] = (int) $this->uri->segment(4);

				if (!$this->offices->exist_office($data['id_office'])) {
					messageInModal(translate('systmess_error_invalid_data'), 'errors');
				}

				$this->view->assign($data);
				$this->view->display('new/offices/popup_contact_view');
			break;
			case 'add_office':
				if (!have_right('moderate_content'))
					messageInModal('Error: You have no rights to manage offices.');
				$this->load->model('Country_Model', 'country');
				$data['countries'] = $this->country->get_countries();
				$this->view->display('admin/offices/form_view', $data);
			break;
			case 'update_office':
				if (!have_right('moderate_content'))
					messageInModal('Error: You have no rights to manage offices.');

				$id_office = intval($this->uri->segment(4));
				$this->load->model('Offices_Model', 'offices');

				if(!$this->offices->exist_office($id_office))
					jsonResponse('ERROR: This office does not exist.');

				$this->load->model('Country_Model', 'country');

				$data['office_info'] = $this->offices->get_office($id_office);
				$data['countries'] = $this->country->get_countries();

				$this->view->display('admin/offices/form_view', $data);
			break;
		}
	}
}

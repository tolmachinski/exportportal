<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seo_static_pages_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();

	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('User_Model', 'user');
		$this->load->model('Seo_static_pages_Model', 'seo_static');
	}

	function administration(){
		if(!logged_in()){
			$this->session->setMessages(translate("systmess_error_should_be_logged"),'errors');
			headerRedirect(__SITE_URL.'login');
		}

		if(!have_right('manage_content')){
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"),'errors');
			headerRedirect(__SITE_URL);
		}

		$this->_load_main();
		$this->view->assign($data);
		$this->view->assign('title', 'Seo for static pages');
		$this->view->display('admin/header_view');
        $this->view->display('admin/seo_static_pages/index_view');
        $this->view->display('admin/footer_view');
	}

	function ajax_dt_administration(){
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->_load_main();

		$params = array('per_p' => $_POST['iDisplayLength'], 'start' => $_POST['iDisplayStart']);

		if($_POST['iSortingCols'] > 0){
			for($i = 0; $i < $_POST['iSortingCols']; $i++){
				switch($_POST["mDataProp_" . intVal($_POST['iSortCol_'.$i])]){
					case 'dt_id':  $params['sort_by'][] = 'id-'.$_POST['sSortDir_'.$i];break;
					case 'dt_short_key':  $params['sort_by'][] = 'short_key-'.$_POST['sSortDir_'.$i];break;
					case 'dt_meta_keys':  $params['sort_by'][] = 'meta_keys-'.$_POST['sSortDir_'.$i];break;
					case 'dt_meta_description':  $params['sort_by'][] = 'meta_description-'.$_POST['sSortDir_'.$i];break;
					case 'dt_meta_title':  $params['sort_by'][] = 'meta_title-'.$_POST['sSortDir_'.$i];break;
				}
			}
		}

		$seo_statics = $this->seo_static->get_seo($params);
		$seo_statics_count = $this->seo_static->counter_by_conditions($params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $seo_statics,
			"iTotalDisplayRecords" => $seo_statics_count,
			'aaData' => array()
		);

		if(empty($seo_statics))
			jsonResponse('', 'success', $output);

		foreach($seo_statics as $item) {

			$output['aaData'][] = array(
				'dt_id' => $item['id'],
				'dt_short_key' => $item['short_key'],
				'dt_meta_title' => $item['meta_title'],
				'dt_meta_description' => $item['meta_description'],
				'dt_meta_keys' => $item['meta_keys'],
				'dt_actions' =>
					'<a href="'.__SITE_URL.'seo_static_pages/popup_seo/edit_seo/'.$item['id'].'" class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit SEO" data-title="Edit SEO"></a>'
					.'<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_seo" data-seo="'.$item['id'].'" title="Remove SEO" data-message="Are you sure you want to delete this SEO?" href="#" ></a>',
			);
		}

		jsonResponse('', 'success', $output);
	}

		public function ajax_seo_operation(){
		if(!isAjaxRequest())
			 headerRedirect();

		if(!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->_load_main();
		$id_user = $this->session->id;
		$op = $this->uri->segment(3);

		switch($op){
			case 'save_seo':
				if(!have_right('moderate_content'))
					jsonResponse('Error: You don\'t have rights.');

				$validator_rules = array(
					array(
	                    'field' => 'short_key',
	                    'label' => 'Short key',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_title',
	                    'label' => 'Meta title',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_description',
	                    'label' => 'Meta description',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_keys',
	                    'label' => 'Meta keys',
	                    'rules' => array('required' => '')
	                ),

	            );
	            $this->validator->set_rules($validator_rules);
	            if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

	        	$insert = array(
	                'short_key' => cleanInput($_POST['short_key']),
	                'meta_title' => cleanInput($_POST['meta_title']),
	                'meta_description' => cleanInput($_POST['meta_description']),
	                'meta_keys' => cleanInput($_POST['meta_keys']),
	        	);

				$id_seo = $this->seo_static->set_seo($insert);

	            if($id_seo)
					jsonResponse('Seo was saved successfully.','success');
				else
					jsonResponse('Error: you cannot add seo now. Please try again later.');
			break;
			case 'edit_seo':
				if(!have_right('moderate_content'))
					jsonResponse('Error: You don\'t have rights.');

				$id_seo = intVal($_POST['seo']);
				$validator_rules = array(
					array(
	                    'field' => 'short_key',
	                    'label' => 'Short key',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_title',
	                    'label' => 'Meta title',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_description',
	                    'label' => 'Meta description',
	                    'rules' => array('required' => '')
	                ),
					array(
	                    'field' => 'meta_keys',
	                    'label' => 'Meta keys',
	                    'rules' => array('required' => '')
	                ),
	            );
	            $this->validator->set_rules($validator_rules);
	            if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

	        	$update = array(
	                'short_key' => cleanInput($_POST['short_key']),
	                'meta_title' => cleanInput($_POST['meta_title']),
	                'meta_description' => cleanInput($_POST['meta_description']),
	                'meta_keys' => cleanInput($_POST['meta_keys']),
	        	);

	            if($this->seo_static->update_seo($id_seo, $update))
					jsonResponse('Seo was changed successfully.','success');
				else
					jsonResponse('Error: you cannot change seo now. Please try again later.');
			break;
			case 'remove_seo':
				if(!have_right('moderate_content'))
					jsonResponse('Error: You don\'t have rights.');

				$id_seo = intVal($_POST['seo']);
				$seo_info = $this->seo_static->get_seo_one($id_seo);
				if(empty($seo_info))
					jsonResponse('Seo does not exist.');

				if($this->seo_static->delete_seo($id_seo))
					jsonResponse('Seo was removed successfully.','success');
				else
					jsonResponse('Error: you cannot remove seo now. Please try again later.');
			break;
		}
	}

	public function popup_seo(){
		if(!isAjaxRequest())
		    headerRedirect();

		if(!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

		$this->_load_main();
		$data['errors'] = array();
		$id_user = $this->session->id;

		$op = $this->uri->segment(3);
		switch($op){
			case 'add_seo':
				$this->view->display('admin/seo_static_pages/add_form_view');
			break;
			case 'edit_seo':
				$id_seo = $this->uri->segment(4);
				$data['seo_info'] = $this->seo_static->get_seo_one($id_seo);
				$this->view->display('admin/seo_static_pages/add_form_view', $data);
			break;
		}
	}
}
?>

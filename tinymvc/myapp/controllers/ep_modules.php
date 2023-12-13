<?php

/**
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
 */
class Ep_Modules_Controller extends TinyMVC_Controller {

	public function administration() {
        checkAdmin('manage_content');

		$data = array(
			'title' => 'EP Modules'
		);

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/ep_modules/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_ep_modules_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

		checkAdmin('manage_content');

        $this->load->model('Ep_Modules_Model', 'modules');

        $params = array('per_p' => (int) $_POST['iDisplayLength'], 'start' => (int) $_POST['iDisplayStart']);

        $params['sort_by'] = flat_dt_ordering($_POST, array(
            'dt_id' => 'id_module',
            'dt_name' => 'name_module',
            'dt_title' => 'title_module',
            'dt_position' => 'position_module',
            'dt_text' => 'description_module',
            'dt_group' => 'group_module',
        ));

        $params = array_merge($params,
            dtConditions($_POST, [
                ['as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput']
            ])
        );

        // print_r($params);
        $ep_modules = $this->modules->get_ep_modules($params);
        $ep_modules_count = $this->modules->get_ep_modules_counter($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $ep_modules_count,
            "iTotalDisplayRecords" => $ep_modules_count,
			'aaData' => array()
        );

		foreach ($ep_modules as $key=>$module) {
			$positions_btn = '<a href="" class="ep-icon ep-icon_arrows-up confirm-dialog" data-message="Are you sure you want to change module order posotion?" data-module="'.$module['id_module'].'" data-callback="change_order" data-action="up"></a>';
			$positions_btn .= '<a href="" class="ep-icon ep-icon_arrows-down confirm-dialog" data-message="Are you sure you want to change module order posotion?" data-module="'.$module['id_module'].'" data-callback="change_order" data-action="down"></a>';
			$output['aaData'][] = array(
				'dt_id' => $module['id_module'],
				'dt_name' => $module['name_module'],
				'dt_title' => $module['title_module'],
				'dt_group' => $module['group_module'],
				'dt_text' => $module['description_module'],
				'dt_position' => $positions_btn,
				'dt_actions' =>'<a href="ep_modules/popup_forms/edit_ep_module/'. $module['id_module'] . '" class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil fs-16" data-title="Edit EP module" title="Edit this EP module"></a>'
						. '<a class="ep-icon ep-icon_remove txt-red fs-16 confirm-dialog" data-callback="remove_ep_module" data-id="' . $module['id_module'] . '" title="Remove this EP module" data-message="Are you sure you want to delete this EP module?" href="#" ></a>',
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms() {
        if (!isAjaxRequest())
            headerRedirect();

        $id_user = $this->session->id;

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_ep_module':
				if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"));

				checkAdminAjaxModal('manage_content');

				$this->view->display('admin/ep_modules/modal_form_view');
                break;
            case 'edit_ep_module':
				if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"), 'errors');

				checkAdminAjaxModal('manage_content');

				$this->load->model('Ep_Modules_Model', 'modules');

                $id_ep_module = intVal($this->uri->segment(4));
				$data = array('module' => $this->modules->get_ep_module($id_ep_module));

				$this->view->display('admin/ep_modules/modal_form_view', $data);
                break;
        }
    }

    public function ajax_ep_modules_operations() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

		checkAdminAjax('manage_content');
		$this->load->model('Ep_Modules_Model', 'modules');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'remove_ep_module':
                $id_module = intVal($_POST['module']);

                if ($this->modules->delete_ep_module($id_module))
                    jsonResponse('The EP module has been successfully removed.', 'success');

				jsonResponse('Error: You cannot remove this EP module now. Please try again later.');
			break;
            case 'change_order':
				$id_module = intVal($_POST['module']);
				$module = $this->modules->get_ep_module($id_module);
				if(empty($module)){
					jsonResponse('Error: This module does not exist.');
				}

				$action = $this->uri->segment(4);
				switch($action){
					case 'up' :
						$sibling_module = $this->modules->get_sibling_up_module($module['position_module']);
						if(!empty($sibling_module)){
							$this->modules->update_module_position($id_module, $sibling_module['position_module'], $sibling_module['id_module'], $module['position_module']);
						}
					break;
					case 'down' :
						$sibling_module = $this->modules->get_sibling_down_module($module['position_module']);
						if(!empty($sibling_module)){
							$this->modules->update_module_position($id_module, $sibling_module['position_module'], $sibling_module['id_module'], $module['position_module']);
						}
					break;
				}

				jsonResponse('The EP module has been successfully updated.', 'success');
			break;
            case 'edit_ep_module':
				checkAdminAjax('manage_content');
                $validator_rules = array(
                    array(
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'module',
                        'label' => 'Module',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'group',
                        'label' => 'Group',
                        'rules' => array('required' => '', 'max_len[1]' => '')
                    )
                );
				$id_module = intVal($_POST['module']);

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $update = array(
                    'name_module' => cleanInput($_POST['name']),
                    'title_module' => cleanInput($_POST['title']),
                    'group_module' => cleanInput($_POST['group']),
                    'description_module' => $_POST['text']
                );

				$this->load->model('Ep_Modules_Model', 'modules');
				if ($this->modules->update_ep_module($id_module, $update))
                    jsonResponse('The EP module has been successfully changed.', 'success');

                jsonResponse('Error: You cannot change this EP module now. Please try again later.');
			break;
            case 'add_ep_module':
				checkAdminAjax('manage_content');
                $validator_rules = array(
                    array(
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'group',
                        'label' => 'Group',
                        'rules' => array('required' => '', 'max_len[1]' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

				$module_position = $this->modules->get_last_module_position();
                $insert = array(
                    'name_module' => cleanInput($_POST['name']),
                    'title_module' => cleanInput($_POST['title']),
                    'group_module' => cleanInput($_POST['group']),
                    'description_module' => $_POST['text'],
					'position_module' => $module_position + 1
                );

				$this->load->model('Ep_Modules_Model', 'modules');
				if ($this->modules->insert_ep_module($insert))
                    jsonResponse('The EP module has been successfully changed.', 'success');

				jsonResponse('Error: You cannot add EP module now. Please try again later.');
                break;
        }
    }
}

?>

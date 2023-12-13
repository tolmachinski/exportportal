<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class UserRights_Controller extends TinyMVC_Controller {

	public function popup_userrights() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			messageInModal(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_grouprights')) {
			messageInModal(translate("systmess_error_rights_perform_this_action"));
        }

		$this->load->model('UserGroup_Model', 'groups');
		$data['errors'] = array();
		$id_user = $this->session->id;

		$op = $this->uri->segment(3);
		switch ($op) {
			case 'edit_right':
				$id_right = intval($this->uri->segment(4));
				$data['right'] = $this->groups->getRight($id_right);
                $data['bymodule'] = $this->groups->getRModules(false);
				$data['upload_folder'] = encriptedFolderName();

				$this->view->display('admin/user/grouprights/forms/right_form_view', $data);
			break;
			case 'add_right':
				$data['bymodule'] = $this->groups->getRModules(false);
				$data['upload_folder'] = encriptedFolderName();

				$this->view->display('admin/user/grouprights/forms/right_form_view', $data);
			break;
			case 'edit_group':
				$id_group = intval($this->uri->segment(4));
				$data['group'] = $this->groups->getGroup($id_group);
				$data['upload_folder'] = encriptedFolderName();
				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				$this->view->display('admin/user/grouprights/forms/group_form_view', $data);
			break;
			case 'add_group':
				$data['upload_folder'] = encriptedFolderName();
				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				$this->view->display('admin/user/grouprights/forms/group_form_view', $data);
			break;
		}
	}

	public function ajax_userrights_operation() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_grouprights')) {
			jsonResponse(translate("systmess_error_rights_perform_this_action"));
        }

		$this->load->model('UserGroup_Model', 'groups');
		$id_user = $this->session->id;
		$op = $this->uri->segment(3);

		switch ($op) {
			case 'update_right':
				$validator_rules = array(
					array(
						'field' => 'r_name',
						'label' => 'Name',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'r_alias',
						'label' => 'Alias',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'r_descr',
						'label' => 'Description',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'r_module',
						'label' => 'Module',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'rcan_delete',
						'label' => 'Can delete option',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'share_to_staff',
						'label' => 'Can share to staff',
						'rules' => array('required' => '')
					),
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$idright = intval($_POST['idright']);

				if(!$this->groups->existRight($idright)) {
					jsonResponse('ERROR: The right does not exist.');
                }

				$data = array(
					'r_name' => cleanInput($_POST['r_name']),
					'r_alias' => cleanInput($_POST['r_alias']),
					'r_descr' => cleanInput($_POST['r_descr']),
					'r_module' => intVal($_POST['r_module']),
					'rcan_delete' => intVal($_POST['rcan_delete']),
					'share_to_staff' => intVal($_POST['share_to_staff'])
				);

				if(isset($_POST['has_field'])) {
					$data['has_field'] = $_POST['has_field'];
                } else {
					$data['has_field'] = 0;
                }

				$right_info = $this->groups->getRight($idright);

				if($this->groups->updateRight($idright, $data)){

					if(isset($_POST['name_field']) && $data['has_field']){
						$field = array(
							'name_field' => cleanInput($_POST['name_field'])
						);

						if(!empty($_POST['icon'])) {
							$field['icon'] = cleanInput($_POST['icon']);
                        }



						if(!empty($_POST['type_field'])) {
							$field['type'] = cleanInput($_POST['type_field']);
                        }

						if(!empty($_POST['sample_field'])) {
							$field['sample_field'] = cleanInput($_POST['sample_field']);
                        }

						if(!empty($_POST['valid_rule'])) {
							$field['valid_rule'] = cleanInput($_POST['valid_rule']);
                        }

						if(empty($right_info['name_field'])) {
							$field['id_right'] = $idright;
							$this->groups->setField($field);
						} else {
							$this->groups->updateField($idright, $field);
                        }
					}

                    $params = array(
                        'idright' => $idright,
                        'old_module' => $right_info['r_module'],
                        'r_module' => $data['r_module'],
                        'r_name' => $data['r_name'],
                        'r_alias' => $data['r_alias'],
                        'r_descr' => $data['r_descr'],
                        'rcan_delete' => $data['rcan_delete']
                    );

					$params['has_field'] = ($_POST['has_field'])?'+':'-';

					jsonResponse('This right has been successfully updated.', 'success', $params);
				} else {
					jsonResponse('ERROR: The rights have not been updated.');
                }
			break;
			case 'add_right':
				$validator_rules = array(
					array(
						'field' => 'r_name',
						'label' => 'Name',
						'rules' => array('required' => '','alpha' => '')
					),
					array(
						'field' => 'r_alias',
						'label' => 'Alias',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'r_descr',
						'label' => 'Description',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'r_module',
						'label' => 'Module',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'rcan_delete',
						'label' => 'Can delete option',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'share_to_staff',
						'label' => 'Can share to staff',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$data = array(
					'r_name' => cleanInput($_POST['r_name']),
					'r_alias' => cleanInput($_POST['r_alias']),
					'r_descr' => cleanInput($_POST['r_descr']),
					'r_module' => intVal($_POST['r_module']),
					'rcan_delete' => intVal($_POST['rcan_delete']),
					'share_to_staff' => intVal($_POST['share_to_staff'])
				);

				if(isset($_POST['has_field'])) {
					$data['has_field'] = $_POST['has_field'];
                }

				if($this->groups->existRight(null, $data['r_name'])) {
					jsonResponse('ERROR: This right already exists (user, category).');
                }

				$idright = $this->groups->setRight($data);

				if(isset($_POST['name_field']) && $_POST['has_field'] && $idright){
					$field = array(
						'name_field' => cleanInput($_POST['name_field']),
						'id_right' => intVal($idright)
					);

					if(!empty($_POST['sample_field'])) {
						$field['sample_field'] = cleanInput($_POST['sample_field']);
                    }

					if(!empty($_POST['valid_rule'])) {
						$field['valid_rule'] = cleanInput($_POST['valid_rule']);
                    }

					if(!empty($_POST['type_field'])) {
						$field['type'] = cleanInput($_POST['type_field']);
                    }

					if(!empty($_POST['icon'])) {
							$field['icon'] = cleanInput($_POST['icon']);
                    }

					$this->groups->setField($field);
				}

                $params = array(
                    'idright' => $idright,
                    'r_module' => $data['r_module'],
                    'r_name' => $data['r_name'],
                    'r_alias' => $data['r_alias'],
                    'r_descr' => $data['r_descr'],
                    'rcan_delete' => $data['rcan_delete']
                );

				$params['has_field'] = ($_POST['has_field'])?'+':'-';

				jsonResponse('This right has been successfully created.','success', $params);
			break;
			case 'remove_right':
				$idright = intval($_POST['right']);
				$field = $this->groups->getField($idright);

				if($field['rcan_delete'] === '0') {
					jsonResponse('ERROR: The right have not been remove.');
                }

				if($this->groups->deleteRights((array)$idright)) {
					jsonResponse('The right has been successfully deleted.','success');
				} else {
					jsonResponse('ERROR: The right has not been deleted.');
                }
			break;
			case 'update_group':
				$validator_rules = array(
					array(
						'field' => 'gr_name',
						'label' => 'Name',
						'rules' => array('required' => '','alpha' => '')
					),
					array(
						'field' => 'gr_type',
						'label' => 'Type of group',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'gr_priority',
						'label' => 'Group priority',
						'rules' => array('required' => '', 'number' => '')
					),
					array(
						'field' => 'can_delete',
						'label' => 'Can delete option',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$data = array(
					'gr_name' => cleanInput($_POST['gr_name']),
					'gr_priority' => intVal($_POST['gr_priority']),
					'can_delete' => intVal($_POST['can_delete']),
					'gr_type' => $_POST['gr_type']
				);

				$idgroup = intVal($_POST['idgroup']);

				if(!$this->groups->existGroup($idgroup)) {
					jsonResponse('ERROR: This group does not exist.');
                }

				if(!empty($_POST['images'])){
					$path = "public/img/groups";
					$file = $_POST['images'];

					$conditions = array(
						'images' => $file,
						'destination' => $path,
						'resize' => '80x80',
						'rules' => array(
							'size' => 2000,
							'min_height' => 80,
							'min_width' => 80
						)
					);
					$res = $this->upload->copy_images_new($conditions);

					if(count($res['errors'])) {
						jsonResponse('ERROR: The rights have not been created.');
                    }

					$data['stamp_pic'] = $res[0]['new_name'];
				}

				if($this->groups->updateGroup($idgroup, $data)) {
					$data['idgroup'] = $idgroup;
					jsonResponse('The group has been successfully updated.','success',$data);
				} else {
					jsonResponse('Error: This group has not been updated.');
                }
			break;
			case 'add_group':
				$validator_rules = array(
					array(
						'field' => 'gr_name',
						'label' => 'Name',
						'rules' => array('required' => '','alpha' => '')
					),
					array(
						'field' => 'gr_type',
						'label' => 'Type of group',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'gr_priority',
						'label' => 'Group priority',
						'rules' => array('required' => '', 'number' => '')
					),
					array(
						'field' => 'can_delete',
						'label' => 'Can delete option',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$data = array(
					'gr_name' => cleanInput($_POST['gr_name']),
					'can_delete' => intVal($_POST['can_delete']),
					'gr_priority' => intVal($_POST['gr_priority']),
					'gr_type' => cleanInput($_POST['gr_type'])
				);

				if($this->groups->existGroup(null, $data['gr_name'])) {
					jsonResponse('Error: Group already exists.');
                }

				if(!empty($_POST['images'])){
					$path = 'public/img/groups';
					$file = $_POST['images'];

					$conditions = array(
						'images' => $file,
						'destination' => $path,
						'resize' => '80x80',
						'rules' => array(
							'size' => 2000,
							'min_height' => 80,
							'min_width' => 80
						)
					);
					$res = $this->upload->copy_images_new($conditions);

					if(count($res['errors'])) {
						jsonResponse('ERROR: The rights have not been created.');
                    }

					$data['stamp_pic'] = $res[0]['new_name'];
				}

				$data['idgroup'] = $this->groups->setGroup($data);
				if(!empty($data['idgroup'])){
					jsonResponse('Group was successfully created.','success',$data);
				} else {
					jsonResponse('Error: Group has not been created.');
                }
			break;
			case 'remove_group':
				$idgroup = intval($_POST['group']);

				$group_info = $this->groups->getGroup($idgroup,'can_delete,stamp_pic');

				if(!$group_info['can_delete']) {
					jsonResponse('ERROR: The group have not been remove.');
                }

				if($this->groups->deleteGroup($idgroup)) {
					@unlink('public/img/groups/'.$group_info['stamp_pic']);
					jsonResponse('The group has been successfully deleted.','success');
				} else {
					jsonResponse('Error: The group has not been deleted.');
                }
			break;
			case 'create_relation':
				$data = array(
					'idgroup' => intval($_POST['group']),
					'idright' => intval($_POST['right'])
				);

				if(!$this->groups->setRelation($data)) {
					jsonResponse('Relation was successfully created.','success');
                } else {
					jsonResponse('Error: The relation has not been created.');
                }
			break;
			case 'remove_relation':
				$data = array(
					'idgroup' => intval($_POST['group']),
					'idright' => intval($_POST['right'])
				);

				if($this->groups->deleteRelation($data['idgroup'], $data['idright'])) {
					jsonResponse('Relation was successfully removed.','success');
                } else {
					jsonResponse('Error: The relation has not been deleted.');
                }
			break;
		}
	}

	function ajax_upload_stamp(){
		if(!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if(empty($_FILES['files'])) {
			jsonResponse('Error: Please select file to upload.');
        }

		global $tmvc;
		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder=checkEncriptedFolder($upload_folder))) {
			jsonResponse('Error: File upload path is not correct.');
        }

		$path = 'temp/groups_stamp/' . $upload_folder;
        create_dir($path);

		// Count number of files in this folder, to prevent upload more files than photo limit
		$fi = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
		$count_temp = iterator_count($fi);

		$disponible = 1 - $count_temp;
		if($disponible <= 0 || (count($_FILES['files']['name']) > $disponible)){
			jsonResponse('Error: You cannot upload more than '.($disponible + $count_temp).' photo(s).');
		}

		$conditions = array(
			'files' => $_FILES['files'],
			'destination' => $path,
			'rules' => array(
				'size' => $tmvc->my_config['fileupload_max_file_size']
			)
		);
		$res = $this->upload->upload_images_new($conditions);

		if (count($res['errors'])) {
			$result['result'] = implode(', ', $res['errors']);
            $result['resultcode'] = 'failed';
			jsonResponse($res['errors']);
		} else {
			foreach($res as $item){
				$result['files'][] = array('path'=> $path . '/' . $item['new_name'],'name' => $item['new_name']);
			}
			jsonResponse('', 'success', $result);
		}
    }

	function ajax_upload_stamp_delete(){
		if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (empty($_POST['file'])) {
            jsonResponse('Error: File name is not correct.');
        }

		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder=checkEncriptedFolder($upload_folder))) {
			jsonResponse('Error: File upload path is not correct.');
        }

		global $tmvc;
        $path = 'temp/groups_stamp/'. $upload_folder;
        if (!is_dir($path)) {
            jsonResponse('Error: Upload path is not correct.');
        }

		@unlink($path.'/'.$_POST['file']);

		jsonResponse('','success');
	}

	function ajax_delete_db_stamp(){
		if(!isAjaxRequest()) {
			headerRedirect();
        }

		if(!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		$this->load->model('UserGroup_Model', 'groups');
		$id_photo = intval($_POST['file']);
		$field = $this->groups->getGroup($id_photo,'idgroup,stamp_pic');

		if($this->groups->updateGroup($field['idgroup'], array('stamp_pic' => ''))){
			@unlink('public/img/groups/' . $field['stamp_pic']);
			jsonResponse('Stamp was successfully deleted.', 'success');
		} else {
			jsonResponse('Error: error in deleting stamp. Please try again.');
        }
	}
}

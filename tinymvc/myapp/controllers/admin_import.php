<?php

/**
 * admin_companies.php
 *
 * admin_companies application controller
 *
 * @author Cravciuc Andrei
 * @deprecated
 */
// class Admin_import_Controller extends TinyMVC_Controller {

// 	private function _load_main() {
// 		$this->load->model('Admin_import_Model', 'import');
// 	}

// 	function index() {

//         show_404();

//         checkAdmin('moderate_content');
//         $this->_load_main();

//         $this->view->assign('title', 'Import');
// 		$this->view->display('admin/header_view');
// 		$this->view->display('admin/import/index_view');
// 		$this->view->display('admin/footer_view');
// 	}

//     function ajax_admin_import_dt() {
//         if (!isAjaxRequest())
//             show_404();

//         checkAdminAjaxDT('manage_content');

//         $this->_load_main();

//         $params = array(
//             'per_p' => intVal($_POST['iDisplayLength']),
//             'start' => intVal($_POST['iDisplayStart']),
//             // 'sort_by' => flat_dt_ordering($_POST, array(
//             //     'dt_id' => 'id',
//             //     'dt_type' => 'type',
//             //     'dt_date' => 'date',
//             //     'dt_status' => 'status'
//             // ))
//         );

//         if ($_POST['iSortingCols'] > 0) {
//             for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
//                 switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
//                     case 'dt_id': $params['sort_by'][] = 'id-' . $_POST['sSortDir_' . $i];
//                     break;
//                     case 'dt_type': $params['sort_by'][] = 'type-' . $_POST['sSortDir_' . $i];
//                     break;
//                     case 'dt_date': $params['sort_by'][] = 'date-' . $_POST['sSortDir_' . $i];
//                     break;
//                     case 'dt_status': $params['sort_by'][] = 'status-' . $_POST['sSortDir_' . $i];
//                     break;
//                 }
//             }
//         }

//         if (isset($_POST['id_import']))
//             $params['id_import'] = intVal($_POST['id_import']);

//         if (isset($_POST['type']))
//             $params['type'] = cleanInput($_POST['type']);

//         if (isset($_POST['status']))
//             $params['status'] = cleanInput($_POST['status']);

//         if (isset($_POST['start']))
//             $params['start_date'] = formatDate(cleanInput($_POST['start']), 'Y-m-d');

//         if (isset($_POST['finish']))
//             $params['finish_date'] = formatDate(cleanInput($_POST['finish']), 'Y-m-d');

//         if (isset($_POST['keywords']))
//             $params['search'] = cleanInput(cut_str($_POST['keywords']));

//         $imports = $this->import->get_import_data($params);
//         $records_total = $this->import->get_import_data_count($params);

//         $output = array(
//             "sEcho" => intval($_POST['sEcho']),
//             "iTotalRecords" => $records_total,
//             "iTotalDisplayRecords" => $records_total,
//             "aaData" => array()
//         );

//         if(empty($imports))
//             jsonResponse('', 'success', $output);

//         $statuses = array(
//             'new' => array(
//                 'icon' => '<i class="ep-icon ep-icon_new txt-red fs-30"></i>',
//                 'title' => 'New'
//             ),
//             'updated' => array(
//                 'icon' => '<i class="ep-icon ep-icon_ok-circle txt-green fs-30"></i>',
//                 'title' => 'Updated'
//             ),
//             'ready' => array(
//                 'icon' => '<i class="ep-icon ep-icon_envelope txt-blue fs-30"></i>',
//                 'title' => 'Ready to notify'
//             )
//         );

//         $types = array(
//             'company_seller' => 'Company, Seller',
//             'seller' => 'Seller',
//             'buyer' => 'Buyer',
//             'shipper' => 'Freight Forwarder'
//         );

//         foreach ($imports as $import_data) {
//             $actions = array();
//             if($import_data['status'] != 'ready'){
//                 $actions[] = '<a href="'.__SITE_URL.'admin_import/popup_forms/edit_data/'.$import_data['id'].'" title="Edit import data" data-title="Edit import data" class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" data-table="dtImport"></a>';
//                 $actions[] = '<a class="ep-icon ep-icon_ok-circle txt-green confirm-dialog" data-callback="import_rows" data-import_type="single" data-message="Are you sure want to import this data?" title="Import data" data-import="'.$import_data['id'].'"></a>';
//             } else{
//                 $actions[] = '<a class="ep-icon ep-icon_envelope txt-blue confirm-dialog" data-callback="notify_import_rows" data-notify_type="single" data-message="Are you sure want to notify the user?" title="Notify the user" data-import="'.$import_data['id'].'"></a>';
//             }

//             $actions[] = '<a href="#" class="confirm-dialog ep-icon ep-icon_remove txt-red" data-callback="delete_row" data-import="'.$import_data['id'].'" title="Delete row" data-message="Are you sure you want to delete this row?"></a>';

//             $output['aaData'][] = array(
//                 'dt_check'         => '<input type="checkbox" class="check-import-data mt-0" data-import="'.$import_data['id'].'">'
//                                       .'<a rel="import_details" title="View details" class="ep-icon ep-icon_plus m-0"></a>',
//                 'dt_id'         => '<span class="lh-14">'.$import_data['id'] . '</span>',
//                 'dt_type'       => '<div class="pull-left">'
//                                    .'<a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Type" title="Filter by ' . $types[$import_data['type']] . '" data-value-text="' . $types[$import_data['type']] . '" data-value="' . $import_data['type'] . '" data-name="type"></a></div>'
//                                    .'<div class="clearfix"></div>'
//                                    .$types[$import_data['type']],
//                 'dt_date'       => formatDate($import_data['date']),
//                 'dt_status'     => '<div class="pull-left">'
//                                    .'<a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Status" title="Filter by ' . $statuses[$import_data['status']]['title'] . '" data-value-text="' . $statuses[$import_data['status']]['title'] . '" data-value="' . $import_data['status'] . '" data-name="status"></a></div>'
//                                    .'<div class="clearfix"></div>'
//                                    .'<span>' . $statuses[$import_data['status']]['icon'] . '<br>' .  $statuses[$import_data['status']]['title'] . '</span>',
//                 'dt_actions'    => implode('', $actions),
//                 'dt_detail'     => $import_data['import_data']
//             );
//         }

//        jsonResponse('', 'success', $output);
//     }

//     function ajax_operations(){
//         if (!isAjaxRequest())
//             show_404();

//         checkAdminAjax('manage_content');

//         $this->_load_main();
//         $action = $this->uri->segment(3);
//         switch($action){
//             case 'import_rows' :
//                 $imports_list = $_POST['imports_list'];
//                 if(!is_array($imports_list)){
//                     jsonResponse('<i class="ep-icon ep-icon_warning "></i> Error: You did not select any rows to be processed.');
//                 }

//                 $params = array(
//                     'status' => 'updated',
//                     'imports_list' => implode(',', $imports_list)
//                 );
//                 $imports = $imports = $this->import->get_import_data($params);

//                 if(empty($imports)){
//                     jsonResponse('<i class="ep-icon ep-icon_warning "></i> Info: There are no imports ready to be processed.', 'warning');
//                 }

//                 foreach($imports as $import){
//                     if(in_array($import['type'], array('seller', 'buyer'))){
//                         $type = 'user';
//                     } else{
//                         $type = $import['type'];
//                     }
//                     $handler = 'handler_import_'.$type;
//                     if(method_exists('Admin_import_Controller',$handler)){
//                         $this->$handler($import);
//                     }
//                 }

//                 jsonResponse('Success', 'success');
//             break;
//             case 'edit_data':
//                 $id_data = (int)$_POST['id_import'];
//                 $import_data = $this->import->get_import($id_data);

// 				if (empty($import_data))
// 					jsonResponse('Error: The import data does not exist.'.$id_data);

//                 if(in_array($import_data['type'], array('seller', 'buyer'))){
//                     $type = 'user';
//                 } else{
//                     $type = $import_data['type'];
//                 }

//                 $handler = 'handler_update_'.$type;
//                 if(method_exists('Admin_import_Controller',$handler)){
//                     return $this->$handler($import_data);
//                 } else{
//                     jsonResponse('Error: The handler does not exist.');
//                 }
//             break;
//         }
//     }

//     function popup_forms() {
//         if (!isAjaxRequest())
//             show_404();

// 		if (!logged_in())
// 			messageInModal('Error: You do not have permission to perform this action.');

//         // CHECK USER RIGHTS - EP MANAGER
//         checkAdminAjaxModal('manage_content');

//         $this->_load_main();
//         $type = $this->uri->segment(3);
//         switch($type){
//             case 'edit_data' :
//                 $id_data = (int)$this->uri->segment(4);
//                 $import_data = $this->import->get_import($id_data);

// 				if (empty($import_data))
// 					messageInModal('Error: The import data does not exist.');

//                 switch($import_data['type']){
//                     case 'company_seller' :
//                         $this->load->model("Country_model", 'country');
//                         $data['port_country'] = $this->country->fetch_port_country();
//                         if($import_data['status'] == 'updated'){
//                             $import = json_decode($import_data['import_data'], true);

// 							$data['states'] = $this->country->get_states($import['id_country']);
//                             $data['city_selected'] = $this->country->get_city($import['id_city']);

//                         $this->load->model('UserGroup_Model', 'user_group');
//                         $data['groups'] = $this->user_group->getGroupsByType(array('type' => "'Seller'"));
// 				        $data['company'] = $import_data;
//                         $view_name = 'edit_company_seller_form_view';
//                     break;
//                     case 'shipper' :
// 				        $data['shipper'] = $import_data;
//                         $view_name = 'edit_shipper_form_view';
//                     break;
//                     case 'buyer' :
//                     case 'seller' :
//                         $this->load->model("Country_model", 'country');
//                         $data['port_country'] = $this->country->fetch_port_country();
//                         if($import_data['status'] == 'updated'){
//                             $import = json_decode($import_data['import_data'], true);

// 							$data['states'] = $this->country->get_states($import['id_country']);
//                             $data['city_selected'] = $this->country->get_city($import['id_city']);
// 				        $data['user'] = $import_data;
//                         $this->load->model('UserGroup_Model', 'user_group');
//                         if($import_data['type'] == 'buyer'){
//                             $data['groups'] = $this->user_group->getGroupsByType(array('type' => "'Buyer'"));
//                         } else{
//                             $data['groups'] = $this->user_group->getGroupsByType(array('type' => "'Seller'"));
//                         }
//                         $view_name = 'edit_user_form_view';
//                     break;
//                     default:
// 					   messageInModal('Error: The import data type does not exist.');
//                     break;
//                 }

// 				$this->view->assign($data);
// 				$this->view->display('admin/import/'.$view_name);
//             break;
//         }
//     }

//     private function handler_update_company_seller($import = array()){
//         if(empty($import)){
//             jsonResponse('Error: The import data does not exist.');
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             jsonResponse('Error: The import data is empty.');
//         }

// 		$this->load->model('Admin_import_Model', 'import');
// 		$this->load->model('Company_Model', 'company');
// 		$this->load->model('User_Model', 'users');

//         $validator_rules = array(
// 			array(
// 				'field' => 'company_name',
// 				'label' => 'Company name',
// 				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'fname',
// 				'label' => 'First name',
// 				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'lname',
// 				'label' => 'Last name',
// 				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'email',
// 				'label' => 'Email',
// 				'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
// 			),
// 			array(
// 				'field' => 'group',
// 				'label' => 'Seller group',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'group_name',
// 				'label' => 'Seller group name',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'port_country',
// 				'label' => 'Country',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'country_name',
// 				'label' => 'Country name',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'port_city',
// 				'label' => 'City',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'city_name',
// 				'label' => 'City name',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'address',
// 				'label' => 'Last name',
// 				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[255]' => '')
// 			),
// 			array(
// 				'field' => 'zip',
// 				'label' => 'Zip',
//                 'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
// 			),
// 			array(
// 				'field' => 'phone',
// 				'label' => 'Phone',
// 				'rules' => array('required' => '', 'min_len[12]' => '', 'max_len[25]' => '')
// 			),
// 			array(
// 				'field' => 'logo',
// 				'label' => 'Logo',
// 				'rules' => array('required' => '', 'valid_url' => '')
// 			)
// 		);
// 		$this->validator->set_rules($validator_rules);
//         if(!$this->validator->validate()){
// 			jsonResponse($this->validator->get_array_errors());
// 		}

//         $email = cleanInput($_POST['email'], true);
//         if($this->company->exist_company(array('email' => $email))){
// 			jsonResponse('Error: The company with this email already exists in the database. Please choose another one!');
// 		}

//         if($this->users->exist_user_by_email($email)){
// 			jsonResponse('Error: The seller with this email already exists in the database. Please choose another one!');
// 		}
//         // UPDATE IMPORT_DATA AND STATUS
//         $updated_data = array(
//             'company_name' => cleanInput($_POST['company_name']),
//             'user_fname' => cleanInput($_POST['fname']),
//             'user_lname' => cleanInput($_POST['lname']),
//             'email' => $email,
//             'group' => intVal($_POST['group']),
//             'group_name' => cleanInput($_POST['group_name']),
//             'country' => cleanInput($_POST['country_name']),
//             'id_country' => intVal($_POST['port_country'])
//         );

//         if(!empty($_POST['states'])){
//             $updated_data['state'] = cleanInput($_POST['state_name']);
//             $updated_data['id_state'] = intVal($_POST['states']);
//         } else{
//             $updated_data['id_state'] = 0;
//             $updated_data['state'] = '';
//         }

//         $updated_data['city'] = cleanInput($_POST['city_name']);
//         $updated_data['id_city'] = intVal($_POST['port_city']);
//         $updated_data['zip'] = cleanInput($_POST['zip']);
//         $updated_data['address'] = cleanInput($_POST['address']);
//         $updated_data['phone'] = cleanInput($_POST['phone']);
//         $updated_data['logo'] = $_POST['logo'];

//         if($import['status'] == 'new' || $import_data['logo'] != $_POST['logo']){
//             $remote_path = $_POST['logo'];
//             $local_path = 'temp/import/company_seller/'.$import['id'];
//             if(!is_dir($local_path)){
//                 if (!mkdir($local_path, 0755, true)) {
//                     jsonResponse('Error: Cannot create the folder for images.');
//                 }
//             }

//             if(!$this->upload->check_remote_file(array('remote_path' => $remote_path))){
//                 jsonResponse('Error: The logo url us not valid.');
//             }

//             $file_extension = $this->upload->get_remote_file_ext(array('remote_path' => $remote_path));
//             $file_name = 'image_' . uniqid() . '.' . $file_extension;
//             if(!$this->upload->get_remote_file($remote_path, $local_path.'/'.$file_name)){
//                 jsonResponse('Error: The remote file cannot be downloaded.');
//             }
//             $conditions = array(
//                 'images' => array($local_path.'/'.$file_name),
//                 'destination' => $local_path,
//                 'resize' => '230x230',
//                 'thumbs' => '50x50,138x138'
//             );

//             $res = $this->upload->copy_images_new($conditions);

//             if (count($res['errors']))
//                 jsonResponse($res['errors']);

//             if(empty($res)){
//                 jsonResponse('Error: The file could not be resized.');
//             }

//             $updated_data['image_logo'] = array('path'=> $local_path . '/','name' => $res[0]['new_name']);

//             $thumbs = array();
//             foreach($res[0]['thumbs'] as $thumb){
//                 $thumbs[$thumb['thumb_key']] = $thumb['thumb_name'];
//             }
//             $updated_data['image_logo']['thumbs'] = $thumbs;
//             if(!empty($import_data['image_logo'])){
//                 @unlink($import_data['image_logo']['path'].'/'.$import_data['image_logo']['name']);
//                 if(!empty($import_data['image_logo']['thumbs'])){
//                     foreach($import_data['image_logo']['thumbs'] as $one_thumb){
//                         @unlink($import_data['image_logo']['path'].'/'.$one_thumb);
//                     }
//                 }
//             }
//             @unlink($local_path.'/'.$file_name);
//         } else{
//             $updated_data['image_logo'] = $import_data['image_logo'];
//         }

//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($updated_data), 'status' => 'updated'));
//         jsonResponse('The data has been successfully updated.', 'success');
//     }

//     private function handler_update_shipper($import = array()){
//         if(empty($import)){
//             jsonResponse('Error: The import data does not exist.');
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             jsonResponse('Error: The import data is empty.');
//         }

// 		$this->load->model('Admin_import_Model', 'import');
// 		$this->load->model('Shippers_Model', 'shipper');

//         $validator_rules = array(
// 			array(
// 				'field' => 'shipper_name',
// 				'label' => 'Freight Forwarder name',
// 				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'email',
// 				'label' => 'Email',
// 				'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
// 			),
// 			array(
// 				'field' => 'phone',
// 				'label' => 'Phone',
// 				'rules' => array('required' => '', 'min_len[12]' => '', 'max_len[25]' => '')
// 			),
// 			array(
// 				'field' => 'fax',
// 				'label' => 'Fax',
// 				'rules' => array('min_len[12]' => '', 'max_len[25]' => '')
// 			),
// 			array(
// 				'field' => 'logo',
// 				'label' => 'Logo',
// 				'rules' => array('required' => '', 'valid_url' => '')
// 			)
// 		);
// 		$this->validator->set_rules($validator_rules);
//         if(!$this->validator->validate()){
// 			jsonResponse($this->validator->get_array_errors());
// 		}

//         $email = cleanInput($_POST['email'], true);
//         if($this->shipper->exist_shipper_by_email($email)){
// 			jsonResponse('Error: The email already exists in the database. Please choose another one!');
// 		}

//         // IF USER HAS BEEN REGISTERED UPDATE IMPORT_DATA AND STATUS
//         $updated_data = array(
//             'shipper_name' => cleanInput($_POST['shipper_name']),
//             'email' => $email,
//             'phone' => cleanInput($_POST['phone']),
//             'fax' => cleanInput($_POST['fax']),
//             'logo' => $_POST['logo']
//         );

//         if($import['status'] == 'new' || $import_data['logo'] != $_POST['logo']){
//             $remote_path = $_POST['logo'];
//             $local_path = 'temp/import/shipper/'.$import['id'];
//             if(!is_dir($local_path)){
//                 if (!mkdir($local_path, 0755, true)) {
//                     jsonResponse('Error: Cannot create the folder for images.');
//                 }
//             }

//             if(!$this->upload->check_remote_file(array('remote_path' => $remote_path))){
//                 jsonResponse('Error: The logo url us not valid.');
//             }

//             $file_extension = $this->upload->get_remote_file_ext(array('remote_path' => $remote_path));
//             $file_name = 'image_' . uniqid() . '.' . $file_extension;
//             if(!$this->upload->get_remote_file($remote_path, $local_path.'/'.$file_name)){
//                 jsonResponse('Error: The remote file cannot be downloaded.');
//             }

//             $conditions = array(
//                 'images' => array($local_path.'/'.$file_name),
//                 'destination' => $local_path,
//                 'resize' => 'Rx200'
//             );
//             $res = $this->upload->copy_images_new($conditions);

//             if (count($res['errors']))
//                 jsonResponse($res['errors']);

//             if(empty($res)){
//                 jsonResponse('Error: The file could not be resized.');
//             }

//             $updated_data['image_logo'] = array('path'=> $local_path . '/','name' => $res[0]['new_name']);
//             if(!empty($import_data['image_logo'])){
//                 @unlink($import_data['image_logo']['path'].'/'.$import_data['image_logo']['name']);
//             }
//             @unlink($local_path.'/'.$file_name);
//         } else{
//             $updated_data['image_logo'] = $import_data['image_logo'];
//         }

//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($updated_data), 'status' => 'updated'));
//         jsonResponse('The data has been successfully updated.', 'success');
//     }

//     private function handler_update_user($import = array()){
//         if(empty($import)){
//             jsonResponse('Error: The import data does not exist.');
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             jsonResponse('Error: The import data is empty.');
//         }

// 		$this->load->model('Admin_import_Model', 'import');
// 		$this->load->model('User_Model', 'users');

//         $validator_rules = array(
// 			array(
// 				'field' => 'fname',
// 				'label' => 'First Name',
// 				'rules' => array('required' => '','valid_user_name' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'lname',
// 				'label' => 'Last Name',
// 				'rules' => array('required' => '','valid_user_name' => '', 'min_len[3]' => '', 'max_len[50]' => '')
// 			),
// 			array(
// 				'field' => 'email',
// 				'label' => 'Email',
// 				'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
// 			),
// 			array(
// 				'field' => 'group',
// 				'label' => 'User group',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'group_name',
// 				'label' => 'User group name',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'port_country',
// 				'label' => 'Country',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'country_name',
// 				'label' => 'Country name',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'port_city',
// 				'label' => 'City',
// 				'rules' => array('required' => '')
// 			),
// 			array(
// 				'field' => 'city_name',
// 				'label' => 'City name',
// 				'rules' => array('required' => '')
// 			)
// 		);
// 		$this->validator->set_rules($validator_rules);
//         if(!$this->validator->validate()){
// 			jsonResponse($this->validator->get_array_errors());
// 		}

//         $email = cleanInput($_POST['email'], true);
//         if($this->users->exist_user_by_email($email)){
// 			jsonResponse('Error: The email already exists in the database. Please choose another one!');
// 		}

//         // UPDATE IMPORT_DATA AND STATUS
//         $updated_data = array(
//             'user_fname' => cleanInput($_POST['fname']),
//             'user_lname' => cleanInput($_POST['lname']),
//             'email' => $email,
//             'group' => intVal($_POST['group']),
//             'group_name' => cleanInput($_POST['group_name']),
//             'country' => cleanInput($_POST['country_name']),
//             'id_country' => intVal($_POST['port_country']),
//             'city' => cleanInput($_POST['city_name']),
//             'id_city' => intVal($_POST['port_city']),
//         );

//         if(!empty($_POST['states'])){
//             $updated_data['state'] = cleanInput($_POST['state_name']);
//             $updated_data['id_state'] = intVal($_POST['states']);
//         } else{
//             $updated_data['id_state'] = 0;
//             $updated_data['state'] = '';
//         }

//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($updated_data), 'status' => 'updated'));
//         jsonResponse('The data has been successfully updated.', 'success');
//     }

//     private function handler_import_company_seller($import = array()){
//         if(empty($import)){
//             return false;
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             return false;
//         }

// 		$this->load->model('Admin_import_Model', 'import');
// 		$this->load->model('Company_Model', 'company');
// 		$this->load->model('User_Model', 'users');

//         if($this->company->exist_company(array('email' => $import_data['email']))){
// 			jsonResponse('Error: The company with this email already exists in the database. Please choose another one!');
//         }

//         $this->load->model('Auth_model', 'auth_hash');
//         $encrypted_email = getEncryptedEmail($import_data['email']);

//         if($this->auth_hash->exists_hash($encrypted_email)){
// 			return false;
// 		}

//         // GENERATE USER PASSWORD AND PREPARE DATA TO INSERT
//         $password = base64_encode(random_bytes(16));
//         $insert = array(
//             'fname' => $import_data['user_fname'],
//             'lname' => $import_data['user_lname'],
//             'email' => $import_data['email'],
//             'user_group' => $import_data['group'],
//             'registration_date' => date('Y-m-d H:i:s'),
//             'activation_code' => get_sha1_token($import_data['email']),
// 			'paid_until' => date('Y-m-d'),
// 			'country' => $import_data['id_country'],
// 			'state' => $import_data['id_state'],
// 			'city' => $import_data['id_city'],
// 			'paid' => 1
//         );
//         //region add_hash
//         $hash_insert = array(
//             'token_email' 	 => $encrypted_email,
//             'token_password' => getEncryptedPassword($password)
//         );

//         $insert['id_principal'] = model('principals')->insert_last_id();
//             $this->auth_hash->add_hash($insert['id_principal'], $hash_insert);
//         //endregion add_hash

//         // INSERT MAIN USER DATA
//         $id_user = $this->users->setUserMain($insert);

//         if(!$id_user){
//             return false;
//         }

//         // IF USER HAS BEEN REGISTERED UPDATE IMPORT_DATA AND STATUS
//         $import_data['password'] = $password;
//         $import_data['id_user'] = $id_user;

//         $insert = array(
//             'name_company' => $import_data['company_name'],
//             'id_type' => intVal($_POST['type']),
//             'id_country' => $import_data['id_country'],
//             'id_state' => $import_data['id_state'],
//             'id_city' => $import_data['id_city'],
//             'address_company' => $import_data['address'],
//             'zip_company' => $import_data['zip'],
//             'phone_company' => $import_data['phone'],
//             'email_company' => $import_data['email'],
//             'logo_company' => $import_data['image_logo']['name'],
//             'visible_company' => 0,
//             'id_user' => $id_user
//         );

//         $id_company = $this->company->set_company($insert);

//         if(!$id_company){
//             return false;
//         }

//         $logo_path = 'public/img/company/'.$id_company.'/logo';
//         if(!is_dir($logo_path)){
//             mkdir($logo_path, 0755, true);
//         }

//         copy($import_data['image_logo']['path'] .$import_data['image_logo']['name'], $logo_path . '/' . $import_data['image_logo']['name']);
//         @unlink($import_data['image_logo']['path'] .$import_data['image_logo']['name']);
//         if(!empty($import_data['image_logo']['thumbs'])){
//             foreach($import_data['image_logo']['thumbs'] as $one_thumb){
//                 copy($import_data['image_logo']['path'] .$one_thumb, $logo_path . '/' . $one_thumb);
//                 @unlink($import_data['image_logo']['path'] .$one_thumb);
//             }
//         }

//         $import_data['id_company'] = $id_company;
//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($import_data), 'status' => 'ready'));
//         return true;
//     }

//     private function handler_import_user($import = array()){
//         if(empty($import)){
//             return false;
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             return false;
//         }

// 		$this->load->model('Admin_import_Model', 'import');
//         $this->load->model('User_Model', 'users');

//         $this->load->model('Auth_model', 'auth_hash');
//         $encrypted_email = getEncryptedEmail($import_data['email']);

//         if($this->auth_hash->exists_hash($encrypted_email)){
// 			return false;
// 		}

//         // GENERATE USER PASSWORD AND PREPARE DATA TO INSERT
//         $password = base64_encode(random_bytes(16));
//         $insert = array(
//             'fname' => $import_data['user_fname'],
//             'lname' => $import_data['user_lname'],
//             'email' => $import_data['email'],
//             'user_group' => $import_data['group'],
//             'registration_date' => date('Y-m-d H:i:s'),
//             'activation_code' => get_sha1_token($import_data['email']),
// 			'paid_until' => date('Y-m-d'),
// 			'country' => $import_data['id_country'],
// 			'state' => $import_data['id_state'],
// 			'city' => $import_data['id_city'],
// 			'paid' => 1
//         );
//         //region add_hash
//         $hash_insert = array(
//             'token_email' 	 => $encrypted_email,
//             'token_password' => getEncryptedPassword($password)
//         );

//         $insert['id_principal'] = model('principals')->insert_last_id();
//         $this->auth_hash->add_hash($insert['id_principal'], $hash_insert);
//         //endregion add_hash

//         // INSERT MAIN USER DATA
//         $id_user = $this->users->setUserMain($insert);

//         if(!$id_user){
//             return false;
//         }

//         // IF USER HAS BEEN REGISTERED UPDATE IMPORT_DATA AND STATUS
//         $import_data['password'] = $password;
//         $import_data['id_user'] = $id_user;
//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($import_data), 'status' => 'ready'));
//         return true;
//     }

//     private function handler_import_shipper($import = array()){
//         if(empty($import)){
//             return false;
//         }

//         $import_data = json_decode($import['import_data'], true);

//         if(empty($import_data)){
//             return false;
//         }

// 		$this->load->model('Admin_import_Model', 'import');
// 		$this->load->model('Shippers_Model', 'shipper');

//         if($this->shipper->exist_shipper_by_email($import_data['email'])){
// 			return false;
// 		}

//         // PREPARE DATA TO INSERT
//         $insert = array(
//             'co_name' => $import_data['shipper_name'],
//             'phone' => $import_data['phone'],
//             'fax' => $import_data['fax'],
//             'email' => $import_data['email'],
//             'logo' => $import_data['image_logo']['name']
//         );

//         // INSERT SHIPPER DATA
//         $id_shipper = $this->shipper->insert_shipper($insert);

//         if(!$id_shipper){
//             return false;
//         }

//         $logo_path = 'public/img/shippers/' . $id_shipper . '/logo';
//         if(!is_dir($logo_path)){
//             mkdir($logo_path, 0755, true);
//         }

//         copy($import_data['image_logo']['path'] .$import_data['image_logo']['name'], $logo_path . '/' . $import_data['image_logo']['name']);
//         @unlink($import_data['image_logo']['path'] .$import_data['image_logo']['name']);

//         $import_data['logo'] = __SITE_URL . $logo_path . '/' .$import_data['image_logo']['name'];
//         $import_data['image_logo']['path'] = $logo_path . '/';
//         $this->import->update_import_data($import['id'], array('import_data' => json_encode($import_data), 'status' => 'ready'));
//         return true;
//     }
// }
?>

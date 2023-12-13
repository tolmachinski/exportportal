<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\ItemPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Prototype_Controller extends TinyMVC_Controller {

	private $breadcrumbs = array();

	function index() {
		header('location: ' . __SITE_URL);
	}

	private function _load_main(){
		$this->load->model('Prototype_Model', 'prototype');
		$this->load->model('Items_Model', 'items');
		$this->load->model('Category_Model', 'category');
		$this->load->model('User_Model', 'user');
	}

	public function item(){
		if(!logged_in()){
			$this->session->setMessages(translate("systmess_error_should_be_logged"),'errors');
			headerRedirect(__SITE_URL.'login');
		}

		// Check rights for accessing this page
		if( !(have_right('manage_seller_inquiries') || have_right('manage_seller_po') || have_right('buy_item') || have_right('manage_content'))){
			show_404();
		}

		$id_prototype = intVal($this->uri->segment(3));
		if(!$id_prototype){
			show_404();
		}

		$this->_load_main();
		$this->load->model('Company_Model', 'company');

		$data['prototype'] = $this->prototype->get_prototype($id_prototype);
		if(empty($data['prototype'])){
			show_404();
		}

		if (have_right('buy_item') && $data['prototype']['changed']) {
			$data = array(
						'activated' => false,
						'message' => 'Information: This prototype has not been activated by the seller. Please try again later or contact the seller.',
						'type' => 'warning'
					);

            $data['main_content'] = 'new/prototype/index_view';
            $this->view->assign($data);
            $this->view->display('new/index_template_view');
		} else {
            // Check rights for different prototype types
            if(!(have_right('buy_item') || have_right('manage_content'))){
                if( $data['prototype']['type_prototype'] == 'inquiry' && !have_right('manage_seller_inquiries')){
                    show_404();
                } elseif( $data['prototype']['type_prototype'] == 'po' && !have_right('manage_seller_po')){
                    show_404();
                }
            }

            $data['company_info'] = $this->company->get_company(array('id_user' => $data['prototype']['id_seller']));
            if(empty($data['company_info'])){
                show_404();
            }

            if(!empty($data['prototype']['attributes'])){
                $data['aditional_info'] = unserialize($data['prototype']['attributes']);
                if(!empty($data['aditional_info']['attr_info'])){
                    $data['aditional_info']['attr_info'] = explode('|', $data['aditional_info']['attr_info']);
                }

                if(!empty($data['aditional_info']['vin_info'])){
                    $data['aditional_info']['vin_info'] = explode('|', $data['aditional_info']['vin_info']);
                }
            }

            if(!empty($data['prototype']['changes'])){
                $data['prototype']['changes'] = json_decode($data['prototype']['changes'], true);
            }

            $data['prototype']['log'] = array_reverse(json_decode('['.$data['prototype']['log'].']', true));

            if (logged_in()) {
                $chatBtn = new ChatButton(['recipient' => $data['company_info']['id_user'], 'recipientStatus' => $data['company_info']['status']]);
                $data['company_info']['btnChat'] = $chatBtn->button();
            }

            $this->breadcrumbs[] = array(
                'link'	=> __SITE_URL.'prototype/item/'.$data['prototype']['id_prototype'],
                'title'	=> $data['prototype']['title']
            );

            $data['breadcrumbs'] = $this->breadcrumbs;

            $data['main_content'] = 'new/prototype/index_view';
            $this->view->assign($data);
            $this->view->display('new/index_template_view');
        }
	}

	public function ajax_prototype_operation(){
		if(!isAjaxRequest()){
			headerRedirect();
		}

		if(!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

		if(!have_right_or('manage_seller_inquiries,manage_seller_po,buy_item')){
			jsonResponse(translate("systmess_error_rights_perform_this_action"));
		}

		$this->_load_main();
		$id_prototype = intVal($_POST['prototype']);
		$id_user = privileged_user_id();
		$op = $this->uri->segment(3);

		switch($op){
			case 'activate_prototype':
				if(!(have_right('manage_seller_inquiries') || have_right('manage_seller_po'))){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$this->load->model('Notify_Model', 'notify');

				$prototype_info = $this->prototype->get_prototype($id_prototype, array('seller' => $id_user));

				if(empty($prototype_info)){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				// Check rights for different prototype types
				if( $prototype_info['type_prototype'] == 'inquiry' && !have_right('manage_seller_inquiries')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				if( $prototype_info['type_prototype'] == 'po' && !have_right('manage_seller_po')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				if($prototype_info['status_prototype'] != 'in_progress'){
					jsonResponse(translate('systmess_error_activate_prototype_wrong_status'));
				}

				if($this->prototype->update_prototype($id_prototype, array('changed' => 0))){
					// Change log
					$log = array(
						"date" => date('Y-m-d H:i:s'),
						"message" => 'The prototype has been activated.',
						"user" => 'Seller'
					);
					switch($prototype_info['type_prototype']){
						case 'inquiry':
							$this->load->model('Inquiry_Model', 'inquiry');
							$this->inquiry->update_inquiry($prototype_info['id_request'], array('status' => 'prototype'));
							$this->inquiry->change_inquiry_log($prototype_info['id_request'], json_encode($log));
						break;
						case 'po':
							$this->load->model('PO_Model', 'po');
                			$this->po->update_po($prototype_info['id_request'], array('status' => 'po_processing'));
							$this->po->change_po_log($prototype_info['id_request'], json_encode($log));
						break;
					}

					// Change prototype log
					$this->prototype->change_prototype_log($id_prototype, '{"date":"'.date('m/d/Y H:i:s').'","message":"The prototype has been activated."}');

					if(intVal($_POST['send'])){

						$data_systmess = [
							'mess_code' => 'prototype_activated',
							'id_users'  => [$prototype_info['id_buyer']], //array
							'replace'   => [
								'[PROTOTYPE_ID]'   => orderNumber($id_prototype),
								'[PROTOTYPE_LINK]' => __SITE_URL . 'prototype/item/' . $id_prototype
							],
							'systmess' => true
						];


						$this->notify->send_notify($data_systmess);
					}

					jsonResponse(translate('systmess_success_activate_prototype'), 'success', array('id_request' => $prototype_info['id_request']));
				} else{
					jsonResponse(translate('systmess_internal_server_error'));
				}
			break;
			case 'confirm_prototype':
				if(!have_right('buy_item'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$prototype_info = $this->prototype->get_prototype($id_prototype, array('buyer' => $id_user));

				if(empty($prototype_info))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				if($prototype_info['status_prototype'] != 'in_progress')
					jsonResponse(translate('systmess_error_confirm_prototype_wrong_status'));

				if($this->prototype->update_prototype($id_prototype, array('status_prototype' => 'accepted'))){

					// Change Inquiry/Producing Requests log
					$log = array(
						"date" => date('Y-m-d H:i:s'),
						"message" => 'Prototype has been accepted.',
						"user" => 'Buyer'
					);
					switch($prototype_info['type_prototype']){
						case 'po':
							$this->load->model('PO_Model', 'po');
							$this->po->update_po($prototype_info['id_request'], array('status' => 'prototype_confirmed', 'price' => $prototype_info['price']));
							$this->po->change_po_log($prototype_info['id_request'], json_encode($log));
							$type = 'po';
						break;
						case 'inquiry':
							$this->load->model('Inquiry_Model', 'inquiry');
							$this->inquiry->update_inquiry($prototype_info['id_request'], array('status' => 'prototype_confirmed', 'price' => $prototype_info['price']));
							$this->inquiry->change_inquiry_log($prototype_info['id_request'], json_encode($log));
							$type = 'inquiry';
						break;
					}

					// Send notify to seller
					$this->load->model('Notify_Model', 'notify');

					$data_systmess = [
						'mess_code' => 'prototype_confirmed',
						'id_users'  => [$prototype_info['id_seller']],
						'replace'   => [
							'[USER]'            => 'buyer',
							'[PROTOTYPE_LINK]'  => __SITE_URL . 'prototype/item/' . $prototype_info['id_prototype'],
							'[PROTOTYPE_TITLE]' => cleanOutput($prototype_info['title'])
						],
						'systmess' => true
					];

					$this->notify->send_notify($data_systmess);

					$this->prototype->change_prototype_log($id_prototype, '{"date":"'.date('m/d/Y H:i:s').'","message":"The prototype has been accepted."}');
					jsonResponse(translate('systmess_success_confirm_prototype'), 'success');
				}else
					jsonResponse(translate('systmess_internal_server_error'));
			break;
			case 'decline_prototype':
				if(!have_right('buy_item'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$prototype_info = $this->prototype->get_prototype($id_prototype, array('buyer' => $id_user));

				if(empty($prototype_info))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				if($prototype_info['status_prototype'] != 'in_progress')
					jsonResponse(translate('systmess_error_decline_prototype_wrong_status'));

				if($this->prototype->update_prototype($id_prototype, array('status_prototype' => 'declined'))){

					$log = array(
						"date" => date('Y-m-d H:i:s'),
						"message" => 'Inquiry has been declined.',
					);
					$this->load->model('User_Statistic_Model', 'statistic');
					switch($prototype_info['type_prototype']){
						case 'po':
							$this->load->model('PO_Model', 'po');
							$this->po->update_po($prototype_info['id_request'], array('status' => 'declined'));
							$this->statistic->set_users_statistic(
								array(
									$prototype_info['id_seller']	=> array('po_declined' => 1),
									$prototype_info['id_buyer']		=> array('po_declined' => 1)
								)
							);
							$this->po->change_po_log($prototype_info['id_request'], json_encode($log));
						break;
						case 'inquiry':
							$this->load->model('Inquiry_Model', 'inquiry');
							$this->inquiry->update_inquiry($prototype_info['id_request'], array('status' => 'declined'));
							$this->statistic->set_users_statistic(
								array(
									$prototype_info['id_seller']	=> array('inquiries_declined' => 1),
									$prototype_info['id_buyer']		=> array('inquiries_declined' => 1)
								)
							);
							$this->inquiry->change_inquiry_log($prototype_info['id_request'], json_encode($log));
						break;
					}

					$this->prototype->change_prototype_log($id_prototype, '{"date":"'.date('m/d/Y H:i:s').'","message":"The prototype has been declined."}');
					jsonResponse(translate('systmess_success_decline_prototype'), 'success');
				}else{
					jsonResponse(translate('systmess_internal_server_error'));
				}

			break;
			case 'edit_prototype':
				if(!have_right_or('manage_seller_inquiries,manage_seller_po')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$prototype_info = $this->prototype->get_prototype($id_prototype);

				// Check rights for different prototype types
				if( $prototype_info['type_prototype'] == 'inquiry' && !is_privileged('user', $prototype_info['id_seller'], 'manage_seller_inquiries')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				} elseif( $prototype_info['type_prototype'] == 'po' && !is_privileged('user', $prototype_info['id_seller'], 'manage_seller_po')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

				if ($prototype_info['status_prototype'] != 'in_progress') {
					jsonResponse(translate('systmess_error_edit_prototype_wrong_status'));
				}

				$validation_rules = array(
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'valide_title' => '', 'min_len[4]' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'price',
						'label' => 'Price in USD',
						'rules' => array('required' => '', 'positive_number' => '', 'min[1]' => '')
					),
					array(
						'field' => 'quantity',
						'label' => 'Quantity',
						'rules' => array('required' => '', 'natural' => '')
					),
					array(
						'field' => 'weight',
						'label' => 'Weight',
						'rules' => array('required' => '', 'positive_number' => '')
					),
					array(
						'field' => 'length',
						'label' => 'Length',
						'rules' => array('required' => '', 'item_size' => '')
					),
					array(
						'field' => 'width',
						'label' => 'Width',
						'rules' => array('required' => '', 'item_size' => '')
					),
					array(
						'field' => 'height',
						'label' => 'Height',
						'rules' => array('required' => '', 'item_size' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validation_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$prototype_length = floatval($_POST['length']);
				$prototype_width = floatval($_POST['width']);
				$prototype_height = floatval($_POST['height']);
				$prototype_weight = floatval($_POST['weight']);

				if($prototype_length <= 0){
					jsonResponse(translate('systmess_error_edit_prototype_wrong_length'));
				}

				if($prototype_width <= 0){
					jsonResponse(translate('systmess_error_edit_prototype_wrong_width'));
				}

				if($prototype_height <= 0){
					jsonResponse(translate('systmess_error_edit_prototype_wrong_height'));
				}

				if($prototype_weight <= 0){
					jsonResponse(translate('systmess_error_edit_prototype_wrong_weight'));
				}

				$this->load->library('Cleanhtml', 'clean');
				$prototype_logs = explode(',', $prototype_info['log']);
				$prototype_logs[] = '{"date":"'.date('m/d/Y H:i:s').'","message":"The prototype has been updated."}';
				$update = array(
					'title' => cleanInput($_POST['title']),
					'price' => floatval($_POST['price']),
					'quantity' => (int) $_POST['quantity'],
					'prototype_length' => $prototype_length,
					'prototype_width' => $prototype_width,
					'prototype_height' => $prototype_height,
					'prototype_weight' => $prototype_weight,
					'description' => $this->clean->sanitizeUserInput($_POST['description']),
					'log' => implode(',', $prototype_logs),
					'changes' => ''
				);

				$attributes = array();
				if(!empty($prototype_info['changes'])){
					$changes = json_decode($prototype_info['changes'], true);
					foreach($changes as $key => $attr){
						if(isset($_POST['e_attr'][$key])){
							$attr_name = htmlspecialchars(cleanInput(trim($_POST['e_attr'][$key]['name'])));
							$attr_value = htmlspecialchars(cleanInput(trim($_POST['e_attr'][$key]['current_value'])));
							if (!empty($attr_name) && !empty($attr_value)) {
								$old_value = $attr['old_values'];
								if($attr['current_value'] != $attr_value){
									if($attr['old_values'] != ""){
										$old_value .= ' &raquo; '.$attr['current_value'];
									} else{
										$old_value = $attr['current_value'];
									}
								}

								$attributes[$key] = array(
									'name' => $attr_name,
									'old_values' => $old_value,
									'current_value' => $attr_value
								);
							}
						}
					}
				}

				if(!empty($_POST['u_attr'])){
					foreach($_POST['u_attr']['name'] as $new_key => $u_attr){
						$attr_name = htmlspecialchars(cleanInput(trim($_POST['u_attr']['name'][$new_key])));
						$attr_value = htmlspecialchars(cleanInput(trim($_POST['u_attr']['value'][$new_key])));
						if (!empty($attr_name) && !empty($attr_value)) {
							$attributes[uniqid()] = array(
								'name' => $attr_name,
								'old_values' => '',
								'current_value' => $attr_value
							);
						}
					}
				}

				if(!empty($attributes)){
					$update['changes'] = json_encode($attributes);
				}

				$this->prototype->update_prototype($id_prototype, $update);

				$request_logs = array(
					json_encode(array(
						"date" => date('Y-m-d H:i:s'),
						"message" => 'The prototype has been updated.',
						"user" => 'Seller'
					))
				);

				if($prototype_info['price'] != $update['price'] || $prototype_info['quantity'] != $update['quantity']){
					$update_request = array();
					if($prototype_info['price'] != $update['price'] ){
						$request_logs[] = json_encode(array(
							"date" => date('Y-m-d H:i:s'),
							"message" => 'The prototype price has been changed to: $ '.get_price($update['price'], false),
							"user" => 'Seller'
						));

						$update_request['price'] = $update['price'];
					}

					if($prototype_info['quantity'] != $update['quantity'] ){
						$request_logs[] = json_encode(array(
							"date" => date('Y-m-d H:i:s'),
							"message" => 'The prototype quantity has been changed to: '.$update['quantity'],
							"user" => 'Seller'
						));

						$update_request['quantity'] = $update['quantity'];
					}
				}

				switch($prototype_info['type_prototype']){
					case 'inquiry' :
						$this->load->model('Inquiry_Model', 'inquiry');

						if (!empty($update_request)) {
							$this->inquiry->update_inquiry($prototype_info['id_request'], $update_request);
						}

						$this->inquiry->change_inquiry_log($prototype_info['id_request'], implode(',', $request_logs));
					break;
					case 'po' :
						$this->load->model('PO_Model', 'po');

						if (!empty($update_request)) {
							$this->po->update_po($prototype_info['id_request'], $update_request);
						}

						$this->po->change_po_log($prototype_info['id_request'], implode(',', $request_logs));
					break;
				}

				jsonResponse(translate('systmess_success_edit_prototype'),'success', array('id_request' => $prototype_info['id_request']));
			break;
			case 'upload_photo':
				if(!have_right_or('manage_seller_inquiries,manage_seller_po')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

                $files = arrayGet($_FILES, 'files');
                if (null === $files) {
                    jsonResponse(translate('validation_image_required'));
                }

                if (is_array($files['name'])) {
                    jsonResponse(translate('validation_invalid_file_provided'));
                }

				//verify if is seller item
				$id_prototype = (int)$this->uri->segment(4);
				$prototype_info = $this->prototype->get_prototype($id_prototype, array('seller' => $id_user));
				if(empty($prototype_info)){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				// Check rights for different prototype types
				if( $prototype_info['type_prototype'] == 'inquiry' && !have_right('manage_seller_inquiries')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				} elseif( $prototype_info['type_prototype'] == 'po' && !have_right('manage_seller_po')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				if($prototype_info['status_prototype'] != 'in_progress'){
					jsonResponse(translate('systmess_error_prototype_upload_image_wrong_status'));
				}

				//verify number of existing images for item
				if(!empty($prototype_info['image'])){
					jsonResponse(translate('systmess_error_cannot_upload_more_than_1_image'));
				}

                /**
                 * @todo Refactoring Library [2022-06-02]
                 */

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
                $module = 'items.prototype';

                $prototypePath = ItemPathGenerator::prototypeDirectory($id_prototype);
                $path = $publicDiskPrefixer->prefixPath($prototypePath);
                $publicDisk->createDirectory($prototypePath);

                $res = library('upload')->upload_images_data(array(
                    'files'       => $files,
                    'destination' => $path,
                    'resize'      => config("img.{$module}.resize"),
                    'rules'       => config("img.{$module}.rules"),
                    'thumbs'        => config("img.{$module}.thumbs"),
                    'watermark'     => true,
                    'change_name'   => false,
                ));

				if(!empty($res['errors'])){
					jsonResponse($res['errors']);
				}

				if($this->prototype->update_prototype($id_prototype, array('image' => $res[0]['new_name'], 'changed' => 1))){
					$this->prototype->change_prototype_log($id_prototype, '{"date":"'.date('m/d/Y H:i:s').'","message":"The prototype photo has been changed"}');
					jsonResponse('','success',array('id' => $id_prototype, 'path' => $path.'/'.$res[0]['new_name']));
				} else{
					jsonResponse(translate('systmess_internal_server_error'));
				}
			break;
			case 'delete_photo':
				if(!have_right_or('manage_seller_inquiries,manage_seller_po')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$this->_load_main();

				$id_prototype = (int)$this->uri->segment(4);
				$prototype_info = $this->prototype->get_prototype($id_prototype, array('seller' => $id_user));
				if(empty($prototype_info)){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				// Check rights for different prototype types
				if ($prototype_info['type_prototype'] == 'inquiry' && !have_right('manage_seller_inquiries')) {
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				} else if ($prototype_info['type_prototype'] == 'po' && !have_right('manage_seller_po')) {
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				if ($prototype_info['status_prototype'] != 'in_progress') {
					jsonResponse(translate('systmess_error_prototype_delete_photo_wrong_status'));
				}

				if ($this->prototype->update_prototype($id_prototype, array('image' => ""))) {
                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->deleteDirectory(ItemPathGenerator::prototypeDirectory($id_prototype));
                    } catch (\Throwable $th) {
                        //NOTHIND TO DO
                    }

					jsonResponse(translate('systmess_success_prototype_delete_image'), 'success');
				} else{
					jsonResponse(translate('systmess_internal_server_error'));
				}
			break;
		}
	}

	function popup_forms(){
        if (!isAjaxRequest()){
			headerRedirect();
		}

		if (!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		$this->_load_main();
		$action = $this->uri->segment(3);
		switch ($action) {
			case 'edit_prototype':
				if(!have_right_or('manage_seller_inquiries,manage_seller_po')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

				$id_prototype = (int)$this->uri->segment(4);
				$this->load->model('Company_Model', 'company');

				$data['prototype'] = $this->prototype->get_prototype($id_prototype);
				if(empty($data['prototype'])){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				// Check rights for different prototype types
				if( $data['prototype']['type_prototype'] == 'inquiry' && !is_privileged('user', $data['prototype']['id_seller'], 'manage_seller_inquiries')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				} elseif( $data['prototype']['type_prototype'] == 'po' && !is_privileged('user', $data['prototype']['id_seller'], 'manage_seller_po')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

				if(!empty($data['prototype']['changes'])){
					$data['prototype']['changes'] = json_decode($data['prototype']['changes'], true);
				}

				$filesize = config('fileupload_max_file_size', 10 * 1024 * 1024);
				$mime_properties = getMimePropertiesFromFormats(config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
				$data['fileupload_max_file_size'] = $filesize;
				$data['fileupload'] = array(
					'limits'    => array(
						'width'             => 940,
						'height'            => 125,
						'amount'            => 1,
						'accept'            => arrayGet($mime_properties, 'accept'),
						'formats'           => arrayGet($mime_properties, 'formats'),
						'mimetypes'         => arrayGet($mime_properties, 'mimetypes'),
						'filesize'          => $filesize,
						'filesize_readable' => config('fileupload_max_file_size_placeholder', '10MB'),
					),
					'url'       => array(
						'upload' => __SITE_URL . "prototype/ajax_prototype_operation/upload_photo/{$id_prototype}",
						'delete' => __SITE_URL . "prototype/ajax_prototype_operation/delete_photo",
					),
				);

				$this->view->assign($data);
				$this->view->display('new/prototype/edit_form_view');

			break;
		}
	}
}

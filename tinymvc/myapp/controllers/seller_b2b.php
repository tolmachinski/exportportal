<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seller_b2b_Controller extends TinyMVC_Controller {

	function index() {
		header('location: ' . __SITE_URL);
	}

	function my(){
		if (!logged_in()) {
            headerRedirect(__SITE_URL . 'login');
        }

		if (!i_have_company()) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		checkGroupExpire();

		$this->load->model('Seller_b2b_Model','seller_b2b');

		$data['seller_b2b'] = $this->seller_b2b->get_seller_b2b(privileged_user_id());
        $this->view->assign('title', 'Seller b2b blocks');

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/b2b/my/index_view');
        $this->view->display('new/footer_view');
	}

	function popup_forms(){
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		if(!i_have_company())
			messageInModal(translate("systmess_error_should_have_company_to_perform_this_action"));

		$op = $this->uri->segment(3);
		switch($op){
			case 'edit_block':
				$block = $this->uri->segment(4);

				switch($block){
					case 'about':
						$data['block_title'] = ' "About"';
						$data['block_name'] = 'about';
					break;
					case 'meeting':
						$data['block_title'] = ' "Meeting"';
						$data['block_name'] = 'meeting';
					break;
					case 'phone':
						$data['block_title'] = ' "Phone"';
						$data['block_name'] = 'phone';
					break;
					case 'meeting_else':
						$data['block_title'] = ' "Meeting else"';
						$data['block_name'] = 'meeting_else';
					break;
					case 'purchase_order':
						$data['block_title'] = ' "Purchase order"';
						$data['block_name'] = 'purchase_order';
					break;
					case 'special_order':
						$data['block_title'] = ' "Special order"';
						$data['block_name'] = 'special_order';
					break;
					default:
						messageInModal('Error: You should select block.');
				}

				$this->load->model('Seller_b2b_Model','seller_b2b');
				$data['seller_b2b'] = $this->seller_b2b->get_seller_b2b(privileged_user_id());
				$this->view->assign($data);
                $this->view->display('new/user/seller/b2b/my/edit_about_form_view');
			break;
		}
	}

	function ajax_seller_b2b_operation(){
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$op = $this->uri->segment(3);

		switch($op){
			case 'edit_b2b_block':
				if(!i_have_company())
					jsonResponse(translate("systmess_error_should_have_company_to_perform_this_action"));

				if(!have_right('have_about_info,have_additional_about_info'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'text',
						'label' => 'Description block',
						'rules' => array( 'max_len[500]' => '')
					),
					array(
						'field' => 'block_name',
						'label' => 'Name block',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$standart_blocks = array(
					'about',
					'meeting',
					'phone',
					'meeting_else',
					'purchase_order',
					'special_order'
				);

				if(in_array($_POST['block_name'], $standart_blocks, TRUE)){

					$update_column = array(
						'block_name' => cleanInput($_POST['block_name']),
						'value'	=> cleanInput($_POST['text'])
					);

					$this->load->model('Seller_b2b_Model','seller_b2b');
					if($this->seller_b2b->update_seller_b2b(privileged_user_id(), $update_column)){
						$resp['text_block'] = $update_column['value'];
						$resp['update_block'] = cleanInput($_POST['block_name']);

						jsonResponse('Your changes have been saved and will be visible soon.','success',$resp);
					} else{
						jsonResponse('Error: you cannot save the changes now. Please try again later.');
					}
				} else{
					jsonResponse('Error: Insufficient block information. Please re-open this form.');
				}
			break;
		}
	}


}

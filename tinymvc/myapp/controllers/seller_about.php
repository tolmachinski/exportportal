<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seller_About_Controller extends TinyMVC_Controller
{
	public function index()
	{
		// None shall pass!
		headerRedirect("/404");
	}

	public function popup_forms()
	{
		if(!isAjaxRequest()){
			headerRedirect();
		}

		if(!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		if(!i_have_company())
			messageInModal(translate("systmess_error_should_have_company_to_perform_this_action"));

		if(!have_right('have_about_info,have_additional_about_info'))
			messageInModal(translate("systmess_error_rights_perform_this_action"));

		$this->load_main();

		$op = $this->uri->segment(3);
		switch($op){
			case 'edit_block':
				$block = $this->uri->segment(4);
				switch($block){
					case 'about':
						$data['block_title'] = ' "About Us"';
						$data['block_name'] = 'about_us';
						break;
					case 'history':
						$data['block_title'] = ' "History"';
						$data['block_name'] = 'history';
						break;
					case 'we_sell':
						$data['block_title'] = ' "Main products lines / services"';
						$data['block_name'] = 'what_we_sell';
						break;
					case 'prod_process_management':
						$data['block_title'] = ' "Production process management"';
						$data['block_name'] = 'prod_process_management';
						break;
					case 'research_develop_abilities':
						$data['block_title'] = ' "Research and develop abilities"';
						$data['block_name'] = 'research_develop_abilities';
						break;
					case 'development_expansion_plans':
						$data['block_title'] = ' "Company development / expansion plans"';
						$data['block_name'] = 'development_expansion_plans';
						break;
					case 'production_flow':
						$data['block_title'] = ' "Production flow"';
						$data['block_name'] = 'production_flow';
					break;
				}
				$data['about_block'] = $this->seller_about->getPageAbout(privileged_user_id());
				$this->view->assign($data);

                $this->view->display('new/user/seller/edit_about_form_view');
			break;
			case 'add_about_block':
                $this->view->display('new/user/seller/contacts/about_block_form_view');
			break;
			case 'edit_about_block':
				$block = intVal($this->uri->segment(4));
				$data['block'] = $this->seller_about->getAboutAditionalBlock(privileged_user_id(), $block);
				if(!count($data['block']))
					messageInModal(translate('systmess_error_invalid_data'));

				$this->view->assign($data);

                $this->view->display('new/user/seller/contacts/about_block_form_view');
			break;
			case 'view_text_block' :
				$block_name = $this->uri->segment(4);

				$about_block = $this->seller_about->getPageAbout(privileged_user_id());

				if(empty($about_block["text_{$block_name}"]))
					messageInModal('Error: Block was not found.');

				$data['block'] = $about_block["text_{$block_name}"];
				$this->view->assign($data);
				$this->view->display('new/user/seller/contacts/view_block_view');
			break;
			case 'view_additional_text_block' :
				$block = intVal($this->uri->segment(4));
				$about_block = $this->seller_about->getAboutAditionalBlock(privileged_user_id(), $block);

				if(empty($about_block))
					messageInModal('Error: Block was not found.');

				$data['block'] = $about_block['text_block'];
				$this->view->assign($data);
				$this->view->display('new/user/seller/contacts/view_block_view');
			break;
			case 'view_text_block' :
				$block_name = $this->uri->segment(4);

				$about_block = $this->seller_about->getPageAbout(privileged_user_id());

				if(empty($about_block["text_{$block_name}"]))
					messageInModal('Error: Block was not found.');

				$data['block'] = $about_block["text_{$block_name}"];
				$this->view->assign($data);
				$this->view->display('new/user/seller/contacts/view_block_view');
			break;
			case 'view_additional_text_block' :
				$block = intVal($this->uri->segment(4));
				$about_block = $this->seller_about->getAboutAditionalBlock(privileged_user_id(), $block);

				if(empty($about_block))
					messageInModal('Error: Block was not found.');

				$data['block'] = $about_block['text_block'];
				$this->view->assign($data);
				$this->view->display('new/user/seller/contacts/view_block_view');
			break;
		}
	}

	public function ajax_about_operation()
	{
		if(!isAjaxRequest()){
			headerRedirect();
		}

		$this->load_main();
		$op = $this->uri->segment(3);
		switch($op){
			case 'unlock_email':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_company = intval($_POST['id']);

				if(empty($id_company))
					jsonResponse('Error: Incorrect data sent.');

				$email = $this->company->get_company_service_email($id_company);

				jsonResponse('','success',array('block_info' => '<span>'.$email.'</span>'));
			break;
			case 'unlock_phone':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_company = intval($_POST['id']);

				if(empty($id_company))
					jsonResponse('Error: Incorrect data sent.');

				$phone = $this->company->get_company_service_phone($id_company);

				jsonResponse('','success',array('block_info' => '<span>'.$phone.'</span>'));
			break;
			case 'unlock_fax':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_company = intval($_POST['id']);

				if(empty($id_company))
					jsonResponse('Error: Incorrect data sent.');

				$fax = $this->company->get_company_service_fax($id_company);

				jsonResponse('','success',array('block_info' => '<span>'.$fax.'</span>' ));
			break;
			case 'add_about_block':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if(!i_have_company()){
					jsonResponse(translate("systmess_error_should_have_company_to_perform_this_action"));
				}
				if(!have_right('have_about_info,have_additional_about_info'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				is_allowed("freq_allowed_about_operations");

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Title block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'text',
						'label' => 'Description block',
						'rules' => array('required' => '', 'html_max_len[2000]' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$this->load->library('Cleanhtml', 'cleanhtml');

				$insertColumn = array(
					'id_seller'	    => privileged_user_id(),
					'id_company'	=> my_company_id(),
					'text_block'	=> $this->cleanhtml->sanitizeUserInput($_POST['text']),
					'title_block'	=> cleanInput($_POST['title']),
					'date_added'	=> date('Y-m-d H:i:s')
				);

				if($this->seller_about->setAboutAditionalBlock($insertColumn)){
					model('complete_profile')->update_user_profile_option(privileged_user_id(), 'company_about');

                    /** @var TinyMVC_Library_Auth $authenticationLibrary */
                    $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                    $authenticationLibrary->setUserCompleteProfile(privileged_user_id());

					jsonResponse(translate('systmess_success_company_info_block_add'),'success');
				} else{
					jsonResponse(translate('systmess_error_company_info_block_add'));
				}
			break;
			case 'edit_about_aditional_block':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if(!i_have_company())
					jsonResponse(translate("systmess_error_should_have_company_to_perform_this_action"));

				if(!have_right('have_about_info,have_additional_about_info'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Title block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'text',
						'label' => 'Description block',
						'rules' => array('required' => '', 'html_max_len[2000]' => '')
					),
					array(
						'field' => 'block',
						'label' => 'Block id',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_block = intVal($_POST['block']);

				$this->load->library('Cleanhtml', 'cleanhtml');

				$updateColumn = array(
					'text_block'	=> $this->cleanhtml->sanitizeUserInput($_POST['text']),
					'title_block'	=> cleanInput($_POST['title'])
				);
				if($this->seller_about->updateAboutAditionalBlock($id_block, privileged_user_id(), $updateColumn)){
					$updateColumn['block'] = $id_block;
					$updateColumn['block_type'] = 'aditional';
					jsonResponse(translate('systmess_success_seller_about_block_edit'), 'success', $updateColumn);
				} else{
					jsonResponse(translate('systmess_error_company_about_block_edit'));
				}
			break;
			case 'delete_blocks' :
				if(!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if(!i_have_company())
					jsonResponse(translate("systmess_error_should_have_company_to_perform_this_action"));

				if(!have_right('have_about_info,have_additional_about_info'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$ids = array();
				foreach($_POST['services'] as $id)
					$ids[] = intval($id);

				if(!count($ids))
					jsonResponse(translate('systmess_error_invalid_data'));

				if($this->seller_about->deleteAboutAditionalBlock(implode(',', $ids), privileged_user_id())){
					jsonResponse(translate('systmess_success_company_about_blocks_delete'), 'success');
				}else
					jsonResponse(translate('systmess_error_company_about_blocks_delete'));
			break;
			case 'edit_about_block':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if(!i_have_company())
					jsonResponse(translate("systmess_error_should_have_company_to_perform_this_action"));

				if(!have_right('have_about_info,have_additional_about_info'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'text',
						'label' => 'Description block',
						'rules' => array('required' => '', 'html_max_len[2000]' => '')
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

				$standartBlocks = array(
					'about_us',
					'history',
					'what_we_sell',
					'id_card',
					'certificate',
					'prod_process_management',
					'research_develop_abilities',
					'development_expansion_plans',
					'production_flow'
				);
				if(in_array($_POST['block_name'], $standartBlocks, TRUE)){
					$this->load->library('Cleanhtml', 'cleanhtml');

					$updateColumn = array(
						'block_name' => cleanInput('text_'.$_POST['block_name']),
						'value'	=> $this->cleanhtml->sanitizeUserInput($_POST['text'])
					);

					if($this->seller_about->updateAboutBlock(privileged_user_id(), my_company_id(), $updateColumn)){
						$resp['text_block'] = $updateColumn['value'];
						$resp['update_block'] = cleanInput($_POST['block_name']);
						$resp['block_type'] = 'standart';
						model('complete_profile')->update_user_profile_option(privileged_user_id(), 'company_about');

                        /** @var TinyMVC_Library_Auth $authenticationLibrary */
                        $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                        $authenticationLibrary->setUserCompleteProfile(privileged_user_id());

						jsonResponse(translate('systmess_success_seller_about_block_edit'),'success',$resp);
					} else{
						jsonResponse(translate('systmess_error_company_about_block_edit'));
					}
				} else{
					jsonResponse(translate('systmess_error_invalid_data'));
				}
			break;
		}
	}

	private function load_main()
	{
		$this->load->model('Seller_About_Model', 'seller_about');
		$this->load->model('Company_Model', 'company');
	}
}

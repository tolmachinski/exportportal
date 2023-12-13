<?php
/**
 * Company_Services_Controller
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 */
class Company_About_Controller extends TinyMVC_Controller {

	public function index()
    {
		show_404();
	}

	private function load_main(){
		$this->load->model('Company_Model', 'company');
		$this->load->model('Category_Model', 'category');
	}

	function my(){
		if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if(!have_right('have_about_info,have_additional_about_info')){
			$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
			headerRedirect(__SITE_URL);
		}

		if (!i_have_company()) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		checkGroupExpire();

		$this->load_main();
		$this->load->model('Seller_About_Model','seller_about');

		$data['about_page'] = $this->seller_about->getPageAbout(privileged_user_id());

		$this->view->assign('title', 'Company about blocks');
		$this->view->assign($data);

		$this->view->display('new/header_view');
        $this->view->display('new/directory/about/index_view');
        $this->view->display('new/footer_view');
	}

	function ajax_about_list_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('have_about_info,have_additional_about_info');

		$this->load->model('Seller_About_Model','seller_about');

		$conditions = array(
			'per_p' => (int) $_POST['iDisplayLength'],
			'start' => (int) $_POST['iDisplayStart']
		);

		$sort_by = flat_dt_ordering($_POST, array(
			'title_dt' => 'title_block',
			'date_added_dt' => 'date_added',
			'date_updated_dt' => 'date_updated'
		));

		if(!empty($sort_by)){
			$conditions['sort_by'] = $sort_by;
		}

		$conditions['id_company'] = my_company_id();
		$conditions['id_seller'] = privileged_user_id();

		$count_about_blocks = $this->seller_about->countPageAboutAditional($conditions);
		$data['about_blocks'] = $this->seller_about->getPageAboutAditional($conditions);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $count_about_blocks,
			"iTotalDisplayRecords" => $count_about_blocks,
			"aaData" => array()
		);

		if(empty($data['about_blocks'])) {
			jsonResponse('', 'success', $output);
        }

        $output['aaData'] = $this->_my_company_about($data);

		jsonResponse('', 'success', $output);
	}

	private function _my_company_about($data){
		extract($data);

		foreach ($about_blocks as $block) {
			$output[] = array(
				"title_dt"   => $block['title_block'],
				"date_added_dt" => formatDate($block['date_added']),
				"date_updated_dt" => formatDate($block['date_updated']),
				"description_dt" => $block['text_block'],
				"actions_dt" =>
					'<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block" title="Edit block" href="seller_about/popup_forms/edit_about_block/' . $block['id_block'] . '">
								<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
							</a>
							<a class="dropdown-item confirm-dialog" data-callback="delete_block" data-message="' . translate("systmess_confirm_delete_this_block") . '" title="Delete update" data-block="' . $block['id_block'] . '">
								<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax" title="Preview" data-title="Preview" href="seller_about/popup_forms/view_additional_text_block/' . $block['id_block'] . '">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
						</div>
					</div>'
			);
		}

		return $output;
	}
}

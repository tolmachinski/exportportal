<?php

/**
 * Banner controller
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \Cleanhtml                       $clean
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \Translations_Model              $translations
 * @property \Banner_Model                    $banner
 *
 */
class Banner_Controller extends TinyMVC_Controller
{
    public function index()
    {
		show_404();
	}

    public function administration()
    {
		checkAdmin('moderate_content');

		$data['title'] = 'Moderate Banners';
        $data['languages'] = $this->translations->get_allowed_languages(array('skip' => array('en')));

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/banner/index_view');
		$this->view->display('admin/footer_view');
    }

    public function ajax_operation()
    {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		if (!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

		$op = $this->uri->segment(3);
		switch ($op) {
            case 'administration_list_dt':
                checkAdminAjaxDT('moderate_content');

				$this->load->model('Faq_Model', 'faq');
				$this->load->model('Banner_Model', 'banner');

                $conditions = array();

				$from = (int)$_POST['iDisplayStart'];
				$till = (int)$_POST['iDisplayLength'];
				$conditions['limit'] = $from . ',' . $till;
				$conditions['lang'] = 'en';

                $conditions['sort_by'] = flat_dt_ordering($_POST, array(
                    'dt_id' => 'id',
                    'dt_name' => 'name',
                    'dt_type' => 'type',
                    'dt_link' => 'link'
                ));

                if(empty($conditions['sort_by'])) {
                    $conditions['sort_by'] = ["id-asc"];
                }

                $records = $this->banner->get_banners($conditions);
				$records_total = $this->banner->count($conditions);

				$output = array(
					'sEcho' => (int)$_POST['sEcho'],
					'iTotalRecords' => $records_total,
					'iTotalDisplayRecords' => $records_total,
					'aaData' => []
				);

				if(empty($records)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($records as $record) {

                    $actions = [];

                    if(have_right('moderate_content')) {
                        $actions[] = '<a class="ep-icon ep-icon_star fancyboxValidateModalDT fancybox.ajax"
                                        title="Preview Banner"
                                        href="'.__SITE_URL . 'banner/popup_forms/preview/'.$record['id'].'"
                                        data-title="Preview banner">
                                    </a>';
                        $actions[] = '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                                        title="Edit Banner"
                                        href="'.__SITE_URL . 'banner/popup_forms/edit/'.$record['id'].'"
                                        data-title="Edit banner">
                                    </a>';
                        $actions[] = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                        data-callback="delete_banner"
                                        data-message="' . translate("systmess_confirm_delete_this_banner") . '"
                                        title="Delete Banner"
                                        data-id_banner="'.$record['id'].'">
                                    </a>';
                    }

                    if(empty($actions)){
                        $actions[] = '&mdash;';
                    }

					$output['aaData'][] = array(
						'dt_id'          => $record['id'],
						'dt_name'        => $record['name'],
						'dt_type'        => $record['type_name'],
						'dt_link'        => $record['link'],
                        'dt_actions'     => implode(' ', $actions),
					);
				}

				jsonResponse('', 'success', $output);
            break;

            case 'add':
                checkAdminAjax('moderate_content');

				$validator = $this->validator;

				$validator_rules = [
					[
						'field' => 'name',
						'label' => 'Template name',
						'rules' => ['required' => '', 'max_len[250]' => '']
					],
					[
						'field' => 'type',
						'label' => 'Type',
						'rules' => ['required' => '']
					]
				];

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$data_add = [
					'name' => cleanInput($_POST['name']),
					'type' => (int) $_POST['type'],
					'link' => cleanInput($_POST['link']),
					'html_banner' => $_POST['html_banner'],
                ];

				$this->load->model('Banner_Model', 'banner');
				if ($this->banner->create_banner($data_add)) {
					jsonResponse(translate("systmess_success_banner_success_added"), 'success');
                } else {
					jsonResponse(translate("systmess_error_banner_wasnt_added"));
                }
            break;

            case 'edit':
                checkAdminAjax('moderate_content');

				$validator = $this->validator;

				$validator_rules = [
					[
						'field' => 'name',
						'label' => 'Template name',
						'rules' => ['required' => '', 'max_len[250]' => '']
					],
					[
						'field' => 'type',
						'label' => 'Type',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'id_baner',
						'label' => 'id',
						'rules' => ['required' => '']
					]
				];

				$this->validator->set_rules($validator_rules);

				$id_banner = $_POST['id_banner'];

				$this->load->model('Banner_Model', 'banner');

                $banner = $this->banner->get_banner($id_banner);
                if(empty($banner)) {
					jsonResponse(translate("systmess_error_banner_doesnt_exist"));
                }

				$data_update = [
					'name' => cleanInput($_POST['name']),
					'type' => (int) $_POST['type'],
					'link' => cleanInput($_POST['link']),
					'html_banner' => $_POST['html_banner'],
                ];

				if ($this->banner->update_banner($id_banner, $data_update)) {
					jsonResponse(translate("systmess_success_banner_success_changed"), 'success');
                } else {
					jsonResponse(translate("systmess_error_banner_wasnt_updated"));
                }
            break;

            case 'delete':
                checkAdminAjax('moderate_content');

				$this->load->model('Banner_Model', 'banner');
				$id_banner = (int)$_POST['id_banner'];
				if(!$this->banner->validate_banner_id($id_banner)){
					jsonResponse(translate("systmess_error_banner_doesnt_exist"));
				}

				if ($this->banner->delete_banner($id_banner)){
					jsonResponse(translate("systmess_success_banner_success_deleted"), 'success');
				} else {
					jsonResponse(translate("systmess_error_db_insert_error"));
				}
			break;
		}
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()){
			headerRedirect();
        }

		if (!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		$this->load->model('Banner_Model', 'banner');

		$op = $this->uri->segment(3);

		switch ($op) {
            case 'preview':
                checkAdminAjaxModal('moderate_content');

				$id_banner = (int) $this->uri->segment(4);
                $data['banner'] = $this->banner->get_banner($id_banner);

				$this->view->assign($data);
				$this->view->display('admin/banner/popup_preview_view');
            break;
            case 'create':
                checkAdminAjaxModal('moderate_content');
                $data['banner_types'] = $this->banner->get_types();

                $this->view->assign($data);
				$this->view->display('admin/banner/popup_form_view');
			break;
            case 'edit':
                checkAdminAjaxModal('moderate_content');

				$id_banner = (int) $this->uri->segment(4);
                $data['banner'] = $this->banner->get_banner($id_banner);
                $data['banner_types'] = $this->banner->get_types();

				$this->view->assign($data);
				$this->view->display('admin/banner/popup_form_view');
			break;
		}
	}
}

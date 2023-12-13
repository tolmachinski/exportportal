<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Api_keys_Controller extends TinyMVC_Controller {
    public function index()
    {
		show_404();
	}

	public function administration(){
		checkAdmin('manage_content');

		$this->view->assign('title', 'API Keys');
		$this->view->display('admin/header_view');
		$this->view->display('admin/api_keys/api_keys_view');
		$this->view->display('admin/footer_view');
	}

	public function ajax_list_dt() {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkPermisionAjaxModal('moderate_content');

        /**
         * @var Api_keys_Model $apiKeysModel
         */
        $apiKeysModel = model(Api_keys_Model::class);

        $order = array_column(dt_ordering($_POST, [
            'dt_id_key' => 'id_key',
            'dt_domain' => 'domain',
            'dt_registered' => 'registered',
            'dt_id_key' => 'id_key',
        ]), 'direction', 'column');

		$conditions = dtConditions($_POST, [
            ['as' => 'date_from',       'key' => 'start_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'date_to',         'key' => 'start_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'search',          'key' => 'search',          'type' => 'cleanInput'],
            ['as' => 'enable',          'key' => 'enable',          'type' => 'int'],
            ['as' => 'moderated',       'key' => 'moderated',       'type' => 'int'],
            ['as' => 'domain',          'key' => 'domain',          'type' => 'cleanInput'],
        ]);

        $params = array_merge(
            [
                'start_from'    => (int) $_POST['iDisplayStart'],
                'sort_by'       => $order,
                'limit'         => (int) $_POST['iDisplayLength'],
            ],
            $conditions
        );

		$apiKeys = $apiKeysModel->get_api_keys($params);

		$output = [
			"iTotalDisplayRecords"  => $apiKeysModel->getCountApiKeys($params),
			"iTotalRecords"         => $apiKeysModel->get_count(),
			"aaData"                => [],
			"sEcho"                 => (int) $_POST['sEcho'],
        ];

		if (empty($apiKeys)) {
			jsonResponse('', 'success', $output);
        }

		foreach($apiKeys as $apiKey){
			$output['aaData'][] = [
				'dt_id_key' => $apiKey['id_key'] .  '<br/><a class="mt-10 ep-icon ep-icon_plus" rel="api_keys_details" title="View details"></a>',
				'dt_api_key' => $apiKey['api_key'],
				'dt_domain' => "<div class='tal'>"
								   . "<a class='ep-icon ep-icon_filter txt-green dt_filter' data-title='Domain' title='Filter by " . $apiKey['domain'] . "' data-value-text='" . $apiKey['domain']  . "' data-name='domain' data-value='" . $apiKey['domain']  . "'></a>"
								. "</div>"
								. "<div>" . $apiKey['domain'] . "</div>" ,
				'dt_title_client' => $apiKey['title_client'],
				'dt_registered' => getDateFormat($apiKey['registered']),
				'dt_actions' =>  '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit api" href="'. __SITE_URL . 'api_keys/popups_forms_api_key/edit/' . $apiKey['id_key'] . '" data-title="Edit api key"></a>'
						. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" href="#" data-message="' . translate("systmess_confirm_delete_api") . '" data-callback="delete_api" data-status="trash" title="Delete" data-id="' . $apiKey['id_key'] . '"></a>'
						. '<a class="confirm-dialog ep-icon ep-icon_' . (($apiKey['enable'] == 1) ? "visible" : "invisible") . '" href="#" data-message="' . translate("systmess_confirm_want_to_change_visibility_api") . '" data-callback="change_visib" data-status="trash" title="Change visibility" data-id="' . $apiKey['id_key'] . '""></a>'
						. (($apiKey['moderated'] == 0) ?
							   '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog moderate" href="#" data-callback="moderate_api" data-message="' . translate("systmess_confirm_are_you_sure_moderate_api") . '" title="Moderate" data-id="' . $apiKey['id_key'] . '"></a>' :
							   '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated"></a>'),
				'dt_desrcription' => $apiKey['description_client'],
				'dt_enabled' => $apiKey['enable'],
				'dt_moderated' => $apiKey['moderated']

            ];
		}

		jsonResponse('', 'success', $output);
	}

	public function popups_forms_api_key(){
		if (!isAjaxRequest())
			show_404();

		if (!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		if (!have_right('moderate_content'))
			messageInModal(translate("systmess_error_rights_perform_this_action"));

		$action = $this->uri->segment(3);
		switch ($action) {
			case 'add':
				$this->view->display('admin/api_keys/modal_api_key_view');
			break;
			case 'edit':
				$this->load->model('Api_keys_Model', 'api');
				$id = intval($this->uri->segment(4));
				$data['api_key'] = $this->api->get_details($id);
				$this->view->display('admin/api_keys/modal_api_key_view', $data);
			break;
		}
	}

	public function ajax_api_keys_operation(){
		if (!isAjaxRequest())
			show_404();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged_in"));

		if (!have_right('moderate_content'))
			jsonResponse(translate("systmess_error_rights_perform_this_action"));


		$op = $this->uri->segment(3);
		if(empty($op))
			jsonResponse(translate("systmess_error_cannot_perform_this_action"));

		$this->load->model('Api_Keys_Model', 'api_key');

		switch($op){
			case 'moderate_api_key':
				$id = intval($_POST['id_key']);
				if(!$id)
					jsonResponse(translate("systmess_error_api_key_invalid"));

				if($this->api_key->moderate_api_key($id))
					jsonResponse(translate("systmess_success_api_was_moderated") , 'success');
				else
					jsonResponse(translate("systmess_error_cannot_save_api_now"));
			break;
			case 'delete_api_key':
				$id = intval($_POST['id_key']);
				if(!$id)
					jsonResponse(translate("systmess_error_sended_data_not_valid"));

				if($this->api_key->delete_api_key($id))
					jsonResponse(translate("systmess_success_api_was_deleted") , 'success');
				else
					jsonResponse(translate("systmess_error_cannot_delete_api"));
			break;
			case 'change_state_api_key':
				$id = intval($_POST['id_key']);
				$enabled = intval($_POST['state']);
				if(!$id)
                    jsonResponse(translate("systmess_error_sended_data_not_valid"));

				if($this->api_key->changeVisibility($id, $enabled))
                    translate("systmess_success_changed_visibility_state", ["[STATE]" => ($enabled == 1) ? 'enabled' : 'disabled']);
				else
					jsonResponse(translate("systmess_error_cannot_change_api_state"));
			break;
			case 'edit':
				$validator_rules = array(
					array(
						'field' => 'domain',
						'label' => 'Domain Name',
						'rules' => array('required' => '')
					),array(
						'field' => 'title_client',
						'label' => 'Title client',
						'rules' => array('required' => '')
					),array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					),array(
						'field' => 'moderated',
						'label' => 'Moderated state',
						'rules' => array('required' => '')
					),array(
						'field' => 'enable',
						'label' => 'Enable state',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$data = array(
					'domain' => cleanInput($_POST['domain']),
					'title_client' => cleanInput($_POST['title_client']),
					'description_client' => cleanInput($_POST['description']),
					'moderated' => intval($_POST['moderated']),
					'enable' => intval($_POST['enable'])
				);
				$id_key = intval($_POST['id_key']);

				if($this->api_key->edit_api_key($data, $id_key)){
					jsonResponse(translate("systmess_success_changes_has_been_saved") , 'success');
				}else{
					jsonResponse(translate("systmess_error_cannot_save_api_now"));
				}

			break;
			case 'insert':
				$validator_rules = array(
					array(
						'field' => 'domain',
						'label' => 'Domain Name',
						'rules' => array('required' => '')
					),array(
						'field' => 'title_client',
						'label' => 'Title client',
						'rules' => array('required' => '')
					),array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$data = array(
					'domain' => cleanInput($_POST['domain']),
					'title_client' => cleanInput($_POST['title_client']),
					'description_client' => cleanInput($_POST['description'])
				);
				$data['api_key'] = md5( $data['domain'] . date("Y-m-d H:i:s") );

				if($this->api_key->insert_api_key($data)){
                    jsonResponse(translate("systmess_success_insert_api_key", ["[KEY]" => $data["api_key"]]), 'success');
				} else {
					jsonResponse(translate("systmess_error_cannot_save_api_now"));
				}
			break;

		}

	}
}

?>

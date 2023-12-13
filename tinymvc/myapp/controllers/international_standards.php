<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class International_standards_Controller extends TinyMVC_Controller {
	private function _load_main(){
		$this->load->model('International_standards_Model', 'international_standards');
	}

	function index() {
		headerRedirect('library_international_standards');
	}

	function detail() {
        // redirect 301 - on 04.20.2018
        $link = str_replace('international_standards', 'library_international_standards', $_SERVER['REQUEST_URI']);
        headerRedirect($link, 301);
	}

	function administration() {
		checkAdmin('manage_international_standards');
        $this->_load_main();

		$data['countries'] = $this->international_standards->get_countries();

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/international_standards/index_view');
        $this->view->display('admin/footer_view');
	}

	function administration_dt(){
		if (!isAjaxRequest())
			headerRedirect();

        checkAdminAjaxDT('manage_international_standards');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id'      => 'id_standard',
                'dt_country' => 'country',
                'dt_title'   => 'standard_title'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'id_country', 'key' => 'country', 'type' => 'int'],
            ['as' => 'keywords', 'key' => 'search', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["standard_country-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

		$this->_load_main();

		$records = $this->international_standards->get_standards($params);
		$records_count = $this->international_standards->count_standards($params);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $records_count,
			"iTotalDisplayRecords" => $records_count,
			'aaData' => array()
		);

		foreach ($records as $record) {
            $country = '---';
            if (!empty($record['country'])) {
                $country = '<div class="tal">
								<a class="ep-icon ep-icon_filter txt-green dt_filter" data-value-text="' . $record['country'] . '" data-value="' . $record['standard_country'] . '" title="Filter by ' . $record['country'] . '" data-title="Country" data-name="country"></a>
							</div>
							<img width="24" height="24" src="' . getCountryFlag($record['country']) . '" title="' . $record['country'] . '" alt="' . $record['country'] . '">';
            }

			$output['aaData'][] = array(
				'dt_id' => $record['id_standard'],
				'dt_title' => $record['standard_title'],
				'dt_country' => $country,
                'dt_actions' => '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax fs-16" href="'.__SITE_URL.'international_standards/popup_forms/edit_standard/'.$record['id_standard'].'" title="Edit standard" data-title="Edit standard"></a>
                                <a class="ep-icon ep-icon_remove txt-red confirm-dialog fs-16" href="#" data-callback="delete_standard" data-standard="'.$record['id_standard'].'" title="Delete standard" data-message="Are you sure you want to delete this standard?"></a>'
			);
		}

		jsonResponse('', 'success', $output);
	}

    function popup_forms(){
		$this->_load_main();

		$action = $this->uri->segment(3);
		$id_user = privileged_user_id();
        switch($action){
            case 'add_standard':
				checkAdminAjaxModal('manage_international_standards');
				$data['countries'] = $this->international_standards->get_countries();
				$this->view->assign($data);
				$this->view->display('admin/international_standards/add_standard_view');
            break;
            case 'edit_standard':
				checkAdminAjaxModal('manage_international_standards');

                $id_standard = intVal($this->uri->segment(4));
                $data['standard'] = $this->international_standards->get_standard($id_standard);
                if(empty($data['standard'])){
                    messageInModal('Error: The standard does not exist.');
                }

				$data['countries'] = $this->international_standards->get_countries();

				$this->view->assign($data);
				$this->view->display('admin/international_standards/edit_standard_view');
            break;
        }
    }

	function ajax_operations(){
		if (!isAjaxRequest())
			headerRedirect();

		$this->_load_main();
		$action = $this->uri->segment(3);
		switch ($action) {
			case 'add_standard':
				checkAdminAjax('manage_international_standards');

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

                $insert = array(
                    'standard_title' => cleanInput($_POST['title']),
                    'standard_description' => $_POST['description'],
					'standard_country' => intval($_POST['country'])
                );

				$this->international_standards->insert_standard($insert);
                jsonResponse('Success: The standard has been added.', 'success');
			break;
			case 'edit_standard':
				checkAdminAjax('manage_international_standards');

				$validator_rules = array(
					array(
						'field' => 'id_standard',
						'label' => 'Standard info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$id_standard = intval($_POST['id_standard']);
                $update = array(
                    'standard_title' => cleanInput($_POST['title']),
                    'standard_description' => $_POST['description'],
					'standard_country' => intval($_POST['country'])
                );

				$this->international_standards->update_standard($id_standard, $update);
                jsonResponse('Success: The changes has been saved.', 'success');
			break;
			case 'delete_standard':
				checkAdminAjax('manage_international_standards');

				$validator_rules = array(
					array(
						'field' => 'id_standard',
						'label' => 'Standard info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

                $id_standard = intVal($_POST['id_standard']);
                $this->international_standards->delete_standard($id_standard);
                jsonResponse('Success: The standard has been deleted.', 'success');
			break;
		}
	}
}

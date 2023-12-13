<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Customs_requirements_Controller extends TinyMVC_Controller {

    private $breadcrumbs= array();

    function index() {
        header('location: ' . __SITE_URL);
    }

    function administration(){
        checkAdmin('manage_content');

        $this->load->model('Country_Model', 'country');

        $data['countries'] = $this->country->get_countries();

        $this->view->assign($data);
        $this->view->assign('title', 'Customs requirements');
        $this->view->display('admin/header_view');
        $this->view->display('admin/customs_requirements/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_customs_requirements_administration(){
        checkAdminAjaxDT('manage_content');

        $this->load->model('Requirement_Model', 'requirement');

        $params['per_p'] = intVal($_POST['iDisplayLength']);
        $params['start'] = intVal($_POST['iDisplayStart']);
        $params['sort_by'] = flat_dt_ordering($_POST, [
            'dt_id_requirement' => 'cat.id_customs_req',
            'dt_country'        => 'pc.country',
            'dt_visible'        => 'cat.visible'
        ]);

        $params = array_merge($params,
            dtConditions($_POST, [
                ['as' => 'country', 'key' => 'country', 'type' => 'cleanInput'],
                ['as' => 'visible', 'key' => 'visible_requirement', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
            ])
        );

        $requirements = $this->requirement->get_requirements($params);
        $requirements_count = $this->requirement->counter_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $requirements_count,
            "iTotalDisplayRecords" => $requirements_count,
			'aaData' => array()
        );

        if(empty($requirements))
			jsonResponse('', 'success', $output);

		foreach($requirements as $requirement){

			$visible_btn = 'ep-icon_visible';
			if (!$requirement['visible'])
				$visible_btn = 'ep-icon_invisible';

			$content = "";
			if (strlen($requirement['customs_text']) > 70)
				$content = '<p class="tac"><a class="btn-customs-req-more ep-icon ep-icon_arrows-down" href="#" title="view more"></a></p>';

			$output['aaData'][] = array(
				'dt_id_requirement'=> $requirement['id_customs_req'],
				'dt_country'    =>  '<div class="tal">
										<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Country" title="Filter by country" data-value-text="' . $requirement['country'] . '" data-value="' . $requirement['id_country'] . '" data-name="country"></a>
									</div>
									<a href="/search/country/'.strForURL($requirement['country']).'-'.$requirement['id_country'].'?keywords='.strForURL($requirement['country']).'" target="_blank">'. $requirement['country'].'</a><br/>
									<img width="24" height="24" src="' . getCountryFlag($requirement['country']) . '" alt="' . $requirement['country'] . '" title="' . $requirement['country'] . '" />',
				'dt_meta_data'  =>  '<span title="' . $requirement['customs_meta_key'] . '">Keywords</span> | <span title="' . $requirement['customs_meta_desc'] . '">Description</span>',
				'dt_text'       =>  '<div class="h-50 hidden-b">' . $requirement['customs_text'] . '</div>' . $content,
				'dt_actions'    =>
									'<a class="ep-icon ' . $visible_btn . ' confirm-dialog" data-callback="change_visible_customs_req" data-record="' . $requirement['id_customs_req'] . '" data-message="Are you sure you want to change the visibility status of this customs requirement?" href="#" title="Set customs requirement ' . ($requirement['visible'] ? 'inactive' : 'active') . '"></a>
									<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="customs_requirements/popup_forms/edit_requirement/'. $requirement['id_customs_req'] . '" data-title="Edit customs requirement" title="Edit this customs requirement"></a>
									<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_customs_req" data-record="' . $requirement['id_customs_req'] . '" title="Remove this customs requirement" data-message="Are you sure you want to delete this customs requirement?" href="#" ></a>',
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms() {
        checkAdminAjaxModal('manage_content');

        $this->load->model('Requirement_Model', 'requirement');
        $this->load->model('Country_Model', 'country');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_requirement':
                $data['port_country'] = $this->country->fetch_port_country();
                $this->view->display('admin/customs_requirements/add_requirement_form_view', $data);
            break;

            case 'edit_requirement':
                $id_record = (int)$this->uri->segment(4);
                $data['record'] = $this->requirement->get_requirement(array('id_record' => $id_record));

                if(empty($data['record']))
                    messageInModal('Error: This customs requirement does not exist.');

                $data['port_country']= $this->country->fetch_port_country();

                $this->view->display('admin/customs_requirements/edit_requirement_form_view', $data);
            break;
        }
    }

    public function ajax_requirement_operation() {
        checkAdminAjax('manage_content');

        $this->load->model('Requirement_Model', 'requirement');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'change_visible_custom_requirement':
                $id_record = intVal($_POST['record']);
                $record = $this->requirement->get_requirement(array('id_record' => $id_record));

                if (empty($record))
                    jsonResponse('Error: This customs requirement does not exist.');

                $update = array();
                if ($record['visible'])
                    $update['visible'] = 0;
                else
                    $update['visible'] = 1;

                if ($this->requirement->update_requirement($id_record, $update))
                    jsonResponse('The customs requirements has been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot change this customs requirement now. Please try again later.');
            break;

            case 'remove_custom_requirement':
                $id_record = intVal($_POST['record']);
                $record = $this->requirement->get_requirement($id_record);
                if (empty($record))
                    jsonResponse('This requirement does not exist.');

                if ($this->requirement->delete_requirement($id_record))
                    jsonResponse('The customs requirements has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this customs requirement now. Please try again later.');
            break;

            case 'edit_requirement':
                $validator_rules = array(
                    array(
                        'field' => 'id_record',
                        'label' => 'Customs requirement info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_key',
                        'label' => 'Meta keywords',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_desc',
                        'label' => 'Meta descripions',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Text',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_record = intVal($_POST['id_record']);
                $exist_record = $this->requirement->exist_requirement_by_condition(array('not_id_record' => $id_record, 'country' => intVal($_POST['country'])));

                if ($exist_record)
                    jsonResponse('Info: The customs requirements info for this country already exists. Please edit the existing requirement.', 'info');

                $update = array(
                    'id_country'        => intVal($_POST['country']),
                    'customs_meta_key'  => cleanInput($_POST['meta_key']),
                    'customs_meta_desc' => cleanInput($_POST['meta_desc']),
                    'customs_text'      => $_POST['text'],
                );

                if (empty($_POST['visible']))
                    $update['visible'] = 0;
                else
                    $update['visible'] = 1;

                if ($this->requirement->update_requirement($id_record, $update))
                    jsonResponse('The customs requirements have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot change this customs requirement now. Please try again later.');
            break;

            case 'save_requirement':

                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_key',
                        'label' => 'Meta keywords',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_desc',
                        'label' => 'Meta descripions',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Text',
                        'rules' => array('required' => '')
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $exist_record = $this->requirement->exist_requirement_by_condition(array('country' => intVal($_POST['country'])));

                if ($exist_record)
                    jsonResponse('Info: The customs requirements info for this country already exists. Please edit the existing requirement.', 'info');

                $insert = array(
                    'id_country'        => intVal($_POST['country']),
                    'customs_meta_key'  => cleanInput($_POST['meta_key']),
                    'customs_meta_desc' => cleanInput($_POST['meta_desc']),
                    'customs_text'      => $_POST['text'],
                );

                if (empty($_POST['visible']))
                    $insert['visible'] = 0;
                else
                    $insert['visible'] = 1;

                $id_record = $this->requirement->set_requirement($insert);

                if ($id_record)
                    jsonResponse('The customs requirements have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot add customs requirement now. Please try again later.');
            break;
        }
    }
}

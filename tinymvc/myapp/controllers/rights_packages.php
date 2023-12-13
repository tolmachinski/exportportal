<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Rights_Packages_Controller extends TinyMVC_Controller {
	function administration() {
		checkAdmin('gr_packages_administration');

		$this->load->model('Packages_Model', 'packages');
		$this->load->model('UserGroup_Model', 'groups');
		$id = $this->uri->segment(4);

		$data = array(
			'periods' => $this->packages->selectPeriods(),
			'groups' => $this->groups->getGroupsByType(array('type' => "'Buyer', 'Seller'")),
			'right_packages' => $this->packages->getRightPackages(),
			'title' => 'Packages'
		);

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/packages/rights_packages/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_rights_packages_dt() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged_in"));

		checkAdminAjaxDT('manage_content');

		$this->load->model('Packages_Model', 'packages');
		$conditions = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart'])
		);

		if (isset($_POST['gr_from']))
			$conditions['gr_from'] = intVal($_POST['gr_from']);

		if (isset($_POST['right']))
			$conditions['right'] = intVal($_POST['right']);

		if ($_POST['iSortingCols'] > 0) {
			for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
				switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
					case 'dt_group':
						$conditions['sort_by'][] = 'gr_name-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_right':
						$conditions['sort_by'][] = 'r_name-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_period':
						$conditions['sort_by'][] = 'full-' . $_POST['sSortDir_' . $i];
					break;
					case 'dt_price':
						$conditions['sort_by'][] = 'price-' . $_POST['sSortDir_' . $i];
					break;
				}
			}
		}

		$right_packages = $this->packages->get_rights_packages($conditions);
		$records_total = $this->packages->get_rights_packages_count($conditions);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $records_total,
			"iTotalDisplayRecords" => $records_total,
			"aaData" => array()
		);

		foreach ($right_packages as $pack) {
			$output['aaData'][] = array(
				'dt_group' => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="From group" title="Filter by '.$pack['gr_name'].'"  data-value-text="'.$pack['gr_name'].'" data-value="'.$pack['group_for'].'" data-name="gr_from"></a><br/>' . $pack['gr_name'] ,
				'dt_right' => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="From group" title="Filter by '.$pack['r_name'].'"  data-value-text="'.$pack['r_name'].'" data-value="'.$pack['id_right'].'" data-name="right"></a><br/>' . $pack['r_name'],
				'dt_period' => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="Period" title="Filter by '.$pack['full'].'"  data-value-text="'.$pack['full'].'" data-value="'.$pack['period'].'" data-name="period"></a><br/>' . $pack['full'],
				'dt_price' => '$'.$pack['price'],
				'dt_actions' => '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Update right package" href="rights_packages/popup_forms/update_right_package/' . $pack['idrpack'] . '" title="Update package info" data-title="Update package info"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red-dark confirm-dialog" data-callback="remove_right_package" data-message="Are you sure you want to delete this right package?"  title="Delete right package" data-id="' . $pack['idrpack'] . '"></a>'
			);
		}

		jsonResponse('', 'success', $output);
	}

	function ajax_rights_packages_operations(){
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }


		$id_user = $this->session->id;
		$op = $this->uri->segment(3);

		switch ($op) {
			case 'edit_right_package':
				checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'group_for',
						'label' => 'Group for',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'right',
						'label' => 'Right',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'period',
						'label' => 'Period',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'price',
						'label' => 'Price',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'id',
						'label' => 'Id',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->model('Packages_Model', 'packages');

				$update = array(
					'group_for' => intVal($_POST['group_for']),
					'id_right' => intVal($_POST['right']),
					'id_period' => intVal($_POST['period']),
					'price' => floatval($_POST['price'])
				);
				if($this->packages->updateRightPackage(intVal($_POST['id']), $update))
					jsonResponse ("The right package was successfuly updated.", "success");

				jsonResponse ("Error: Cannot update right package. Try later");
			break;
			case 'add_right_package':
				checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'group_for',
						'label' => 'Group for',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'right',
						'label' => 'Right',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'period',
						'label' => 'Period',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'price',
						'label' => 'Price',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->model('Packages_Model', 'packages');

				$insert = array(
					'group_for' => intVal($_POST['group_for']),
					'id_right' => intVal($_POST['right']),
					'id_period' => intVal($_POST['period']),
					'price' => floatval($_POST['price'])
				);

				if($this->packages->setRightPackage($insert))
					jsonResponse ("The package was successfuly inserted.", "success");

				jsonResponse ("Error: Cannot insert package. Try later");
			break;
			case 'delete_right_package':
				checkAdminAjax('manage_content');

				$this->load->model('Packages_Model', 'packages');
				$id = intVal($_POST['id']);

				if($this->packages->deleteRightPackage($id))
					jsonResponse('The right package was deleted.', 'success');

				jsonResponse('Error: Cannot delete right package.');
			break;
			case 'get_rights_for_group':
				checkAdminAjax('manage_content');

				$id = intVal($this->uri->segment(4));

				$this->load->model('Packages_Model', 'packages');

				$right_packages = $this->packages->get_used_right_pack($id);
				jsonResponse('', 'success', $right_packages);
			break;
		}
	}

	function popup_forms() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"), 'errors');

		$id_user = $this->session->id;

		$op = $this->uri->segment(3);
		switch ($op) {
			case 'update_right_package':
				checkAdminAjaxModal('manage_content');

				$this->load->model('Packages_Model', 'packages');
				$this->load->model('UserGroup_Model', 'groups');
				$id = $this->uri->segment(4);

				$data = array(
					'periods' => $this->packages->selectPeriods(),
					'package_info' => $this->packages->getRightPackage($id),
					'groups' => $this->groups->getGroupsByType(array('type' => "'Buyer', 'Seller'")),
					'bymodules' => $this->groups->getRModules(),
				);

				$this->view->display('admin/packages/rights_packages/right_package_form_view', $data);
			break;
			case 'insert_right_package':
				checkAdminAjaxModal('manage_content');

				$this->load->model('Packages_Model', 'packages');
				$this->load->model('UserGroup_Model', 'groups');
				$id = $this->uri->segment(4);
				$data = array(
					'periods' => $this->packages->selectPeriods(),
					'groups' => $this->groups->getGroupsByType(array('type' => "'Buyer', 'Seller'")),
					'bymodules' => $this->groups->getRModules(),
				);

				$this->view->display('admin/packages/rights_packages/right_package_form_view', $data);
			break;
		}
	}
}

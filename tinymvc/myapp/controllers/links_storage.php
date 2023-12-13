<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Links_Storage_Controller extends TinyMVC_Controller {

	function index() {
        headerRedirect();
    }

    function administration() {
        checkAdmin('manage_content');

		$this->load->model('Country_Model', 'country');

		$data['port_country'] = $this->country->fetch_port_country();

        $this->view->assign($data);
        $this->view->assign('title', 'Links Storage');
        $this->view->display('admin/header_view');
        $this->view->display('admin/links_storage/index_view');
        $this->view->display('admin/footer_view');
    }

	function ajax_links_storage_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->load->model('Links_Storage_Model', 'links_storage');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_links_storage' => 'ls.id_links_storage',
                'dt_link'             => 'ls.link',
                'dt_title'            => 'ls.title',
                'dt_country'          => 'pc.country_name',
                'dt_paid'             => 'ls.paid'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'country', 'key' => 'country', 'type' => 'int'],
            ['as' => 'paid', 'key' => 'paid', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["ls.id_links_storage-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        $params['count'] = $this->links_storage->count_links_storage($params);
        $links_storage = $this->links_storage->get_links_storage($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $params['count'],
            "iTotalDisplayRecords" => $params['count'],
            "aaData" => array(),
        );

		if(empty($links_storage))
			jsonResponse('', 'success', $output);

		foreach ($links_storage as $link) {

			$account_info = '';
			if(!empty($link['account_info']))
				$account_info = '<a class="ep-icon ep-icon_user fancybox fancybox.ajax" href="'.__SITE_URL.'links_storage/popup_links_storage/decode_account/'.$link['id_links_storage'].'" title="Account information" data-title="Account information"></a>';

			$country_link = '---';
			if(!empty($link['country_name']))
				$country_link = '<div><a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Country" title="Filter by ' . $link['country_name'] . '" data-value-text="' . $link['country_name'] . '" data-value="' . $link['id_country'] . '" data-name="country"></a></div>
						<div><img width="24" height="24" src="' . getCountryFlag($link['country_name']) . '" title="' . $link['country_name'] . '" alt="' . $link['country_name'] . '"/></div>'
						.$link['country_name'];

			$paid = 'No';
			if($link['paid'])
				$paid = 'Yes';

			$output['aaData'][] = array(
				'dt_id_links_storage' => $link['id_links_storage'],
				'dt_link'  => '<div class="w-300 txt-break-word"><a href="'.$link['link'].'" target="_blank">'.$link['link'].'</a></div>',
				'dt_title' => $link["title"],
				'dt_description' => $link['description'],
				'dt_country' => $country_link,
				'dt_paid'  =>
					'<div><a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Paid" title="Filter by paid" data-value-text="' . $paid . '" data-value="' . $link['paid'] . '" data-name="paid"></a></div>'
					.$paid,
				'dt_actions' =>
					$account_info
					. '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'links_storage/popup_links_storage/edit_link/'.$link['id_links_storage'].'" title="Edit link" data-title="Edit link"></a>
					<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_link" data-message="Are you sure you want to delete this link?" data-link="'.$link['id_links_storage'].'" href="'.__SITE_URL.'links_storage/popup_links_storage/delete_link" title="Remove link"></a>',
			);
		}

        jsonResponse('', 'success', $output);
    }

	function popup_links_storage(){
        if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		if(!have_right('moderate_content'))
			messageInModal(translate("systmess_error_should_be_logged"));

        $op = cleanInput($this->uri->segment(3));

        switch($op){
			case 'decode_account':
				$this->load->model('Links_Storage_Model', 'links_storage');
				$id_link = intval($this->uri->segment(4));
				$data['account'] = $this->links_storage->get_encrypt_info($id_link, array('account_info'));

                if(!empty($data['account']))
					$this->view->display('admin/links_storage/account_info_view', $data);
                else
					messageInModal('Error: This link doesn\'t exist.');
            break;
			case 'edit_link':
				$this->load->model('Links_Storage_Model', 'links_storage');
				$this->load->model('Country_Model', 'country');

				$id_link = intval($this->uri->segment(4));

				$data['port_country'] = $this->country->fetch_port_country();
                $data['link_storage'] = $this->links_storage->get_link_storage($id_link);
                $data['account'] = $this->links_storage->get_encrypt_info($id_link, array('account_info'));
                $this->view->display('admin/links_storage/form_view', $data);
            break;
			case 'add_link':
				$this->load->model('Country_Model', 'country');

				$data['port_country'] = $this->country->fetch_port_country();
                $this->view->display('admin/links_storage/form_view', $data);
            break;
        }
    }

	function ajax_links_storage_operation(){
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$op = $this->uri->segment(3);
		$this->load->model('Links_Storage_Model', 'links_storage');

		switch ($op) {
			case 'delete_link':
				$id_link = intval($_POST['link']);
                if($this->links_storage->delete_link_storage($id_link))
					jsonResponse('The link has been successfully deleted.', 'success');
                else
					jsonResponse('Error: This link doesn\'t exist.');
            break;
			case 'create_link':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'link',
						'label' => 'Link',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'max_len[150]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('max_len[500]' => '')
					),
					array(
						'field' => 'paid',
						'label' => 'Paid',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$validator->validate())
					jsonResponse($validator->get_array_errors());

				$insert = array(
					'link' => cleanInput($_POST['link']),
					'title' => cleanInput($_POST['title']),
					'description' => cleanInput($_POST['description']),
					'paid' => intval($_POST['paid'])
				);

				if(!empty($_POST['country']))
					$insert['id_country'] = intval($_POST['country']);

				$id_link = $this->links_storage->set_link_storage($insert);

				if(!empty($id_link)){
					if(!empty($_POST['account_info'])){
						$insert_account_info['account_info'] = cleanInput($_POST['account_info']);
						$this->links_storage->set_encrypt_info($id_link, $insert_account_info);
					}

					jsonResponse('The link was successfully inserted.', 'success');
				}else
					jsonResponse('Error: You could not inserted link now. Please try again late.');
            break;
			case 'update_link':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'link',
						'label' => 'Link',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'max_len[150]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('max_len[500]' => '')
					),
					array(
						'field' => 'paid',
						'label' => 'Paid',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$validator->validate())
					jsonResponse($validator->get_array_errors());

				$update = array(
					'link' => cleanInput($_POST['link']),
					'title' => cleanInput($_POST['title']),
					'description' => cleanInput($_POST['description']),
					'paid' => intval($_POST['paid'])
				);

				if(!empty($_POST['country']))
					$update['id_country'] = intval($_POST['country']);

				$id_link = intval($_POST['id_links_storage']);

				if(!$this->links_storage->exist_link_storage($id_link))
					jsonResponse('Error: This link doesn\'t exist.');

				if($this->links_storage->update_link_storage($id_link, $update)){
					if(!empty($_POST['account_info'])){
						$insert_account_info['account_info'] = cleanInput($_POST['account_info']);
						$this->links_storage->set_encrypt_info($id_link, $insert_account_info);
					}

					jsonResponse('The link was successfully updated.', 'success');
				}else
					jsonResponse('Error: You could not updated link now. Please try again late.');
            break;
		}
    }
}

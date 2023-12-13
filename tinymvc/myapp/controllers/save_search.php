<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Save_Search_Controller extends TinyMVC_Controller {

	function popup_save_search(){
		if (!logged_in()) {
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		$data = array(
			'type'	=> $this->uri->segment(3),
			'link'	=> cleanOutput(urldecode($_GET['curr_link']))
		);

		$this->view->assign($data);
		$this->view->display('new/popup_save_search_view');
	}

	function ajax_savesearch_operations(){
		if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse('Error: You must be logged in to make changes.');


		$this->load->model('Save_Search_Model', 'save_search');
		$op = $this->uri->segment(3);

        switch($op) {
            case 'remove_search_saved':
                $item = intval($_POST['search']);
                $search = $this->save_search->i_save_it(id_session(), $item);
                if(!$search){
                    jsonResponse('Error: This is not yours search.');
                }

                if($this->save_search->unsave_search($item))
                    jsonResponse('The search was removed successfully from the saved search.', 'success');
                else
                    jsonResponse('Error: You cannot delete this seach now. Please try again later.');
            break;
            case 'add_search_saved':
				is_allowed("freq_allowed_save_search");

				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'link',
						'label' => 'Link',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Type',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$insert = array(
					'id_user' => id_session(),
					'type_search' => cleanInput($_POST['type']),
					'link_search' => cleanInput($_POST['link']),
					'description_search' => cleanInput($_POST['description']),
				);

				if($this->save_search->set_save_search($insert))
					jsonResponse('Your search results have been successfully saved in the "Saved" section on the head panel.', 'success');
				else
					jsonResponse('Error: You cannot save these search results now. Please try again later.');
			break;
		}
	}

	function ajax_get_saved(){
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model('Save_Search_Model', 'saved_search');
//			print_r($_POST);
		$data['curr_page'] = $page = (int) $_POST['page'];
		$data['counter'] = $this->saved_search->get_count_saved_search($this->session->id);

		$data['per_page'] = 10;
		$limit = ($page - 1) * $data['per_page']. ',' . $data['per_page'];
		$data['saved_search'] = $this->saved_search->get_saved_search($this->session->id, $limit);

		$content = $this->view->fetch('new/nav_header/saved/search_saved_list_view', $data);

		jsonResponse($content, 'success', array('counter' => $data['counter']));
	}
}
?>

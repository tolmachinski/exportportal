<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Meta_Controller extends TinyMVC_Controller {

    public function administration()
    {
        checkAdmin('manage_content,manage_translations');

		$data = array(
			'title' => 'EP meta pages'
		);

		$data['translations'] = $this->translations->get_languages();

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/meta/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_meta_administration()
    {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

		checkAdmin('manage_content,manage_translations');

        $this->load->model('Meta_Model', 'meta');

        $params = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id'       => 'id',
                'dt_page_key' => 'page_key',
                'dt_link'     => 'link'
            ])
        ];

        $params['sort_by'] = empty($params['sort_by']) ? ["id-desc"] : $params['sort_by'];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput'],
            ['as' => 'page_key', 'key' => 'page_key', 'type' => 'cleanInput'],
            ['as' => 'id_lang', 'key' => 'id_lang', 'type' => 'cleanInput']
        ]);

        $params = array_merge($params, $filters);

        $meta_list = $this->meta->get_meta($params);
        $meta_count = $this->meta->get_meta_counter($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $meta_count,
            "iTotalDisplayRecords" => $meta_count,
			'aaData' => array()
        );

		foreach ($meta_list as $meta) {
            $actions = "
                <a href=\"meta/popup_forms/edit_meta/{$meta['id']}\"
                    class=\"fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil\"
                    data-title=\"Edit meta page\"
                    title=\"Edit meta page\">
                </a>
            ";
            if(have_right('manage_content')) {
                $actions = "
                    {$actions}
                    <a class=\"ep-icon ep-icon_remove txt-red confirm-dialog\"
                        data-callback=\"remove_meta\"
                        data-id=\"{$meta['id']}\"
                        title=\"Remove page meta\"
                        data-message=\"Are you sure you want to delete this page meta?\"
                        href=\"#\">
                    </a>
                ";
            }

			$output['aaData'][] = array(
				'dt_id' => $meta['id'],
				'dt_lang' =>
					'<div class="tal"><a class="ep-icon ep-icon_filter dt_filter txt-green" data-value-text="' . $meta['lang_name'] . '" data-value="' . $meta['id_lang'] . '" title="Filter by ' . $meta['lang_name'] . '" data-title="Language name" data-name="id_lang"></a><div>'
					.'<img width="24" height="24" src="'. getCountryFlag($meta['lang_icon']) . '" alt="' . $meta['lang_icon'] . '">
					 <span class="lh-24">'.$meta['lang_name'].'</span>',
				'dt_page_key' =>
					'<div class="tal"><a class="ep-icon ep-icon_filter dt_filter txt-green" data-value-text="' . $meta['page_key'] . '" data-value="' . $meta['page_key'] . '" title="Filter by ' . $meta['page_key'] . '" data-title="Page key" data-name="page_key"></a><div>'
					. $meta['page_key'],
				'dt_link' =>
					'<a href="'.$meta['link'].'" target="_blank">'.$meta['link'].'</a>',
				'dt_title' => $meta['title'],
				'dt_actions' => $actions,
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            messageInModal(translate("systmess_error_should_be_logged"));
        }

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_meta':
				checkAdmin('manage_content,manage_translations');

				$data['translations'] = $this->translations->get_languages();

				$this->view->display('admin/meta/modal_form_view', $data);
			break;
            case 'edit_meta':
				checkAdmin('manage_content,manage_translations');

				$this->load->model('Meta_Model', 'meta');

                $id = intVal($this->uri->segment(4));
                $meta = $this->meta->get_one_meta($id);
                if(empty($meta)) {
                    messageInModal("The meta record with such ID is not found on this server");
                }

				$data['meta'] = $this->meta->get_one_meta($id);
				$data['meta']['rules'] = json_decode($data['meta']['rules'], true);

				$this->view->display('admin/meta/modal_form_view', $data);
			break;
        }
    }

    public function ajax_meta_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'verify_lang':
                checkAdmin('manage_content,manage_translations');

                $page_key = cleanInput($_POST['page_key']);
                $lang = intVal($_POST['lang']);

				$this->load->model('Meta_Model', 'meta');

                if (!$this->meta->exist_meta($page_key, $lang))
                    jsonResponse('Meta for this language does not exist.', 'success');
				else
					jsonResponse('Error: Meta for this language already exists.');
			break;
            case 'remove_meta':
                checkAdmin('manage_content');

                $id = intVal($_POST['id']);
				$this->load->model('Meta_Model', 'meta');
                if ($this->meta->delete_meta($id)) {
                    jsonResponse('The Meta page has been successfully removed.', 'success');
                }

				jsonResponse('Error: You cannot remove this EP module now. Please try again later.');
			break;
            case 'edit_meta':
                checkAdmin('manage_content');

				$validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
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
                        'field' => 'id',
                        'label' => 'ID',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

				$this->load->model('Meta_Model', 'meta');
				$id_meta = intVal($_POST['id']);

				if(empty($_POST['rules'])){
					$rules = array();
				}else{
					foreach($_POST['rules'] as $type_key => $type_value){
						if(!is_array($type_value))
							jsonResponse('Error: You must save all replace keys.');

						foreach($type_value as $rule_key => $rule_value){
							$rules[$type_key]['['.$rule_key.']'] = $rule_value;
						}
					}
				}

				$rules = json_encode($rules);
                $update = array(
                    'image' => cleanInput($_POST['image']),
                    'title' => cleanInput($_POST['title']),
					'description' => cleanInput($_POST['description']),
					'keywords' => cleanInput($_POST['keywords']),
					'link' => cleanInput($_POST['link']),
					'rules' => $rules
                );

				if ($this->meta->update_meta($id_meta, $update)) {
                    jsonResponse('The meta has been successfully changed.', 'success');
                }

                jsonResponse('Error: You cannot change this meta now. Please try again later.');
			break;
            case 'add_meta':
                checkAdmin('manage_content');

				$validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
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
                        'field' => 'page_key',
                        'label' => 'Page key',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'id_lang',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

				$this->load->model('Meta_Model', 'meta');

				$page_key = cleanInput($_POST['page_key']);
				$id_lang = cleanInput($_POST['id_lang']);

				// $meta_details = $this->meta->get_seo($page_key);
				// if(!empty($meta_details))
				// 	jsonResponse("Error: Meta information for this page key already exists.");

				if($this->meta->exist_meta($page_key, $id_lang))
					jsonResponse('Error: Meta for this language already exists.');

				if(empty($_POST['rules']))
					$rules = array();
				else{
					foreach($_POST['rules'] as $type_key => $rule){
						if(!is_array($rule))
							jsonResponse('Error: You must save all replace keys.');

						foreach($rule as $rule_key => $rule_value){
							$rules[$type_key]['['.$rule_key.']'] = $rule_value;
						}
					}
				}

				$language_info = $this->translations->get_language($id_lang);
				if(empty($language_info)) {
					jsonResponse('Error: This language does not exists.');
                }

				$rules = json_encode($rules);
                $insert = array(
					'image' => cleanInput($_POST['image']),
                    'title' => cleanInput($_POST['title']),
					'description' => cleanInput($_POST['description']),
					'keywords' => cleanInput($_POST['keywords']),
					'link' => cleanInput($_POST['link']),
					'page_key' => $page_key,
					'id_lang' => $id_lang,
					'lang_iso2' => $language_info['lang_iso2'],
					'rules' => $rules
                );

				if ($this->meta->insert_meta($insert)) {
                    jsonResponse('The meta has been successfully added.', 'success');
                }

                jsonResponse('Error: You cannot add this meta now. Please try again later.');
			break;
        }
    }
}

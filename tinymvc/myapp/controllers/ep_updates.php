<?php

use App\Common\Contracts\CommentType;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Ep_Updates_Controller extends TinyMVC_Controller {
	function index() {
        $uri = uri()->uri_to_assoc(4);

        checkURI($uri, array('page'));
        checkIsValidPage($uri['page']);

        /**
         * @var Ep_Updates_Model $ep_updates_model
         */
        $ep_updates_model = model(Ep_Updates_Model::class);

        $this->breadcrumbs = array(
            array(
                'link' 	=> __SITE_URL . 'about',
                'title'	=> translate('about_us_nav_about_us')
            ),
            array(
                'link' 	=> __SITE_URL . 'about/in_the_news?hash=ep_updates',
                'title'	=> translate('about_us_nav_in_the_news')
            ),
            array(
                'link'	=> __SITE_URL . 'ep_updates',
                'title'	=> translate('breadcrumb_ep_updates')
            )
        );

        $links_map = array(
            'page' => array(
				'type' => 'uri',
				'deny' => array('page'),
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('page', 'keywords')
            ),
        );

		$links_tpl = uri()->make_templates($links_map, $uri);
        $links_tpl_without = uri()->make_templates($links_map, $uri, true);

        $per_page = config('ep_updates_per_page', 10);
        $page = empty($uri['page']) ? 1 : (int) $uri['page'];

        $updates_params = array(
            'select'    => 'id, title, description, content, date_time, url',
            'per_p'     => $per_page,
            'from'      => $per_page * ($page - 1),
        );

        $keywords = $_GET['keywords'] ?? '';
        if (!empty($keywords)) {
            $search = trim(cleanInput(cut_str($_GET['keywords'])));

            if (!empty($search)) {
                $updates_params['keywords'] = $search;
            }
        }

        $total_updates = $ep_updates_model->get_list_ep_update_public_count($updates_params);

        $paginator_config = array(
            'replace_url'   => true,
            'total_rows'    => $total_updates,
            'first_url'     => rtrim('ep_updates/' . $links_tpl_without['page'], '/'),
            'base_url'      => 'ep_updates/' . $links_tpl['page'],
            'per_page'      => $per_page,
        );

        if (!empty($keywords)) {
            $paginator_config['suffix'] = '?' . http_build_query(array('keywords' => $keywords));
        }

        library('pagination')->initialize($paginator_config);

        $partial_search_params = array(
            'action' => __SITE_URL . 'ep_updates',
            'keywords' => $keywords,
            'title' => translate('ep_updates_sidebar_search_block_title'),
            'input_text_placeholder' => translate('ep_updates_sidebar_search_block_keywords_placeholder', null, true),
            'btn_text_submit' => translate('ep_updates_sidebar_search_block_submit_btn'),
        );

        $data = array(
            'sidebar_right_content' => 'new/partial_sidebar_search_view',
            'header_out_content'    => 'new/about/in_the_news/header_view',
            'main_content'          => 'new/ep_updates/index_view',
            'breadcrumbs'           => $this->breadcrumbs,
            'ep_updates'            => $ep_updates_model->get_list_ep_update_public($updates_params),
            'nav_active'            => 'in the news',
            'pagination'            => library('pagination')->create_links(),
            'keywords'              => cleanOutput($keywords),
            'per_p'                 => $per_page,
            'count'                 => $total_updates,
            'page'                  => $page,
            'partial_search_params' => $partial_search_params,
            'header_title'          => translate('about_us_in_the_news_updates_header_title'),
            'header_img'            => 'updates_header.jpg'
        );

        if ($page > 1) {
            $data['meta_params'] = array('[PAGE]' => $page);
        }

        views()->assign($data);
        views()->display('new/index_template_view');
    }

	function detail(){
        $uri = uri()->uri_to_assoc(5);
        checkURI($uri, array());

        /**
         * @var Ep_Updates_Model $ep_updates_model
         */
        $ep_updates_model = model(Ep_Updates_Model::class);

        $id = id_from_link(uri()->segment(3));
        if (empty($id)) {
            show_404();
        }

        $ep_update = $ep_updates_model->get_one_ep_update_public($id);
        if (empty($ep_update)) {
            show_404();
        }

        $this->breadcrumbs = array(
            array(
                'link' 	=> __SITE_URL . 'about',
                'title'	=> translate('about_us_nav_about_us')
            ),
            array(
                'link' 	=> __SITE_URL . 'about/in_the_news?hash=ep_updates',
                'title'	=> translate('about_us_nav_in_the_news')
            ),
            array(
                'link'	=> __SITE_URL . 'ep_updates',
                'title'	=> translate('breadcrumb_ep_updates')
            ),
            array(
                'link' 	=> '',
                'title'	=> truncWords($ep_update['title'], 10)
            )
        );

        $params_updates = array(
            'limit' => 8,
            'not_id_record' => $id,
            'order_by' => 'id DESC',
        );

        $temp_updates = $ep_updates_model->get_list_ep_update_public($params_updates);

        $ep_updates_other = $ep_updates_last = array();
        if (!empty($temp_updates)) {
            $ep_updates_last = array_splice($temp_updates, 0, 4);
            $ep_updates_other = $temp_updates;
        }

        $partial_search_params = array(
            'action' => __SITE_URL . 'ep_updates',
            'keywords' => '',
            'title' => translate('ep_updates_sidebar_search_block_title'),
            'input_text_placeholder' => translate('ep_updates_sidebar_search_block_keywords_placeholder', null, true),
            'btn_text_submit' => translate('ep_updates_sidebar_search_block_submit_btn'),
        );

        $data = array(
            'sidebar_right_content' => 'new/ep_updates/details/sidebar_view',
            'ep_updates_last'       => $ep_updates_last,
            'main_content'          => 'new/ep_updates/details/index_view',
            'breadcrumbs'           => $this->breadcrumbs,
            'meta_params'           => array('[UPDATE_NAME]' => $ep_update['title']),
            'ep_updates'            => $ep_updates_other,
            'nav_active'            => 'in the news',
            'ep_update'             => $ep_update,
            'partial_search_params' => $partial_search_params,
            'footer_content'        => 'new/trade_news/mobile_wrapper',
            'comments'              => array(
                'hash_components'   => updatesCommentsResourceHashComponents($id),
                'type_id'           => CommentType::UPDATES()->value,
            ),
        );

        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function administration() {
        checkAdmin('ep_updates_administration');

        $this->load->model('Ep_Updates_Model', 'ep_updates');
		$data = array(
			'title' => 'EP Updates',
			'upload_folder'  => encriptedFolderName(),
		);

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/ep_updates/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_ep_updates_administration() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjaxDT("ep_updates_administration");

        /** @var Ep_Updates_Model $epUpdatesModel */
        $epUpdatesModel = model(Ep_Updates_Model::class);

        $params = array_merge(
            dtConditions($_POST, [
                ['as' => 'date_to',     'key' => 'date_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'date_from',   'key' => 'date_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'visible',     'key' => 'visible',     'type' => 'int'],
                ['as' => 'keywords',    'key' => 'keywords',    'type' => 'cleanInput']
            ]),
            [
                'per_p'     => (int) $_POST['iDisplayLength'],
                'start'     => (int) $_POST['iDisplayStart'],
                'sort_by'   => flat_dt_ordering($_POST, [
                    'dt_date_time'  => 'date_time',
                    'dt_title'      => 'title',
                    'dt_id'         => 'id',
                ])
            ]
        );

        $ep_updates = $epUpdatesModel->get_ep_updates($params);
        $ep_updates_count = $epUpdatesModel->get_ep_updates_counter($params);

        $output = [
            'iTotalDisplayRecords'  => $ep_updates_count,
            'iTotalRecords'         => $ep_updates_count,
			'aaData'                => [],
            'sEcho'                 => (int) $_POST['sEcho'],
        ];

        if (empty($ep_updates)) {
			jsonResponse('', 'success', $output);
        }

        $upload_folder = uri()->segment(3);

		foreach ($ep_updates as $update) {
			$visible_btn = '<a class="ep-icon ep-icon_' . (($update['visible']) ? '' : 'in') . 'visible confirm-dialog" data-callback="change_visible_ep_update" data-id="' . $update['id'] . '" data-message="Are you sure you want to change the visibility status of this EP update?" href="#" title="Set EP update ' . (($update['visible']) ? 'active' : 'inactive') . '"></a>';

            $langs = array();
            $langs_record = array_filter(json_decode($update["translations_data"], true));
            $langs_record_list = array("English");
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_key => $lang_record) {
                    if($lang_key == "en"){
                        continue;
                    }

                    $langs[] = '<li>
                                    <div class="flex-display">
                                        <span class="display-ib_i lh-30 pl-5 pr-10 text-nowrap mw-150">'.$lang_record["lang_name"].'</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_ep_update_i18n" data-ep-update-id="' . $update["id"] . '" data-ep-update-i18n-lang="'.$lang_record['abbr_iso2'].'" title="Delete" data-message="Are you sure you want to delete the ep update translation?" href="#" ></a>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right" href="'.__SITE_URL.'ep_updates/popup_forms/edit_ep_updates_i18n/'.$update["id"]."/".$upload_folder."/".$lang_record["abbr_iso2"].'" data-title="Edit ep updates translation" title="Edit"></a>
                                    </div>
                                </li>';
                    $langs_record_list[] = $lang_record["lang_name"];
                }
                $langs[] = '<li role="separator" class="divider"></li>';
            }

            $langs_dropdown = '<div class="dropdown">
                                <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu">
                                    '.implode("", $langs).'
                                    <li><a href="'.__SITE_URL.'ep_updates/popup_forms/add_ep_updates_i18n/'.$update["id"]."/".$upload_folder.'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';

			$output['aaData'][] = array(
				'dt_id' => $update['id'] . '<br/><a rel="view_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				'dt_title' => $update['title'],
				'dt_content' => $update['content'],
				'dt_description' => $update['description'],
				'dt_date_time' => formatDate($update['date_time']),
				'dt_actions' =>
					$visible_btn
				. '<a href="ep_updates/popup_forms/edit_ep_update/'. $update['id'] . '" class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" data-title="Edit EP update" title="Edit this EP update"></a>'
				. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_ep_update" data-id="' . $update['id'] . '" title="Remove this EP update" data-message="Are you sure you want to delete this EP update?" href="#" ></a>',
                "dt_tlangs" => $langs_dropdown,
                "dt_tlangs_list" => implode(", ", $langs_record_list)
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms() {
        if (!isAjaxRequest())
            headerRedirect();

        checkAdminAjaxModal("ep_updates_administration");

        $data['errors'] = array();
        $id_user = $this->session->id;

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_ep_updates':
				$this->load->model('Ep_Updates_Model', 'ep_updates');

                $this->view->display('admin/ep_updates/modal_form_view');
                break;
            case 'edit_ep_update':
				$id_ep_update = (int)$this->uri->segment(4);
				$this->load->model('Ep_Updates_Model', 'ep_updates');
				$data['ep_updates'] = $this->ep_updates->get_one_ep_update($id_ep_update);

				$this->view->display('admin/ep_updates/modal_form_view', $data);
                break;
            case "add_ep_updates_i18n":
                $this->load->model('Ep_Updates_Model', 'ep_updates');
				$id_ep_update = intval($this->uri->segment(4));
				$data["ep_update"] = $this->ep_updates->get_one_ep_update($id_ep_update);
				$data["tlanguages"] = $this->translations->get_languages();

                $this->view->display("admin/ep_updates/form_i18n_view", $data);
            break;

            case "edit_ep_updates_i18n":
                $ep_update_i18n_lang = $this->uri->segment(6);
                if(empty($ep_update_i18n_lang)) {
                    messageInModal("Error: Lang is not setted.", $type = "errors");
                }

                $data = array();

                $this->load->model('Ep_Updates_Model', 'ep_updates');
				$id_ep_update = intVal($this->uri->segment(4));
				$data["ep_update"] = $this->ep_updates->get_one_ep_update($id_ep_update);
                if(empty($data['ep_update'])) {
                    messageInModal('Error: Could not find the update.', $type = 'errors');
                }

                $data['ep_update_i18n'] = $this->ep_updates->get_one_ep_update_i18n(array("id_ep_update" => $id_ep_update, "ep_update_i18n_lang" => $ep_update_i18n_lang));
                if(empty($data['ep_update_i18n'])) {
                    messageInModal('Error: Could not find the translation.', $type = 'errors');
                }

				$data['tlanguages'] = $this->translations->get_languages();

                $this->view->display('admin/ep_updates/form_i18n_view', $data);
            break;
        }
    }

    public function ajax_ep_updates_operations() {
        if (!isAjaxRequest())
            headerRedirect();

        checkAdminAjax('ep_updates_administration');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'change_visible_ep_update':
                $id_ep_update = intVal($_POST['id']);
                $this->load->model('Ep_Updates_Model', 'ep_updates');

                $ep_update_info = $this->ep_updates->get_one_ep_update($id_ep_update, 'visible');

                if (empty($ep_update_info))
                    jsonResponse('Error: This EP news does not exist.');

				$update = array('visible' => intVal(!(bool)$ep_update_info['visible']));

                if ($this->ep_updates->change_ep_update($id_ep_update, $update))
                    jsonResponse('The EP update has been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot change this EP update now. Please try again later.');
            break;
            case 'remove_ep_update':
                $id_ep_update = intVal($_POST['id']);
				$this->load->model('Ep_Updates_Model', 'ep_updates');

                if ($this->ep_updates->delete_ep_update($id_ep_update)) {
                    jsonResponse('The EP update has been successfully removed.', 'success');
                }

                jsonResponse('Error: You cannot remove this EP update now. Please try again later.');
            break;
            case 'edit_ep_update':
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'Update info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Short description',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Ep_Updates_Model', 'ep_updates');
                $id_ep_update = (int)$_POST['id'];
                $update_info = $this->ep_updates->get_one_ep_update($id_ep_update);

                if(empty($update_info)){
                    jsonResponse('The EP update does not exist.');
                }

                $ep_update_title = cleanInput($_POST['title']);
                $update = array(
                    'title' => $ep_update_title,
                    'content' => $_POST['content'],
                    'description' => cleanInput($_POST['description']),
					'visible' => intVal((bool)$_POST['visible']),
                    'url' => strForUrl($ep_update_title)."-".$id_ep_update
                );

                if ($this->ep_updates->change_ep_update($id_ep_update, $update))
                    jsonResponse('The EP update has been successfully changed.', 'success');
                else
                    jsonResponse('You cannot change this EP update now. Please try again later.');
            break;
            case 'add_ep_update':
                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Short description',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());


                $ep_update_title = cleanInput($_POST['title']);
                $insert = array(
                    'title' => $ep_update_title,
                    'content' => $_POST['content'],
                    'description' => cleanInput($_POST['description']),
					'visible' => intVal((bool)$_POST['visible'])
                );

				$this->load->model('Ep_Updates_Model', 'ep_updates');

                if ($id_ep_update = $this->ep_updates->insert_ep_update($insert)) {
                    $this->ep_updates->change_ep_update($id_ep_update, array('url' => strForUrl($ep_update_title)."-".$id_ep_update));
                    jsonResponse('The EP update has been successfully changed.', 'success');
                } else {
                    jsonResponse('Error: You cannot add EP update now. Please try again later.');
                }
            break;
            case "save_ep_update_i18n":
                $validator_rules = array(
                    array(
                        "field" => "ep_update_i18n_title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[200]" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_description",
                        "label" => "Description",
                        "rules" => array("required" => "", "max_len[500]" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_content",
                        "label" => "Content",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_lang",
                        "label" => "Language",
                        "rules" => array("required" => "")
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Ep_Updates_Model', 'ep_updates');
                $id_ep_update = $this->uri->segment(4);
                $ep_update= $this->ep_updates->get_one_ep_update($id_ep_update);
                if (empty($ep_update)) {
                    jsonResponse('This EP updates does not exist.');
                }

                $ep_update_i18n_lang = cleanInput($_POST['ep_update_i18n_lang']);
                $tlang = $this->translations->get_language_by_iso2($ep_update_i18n_lang);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $translations_data = json_decode($ep_update['translations_data'], true);
                if(array_key_exists($ep_update_i18n_lang, $translations_data)){
                    jsonResponse('Error: Ep update translation for this language already exist.');
                }

                $translations_data[$ep_update_i18n_lang] = array(
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2']
                );

				$this->load->library("Cleanhtml", "clean");
                $ep_update_i18n_title = cleanInput($_POST["ep_update_i18n_title"]);
                $insert = array(
                    "id_ep_update" => $id_ep_update,
                    "ep_update_i18n_lang" => $ep_update_i18n_lang,
                    "ep_update_i18n_content" => $this->clean->sanitizeUserInput($_POST["ep_update_i18n_content"]),
                    "ep_update_i18n_description" => cleanInput($_POST["ep_update_i18n_description"]),
                    "ep_update_i18n_title" => $ep_update_i18n_title,
                    "ep_update_i18n_url" => strForUrl($ep_update_i18n_title)."-".$id_ep_update
                );


                if($this->ep_updates->insert_ep_update_i18n($insert)){
                    $this->ep_updates->change_ep_update($id_ep_update, array("translations_data" => json_encode($translations_data)));
                    jsonResponse("The translation has been successfully added", "success");
                }

                jsonResponse("Error: Cannot add translation now. Please try later.");
                break;
            case "edit_ep_update_i18n":
                $validator_rules = array(
                    array(
                        "field" => "ep_update_i18n_title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[200]" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_description",
                        "label" => "Description",
                        "rules" => array("required" => "", "max_len[500]" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_content",
                        "label" => "Content",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "ep_update_i18n_lang",
                        "label" => "Language",
                        "rules" => array("required" => "")
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Ep_Updates_Model', 'ep_updates');
                $id_ep_update = $this->uri->segment(4);
                $ep_update= $this->ep_updates->get_one_ep_update($id_ep_update);
                if (empty($ep_update)) {
                    jsonResponse('This EP update does not exist.');
                }

                $ep_update_i18n_lang = cleanInput($_POST['ep_update_i18n_lang']);
                $tlang = $this->translations->get_language_by_iso2($ep_update_i18n_lang);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $translations_data = json_decode($ep_update['translations_data'], true);
                if(!array_key_exists($ep_update_i18n_lang, $translations_data)){
                    jsonResponse('Error: Ep update translation for this language already exist.');
                }

                $translations_data[$ep_update_i18n_lang] = array(
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2']
                );

				$this->load->library("Cleanhtml", "clean");
                $ep_update_i18n_title = cleanInput($_POST["ep_update_i18n_title"]);
                $update_i18n = array(
                    "id_ep_update" => $id_ep_update,
                    "ep_update_i18n_lang" => $ep_update_i18n_lang,
                    "ep_update_i18n_content" => $this->clean->sanitizeUserInput($_POST["ep_update_i18n_content"]),
                    "ep_update_i18n_description" => cleanInput($_POST["ep_update_i18n_description"]),
                    "ep_update_i18n_title" => $ep_update_i18n_title,
                    "ep_update_i18n_url" => strForUrl($ep_update_i18n_title)."-".$id_ep_update
                );

                $id_ep_update_i18n = intVal($_POST["id_ep_update_i18n"]);
                if($this->ep_updates->change_ep_update_i18n($id_ep_update_i18n, $update_i18n)){
                    jsonResponse("The translation has been successfully edited", "success");
                }

                jsonResponse("Error: Cannot add translation now. Please try later.");
                break;
            case "delete_ep_update_i18n":
				$this->load->model('Ep_Updates_Model', 'ep_updates');
				$id_ep_update = intval($_POST["id_ep_update"]);
                $ep_update = $this->ep_updates->get_one_ep_update($id_ep_update);
                if (empty($ep_update)) {
                    jsonResponse("This EP update does not exist.");
                }

				$ep_update_i18n_lang = cleanInput($_POST["ep_update_i18n_lang"]);
				$ep_update_i18n = $this->ep_updates->get_one_ep_update_i18n(array("id_ep_update" => $id_ep_update, "ep_update_i18n_lang" => $ep_update_i18n_lang));
				if(empty($ep_update_i18n)){
					jsonResponse("Error: The ep update translation does not exist.");
				}


				$translations_data = json_decode($ep_update["translations_data"], true);
				unset($translations_data[$ep_update_i18n_lang]);
				$this->ep_updates->change_ep_update($id_ep_update, array("translations_data" => json_encode($translations_data)));
				$this->ep_updates->delete_ep_update_i18n($ep_update_i18n["id_ep_update_i18n"]);

				jsonResponse("The ep update translation has been successfully deleted.", "success");
            break;
        }

    }
}


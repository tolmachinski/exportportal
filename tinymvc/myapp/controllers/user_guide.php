<?php

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use const App\Common\ROOT_PATH;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class User_Guide_Controller extends TinyMVC_Controller
{
    function _load_main()
    {
        $this->load->model('User_Guide_Model', 'user_guide');
        $this->load->model('Elasticsearch_User_Guide_Model', 'elasticuser_guide');
    }

    public function index() {
        $this->breadcrumbs[] = [
            'link'     => __SITE_URL . 'help',
            'title'    => translate('breadcrumb_help')
        ];

        $this->breadcrumbs[] = [
            'link'     => '',
            'title'    => translate('breadcrumb_user_guide')
        ];

        /** @var User_Guide_Model $userGuide */
        $userGuide = model(User_Guide_Model::class);
        $documentLangs = $userGuide->getUserGuidesLang();

        $this->view_data = [
            'breadcrumbs'   => $this->breadcrumbs,
            'current_page'  => 'user_guide',
            'documentUploadLangs' => $documentLangs['document_upload'],
            'templateViews' => [
                'headerOutContent'  => 'user_guide/landing/header_view',
			    'mainContent' 	    => 'user_guide/landing/index_view',
			    'footerOutContent' 	=> 'about/bottom_who_we_are_view',
            ],
        ];

        views()->display_template($this->view_data);
    }

    function search()
    {
        // show_comming_soon();
        $meta_params = array();
        if ( ! isset($_GET['keywords']) || empty($keywords = cleanInput(cut_str($_GET['keywords']))) || mb_strlen(decodeCleanInput($keywords)) < config('help_search_min_keyword_length')) {
            $this->session->setMessages('Error: The search keywords must have at least ' . config('help_search_min_keyword_length') . ' characters.', 'errors');
            headerRedirect(get_dynamic_url('user_guide'));
        }
        $meta_params['[KEYWORDS]'] = $keywords;

        model('search_log')->log($keywords);

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

        $uri_params = uri()->uri_to_assoc(4);
        $links_tpl = uri()->make_templates($links_map, $uri_params);
        $links_tpl_without = uri()->make_templates($links_map, $uri_params, true);

        $this->breadcrumbs[] = array(
            'link'     => __SITE_URL . 'help',
            'title'    => translate('breadcrumb_help')
        );

        $this->breadcrumbs[] = array(
            'link'     => get_dynamic_url('user_guide'),
            'title'    => translate('help_nav_header_documentation')
        );

        $this->breadcrumbs[] = array(
            'link'     => '',
            'title'    => 'Your search'
        );

        $per_page = 10;
        $page = (int) arrayGet($uri_params, 'page', 1);
        $page = $page < 1 ? 1 : $page;
        if($page > 1){
            $meta_params['[PAGE]'] = $page;
        }
        $params = array(
            'limit' => $per_page,
            'start' => $per_page * ($page - 1),
            'keywords'  => $keywords,
            'onlyPublicGuides'  => true,
        );

        model('elasticsearch_user_guide')->get_user_guides($params);
        $user_guides = arrayByKey(model('elasticsearch_user_guide')->user_guides_records, 'id_menu');
        $count_user_guides = model('elasticsearch_user_guide')->user_guides_count;

        $paginator_config = array(
            'base_url'      => 'user_guide/search/' . $links_tpl['page'],
            'first_url'     => get_dynamic_url('user_guide/search/' . $links_tpl_without['page']),
			'total_rows'    => $count_user_guides,
			'per_page'      => $per_page,
            'replace_url'   => true,
        );

        library('pagination')->initialize($paginator_config);

        $data = array(
            'sidebar_right_content' => 'new/user_guide/sidebar_view',
            'footer_out_content'    => 'new/about/bottom_become_partner_view',
            'header_out_content'    => 'new/user_guide/header_view',
            'count_user_guides'     => $count_user_guides,
            'count_by_type'         => model('elasticsearch_user_guide')->user_guides_count_by_type,
            'current_page'          => 'user_guide',
            'main_content'          => 'new/user_guide/index_view',
            'breadcrumbs'           => $this->breadcrumbs,
            'user_guides'           => $user_guides,
            'pagination'            => library('pagination')->create_links(),
            'nav_page'              => 'user_guide',
            'keywords'              => $keywords,
            'count'                 => $count_user_guides,
            'per_p'                 => $per_page,
            'meta_params'           => $meta_params,
            'search_params'         => array(array(
                                            'link' => get_dynamic_url('user_guide/'),
                                            'title' => $keywords,
                                            'param' => 'Keywords',
                                        )),
        );

        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    function ba()
    {
        $file = 'public/user_guide/ep_brand_ambassador.pdf';
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="EP Brand Ambassador.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');
        @readfile($file);
    }

    function administration()
    {
        checkAdmin('manage_doc');
        $this->_load_main();
        $data = array(
            'title' => 'EP Documentation'
        );

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/user_guide/index_view');
        $this->view->display('admin/footer_view');
    }

    public function show_doc()
    {
        $this->_load_main();
        $doc = $this->uri->segment(3);
        if (intVal($doc)) {
            $data['document'] = $this->user_guide->get_user_guide(intVal($doc));
        } else {
            $doc = cleanInput($doc);
            $data['document'] = $this->user_guide->get_user_guide_by_alias($doc);
        }

        $params_childrens = array('id_parent' => $data['document']['id_menu'], 'columns' => 'edm.id_menu, edm.menu_title');

        if (!empty($_GET['user_type'])) {
            $data['user_type'] = $params_childrens['user_type'] = cleanInput($_GET['user_type']);
        }

        $data['document_childrens'] = $this->user_guide->get_user_guides($params_childrens);

        $data['document']['menu_breadcrumbs'] = json_decode("[" . $data['document']['menu_breadcrumbs'] . "]", true);

        $this->view->display('new/header_view');
        $this->view->display('new/user_guide/show_doc_view', $data);
        $this->view->display('new/footer_view');
    }

    public function popup_forms()
    {
        $this->_load_main();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'show_doc':
                $doc = $this->uri->segment(4);
                if (intVal($doc)) {
                    $data['document'] = $this->user_guide->get_user_guide(intVal($doc));
                } else {
                    $doc = cleanInput($doc);
                    $data['document'] = $this->user_guide->get_user_guide_by_alias($doc);
                }

                $params_childrens = array('id_parent' => $data['document']['id_menu'], 'columns' => 'edm.id_menu, edm.menu_title');

                if (!empty($_GET['user_type'])) {
                    $data['user_type'] = $params_childrens['user_type'] = cleanOutput(cleanInput($_GET['user_type']));
                }

                $data['document_childrens'] = $this->user_guide->get_user_guides($params_childrens);

                $data['document']['menu_breadcrumbs'] = json_decode("[" . $data['document']['menu_breadcrumbs'] . "]", true);
                $data['meta_params']['[TITLE]'] = $data['document']['menu_title'];

                if (!isAjaxRequest()) {
                    $data['guide_on_page'] = true;
                    $this->view->display('new/header_view');
                    $this->view->display('new/user_guide/show_doc_view', $data);
                    $this->view->display('new/footer_view');
                } else {
                    views()->display('new/user_guide/popup_show_doc_view', $data);
                }

            break;
            case 'add_guide':
                if (!logged_in()) {
                    messageInModal(translate("systmess_error_should_be_logged"));
                }

                checkAdminAjaxModal('manage_doc');

                $id_parent = intVal($this->uri->segment(4));

                if ($id_parent > 0) {
                    $data['parent_info'] = $this->user_guide->get_user_guide($id_parent);
                    $data['parent_info']['menu_breadcrumbs'] = json_decode("[" . $data['parent_info']['menu_breadcrumbs'] . "]", true);
                    $menu_users = $this->user_guide->get_user_guide_relation($id_parent);
                    $data['menu_users'] = array();
                    foreach ($menu_users as $menu_user) {
                        $data['menu_users'][] = $menu_user['rel_user_type'];
                    }
                }

                $data['uploadFolder'] = encriptedFolderName();

                $this->view->display('admin/user_guide/add_form_view', $data);
            break;
            case 'edit_guide':
                if (!logged_in()) {
                    messageInModal(translate("systmess_error_should_be_logged"));
                }

                checkAdminAjaxModal('manage_doc');

                $id_menu = intVal($this->uri->segment(4));
                $data['menu_info'] = $this->user_guide->get_user_guide($id_menu);
                if (empty($data['menu_info'])) {
                    messageInModal('Error: The menu item does not exist.');
                }
                if ($data['menu_info']['id_parent'] > 0) {
                    $menu_users = $this->user_guide->get_user_guide_relation($data['menu_info']['id_parent']);
                    $data['parent_menu_users'] = array();
                    foreach ($menu_users as $menu_user) {
                        $data['parent_menu_users'][] = $menu_user['rel_user_type'];
                    }
                }
                $menu_users = $this->user_guide->get_user_guide_relation($id_menu);
                $data['menu_users'] = array();
                foreach ($menu_users as $menu_user) {
                    $data['menu_users'][] = $menu_user['rel_user_type'];
                }

                $data['uploadFolder'] = encriptedFolderName();

                $data['menu_info']['menu_breadcrumbs'] = json_decode("[" . $data['menu_info']['menu_breadcrumbs'] . "]", true);
                $this->view->display('admin/user_guide/edit_form_view', $data);
            break;
        }
    }

    function view()
    {
        checkAdmin('manage_doc');

        $this->_load_main();

        $doc = $this->uri->segment(3);
        if (intVal($doc)) {
            $data['document'] = $this->user_guide->get_user_guide(intVal($doc));
        } else {
            $doc = cleanInput($doc);
            $data['document'] = $this->user_guide->get_user_guide_by_alias($doc);
        }

        $this->view->display('admin/user_guide/document_view', $data);
    }

    /**
     * @author Usinevici Alexandr
     * @todo Remove [02.12.2021]
     * Reason: not used
     */
    /* public function ajax_operations()
    {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();

        $op = cleanInput($this->uri->segment(3));

        switch ($op) {
            case 'show_menu':
                $params = array();
                $params['user_type'] = cleanInput($_POST['user_type']);
                if (!empty($_POST['keywords'])) {
                    $params['keywords'] = cleanInput(cut_str($_POST['keywords']));
                }

                // $menu = $this->user_guide->get_user_guides($params);
                $this->elasticuser_guide->get_user_guides($params);
                $searched_user_guides = $this->elasticuser_guide->user_guides_records;
                $menu_breadcrumbs = array_column($searched_user_guides, 'menu_breadcrumbs');
                $ids_user_guides = [];

                foreach ($menu_breadcrumbs as $menu_breadcrumb) {
                    $decode = json_decode('[' . $menu_breadcrumb . ']', true);
                    foreach ($decode as $key => $value) {
                        array_map(function ($v) use (&$ids_user_guides) {
                            $ids_user_guides[] = $v;
                        }, array_keys($value));
                    }
                }

                $ids_user_guides = array_values(array_unique($ids_user_guides));
                $this->elasticuser_guide->get_user_guides(['ids_user_guides' => $ids_user_guides]);
                $menu = $this->elasticuser_guide->user_guides_records;

                if (!empty($menu)) {
                    $menu = arrayByKey($menu, 'id_menu');
                    $menu = $this->user_guide->_menu_map($menu);

                    $widget_params['user_type'] = $params['user_type'];
                    $widget_params['menu_list'] = $menu;

                    $widget_epdoc_menu = "widget_epdoc_menu_new";

                    $menu_html = call_user_func($widget_epdoc_menu, $widget_params);

                    jsonResponse('', 'success', array('list' => $menu_html));
                } else {
                    jsonResponse('Error: The menu not was selected.');
                }
            break;
        }
    } */

    public function ajax_admin_operations()
    {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        checkAdminAjax('manage_doc');

        $this->_load_main();

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_menu':
                $validator_rules = array(
                    array(
                        'field' => 'parent',
                        'label' => 'Menu parent',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'name',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'name_alias',
                        'label' => 'Document alias',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'intro',
                        'label' => 'Menu intro',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
                );

                if (empty($_POST['user_type'])) {
                    $validator_rules[] = array(
                        'field' => 'user_type',
                        'label' => 'User type',
                        'rules' => array('required' => '')
                    );
                }
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (!($uploadFolder = checkEncriptedFolder($_POST['upload_folder']))) {
                    jsonResponse(translate('systmess_error_select_file_to_upload'));
                }

                $doc_alias = cleanInput($_POST['name_alias']);
                $exist_doc = $this->user_guide->get_user_guide_by_alias($doc_alias);
                if (!empty($exist_doc)) {
                    jsonResponse('Error: The alias name are already used. Please use another one.');
                }

                $id_parent = intVal($_POST['parent']);

                $insert = array(
                    'menu_title' => cleanInput($_POST['name']),
                    'menu_alias' => $doc_alias,
                    'menu_icon' => cleanInput($_POST['name_icon']),
                    'menu_intro' => cleanInput($_POST['intro']),
                    'menu_description' => $_POST['text'],
                    'id_parent' => $id_parent
                );

                if (!empty($_POST['video_buyer'])) {
                    $insert['menu_video_buyer'] = $_POST['video_buyer'];
                }
                if (!empty($_POST['video_seller'])) {
                    $insert['menu_video_seller'] = $_POST['video_seller'];
                }
                if (!empty($_POST['video_shipper'])) {
                    $insert['menu_video_shipper'] = $_POST['video_shipper'];
                }
                $menu_position = $this->user_guide->get_last_user_guide_position($id_parent);
                $insert['menu_position'] = $menu_position + 1;

                if (!$id_menu = $this->user_guide->insert_user_guide($insert)) {
                    jsonResponse('You cannot add menu now. Please try again later.');
                }

                $menu_usertype_rel = array();
                foreach ($_POST['user_type'] as $user_type) {
                    $menu_usertype_rel[] = array(
                        'rel_id_menu' => $id_menu,
                        'rel_user_type' => $user_type
                    );
                }

                if (!empty($menu_usertype_rel)) {
                    $this->user_guide->set_user_guide_relation($menu_usertype_rel);
                }

                $breadcrumbs = json_encode(array($id_menu => $insert['menu_title']));
                if ($insert['id_parent'] != 0) {
                    //insert in parents
                    $parent = $this->user_guide->get_user_guide($id_parent);

                    $parents = $this->user_guide->parents_from_breadcrumbs($parent['menu_breadcrumbs']);
                    $this->user_guide->append_children_to_parents($id_menu, $parents);
                    $breadcrumbs = $parent['menu_breadcrumbs'] . ',' . $breadcrumbs;
                }
                //update breadcrumbs
                $this->user_guide->update_user_guide($id_menu, array('menu_breadcrumbs' => $breadcrumbs, 'menu_actualized' => 1));
                $this->elasticuser_guide->sync($id_menu);

                //region Text & image processing
                // Load sanitize library
                $sanitizer = tap(library(TinyMVC_Library_Cleanhtml::class), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea(array(
                        'attribute' => 'data-video,colspan,rowspan'
                    ));
                    $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><tbody><tr><td><thead><th>');
                });

                $update = $postImages = [];

                try {
                    $postProcessedImages = $this->processTextImages($_POST['text'], $id_menu);

                    if (!empty($postProcessedImages['paths'])) {
                        $postImages = array_flip(array_flip($postProcessedImages['paths']));
                    }

                    $menuDescription = $sanitizer->sanitize($this->change_content_paths(
                        $_POST['text'],
                        $id_menu,
                        $postImages,
                        $uploadFolder,
                    ));

                } catch (\Exception $exception) {
                    $this->user_guide->delete_user_guide($id_menu);

                    jsonResponse(translate('systmess_error_user_guide_not_allowed_images'));
                }
                //endregion Text & image processing

                if (!$this->user_guide->update_user_guide($id_menu, ['menu_description' => $menuDescription])) {
                    $this->user_guide->delete_user_guide($id_menu);

                    jsonResponse(translate('systmess_internal_server_error'));
                }

                //region Inline images
                // Copying inline images
                if (!empty($postImages)) {
                    $basePath = getImgPath('user_guide.inline', ['{ID}' => $id_menu]);
                    $cwdPath = getcwd();

                    $images = array_filter(array_map(
                        function ($imagePath) use ($cwdPath) {
                            $imagePath = $cwdPath . $imagePath;

                            return file_exists($imagePath) ? $imagePath : null;
                        },
                        $postImages
                    ));

                    if (!empty($images)) {
                        $copy_result = $this->upload->copy_images_new(array(
                            'images'      => $images,
                            'destination' => $basePath,
                            'change_name' => false,
                        ));

                        if (!empty($copy_result['errors'])) {
                            $this->user_guide->delete_user_guide($id_menu);

                            jsonResponse($copy_result['errors']);
                        }
                    }
                }

                jsonResponse('The menu has been successfully saved.', 'success');
            break;
            case 'edit_menu':
                $validator_rules = array(
                    array(
                        'field' => 'id_menu',
                        'label' => 'Menu info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'name',
                        'label' => 'Menu title',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'intro',
                        'label' => 'Menu intro',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
                );

                if (empty($_POST['user_type'])) {
                    $validator_rules[] = array(
                        'field' => 'user_type',
                        'label' => 'User type',
                        'rules' => array('required' => '')
                    );
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (!($uploadFolder = checkEncriptedFolder($_POST['upload_folder']))) {
                    jsonResponse(translate('systmess_error_select_file_to_upload'));
                }

                $id_menu = intVal($_POST['id_menu']);
                $menu = $this->user_guide->get_user_guide($id_menu);
                if (empty($menu)) {
                    jsonResponse('Error: This menu item does not exist.');
                }

                $id_parent = intVal($_POST['parent']);
                $children_list = $menu['menu_children'];
                $children_array = array_filter(explode(',', $children_list));
                if (in_array($id_parent, $children_array)) {
                    jsonResponse('Error: New parent cannot be from children menu. Please try again later.');
                }

                $update = array(
                    'menu_title' => cleanInput($_POST['name']),
                    'menu_icon' => cleanInput($_POST['name_icon']),
                    'menu_intro' => cleanInput($_POST['intro']),
                    'menu_description' => $_POST['text'],
                    'menu_video_buyer' => '',
                    'menu_video_seller' => '',
                    'menu_video_shipper' => ''
                );

                if (!empty($_POST['video_buyer'])) {
                    $update['menu_video_buyer'] = $_POST['video_buyer'];
                }
                if (!empty($_POST['video_seller'])) {
                    $update['menu_video_seller'] = $_POST['video_seller'];
                }
                if (!empty($_POST['video_shipper'])) {
                    $update['menu_video_shipper'] = $_POST['video_shipper'];
                }

                if (!$this->user_guide->update_user_guide($id_menu, $update)) {
                    jsonResponse('You cannot change menu now. Please try again later.');
                }

                $this->user_guide->delete_user_guide_relation($id_menu);
                $menu_usertype_rel = array();
                foreach ($_POST['user_type'] as $user_type) {
                    $menu_usertype_rel[] = array(
                        'rel_id_menu' => $id_menu,
                        'rel_user_type' => $user_type
                    );
                }
                if (!empty($menu_usertype_rel)) {
                    $this->user_guide->set_user_guide_relation($menu_usertype_rel);
                }

                $breadcrumbs = json_encode(array($id_menu => $update['menu_title']));
                $old_breadcrumbs = $menu['menu_breadcrumbs'];

                if ($update['menu_title'] != $menu['menu_title'] && !empty($menu['menu_breadcrumbs'])) {
                    //actualize menu breadcrumb if name changed
                    $breadcrumbs = str_replace(json_encode(array($id_menu => $menu['menu_title'])), $breadcrumbs, $menu['menu_breadcrumbs']);
                }

                if ($update['menu_title'] != $menu['menu_title']) {
                    //actualize children breadcrumb
                    $list_for_replace = $id_menu;
                    if (!empty($children_list))
                        $list_for_replace .= ',' . $children_list;

                    $this->user_guide->replace_breadcrumbs_part($old_breadcrumbs, $breadcrumbs,  $list_for_replace);
                }

                //region Text & image processing
                // Load sanitize library
                $sanitizer = tap(library(TinyMVC_Library_Cleanhtml::class), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea(array(
                        'attribute' => 'data-video,colspan,rowspan'
                    ));
                    $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><tbody><tr><td><thead><th>');
                });

                $postImages = [];

                try {
                    $postProcessedImages = $this->processTextImages($_POST['text'], $id_menu);

                    if (!empty($postProcessedImages['paths'])) {
                        $postImages = array_flip(array_flip($postProcessedImages['paths']));
                    }

                    $update['menu_description'] = $sanitizer->sanitize($this->change_content_paths(
                        $_POST['text'],
                        $id_menu,
                        $postImages,
                        $uploadFolder,
                    ));

                } catch (\Exception $exception) {
                    jsonResponse(translate('systmess_error_user_guide_not_allowed_images'));
                }
                //endregion Text & image processing

                if (!$this->user_guide->update_user_guide($id_menu, $update)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                //region Inline images
                // Copying inline images
                if (!empty($postImages)) {
                    $basePath = getImgPath('user_guide.inline', ['{ID}' => $id_menu]);
                    $cwdPath = getcwd();

                    $images = array_filter(array_map(
                        function ($imagePath) use ($cwdPath) {
                            $imagePath = $cwdPath . $imagePath;

                            return file_exists($imagePath) ? $imagePath : null;
                        },
                        $postImages
                    ));

                    if (!empty($images)) {
                        $copy_result = $this->upload->copy_images_new([
                            'images'      => $images,
                            'destination' => $basePath,
                            'change_name' => false,
                        ]);

                        if (!empty($copy_result['errors'])) {
                            jsonResponse($copy_result['errors']);
                        }
                    }
                }

                $basePath = $basePath ?? getImgPath('user_guide.inline', ['{ID}' => $id_menu]);
                $postProcessedImages = $this->get_images_from_text($update['menu_description']);
                $inlineImages = array_column($this->get_images_stats($postProcessedImages), null, 'name');
                $postInlineFiles = glob($basePath . '/*.*');

                $deletedImages = [];
                foreach ($postInlineFiles as $file) {
                    if (!isset($inlineImages[basename($file)])) {
                        $deletedImages[] = $file;
                    }
                }

                //region Clean inline images
                if (!empty($deletedImages)) {
                    $getcwd = getcwd();

                    foreach ($deletedImages as $image) {
                        $imagePath = $getcwd . DS . $image;
                        $imagePathInfo = pathinfo($imagePath);
                        $imagePathGlob = $imagePathInfo['dirname'] . DS . '*' .  $imagePathInfo['filename'] . '.*';

                        removeFileByPatternIfExists($imagePath, $imagePathGlob);
                    }
                }
                //endregion Clean inline images

                $this->elasticuser_guide->sync($id_menu);

                jsonResponse('Menu has been updated succesfully', 'success');

            break;
            case 'update_menu':
                $menu_list = $this->tree_to_array($_POST['menu_list']);
                $result = $this->user_guide->update_user_guides_batch($menu_list);
                if ($result > 0) {
                    jsonResponse('Error: The menu cannot be updated now. Please try again late.');
                }

                $this->menu_actualize();
            break;
            case 'get_menu':
                $this->_load_main();
                $menu_html = '<div class="info-alert-b mb-10"><i class="ep-icon ep-icon_info"></i> No data available in the menu.</div>';
                $params = array();
                if (!empty($_POST['user_group'])) {
                    $params['user_type'] = cleanInput($_POST['user_group']);
                }
                $menu = $this->user_guide->get_user_guides($params);
                if (!empty($menu)) {
                    $menu = arrayByKey($menu, 'id_menu');
                    $menu = $this->user_guide->_menu_map($menu);
                    $menu_html = views()->fetch('admin/user_guide/menu_view', ['menuList' => $menu]);
                }
                jsonResponse('', 'success', array('menu_html' => $menu_html));
            break;
            case 'remove_menu':
                $id_menu = intVal($_POST['menu']);
                $menu = $this->user_guide->get_user_guide($id_menu);
                if (empty($menu)) {
                    jsonResponse('Error: This menu item does not exist.');
                }

                if ($menu['menu_actualized'] == 0) {
                    jsonResponse('Error: The menu is not actualized. Please actualize menu first.');
                }

                $menu_children = array_filter(explode(',', $menu['menu_children']));
                if (!empty($menu_children)) {
                    jsonResponse('Error: The menu contain children. Plase remove all children first.');
                }

                //delete menu from it's parents chilren column
                $parents_list = $this->user_guide->parents_from_breadcrumbs($menu['menu_breadcrumbs'], $id_menu);
                if (!empty($parents_list)) {
                    $parents = $this->user_guide->get_user_guides(array('menu_list' => $parents_list, 'columns' => 'id_menu, menu_children'));
                    foreach ($parents as $old_par) {
                        $data['menu_children'] = implode(',', array_diff(explode(',', $old_par['menu_children']), array($id_menu)));
                        $this->user_guide->update_user_guide($old_par['id_menu'], $data);
                    }
                }

                if ($this->user_guide->delete_user_guide($id_menu)) {
                    $this->user_guide->delete_user_guide_relation($id_menu);
                    $this->elasticuser_guide->sync($id_menu);

                    remove_dir(rtrim(getImgPath('user_guide.inline', ['{ID}' => $id_menu]), '/'));

                    jsonResponse('The menu item has been successfully removed.', 'success');
                }

                jsonResponse('Error: You cannot remove this menu item now. Please try again later.');
            break;
        }
    }

    public function upload_photo()
    {
        checkPermisionAjax('manage_doc');

        if (!($uploadFolder = checkEncriptedFolder(uri()->segment(3)))) {
            jsonResponse(translate('systmess_error_file_upload_path_not_correct'));
        }

        if (empty($fileToUpload = $_FILES['userfile'])) {
            jsonResponse(translate('systmess_error_select_file_to_upload'));
        }

        /** @var TinyMVC_Library_Image_intervention $imageInterventionLibrary */
        $imageInterventionLibrary = library(TinyMVC_Library_Image_intervention::class);

        $path = getTempImgPath('user_guide.inline', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
        create_dir($path);

        $result = $imageInterventionLibrary->image_processing(
            $fileToUpload,
            [
                'destination'   => $path,
                'rules'         => config('img.user_guide.inline.rules'),
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        jsonResponse('/' . $path . $result[0]['new_name'], 'success');
    }

    // FULL MENU ACTUALIZATION, CHILDREN AND BREADCRUMBS
    function menu_actualize()
    {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->_load_main();

        //breadcrumbs actualization
        $update = array(
            'menu_actualized'    => 0,
            'menu_children' => '',
            'menu_breadcrumbs'    => ''
        );
        $this->user_guide->update_user_guides($update);

        $menus_bc = arrayByKey($this->user_guide->get_user_guides(array('columns' => 'id_menu, id_parent, menu_title', 'menu_actualized' => 0)), 'id_menu');

        $bc_menu = array();
        if (!empty($menus_bc)) {
            foreach ($menus_bc as $menu) {
                $this->user_guide->update_breadcrumbs($menu['id_menu'], $bc_menu, $menus_bc);
            }
        }

        if (!empty($bc_menu))
            $this->user_guide->update_breadcrumbs_batch($bc_menu);

        //children actualization
        $menus_child = $this->user_guide->get_user_guides(array('columns' => 'id_menu', 'id_parent' => 0));
        if (empty($menus_child)) {
            jsonResponse('Info: The menu does not contain any items to update.', 'info');
        }

        $child_menu = array();
        $menu_list = array();
        foreach ($menus_child as $menu)
            $menu_list[] = $menu['id_menu'];

        if (!empty($menu_list)) {
            $this->user_guide->get_user_guides_children_recursive(implode(',', $menu_list), $child_menu);
        }

        if (!empty($child_menu))
            $this->user_guide->update_user_guide_children_batch($child_menu);

        jsonResponse('Documentation menu were actualized.', 'success');
    }

    // GET SUBMENU ITEMS
    function get_submenu_options()
    {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        checkAdminAjax('manage_doc');

        $this->_load_main();

        $parent = (int) $_POST['id_parent'];
        if ($parent <= 0) {
            jsonResponse('Info: The selected menu does not have children.', 'info');
        }

        $user_guides = $this->user_guide->get_user_guides(array('id_parent' => $parent));
        if (empty($user_guides)) {
            jsonResponse('Info: The selected menu does not have children.', 'info');
        }

        $return_options = '';
        foreach ($user_guides as $user_guide) {
            $return_options .= '<option value="' . $user_guide['id_menu'] . '">' . $user_guide['menu_title'] . '</option>';
        }

        jsonResponse('', 'success', array('menu_options' => $return_options));
    }

    // PRIVATE HELPERS
    // GER SIMPLE ARRAY FROM TREE TO UPDATE ALL MENU ITEMS
    private function tree_to_array($items = array(), $parent = 0)
    {
        if (empty($items)) {
            return array();
        }

        $result = array();
        foreach ($items as $key => $item) {
            $result[] = array(
                'id_menu' => $item['id'],
                'id_parent' => $parent,
                'menu_position' => $key
            );
            if (!empty($item['children'])) {
                $result = array_merge($result, $this->tree_to_array($item['children'], $item['id']));
            }
        }

        return $result;
    }

    //GENERATE PDF FROM HTML
    function doc2pdf()
    {
        ini_set('max_execution_time', 0);
        $this->_load_main();

        $params = array();
        $file_prefix = '';
        if (!empty($_POST['user_group'])) {
            $params['user_type'] = cleanInput($_POST['user_group']);
            $file_prefix = $params['user_type'] . '_';
        }

        $path = 'public/user_guide';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = $path . '/' . $file_prefix . 'user_guide.pdf';

        if (file_exists($file))
            unlink($file);

        $menu = $this->user_guide->get_user_guides($params);
        if (!empty($menu)) {
            foreach ($menu as $item)
                $ds[$item['id_menu']] = array(
                    'menu_title' => $item['menu_title'],
                    'menu_alias' => $item['menu_alias'],
                    'menu_description' => $item['menu_description'],
                    'menu_breadcrumbs' => $item['menu_breadcrumbs'],
                    'id_menu' => $item['id_menu'],
                    'id_parent' => $item['id_parent']
                );

            $menu = arrayByKey($menu, 'id_menu');
            $menu = $this->user_guide->_menu_map($menu);
            $menu_html = widgetDocMenuText($menu);
        }

        $data['document'] = $menu_html;
        $html_text = $this->view->fetch('admin/user_guide/doc2pdf_view', $data);

        // LIBRARY M-PDF
        $this->load->library('mpdf', 'mpdf');
        $mpdf = $this->mpdf->new_pdf();
        $mpdf->h2toc = array('H1' => 0, 'H2' => 1, 'H3' => 2, 'H4' => 3, 'H5' => 4);
        $mpdf->h2bookmarks = array('H1' => 0, 'H2' => 1, 'H3' => 2, 'H4' => 3, 'H5' => 4);
        $footer = '<table width="100%" style="border:0;">
                        <tr>
                            <td align="left" width="50%" style="padding-left:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Export Portal Team</span>
                            </td>
                            <td align="right" width="50%" style="padding-right:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Page {PAGENO}</span>
                            </td>
                        </tr>
                    </table>';
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($html_text);
        $mpdf->Output($file, "F");
        jsonResponse('Success', 'success');
    }

    private function processTextImages($text, $userGuideId)
    {
        $matches = [];
        $imagesInText = $this->get_images_from_text($text, $matches);
        if (empty($imagesInText)) {
            return [];
        }

        // Collect external images
        $externalPaths = [];
        $petentialThreats = $imagesInText;
        foreach ($petentialThreats as $imageUrl) {
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                continue;
            }

            $imageHost = parse_url($imageUrl, PHP_URL_HOST);

            if (
                __HTTP_HOST_ORIGIN !== $imageHost &&
                !endsWith($imageHost, __HTTP_HOST_ORIGIN)
            ) {
                $externalPaths[] = $imageUrl;
            }
        }

        $externalPaths = array_filter($externalPaths);
        if (!empty($externalPaths)) {
            throw new Exception(translate('systmess_error_user_guide_not_allowed_images'));
        }

        $path = getImgPath('user_guide.inline', ['{ID}' => $userGuideId]);

        $imagesPaths = array();
        $temporaryImages = array_filter($matches[1]);
        foreach ($temporaryImages as $key => $image) {
            if (null !== parse_url($image, PHP_URL_HOST)) {
                if (null !== $path && false !== strpos($image, $path)) {
                    continue;
                }

                if (false === strpos($image, __HTTP_HOST_ORIGIN)) {
                    throw new Exception(translate('systmess_error_user_guide_not_allowed_images'));

                    continue;
                }

                list(, $path) = explode(__HTTP_HOST_ORIGIN, $image);
                $image = '/' . trim($path, '/');
            } else {
                if (!(startsWith($image, '/temp') || startsWith($image, 'temp'))) {
                    continue;
                }
            }

            $imagesPaths[] = $image;
        }

        return [
            'collected' => $temporaryImages,
            'paths'     => $imagesPaths,
        ];
    }

    private function get_images_from_text($text, &$matches = [])
    {
        $host = preg_quote(__HTTP_HOST_ORIGIN);
        $pattern = '/<img[^>]*?src=["\'](((\/?temp[^"\'>]+)|(https?\:\/\/([\w]+\.)?' . $host . '\/temp[^"\'>]+))|(https?\:\/\/([\w]+\.)?' . $host . '[^"\'>]+)|([^"\'\s>]+))["\'][^>]*?>/m';
        preg_match_all($pattern, $text, $matches, PREG_PATTERN_ORDER);
        $imagesInText = array_filter($matches[1]);

        return $imagesInText ?: [];
    }

    private function change_content_paths($text, $userGuideId, $source, $uploadFolder)
    {
        if (empty($source)) {
            return $text;
        }

        $tempPath = getTempImgPath('user_guide.inline', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
        $tempUrl = __IMG_URL . $tempPath;

        $basePath = getImgPath('user_guide.inline', ['{ID}' => $userGuideId]);
        $baseUrl = __IMG_URL . $basePath;

        create_dir($basePath);

        $replacements = [];
        foreach ($source as $image) {
            if (!(startsWith($image, '/temp') || startsWith($image, 'temp'))) {
                continue;
            }

            if (false !== strpos($text, $tempUrl = __IMG_URL . trim($image, '/'))) {
                $replacements[$tempUrl] = $baseUrl . substr($tempUrl, mb_strlen($tempUrl));

                continue;
            }

            if (false === strpos($image, $tempPath)) {
                $imageName = basename($image);
                $replacements[$image] = $baseUrl . $imageName;

                continue;
            }

            $replacements[$image] = $baseUrl . substr(trim($image, '/'), mb_strlen($tempPath));
        }

        return empty($replacements) ? $text : strtr($text, $replacements);
    }

    private function get_images_stats($raw)
    {
        if (null === $raw) {
            return [];
        }

        $images = [];
        foreach ($raw as $key => $url) {
            if (null === ($host = parse_url($url, PHP_URL_HOST))) {
                $path = $url;
            } else {
                list(, $path) = explode($host, $url);
            }

            list($realpath) = explode('?', $path);

            $fullpath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($realpath, '/');

            if (file_exists($fullpath)) {
                $imagesize = getimagesize($fullpath);
                $imageinfo = pathinfo($fullpath);
                $post_image = array(
                    'url'       => $url,
                    'path'      => $path,
                    'name'      => $imageinfo['basename'],
                    'filename'  => $imageinfo['filename'],
                    'extension' => $imageinfo['extension'],
                    'width'     => $imagesize[0],
                    'height'    => $imagesize[1],
                    'mime'      => $imagesize['mime'],
                );

                $images[] = $post_image;
            }
        }

        return $images;
    }

    public function download(): Response
    {
        /** @var User_Guide_Model $userGuide */
        $userGuide = model(User_Guide_Model::class);
        $name = (string) uri()->segment(3);
        $lang = (string) uri()->segment(4);
        $grType = (string) uri()->segment(5);
        if (empty($name) || empty($name) || empty($grType)) {
            return new RedirectResponse('/404');
        }

        $guides = $userGuide->getUserGuides();
        $guideTitle = $guides[$name][$lang][$grType];
        try {
            $file = new File(
                sprintf(
                    '%s/%s/%s.pdf',
                    ROOT_PATH,
                    rtrim(str_replace(['{GUIDE_NAME}', '{LANG}'], [$name, $lang], (string) config('files.user_guide.pdf.folder_path')), '/'),
                    $guideTitle
                )
            );
        } catch (FileNotFoundException $e) {
            // If file is not found then we will redirect to the 404 page.
            return new RedirectResponse('/404');
        }

        return (new BinaryFileResponse($file, 200, ['Content-Type' => 'application/pdf'], true))
            ->setContentDisposition('attachment', sprintf('%s.pdf', $guideTitle))
            ->prepare(request())
        ;
    }

    public function get_guides() {
        /** @var User_Guide_Model $userGuide */
        $userGuide = model(User_Guide_Model::class);

        $name = (string) uri()->segment(3);
        $lang = (string) uri()->segment(4);
        $grType = (string) uri()->segment(5);

        if (empty($name) || empty($name) || empty($grType)) {
            show_404();
        }

        $guides = $userGuide->getUserGuides();
        $guideTitle = $guides[$name][$lang][$grType];

        if (!$guideTitle) {
            jsonResponse(translate('systmess_error_sended_data_not_valid'));
        }

        jsonResponse('', 'success');
    }
}

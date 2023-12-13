<?php

/**
 * Pages application controller.
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 * @property \Ep_Modules_Model          $modules
 * @property \Pages_Model               $pages
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 *
 * @author Anton Zencenco
 */
class Pages_Controller extends TinyMVC_Controller
{

    public function configs()
    {
        checkAdmin('admin_site');
        $this->view->display('admin/header_view');
        $this->view->display('admin/pages/configs_view');
        $this->view->display('admin/footer_view');
    }

    public function administration()
    {
        checkAdmin('admin_site');

        $this->load->model('Ep_Modules_Model', 'modules');

        $this->view->assign(array(
            'title'   => 'Pages',
            'modules' => $this->modules->get_all_modules(),
        ));
        $this->view->display('admin/header_view');
        $this->view->display('admin/pages/index_view');
        $this->view->display('admin/footer_view');
    }

    public function administration_dt()
    {
        checkAdminAjaxDT('admin_site');

        $this->load->model('Pages_Model', 'pages');

        $order = array();
        $conditions = array();
        $post_data = $_POST;
        $limit = empty($post_data['iDisplayLength']) ? null : $post_data['iDisplayLength'];
        $skip = empty($post_data['iDisplayStart']) ? null : $post_data['iDisplayStart'];
        if ($post_data['iSortingCols'] > 0) {
            for ($i = 0; $i < $post_data['iSortingCols']; ++$i) {
                switch ($post_data['mDataProp_' . intval($post_data['iSortCol_' . $i])]) {
                    case 'dt_id':
                        $order['id_page'] = $post_data['sSortDir_' . $i];

                        break;
                    case 'dt_name':
                        $order['page_name'] = $post_data['sSortDir_' . $i];

                        break;
                    case 'dt_action':
                        $order['page_action'] = $post_data['sSortDir_' . $i];

                        break;
                    case 'dt_controller':
                        $order['page_controller'] = $post_data['sSortDir_' . $i];

                        break;
                    case 'dt_created_at':
                        $order['page_created_at'] = $post_data['sSortDir_' . $i];

                        break;
                    case 'dt_updated_at':
                        $order['page_updated_at'] = $post_data['sSortDir_' . $i];

                        break;
                }
            }
        }

        if (isset($post_data['url'])) {
            $conditions['url'] = cleanInput($post_data['url']);
        }
        if (isset($post_data['module'])) {
            $conditions['module'] = (int) cleanInput($post_data['module']);
        }
        if (isset($post_data['created_from'])) {
            $conditions['created_from'] = formatDate(cleanInput($post_data['created_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }
        if (isset($post_data['created_to'])) {
            $conditions['created_to'] = formatDate(cleanInput($post_data['created_to']) . '23:59:59', 'Y-m-d H:i:s');
        }
        if (isset($post_data['updated_from'])) {
            $conditions['updated_from'] = formatDate(cleanInput($post_data['updated_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }
        if (isset($post_data['updated_to'])) {
            $conditions['updated_to'] = formatDate(cleanInput($post_data['updated_to']) . '23:59:59', 'Y-m-d H:i:s');
        }
        if (isset($post_data['translation_status'])) {
            $conditions['ready_for_translation'] = (int) $post_data['translation_status'];
        }
        if (isset($post_data['title'])) {
            $conditions['title'] = cleanInput($post_data['title']);
        }
        if (isset($post_data['controller'])) {
            $conditions['controller'] = cleanInput($post_data['controller']);
        }
        if (isset($post_data['action'])) {
            $conditions['action'] = cleanInput($post_data['action']);
        }

        $params = compact('conditions', 'order', 'limit', 'skip');
        $pages = model('pages')->get_pages($params);
        $pages_count = model('pages')->count_pages($params);
        $output = array(
            'sEcho'                => (int) $post_data['sEcho'],
            'iTotalRecords'        => $pages_count,
            'iTotalDisplayRecords' => $pages_count,
            'aaData'               => array(),
        );

        if (empty($pages)) {
            jsonResponse(null, 'success', $output);
        }

        $site_languages = model('translations')->get_languages(array('lang_active' => 1));

        // Get modules for the pages
        $pages_ids = array_column($pages, 'id_page');
        $pages_modules = model('pages')->get_pages_modules($pages_ids, array('id_page', 'id_module', 'name_module'), array('modules' => true));
        $pages_modules = arrayByKey($pages_modules, 'id_page', true);

        foreach ($pages as $index => $page) {
            $page_id = $page['id_page'];
            $edit_url = __SITE_URL . 'pages/popup_forms/edit/' . $page_id;
            $delete_url = __SITE_URL . 'pages/ajax_operations/delete/' . $page_id;
            $change_translation_status_url = __SITE_URL . 'pages/ajax_operations/change_translation_status/' . $page_id;
            $modules = array();
            if(isset($pages_modules[$page_id])) {
                foreach ($pages_modules[$page_id] as $module) {
                    $module_id = $module['id_module'];
                    $module_name = cleanOutput($module['name_module']);
                    $modules[] = "
                        <a class=\"btn btn-xs btn-primary mb-5 dt_filter\"
                            data-title=\"Assigned to module\"
                            data-value-text=\"{$module_name}\"
                            data-value=\"{$module_id}\"
                            data-name=\"module\"
                            title=\"Assigned to module: '{$module_name}'\">
                            {$module_name}
                        </a>
                    ";
                }
            }

            $dt_languages = '';
            $translations_status_labels = array(
                array(
                    'label'             => '<span class="label label-default">NO</span>',
                    'change_btn_title'  => 'Mark as ready for translation'
                ),
                array(
                    'label'             => '<span class="label label-success">YES</span>',
                    'change_btn_title'  => 'Mark as not ready for translation'
                ),
            );

            foreach ($site_languages as $key => $language) {
                $page_column = 'lang_' . $language['lang_iso2'];

                if (!isset($page[$page_column])) {
                    $dt_languages .=    '<span
                                            class="btn btn-xs tt-uppercase mnw-30 mb-5 btn-warning call-systmess"
                                            title="' . $language['lang_name'] . '"
                                            data-message="' . $language['lang_name'] . ' language has not been added to the page table."
                                            data-type="warning">' .
                                            $language['lang_iso2'] .
                                        '</span> ';
                    continue;
                }

                $is_translated_page = (int) $page[$page_column];
                $is_domain_lang = 'domain' === $language['lang_url_type'];

                $btn_class = 'btn-danger';
                if($is_domain_lang && $is_translated_page){
                    $btn_class = 'btn-primary';
                } elseif($is_domain_lang){
                    $btn_class = 'btn-success';
                } elseif($is_translated_page){
                    $btn_class = 'btn-warning';
                }

                $confirm_action = $is_translated_page ? 'disable' : 'enable';
                $data_message = 'Are you sure you want to ' . $confirm_action . ' ' . $language['lang_name'] . ' for ' . $page['page_name'] . ' page';
                $additional_tooptip = implode(', ', array_filter(array(
                    $language['lang_url_type'],
                    $is_translated_page ? 'page' : null
                )));

                $dt_languages .=    '<span class="btn ' . $btn_class . ' btn-xs tt-uppercase mnw-30  mb-5 confirm-dialog"
                                        title="'. $language['lang_name'] . ' ('. $additional_tooptip . ')' .'"
                                        data-message="' . $data_message . '"
                                        data-callback="reverseLangConfig"
                                        data-id-page="' . $page_id . '"
                                        data-page-column="' . $page_column . '">' .
                                        $language['lang_iso2'] .
                                    '</span> ';
            }

            $edit_page_btn = '<li><a href="' . $edit_url .'"
                                    class="fancyboxValidateModalDT fancybox.ajax"
                                    data-title="Edit page">
                                    <span class="ep-icon ep-icon_pencil"></span>Edit page
                                   </a>
                              </li>';

            $remove_page_btn = '<li><a href="' . $delete_url . '"
                                    class="confirm-dialog"
                                    data-page="' . $page_id . '"
                                    data-callback="removePage"
                                    data-message="Are you sure you want to delete this page?">
                                    <span class="ep-icon ep-icon_remove txt-red"></span>Remove page
                                </a></li>';

            $change_translation_status_btn = '<li><a href="' . $change_translation_status_url . '"
                                                class="confirm-dialog"
                                                data-current-status="' . $page['is_ready_for_translation'] . '"
                                                data-callback="reverseTranslationStatus"
                                                data-message="' . $translations_status_labels[$page['is_ready_for_translation']]['change_btn_title'] . '?">
                                                <span class="ep-icon ep-icon_language-stroke"></span>' . $translations_status_labels[$page['is_ready_for_translation']]['change_btn_title'] . '
                                            </a></li>';

            $page_status = $page['is_public'] ? '<span class="btn btn-success btn-xs tt-uppercase mnw-30 mb-5" title="Page is available for guests.">Public page</span>' : '<span class="btn btn-warning btn-xs tt-uppercase mnw-30 mb-5" title="Page is available only for registered users.">Private page</span>';

            $output['aaData'][] = array(
                'dt_id'                     => $page_id,
                'dt_name'                   => cleanOutput($page['page_name']),
                'dt_action'                 => cleanOutput($page['page_action']),
                'dt_controller'             => cleanOutput($page['page_controller']),
                'dt_description'            => $page_status . '<br>' . cleanOutput($page['page_description']) . '<br> Modules: ' . implode('', $modules),
                'dt_created_at'             => getDateFormat($page['page_created_at']),
                'dt_updated_at'             => getDateFormat($page['page_updated_at']),
                'dt_languages'              => $dt_languages,
                'dt_ready_for_translation'  => $translations_status_labels[$page['is_ready_for_translation']]['label'],
                'dt_actions'                => '<div class="dropdown">
                                                    <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">'
                                                        . $change_translation_status_btn
                                                        . $edit_page_btn
                                                        . $remove_page_btn .
                                                    '</ul>
                                                </div>'
            );
        }

        jsonResponse(null, 'success', $output);
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        checkAdminAjaxModal('admin_site');

        $this->load->model('Pages_Model', 'pages');

        $page_id = (int) $this->uri->segment(4);
        $action = (string) cleanInput($this->uri->segment(3));
        switch ($action) {
            case 'add':
                $this->show_page_create_form();

                break;
            case 'edit':
                $this->show_page_update_form($page_id);

                break;
            default:
                show_404();

                break;
        }
    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if(!logged_in()){
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right_or('admin_site')) {
            jsonResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->load->model('Pages_Model', 'pages');

        $id = (int) $this->uri->segment(4);
        $action = (string) cleanInput($this->uri->segment(3));
        switch ($action) {
            case 'create':
                $this->create_page();

                break;
            case 'update':
                $this->update_page($id);

                break;
            case 'delete':
                $this->delete_page($id);

                break;
            case 'load_module_pages':
                $this->load_pages($id);

                break;
            case 'change_language_config':
                $this->toggle_language_config($id);

                break;
            case 'change_translation_status':
                $this->toggle_translation_status($id);

                break;
            default:
                show_404();

                break;
        }
    }

    private function show_page_create_form()
    {
        $this->load->model('Ep_Modules_Model', 'modules');
        $this->view->display('admin/pages/add_page_form', array(
            'action'  => __SITE_URL . 'pages/ajax_operations/create',
            'modules' => $this->modules->get_all_modules(),
        ));
    }

    private function show_page_update_form($page_id)
    {
        $this->load->model('Ep_Modules_Model', 'modules');

        $page = $this->pages->find_page($page_id);
        if (empty($page)) {
            messageInModal(translate('systmess_error_page_does_not_exist'));
        }
        $page['page_view_files'] = json_decode($page['page_view_files'], true);
        $page['page_view_files'] = null !== $page['page_view_files'] ? $page['page_view_files'] : array();
        $page['page_view_files'] = array_map(function($file) {
            $prefix = "/tinymvc/myapp/";
            $view_prefix = '/views/';

            if (strpos($file, $prefix) === 0) {
                return substr($file, strlen($prefix));
            }
            if (strpos($file, $view_prefix) === 0) {
                return substr($file, strlen($view_prefix));
            }

            return $file;
        }, $page['page_view_files']);

        $modules = $this->modules->get_all_modules();
        $page_modules = array_column($this->pages->get_page_modules($page_id, 'id_module'), 'id_module');
        foreach ($modules as &$module) {
            $module['selected'] = in_array($module['id_module'], $page_modules);
        }

        $this->view->display('admin/pages/edit_page_form', array(
            'action'  => __SITE_URL . "pages/ajax_operations/update/{$page_id}",
            'page'    => $page,
            'modules' => $modules
        ));
    }

    private function create_page()
    {
        $validator_rules = array(
            array(
                'field' => 'name',
                'label' => 'Page name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'controller',
                'label' => 'Page controller name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'action',
                'label' => 'Page action name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'url',
                'label' => 'Page URL',
                'rules' => array('max_len[2000]' => ''),
            ),
            array(
                'field' => 'description',
                'label' => 'Page description',
                'rules' => array('max_len[500]' => ''),
            ),
            array(
                'field' => 'modules',
                'label' => 'Modules',
                'rules' => array('required' => ''),
            ),
        );

        if (!empty($validator_rules)) {
            $this->validator->reset_postdata();
            $this->validator->clear_array_errors();
            $this->validator->validate_data = $_POST;
            $this->validator->set_rules($validator_rules);
            if (!$this->validator->validate()) {
                jsonResponse($this->validator->get_array_errors());
            }
        }

        $modules = array_filter(array_map(
            function($module) { return (int) cleanInput($module); },
            !empty($_POST['modules']) ? $_POST['modules'] : array()
        ));

        $page = array(
            'page_name'         => trim(cleanInput($_POST['name'])),
            'page_action'       => trim(cleanInput($_POST['action'])),
            'page_controller'   => trim(cleanInput($_POST['controller'])),
            'page_description'  => trim(cleanInput($_POST['description'])),
            'page_url_template' => trim(cleanInput($_POST['url'])),
            'page_view_files'   => array_filter(array_map(
                function($page) {
                    $page = trim((string) cleanInput($page));
                    return !empty($page) ? "/views/${page}" : null;
                },
                !empty($_POST['views']) ? $_POST['views'] : array()
            )),
        );
        $page['page_hash'] = getPageHash($page['page_controller'], $page['page_action']);
        $page['is_public'] = empty($_POST['is_public']) ? 0 : 1;

        $page_id = $this->pages->create_page($page);
        if (!$page_id || !$this->pages->create_relations($page_id, $modules)) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        jsonResponse(translate('systmess_success_page_added'), 'success');
    }

    private function update_page($page_id)
    {
        $validator_rules = array(
            array(
                'field' => 'name',
                'label' => 'Page name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'controller',
                'label' => 'Page controller name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'action',
                'label' => 'Page action name',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'url',
                'label' => 'Page URL',
                'rules' => array('max_len[2000]' => ''),
            ),
            array(
                'field' => 'description',
                'label' => 'Page description',
                'rules' => array('max_len[500]' => ''),
            ),
        );

        if (!empty($validator_rules)) {
            $this->validator->reset_postdata();
            $this->validator->clear_array_errors();
            $this->validator->validate_data = $_POST;
            $this->validator->set_rules($validator_rules);
            if (!$this->validator->validate()) {
                jsonResponse($this->validator->get_array_errors());
            }
        }

        $page = $this->pages->find_page($page_id);
        if (empty($page)) {
            jsonResponse(translate('systmess_error_page_does_not_exist'));
        }

        $modules = array_filter(
            array_map(
                function($module) {
                    return cleanInput($module);
                },
                !empty($_POST['modules']) ? $_POST['modules'] : array()
            )
        );
        $page['page_name'] = trim(cleanInput($_POST['name']));
        $page['page_action'] = trim(cleanInput($_POST['action']));
        $page['page_controller'] = trim(cleanInput($_POST['controller']));
        $page['page_description'] = trim(cleanInput($_POST['description']));
        $page['page_url_template'] = trim(cleanInput($_POST['url']));
        $page['page_view_files'] = array_filter(array_map(
            function($page) {
                $page = trim((string) cleanInput($page));

                return !empty($page) ? "/views/${page}" : null;
            },
            !empty($_POST['views']) ? $_POST['views'] : array()
        ));
        $page['page_hash'] = getPageHash($page['page_controller'], $page['page_action']);
        $page['is_public'] = empty($_POST['is_public']) ? 0 : 1;

        if (!$this->pages->update_page($page_id, $page) || !$this->pages->replace_relations($page_id, $modules)) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        jsonResponse(translate('systmess_success_page_updated'), 'success');
    }

    private function delete_page($page_id)
    {
        if (!$this->pages->is_page_exists($page_id)) {
            jsonResponse(translate('systmess_error_page_does_not_exist'));
        }

        if (!$this->pages->remove_relations($page_id) || !$this->pages->remove_page($page_id)) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        jsonResponse(translate('systmess_success_page_deleted'), 'success');
    }

    private function load_pages($module_id)
    {
        $this->load->model('Ep_Modules_Model', 'modules');

        $conditions = array();
        if(!empty($module_id)) {
            $module = $this->modules->get_ep_module($module_id);
            if(empty($module)) {
                jsonResponse(translate('systmess_error_module_does_not_exist'));
            }

            $conditions = array(
                'module' => $module_id
            );
        }

        jsonResponse(null, 'success', array(
            'type' => 'page',
            'data' => $this->pages->get_pages(array(
                'columns'    => array('id_page as id', 'page_name as name'),
                'conditions' => $conditions
            )),
        ));
    }

    private function toggle_language_config($page_id)
    {
        if (!isset($_POST['column'])) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $column = $_POST['column'];
        $page = $this->pages->find_page($page_id);

        if (empty($page) || !isset($page[$column])) {
            jsonResponse(translate('systmess_error_page_does_not_exist'));
        }

        $new_value = (int) $page[$column] == 1 ? 0 : 1;

        if (!$this->pages->simple_update($page_id, array($column => $new_value))) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        $message = 'Language was successful ' . ($new_value == 0 ? 'disabled' : 'enabled');
        jsonResponse($message, 'success');
    }

    private function toggle_translation_status($page_id)
    {
        $page = $this->pages->find_page($page_id);

        if (empty($page)) {
            jsonResponse(translate('systmess_error_page_does_not_exist'));
        }

        $current_page_status = (int) $_POST['current_page_status'];

        if (!in_array($current_page_status, array(0, 1)) && $current_page_status != $page['is_ready_for_translation']) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $new_page_status = (int) !$current_page_status;

        if (!$this->pages->simple_update($page_id, array('is_ready_for_translation' => $new_page_status))) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        $current_date = new \DateTimeImmutable();

        $new_log_data = array(
            'date' => $current_date->format('Y-m-d H:i:s'),
            'id_user' => privileged_user_id(),
            'set_value' => $new_page_status
        );

        $current_log = json_decode($page['translation_status_log'], true);

        $updated_log = empty($current_log) ? array() : $current_log;
        array_unshift($updated_log, $new_log_data);

        if (!$this->pages->simple_update($page_id, array('translation_status_log' => json_encode($updated_log)))) {
            //resset new value
            $this->pages->simple_update($page_id, array('is_ready_for_translation' => $current_page_status));

            jsonResponse(translate('systmess_error_db_insert_error'));
        }

        jsonResponse($page['page_name'] . ' page updated successfully', 'success');
    }
}

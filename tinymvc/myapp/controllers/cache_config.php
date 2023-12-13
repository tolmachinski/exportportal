<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cache_Config_Controller extends TinyMVC_Controller
{
    function index() {
        headerRedirect(__SITE_URL . 'cache_config/administration');
    }

    function administration() {
        checkAdmin('manage_content');

        views(['admin/header_view', 'admin/cache_config/index_view', 'admin/footer_view'], ['title' => 'Cache gonfiguration']);
    }

    public function ajax_list_dt() {
        checkIsAjax();
        checkPermision('manage_content');

        /**
         * @var Cache_Config_Model $cacheConfigModel
         */
        $cacheConfigModel = model(Cache_Config_Model::class);

        // $dtFilters = dtConditions($_POST, [
        //     ['as' => 'date_from',       'key' => 'reg_date_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        //     ['as' => 'date_to',         'key' => 'reg_date_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        //     ['as' => 'search',          'key' => 'search',             'type' => 'cleanInput'],
        //     ['as' => 'enable',          'key' => 'enable',             'type' => 'int'],
        //     ['as' => 'moderated',       'key' => 'moderated',          'type' => 'int'],
        // ]);

        $sortBy = flat_dt_ordering($_POST, array(
            'dt_cache_key'      => 'cache_key',
            'dt_folder'         => 'folder',
            'dt_cache_time'     => 'cache_time',
        ));

        $params = array_merge(
            [
                'start_from'    => (int) $_POST["iDisplayStart"],
                'sort_by'       => empty($sortBy) ? ['id_config-asc'] : $sortBy,
                'limit'         => (int) $_POST["iDisplayLength"],
            ],
            $dtFilters ?? []
        );

        $tempData = $cacheConfigModel->get_cache_configs($params);
        $countConfigs = $cacheConfigModel->count_cache_configs();

        $output = array(
            "sEcho" => (int) $_POST['sEcho'],
            "iTotalRecords" => $countConfigs,
            "iTotalDisplayRecords" => $countConfigs,
            "aaData" => []
        );

        if (empty($tempData)) {
			jsonResponse('', 'success', $output);
        }

        foreach ($tempData as $conf) {
            $output['aaData'][] = array(
                'dt_id_config' => '<input type="checkbox" class="check-one mr-5 pull-left" data-id="' . $conf['id_config'] . '">' . $conf['id_config'],
                'dt_cache_key' => $conf['cache_key'],
                'dt_folder' => $conf['folder'],
                'dt_cache_time' => $conf['cache_time'],
                'dt_description' => $conf['description'],
                'dt_actions' => '<a class="confirm-dialog ep-icon ep-icon_' . (($conf['enable'] == 1) ? "visible" : "invisible") . '" href="#" data-message="Are you sure you want to change enable?" data-callback="change_enable" data-status="trash" title="Change enable" data-id="' . $conf['id_config'] . '"></a>'
                . '<a class="ep-icon ep-icon_trash txt-red confirm-dialog" href="#" data-message="Are you sure you want to clean cache?" data-callback="delete_clean" data-op="clean" data-id="' . $conf['id_config'] . '" title="Clean cache"></a>'
                . '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit configuration" href="' . __SITE_URL . 'cache_config/popups_forms/edit/' . $conf['id_config'] . '/" data-title="Edit configuration"></a>'
                . '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" href="#" data-message="Are you sure you want to delete this configuration?" data-callback="delete_clean" data-op="delete" title="Delete configuration" data-id="' . $conf['id_config'] . '"></a>'
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popups_forms() {
        if (!isAjaxRequest())
            show_404();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            messageInModal(translate("systmess_error_rights_perform_this_action"));

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'add':
                $this->view->display('admin/cache_config/modal_view');
                break;
            case 'edit':
                $this->load->model('Cache_Config_Model', 'cache_config');

                $id_config = intval($this->uri->segment(4));

                $data['cache_config'] = $this->cache_config->get_cache_config($id_config);
                $this->view->display('admin/cache_config/modal_view', $data);
                break;
        }
    }

    public function ajax_operation() {
        if (!isAjaxRequest())
            show_404();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));


        $op = $this->uri->segment(3);
        if (empty($op))
            jsonResponse('Error: you cannot perform this action. Please try again later.');

        $this->load->model('Cache_Config_Model', 'cache_config');

        switch ($op) {
            case 'change_enable':
                $id_config = intval($_POST['id_config']);

                if (!$id_config)
                    jsonResponse('Error: This ID is invalid');

                if ($this->cache_config->update_cache_config($id_config, array('enable' => intval($_POST['enable']))))
                    jsonResponse('The  enable option was changed', 'success');
                else
                    jsonResponse('Error: you cannot change the enable option. Please try again later');
                break;
            case 'delete':
                $id = intval($_POST['id_config']);

                if (!$id)
                    jsonResponse('Error: This ID is invalid');

                $data = $this->cache_config->get_cache_config($id);

                if ($this->cache_config->delete_cache_config($id)) {
                    remove_dir_hidden(__CACHE_FOLDER . $data['folder']);
                    jsonResponse('The cache configuration was deleted', 'success');
                } else
                    jsonResponse('Error: you cannot delete cache configuration now. Please try again later');
                break;
            case 'clean':
                $id = intval($_POST['id_config']);

                if (!$id)
                    jsonResponse('Error: This ID is invalid');

                $data = $this->cache_config->get_cache_config($id);

                remove_dir(__CACHE_FOLDER . $data['folder'], false);
                jsonResponse('The cache was cleaned', 'success');
            break;
            case 'clean_checked':
                if (empty($_POST['list_id'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $listIds = array_map('intval', $_POST['list_id']);
                $data = $this->cache_config->get_cache_configs(['list_id' => implode(',', $listIds)]);

                foreach ($data as $config) {
                    remove_dir(__CACHE_FOLDER . $config['folder'], false);
                }

                jsonResponse('The checked cache was cleaned', 'success');
                break;
            case 'edit':
                $validator_rules = array(
                    array(
                        'field' => 'cache_key',
                        'label' => 'Key',
                        'rules' => array('required' => '', 'for_url' => '',)
                    ), array(
                        'field' => 'folder',
                        'label' => 'Folder',
                        'rules' => array('required' => '', 'for_url' => '')
                    ), array(
                        'field' => 'cache_time',
                        'label' => 'Time',
                        'rules' => array('required' => '', 'integer' => '')
                    ), array(
                        'field' => 'enable',
                        'label' => 'Enable',
                        'rules' => array('required' => '')
                    ), array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $data = array(
                    'cache_key' => cleanInput($_POST['cache_key']),
                    'folder' => cleanInput($_POST['folder']),
                    'cache_time' => intVal($_POST['cache_time']),
                    'enable' => intVal($_POST['enable']),
                    'description' => cleanInput($_POST['description'])
                );


                $id_config = intval($_POST['id_config']);

                if ($this->cache_config->check_key($data['cache_key'], $id_config))
                    jsonResponse('Error: This key already exists.');


                if ($this->cache_config->update_cache_config($id_config, $data)) {
                    if ($data['folder'] != $_POST['old_folder'])
                        rename(__CACHE_FOLDER . $_POST['old_folder'], __CACHE_FOLDER . $data['folder']);

                    jsonResponse('All changes have been saved', 'success');
                }else {
                    jsonResponse('Error: you cannot save this configuration now. Please try again later');
                }

                break;
            case 'insert':
                $validator_rules = array(
                    array(
                        'field' => 'cache_key',
                        'label' => 'Key',
                        'rules' => array('required' => '', 'for_url' => '',)
                    ), array(
                        'field' => 'folder',
                        'label' => 'Folder',
                        'rules' => array('required' => '', 'for_url' => '')
                    ), array(
                        'field' => 'cache_time',
                        'label' => 'Time',
                        'rules' => array('required' => '', 'integer' => '')
                    ), array(
                        'field' => 'enable',
                        'label' => 'Enable',
                        'rules' => array('required' => '')
                    ), array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $data = array(
                    'cache_key' => cleanInput($_POST['cache_key']),
                    'folder' => cleanInput($_POST['folder']),
                    'cache_time' => intVal($_POST['cache_time']),
                    'enable' => intVal($_POST['enable']),
                    'description' => cleanInput($_POST['description'])
                );

                if ($this->cache_config->check_key($data['cache_key']))
                    jsonResponse('Error: This key already exists.');

                if ($this->cache_config->add_cache_config($data)) {
                    mkdir(__CACHE_FOLDER . $data['folder'], 0755);
                    jsonResponse('Configuration was saved successfully', 'success');
                } else {
                    jsonResponse('Error: you cannot save this configuration now. Please try again later');
                }
                break;
        }
    }

    public function test() {
        echo __CACHE_FOLDER . 'dsddss';
        rename(__CACHE_FOLDER . 'dsddss', __CACHE_FOLDER . 'aaaaaaaa');
    }

}

?>

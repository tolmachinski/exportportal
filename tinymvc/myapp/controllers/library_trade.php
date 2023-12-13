<?php

use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Trade_Controller extends TinyMVC_Controller {
    private $dir_config = 'library_store/';
    private $id_config  = 9;

    /**
     * @var Config_Lib_Model $library_config_model
     */
    private $library_config_model;

    /**
     * @var Lib_Trade_Model $library_trade_model
     */
    private $library_trade_model;

    private function load_main_models(){
        $this->library_config_model = model(Config_Lib_Model::class);
        $this->library_trade_model = model(Lib_Trade_Model::class);
    }

    public function index(){
        show_comming_soon();
        $uri = uri()->uri_to_assoc(4);
        checkURI($uri, array());

        $this->load_main_models();

        $libraries_configs = array_column($this->library_config_model->get_lib_configs(), null, 'id_lib');
        if (!isset($libraries_configs[$this->id_config])) {
            show_404();
        }

        $trade_library = $libraries_configs[$this->id_config];

        $this->breadcrumbs[] = array(
            'link' => __SITE_URL . $trade_library['link_public'],
            'title'=> $trade_library['lib_title']
        );

        $list_of_countries = model(Country_Model::class)->get_countries();

        $data = array(
            'countries_by_continents'   => arrayByKey($list_of_countries, 'id_continent', true),
            'sidebar_right_content'     => 'new/library_settings/sidebar_view',
            'header_out_content'        => 'new/library_settings/library_trade/header_view',
            'footer_out_content'        => 'new/about/bottom_need_help_view',
            'library_head_title'        => $trade_library['lib_title'],
            'configs_library'           => $libraries_configs,
            'list_countries'            => $list_of_countries,
            'library_search'            => $trade_library['link_public_search'],
            'main_content'              => 'new/library_settings/library_trade/index_view',
            'library_page'              => $trade_library['link_public'],
            'library_name'              => 'library_trade',
            'continents'                => model(Country_Model::class)->get_continents(),
            'breadcrumbs'               => array(array(
                'title' => $trade_library['lib_title'],
                'link'  => __SITE_URL . $trade_library['link_public'],
            )),
        );

        views()->assign($data);
        views()->display('new/index_template_view');
    }

    function search() {
        show_comming_soon();

        $uri = uri()->uri_to_assoc(4);

        checkURI($uri, array('country', 'page'));
        checkIsValidPage($uri['page']);

        $this->load_main_models();

        $libraries_configs = array_column($this->library_config_model->get_lib_configs(), null, 'id_lib');
        if (!isset($libraries_configs[$this->id_config])) {
            show_404();
        }

        $trade_library = $libraries_configs[$this->id_config];
        $url_to_library_trade_main_page = get_dynamic_url($trade_library['link_public']);

        if (empty($uri['country']) && empty($_GET['keywords'])) {
            headerRedirect($url_to_library_trade_main_page, 301);
        }

        $this->breadcrumbs[] = array(
            'link' => $url_to_library_trade_main_page,
            'title'=> $trade_library['lib_title']
        );

        $links_map = array(
            'country' => array(
                'type' => 'uri',
                'deny' => array('country', 'page')
            ),
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('keywords', 'page')
            ),
        );

        $links_tpl = uri()->make_templates($links_map, $uri);
        $links_tpl_without = uri()->make_templates($links_map, $uri, true);

        $meta_params = array();
        $page = (int) ($uri['page'] ?? 1);
        $per_page = (int) config('library_trade_per_page', 10);
        $results_limit = (int) config('env.ENTITIES_RESULT_LIMIT', 10000);

        $trade_performance_params = array(
            'sort_by'   => array('industry-asc'),
            'start'     => ($page - 1) * $per_page,
            'per_p'     => $per_page,
        );

        if (isset($uri['country'])) {
            $id_country = id_from_link($uri['country']);

            $country = model(Country_Model::class)->get_country($id_country);
            if (empty($country) || strForURL($country['country'] . ' ' . $id_country) != $uri['country']) {
                show_404();
            }

            $this->breadcrumbs[] = array(
                'link' => get_dynamic_url($trade_library['link_public_search'] . '/country/' . $uri['country']),
                'title'=> 'In ' . $country['country']
            );

            $trade_performance_params['country_id'] = $id_country;
            $meta_params['[COUNTRY]'] = $country['country'];
        }

        $keywords = '';
        $isset_invalid_keywords_search = false;
		if (!empty($_GET['keywords'])) {
            $keywords = $_GET['keywords'];
            $trade_performance_params['keywords'] = cut_str($keywords);
            $meta_params['[KEYWORDS]'] = cleanOutput($keywords);

            if (mb_strlen($trade_performance_params['keywords']) < 3) {
                $isset_invalid_keywords_search = true;
            }
        }

        if ($page > 1) {
            $meta_params['[PAGE]'] = $uri['page'];
        }

        $data = array(
            'link_to_reset_all_filters' => get_dynamic_url($trade_library['link_public']),
            'link_to_reset_keywords'    => get_dynamic_url(empty($uri['country']) ? $trade_library['link_public'] : $trade_library['link_public_search'] . '/' . $links_tpl_without['keywords']),
            'link_to_reset_country'     => get_dynamic_url(empty($keywords) ? $trade_library['link_public'] : $trade_library['link_public_search'] . '/' . $links_tpl_without['country']),
            'sidebar_right_content'     => 'new/library_settings/sidebar_view',
            'footer_out_content'        => 'new/about/bottom_need_help_view',
            'header_out_content'        => 'new/library_settings/library_trade/header_view',
            'library_head_title'        => $trade_library['lib_title'],
            'country_selected'          => $uri['country'] ?? null,
            'configs_library'           => $libraries_configs,
            'list_countries'            => model(Country_Model::class)->get_countries(),
            'library_search'            => $trade_library['link_public_search'],
            'main_content'              => 'new/library_settings/library_trade/detail_by_country_view',
            'library_page'              => $trade_library['link_public'],
            'country_name'              => $country['country'] ?? null,
            'library_name'              => 'library_trade',
            'breadcrumbs'               => $this->breadcrumbs,
            'meta_params'               => $meta_params,
            'continents'                => model(Country_Model::class)->get_continents(),
            'keywords'                  => $keywords,
            'trades'                    => array(),
        );

        if ($page * $per_page > $results_limit) {
            $this->view->assign($data);
            $this->view->display('new/index_template_view');

            return;
        }

        $count_trades = $isset_invalid_keywords_search ? 0 : $this->library_trade_model->get_trade_count($trade_performance_params);

        $paginator_config = array(
            'replace_url'   => true,
            'total_rows'    => $count_trades,
            'first_url'     => rtrim($trade_library['link_public_search'] . '/' . $links_tpl_without['page'], '/'),
            'base_url'      => $trade_library['link_public_search'] . '/' . $links_tpl['page'],
            'per_page'      => $per_page,
            'suffix'		=> empty($keywords) ? null : '?' . http_build_query(array('keywords' => $keywords)),
        );

        library('pagination')->initialize($paginator_config);

        $data['pagination'] = library('pagination')->create_links();
        $data['per_page'] = $per_page;
        $data['trades'] = empty($count_trades) ? array() : $this->library_trade_model->get_all_trade($trade_performance_params);
        $data['count'] = $count_trades;
        $data['page'] = $page;

        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function administration(){
        checkAdmin('manage_content');

        $data['filter_country'] = true;
        $data['filter_visible'] = true;

        $this->view->assign($data);
        $this->view->assign('title', 'Configs Trade Performance');
        $this->view->display('admin/header_view');
        $this->view->display('admin/library_settings/trade_performance/trade_performance_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_trade_performance(){
        checkAdminAjaxDT('manage_content');

        $this->load_main_models();

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id'            => 'id_trade',
                'dt_country'       => 'country',
                'dt_industry'      => 'industry',
                'dt_export'        => 'export',
                'dt_trade'         => 'trade',
                'dt_total_export'  => 'total_export',
                'dt_total_import'  => 'total_import',
                'dt_world_export'  => 'world_export',
                'dt_world_import'  => 'world_import',
                'dt_growth_export' => 'growth_export',
                'dt_growth_import' => 'growth_import',
                'dt_net_trade'     => 'net_trade',
                'dt_is_visible'    => 'is_visible'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'visible_record', 'key' => 'set_visible', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_trade-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        if (isset($_POST['set_country'])){

            if($_POST['set_country'] == 0)
                $params['country_id'] = 0;
            else
                $params['exist_country'] = true;
        }

        $records = $this->library_trade_model->get_all_trade($params);
        $records_count = $this->library_trade_model->get_trade_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_count,
            "iTotalDisplayRecords" => $records_count,
            'aaData' => array()
        );

        if(empty($records))
            jsonResponse('', 'success', $output);

        foreach($records as $record){
            $output['aaData'][] = array(
                'dt_id'           => $record['id_trade'] . '<div class="ml-5 w-15 pull-right"><input class="checked-element" type="checkbox" value="' . $record['id_trade'] . '">',
                'dt_country'      => $record['id_country'] ? $record['country'] : '---',
                'dt_industry'     => $record['industry'],
                'dt_export'       => $record['export'],
                'dt_import'       => $record['import'],
                'dt_trade'        => $record['trade'],
                'dt_total_export' => $record['total_export'],
                'dt_total_import' => $record['total_import'],
                'dt_world_export' => $record['world_export'],
                'dt_world_import' => $record['world_import'],
                'dt_growth_export'=> $record['growth_export'],
                'dt_growth_import'=> $record['growth_import'],
                'dt_net_trade'    => $record['net_trade'],
                'dt_type_add'     => $record['type_record'] == 1 ? 'Manual' : 'File',
                'dt_is_visible'   => $record['is_visible'] ? 'Yes' : 'Not',
                'dt_actions'      => '<a class="ep-icon ep-' . ($record['is_visible'] ? 'icon_invisible' : 'icon_visible') . ' confirm-dialog" data-callback="visible_status" data-record="' . $record['id_trade'] . '" data-status="' . $record['is_visible'] . '" title="Change status record" data-message="Are you sure you want to change status of this record?" href="#" ></a>
                                      <a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="library_trade/popup_forms/edit_record/'. $record['id_trade'] . '" data-title="Edit Trade Performance" title="Edit this Trade Performance"></a>
                                      <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_acc_record" data-record="' . $record['id_trade'] . '" title="Remove this Trade Performance" data-message="Are you sure you want to delete this Trade Performance?" href="#" ></a>',
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkAdminAjaxModal('manage_content');

        $this->load_main_models();

        $form = $this->uri->segment(3);

        switch ($form) {
            case 'add_record':
                $data = array();

                $library = $this->library_config_model->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if(is_file($file_config))
                        require ($file_config);

                    // Functional relation
                    if (!empty($relation_config)){
                        foreach($relation_config['db_table'] as $char => $params){
                            if (!array_key_exists($params['return_key'], $temp)){
                                $temp[$params['return_key']] = $params['from_table'];

                                $condition = array(
                                    'db_select' => $params['from_column'],
                                    'db_table'  => $params['from_table'],
                                );

                                $temp[$params['return_key']] = $this->library_config_model->list_record_by_relation($condition);
                            }

                            if (!empty($relation_config['config_row'][$char])){
                                $field_key = $relation_config['config_row'][$char];
                                if (is_array($relation_config['config_row'][$char])){
                                    $field_key = key($relation_config['config_row'][$char]);
                                    $field_key = $relation_config['config_row'][$char][$field_key];
                                }
                            }

                            foreach($temp[$params['return_key']] as $i => $value){
                                $element[$i]['id']   = $value[$relation_config['insert_column'][$field_key]];
                                $element[$i]['value']= $value[$params['return_key']];
                            }

                            $list_element[$field_key] = $element;
                        }

                        $data['relation_config']    = $relation_config;
                        $data['records_by_relation']= $list_element;
                    }

                    $data['config'] = $config;
                    $data['current_contoller'] = 'library_trade';
                }
                $this->view->display('admin/library_settings/common_form/add_by_config_form', $data);
            break;

            case 'add_record_excel':
                $library = $this->library_config_model->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if(is_file($file_config))
                        require ($file_config);

                    $data['format_read'] = implode(', ', $allowed_extension);
                }

                $data['current_contoller'] = 'library_trade';
                $this->view->display('admin/library_settings/common_form/upload_file_form', $data);
            break;

            case 'edit_record':
                $id_record = (int)$this->uri->segment(4);

                $library = $this->library_config_model->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if(is_file($file_config))
                        require ($file_config);

                    // Functional relation
                    if (!empty($relation_config)){
                        foreach($relation_config['db_table'] as $char => $params){
                            if (!array_key_exists($params['return_key'], $temp)){
                                $temp[$params['return_key']] = $params['from_table'];

                                $condition = array(
                                    'db_select' => $params['from_column'],
                                    'db_table'  => $params['from_table'],
                                );

                                $temp[$params['return_key']] = $this->library_config_model->list_record_by_relation($condition);
                            }

                            if (!empty($relation_config['config_row'][$char])){
                                $field_key = $relation_config['config_row'][$char];
                                if (is_array($relation_config['config_row'][$char])){
                                    $field_key = key($relation_config['config_row'][$char]);
                                    $field_key = $relation_config['config_row'][$char][$field_key];
                                }
                            }

                            foreach($temp[$params['return_key']] as $i => $value){
                                $element[$i]['id']   = $value[$relation_config['insert_column'][$field_key]];
                                $element[$i]['value']= $value[$params['return_key']];
                            }

                            $list_element[$field_key] = $element;
                        }

                        $data['relation_config']    = $relation_config;
                        $data['records_by_relation']= $list_element;
                    }

                    $data['config'] = $config;
                    $data['current_contoller'] = 'library_trade';
                }

                $data['record'] = $this->library_trade_model->get_trade(array('id_record' => $id_record));

                if(empty($data['record']))
                    messageInModal('Error: This library setting does not exist.');

                $data['id_record'] = $data['record']['id_trade'];
                $data['id_select'] = $data['record']['id_country'];

                $this->view->display('admin/library_settings/common_form/edit_by_config_form', $data);
            break;

            case 'update_by_country':
                $this->load->model('Country_Model', 'country');
                $data['empty_country_records'] = $this->library_trade_model->get_countries_trade(array('is_country' => false));
                $data['countres'] = $this->country->get_countries();
                $this->view->display('admin/library_settings/trade_performance/forms/update_country', $data);
            break;
        }
    }

    public function ajax_library_operation(){
        checkAdminAjax('manage_content');

        $this->load_main_models();

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'change_status':
                $id_record = intVal($_POST['id_record']);
                $status_record = intVal($_POST['status']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

                $exist_record = $this->library_trade_model->check_trade(array('id_record' => $id_record, 'visible_record' => $status_record));

                if (!$exist_record)
                    jsonResponse('Info: The Trade Performance with this id and status doesn\'t found', 'info');

                if (!$this->library_trade_model->update_trade($id_record, array('is_visible' => !$status_record))){
                    jsonResponse('Info: Error updating the status of this entry', 'info');
                }

                jsonResponse('The status of the record was updated with success', 'success');
                break;

            case 'save_record_manual':
                $validator_rules = array(
                //    array(
                //        'field' => 'country',
                //        'label' => 'Country',
                //        'rules' => array('required' => '', 'max_len[100]' => '')
                //    ),
                    array(
                        'field' => 'id_country',
                        'label' => 'Id country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'industry',
                        'label' => 'Industry',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'export',
                        'label' => 'Export',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'import',
                        'label' => 'Import',
                        'rules' => array('max_len[20]' => '')//'valid_email' => '',
                    ),
                    array(
                        'field' => 'trade',
                        'label' => 'trade',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'total_export',
                        'label' => 'Exports as a share of total exports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'total_import',
                        'label' => 'Imports as a share of total imports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'world_export',
                        'label' => 'Exports as a share of world exports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'world_import',
                        'label' => 'Imports as a share of world imports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'growth_export',
                        'label' => 'Growth of exports in value',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'growth_import',
                        'label' => 'Growth of imports in value',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'net_trade',
                        'label' => 'Net Trade',
                        'rules' => array('max_len[20]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

            //    $exist_record = $this->library_trade_model->check_icc_country(array('email' => cleanInput($_POST['email'], true)));

            //    if ($exist_record)
            //        jsonResponse('Info: The ICC Country with this email already exists. Please edit the existing.', 'info');

                $insert = array(
                    'id_country'    => cleanInput($_POST['id_country']),
                    'country'       => cleanInput($_POST['item_id_country']),
                    'industry'      => cleanInput($_POST['industry']),
                    'export'        => cleanInput($_POST['export']),
                    'import'        => cleanInput($_POST['import']),
                    'trade'         => cleanInput($_POST['trade']),
                    'total_export'  => cleanInput($_POST['total_export']),
                    'total_import'  => cleanInput($_POST['total_import']),
                    'world_export'  => cleanInput($_POST['world_export']),
                    'world_import'  => cleanInput($_POST['world_import']),
                    'growth_export' => cleanInput($_POST['growth_export']),
                    'growth_import' => cleanInput($_POST['growth_import']),
                    'net_trade'     => cleanInput($_POST['net_trade']),
                    'type_record'   => 1
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $insert[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                $id_record = $this->library_trade_model->set_trade($insert);

                if($id_record)
                    jsonResponse('The library Trade Performance have been successfully saved.', 'success');
                else
                    jsonResponse('Error: You cannot add library Trade Performance now. Please try again later.');
            break;

            case 'save_from_file':
                if (empty($_FILES['file_excell'])) {
                    jsonResponse('Error: Please select file to upload.');
                }

                try {
                    /** @var TinyMVC_Library_Phpexcel $phpexcel */
                    $phpexcel = library(TinyMVC_Library_Phpexcel::class);
                    $phpexcel->set_config($this->dir_config, $this->id_config);
                    $result = $phpexcel->file_create(request()->files->get('file_excell'));
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                } catch (\Throwable $exception) {
                    jsonResponse($exception->getMessage(), 'error', withDebugInformation(
                        array(), array('exception' => throwableToArray($exception))
                    ));
                }

                jsonResponse('File save with success!', 'success', array('file_excel' => $result, 'file_type' => pathinfo($result, PATHINFO_EXTENSION)));
            break;

            case 'file_parse':
                if (!isset($_POST['file_excel_name'])){
                    jsonResponse('File doesn\'t selected!');
                }

                $file_name = cleanInput($_POST['file_excel_name']);
                $delete_record = filter_var($_POST['delete_record'], FILTER_VALIDATE_BOOLEAN, array('options' => array('default' => false)));

                try {
                    /** @var TinyMVC_Library_Phpexcel $phpexcel */
                    $phpexcel = library(TinyMVC_Library_Phpexcel::class);
                    $phpexcel->set_config($this->dir_config, $this->id_config, $file_name);
                    $result = $phpexcel->excel_parse();
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                } catch (\Throwable $exception) {
                    jsonResponse($exception->getMessage(), 'error', withDebugInformation(
                        array(), array('exception' => throwableToArray($exception))
                    ));
                }

                if($delete_record && $this->library_trade_model->get_trade_count(array('type_record' => 0))){
                    if(!$this->library_trade_model->delete_trade(array('type_record' => 0)))
                        jsonResponse('Error: You cannot remove Trade Performance now.');
                }

                if(!$this->library_trade_model->set_rows_trade($result))
                    jsonResponse('Error : New records cannot be insert!');

                jsonResponse('All data save success!', 'success', array('src' => $file_name));
            break;

            case 'delete_uploaded':
                $file_name = cleanInput($_POST['file']);

                $file_path = $this->dir_config . $this->id_config . '/' . $file_name;

                if(!is_file($file_path))
                    jsonResponse('File not found!', 'error');

                if(!unlink($file_path))
                    jsonResponse('The file cannot be deleted!', 'error');


                jsonResponse('The file was deleted successfully!', 'success');
            break;

            case 'edit_manual_record':
                $validator_rules = array(
                //    array(
                //        'field' => 'country',
                //        'label' => 'Country',
                //        'rules' => array('required' => '', 'max_len[100]' => '')
                //    ),
                    array(
                        'field' => 'id_country',
                        'label' => 'Id country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'industry',
                        'label' => 'Industry',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'export',
                        'label' => 'Export',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'import',
                        'label' => 'Import',
                        'rules' => array('max_len[20]' => '')//'valid_email' => '',
                    ),
                    array(
                        'field' => 'trade',
                        'label' => 'trade',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'total_export',
                        'label' => 'Exports as a share of total exports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'total_import',
                        'label' => 'Imports as a share of total imports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'world_export',
                        'label' => 'Exports as a share of world exports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'world_import',
                        'label' => 'Imports as a share of world imports',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'growth_export',
                        'label' => 'Growth of exports in value',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'growth_import',
                        'label' => 'Growth of imports in value',
                        'rules' => array('max_len[20]' => '')
                    ),
                    array(
                        'field' => 'net_trade',
                        'label' => 'Net Trade',
                        'rules' => array('max_len[20]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_record = intVal($_POST['id_record']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

            //    $exist_record = $this->icc->check_lawyer(array('email' => cleanInput($_POST['email'], true), 'not_id_record' => $id_record));

            //    if ($exist_record)
            //        jsonResponse('Info: The ICC Country with this email already exists. Please edit the existing.', 'info');

                $update = array(
                    'id_country'    => cleanInput($_POST['id_country']),
                    'country'       => cleanInput($_POST['item_id_country']),
                    'industry'      => cleanInput($_POST['industry']),
                    'export'        => cleanInput($_POST['export']),
                    'import'        => cleanInput($_POST['import']),
                    'trade'         => cleanInput($_POST['trade']),
                    'total_export'  => cleanInput($_POST['total_export']),
                    'total_import'  => cleanInput($_POST['total_import']),
                    'world_export'  => cleanInput($_POST['world_export']),
                    'world_import'  => cleanInput($_POST['world_import']),
                    'growth_export' => cleanInput($_POST['growth_export']),
                    'growth_import' => cleanInput($_POST['growth_import']),
                    'net_trade'     => cleanInput($_POST['net_trade']),
                    'type_record'   => intVal($_POST['type_record']),
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $update[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                if ($this->library_trade_model->update_trade($id_record, $update))
                    jsonResponse('The Trade Performance have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot edit Trade Performance now. Please try again later.');
            break;

            case 'remove_record':
                $id_record = intVal($_POST['record']);

                if(!$this->library_trade_model->check_trade(array('id_record' => $id_record)))
                    jsonResponse('This Trade Performance doesn\'t exist.');

                if($this->library_trade_model->delete_trade(array('id_record' => $id_record)))
                    jsonResponse('The Trade Performance has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this Trade Performance now. Please try again later.');
            break;

            case 'download_last_file':
                $this->load->model('Config_Lib_Model', 'lib_config');

                $library = $this->library_config_model->get_lib_config($this->id_config);

                if(empty($library['file_name']))
                    jsonResponse('Error: File config doesn\'t found!');

                $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                require ($file_config);

                $path = $this->dir_config . $this->id_config . '/';

                if(!empty($allowed_extension)){
                    foreach($allowed_extension as $format_file){
                        $file_path = $path . $file_xls_name . '.' . $format_file;
                        if(is_file($file_path))
                            $download_file_path = $file_path;
                    }
                }else
                    jsonResponse('Error: File extension doesn\'t found!');

                if(empty($download_file_path))
                    jsonResponse('Error: File doesn\'t found!');

                jsonResponse('', 'success', array('src'=> __SITE_URL . $download_file_path));
            break;

            case 'remove_records':
                $list_elements = '';
                if (!empty($_POST['elements'])){
                    $list_elements = implode(',', $_POST['elements']);
                }

                if($this->library_trade_model->delete_trade(array('id_records' => $list_elements)))
                    jsonResponse('The list of records has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this list of records right now. Please try again later.');
            break;

            case 'change_country_trade':
                $validator_rules = array(
                    array(
                        'field' => 'change_country',
                        'label' => 'Id country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'record_country',
                        'label' => 'Country default',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_country_select = intVal($_POST['change_country']);
                if (empty($id_country_select)){
                    jsonResponse('Info: Id of record doesn\'t set.');
                }
                $this->load->model('Country_Model', 'country');
                $country_select = $this->country->get_country($id_country_select);
                if (empty($country_select)){
                    jsonResponse('Info: Country with this id doesn\'t find.');
                }

                $contry_name_change = $_POST['record_country'];

                if (!$this->library_trade_model->check_trade(array('country_name' => $contry_name_change))){
                    jsonResponse('Info: Country you want to replace is not found.');
                }

                $params = array(
                    'source_data'     => $country_select,
                    'condition'       => array('column' => 'country', 'value' => $contry_name_change),
                    'replace_columns' => array('from' => 'id, country', 'to' => 'id_country, country'),
                );

                if (!$this->library_trade_model->update_country_trade($params)){
                    jsonResponse('Info: Error to chengede data.');
                }

                jsonResponse('All entries were successfully updated', 'success');
            break;
        }
    }
}
?>

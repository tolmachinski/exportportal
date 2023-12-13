<?php

use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Consulates_Controller extends TinyMVC_Controller {
    private $dir_config = 'library_store/';
    private $id_config  = 8;
    private $breadcrumbs= array();

    private function load_main(){
        $this->load->model('Config_Lib_Model', 'lib_config');
        $this->load->model('Lib_Consulates_Model', 'consulate');
    }

    public function index(){
        show_404();
    }

    public function all(){
        $this->load_main();
        $this->load->model('Country_Model', 'country');
        $current_lib = $this->lib_config->get_lib_config($this->id_config);
        $data['library_head_title'] = $current_lib['lib_title'];

        $link_page = array(
            'main' => __SITE_URL . $current_lib['link_public']
        );

        $this->breadcrumbs[] = array(
            'link' => $link_page['main'],
            'title'=> $current_lib['lib_title']
        );

        $page_link = $link_page;
        $data['page_link'] = implode('', $page_link);

        $countries = $this->consulate->get_countries_consulates(array('is_country' => true, 'visible_record' => 1));
        foreach($countries as $country_row){
            $char = strtoupper(substr($country_row['country_main'], 0, 1));
            $alphabetic[$char][$country_row['country_main']] = $country_row;
        }

        ksort($alphabetic);

        $data['configs_library']= $this->lib_config->get_lib_configs();

        $data['countries_by_char'] = $alphabetic;
        $data['library_page'] = $current_lib['link_public'];
        $data['library_detail'] = $current_lib['link_public_detail'];
        $data['library_search'] = $current_lib['link_public_search'];
        $data['library_name'] = 'library_consulates';
        $data['list_countries'] = $this->country->get_countries();
        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['header_out_content'] = 'new/library_settings/library_consulates/header_view';
        $data['main_content'] = 'new/library_settings/library_consulates/index_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['footer_out_content'] = 'new/about/bottom_need_help_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    function detail() {
        $uri = $this->uri->uri_to_assoc(4);
        if ( ! isset($uri['country'])) {
            show_404();
        }

        checkURI($uri, array('country', 'page'));

        $links_map = array(
            'keywords' => array(
                'type' => 'get',
                'deny' => array('keywords', 'page')
            ),
            'country' => array(
                'type' => 'uri',
                'deny' => array('country', 'page')
            ),
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            ),
        );

        $links_tpl = $this->uri->make_templates($links_map, $uri);
        $links_tpl_without = $this->uri->make_templates($links_map, $uri, true);

        $id_country = id_from_link($uri['country']);
        $country_info = model('country')->get_country($id_country);
        if (empty($country_info)) {
            show_404();
        }

        $meta_params['[COUNTRY]'] = $country_info['country'];

        $current_lib = model('config_lib')->get_lib_config($this->id_config);

        $this->breadcrumbs[] = array(
            'link' => $current_lib['link_public'],
            'title' => 'List of Consulates'
        );

        $this->breadcrumbs[] = array(
            'link' => __SITE_URL . $current_lib['link_public_detail'] . '/country/' . $uri['country'],
            'title'=> $country_info['country']
        );

        $params = array(
            'visible_record'    => 1,
            'main_country'      => $id_country,
            'sort_by'           => array('country_consulate-asc'),
            'per_p'             => 10,
            'start'             => 0,
        );

        if (!empty($_GET['keywords'])){
            $keywords = cleanOutput(cleanInput(cut_str($_GET['keywords'])));
            $meta_params['[KEYWORDS]'] = $params['keywords'] = $keywords;
            $data['get_params']['keywords'] = 'keywords' . '=' . $keywords;
        }

        if (!empty($uri['page']) && $uri['page'] >= 1){
           $data['page'] = $params['start'] = abs(intVal($uri['page'])) * $params['per_p'] - $params['per_p'];
           $meta_params['[PAGE]'] = $uri['page'];
        }

        $data = array(
            'country_selected'  => strForURL($country_info['country'] . ' ' . $id_country),
            'configs_library'   => model('config_lib')->get_lib_configs(),
            'library_search'    => $current_lib['link_public_search'],
            'list_countries'    => model('country')->get_countries(),
            'library_page'      => $current_lib['link_public'],
            'library_name'      => 'library_consulates',
            'breadcrumbs'       => $this->breadcrumbs,
            'consulates'        => model('lib_consulates')->get_all_consulates($params),
            'page_link'         => __SITE_URL . uri()->segment(1) . '/' . uri()->segment(2) . '/' . uri()->segment(3) . '/' . uri()->segment(4),
            'keywords'          => $keywords,
            'count'             => model('lib_consulates')->get_consulates_count($params),
            'page'              => 0,
            'meta_params'       => $meta_params,
            'country_name'      => $country_info['country'],
            'link_to_reset_all_filters' => get_dynamic_url($current_lib['link_public']),
            'link_to_reset_keywords'    => get_dynamic_url($current_lib['link_public_detail'] . '/country/' . $uri['country']),
            'link_to_reset_country'     => get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['country']),
        );

        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl['page'], $current_lib['link_public_detail']),
            'first_url'     => get_dynamic_url($links_tpl_without['page'], $current_lib['link_public_detail']),
            'per_page'      => $params['per_p'],
            'total_rows'    => $data['count'],
            'replace_url'   => true
        );

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();

        $data['header_out_content'] = 'new/library_settings/library_consulates/header_view';
        $data['main_content'] = 'new/library_settings/library_consulates/detail_by_country_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['footer_out_content'] = 'new/about/bottom_need_help_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function get_email_by_id(){
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $validator_rules = array(
            array(
                'field' => 'item_id',
                'label' => 'Id record',
                'rules' => array('required' => '', 'integer' => '')
            )
        );

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $this->load_main();

        $id_record = intVal($_POST['item_id']);

        $record = array();

        $record = $this->consulate->get_consulate(array('id_record' => $id_record));

        if(empty($record['email'])) {
            jsonResponse("Error: Email for this consulate doesn't found!");
        }

        jsonResponse('', 'success', array('email' => $record['email']));
    }

    public function administration(){
        checkAdmin('manage_content');

        $data['filter_country'] = true;
        $data['filter_visible'] = true;

        $this->view->assign($data);
        $this->view->assign('title', 'Configs Library Consulates');
        $this->view->display('admin/header_view');
        $this->view->display('library_settings/admin/consulates/consulates_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_consulates(){
        checkAdminAjaxDT('manage_content');

        $this->load->model('Lib_Consulates_Model', 'consulate');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id'           => 'id_consulate',
                'dt_country_main' => 'country_main',
                'dt_country_cons' => 'country_consulate',
                'dt_mission_name' => 'mission_name',
                'dt_phone'        => 'phone',
                'dt_email'        => 'email',
                'dt_website'      => 'url_site',
                'dt_is_visible'   => 'is_visible'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'visible_record', 'key' => 'set_visible', 'type' => 'cleanInput'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_consulate-asc"] : $sorting['sort_by'];

        $params = array_merge($filters, $sorting);

        if (isset($_POST['set_country'])){

            if ($_POST['set_country'] == 0){
                $params['not_country'] = true;
            } else {
                $params['main_country_exist'] = true;
                $params['consulate_countr_exist'] = true;
            }
        }

        $records = $this->consulate->get_all_consulates($params);
        $records_count = $this->consulate->get_consulates_count($params);

        $output  = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_count,
            "iTotalDisplayRecords" => $records_count,
            'aaData' => array()
        );

        if(empty($records)) {
            jsonResponse('', 'success', $output);
        }

        foreach($records as $record){
            $output['aaData'][] = array(
                'dt_id'               =>  $record['id_consulate'] . '<div class="ml-5 w-15 pull-right"><input class="checked-element" type="checkbox" value="' . $record['id_consulate'] . '">',
                'dt_country_main'     =>  $record['id_country'] ? $record['country_main'] : '---',
                'dt_country_cons'     =>  $record['id_country_cons'] ? $record['country_consulate'] : '---',
                'dt_mission_name'     =>  $record['mission_name'],
                'dt_phone'            =>  $record['phone'],
                'dt_email'            =>  $record['email'],
                'dt_website'          =>  !empty($record['url_site']) ? '<button class="btn btn-primary btn-xs link-clipboard" data-clipboard-text="'.$record['url_site'].'"><i class="ep-icon ep-icon_link fs-12_i m-0"></i> site link</button>' : '---',
                'dt_type_add'         =>  $record['type_record'] == 1 ? 'Manual' : 'File',
                'dt_is_visible'       =>  $record['is_visible'] ? 'Yes' : 'Not',
                'dt_actions'          =>  '<a class="ep-icon ep-' . ($record['is_visible'] ? 'icon_invisible' : 'icon_visible') . ' confirm-dialog" data-callback="visible_status"
                                            data-record="'.$record['id_consulate'].'" data-status="' . $record['is_visible'] . '" title="Change status record" data-message="Are you sure you want to change status of this record?" href="#" ></a>
                                            <a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="' . __SITE_URL . 'library_consulates/popup_forms/edit_record/'. $record['id_consulate'] . '" data-title="Edit Consulates" title="Edit this Consulates"></a>
                                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_acc_record" data-record="' . $record['id_consulate'] . '" title="Remove this Consulates" data-message="Are you sure you want to delete this Consulates?" href="#" ></a>',
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkAdminAjaxModal('manage_content');

        $this->load_main();

        $form = $this->uri->segment(3);

        switch ($form) {
            case 'add_record':
                $data = array();

                $library = $this->lib_config->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if (is_file($file_config)){
                        require ($file_config);
                    }

                    // Functional relation
                    if (!empty($relation_config)){
                        foreach($relation_config['db_table'] as $char => $params){
                            if (!array_key_exists($params['return_key'], $temp)){
                                $temp[$params['return_key']] = $params['from_table'];

                                $condition = array(
                                    'db_select' => $params['from_column'],
                                    'db_table'  => $params['from_table'],
                                );

                                $temp[$params['return_key']] = $this->lib_config->list_record_by_relation($condition);
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
                    $data['current_contoller'] = 'library_consulates';
                }
                $this->view->display('admin/library_settings/common_form/add_by_config_form', $data);
            break;
            case 'add_record_excel':
                $library = $this->lib_config->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if(is_file($file_config)) {
                        require ($file_config);
                    }

                    $data['format_read'] = implode(', ', $allowed_extension);
                }

                $data['current_contoller'] = 'library_consulates';
                $this->view->display('admin/library_settings/common_form/upload_file_form', $data);
            break;
            case 'edit_record':
                $id_record = (int)$this->uri->segment(4);

                $library = $this->lib_config->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if (is_file($file_config)){
                        require ($file_config);
                    }

                    if (!empty($relation_config)){
                        foreach($relation_config['db_table'] as $char => $params){
                            if (!array_key_exists($params['return_key'], $temp)){
                                $temp[$params['return_key']] = $params['from_table'];

                                $condition = array(
                                    'db_select' => $params['from_column'],
                                    'db_table'  => $params['from_table'],
                                );

                                $temp[$params['return_key']] = $this->lib_config->list_record_by_relation($condition);
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
                    $data['current_contoller'] = 'library_consulates';
                }

                $data['record'] = $this->consulate->get_consulate(array('id_record' => $id_record));

                if(empty($data['record'])) {
                    messageInModal('Error: This library setting does not exist.');
                }

                $data['id_record'] = $data['record']['id_consulate'];

                $this->view->display('admin/library_settings/common_form/edit_by_config_form', $data);
            break;

            case 'update_by_country':
                $this->load->model('Country_Model', 'country');
                $main_country = $this->consulate->get_countries_consulates(array('is_country' => false));
                $consulates = $this->consulate->get_countries_consulates(array('is_consulates' => false));

                $main_country = array_merge($main_country, $consulates);
                $data['empty_country_records'] = $main_country;
                $data['countres'] = $this->country->get_countries();

                $this->view->display('library_settings/admin/consulates/forms/update_country', $data);
            break;
        }
    }

    public function ajax_library_operation(){
        checkAdminAjax('manage_content');

        $this->load->model('Lib_Consulates_Model', 'consulate');

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'change_status':
                $id_record = intVal($_POST['id_record']);
                $status_record = intVal($_POST['status']);

                if(empty($id_record)) {
                    jsonResponse('Info: Id record doesn\'t set.');
                }

                $exist_record = $this->consulate->check_consulates(array('id_record' => $id_record, 'visible_record' => $status_record));

                if (!$exist_record) {
                    jsonResponse('Info: The consulate with this id and status doesn\'t found', 'info');
                }

                if (!$this->consulate->update_consulate($id_record, array('is_visible' => !$status_record))){
                    jsonResponse('Info: Error updating the status of this entry', 'info');
                }

                jsonResponse('The status of the record was updated with success', 'success');
                break;

            case 'save_record_manual':
                $validator_rules = array(
                    array(
                        'field' => 'id_country',
                        'label' => 'Id country main',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'id_country_cons',
                        'label' => 'Id country consulate',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country_cons',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'mission_name',
                        'label' => 'Mission name',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'head',
                        'label' => 'Head',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('max_len[150]' => '')//'valid_email' => '',
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'person_name',
                        'label' => 'Contact person',
                        'rules' => array('max_len[500]' => '')
                    ),
                    array(
                        'field' => 'person_email',
                        'label' => 'Contact person email',
                        'rules' => array('max_len[150]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $exist_record = $this->consulate->check_consulates(array('email' => cleanInput($_POST['email'], true)));

                if ($exist_record) {
                    jsonResponse('Info: The Consulates with this email already exists. Please edit the existing.', 'info');
                }

                $insert = array(
                    'id_country'        => intVal($_POST['id_country']),
                    'country_main'      => cleanInput($_POST['item_id_country']),
                    'id_country_cons'   => intVal($_POST['id_country_cons']),
                    'country_consulate' => cleanInput($_POST['item_id_country_cons']),
                    'mission_name'      => cleanInput($_POST['mission_name']),
                    'head'              => cleanInput($_POST['head']),
                    'email'             => cleanInput($_POST['email'], true),
                    'address'           => cleanInput($_POST['address']),
                    'phone'             => $_POST['phone'],
                    'url_site'          => $_POST['url_site'],
                    'person_name'       => $_POST['person_name'],
                    'person_email'      => $_POST['person_email'],
                    'type_record'       => 1
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name'])) {
                    $insert[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);
                }

                $id_record = $this->consulate->set_consulate($insert);

                if($id_record) {
                    jsonResponse('The library Consulates have been successfully saved.', 'success');
                } else {
                    jsonResponse('Error: You cannot add library Consulates now. Please try again later.');
                }
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

                if($delete_record && $this->consulate->get_consulates_count(array('type_record' => 0))){
                    if(!$this->consulate->delete_consulates(array('type_record' => 0))) {
                        jsonResponse('Error: You cannot remove Consulates now.');
                    }
                }

                if(!$this->consulate->set_rows_consulates($result)) {
                    jsonResponse('Error : New records cannot be insert!');
                }

                jsonResponse('All data save success!', 'success', array('src' => $file_name));
            break;
            case 'delete_uploaded':
                $file_name = cleanInput($_POST['file']);

                $file_path = $this->dir_config . $this->id_config . '/' . $file_name;

                if(!is_file($file_path)) {
                    jsonResponse('File not found!', 'error');
                }

                if(!unlink($file_path)) {
                    jsonResponse('The file cannot be deleted!', 'error');
                }

                jsonResponse('The file was deleted successfully!', 'success');
            break;
            case 'edit_manual_record':
                $validator_rules = array(
                    array(
                        'field' => 'id_country',
                        'label' => 'Id country main',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'id_country_cons',
                        'label' => 'Id country consulate',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'item_id_country_cons',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'mission_name',
                        'label' => 'Mission name',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'head',
                        'label' => 'Head',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('max_len[200]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'person_name',
                        'label' => 'Contact person',
                        'rules' => array('max_len[500]' => '')
                    ),
                    array(
                        'field' => 'person_email',
                        'label' => 'Contact person email',
                        'rules' => array('max_len[150]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_record = intVal($_POST['id_record']);

                if (empty($id_record)){
                    jsonResponse('Info: Id record doesn\'t set.');
                }

                $update = array(
                    'id_country'        => intVal($_POST['id_country']),
                    'country_main'      => cleanInput($_POST['item_id_country']),
                    'id_country_cons'   => intVal($_POST['id_country_cons']),
                    'country_consulate' => cleanInput($_POST['item_id_country_cons']),
                    'mission_name'      => cleanInput($_POST['mission_name']),
                    'head'              => cleanInput($_POST['head']),
                    'email'             => cleanInput($_POST['email'], true),
                    'address'           => cleanInput($_POST['address']),
                    'phone'             => $_POST['phone'],
                    'url_site'          => $_POST['url_site'],
                    'person_name'       => $_POST['person_name'],
                    'person_email'      => $_POST['person_email'],
                    'type_record'       => intVal($_POST['type_record'])
                );

                if (!empty($_POST['relation']) && !empty($_POST['column_name'])){
                    $update[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);
                }

                if ($this->consulate->update_consulate($id_record, $update)){
                    jsonResponse('The Consulates have been successfully changed.', 'success');
                } else {
                    jsonResponse('Error: You cannot edit Consulates now. Please try again later.');
                }
            break;
            case 'remove_record':
                $id_record = intVal($_POST['record']);

                if(!$this->consulate->check_consulates(array('id_record' => $id_record))) {
                    jsonResponse('This Consulates doesn\'t exist.');
                }

                if($this->consulate->delete_consulates(array('id_record' => $id_record))) {
                    jsonResponse('The Consulates has been successfully removed.', 'success');
                } else {
                    jsonResponse('Error: You cannot remove this Consulates now. Please try again later.');
                }
            break;
            case 'download_last_file':
                $this->load->model('Config_Lib_Model', 'lib_config');

                $library = $this->lib_config->get_lib_config($this->id_config);

                if(empty($library['file_name'])) {
                    jsonResponse('Error: File config doesn\'t found!');
                }

                $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                require ($file_config);

                $path = $this->dir_config . $this->id_config . '/';

                if(!empty($allowed_extension)){
                    foreach($allowed_extension as $format_file){
                        $file_path = $path . $file_xls_name . '.' . $format_file;
                        if(is_file($file_path)) {
                            $download_file_path = $file_path;
                        }
                    }
                } else {
                    jsonResponse('Error: File extension doesn\'t found!');
                }

                if(empty($download_file_path)) {
                    jsonResponse('Error: File doesn\'t found!');
                }

                jsonResponse('', 'success', array('src'=> __SITE_URL . $download_file_path));
            break;

            case 'remove_records':
                $list_elements = '';
                if (!empty($_POST['elements'])) {
                    $list_elements = implode(',', $_POST['elements']);
                }

                if($this->consulate->delete_consulates(array('id_records' => $list_elements))) {
                    jsonResponse('The list of records has been successfully removed.', 'success');
                } else {
                    jsonResponse('Error: You cannot remove this list of records right now. Please try again later.');
                }
            break;

            case 'change_country_consulate':
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
                if (!$this->consulate->check_consulates(array('country_present' => $contry_name_change))){
                    jsonResponse('Info: Country you want to replace is not found.');
                }

                $params = array(
                    'source_data'     => $country_select,
                    'condition'       => array('column' => 'country_main, country_consulate', 'value' => $contry_name_change),
                    'replace_columns' => array('from' => 'id, country', 'to' => 'id_country, country_main, id_country_cons, country_consulate'),
                );

                if (!$this->consulate->update_country_consulates($params)){
                    jsonResponse('Info: Error to chengede data.');
                }

                jsonResponse('All entries were successfully updated', 'success');
            break;
        }
    }
}

<?php

use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Inspection_Agency_Controller extends TinyMVC_Controller {
    private $dir_config = 'library_store/';
    private $id_config  = 5;

    private $breadcrumbs = array();

    private function load_main(){
        $this->load->model('Config_Lib_Model', 'lib_config');
        $this->load->model('Lib_Inspection_Agency_Model', 'inspection_agency');
    }

    public function index(){
        show_404();
    }

    public function all(){
        $this->load_main();
        $this->load->model('Country_Model', 'country');

        $current_lib = $this->lib_config->get_lib_config($this->id_config);
        $data['library_head_title'] = $current_lib['lib_title'];

        $uri = $this->uri->uri_to_assoc(4);
        checkURI($uri, array('country', 'page'));

        $links_map = array(
            'country' => array(
                'type' => 'uri',
                'deny' => array('country', 'page')
            ),
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            ),
            'per_p' => array(
                'type' => 'get',
                'deny' => array('page', 'per_p')
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('keywords', 'page')
            ),
            'sort_by' => array(
                'type' => 'get',
                'deny' => array('sort_by', 'page')
            )
        );

        $links_tpl = $this->uri->make_templates($links_map, $uri);
        $links_tpl_without = $this->uri->make_templates($links_map, $uri, true);

        $this->breadcrumbs[] = array(
            'link' => __SITE_URL.$current_lib['link_public'],
            'title'=> $current_lib['lib_title']
        );

        $params = array();

        $params['visible_record'] = 1;

        $data['page'] = $params['start'] = 0;
        $data['per_p'] = $params['per_p']= 50;
        $data['country_selected'] = '';
        $data['alt'] = 'Office workers';

        $data['list_sort_by'] = array(
            'company-asc'   => 'Company name &#9650;',
            'company-desc'  => 'Company name &#9660;',
            'country-asc'   => 'Country &#9650;',
            'country-desc'  => 'Country &#9660;',
        );

        if (isset($uri['country'])) {
            $params['country_id'] = id_from_link($uri['country']);

            $country_info = $this->country->get_country($params['country_id']);
            if(empty($country_info)){
                show_404();
            }

            $data['country_selected'] = strForURL($country_info['country'] . ' ' . $params['country_id']);
            $data['meta_params']['[COUNTRY]'] = $data['country_name'] = $country_info['country'];
            $data['library_head_title'] .= ' - '.$country_info['country'];
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL.$current_lib['link_public'],
                'title'=> 'In '.$country_info['country']
            );
        }

        if (!empty($_SERVER['QUERY_STRING'])) {
            $data['get_params'] = array();
            foreach($_GET as $key => $one_param){
                $param_value = cleanOutput(cleanInput(cut_str($one_param)));
                $get_parameters[$key] = $param_value;
                $data['get_params'][$key] = $key.'='.$param_value;
            }
        }

        if(!empty($get_parameters['sort_by']) && array_key_exists($get_parameters['sort_by'], $data['list_sort_by'])){
            $data['sort_by'] = $params['sort_by'][] = $get_parameters['sort_by'];
            $data['meta_params']['[SORT_BY]'] = $data['list_sort_by'][$data['sort_by']];
        }else{
            $sort_by = array_keys($data['list_sort_by']);
            $params['sort_by'][] = $sort_by[0];
        }

        list($data['sort_by_link'], $data['get_sort_by']) = explode('?', get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['sort_by']));
        list($data['page_link'], $data['get_per_p']) = explode('?', get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['per_p']));
        $data['keywords_link'] = get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['keywords']);
        list($data['link_country'], $_) = explode('?', get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['country']));

        // GET KEYWORDS
        if (!empty($get_parameters['keywords'])){
            $data['meta_params']['[KEYWORDS]'] = $data['keywords'] = $params['keywords'] = $get_parameters['keywords'];
        }

        if (isset($_GET['per_p']) && abs(intVal($_GET['per_p']))){
            $data['per_p'] = $params['per_p'] = abs(intVal($_GET['per_p']));
        }

        if (!empty($uri['page']) && $uri['page'] >= 1){
            $data['page'] = $params['start'] = abs(intVal($uri['page'])) * $params['per_p'] - $params['per_p'];
            $data['meta_params']['[PAGE]'] = intVal($uri['page']);
        }

        $data['count'] = $this->inspection_agency->get_inspection_count($params);
        $data['list_inspection_agency'] = $this->inspection_agency->get_all_inspection($params);

        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl['page'], $current_lib['link_public']),
            'first_url'     => get_dynamic_url($links_tpl_without['page'], $current_lib['link_public']),
            'total_rows'    => $data['count'],
            'per_page'      => $params['per_p'],
            'replace_url'   => true
        );
        $this->load->library('Pagination', 'pagination');
        $this->pagination->initialize($paginator_config);
        $data['pagination']     = $this->pagination->create_links();

        $data['breadcrumbs']    = $this->breadcrumbs;
        $data['configs_library']= $this->lib_config->get_lib_configs();

        $data['library_page']   = $current_lib['link_public'];
        $data['library_detail']   = $current_lib['link_public_detail'];
        $data['library_search']   = $current_lib['link_public_search'];
        $data['library_name']   = 'library_inspection_agency';
        $data['link_to_reset_keywords'] = get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['keywords']);
        $data['link_to_reset_country'] = get_dynamic_url($current_lib['link_public'] . '/' . $links_tpl_without['country']);
        $data['link_to_reset_all_filters'] = get_dynamic_url($current_lib['link_public']);

        $data['list_countries'] = $this->country->get_countries();

        $data['header_out_content'] = 'new/library_settings/library_inspection_agency/header_view';
        $data['main_content'] = 'new/library_settings/library_inspection_agency/index_view';
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
                'rules' => array('required' => '', 'integer' => '', 'min[0]' => '')
            )
        );

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $this->load_main();

        $id_record = $_POST['item_id'];

        $record = array();
        $record = $this->inspection_agency->get_inspection(array('id_record' => $id_record));

        if(empty($record['email'])) {
            jsonResponse('Error: Email for this inspection agency doesn\'t found!');
        }

        jsonResponse('', 'success', array('email' => $record['email']));
    }

    public function administration(){
        checkAdmin('manage_content');

        $data['filter_country'] = true;
        $data['filter_visible'] = true;

        $this->view->assign($data);
        $this->view->assign('title', 'Configs Library Inspection Agency');
        $this->view->display('admin/header_view');
        $this->view->display('admin/library_settings/inspection_agency/inspection_agency_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_inspection_agency(){
        checkAdminAjaxDT('manage_content');

        $this->load->model('Lib_Inspection_Agency_Model', 'inspection_agency');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_record'  => 'id_ia',
                'dt_country'    => 'country',
                'dt_company'    => 'company',
                'dt_address'    => 'address',
                'dt_phone'      => 'phone',
                'dt_email'      => 'email',
                'dt_website'    => 'url_site',
                'dt_is_visible' => 'is_visible'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'visible_record', 'key' => 'set_visible', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_ia-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        if (isset($_POST['set_country'])){

            if($_POST['set_country'] == 0) {
                $params['country_id'] = 0;
            } else {
                $params['exist_country'] = true;
            }
        }

        $records = $this->inspection_agency->get_all_inspection($params);
        $records_count = $this->inspection_agency->get_inspection_count($params);

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
                'dt_id_record'    =>  $record['id_ia'] . '<div class="ml-5 w-15 pull-right"><input class="checked-element" type="checkbox" value="' . $record['id_ia'] . '">',
                'dt_country'      =>  $record['id_country'] ? $record['country'] : '---',
                'dt_company'      =>  $record['company'],
                'dt_address'      =>  $record['address'],
                'dt_phone'        =>  $record['phone'],
                'dt_email'        =>  $record['email'],
                'dt_website'      =>  !empty($record['url_site']) ? '<button class="btn btn-primary btn-xs link-clipboard" data-clipboard-text="'.$record['url_site'].'"><i class="ep-icon ep-icon_link fs-12_i m-0"></i> site link</button>' : '---',
                'dt_serv_provided'=>  $record['services_provided'],
                'dt_type_add'     =>  $record['type_record'] == 1 ? 'Manual' : 'File',
                'dt_is_visible'   =>  $record['is_visible'] ? 'Yes' : 'Not',
                'dt_actions'      =>  '<a class="ep-icon ep-' . ($record['is_visible'] ? 'icon_invisible' : 'icon_visible') . ' confirm-dialog" data-callback="visible_status" data-record="' . $record['id_ia'] . '" data-status="' . $record['is_visible'] . '" title="Change status record" data-message="Are you sure you want to change status of this record?" href="#" ></a>
                                       <a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="library_inspection_agency/popup_forms/edit_record/'. $record['id_ia'] . '" data-title="Edit inspection agency" title="Edit this inspection agency"></a>
                                       <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_acc_record" data-record="' . $record['id_ia'] . '" title="Remove this inspection agency" data-message="Are you sure you want to delete this inspection agency?" href="#" ></a>',
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
                    $data['current_contoller'] = 'library_inspection_agency';
                }
                $this->view->display('admin/library_settings/common_form/add_by_config_form', $data);
            break;

            case 'add_record_excel':
                $library = $this->lib_config->get_lib_config($this->id_config);

                if(!empty($library['file_name'])){
                    $file_config = $this->dir_config . $this->id_config . '/config_' . $library['file_name'] . '.php';
                    if(is_file($file_config))
                        require ($file_config);

                    $data['format_read'] = implode(', ', $allowed_extension);
                }

                $data['current_contoller'] = 'library_inspection_agency';
                $this->view->display('admin/library_settings/common_form/upload_file_form', $data);
            break;

            case 'edit_record':
                $id_record = (int)$this->uri->segment(4);

                $library = $this->lib_config->get_lib_config($this->id_config);

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
                    $data['current_contoller'] = 'library_inspection_agency';
                }

                $data['record'] = $this->inspection_agency->get_inspection(array('id_record' => $id_record));

                if(empty($data['record']))
                    messageInModal('Error: This library setting does not exist.');

                $data['id_record'] = $data['record']['id_ia'];
                $data['id_select'] = $data['record']['id_country'];

                $this->view->display('admin/library_settings/common_form/edit_by_config_form', $data);
            break;

            case 'update_by_country':
                $this->load->model('Country_Model', 'country');
                $data['empty_country_records'] = $this->inspection_agency->get_all_inspection(array('is_country' => false, 'group_by' => 'country'));
                $data['countres'] = $this->country->get_countries();
                $this->view->display('admin/library_settings/inspection_agency/forms/update_country', $data);
            break;
        }
    }

    public function ajax_library_operation(){
        checkAdminAjax('manage_content');

        $this->load->model('Lib_Inspection_Agency_Model', 'inspection_agency');

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'change_status':
                $id_record = intVal($_POST['id_record']);
                $status_record = intVal($_POST['status']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

                $exist_record = $this->inspection_agency->check_inspection(array('id_record' => $id_record, 'visible_record' => $status_record));

                if (!$exist_record)
                    jsonResponse('Info: The inspection agency with this id and status doesn\'t found', 'info');

                if (!$this->inspection_agency->update_inspection($id_record, array('is_visible' => !$status_record))){
                    jsonResponse('Info: Error updating the status of this entry', 'info');
                }

                jsonResponse('The status of the record was updated with success', 'success');
                break;

            case 'save_record_manual':
                $validator_rules = array(
//                    array(
//                        'field' => 'country',
//                        'label' => 'Country',
//                        'rules' => array('required' => '', 'max_len[100]' => '')
//                    ),
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
                        'field' => 'company',
                        'label' => 'Company',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array( 'max_len[150]' => '') //'required' => '',
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('required' => '', 'valid_url' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'services_provided',
                        'label' => 'Services Provided',
                        'rules' => array('required' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $exist_record = $this->inspection_agency->check_inspection(array('email' => cleanInput($_POST['email'], true)));

                if ($exist_record)
                    jsonResponse('Info: The inspection agency with this email already exists. Please edit the existing.', 'info');

                $insert = array(
                    'id_country' => cleanInput($_POST['id_country']),
                    'country'    => cleanInput($_POST['item_id_country']),
                    'company'    => cleanInput($_POST['company']),
                    'address'    => cleanInput($_POST['address']),
                    'phone'      => cleanInput($_POST['phone']),
                    'email'      => cleanInput($_POST['email'], true),
                    'url_site'   => $_POST['url_site'],
                    'services_provided'=> cleanInput($_POST['services_provided']),
                    'type_record'   => 1
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $insert[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                $id_record = $this->inspection_agency->set_inspection($insert);

                if($id_record)
                    jsonResponse('The library inspection agency have been successfully saved.', 'success');
                else
                    jsonResponse('Error: You cannot add library inspection agency now. Please try again later.');
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

                if($delete_record && $this->inspection_agency->get_inspection_count(array('type_record' => 0))){
                    if(!$this->inspection_agency->delete_inspection(array('type_record' => 0)))
                        jsonResponse('Error: You cannot remove inspection agency now.');
                }

                if(!$this->inspection_agency->set_rows_inspection($result))
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
//                    array(
//                        'field' => 'country',
//                        'label' => 'Country',
//                        'rules' => array('required' => '', 'max_len[100]' => '')
//                    ),
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
                        'field' => 'company',
                        'label' => 'Company',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('valid_url' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'services_provided',
                        'label' => 'Services Provided',
                        'rules' => array('required' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_record = intVal($_POST['id_record']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

//                $exist_record = $this->inspection_agency->check_inspection(array('email' => cleanInput($_POST['email'], true), 'not_id_record' => $id_record));
//
//                if ($exist_record)
//                    jsonResponse('Info: The inspection agency with this email already exists. Please edit the existing.', 'info');

                $update = array(
                    'id_country' => cleanInput($_POST['id_country']),
                    'country'    => cleanInput($_POST['item_id_country']),
                    'company'    => cleanInput($_POST['company']),
                    'address'    => cleanInput($_POST['address']),
                    'phone'      => cleanInput($_POST['phone']),
                    'email'      => cleanInput($_POST['email'], true),
                    'url_site'   => $_POST['url_site'],
                    'services_provided'=> cleanInput($_POST['services_provided']),
                    'type_record'   => intVal($_POST['type_record'])
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $update[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                if ($this->inspection_agency->update_inspection($id_record, $update))
                    jsonResponse('The inspection agency have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot edit inspection agency now. Please try again later.');
            break;

            case 'remove_record':
                $id_record = intVal($_POST['record']);

                if(!$this->inspection_agency->check_inspection(array('id_record' => $id_record)))
                    jsonResponse('This inspection agency doesn\'t exist.');

                if($this->inspection_agency->delete_inspection(array('id_record' => $id_record)))
                    jsonResponse('The inspection agency has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this inspection agency now. Please try again later.');
            break;

            case 'download_last_file':
                $this->load->model('Config_Lib_Model', 'lib_config');

                $library = $this->lib_config->get_lib_config($this->id_config);

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

                if($this->inspection_agency->delete_inspection(array('id_records' => $list_elements)))
                    jsonResponse('The list of records has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this list of records right now. Please try again later.');
            break;

            case 'change_country_agency':
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
                if (!$this->inspection_agency->check_inspection(array('country_name' => $contry_name_change))){
                    jsonResponse('Info: Country you want to replace is not found.');
                }

                $params = array(
                    'source_data'     => $country_select,
                    'condition'       => array('column' => 'country', 'value' => $contry_name_change),
                    'replace_columns' => array('from' => 'id, country', 'to' => 'id_country, country'),
                );

                if (!$this->inspection_agency->update_country_inspection_agency($params)){
                    jsonResponse('Info: Error to chengede data.');
                }

                jsonResponse('All entries were successfully updated', 'success');
            break;
        }
    }
}
?>

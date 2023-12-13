<?php

use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */

class Library_Accreditation_Body_Controller extends TinyMVC_Controller {
    private $dir_config = 'library_store/';
    private $id_config  = 3;

    /**
     * @var Config_Lib_Model $library_config_model
     */
    private $library_config_model;

    /**
     * @var Lib_Accreditation_Body_Model $accreditation_body_model
     */
    private $accreditation_body_model;

    private function load_main_models(){
        $this->library_config_model = model(Config_Lib_Model::class);
        $this->accreditation_body_model = model(Lib_Accreditation_Body_Model::class);
    }

    public function index(){
        $uri = uri()->uri_to_assoc(4);

        checkURI($uri, array('country', 'page'));
        checkIsValidPage($uri['page']);

        $this->load_main_models();

        $accreditation_library = $this->library_config_model->get_lib_config($this->id_config);
        if (empty($accreditation_library)) {
            show_404();
        }

        $this->breadcrumbs[] = array(
            'link' =>  __SITE_URL . $accreditation_library['link_public'],
            'title'=> $accreditation_library['lib_title']
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
            )
        );

        $links_tpl = uri()->make_templates($links_map, $uri);
        $links_tpl_without = uri()->make_templates($links_map, $uri, true);

        $meta_params = array();

        $page = empty($uri['page']) ? 1 : (int) $uri['page'];
        $per_page = (int) config('library_accreditation_body_per_page', 20);
        $results_limit = (int) config('env.ENTITIES_RESULT_LIMIT', 10000);

        if (!empty($uri['country'])) {
            $country_id = id_from_link($uri['country']);

            $country = model(Country_Model::class)->get_country($country_id);
            if (empty($country) || $uri['country'] != strForURL($country['country'] . ' ' . $country_id)) {
                show_404();
            }

            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . $accreditation_library['link_public'] . '/country/' . $uri['country'],
                'title' => $country['country']
            );

            $meta_params['[COUNTRY]'] = $country['country'];
            $selected_country_slug = $uri['country'];
        }

        if (!empty($_GET['keywords'])) {
            $keywords = $_GET['keywords'];

            $meta_params['[KEYWORDS]'] = cleanOutput(cleanInput($keywords));
        }

        if ($page > 1){
            $meta_params['[PAGE]'] = $uri['page'];
        }

        $data = array(
            'link_to_reset_all_filters' => get_dynamic_url($accreditation_library['link_public']),
            'link_to_reset_keywords'    => get_dynamic_url($accreditation_library['link_public'] . '/' . $links_tpl_without['keywords']),
            'link_to_reset_country'     => get_dynamic_url($accreditation_library['link_public'] . '/' . $links_tpl_without['country']),
            'sidebar_right_content'     => 'new/library_settings/sidebar_view',
            'list_accreditation'        => array(),
            'header_out_content'        => 'new/library_settings/library_accreditation_body/header_view',
            'footer_out_content'        => 'new/about/bottom_need_help_view',
            'library_head_title'        => $accreditation_library['lib_title'],
            'country_selected'          => $selected_country_slug ?? '',
            'configs_library'           => $this->library_config_model->get_lib_configs(),
            'list_countries'            => model(Country_Model::class)->get_countries(),
            'library_search'            => $accreditation_library['link_public_search'],
            'main_content'              => 'new/library_settings/library_accreditation_body/index_view',
            'library_page'              => $accreditation_library['link_public'],
            'country_name'              => $country['country'] ?? null,
            'library_name'              => 'library_accreditation_body',
            'breadcrumbs'               => $this->breadcrumbs,
            'meta_params'               => $meta_params,
            'keywords'                  => $keywords ?? null,
        );

        if ($page * $per_page > $results_limit) {
            $this->view->assign($data);
            $this->view->display('new/index_template_view');

            return;
        }

        $accreditation_list_params = array(
            'visible_record'    => 1,
            'country_id'        => $country_id ?? null,
            'keywords'          => null === $keywords ? null : cleanInput(cut_str($keywords)),
            'sort_by'           => array('body-asc'),
            'start'             => ($page - 1) * $per_page,
            'per_p'             => $per_page,
        );

        $accreditation_list_count = $this->accreditation_body_model->get_accreditation_count($accreditation_list_params);

        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl['page'], $accreditation_library['link_public']),
            'first_url'     => get_dynamic_url($links_tpl_without['page'], $accreditation_library['link_public']),
            'total_rows'    => $accreditation_list_count,
            'per_page'      => $per_page,
            'replace_url'   => true
        );

        library('pagination')->initialize($paginator_config);

        $data['list_accreditation'] = empty($accreditation_list_count) ? array() : $this->accreditation_body_model->get_all_accreditation($accreditation_list_params);
        $data['pagination'] = library('pagination')->create_links();
        $data['count'] = $accreditation_list_count;
        $data['per_p'] = $per_page;
        $data['page'] = $page;

        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function get_email_by_id(){
        checkIsAjax();

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

        $this->load_main_models();

        $id_record = intVal($_POST['item_id']);

        $record = $this->accreditation_body_model->get_accreditation(array('id_record' => $id_record));

        if (empty($record['email'])) {
            jsonResponse('Email for this accreditation body doesn\'t found!');
        }

        jsonResponse('', 'success', array('email' => $record['email']));
    }

    public function administration(){
        checkAdmin('manage_content');

        $data['filter_country'] = true;
        $data['filter_visible'] = true;

        $this->view->assign($data);
        $this->view->assign('title', 'Configs Library Accreditation Body');
        $this->view->display('admin/header_view');
        $this->view->display('admin/library_settings/accreditation/accreditation_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_accreditation(){
        checkAdminAjaxDT('manage_content');

        $this->load->model('Lib_Accreditation_Body_Model', 'accreditation');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_record'  => 'id_acc',
                'dt_country'    => 'country',
                'dt_contact'    => 'contact',
                'dt_title'      => 'title',
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

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_acc-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        if (isset($_POST['set_country'])){

            if($_POST['set_country'] == 0){
                $params['country_id'] = 0;
            } else {
                $params['exist_country'] = true;
            }
        }

        $records = $this->accreditation->get_all_accreditation($params);
        $records_count = $this->accreditation->get_accreditation_count($params);

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
                'dt_id_record'   => $record['id_acc'] . '<div class="ml-5 w-15 pull-right"><input class="checked-element" type="checkbox" value="' . $record['id_acc'] . '">',
                'dt_country'     => $record['id_country'] ? $record['country'] : '---',
                'dt_body'        => $record['body'],
                'dt_contact'     => $record['contact'],
                'dt_title'       => $record['title'],
                'dt_address'     => $record['address'],
                'dt_phone'       => $record['phone'],
                'dt_email'       => $record['email'],
                'dt_website'     => !empty($record['url_site']) ? '<button class="btn btn-primary btn-xs link-clipboard" data-clipboard-text="'.$record['url_site'].'"><i class="ep-icon ep-icon_link fs-12_i m-0"></i> site link</button>' : '---',
                'dt_type_add'    => $record['type_record'] == 1 ? 'Manual' : 'File',
                'dt_is_visible'  => $record['is_visible'] ? 'Yes' : 'Not',
                'dt_actions'     => '<a class="ep-icon ep-' . ($record['is_visible'] ? 'icon_invisible' : 'icon_visible') . ' confirm-dialog" data-callback="visible_status" data-record="' . $record['id_acc'] . '" data-status="' . $record['is_visible'] . '" title="Change status record" data-message="Are you sure you want to change status of this record?" href="#" ></a>
                                    <a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="library_accreditation_body/popup_forms/edit_record/'. $record['id_acc'] . '" data-title="Edit library accreditation" title="Edit this accreditation"></a>
                                <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_acc_record" data-record="' . $record['id_acc'] . '" title="Remove this accreditation" data-message="Are you sure you want to delete this accreditation?" href="#" ></a>',
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
                    $data['current_contoller'] = 'library_accreditation_body';
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

                $data['current_contoller'] = 'library_accreditation_body';
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
                    $data['current_contoller'] = 'library_accreditation_body';
                }

                $data['record'] = $this->accreditation_body_model->get_accreditation(array('id_record' => $id_record));

                if(empty($data['record']))
                    messageInModal('Error: This library setting does not exist.');

                $data['id_record'] = $data['record']['id_acc'];
                $data['id_select'] = $data['record']['id_country']; // selected for select from form...

                $this->view->display('admin/library_settings/common_form/edit_by_config_form', $data);
            break;

            case 'update_by_country':
                $this->load->model('Country_Model', 'country');
                $data['empty_country_records'] = $this->accreditation_body_model->get_all_accreditation(array('is_country' => false, 'group_by' => 'country'));
                $data['countres'] = $this->country->get_countries();
                $this->view->display('admin/library_settings/accreditation/forms/update_country', $data);
            break;
        }
    }

    public function ajax_library_operation(){
        checkAdminAjax('manage_content');

        // $this->load->model('Lib_Accreditation_Body_Model', 'accreditation');

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'change_status':
                $id_record = intVal($_POST['id_record']);
                $status_record = intVal($_POST['status']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

                $this->load_main_models();

                $exist_record = $this->accreditation_body_model->check_accreditation(array('id_record' => $id_record, 'visible_record' => $status_record));

                if (!$exist_record)
                    jsonResponse('Info: The accreditation body with this id and status doesn\'t found', 'info');

                if (!$this->accreditation_body_model->update_accreditation($id_record, array('is_visible' => !$status_record))){
                    jsonResponse('Info: Error updating the status of this entry', 'info');
                }

                jsonResponse('The status of the record was updated with success', 'success');
                break;

            case 'save_record_manual':
                $validator_rules = array(
                    // array(
                    //     'field' => 'country',
                    //     'label' => 'Country',
                    //     'rules' => array('required' => '', 'max_len[100]' => '')
                    // ),
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
                        'field' => 'body',
                        'label' => 'Body',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'contact',
                        'label' => 'Contact',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[300]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array('required' => '',  'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('valid_url' => '', 'max_len[100]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $this->load_main_models();
                $exist_record = $this->accreditation_body_model->check_accreditation(array('email' => cleanInput($_POST['email'], true)));

                if ($exist_record)
                    jsonResponse('Info: The accreditation body with this email already exists. Please edit the existing.', 'info');

                $insert = array(
                    'id_country' => cleanInput($_POST['id_country']),
                    'country'    => cleanInput($_POST['item_id_country']),
                    'body'       => cleanInput($_POST['body']),
                    'contact'    => cleanInput($_POST['contact']),
                    'title'      => $_POST['title'],
                    'address'    => $_POST['address'],
                    'phone'      => cleanInput($_POST['phone']),
                    'email'      => cleanInput($_POST['email'], true),
                    'url_site'   => $_POST['url_site'],
                    'type_record'=> 1
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $insert[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                $id_record = $this->accreditation_body_model->set_accreditation($insert);

                if($id_record)
                    jsonResponse('The library settings have been successfully saved.', 'success');
                else
                    jsonResponse('Error: You cannot add library settings now. Please try again later.');
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

                $this->load_main_models();

                if($delete_record && $this->accreditation_body_model->get_accreditation_count(array('type_record' => 0))) {
                    if(!$this->accreditation_body_model->delete_accreditation(array('type_record' => 0))) {
                        jsonResponse('Error: You cannot remove accreditation body now.');
                    }
                }

                if(!$this->accreditation_body_model->set_rows_accreditation($result)) {
                    jsonResponse('Error : New records cannot be insert!');
                }

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
                        'field' => 'body',
                        'label' => 'Body',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'contact',
                        'label' => 'Contact',
                        'rules' => array('required' => '', 'max_len[100]' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('max_len[150]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[300]' => '')
                    ),
                    array(
                        'field' => 'phone',
                        'label' => 'Phone',
                        'rules' => array('required' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[150]' => '')
                    ),
                    array(
                        'field' => 'url_site',
                        'label' => 'Website',
                        'rules' => array('valid_url' => '', 'max_len[100]' => '')
                    )
                );

                $this->load_main_models();

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_record = intVal($_POST['id_record']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

                $exist_record = $this->accreditation_body_model->check_accreditation(array('email' => cleanInput($_POST['email'], true), 'not_id_record' => $id_record));

                if ($exist_record)
                    jsonResponse('Info: The accreditation body with this email already exists. Please edit the existing.', 'info');

                $update = array(
                    'id_country' => cleanInput($_POST['id_country']),
                    'country'    => cleanInput($_POST['item_id_country']),
                    'body'       => cleanInput($_POST['body']),
                    'contact'    => cleanInput($_POST['contact']),
                    'title'      => $_POST['title'],
                    'address'    => $_POST['address'],
                    'phone'      => cleanInput($_POST['phone']),
                    'email'      => cleanInput($_POST['email'], true),
                    'url_site'   => $_POST['url_site'],
                    'type_record'=> intVal($_POST['type_record'])
                );

                if(!empty($_POST['relation']) && !empty($_POST['column_name']))
                    $update[cleanInput($_POST['column_name'])] = intVal($_POST['relation']);

                if ($this->accreditation_body_model->update_accreditation($id_record, $update))
                    jsonResponse('The accreditation body have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot edit accreditation body now. Please try again later.');
            break;

            case 'remove_record':
                $id_record = intVal($_POST['record']);

                if(!$this->accreditation_body_model->check_accreditation(array('id_record' => $id_record)))
                    jsonResponse('This accreditation doesn\'t exist.');

                if($this->accreditation_body_model->delete_accreditation(array('id_record' => $id_record)))
                    jsonResponse('The accreditation body has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this accreditation body now. Please try again later.');
            break;

            case 'download_last_file':

                $this->load_main_models();

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

                $this->load_main_models();
                if($this->accreditation_body_model->delete_accreditation(array('id_records' => $list_elements)))
                    jsonResponse('The list of records has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this list of records right now. Please try again later.');
            break;

            case 'change_country_accreditation':
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

                $this->load_main_models();
                $contry_name_change = $_POST['record_country'];
                if (!$this->accreditation_body_model->check_accreditation(array('country_name' => $contry_name_change))){
                    jsonResponse('Info: Country you want to replace is not found.');
                }

                $params = array(
                    'source_data'     => $country_select,
                    'condition'       => array('column' => 'country', 'value' => $contry_name_change),
                    'replace_columns' => array('from' => 'id, country', 'to' => 'id_country, country'),
                );

                if (!$this->accreditation_body_model->update_country_accreditation($params)){
                    jsonResponse('Info: Error to chengede data.');
                }

                jsonResponse('All entries were successfully updated', 'success');
            break;
        }
    }
}
?>

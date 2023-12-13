<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Setting_Controller extends TinyMVC_Controller {
    private $dir_config = 'library_store/';

    public function administration(){
        checkAdmin('manage_content');

        $data['title'] = 'Configs loading file';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/library_settings/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_config_library_administration(){
        checkAdminAjaxDT('manage_content');

        $this->load->model('Config_Lib_Model', 'lib_config');

        $params = array(
            'limit'   => intval($_POST['iDisplayLength']),
            'skip'   => intval($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, array(
                'dt_id_lib'     => 'id_lib',
                'dt_lib_title'  => 'lib_title'
            ))
        );

        if (isset($_POST['keywords']))
            $params['keywords'] = cleanInput($_POST['keywords']);

        $configs = $this->lib_config->get_lib_configs($params);
        $configs_count = $this->lib_config->count_lib_configs($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $configs_count,
            "iTotalDisplayRecords" => $configs_count,
            'aaData' => array()
        );

        if(empty($configs))
            jsonResponse('', 'success', $output);

        foreach($configs as $config){
            $content = "";
            if (strlen($config['lib_description']) > 250)
                $content = '<p class="tac"><a class="btn-customs-req-more ep-icon ep-icon_arrows-down" href="#" title="view more"></a></p>';

            $output['aaData'][] = array(
                'dt_id_lib'   =>  $config['id_lib'],
                'dt_lib_title'=>  $config['lib_title'],
                'dt_lib_text' =>  '<div class="h-35 hidden-b">' . $config['lib_description'] . '</div>' . $content,
                'dt_lib_file' =>  !empty($config['file_name']) ? '<span title="' . $config['file_name'] . '">' . $config['file_name'] . '</span>' : '',
                'dt_type_control'=>$config['lib_type'],
                'dt_actions'  =>  '<a class="ep-icon  ep-icon_file-view" href="' . __SITE_URL . $config['link_admin'] . '" title="List of record"></a>
                                  ' . ($config['lib_type'] == 'file' ?
                                  '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon w-20 h-20 icon-files-xlsx-small" href="library_setting/popup_forms/show_structure/'. $config['id_lib'] . '" data-title="Structure file" title="Structure file"></a>' : '').
                                  '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="library_setting/popup_forms/edit_library_setting/'. $config['id_lib'] . '" data-title="Edit library setting" title="Edit this library setting"></a>
                                   <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_library_setting" data-record="' . $config['id_lib'] . '" title="Remove this library setting" data-message="Are you sure you want to delete this library setting?" href="#" ></a>',
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkAdminAjaxModal('manage_content');

        $this->load->model('Config_Lib_Model', 'lib_config');

        $form = $this->uri->segment(3);

        switch ($form) {
            case 'add_library_setting':
                $this->view->display('admin/library_settings/add_setting_form_view');
            break;

            case 'edit_library_setting':
                $id_record = (int)$this->uri->segment(4);
                $data['record'] = $this->lib_config->get_lib_config($id_record);

                if(empty($data['record']))
                    messageInModal('Error: This library setting does not exist.');

                $this->view->display('admin/library_settings/edit_setting_form_view', $data);
            break;

            case 'show_structure':
                $id_record= (int)$this->uri->segment(4);
                $record   = $this->lib_config->get_lib_config($id_record);

                if(empty($record['file_name']))
                    messageInModal('Error: This library setting don\'t have file for setting.');

                $file = $this->dir_config . $id_record . '/config_' . $record['file_name'] . '.php';

                if(!is_file($file))
                    messageInModal('Error: File for this library setting doesn\'t found.');

                require_once ($file);

                $data['config'] = $config;
                $data['config_name'] = $record['file_name'];
                $data['id_record'] = $id_record;

                $this->view->display('admin/library_settings/show_structure_form_view', $data);
            break;
        }
    }

    public function ajax_library_setting_operation(){
        checkAdminAjax('manage_content');

        $this->load->model('Config_Lib_Model', 'lib_config');

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'save_library_setting':
                $type_control = $_POST['type_control'];
                $type_param   = 'manual';

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
                        'field' => 'link_admin',
                        'label' => 'Admin page',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public',
                        'label' => 'Public page',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public_detail',
                        'label' => 'Public detail',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public_search',
                        'label' => 'Public search',
                        'rules' => array('required' => '')
                    )
                );

                if(!empty($type_control)){
                    $validator_rules[] = array(
                        'field' => 'file_name',
                        'label' => 'File Name',
                        'rules' => array('required' => '')
                    );

                    $type_param = 'file';
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());


                $exist_record = $this->lib_config->check_lib_config(
                    array(
                        'title' => cleanInput($_POST['title']),
                        'file' => cleanInput($_POST['file_name'])
                    )
                );
                if ($exist_record)
                    jsonResponse('Info: The library settings with this name and file already exists. Please edit the existing.', 'info');

                $insert = array(
                    'lib_title'      => cleanInput($_POST['title']),
                    'lib_description'=> $_POST['description'],
                    'lib_type'       => $type_param,
                    'file_name'      => cleanInput($_POST['file_name']),
                    'link_admin'     => $_POST['link_admin'],
                    'link_public'    => $_POST['link_public'],
                    'link_public_detail'    => $_POST['link_public_detail'],
                    'link_public_search'    => $_POST['link_public_search']
                );

                $id_record = $this->lib_config->set_library_setting($insert);

                if($id_record){
                    if(!empty($type_control)){
                        if(!file_exists($this->dir_config . $id_record))
                            mkdir($this->dir_config . $id_record);

                        $new_file = fopen($this->dir_config . $id_record . "/config_" . $insert['file_name'] . ".php", "w");
                        $txt = "<?php";
                        fwrite($new_file, $txt);
                        fclose($new_file);
                    }
                    jsonResponse('The library settings have been successfully saved.', 'success');
                }
                else
                    jsonResponse('Error: You cannot add library settings now. Please try again later.');
            break;

            case 'edit_library_setting':
                $type_control = $_POST['type_control'];
                $type_param   = 'manual';

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
                        'field' => 'link_admin',
                        'label' => 'Admin page',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public',
                        'label' => 'Public page',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public_detail',
                        'label' => 'Public detail',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_public_search',
                        'label' => 'Public search',
                        'rules' => array('required' => '')
                    )
                );

                if(!empty($type_control)){
                    $validator_rules[] = array(
                        'field' => 'file_name',
                        'label' => 'File Name',
                        'rules' => array('required' => '')
                    );

                    $type_param = 'file';
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_record = intVal($_POST['id_record']);

                if(empty($id_record))
                    jsonResponse('Info: Id record doesn\'t set.');

                $exist_record = $this->lib_config->check_lib_config(
                    array(
                        'title' => cleanInput($_POST['title']),
                        'file' => cleanInput($_POST['file_name']),
                        'not_id_record' => $id_record
                    )
                );

                if($exist_record)
                    jsonResponse('Info: The library settings with this name and file already exists. Please edit the existing.', 'info');

                $update = array(
                    'lib_title'      => cleanInput($_POST['title']),
                    'lib_description'=> $_POST['description'],
                    'lib_type'       => $type_param,
                    'link_admin'     => $_POST['link_admin'],
                    'link_public'    => $_POST['link_public'],
                    'link_public_detail'    => $_POST['link_public_detail'],
                    'link_public_search'    => $_POST['link_public_search'],
                    'file_name'      => !empty($type_control) ? cleanInput($_POST['file_name']) : '',
                );

                $record = $this->lib_config->get_lib_config($id_record);

                if(!empty($update['file_name'])){
                    // Old file name
                    $file_config  = '/config_' . $record['file_name'] . '.php';
                    $file_sample  = '/sample_' . $record['file_name'] . '.xls';

                    // New file name
                    $change_config= '/config_' . $update['file_name'] . '.php';
                    $change_sample= '/sample_' . $update['file_name'] . '.xls';

                    if(!is_file($this->dir_config . $id_record . $file_config))
                        jsonResponse('Info: File config_' . $record['file_name'] . '.php doesn\'t found.', 'info');

                    if(!rename($this->dir_config . $id_record . $file_config, $this->dir_config . $id_record . $change_config))
                        jsonResponse('Info: Error to change file name.', 'info');

                    if(is_file($this->dir_config . $id_record . $file_sample)){
                        if(!rename($this->dir_config . $id_record . $file_sample, $this->dir_config . $id_record . $change_sample))
                            jsonResponse('Info: Error to change sample file.', 'info');
                    }

                }

                if ($this->lib_config->update_library_setting($id_record, $update))
                    jsonResponse('The library settings have been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot add library settings now. Please try again later.');
            break;

            case 'remove_library_setting':
                $id_record = intVal($_POST['record']);
                $record = $this->lib_config->get_lib_config($id_record);

                if(empty($record))
                    jsonResponse('This library setting does not exist.');

                if(!empty($record['file_name'])){
                    $path = $this->dir_config . $id_record . '/config_' . $record['file_name'] . '.php';

                    if(is_file($path))
                        require ($path);

                    if(empty($db_table))
                        jsonResponse('Error : Table name for library doesn\'t set!');

                }

                if(file_exists($this->dir_config . $id_record))
                    remove_dir($this->dir_config . $id_record);

                if($this->lib_config->delete_library_setting($id_record))
                    jsonResponse('The library settings has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this library settings now. Please try again later.');
            break;

            case 'download_sample':
                $validator_rules = array(
                    array(
                        'field' => 'file_name',
                        'label' => 'File',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'record_id',
                        'label' => 'Config Id',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $file_name = cleanInput($_POST['file_name']);
                $id_record = (int)$_POST['record_id'];

                try {
                    /** @var TinyMVC_Library_Phpexcel $phpexcel */
                    $phpexcel = library(TinyMVC_Library_Phpexcel::class);
                    $phpexcel->set_config($this->dir_config, $this->id_config);
                    $phpexcel->example_xls();
                } catch (\Throwable $exception) {
                    jsonResponse($exception->getMessage(), 'error', withDebugInformation(
                        array(), array('exception' => throwableToArray($exception))
                    ));
                }

                jsonResponse('','success',array('src'=> __SITE_URL . $this->dir_config . $id_record . '/sample_' . $file_name . '.xls'));
            break;
        }
    }
}

?>

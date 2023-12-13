<?php

use App\Filesystem\CountryStatisticFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator as FilesystemFilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Country_Statistic_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();

    private FilesystemFilesystemOperator $storage;

    private PathPrefixer $prefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');

        $this->prefixer = $storageProvider->prefixer('public.storage');
    }

//	function get_img() {
//        set_time_limit(0);
//        $this->load->model('Country_Model', 'country');
//        $continents = $this->country->get_continents();
//        $countries = $this->country->fetch_port_country();
//        $continents = arrayByKey($continents, 'id_continent');
//
//        foreach ($countries as $country) {
////            $name = $continents[$country['id_continent']]['abbr'] . strtolower($country['abr3']);
//            $name = substr($continents[$country['id_continent']]['name_continent'], 0, 2) . strtolower($country['abr3']);
//            $name = strtolower($name);
//            $content = file_get_contents('https://atlas.media.mit.edu/static/img/headers/country/' . $name . '.jpg');
//            if ($content == false) {
//                $name = substr($continents[$country['id_continent']]['name_continent'], 0, 2);
//                $name = strtolower($name);
////                $content = file_get_contents('https://atlas.media.mit.edu/static/img/headers/country/' . $continents[$country['id_continent']]['abbr'] . '.jpg');
//                $content = file_get_contents('https://atlas.media.mit.edu/static/img/headers/country/' . $name . '.jpg');
//            }
//
//            if ($content == false) {
//                continue;
//            }
//            file_put_contents('public/img/export_import_statistic/' . strtolower($country['abr3']) . '.jpg', $content);
//        }
//    }


    function index() {
        show_comming_soon();

        $this->load->model('Country_Model', 'country');
        $this->load->model('Text_block_model', 'text_block');
        $this->load->model('Config_Lib_Model', 'lib_config');

        $this->breadcrumbs[] = array(
            'link' 	=> '',
            'title'	=> 'Export Import Statistic'
        );

        $data = array(
            'title' => 'Export / Import Statistic',
            'breadcrumbs' => $this->breadcrumbs,
            'hide_search_block' => true,
            'countries' => $this->country->fetch_port_country(),
            'continents' => $this->country->get_continents(),
            'bottom_text' => $this->text_block->get_text_block_by_shortname('export_import_statistic_countries')
        );

        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['library_page'] = 'library_country_statistic';
        $data['header_out_content'] = 'new/library_settings/library_country_statistic/header_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['main_content'] = 'new/library_settings/library_country_statistic/countries_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }


	function country() {
        show_comming_soon();

        $this->load->model('Export_Import_Info_Model', 'export_import_info');
        $this->load->model('Export_Import_Info_Templates_Model', 'export_import_info_templates');
        $this->load->model('Export_Import_Statistic_Model', 'export_import_statistic');
        $this->load->model('Country_Model', 'country');
        $this->load->model('Config_Lib_Model', 'lib_config');

        $country = $this->uri->segment(3);
        if (empty($country)) {
            show_404();
        }

        $country_id = id_from_link($country);

        $info = $this->export_import_info->get_info(array(
            'id_country' => $country_id
        ));

        if (empty($info)) {
            show_404();
        }

        $this->breadcrumbs[] = array(
            'link' 	=> __SITE_URL . 'library_country_statistic',
            'title'	=> 'Export Import Statistic'
        );

        $this->breadcrumbs[] = array(
            'link' 	=> '',
            'title'	=> $info['country']
        );

        $country_abbr = strtolower($info['abr3']);

        $templates = $this->export_import_info_templates->get_templates();


        $templateData = null;
        $plotData = null;

        if(__CACHE_ENABLE) {
            $this->load->model('Cache_Config_Model', 'cache_config');

            $c_config = $this->cache_config->get_cache_options('export_import_template_data');

            if(!empty($c_config) && $c_config['enable']){
                $this->load->library('Cache', 'cache');
                $this->cache->init(array('securityKey'	=> $c_config['folder']));
                $cacheData = $this->cache->get('country_' . $country_abbr);
                $templateData = $cacheData['template_data'];
                $plotData = $cacheData['plot_data'];
            }
        }

        if($templateData == null || $plotData == null) {
            $templateData = $this->export_import_statistic->get_template_data($country_abbr);
            $plotData = $this->export_import_statistic->get_plot_data($country_abbr);

            if(__CACHE_ENABLE && $c_config['enable']) {
                $this->cache->set('country_' . $country_abbr , array(
                    'template_data' => $templateData,
                    'plot_data' => $plotData
                ), $c_config['cache_time']);
            }
        }


        $templateDataKeys = array();
        $templateDataValues = array_values($templateData);
        foreach (array_keys($templateData) as $key) {
            $templateDataKeys[] = "[$key]";
        }

        foreach ($templates as $template) {
            if (empty($info[$template['key']])) {
                $info[$template['key']] = $template['text'];
            }

            $info[$template['key']] = str_replace($templateDataKeys, $templateDataValues, $info[$template['key']]);
        }

        $info['imageUrl'] = $this->storage->url(CountryStatisticFilePathGenerator::relativeImageUploadPath(strtolower($info['abr3']) . '.jpg'));

        $data = array(
            'hide_search_keywords' => true,
            'title' => 'Export / Import Statistic',
            'info' => $info,
            'breadcrumbs' => $this->breadcrumbs,
            'library_search' => 'library_country_statistic',
            'list_countries' => $this->country->fetch_port_country(),
            'plotData' => $plotData
        );

        $data['meta_params'] = array(
            '[COUNTRY]' => $info['country']
        );

        $data['library_page'] = 'library_country_statistic';
        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['header_out_content'] = 'new/library_settings/library_country_statistic/header_country_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['main_content'] = 'new/library_settings/library_country_statistic/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
	}


	function manage() {
        checkAdmin('manage_content');

        $data = array(
            'title' => 'Export / Import Statistic'
        );

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/library_settings/library_country_statistic/index_view');
        $this->view->display('admin/footer_view');
	}


    function ajax_save_text() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');

        if (empty($_POST['id'])) {
            jsonResponse('Please specify the ID');
        }

        if (empty($_POST['type'])) {
            jsonResponse('Please specify the type');
        }

        if (!isset($_POST['text'])) {
            jsonResponse('Please send the text');
        }

        $this->load->model('Export_Import_Info_Model', 'export_import_info');

        $updated = $this->export_import_info->update(intval($_POST['id']), array(
            cleanInput($_POST['type'], true) => empty($_POST['text']) ? null : cleanInput($_POST['text'])
        ));

        if(!$updated) {
            jsonResponse('Error while updating');
        }

        jsonResponse('Updated successfully', 'success');
    }


	function ajax_edit_text_modal() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');

        if (empty($_GET['id'])) {
            jsonResponse('Please specify the ID');
        }

        if (empty($_GET['type'])) {
            jsonResponse('Please specify the type');
        }

        $this->load->model('Export_Import_Info_Model', 'export_import_info');
        $this->load->model('Export_Import_Info_Templates_Model', 'export_import_info_templates');

        $params = array(
            'id' => $_GET['id']
        );

        $info = $this->export_import_info->get_info($params);

        if(!array_key_exists($_GET['type'], $info) && $_GET['type'] != 'image') {
            jsonResponse('Wrong type');
        }

        if ($_GET['type'] === 'image') {
            $this->view->display('admin/library_settings/library_country_statistic/edit_image_modal_view', array(
                'image' => strtolower($info['abr3']) . '.jpg',
                'path' => $this->storage->url(CountryStatisticFilePathGenerator::relativeImageUploadPath(strtolower($info['abr3']) . '.jpg')),
                'id' => $_GET['id'],
                'type' => $_GET['type'],
            ));
        } else {
            $templates = $this->export_import_info_templates->get_templates();
            $templates = arrayByKey($templates, 'key');

            $this->view->display('admin/library_settings/library_country_statistic/edit_text_modal_view', array(
                'text' => empty($info[$_GET['type']]) ? $templates[$_GET['type']]['text'] : $info[$_GET['type']],
                'id' => $_GET['id'],
                'type' => $_GET['type']
            ));
        }
    }


    function ajax_edit_templates_modal() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');

        $this->load->model('Export_Import_Info_Templates_Model', 'export_import_info_templates');

        $templates = $this->export_import_info_templates->get_templates();

        $this->view->display('admin/library_settings/library_country_statistic/edit_templates_modal_view', array(
            'templates' => $templates,
        ));
    }


    function ajax_save_templates() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');


        $this->load->model('Export_Import_Info_Templates_Model', 'export_import_info_templates');

        $allowedFields = array('description_text', 'export_text', 'import_text', 'origin_text', 'destination_text', 'h1_text');
        $updateData = array();
        foreach ($allowedFields as $allowedField) {
            if (empty($_POST[$allowedField])) {
                jsonResponse("Error: Please enter $allowedField");
            }

            $updateData[$allowedField] = cleanInput($_POST[$allowedField]);
        }


        $updated = $this->export_import_info_templates->updateTemplates($updateData);

        if(!$updated) {
            jsonResponse('Error while updating');
        }

        jsonResponse('Updated successfully', 'success');
    }


    function ajax_upload_image() {
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"), 'error');
        }

        $exportImportId = request()->request->get('id');

        if (empty($_FILES['files'])) {
            jsonResponse('Error: Please select file to upload.');
        }

        if (empty($exportImportId)) {
            jsonResponse('Please provide the ID');
        }

        checkAdmin('manage_content');

        $this->load->model('Export_Import_Info_Model', 'export_import_info');


        $info = $this->export_import_info->get_info(array(
            'id' => intval($exportImportId)
        ));

        if (empty($info)) {
            jsonResponse('Record not found');
        }

        if (empty($_FILES['files']) || empty($_FILES['files']['name'])) {
            jsonResponse('Empty file');
        }

        $path_info = pathinfo($_FILES['files']['name'][0]);
        $file_name = strtolower($info['abr3']) . '.' . $path_info['extension'];
        $_FILES['files']['name'][0] = $file_name;

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $path = CountryStatisticFilePathGenerator::relativeImageUploadPath($file_name);
        $uploadPath = $this->prefixer->prefixPath(dirname($path));

        if ($publicDisk->fileExists($path)) {
            try {
                $publicDisk->delete($path);
            } catch (\Throwable $th) {
                jsonResponse(translate('systmess_error_delete_country_statistic_image_fail'));
            }
        }

        /**
         * @deprecated Refactoring Library
         */

        global $tmvc;
        $conditions = array(
            'files' => $_FILES['files'],
            'destination' => $uploadPath,
            'use_original_name' => true,
            'resize' => '1500xR',
            'rules' => array(
                'size' => $tmvc->my_config['fileupload_max_file_size'],
                'min_height' => 200,
                'min_width' => 1000
            )
        );

        $result = $this->upload->upload_images_new($conditions);

        jsonResponse('Uploaded', 'success', array(
            'path' => $uploadPath,
            'name' => $file_name
        ));
    }


    function ajax_export_import_info() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');

        $this->load->model('Export_Import_Info_Model', 'export_import_info');
        $this->load->model('Export_Import_Info_Templates_Model', 'export_import_info_templates');

        $params = array(
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'select' => 'pc.country, pc.abr3, ei.id, ei.description_text, ei.export_text, ei.import_text, ei.destination_text, ei.origin_text'
        );

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                    case 'dt_country': $params['sort_by'][] = "pc.country {$_POST['sSortDir_' . $i]}";
                }
            }
        }

        if (!empty($_POST['sSearch'])) {
            $params['keywords'] = cleanInput($_POST['sSearch']);
        }

        $items = $this->export_import_info->get_info($params);
        $items_count = $this->export_import_info->get_info_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $items_count,
            "iTotalDisplayRecords" => $items_count,
            'aaData' => array()
        );

        $templates = $this->export_import_info_templates->get_templates();
        $templates = arrayByKey($templates, 'key');

        $fields = array('description_text', 'export_text', 'import_text', 'destination_text', 'origin_text');

        foreach ($items as $key => $item) {
            $_img_name = strtolower($item['abr3']);
            $imageUrl = '';

            if (!empty($_img_name)) {
                $imageUrl = $this->storage->url(CountryStatisticFilePathGenerator::relativeImageUploadPath(strtolower($_img_name . '.jpg')));
            }

            $out = array(
                'id_item' => $item['id'],
                'country' => $item['country'],
                'image'   => '<img class="h-50" style="max-width: 150px;" src="' . $imageUrl . '"/>'
            );

            foreach ($fields as $field) {
                $out[$field] = cut_str_with_dots( empty($item[$field]) ? $templates[$field]['text'] : $item[$field], 100);
            }

            $output['aaData'][] = $out;
        }

        jsonResponse('', 'success', $output);
    }

}

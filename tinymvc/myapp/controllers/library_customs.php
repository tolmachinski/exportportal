<?php

use App\Filesystem\CountryStatisticFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemOperator as FilesystemFilesystemOperator;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_Customs_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();

    private FilesystemFilesystemOperator $storage;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
    }

    function index() {
        show_comming_soon();

        $this->load->model('Country_Model', 'country');
        $this->load->model('Text_block_model', 'text_block');
        $this->load->model('Config_Lib_Model', 'lib_config');
        $this->load->model('Requirement_Model', 'requirements');

        $this->breadcrumbs[] = array(
            'link' 	=> '',
            'title'	=> 'Custom performance'
        );

        $data = array(
            'title' => 'Custom performance',
            'breadcrumbs' => $this->breadcrumbs,
            'hide_search_block' => true,
            'countries' => arrayByKey($this->requirements->get_countries_requirements(), 'id_continent', true),
            'continents' => $this->country->get_continents(),
            'bottom_text' => $this->text_block->get_text_block_by_shortname('export_import_statistic_countries')
        );

        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['library_page'] = 'library_customs';
        $data['header_out_content'] = 'new/library_settings/library_customs/header_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['main_content'] = 'new/library_settings/library_customs/countries_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }


	function country() {
        // show_comming_soon();

        $this->load->model('Country_Model', 'country');
        $this->load->model('Config_Lib_Model', 'lib_config');
        $this->load->model('Requirement_Model', 'requirements');
        $country = $this->uri->segment(3);
        if (empty($country)) {
            show_404();
        }

        $country_id = id_from_link($country);

        $country = $this->country->fetch_port_country($country_id);
        if (empty($country)) {
            show_404();
        }

        $requirements = $this->requirements->get_requirement(array('country' => $country_id));
        if (empty($requirements)) {
            show_404();
        }

        $this->breadcrumbs[] = array(
            'link' 	=> __SITE_URL . 'library_customs',
            'title'	=> 'Customs Performance'
        );

        $this->breadcrumbs[] = array(
            'link' 	=> '',
            'title'	=> $country[0]['country']
        );

        $country[0]['imageUrl'] = $this->storage->url(CountryStatisticFilePathGenerator::relativeImageUploadPath(strtolower($country[0]['abr3']) . '.jpg'));

        $data = array(
            'hide_search_keywords' => true,
            'requirements' => $requirements,
            'breadcrumbs' => $this->breadcrumbs,
            'title' => 'Export / Import Statistic',
            'country' => $country[0],
            'library_search' => 'library_customs',
            'list_countries' => $this->country->fetch_port_country(),
        );

        $data['meta_params'] = array(
            '[COUNTRY]' => $country[0]['country']
        );

        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['library_page'] = 'library_customs';
        $data['header_out_content'] = 'new/library_settings/library_customs/header_country_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['main_content'] = 'new/library_settings/library_customs/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
	}



}

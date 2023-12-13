<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Library_International_Standards_Controller extends TinyMVC_Controller {
    private $id_config  = 10;
    private $breadcrumbs = array();

    private function _load_main() {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Config_Lib_Model', 'lib_config');
    }

    public function index() {
        global $tmvc;
        $this->_load_main();
        $this->load->model('International_standards_Model', 'international_standards');
        $this->load->model('Country_Model', 'country');


        $current_lib = $this->lib_config->get_lib_config($this->id_config);
        $data['library_head_title'] = $current_lib['lib_title'];

        $link_array = array(
            'main' => __SITE_URL . $current_lib['link_public']
        );

        $this->breadcrumbs[] = array(
            'link' => $link_array['main'],
            'title'=> $current_lib['lib_title']
        );

        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['library_page'] = $current_lib['link_public'];
        $data['library_detail'] = $current_lib['link_public_detail'];
        $data['library_name'] = 'international_standards';
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['international_standards'] = $this->international_standards->get_standards();

        $data['markers_type'] = 'new';

        $countries = $this->international_standards->get_used_countries();
        $marker = array();

        foreach ($countries as $country_row) {
            $char = strtoupper(substr($country_row['country'], 0, 1));
            $alphabetic[$char][$country_row['id']] = $country_row;

            $country_row['company_flag'] = getCountryFlag($country_row['country']);

            $marker[] = array(
                'lat' => $country_row['country_latitude'],
                'lng' => $country_row['country_longitude'],
                'type' => 'coords',
                'main_info' => $country_row,
                'type_info' => 'international_standards',
                'title' => $country_row['country']);
        }
        ksort($alphabetic);

        $data['countries_by_char'] = $alphabetic;
        $data['no_our_contact'] = true;
        $data['myMapConfig'] = array('markers' => json_encode($marker, JSON_FORCE_OBJECT));

        $data['configs_library']= $this->lib_config->get_lib_configs();
        $data['hide_search_block']= true;

        $data['continents'] = $this->country->get_continents();
        $data['header_out_content'] = 'new/library_settings/library_international_standards/header_view';
        $data['main_content'] = 'new/library_settings/library_international_standards/index_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['footer_out_content'] = 'new/about/bottom_need_help_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function detail() {
        $this->_load_main();
        $this->load->model('International_standards_Model', 'international_standards');

        $uri = $this->uri->uri_to_assoc();
        $conditions = [];

        $conditions['id_country'] = id_from_link($uri['country']);
        $international_standards = $this->international_standards->get_standards($conditions);
        if(empty($international_standards)){
            show_404();
        }

        $data['standards'] = arrayByKey($international_standards, 'id_standard');

        checkURI($uri, ["library_international_standards", "country", "standard"]);
        if (!empty($uri["standard"])) {
            $idStandard = id_from_link($uri['standard']);
            if (!array_key_exists($idStandard, $data['standards'])) {
                show_404();
            }

            headerRedirect(__SITE_URL . "international_standards/{$uri['library_international_standards']}/country/{$uri['country']}");
        }

        sort($data['standards'], SORT_REGULAR);
        foreach($data['standards'] as $key => $standard){
            $data['standards'][$key]['standard_link'] = strtolower(str_replace(" ", "-", $standard['standard_title']));
        }

        $data['country'] = array(
            'id_country' => $international_standards[0]['standard_country'],
            'country' => $international_standards[0]['country']
        );

        $data['meta_params'] = array(
            '[COUNTRY]' => $international_standards[0]['country']
        );

        $data['list_countries'] = $this->international_standards->get_used_countries(array('exclude_countries' => $international_standards[0]['standard_country']));

        $this->breadcrumbs[] = array(
            'link' => __SITE_URL . 'library_international_standards',
            'title'	=> 'International Standards'
        );
        $this->breadcrumbs[] = array(
            'link' => __SITE_URL . 'library_international_standards/detail/country/' . strForUrl($data['country']['country'] . ' ' . $data['country']['id_country']),
            'title'	=> $data['country']['country']
        );
        $this->breadcrumbs[] = array(
            'link' => __SITE_URL . 'international_standards/detail/country/' . strForUrl($data['country']['country'] . ' ' . $data['country']['id_country']) . '/standard/' . strForUrl($international_standards[0]['standard_title'] . ' ' . $international_standards[0]['id_standard']),
            'title'	=> $international_standards[0]['standard_title']
        );

        $current_lib = $this->lib_config->get_lib_config($this->id_config);
        $data['library_head_title'] = $current_lib['lib_title'];
        $data['configs_library'] = $this->lib_config->get_lib_configs();
        $data['library_page'] = $current_lib['link_public'];
        $data['library_detail'] = $current_lib['link_public_detail'];
        $data['library_search'] = $current_lib['link_public_search'];
        $data['library_name'] = 'international_standards';
        $data['hide_search_keywords'] = true;
        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['header_out_content'] = 'new/library_settings/library_international_standards/header_view';
        $data['main_content'] = 'new/library_settings/library_international_standards/detail_view';
        $data['sidebar_right_content'] = 'new/library_settings/sidebar_view';
        $data['footer_out_content'] = 'new/about/bottom_need_help_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }
}

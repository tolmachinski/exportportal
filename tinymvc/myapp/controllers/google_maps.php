<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Google_Maps_Controller extends TinyMVC_Controller {

    function index() {
        header('location: ' . __SITE_URL);
    }

	public function get_direction(){
		$type = $this->uri->segment(3);
		$id_item = $this->uri->segment(4);

		switch($type){
			case 'b2b' :
			    $this->load->model('Country_Model', 'country');
				$this->load->model('B2b_Model', 'b2b');
				$data['request'] = $this->b2b->get_b2b_request($id_item);
				$data['request']['b2b_loc'] = $this->country->get_country_city($data['request']['id_country'],$data['request']['id_city']);
				$data['request']['company_loc'] = $this->country->get_country_city($data['request']['c_country'],$data['request']['c_city']);
				$data['request']['image'] = 'thumb_50x50_'.$data['request']['logo_company'];

				$data['request']['company_link'] = getCompanyURL($data['request']);
				$data['request']['company_flag'] = getCountryFlag($data['request']['company_loc']['country']);
				$data['request']['company_logo'] = getDisplayImageLink(array('{ID}' => $data['request']['id_company'], '{FILE_NAME}' => $data['request']['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

				$marker[] = array(
					'lat' => $data['request']['latitude'],
					'lng' => $data['request']['longitude'],
					'type' => 'coords',
					'main_info' => $data['request'],
					'type_info' => 'b2b',
					'title' => $data['request']['name_company'],
					'radius' => $data['request']['b2b_radius']
				);
			break;
		}

		$data['markers_type'] = 'new';

		$data['myMapConfig'] = array(
			'markers' => json_encode($marker, JSON_FORCE_OBJECT),
            'mapType' => 'direction'
		);

		$this->view->assign($data);
		$this->view->display('new/google_maps/map_get_direction_view');
	}
}

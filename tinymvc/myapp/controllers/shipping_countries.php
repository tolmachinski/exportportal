<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Shipping_Countries_Controller extends TinyMVC_Controller {

    function index(){
        checkIsLogged();
        checkPermision('shipper_edit_company');
        checkDomainForGroup();

        $this->load->model("Country_model", 'country');
        $this->load->model("Shipper_Countries_Model", 'shipper_countries');

        $data['countries_by_continents'] = $data['countries_by_continents_selected'] = arrayByKey($this->country->get_continents(), 'id_continent');

        $data['port_country'] = $this->country->get_countries();
        $id_user = id_session();

        //if worldwide countries
        $data['worldwide'] = $this->shipper_countries->worldwide_shipper_countries($id_user);

        if($data['worldwide']){
            foreach($data['port_country'] as $country){
                $data['countries_by_continents_selected'][$country['id_continent']]['countries'][] = $country;
                $data['array_countries_selected'][] = $country['id'];
                $data['countries_by_continents'][$country['id_continent']]['countries'][] = $country;
            }
        }else{
            foreach($data['port_country'] as $country){
                $data['countries_by_continents'][$country['id_continent']]['countries'][] = $country;
            }

            $data['shipper_countries'] = $this->shipper_countries->get_shipper_countries(array('id_user' => $id_user));

            foreach($data['shipper_countries'] as $country){
                $data['countries_by_continents_selected'][$country['id_continent']]['countries'][] = $country;
                $data['array_countries_selected'][] = $country['id'];
            }
        }

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/countries/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    function ajax_countries_operations() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('shipper_edit_company');

        $op = $this->uri->segment(3);

        switch($op) {
            case 'save_countries':
                $worldwide = arrayGet($_POST, 'worldwide', array());
                $countries = arrayGet($_POST, 'countriesSelected', array());

                if(empty($countries) && empty($worldwide)){
                    jsonResponse(translate('systmess_error_no_shipping_countries_selected'), 'warning');
                }

                $id_user = privileged_user_id();

                $user_countries = array(array('id_user' => $id_user,'id_country' => 0)); // if was selected worldwide

                if (!empty($countries)) {
                    $user_countries = array();

                    $available_countries_ids_raw = model('country')->get_countries(array('columns' => 'id'));
                    $available_country_ids = array_column($available_countries_ids_raw, 'id', 'id');

                    foreach($countries as $id_country){
                        if (!isset($available_country_ids[$id_country])) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $user_countries[] = array(
                            'id_user' => $id_user,
                            'id_country' => (int) $id_country
                        );
                    }
                }

                model('shipper_countries')->delete_shipper_countries_by_user($id_user);
                model('shipper_countries')->set_shipper_countries($user_countries);

                $this->session->select_locations = 1;

                // UPDATE PROFILE COMPLETION
                model('complete_profile')->update_user_profile_option($id_user, 'delivery_in_countries');

                /** @var TinyMVC_Library_Auth $authenticationLibrary */
                $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                $authenticationLibrary->setUserCompleteProfile($id_user);

                jsonResponse(translate('systmess_countries_saved_successfully_message'),'success');
            break;
        }
    }
}

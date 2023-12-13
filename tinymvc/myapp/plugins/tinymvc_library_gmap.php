<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring code style
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/l.-Gmap
 */
class TinyMVC_Library_Gmap{

    protected $api_url = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E';
    public $address = null;
    public $zip = null;
    public $country = null;
    public $city = null;
    public $state = null;
    public $lat = null;
    public $lng = null;
    public $find_by = 'address';
    private $formated_address = null;
    private $result = array(
                'status' => 'INVALID_REQUEST',
                'results' => null
            );

    private function _init($params = array()){
        extract($params);

        if(!empty($find_by)){
            $this->find_by = $find_by;
        }

        if(!empty($address)){
            $this->address = $address;
        }

        if(!empty($zip)){
            $this->zip = $zip;
        }

        if(!empty($country)){
            $this->country = $country;
        }

        if(!empty($city)){
            $this->city = $city;
        }

        if(!empty($state)){
            $this->state = $state;
        }

        if(!empty($lat)){
            $this->lat = $lat;
        }

        if(!empty($lng)){
            $this->lng = $lng;
        }
    }

    public function get_geocode($params = array()){
        if(!empty($params)){
            $this->_init($params);
        }

        $this->_format_address();

        $geocode_url = $this->api_url;

        if($this->formated_address != null){
            $geocode_url .= "&{$this->find_by}={$this->formated_address}";
        } else{
            $this->result['status_message'] = $this->_get_geocode_message('INVALID_REQUEST');
            return $this->result;
        }

        $results = @file_get_contents($geocode_url);
        if(empty($results)){
            $this->result['status_message'] = $this->_get_geocode_message('UNKNOWN_ERROR');
            return $this->result;
        }

        $this->result = json_decode($results, true);
        $this->result['status_message'] = $this->_get_geocode_message($this->result['status']);
        return $this->result;
    }

    private function _format_address(){
        $address_components = array();

        if($this->address != null){
            $address_components['address'] = $this->address;
        }

        if($this->city != null){
            $address_components['city'] = $this->city;
        }

        if($this->state != null){
            $address_components['state'] = $this->state;
        }

        if($this->zip != null){
            $address_components['zip'] = $this->zip;
        }

        if($this->country != null){
            $address_components['country'] = $this->country;
        }

        if($this->lat != null){
            $address_components['lat'] = $this->lat;
        }

        if($this->lng != null){
            $address_components['lng'] = $this->lng;
        }

        if(!empty($address_components)){
            $format_address = implode(', ', $address_components);
            $this->formated_address = urlencode($format_address);
        }
    }

    private function _get_geocode_message($status = 'OK'){
        $messages = array(
            'OK' => 'Success',
            'ZERO_RESULTS' => 'No results. The address does not exist. Please check the address and try again.',
            'OVER_QUERY_LIMIT' => 'We cannot check the address now, please try again late.1',
            'REQUEST_DENIED' => 'We cannot check the address now, access denied.',
            'INVALID_REQUEST' => 'We cannot check the address, some query component is missing.',
            'UNKNOWN_ERROR' => 'We cannot check the address now, please try again late.2'
        );

        return $messages[$status];
    }
}

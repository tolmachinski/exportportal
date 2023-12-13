<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Location_Controller extends TinyMVC_Controller {

    function index() {
        header('location: ' . __SITE_URL);
        exit();
    }

    public function ajax_get_states(){
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $validator_rules = array(
            array(
                'field' => 'country',
                'label' => 'Country',
                'rules' => array('required' => '','natural' => '')
            )
        );

        $this->validator->set_rules($validator_rules);
        $placeholder = cleanInput($_POST['placeholder']);

        if($placeholder == 'register' ){
            $data['placeholder_text'] = translate('register_state_region_text');
        }else{
            $data['placeholder_text'] = translate('form_placeholder_select2_state');
        }

        if(!$this->validator->validate()){
            $responce = array(
                'states' => '<option value="">'.$data['placeholder_text'].'</option>'
            );
            jsonResponse($this->validator->get_array_errors(), 'error', $responce);
        }

        $this->load->model("Country_model", 'country');
        $country = (int) $_POST['country'];
        $data['states'] = $this->country->get_states($country);
        $states_html = $this->view->fetch('new/country_states_view', $data);
        jsonResponse('', 'success', array('states' => $states_html));
    }

    public function ajax_get_regions() {
        checkIsAjax();

        $per_page = 10;
        $request = request();
        $country = (int) $request->get('country') ?: null;
        $search = cleanInput($request->get('search') ?? '');
        $page = (int) $request->get('page') ?: 1;
        $start = $per_page * ($page - 1);

        if (empty($search) || null === $country) {
            jsonResponse(null, 'success', arrayCamelizeAssocKeys(
                array('items' => array(), 'per_page' => $per_page, 'total' => 0)
            ));
        }

        $total = model(Country_Model::class)->cound_found_regions($search, $country);
        $regions = $total <= 0 ? array () : model(Country_Model::class)->find_regions($search, $country, $per_page, $start);

        jsonResponse(null, 'success', arrayCamelizeAssocKeys(
            array(
                'items'    => array_map(function (array $region) { return array('id' => $region['id'], 'name' => $region['state']); }, $regions),
                'total'    => $total,
                'per_page' => $per_page,
            )
        ));
    }

	public function ajax_get_cities() {
        checkIsAjax();

        /** @var Elasticsearch_Cities_Model $elasticsearchCitiesModel */
        $elasticsearchCitiesModel = model(Elasticsearch_Cities_Model::class);

        $request = request()->request;

        if (empty($state = $request->getInt('state')) || empty($search = cleanInput($request->get('search', '')))) {
            jsonResponse('', 'success', [
                'incomplete_results'    => false,
                'items'                 => []
            ]);
        }

        $perPage = 10;

        $foundCities = $elasticsearchCitiesModel->getCities(
            [
                'stateId'   => $state,
                'search'    => $search,
                'columns'   => [
                    'id',
                    'name',
                ],
            ],
            $request->getInt('page', 1),
            $perPage
        );

		jsonResponse('', 'success', [
            'total_count'   => $elasticsearchCitiesModel->countCities,
            'per_p'         => $perPage,
            'items'         => $foundCities
        ]);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();

        switch (uri()->segment(3)) {
            case 'get_location':
                $post = $_POST;
                $data['country'] = model('country')->fetch_port_country();

                if(isset($post['country']) && (int) $post['country']['value'] > 0){
                    $data['id_country'] = (int) $post['country']['value'];
                }

                if(isset($post['state']) && (int) $post['state']['value'] > 0){
                    $data['id_state'] = (int) $post['state']['value'];
                    $data['states'] = model('country')->get_states($data['id_country']);
                }

                if(isset($post['city']) && (int) $post['city']['value'] > 0){
                    $data['city_selected'] = model('country')->get_city((int)$post['city']['value']);
                }

                if(isset($post['postal_code_show']) && filter_var($post['postal_code_show'], FILTER_VALIDATE_BOOLEAN)){
                    $data['postal_code_show'] = true;

                    if(isset($post['postal_code']) && !empty($post['postal_code']['value'])){
                        $data['postal_code'] = cleanInput($post['postal_code']['value']);
                    }
                }

                if(isset($post['address_show']) && filter_var($post['address_show'], FILTER_VALIDATE_BOOLEAN)){
                    $data['address_show'] = true;

                    if(isset($post['address']) && !empty($post['address']['value'])){
                        $data['address'] = cleanInput($post['address']['value']);
                    }
                }

                $this->view->assign($data);
                $form = $this->view->fetch("new/location/modal_view");

                jsonResponse('', 'success', array('html' => $form));
                break;
            default:
                messageInModal(translate('systmess_error_route_not_found'));

                break;
        }
    }
}
?>

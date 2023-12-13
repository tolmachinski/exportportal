<?php
use App\Logger\MonologDBHandler;
use Monolog\Logger;
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Library_validator $validator
 */
class Api_v2_Controller extends TinyMVC_Controller
{
    private $response = array(
        200 => array(
            'status'  => 200,
            'message' => "Everything is working",
            'type'    => 'ok'
        ),
        400 => array(
            'status'  => 400,
            'message' => "Bad Request",
            'type'    => "bed_request"
        ),
        401 => array(
            'status'  => 401,
            'message' => "The request requires an user authentication",
            'type'    => "authentication"
        ),
        404 => array(
            'status'  => 404,
            'message' => "There is no resource behind the URI",
            'type'    => "no_resource_behind"
        )
    );

    private $_validation_rules_common = array(
        array(
            'field' => 'start',
            'label' => 'Start',
            'rules' => array('integer' => 'Parameter "start" should be integer'),
        ),
        array(
            'field' => 'limit',
            'label' => 'Limit',
            'rules' => array('integer' => 'Parameter "limit" should be integer')
        )
    );

    private function _check_api_key()
    {
        $token = request()->headers->get('token');// getallheaders();

        $this->load->model('Api_keys_Model', 'api_keys');

        if (empty($token) || !$this->api_keys->check_api_key($token)){
            $this->output(array(), 401);
        }
    }

    private function request_data($type = "")
    {
        switch ($type) {
            case 'POST':
                $output = $_POST;
                break;

            case 'GET':
                $output = $_GET;
                break;

            default:
                $output = $temp = file_get_contents('php://input');
                if (!empty($temp) && !is_array($temp)) {
                    parse_str($temp, $output);
                }
                break;
        }
        return $output;
    }

    private function output($additionals = array(), $status = 404)
    {
        http_response_code($status);
        header("Content-Type: application/json");

        if (empty($this->response[$status])) {
            $status = 404;
            $additionals = array();
        }

        $data = $output = array();
        if (!empty($_GET['debug_mode'])) {
            $data['request'] = $this->request_data();
        }

        if (!empty($additionals['type'])) {
            $this->response[$status]['type'] = $additionals['type'];
            unset($additionals['type']);
        }

        if (!empty($additionals['message'])) {
            $this->response[$status]['message'] = $additionals['message'];
            unset($additionals['message']);
        }

        if ($status !== 200 && empty($additionals)) {
            $output = array(
                'error' => array(
                    'type'  => $this->response[$status]['type'],
                    'reason'=> $this->response[$status]['message'],
                    'line'  => "",
                    'col'   => "",
                ),
                'status'=> $status,
            );
        }

        $data = array_merge($data, $output, $additionals);
        exit(json_encode($data));
    }

    private function log_errors($message)
    {
        $context = array(
            'REQUEST_URI' => $_SERVER['REQUEST_URI'],
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR']
        );
        $context = array_merge(getallheaders(), $context);

        $logger = new Logger('MonologDBHandler');
        $logger->pushHandler(new MonologDBHandler('api_logs', model('user')->db));
        $logger->error($message, $context);
    }

    private function output_check_for_empty($data)
    {
        if (empty($data)) {
            $this->output(array(
                'error' => 'No data on the specified parameters.',
                'status' => 200,
            ), 200);
        }
    }

    private function output_elastic_errors($result)
    {
        if (!empty($result['error'])) {

            $this->log_errors(json_encode($result['error']));

            $this->output(array(
                'error' => 'Server error, cannot get the data.',
                'status' => 404
            ), 404);
        }
        $this->output_check_for_empty($result['hits']['hits']);

    }

    private function get_from_database_output_errors($function, $conditions, $check_for_empty = true)
    {
        try{
            $data = model('api')->{$function}($conditions);
        } catch (\Exception $e) {

            $this->log_errors(json_encode($e->getMessage()));

            $this->output(array(
                'error' => 'Server error, cannot get the data.',
                'status' => 404
            ), 404);
        }

        if($check_for_empty){
            $this->output_check_for_empty($data);
        }

        return $data;
    }

    public function get_buyers()
    {
        $this->_check_api_key();

        $request = $this->request_data('GET');

        $request_data = array();
        if(config('env.APP_ENV') != 'dev'){
            $request_data['fake_user'] = 0;
        }

        //region search params
        if (!empty($request['start'])) {
            $request_data['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $request_data['limit'] = intval($request['limit']);
        }

        if (!empty($request['id_countries'])) {
            $temp_id_countries = explode(',', $request['id_countries']);
            $temp_id_countries = array_map('intval', $temp_id_countries);
            $request_data['country_list'] = array_filter($temp_id_countries);
        }
        //endregion search params

        //region validation search params
        $validation_rules = $this->_validation_rules_common;
        $validation_rules[] = array(
            'field' => 'id_countries',
            'label' => 'Id countries',
            'rules' => array('valid_ids' => 'Parameter "id_countries" should be integers delimited with comma'),
        );

        if (!empty($validation_rules)) {
            $this->validator->validate_data = $request;
            $this->validator->set_rules($validation_rules);
            if (!$this->validator->validate()) {
                $this->output(array(
                    'error'  => implode(',', $this->validator->get_array_errors()),
                    'status' => 400
                ), 400);
            }
        }
        //endregion validation search params

        $users = $this->get_from_database_output_errors('get_buyers', $request_data);
        $output['total'] = $this->get_from_database_output_errors('count_buyers', $request_data, false);

        $config_thumbs = config('img.users.main.thumbs');

        foreach ($users as $user)
        {
            $user['image'] = getUserAvatar($user['idu'], $user['user_photo'], $user["user_group"]);
            $user['user_link'] = getUserLink($user['user_name'], $user['idu'], $user['gr_type']);

            //region create thumb links
            foreach($config_thumbs as $thumb_k => $thumb_key){
                $user['thumbs'][$thumb_k]['link'] = getUserAvatar($user['idu'], $user['user_photo'], $user["user_group"], $thumb_key);
                $user['thumbs'][$thumb_k]['size'] = $thumb_key['w'] . 'x' . $thumb_key['h'];
            }
            //endregion create thumb links

            //region remove uneeded data
            $remove = ['user_group', 'user_photo', 'gr_type'];
            $user = array_diff_key($user, array_flip($remove));
            //endregion remove uneeded data

            $output['data'][] = $user;
        }

        $this->output($output, 200);
    }

    public function get_sellers()
    {
        $this->_check_api_key();

        $request = $this->request_data('GET');

        $request_data = array();

        //region search params
        if (!empty($request['start'])) {
            $request_data['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $request_data['limit'] = intval($request['limit']);
        }

        if (!empty($request['indexed_from'])) {
            try {
                $last_from = new DateTime($request['indexed_from'], new DateTimeZone('UTC'));
                $request_data['last_indexed_from'] = $last_from->format(DATE_ATOM);
            } catch (Exception $e){
                $this->output(array(
                    'error'  => 'Parameter "indexed_from" should be a valid date',
                    'status' => 400
                ), 400);
            }
        }

        if (!empty($request['indexed_to'])) {
            try{
                $last_to = new DateTime($request['indexed_to'], new DateTimeZone('UTC'));
                $request_data['last_indexed_to'] = $last_to->format(DATE_ATOM);
            }catch (Exception $e){
                $this->output(array(
                    'error'  => 'Parameter "indexed_to" should be a valid date',
                    'status' => 400
                ), 400);
            }

        }

        if (!empty($request['id_country'])) {
            $request_data['id_country'] = intval($request['id_country']);
            $request_data['term'] = "id_country";
        }
        //endregion search params

        //region validation search params
        $validation_rules = $this->_validation_rules_common;
        $validation_rules[] = array(
            'field' => 'id_country',
            'label' => 'Id country',
            'rules' => array('integer' => 'Parameter "id_country" should be integer'),
        );

        if (!empty($validation_rules)) {
            $this->validator->validate_data = $request;
            $this->validator->set_rules($validation_rules);
            if (!$this->validator->validate()) {
                $this->output(array(
                    'error'  => implode(',', $this->validator->get_array_errors()),
                    'status' => 400
                ), 400);
            }
        }
        //endregion validation search params

        $sellers = model('api')->get_from_elastic('company', $request_data);

        $this->output_elastic_errors($sellers);

        $output['total'] = $sellers['hits']['total'];
        $config_thumbs = config('img.companies.main.thumbs');

        foreach ($sellers['hits']['hits'] as $item)
        {
            $seller = $item['_source'];

            //region dynamically created data
            $seller['user_link'] = getUserLink($seller['user_name'], $seller['id_user'], 'seller');
            $seller['image'] = getDisplayImageLink(array('{ID}' => $seller['id_company'], '{FILE_NAME}' => $seller['logo_company']), 'companies.main');
            $seller['company_link'] = getCompanyURL($seller);
            $seller['thumbs'] = array();
            //endregion dynamically created data

            //region create thumb links
            foreach($config_thumbs as $thumb_k => $thumb_key){
                $seller['thumbs'][$thumb_k]['link'] = getDisplayImageLink(array('{ID}' => $seller['id_company'], '{FILE_NAME}' => $seller['logo_company']), 'companies.main', array('thumb_size' => $thumb_key));
                $seller['thumbs'][$thumb_k]['size'] = $thumb_key['w'] . 'x' . $thumb_key['h'];
            }
            //endregion create thumb links

            //region remove uneeded data
            $remove = ['type_company', 'index_name', 'gr_type', 'zip'];
            $seller = array_diff_key($seller, array_flip($remove));
            //endregion remove uneeded data

            $output['data'][] = $seller;
        }

        $this->output($output, 200);
    }

    public function get_categories()
    {
        $this->_check_api_key();

        $request = $this->request_data('GET');

        $request_data = array();

        //region search params
        if (!empty($request['start'])) {
            $request_data['start'] = intval($request['start']);
        }

        if (!empty($request['limit'])) {
            $request_data['limit'] = intval($request['limit']);
        }
        //endregion search params

        //region validation search params
        $validation_rules = $this->_validation_rules_common;

        if (!empty($validation_rules)) {
            $this->validator->validate_data = $request;
            $this->validator->set_rules($validation_rules);
            if (!$this->validator->validate()) {
                $this->output(array(
                    'error'  => implode(',', $this->validator->get_array_errors()),
                    'status' => 400
                ), 400);
            }
        }
        //endregion validation search params
        $categories = model('api')->get_from_elastic('item_category', $request_data);

        $this->output_elastic_errors($categories);

        $output = array(
            'total' => $categories['hits']['total'],
            'data'  => array_column($categories['hits']['hits'], '_source')
        );

        $this->output($output, 200);
    }
}

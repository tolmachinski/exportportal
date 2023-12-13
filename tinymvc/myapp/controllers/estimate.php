<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Notifier\SystemChannel;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Estimate_Controller extends TinyMVC_Controller
{
    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notifier = $container->get(NotifierInterface::class);
    }

    private $breadcrumbs = array();
    private $estimate_logs = array(
       'estimate_init' => 'The buyer has sent an estimate request.'
    );

    private $estimate_statuses = array(
        'new' => array(
            'icon' => 'new txt-green',
            'icon_new' => 'new-stroke',
            'title' => 'New estimates',
            'title_color' => '',
            'description' => 'Waiting for seller response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'optional' 	=> '<p>The buyer can <strong>negotiate with the seller</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'mandatory' => '<p>The seller has to <strong>answer to the buyer request</strong>, including the <strong>Price</strong> and <strong>Quantity</strong>.</p>',
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'wait_buyer' => array(
            'icon' => 'hourglass-user-left txt-blue',
            'icon_new' => 'user',
            'title' => 'Waiting for the buyer',
            'title_color' => '',
            'description' => 'Waiting for buyer response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>If the <strong>buyer agrees</strong> with the <strong>seller’s answer</strong>, the buyer has to accept it and the Estimate’s <strong>status</strong> will be changed to <strong>Accepted</strong>.</p>',
						'optional' 	=> '<p>The buyer can <strong>negotiate with the seller</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'wait_seller' => array(
            'icon' => 'hourglass-user txt-blue',
            'icon_new' => 'box fs-20',
            'title' => 'Waiting for the seller',
            'title_color' => '',
            'description' => 'Waiting for seller response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'optional' 	=> '<p>The buyer can <strong>negotiate with the seller</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'mandatory' => '<p>The seller has to <strong>answer to the buyer request</strong>, including the <strong>Price</strong> and <strong>Quantity</strong>.</p>',
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'accepted' => array(
            'icon' => 'thumbup txt-green',
            'icon_new' => 'ok-circle',
            'title' => 'Accepted',
            'title_color' => '',
            'description' => 'The estimate has been accepted.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p><strong>The buyer</strong> has to <strong>start a New Order</strong> using the <strong>Start Order</strong> button, and the Estimate’s status will be changed to <strong>Order initiated</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'mandatory' => '<p>The seller has to <strong>wait</strong> until the buyer <strong>starts the New Order</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'initiated' => array(
            'icon' => 'file-confirm txt-green',
            'icon_new' => 'file-ok',
            'title' => 'Order initiated',
            'title_color' => 'txt-green',
            'description' => 'The order has been initiated in base of this Estimate.'
        ),
        'declined' => array(
            'icon' => 'minus-circle txt-red',
            'icon_new' => 'remove-circle',
            'title' => 'Declined',
            'title_color' => 'txt-red',
            'description' => 'The Estimate has been declined.'
        ),
        'expired' => array(
            'icon' => 'hourglass-timeout txt-red',
            'icon_new' => 'hourglass',
            'title' => 'Expired',
            'title_color' => 'txt-red',
            'description' => 'The Estimate has been expired.'
        ),
        'archived' => array(
            'icon' => 'archive txt-blue',
            'icon_new' => 'folder',
            'title' => 'Archived',
            'title_color' => '',
            'description' => 'The Estimate has been archived.'
        ),
    );

    function index() {
       headerRedirect();
    }

    private function _load_main() {
        $this->load->model('Estimate_Model', 'estimate');
        $this->load->model('Items_Model', 'items');
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
    }

    public function my() {
        if (!logged_in()) {
            $this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!(have_right('manage_seller_estimate') || have_right('buy_item'))) {
            $this->session->setMessages('Error: This page does not exist.', 'errors');
            headerRedirect();
        }

        if (have_right('manage_seller_estimate') && !i_have_company()) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

		checkGroupExpire();

        $this->_load_main();

        // GET SELECTED STATUS FROM URI - IF EXIST
		$uri = $this->uri->uri_to_assoc();

        // GET SELECTED STATUS FROM URI - IF EXIST
		if(isset($uri['status'])){
        	$data['status_select'] = $uri['status'];
		}

        // ARRAY WITH FULL STATUSES DETAILS
        $data['estimate_statuses'] = $this->estimate_statuses;
        $data['status_array'] = $data['estimate_statuses'];
		$data['status_array']['estimate_number'] = array('icon' => 'magnifier', 'title' => 'Search result');
		$data['status_array']['expire_soon'] = array('icon' => 'hourglass-timeout txt-orange', 'title' => 'Expire soon');

        // IF THE STATUS WAS NOT SETTED IN THE URI - DEFAULT STATUS IS "NEW"
        if (!isset($data['status_array'][$data['status_select']])){
            $data['status_select'] = 'all';
        }

        $id_user = privileged_user_id();

        if (have_right('buy_item')) {
            $conditions = array('buyer' => $id_user);
            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_buyer'] = 1;
            }

            $count_conditions = array('id_buyer' => $id_user);
            $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
        } else {
            $conditions = array('seller' => $id_user);
            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_seller'] = 1;
            }

            $count_conditions = array('id_seller' => $id_user);
            $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
        }

        // GET SELECTED estimate NUMBER FROM URI - IF EXIST
		if(isset($uri['estimate_number'])){
        	$data['id_estimate'] = $conditions['estimate_number'] = toId($uri['estimate_number']);
			$data['keywords'] = orderNumber($data['id_estimate']);
			$data['status_select'] = 'estimate_number';
			$conditions['status'] = 'all';
		}

		if(isset($uri['expire'])){
			$data['status_select'] = 'expire_soon';
			$data['expire_days'] = $conditions['expire_soon'] = intval($uri['expire']);
			$data['keywords'] = $data['expire_days'];
			$conditions['status'] = 'all';
		}

        global $tmvc;
        $data['estimates_per_page'] = $conditions['limit'] = $tmvc->my_config['user_estimate_per_page'];

        // GET estimate DETAIL
        $data['users_estimates'] = $this->estimate->get_request_estimates($conditions);
        // GET estimate STATUSES COUNTERS
        $data['statuses'] = arrayByKey($this->estimate->count_estimates_request_by_statuses($count_conditions), 'status');

        // COUNT ARCHIVED estimate
        $archived_counters = $this->estimate->count_estimates_request_by_statuses($archived_conditions);

        // SET DEFAULT ARCHIVED COUNTER
        $data['statuses']['archived'] = array('status' => 'archived', 'counter' => 0);

        // SET ARCHIVED COUNTER NEW DATA - IF EXIST
        if (!empty($archived_counters)) {
            foreach ($archived_counters as $status_couter)
                $data['statuses']['archived']['counter'] += $status_couter['counter'];
        }

        foreach ($data['status_array'] as $key => $statuses_item){
            $data['status_array'][$key]['counter'] = (int)$data['statuses'][$key]['counter'];
        }

        if ($data['status_select'] != 'archived')
            $data['status_select_count'] = $this->estimate->counter_by_conditions_request($conditions);
        else
            $data['status_select_count'] = $data['statuses']['archived']['counter'];

        $items_id = array();
        $users_id = array();

        if (!empty($data['users_estimates'])) {
            foreach ($data['users_estimates'] as $item) {
                $items_id[$item['id_item']] = $item['id_item'];

                if (have_right('buy_item')) {
                    $users_id[$item['id_seller']] = $item['id_seller'];
                } elseif (have_right('manage_seller_estimate')) {
                    $users_id[$item['id_buyer']] = $item['id_buyer'];
                }
            }
        }

        // GET ITEMS INFO FOR ALL estimates
        if (!empty($items_id)) {
            $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $data['products_list'] = arrayByKey($data['products_list'], 'id');
        }

        // GET USERS INFO FOR ALL estimates
        if (!empty($users_id)) {
            $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
            $data['users_list'] = arrayByKey($data['users_list'], 'idu');
            if(have_right('buy_item')){
                $this->load->model('Company_Model', 'company');
                $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
            }
        }

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display("new/estimate/index_view");
        $this->view->display("new/footer_view");
    }

    public function ajax_estimate_operation() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Notify_Model', 'notify');

        $id_user = privileged_user_id();
        $op = $this->uri->segment(3);

        switch ($op) {
            // CHECK FOR NEW ESTIMATES AND RETURN COUNTER FOR ADMIN
            case 'check_new':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $lastId = $_POST['lastId'];
                $estimates_count = $this->estimate->get_count_new_estimates($lastId);

                if ($estimates_count) {
                    $last_estimates_id = $this->estimate->get_estimates_last_id();
                    jsonResponse('', 'success', array('nr_new' => $estimates_count, 'lastId' => $last_estimates_id));
                } else
                    jsonResponse('Error: New estimates do not exist');
            break;
            // ACCEPT ESTIMATE BY THE BUYER
            case 'confirm_estimate':
                if (!have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_estimate = intVal($_POST['estimate']);
                $estimate_request_info = $this->estimate->get_request_estimate($id_estimate);

                if (!is_my($estimate_request_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if ($estimate_request_info['status'] != 'wait_buyer')
                    jsonResponse(translate('systmess_error_confirm_estimate_wrong_status'), 'warning');

                $log = array(
                    'poster' => 'Buyer',
                    'date' => date('m/d/Y H:i:s'),
                    'message' => 'The estimate was confirmed by the buyer.'
                );
                $update_estimate = array('status' => 'accepted', 'log' => $estimate_request_info['log'] . ',' . json_encode($log));
                $this->estimate->update_request_estimate($estimate_request_info['id_request_estimate'], $update_estimate);

                $seller_info = $this->user->getSimpleUser($estimate_request_info['id_seller'], 'users.idu, users.email');

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array(
                    $estimate_request_info['id_seller'] => array('estimates_accepted' => 1),
                    $estimate_request_info['id_buyer'] => array('estimates_accepted' => 1)
                ));

                $estimate_number = orderNumber($id_estimate);

				$data_systmess = [
					'mess_code'     => 'estimate_approved_buyer',
					'id_users'      => [$estimate_request_info['id_seller']],
					'replace'       => [
						'[ESTIMATE_ID]'   => $estimate_number,
						'[ESTIMATE_LINK]' => __SITE_URL . 'estimate/my/estimate_number/' . $id_estimate,
						'[LINK]'          => __SITE_URL . 'estimate/my'
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_confirm_estimate'), 'success', array('new_status' => 'accepted'));
            break;
            // IF ESTIMATE HAS BEEN ACCEPTED BY THE BUYER - SELLER INITIATE THE ORDER
            case 'create_order':
                checkPermisionAjax('buy_item');

                $this->load->model('Item_Snapshot_Model', 'snapshot');
                $this->load->model('Orders_model', 'orders');
                $this->load->model('Country_Model', 'country');

                // VALIDATE POST DATA
                $validator_rules = array(
                    array(
                        'field' => 'id_estimate',
                        'label' => 'Estimate detail',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'port_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'port_city',
                        'label' => 'City',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'zip',
                        'label' => 'ZIP',
						'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_estimate = intVal($_POST['id_estimate']);
                $estimate_info = $this->estimate->get_request_estimate($id_estimate);

                if (empty($estimate_info)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #4');
                }

                if (!is_my($estimate_info['id_buyer'])){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #5');
                }

                if ($estimate_info['status'] != 'accepted'){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #6');
                }

                $item = $this->items->get_item($estimate_info['id_item']);
                if(empty($item)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #7');
                }

                // CHECK IF REQUESTED QUANTITY IS NOT GREATHER THAN DISPONIBLE
                if ($item['quantity'] < $estimate_info['quantity']){
                    jsonResponse(translate('systmess_error_estimate_create_order_necessary_quantity_not_available'));
                }

                $snapshot = $this->snapshot->get_last_item_snapshot($estimate_info['id_item']);
                if(empty($snapshot)){
                    jsonResponse(translate('systmess_error_estimate_create_order_no_snapshot'));
                }

                $new_status_info = $this->orders->get_status_by_alias('new_order');
				if(empty($new_status_info)){
					jsonResponse(translate('systmess_error_invalid_data') . 'Code #8');
				}

                $estimate_number = orderNumber($id_estimate);
                // GENERATE FIRST ORDER LOG
                $order_log = array(
                    'date' => date('m/d/Y H:i:s'),
                    'user' => 'Buyer',
                    'message' => "The order has been initiated in base of Estimate: {$estimate_number}."
                );

                // PREPARING SHIPPING TO LOCATION ADDRESS
                $ship_to_country = (int) $_POST['port_country'];
				$ship_to_state = (int) $_POST['states'];
				$ship_to_city = (int) $_POST['port_city'];
				$ship_to_zip = cleanInput($_POST['zip']);
				$ship_to_address = cleanInput($_POST['address']);
				$location = model('country')->get_country_state_city($ship_to_city);
				$location['zip'] = $ship_to_zip;
				$location['address'] = $ship_to_address;
				$ship_to = implode(', ', array_filter($location));

                $total_order_weight = $estimate_info['quantity'] * $item['weight'];
				$purchase_order = array(
					'shipping_to' => array(
						'country' => $ship_to_country,
						'state' => $ship_to_state,
						'city' => $ship_to_city,
						'zip' => $ship_to_zip,
						'address' => $ship_to_address,
						'full_address' => $ship_to
					),
                    'products_weight' => $total_order_weight
				);

                $order = array(
                    'id_buyer' => $estimate_info['id_buyer'],
                    'id_seller' => $estimate_info['id_seller'],
                    'price' => $estimate_info['price']*$estimate_info['quantity'],
                    'final_price' => $estimate_info['price']*$estimate_info['quantity'],
                    'weight' => $total_order_weight,
                    'comment' => "The ordered item has been sold by the Estimate {$estimate_number}.",
                    'order_summary' => json_encode($order_log),
                    'ship_to_country' => $ship_to_country,
					'ship_to_state' => $ship_to_state,
					'ship_to_city' => $ship_to_city,
					'ship_to_zip' => $ship_to_zip,
					'ship_to_address' => $ship_to_address,
                    'ship_to' => $ship_to,
                    'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                    'purchase_order' => json_encode($purchase_order),
					'purchase_order_timeline' => json_encode(array(array(
                        'date' => date('Y-m-d H:i:s'),
                        'user' => 'Buyer',
                        'message' => "The order has been initiated in base of Estimate: {$estimate_number}."
                    )))
                );

                $id_order = $this->orders->insert_order($order);
                if (!$id_order){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $order_number = orderNumber($id_order);

                // PREPARE SEARCH INFO
                $users = $this->user->getSimpleUsers(implode(',', array($estimate_info['id_buyer'], $estimate_info['id_seller'])), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                $this->load->model('Company_Model', 'company');
                $company = $this->company->get_seller_base_company($estimate_info['id_seller'], 'cb.name_company');
                $search_info = $order_number . ', ' . $users[0]['username'] . ', ' . $users[1]['username'] .', '. $company['name_company'] .', '.$item['title'];

                // UPDATE SEARCH INFO - ADD ORDER ID
                $this->orders->change_order($id_order, array('search_info' => $search_info));

                $ordered_item = array(
                    'id_order' => $id_order,
                    'id_item' => $estimate_info['id_item'],
                    'id_snapshot' => $snapshot['id_snapshot'],
                    'price_ordered' => $estimate_info['price'],
                    'quantity_ordered' => $estimate_info['quantity'],
                    'weight_ordered' => $item['weight'],
                    'detail_ordered' => 'The item has been sold by the estimate ' . orderNumber($id_estimate),
                );
                $this->orders->set_ordered_item($ordered_item);

                $date_order = date('Y-m-d H:i:s');

                $this->notifier->send(
                    (new SystemNotification('order_created', [
						'[ORDER_ID]'   => $order_number,
						'[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
						'[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $estimate_info['id_buyer'],
                        $estimate_info['id_seller'],
                    ])
                );

                $log = array(
                    'poster' => 'Buyer',
                    'date' => date('m/d/Y H:i:s'),
                    'message' => 'The order '.$order_number.' has been initiated.'
                );
                $update_estimate = array('status' => 'initiated', 'log' => $estimate_info['log'] . ',' . json_encode($log));
                $this->estimate->update_request_estimate($id_estimate, $update_estimate);

                // CHANGE USER STATISTIC
                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array(
                    $estimate_info['id_seller'] => array('estimates_finished' => 1, 'estimates_accepted' => -1),
                    $estimate_info['id_buyer'] => array('estimates_finished' => 1, 'estimates_accepted' => -1)
                ));

                // CHANGE ITEM QUANTITY
                $this->items->update_item(array('id' => $estimate_info['id_item'],'quantity' => ($item['quantity'] - $estimate_info['quantity'])));

                jsonResponse(translate('systmess_success_estimate_create_order', ['{ORDER_NUMBER}' => orderNumber($id_order)]), 'success', array('order' => $id_order));

            break;
            // REMOVE THE ESTIMATE REQUEST
            // ONLY IF ESTIMATE HAS BEEN:
            //          1. DECLINED;
            //          2. ARCHIVED;
            //          3. THE ORDER HAS BEEN INITIATED.
            case 'remove_estimate':
                if(!have_right('manage_seller_estimate') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_request_estimate = intVal($_POST['estimate']);
                $estimate_info = $this->estimate->get_request_estimate($id_request_estimate);

                if (empty($estimate_info))
                    jsonResponse('Error: This estimate does not exist.');

                if (!is_privileged('user', $estimate_info['id_seller'], true) && !is_my($estimate_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if (have_right('manage_seller_estimate')) {
                    $state_user = 'state_seller';
                } elseif (have_right('buy_item')) {
                    $state_user = 'state_buyer';
                }
                $remove_in_status = array('initiated','declined');

                if(!$estimate_info[$state_user] == 1 && !in_array($estimate_info['status'], $remove_in_status))
                    jsonResponse('Error: The estimate cannot be deleted now. Please try again late.');

                $update_estimate = array($state_user => 2);
                $this->estimate->update_request_estimate($estimate_info['id_request_estimate'], $update_estimate);

                $status = 'declined';
                if($estimate_info['status'] == 'initiated')
                    $status = 'finished';

                $statistic = array();
                if(have_right('buy_item')) {
                    $statistic[$id_user] = array('estimates_sent' => -1, 'estimates_' . $status => -1);
                } elseif(have_right('manage_seller_estimate')){
                    $statistic[$id_user] = array('estimates_received' => -1, 'estimates_' . $status => -1);
                }

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic($statistic);

                jsonResponse('The estimate has been deleted.', 'success', array('new_status' => 'new'));
            break;
            // IF THE ORDER HAS BEEN INITIATED, THE USERS CAN ARCHIVE THE ESTIMATE
            case 'archived_estimate':
                if (!have_right('manage_seller_estimate') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_request_estimate = intVal($_POST['estimate']);
                $estimate_info = $this->estimate->get_request_estimate($id_request_estimate);

                if (empty($estimate_info))
                    jsonResponse('Error: This estimate does not exist.');

                if (!is_privileged('user', $estimate_info['id_seller'], true) && !is_my($estimate_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if ($estimate_info['status'] != 'initiated')
                    jsonResponse('Error: This estimate has not been finished.');

                if (have_right('manage_seller_estimate')) {
                    if ($estimate_info['state_seller'] == 1)
                        jsonResponse('Error: The estimate status already "Archived".');

                    $archived_status = array('state_seller' => 1);
                }elseif (have_right('buy_item')) {
                    if ($estimate_info['state_buyer'] == 1)
                        jsonResponse('Error: The estimate status already "Archived".');

                    $archived_status = array('state_buyer' => 1);
                }

                if ($this->estimate->update_request_estimate($estimate_info['id_request_estimate'], $archived_status)) {
                    jsonResponse('The estimate status has been successfully changed.', 'success', array('new_status' => 'archived'));
                } else {
                    jsonResponse('Error: The estimate status has not been changed. Please try again later.');
                }
            break;
            // IF THE ESTIMATE STATUS IS NEW OR WAIT_USER, THE USER CAN DECLINE IT
            case 'declined_estimate':
                if (!have_right('manage_seller_estimate') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_estimate = intVal($_POST['estimate']);
                $estimate_info = $this->estimate->get_request_estimate($id_estimate);

                if (empty($estimate_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                if (!is_privileged('user', $estimate_info['id_seller'], true) && !is_my($estimate_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if (have_right('manage_seller_estimate')) {
                    $decline_in_status = array('new', 'wait_seller');
                } elseif (have_right('buy_item')) {
                    $decline_in_status = array('new', 'wait_buyer');
                }

                if (!in_array($estimate_info['status'], $decline_in_status))
                    jsonResponse(translate('systmess_error_decline_estimate_wrong_status'));

                if (have_right('manage_seller_estimate')) {
                    $id_user_send = $estimate_info['id_buyer'];
                    $user_declined = 'Seller';
                } elseif (have_right('buy_item')) {
                    $id_user_send = $estimate_info['id_seller'];
                    $user_declined = 'Buyer';
                }

                $this->estimate->update_request_estimate($id_estimate, array('status' => 'declined'));

                $this->load->model('User_Statistic_Model', 'statistic');
                $users_statistic = array(
                    $estimate_info['id_seller'] => array('estimates_declined' => 1),
                    $estimate_info['id_buyer'] => array('estimates_declined' => 1)
                );
                $this->statistic->set_users_statistic($users_statistic);

                $user_info = $this->user->getSimpleUser($id_user_send, 'users.idu, users.email');

				$data_systmess = [
					'mess_code' => 'estimate_declined',
					'id_item'   => $id_estimate,
					'id_users'  => [$id_user_send],
					'replace'   => [
						'[ESTIMATE_ID]'   => orderNumber($id_estimate),
						'[ESTIMATE_LINK]' => __SITE_URL . 'estimate/my/estimate_number/' . $id_estimate,
						'[LINK]'          => __SITE_URL . 'estimate/my',
						'[ITEM]'          => cleanOutput($estimate_info['title']),
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_decline_estimate'), 'success', array('new_status' => 'declined'));
            break;
            // ESTIMATES USERS DISCUSIONS
            case 'resend_estimate':
                if (!(have_right('manage_seller_estimate') || have_right('buy_item')))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                    'field' => 'message',
                    'label' => 'Message',
                    'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                    'field' => 'estimate',
                    'label' => 'Estimate information',
                    'rules' => array('required' => '', 'integer' => '')
                    )
                );

                if(have_right('manage_seller_estimate')){
                    $validator_rules[] = array(
                        'field' => 'price',
                        'label' => 'Price',
                        'rules' => array('required' => '', 'positive_number' => '', 'min[0.01]' => '')
                    );
                    $validator_rules[] = array(
                        'field' => 'quantity',
                        'label' => 'Quantity',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '')
                    );
                }

                $this->validator->set_rules($validator_rules);
                if (!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_estimate = intVal($_POST['estimate']);
                $estimate_info = $this->estimate->get_request_estimate($id_estimate);

                if (empty($estimate_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                if (!is_privileged('user', $estimate_info['id_seller'], true) && !is_my($estimate_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if (have_right('manage_seller_estimate')) {
                    $archived_status = $estimate_info['state_seller'];
                } elseif (have_right('buy_item')) {
                    $archived_status = $estimate_info['state_buyer'];
                }

                if (!in_array($estimate_info['status'], array('wait_seller', 'wait_buyer', 'new')) && !$archived_status)
                    jsonResponse(translate('systmess_error_resend_completed_estimate'), 'info');


                $user_message = cleanInput($_POST['message']);
                $log = array(
                    "date" => date('m/d/Y H:i:s'),
                    "message" => $user_message,
                    "message" => iconv(mb_detect_encoding($user_message, mb_detect_order(), true), "UTF-8", $user_message),
                );

                if(have_right('manage_seller_estimate')){
                    $estimate_changes = array(
                        'status' => 'wait_buyer',
                        'price' => floatVal($_POST['price']),
                        'quantity' => intVal($_POST['quantity'])
                    );

                    $id_user_send = $estimate_info['id_buyer'];
                    $log['poster'] = 'Seller';
                    $log['price'] = floatVal($_POST['price']);
                    $log['quantity'] = intVal($_POST['quantity']);
                } elseif(have_right('buy_item') && in_array($estimate_info['status'], array('wait_seller', 'wait_buyer', 'new'))) {
                    $estimate_changes = array('status' => 'wait_seller');
                    $id_user_send = $estimate_info['id_seller'];
                    $log['poster'] = 'Buyer';
                }

                $user_info = $this->user->getSimpleUser($id_user_send, 'users.fname, users.lname, users.email');
                if ($this->estimate->update_request_estimate($id_estimate, $estimate_changes)) {
                    $this->estimate->change_request_estimate_log($id_estimate, json_encode($log));


                    $data_systmess = [
                        'mess_code' => 'estimate_message_sent',
                        'id_item'   => $id_estimate,
                        'id_users'  => [$id_user_send],
                        'replace'   => [
                            '[ESTIMATE_ID]'   => orderNumber($id_estimate),
                            '[ESTIMATE_LINK]' => __SITE_URL . 'estimate/my/estimate_number/' . $id_estimate,
                            '[LINK]'          => __SITE_URL . 'estimate/my'
                        ],
                        'systmess' => true,
                    ];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_resend_estimate'), 'success', array('new_status' => $estimate_changes['status']));
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // CREATE THE ESTIMATE REQUEST - ONLY BUYER
            case 'create_estimate':
                if (!have_right('buy_item')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $this->validator->set_rules([
                    [
                        'field' => 'quantity',
                        'label' => 'Quantity',
                        'rules' => ['required' => '', 'integer' => '', 'min[1]' => '']
                    ],
                    [
                        'field' => 'days',
                        'label' => 'Estimate expires in',
                        'rules' => ['required' => '', 'integer' => '', 'min[1]' => '', 'max[14]' => '']
                    ],
                    [
                        'field' => 'comment',
                        'label' => 'Comment',
                        'rules' => ['required' => '', 'max_len[1000]' => '']
                    ]
                ]);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $request = request()->request;

                if (
                    empty($itemId = $request->getInt('item'))
                    || empty($item = $productsModel->findOne($itemId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$item['estimate']) {
                    jsonResponse(translate('systmess_error_item_not_disponible_for_estimate'));
                }

                if ($item['is_out_of_stock']) {
                    jsonResponse(translate('translations_out_of_stock_system_message'));
                }

                $details = [];

                if ($item['has_variants']) {
                    /** @var Items_Variants_Model $itemVariantsModel */
                    $itemVariantsModel = model(Items_Variants_Model::class);

                    if (empty($variant = (array) $request->get('variant'))) {
                        jsonResponse(translate('systmess_info_fill_all_specific_item_options'));
                    }

                    if (empty($variant['id']) || empty($variant['options'])) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    if (empty($itemVariant = $itemVariantsModel->findOneBy([
                        'conditions'    => [
                            'itemId'    => $itemId,
                            'id'        => (int) $variant['id'],
                        ],
                        'with'  => [
                            'propertyOptions',
                        ],
                    ]))) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $allVariantOptions = array_column($itemVariant['property_options']->toArray(), null, 'id');

                    foreach ((array) $variant['options'] as $optionId) {
                        if (!isset($allVariantOptions[$optionId])) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $details[] = $allVariantOptions[$optionId]['propertyName'] . ' : ' . $allVariantOptions[$optionId]['name'];
                    }
                }

                $now = new \DateTimeImmutable();
                $estimateComment = $request->get('comment');
                $countDays = $request->getInt('days');
                $expireDate = date_plus($countDays, 'days', $now->format('Y-m-d H:i:s'), true);

                /** @var Product_Estimate_Requests_Model $productEstimateRequestsModel */
                $productEstimateRequestsModel = model(Product_Estimate_Requests_Model::class);

                $estimateId = $productEstimateRequestsModel->insertOne([
                    'id_item'       => $itemId,
                    'quantity'      => $request->getInt('quantity'),
                    'id_seller'     => $item['id_seller'],
                    'id_buyer'      => $id_user,
					'detail_item'   => implode(', ', $details),
                    'days'          => $countDays,
                    'log'           => [
                        'date'      => $now->format('m/d/Y H:i:s'),
                        'message'   => $this->estimate_logs['estimate_init'] . '<br>' . $estimateComment,
                        'poster'    => 'Buyer'
                    ],
                    'expire_date' => (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $expireDate)
                ]);

                if (empty($estimateId)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $this->load->model('Item_Snapshot_Model', 'item_snapshot');
                $this->load->model('User_Statistic_Model', 'statistic');
                $users_statistic = array(
                    $item['id_seller'] => array('estimates_received' => 1),
                    $id_user => array('estimates_sent' => 1)
                );
                $this->statistic->set_users_statistic($users_statistic);

                // GET USERS INFO FOR ALL estimateS
                $users_id = array($item['id_seller'], $id_user);
                $users_info = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                $users_info = arrayByKey($users_info, 'idu');
                $this->load->model('Company_Model', 'company');
                $company_info = $this->company->get_seller_base_company($item['id_seller'], "cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company");

                $item_info = $this->item_snapshot->get_last_item_snapshot($itemId);

                $productEstimateRequestsModel->updateOne(
                    $estimateId,
                    [
                        'for_search' => implode(', ', [
                            orderNumber($estimateId),
                            $item_info['title'],
                            $users_info[$id_user]['username'],
                            $users_info[$item['id_seller']]['username'],
                            $company_info['name_company'],
                        ])
                    ]
                );

                $this->notify->send_notify([
                    'mess_code' => 'estimate_new',
                    'id_item'   => $estimateId,
                    'id_users'  => [$item['id_seller'], $id_user],
                    'replace'   => [
                        '[ESTIMATE_ID]'   => orderNumber($estimateId),
                        '[ESTIMATE_LINK]' => __SITE_URL . 'estimate/my/estimate_number/' . $estimateId,
                        '[LINK]'          => __SITE_URL . 'estimate/my',
                        '[ITEM]'          => cleanOutput($item_info['title']),
                        '[USER_NAME]'     => cleanOutput(user_name_session()),
                        '[USER_LINK]'     => __SITE_URL . getMyProfileLink()
                    ],
                    'systmess' => true
                ]);

                jsonResponse(translate('systmess_success_send_estimate_request'), 'success');
            break;
        }
    }

    public function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $this->_load_main();
        $data['errors'] = array();
        $id_user = privileged_user_id();

        $op = $this->uri->segment(3);
        switch ($op) {
            // ADD ESTIMATE FORM
            case 'add_estimate_form':
                checkPermisionAjaxModal('buy_item');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = (int) uri()->segment(4))
                    || empty($item = $productsModel->findOne($itemId, ['with' => ['productUnitType']]))
                ) {
                    messageInModal(translate('systmess_error_item_does_not_exist'));
                }

                if (!$item['estimate']) {
                    messageInModal(translate('systmess_error_item_not_disponible_for_estimate'));
                }

                if ($item['is_out_of_stock']) {
                    messageInModal(translate('translations_out_of_stock_system_message'), 'info');
                }

                $quantityInStock = $item['quantity'];

                if ($item['has_variants']) {
                    /** @var Items_Variants_Model $itemVariantsModel */
                    $itemVariantsModel = model(Items_Variants_Model::class);

                    if (empty($variant = (array) request()->request->get('variant'))) {
                        messageInModal(translate('systmess_info_fill_all_specific_item_options'));
                    }

                    if (empty($variant['id']) || empty($variant['options'])) {
                        messageInModal(translate('systmess_error_invalid_data'));
                    }

                    if (empty($itemVariant = $itemVariantsModel->findOneBy([
                        'conditions'    => [
                            'itemId'    => $itemId,
                            'id'        => (int) $variant['id'],
                        ],
                        'with'  => [
                            'propertyOptions',
                        ],
                    ]))) {
                        messageInModal(translate('systmess_info_select_available_variation_item'));
                    }

                    $allVariantOptions = array_column($itemVariant['property_options']->toArray(), null, 'id');
                    $usedVariantOptions = [];

                    foreach ((array) $variant['options'] as $optionId) {
                        if (!isset($allVariantOptions[$optionId])) {
                            messageInModal(translate('systmess_error_invalid_data'));
                        }

                        $usedVariantOptions[] = $allVariantOptions[$optionId];
                    }

                    $quantityInStock = $itemVariant['quantity'];
                }

                if ($quantityInStock < $item['min_sale_q']) {
                    messageInModal(translate('translations_out_of_stock_system_message'), 'info');
                }

                views(
                    'new/estimate/item_estimate_form_view',
                    [
                        'availableQuantity' => $quantityInStock,
                        'variantOptions'    => $usedVariantOptions ?? null,
                        'sold_counter'      => $this->items->soldCounter($itemId),
                        'itemVariant'       => $itemVariant ?? null,
                        'photo'             => $this->items->get_items_photo($itemId, 1),
                        'item'              => $item,
                    ]
                );
            break;
            // SEND ESTIMATE MESSAGE
            case 'resend_estimate':
                if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"));

                if (!(have_right('manage_seller_estimate') || have_right('buy_item')))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $id_estimate = intVal($this->uri->segment(4));
                $data['estimate'] = $this->estimate->get_request_estimate($id_estimate);

                if (empty($data['estimate']))
                    messageInModal(translate('systmess_error_invalid_data'));

                if (!is_privileged('user', $data['estimate']['id_seller'], true) && !is_my($data['estimate']['id_buyer'])) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                if (have_right('manage_seller_estimate')) {
                    $user_status = $data['estimate']['state_seller'];
                } elseif (have_right('buy_item')) {
                    $user_status = $data['estimate']['state_buyer'];
                }

                if (!in_array($data['estimate']['status'], array('wait_seller', 'wait_buyer', 'new')) || $user_status) {
                    messageInModal(translate('systmess_error_resend_completed_estimate'), 'info');
                }

                $this->view->assign($data);

                $this->view->display('new/estimate/resend_estimate_form_view');
            break;
            // ADD SHIP-TO ADDRESS AND CREATE THE ORDER
            case 'ship_to':
                // CHECK USER FOR BUYER RIGHTS
                if (!have_right('buy_item'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // LOAD ADDITIONAL MODELS - COUNTRY_MODEL
                $this->load->model('Country_Model', 'country');

                // GET ESTIMATE ID FROM URI SEGMENT
                $data['id_estimate'] = (int) $this->uri->segment(4);

                // GET ESTIMATE DETAIL
                $estimate_info = $this->estimate->get_request_estimate($data['id_estimate']);

                // CHECK IF EXIST ESTIMATE
                if (empty($estimate_info))
                    messageInModal(translate("systmess_error_request_does_not_exist"));

                if (!is_my($estimate_info['id_buyer']))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // CHECK ESTIMATE STATUS - MUST BE "ACCEPTED"
                if ($estimate_info['status'] != 'accepted')
                    messageInModal(translate('systmess_error_estimate_shipping_to_address'), 'info');

                // GET ADDITIONAL USER DATA
                $data['user_info'] = $this->user->getSimpleUser($id_user);

                // GET COUNTRIES LIST
                $data['port_country'] = $this->country->fetch_port_country();

                if ($data['user_info']['country'])
                    $data['states'] = $this->country->get_states($data['user_info']['country']);

				$data['city_selected'] = $this->country->get_city($data['user_info']['city']);

                $this->view->display('new/estimate/ship_view', $data);
            break;
        }
    }

    function ajax_estimate_info() {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();
        $id_user = $this->session->id;

        switch ($_POST['type']) {
            // SEARCH ESTIMATE
            case 'search_estimates':
                if(!have_right('manage_seller_estimate') && !have_right('buy_item')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $keywords = cleanInput(cut_str($_POST['keywords']));
                if ($keywords == '')
                    jsonResponse('Error: Search keywords is required.');

                global $tmvc;
                $per_page = $tmvc->my_config['user_estimate_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

				$search_filter = cleanInput($_POST['search_filter']);
				if (!empty($search_filter)) {
					switch($search_filter){
						case 'estimate_number' :
							$conditions = array('estimate_number' => toId($keywords));
						break;
						case 'expire_soon' :
							$conditions = array('expire_soon' => intval($keywords));
						break;
						case 'archived' :
							$conditions = array('keywords' => $keywords);
						break;
						default:
							$conditions = array('status' => $search_filter, 'keywords' => $keywords);
						break;
					}
				} else{
					$conditions = array('keywords' => $keywords);
				}
                if (have_right('buy_item')) {
                    $conditions['buyer'] = $id_user;
					if($search_filter == 'archived'){
						$conditions['state_buyer'] = 1;
					}
                }else {
                    $conditions['seller'] = $id_user;
					if($search_filter == 'archived'){
						$conditions['state_seller'] = 1;
					}
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $data['users_estimates'] = $this->estimate->get_request_estimates($conditions);
                $total_estimates_by_status = $this->estimate->counter_by_conditions_request($conditions);

                if (empty($data['users_estimates'])) {
                    jsonResponse('0 estimates found by this search.', 'info', array('total_estimates_by_status' => 0));
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_estimates'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];
                    if(have_right('buy_item'))
                        $users_id[$item['id_seller']] = $item['id_seller'];

                    if(have_right('manage_seller_estimate'))
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                // GET USERS INFO FOR ALL estimates
                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->estimate_statuses;

                $estimates_list = $this->view->fetch('new/estimate/estimate_list_view', $data);

                jsonResponse('', 'success', array('estimates_list' => $estimates_list, 'total_estimates_by_status' => $total_estimates_by_status, 'status' => $search_filter));
            break;
            // ESTIMATES DETAILS
            case 'estimate':
                checkPermisionAjax('manage_seller_estimate, buy_item');

                $id_estimate = (int) $_POST['estimate'];
                $data['estimate'] = $this->estimate->get_request_estimate($id_estimate);

                if (empty($data['estimate']) || !in_array(privileged_user_id(), array((int) $data['estimate']['id_seller'], (int) $data['estimate']['id_buyer']))){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $data['estimate']['log'] = array_reverse(with(json_decode("[{$data['estimate']['log']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));

                // CALCULATE EXPIRE TIME IN MILISECONDS FOR COUNTDOWN TIMER
                $expire = (strtotime($data['estimate']['expire_date']) - time()) * 1000;

                //region user information for tablet/mobile
                if(have_right('buy_item')){
                    $data['seller_info'] = model('company')->get_seller_base_company(
                        (int) $data['estimate']['id_seller'],
                        "cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company, u.user_group",
                        true
                    );
                } else{
                    $data['buyer_info'] = $this->user->getSimpleUser((int) $data['estimate']['id_buyer'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name, users.user_group, users.user_photo");
                }
                //endregion user information for tablet/mobile

                $data['estimate_status'] = $this->estimate_statuses[$data['estimate']['status']];
                $data['estimate_status_user'] = have_right('buy_item') ? 'buyer' : 'seller';

                if(have_right('buy_item')){
					$btnChatSeller = new ChatButton(['recipient' => $data['estimate']['id_seller'], 'recipientStatus' => 'active', 'module' => 5, 'item' => $data['estimate']['id_request_estimate']], ['text' => 'Chat with seller']);
					$data['btnChatSeller'] = $btnChatSeller->button();

					$btnChatSeller2 = new ChatButton(['recipient' => $data['estimate']['id_seller'], 'recipientStatus' => 'active', 'module' => 5, 'item' => $data['estimate']['id_request_estimate']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with seller']);
					$data['btnChatSeller2'] = $btnChatSeller2->button();
				}else{
					$btnChatBuyer = new ChatButton(['recipient' => $data['estimate']['id_buyer'], 'recipientStatus' => 'active', 'module' => 5, 'item' => $data['estimate']['id_request_estimate']], ['text' => 'Chat with buyer']);
					$data['btnChatBuyer'] = $btnChatBuyer->button();

					$btnChatBuyer2 = new ChatButton(['recipient' => $data['estimate']['id_buyer'], 'recipientStatus' => 'active', 'module' => 5, 'item' => $data['estimate']['id_request_estimate']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with buyer']);
					$data['btnChatBuyer2'] = $btnChatBuyer2->button();
				}

                $content = $this->view->fetch('new/estimate/estimate_detail_view', $data);

                jsonResponse('', 'success', array('expire' => $expire, 'content' => $content));
            break;
            // ESTIMATES LIST
            case 'estimate_list':
                if (!(have_right('manage_seller_estimate') || have_right('buy_item')))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $statuses = array('all', 'new', 'wait_buyer', 'wait_seller', 'accepted', 'declined', 'expired', 'initiated', 'archived');
                $status = cleanInput($_POST['status']);
				if (!in_array($status, $statuses)) {
                    jsonResponse('Error: The status you selected is not correct.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_estimate_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                if (have_right('buy_item')) {
                    $conditions = array('buyer' => $id_user);
                    if ($status != 'archived') {
                        $conditions['status'] = $status;
                    } else {
                        $conditions['state_buyer'] = 1;
                    }
                } else {
                    $conditions = array('seller' => $id_user);
                    if ($status != 'archived') {
                        $conditions['status'] = $status;
                    } else {
                        $conditions['state_seller'] = 1;
                    }
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $data['users_estimates'] = $this->estimate->get_request_estimates($conditions);
                $total_estimates_by_status = $this->estimate->counter_by_conditions_request($conditions);

                if (empty($data['users_estimates'])) {
                    jsonResponse('0 estimates found by this search.', 'info', array('total_estimates_by_status' => 0));
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_estimates'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];
                    if(have_right('buy_item'))
                        $users_id[$item['id_seller']] = $item['id_seller'];

                    if(have_right('manage_seller_estimate'))
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                // GET USERS INFO FOR ALL estimates
                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->estimate_statuses;

                $estimates_list = $this->view->fetch('new/estimate/estimate_list_view', $data);

                jsonResponse('', 'success', array('estimates_list' => $estimates_list, 'total_estimates_by_status' => $total_estimates_by_status));
            break;
            // UPDATE ESTIMATES COUNTERS BY STATUSES
            case 'update_sidebar_counters':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('manage_seller_estimate') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // PREPARING CONDITIONS
                if (have_right('buy_item')) {
                    $count_conditions = array('id_buyer' => $id_user);
                    $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
                } else {
                    $count_conditions = array('id_seller' => $id_user);
                    $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
                }

                // GET COUNTERS
                $statuses_counters = arrayByKey($this->estimate->count_estimates_request_by_statuses($count_conditions), 'status');
                $archived_counters = $this->estimate->count_estimates_request_by_statuses($archived_conditions);
                $statuses_counters['archived'] = array('status' => 'archived', 'counter' => 0);
                if (!empty($archived_counters)) {
                    foreach ($archived_counters as $status_couter)
                        $statuses_counters['archived']['counter'] += $status_couter['counter'];
                }

                // RETURN THE RESPONCE
                jsonResponse('', 'success', array('counters' => $statuses_counters));
            break;
        }
    }

    public function administration() {
        checkAdmin('manage_content');

        $this->_load_main();

        $data['statuses'] = arrayByKey($this->estimate->count_estimates_request_by_statuses(), 'status');
        $data['last_estimates_id'] = $this->estimate->get_estimates_request_last_id();

        $this->view->assign($data);
        $this->view->assign('title', 'Estimates');
        $this->view->display('admin/header_view');
        $this->view->display('admin/estimate/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_estimate_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_request_estimate' => 'e.id_request_estimate',
                'dt_status'              => 'e.status',
                'dt_quantity'            => 'e.quantity',
                'dt_price'               => 'e.price',
                'dt_date_created'        => 'e.create_date',
                'dt_date_changed'        => 'e.update_date'
            ])
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'status', 'key' => 'status', 'type' => 'cleanInput'],
            ['as' => 'seller', 'key' => 'seller', 'type' => 'cleanInput'],
            ['as' => 'buyer', 'key' => 'buyer', 'type' => 'cleanInput'],
            ['as' => 'item', 'key' => 'item', 'type' => 'cleanInput'],
            ['as' => 'start_from',  'key' => 'start_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',  'key' => 'start_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_from',  'key' => 'update_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_to',  'key' => 'update_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["e.id_request_estimate-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $conditions);

        $estimates = $this->estimate->get_request_estimates($params);
        $estimates_count = $this->estimate->counter_by_conditions_request($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $estimates_count,
            "iTotalDisplayRecords" => $estimates_count,
			'aaData' => array()
        );

		if(empty($estimates))
			jsonResponse('', 'success', $output);

        $items_id = array();
        $users_id = array();

        foreach ($estimates as $item) {
            $items_id[$item['id_item']] = $item['id_item'];
            $users_id[$item['id_seller']] = $item['id_seller'];
            $users_id[$item['id_buyer']] = $item['id_buyer'];
        }

        if (!empty($items_id)) {
            $products_list = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $products_list = arrayByKey($products_list, 'id');
        }

        if (!empty($users_id)) {
            $users_list = $this->user->getUsers(array('users_list' => implode(',', $users_id), 'company_info' => 1));
            $users_list = arrayByKey($users_list, 'idu');
        }

		foreach ($estimates as $estimate) {
			if (!empty($estimate['log'])) {
                $logs = array_reverse(with(json_decode("[{$estimate['log']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));
				$logs_html = '<table class="table table-bordered table-hover mb-5">
								<caption class="ttl-b tac mb-10"><i class="ep-icon ep-icon_clock"></i> Estimate timeline</caption>
								<thead>
									<tr role="row">
										<th class="w-90 tac">Date</th>
										<th class="w-90 tac">User</th>
										<th>Note(s)</th>
									</tr>
								</thead>
								<tbody>';

				foreach($logs as $key => $estimate_timeline){
					$logs_html .= '<tr class="odd">
									<td class="tac">'.formatDate($estimate_timeline['date'], 'm/d/Y H:i:s').'</td>
									<td class="tac">';

					if(isset($estimate_timeline['poster']))
						$logs_html .= $estimate_timeline['poster'];
					else
						$logs_html .= "System";

					$logs_html .= '</td>
									<td>';
					if(isset($estimate_timeline['price']))
						$logs_html .= '<strong>Estimated price: </strong> $ '.get_price($estimate_timeline['price'], false).'<br>';

					if(isset($estimate_timeline['quantity']))
						$logs_html .= '<strong>Estimated quantity: </strong> '.$estimate_timeline['quantity'].'<br>';

					$logs_html .= '<strong>Message: </strong> '.cleanOutput($estimate_timeline['message']).
								'</td>
							</tr>';
				}
				$logs_html .= '</tbody>
					</table>';
			} else{
				$logs_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> This estimate does not have any log(s).</div>';
			}

			$seller_name = $users_list[$estimate['id_seller']]['user_name'];
			$buyer_name = $users_list[$estimate['id_buyer']]['user_name'];

			$status_array = array(
				'new'           => 'New estimates',
				'wait_buyer'    => 'Waiting for the buyer',
				'wait_seller'   => 'Waiting for the seller',
				'declined'      => 'Declined',
				'initiated'     => 'Order initiated',
				'expired'       => 'Expired',
				'accepted'      => 'Accepted'
			);

			$company_link = getCompanyURL($users_list[$estimate['id_seller']]);
			$company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $users_list[$estimate['id_seller']]['name_company'] . "' target='_blank' href='" . $company_link . "'></a>";

            $history_estimate = orderNumber($estimate['id_request_estimate']);
            $item_img_link = getDisplayImageLink(array('{ID}' => $estimate['id_item'], '{FILE_NAME}' => $products_list[$estimate['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));

            //TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $estimate['id_seller'], 'recipientStatus' => $users_list[$estimate['id_seller']]['status'], 'module' => 5, 'item' => $estimate['id_request_estimate']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            //TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $estimate['id_buyer'], 'recipientStatus' => $users_list[$estimate['id_buyer']]['status'], 'module' => 5, 'item' => $estimate['id_request_estimate']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();

			$output['aaData'][] = array(
				'dt_estimate'               =>  $history_estimate,
				'dt_id_request_estimate'    =>  $estimate['id_request_estimate'] .
												'<br /><a rel="log_details" title="View log" class="ep-icon ep-icon_plus"></a>',
				'dt_status'                 =>  '<div class="tal">'.
												'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $status_array[$estimate['status']] . '" data-value="' . $estimate['status'] . '" data-name="status"></a>'.
												'</div>'.
												$status_array[$estimate['status']],
				'dt_item'                   =>  '<div class="img-prod pull-left w-30pr">
                                                <img
                                                    class="w-100pr"
                                                    src="' . $item_img_link . '"
                                                    alt="' . $products_list[$estimate['id_item']]['title'] . '"
                                                />
                                                </div>
												<div class="pull-right w-68pr">
												<div class="clearfix">
												<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Item" title="Filter by item" data-value-text="' . $products_list[$estimate['id_item']]['title'] . '" data-value="' . $estimate['id_item'] . '" data-name="item"></a>'.
												'<a class="ep-icon ep-icon_item txt-orange pull-left" title="View Product" href="' . __SITE_URL . 'item/' . strForURL($products_list[$estimate['id_item']]['title']) . '-' . $products_list[$estimate['id_item']]['id'] . '"></a>'.
													'<div class="pull-right">
														<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $products_list[$estimate['id_item']]['rating'] . '" data-readonly>
													</div>'.
												'</div>'.
												'<div>' . $products_list[$estimate['id_item']]['title'] . '</div>'.
												'</div>',
				'dt_buyer'                  =>  '<div class="tal">'.
												'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $buyer_name . '" data-value-text="' . $buyer_name . '" data-value="' . $estimate['id_buyer'] . '" data-name="buyer"></a>'.
												'<a class="ep-icon ep-icon_user" title="View personal page of ' . $buyer_name . '" href="' . __SITE_URL . 'usr/' . strForURL($buyer_name) . '-' . $estimate['id_buyer'] . '"></a>'.
												$btnChatBuyerView
                                                .'</div><a href="usr/' . strForURL($buyer_name) . '-' . $estimate['id_buyer'] . '">' . $buyer_name . '</a> <br /><span>' . $users_list[$estimate['id_buyer']]['gr_name'] . '</span>',
				'dt_seller'                 =>  '<div class="tal">'.
												'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $seller_name . '" data-value-text="' . $seller_name . '" data-value="' . $estimate['id_seller'] . '" data-name="seller"></a>'.
												'<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_list[$estimate['id_seller']]['fname'] . ' ' . $users_list[$estimate['id_seller']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($seller_name) . '-' . $estimate['id_seller'] . '"></a>'.
												$company_icon
                                                . $btnChatSellerView
                                                . '</div><a href="usr/' . strForURL($seller_name) . '-' . $estimate['id_seller'] . '">' . $seller_name . '</a> (' . $users_list[$estimate['id_seller']]['name_company'] . ') <br />'.
												'<span>' . $users_list[$estimate['id_seller']]['gr_name'] . '</span>',
				'dt_quantity'               =>  $estimate['quantity'],
				'dt_price'                  =>  '$' . $estimate['price'],
				'dt_date_created'           =>  formatDate($estimate['create_date']),
				'dt_date_changed'           =>  formatDate($estimate['update_date']),
				'dt_log'                    =>  $logs_html,
			);
		}

        jsonResponse('', 'success', $output);
    }
}

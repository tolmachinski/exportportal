<?php

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
class Extend_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = array();

    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notifier = $container->get(NotifierInterface::class);
    }

    private function _load_main(){
        $this->load->model('Extend_model', 'extend');
        $this->load->model('User_model', 'user');
    }

    function index() {
        headerRedirect();
    }

    function popup_form(){
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();

        $action = $this->uri->segment(3);

        switch($action){
            case 'bill':
                $this->load->model('User_Bills_Model', 'user_bills');
                if(!have_right('manage_personal_bills')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $id_bill = intVal($this->uri->segment(4));
                $id_user = id_session();
                $data['bill'] = $this->user_bills->get_user_bill($id_bill, array('id_user' => $id_user));

                if(empty($data['bill'])){
                    messageInModal(translate('systmess_billing_bill_not_exist_message'));
                }

                if(!in_array($data['bill']['status'], array('init','paid'))){
                    messageInModal(translate('systmess_error_cannot_perform_this_action'));
                }

                if($data['bill']['extend_request']){
                    messageInModal(translate('systmess_billing_already_open_request'), 'info');
                }

                $data['id_extend_item'] = $id_bill;
                $data['extend_type'] = 'bill';
                $this->view->assign($data);

                $this->view->display('new/extends/request_extend_form');
            break;
            case 'order':
                $this->load->model('Orders_Model', 'orders');
                if(!have_right('buy_item') && !have_right('manage_seller_orders'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $id_order = $this->uri->segment(4);
                $data['order_info'] = $this->orders->get_order($id_order);

                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if(is_privileged('user', $data['order_info']['id_buyer'], 'buy_item')){
                    if(!in_array($data['order_info']['status_alias'], array('invoice_sent_to_buyer', 'shipper_assigned', 'payment_processing', 'shipping_completed'))){
                        messageInModal(translate('systmess_error_extend_order_status_time_inappropriate_status'));
                    }
                } elseif(is_privileged('user', $data['order_info']['id_seller'], 'manage_seller_orders')){
                    if(!in_array($data['order_info']['status_alias'], array('new_order', 'invoice_confirmed', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress'))){
                        messageInModal(translate('systmess_error_extend_order_status_time_inappropriate_status'));
                    }
                } else{
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if ((bool)$data['order_info']['extend_request']) {
                    messageInModal(translate('systmess_error_order_extend_status_time_request_already_exists'), 'info');
                }

                $data['id_extend_item'] = $id_order;
                $data['extend_type'] = 'order';
                $this->view->assign($data);

                $this->view->display('new/extends/request_extend_form');
            break;
            case 'extend_time':
                if(!have_right('manage_bills')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $data['id_extend_item'] = (int)$this->uri->segment(5);
                $data['extend_type'] = $this->uri->segment(4);
                $this->view->assign($data);
                $this->view->display('admin/extends/admin_extend_form');
            break;
            case 'detail':
                if(!have_right('manage_personal_bills')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $id_request = $this->uri->segment(4);
                $data['request_info'] = $this->extend->get_extend_request($id_request);

                if(empty($data['request_info']))
                    messageInModal(translate("systmess_error_request_does_not_exist"));

                if(!is_privileged('user', $data['request_info']['id_user'], true)){
                    messageInModal(translate("systmess_error_request_does_not_exist"));
                }

                $this->view->assign($data);

                $this->view->display('new/extends/request_extend_detail');
            break;
            case 'detail_admin':
                if(!have_right('manage_bills')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $id_request = $this->uri->segment(4);
                $data['request_info'] = $this->extend->get_extend_request($id_request);

                if(empty($data['request_info']))
                    messageInModal(translate("systmess_error_request_does_not_exist"));

                $this->view->assign($data);
                $this->view->display('admin/extends/request_extend_detail_admin');
            break;
        }
    }

    public function ajax_operation(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $action = $this->uri->segment(3);
        $id_user = privileged_user_id();
        switch($action){
            case 'create_request':
                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'extend_days',
                        'label' => 'Extend for N day(s)',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[90]' => '')
                    ),
                    array(
                        'field' => 'extend_reason',
                        'label' => 'Extend reason',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'id_extend_item',
                        'label' => 'Item detail',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'extend_type',
                        'label' => 'Item type',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_extend_item = (int)$_POST['id_extend_item'];
                $extend_type = $_POST['extend_type'];
                $extend_days = (int)$_POST['extend_days'];
                $extend_reason = cleanInput($_POST['extend_reason']);

                switch($extend_type){
                    case 'bill':
                        if(!have_right('manage_personal_bills'))
                            jsonResponse(translate("systmess_error_rights_perform_this_action"));

                        $this->load->model('User_Bills_Model', 'user_bills');

                        $bill_info = $this->user_bills->get_user_bill($id_extend_item, array('id_user' => $id_user));
                        if(empty($bill_info)){
                            jsonResponse(translate('systmess_billing_bill_not_exist_message'));
                        }

                        if(!in_array($bill_info['status'], array('init','paid'))){
                            jsonResponse(translate('systmess_error_cannot_perform_this_action'));
                        }

                        if((bool)$bill_info['extend_request'])
                            jsonResponse(translate('systmess_billing_already_open_request'), 'info');

                        $action_date = date('Y-m-d H:i:s');
                        $insert = array(
                            'id_user'       => $id_user,
                            'id_item'       => $id_extend_item,
                            'item_type'     => 'bill',
                            'days'          => $extend_days,
                            'extend_reason' => $extend_reason,
                            'date_create'   => $action_date
                        );
                        $id_extend = $this->extend->set_extend_request($insert);

                        $note = array(
                            'date_note' => $action_date,
                            'note' => 'Request extend payment time.<br><strong>Extend reason:</strong> '.$extend_reason
                        );

                        $update_bill = array(
                            'extend_request' => $id_extend,
                            'note' => json_encode($note)
                        );
                        $this->user_bills->change_user_bill($id_extend_item, $update_bill);
                        jsonResponse(translate('systmess_bill_extend_request_success_message'), 'success', array('bill'=>$id_extend_item));
                    break;
                    case 'order':
                        $this->load->model('Orders_Model', 'orders');
                        if(!have_right('buy_item') && !have_right('manage_seller_orders'))
                            jsonResponse(translate("systmess_error_rights_perform_this_action"));

                        $order_info = $this->orders->get_order($id_extend_item);

                        if (empty($order_info)) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        if(is_privileged('user', $order_info['id_buyer'], 'buy_item')){
                            if(!in_array($order_info['status_alias'], array('invoice_sent_to_buyer','shipper_assigned','payment_processing','shipping_completed'))){
                                jsonResponse(translate('systmess_error_extend_order_status_time_inappropriate_status'));
                            }
                            $user_group = 'Buyer';
                        } elseif(is_privileged('user', $order_info['id_seller'], 'manage_seller_orders')){
                            if(!in_array($order_info['status_alias'], array('new_order', 'invoice_confirmed', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress'))){
                                jsonResponse(translate('systmess_error_extend_order_status_time_inappropriate_status'));
                            }
                            $user_group = 'Seller';
                        } else{
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        if ((bool)$order_info['extend_request']) {
                            jsonResponse(translate('systmess_error_order_extend_status_time_request_already_exists'), 'info');
                        }

                        $action_date = date('Y-m-d H:i:s');
                        $insert = array(
                            'id_user'       => $id_user,
                            'id_item'       => $id_extend_item,
                            'item_type'     => 'order',
                            'days'          => $extend_days,
                            'extend_reason' => $extend_reason,
                            'date_create'   => $action_date
                        );
                        $id_extend = $this->extend->set_extend_request($insert);
                        $order_log = array(
                            'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                            'user' => $user_group,
                            'message' => 'Create request for extend current order status ('.$order_info['order_status'].') time.<br><strong>Extend reason:</strong> '.$extend_reason
                        );

                        $update_order = array(
                            'extend_request' => $id_extend,
                            'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                        );

                        $this->orders->change_order($id_extend_item, $update_order);
                        jsonResponse(translate('systmess_success_order_add_extend_status_time_request'), 'success', array('order'=>$id_extend_item));
                    break;
                    default:
                        jsonResponse('Error: Incorrect data has been sent.');
                    break;
                }
            break;
            case 'extend_item' :
                if(!have_right('manage_bills') && !have_right('administrate_orders')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'extend_days',
                        'label' => 'Extend for N day(s)',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[90]' => '')
                    ),
                    array(
                        'field' => 'extend_reason',
                        'label' => 'The extend reason',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'id_extend_item',
                        'label' => 'Extend detail',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'extend_type',
                        'label' => 'Extend detail',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $extend_days = (int)$_POST['extend_days'];
                $extend_reason = cleanInput($_POST['extend_reason']);
                $id_extend_item = (int)$_POST['id_extend_item'];
                $extend_type = $_POST['extend_type'];

                switch($extend_type){
                    case 'order':
                        $this->load->model('Orders_Model', 'orders');
                        $order_info = $this->orders->get_order($id_extend_item);
                        $orderNumber = orderNumber($id_extend_item);
                        $action_date = date('Y-m-d H:i:s');
                        $order_log = array(
                            'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                            'user' => 'EP Manager',
                            'message' => 'The status ('.$order_info['order_status'].') time for order '.$orderNumber.' has been extended with '.$extend_days.' day(s).<br><strong>Reason: </strong>'.$extend_reason
                        );

                        $update_order = array(
                            'extend_request' => 0,
                            'status_countdown' => date_plus($extend_days, 'days', $order_info['status_countdown'], true),
                            'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                        );
                        $this->orders->change_order($id_extend_item, $update_order);

                        if(in_array($order_info['status_alias'], array('shipper_assigned', 'payment_processing'))){
                            $this->load->model('User_Bills_Model', 'user_bills');
                            $bills = $this->user_bills->get_user_bills(array('id_order' => $order_info['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                            if(!empty($bills)){
                                foreach($bills as $bill){
                                    $update_bill = array(
                                        'due_date' => date_plus($extend_days, 'days', $bill['due_date'], true)
                                    );
                                    $this->user_bills->change_user_bill($bill['id_bill'], $update_bill);
                                }
                            }
                        }

                        $this->notifier->send(
                            (new SystemNotification('extend_item_admin', [
								'[DAYS]'      => $extend_days,
								'[ITEM_NAME]' => $orderNumber,
								'[ITEM_TYPE]' => 'order',
								'[ITEM_LINK]' => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_extend_item),
								'[LINK]'      => \sprintf('%sorder/my', __SITE_URL),
                            ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                            ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                                $order_info['id_seller'],
                                $order_info['id_buyer'],
                            ])
                        );

                        jsonResponse('The extend action has been successfully done.', 'success');
                    break;
                    case 'bill':
                        $this->load->model('User_Bills_Model', 'user_bills');
                        $bill_info = $this->user_bills->get_user_bill($id_extend_item);
                        if(empty($bill_info)){
                            jsonResponse('Error: The bill does not exist.');
                        }

                        if(!in_array($bill_info['status'], array('init','paid'))){
                            jsonResponse('Error: You cannot perform this action now.');
                        }

                        $action_date = date('Y-m-d H:i:s');
                        $billNumber = orderNumber($id_extend_item);
                        $note = array(
                            'date_note' => $action_date,
                            'note' => 'The payment time for bill '.$billNumber.' has been extended with '.$extend_days.' day(s).<br><strong>Reason: </strong>'.$extend_reason
                        );

                        $update_bill = array(
                            'extend_request' => 0,
                            'due_date' => date_plus($extend_days, 'days', $bill_info['due_date'], true),
                            'note' => json_encode($note)
                        );
                        $this->user_bills->change_user_bill($id_extend_item, $update_bill);

                        if(in_array($bill_info['id_type_bill'], array(1,2))){
                            $this->load->model('Orders_Model', 'orders');
                            $order_info = $this->orders->get_order($bill_info['id_item']);
                            if(!empty($order_info)){
                                if(in_array($order_info['status_alias'], array('shipper_assigned', 'payment_processing'))){
                                    $bills = $this->user_bills->get_user_bills(array('id_order' => $order_info['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                                    if(!empty($bills)){
                                        foreach($bills as $bill){
                                            if($bill['id_bill'] == $bill_info['id_bill']){
                                                continue;
                                            }

                                            $update_bill = array(
                                                'due_date' => date_plus($extend_days, 'days', $bill['due_date'], true)
                                            );
                                            $this->user_bills->change_user_bill($bill['id_bill'], $update_bill);
                                        }
                                    }
                                }
                            }

                            $order_log = array(
                                'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                                'user' => 'EP Manager',
                                'message' => 'The payment time for bill '.$billNumber.' and the current order status ('.$order_info['order_status'].') time has been extended with '.$extend_days.' day(s).'
                            );

                            $update_order = array(
                                'status_countdown' => date_plus($extend_days, 'days', $order_info['status_countdown'], true),
                                'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                            );

                            $this->orders->change_order($order_info['id'], $update_order);
                        }

                        $this->notifier->send(
                            (new SystemNotification('extend_item_admin', [
								'[DAYS]'      => $extend_days,
								'[ITEM_NAME]' => $billNumber,
								'[ITEM_TYPE]' => 'bill',
								'[ITEM_LINK]' => sprintf('%sbilling/my/bill/%s', __SITE_URL, $id_extend_item),
								'[LINK]'      => sprintf('%sbilling/my/bill/%s', __SITE_URL, $id_extend_item),
                            ]))->channels([(string) SystemChannel::STORAGE()]),
                            new Recipient((int) $bill_info['id_user'])
                        );

                        jsonResponse('The payment time has been successfully extended.', 'success');
                    break;
                }
            break;
            case 'confirm' :
                if(!have_right('manage_bills')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'extend_days',
                        'label' => 'Extend for N day(s)',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[90]' => '')
                    ),
                    array(
                        'field' => 'id_extend',
                        'label' => 'Extend request detail',
                        'rules' => array('required' => '', 'integer' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_extend = (int)$_POST['id_extend'];
                $extend_days = (int)$_POST['extend_days'];
                $request_info = $this->extend->get_extend_request($id_extend);

                if(empty($request_info)) {
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                $now = new DateTime();
                $nowFormatedAsString = $now->format('Y-m-d H:i:s');

                switch($request_info['item_type']){
                    case 'order':
                        $this->load->model('Orders_Model', 'orders');
                        $order_info = $this->orders->get_order($request_info['id_item']);
                        $action_date = date('Y-m-d H:i:s');
                        $order_log = array(
                            'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                            'user' => 'EP Manager',
                            'message' => 'The request for extending current order status ('.$order_info['order_status'].') time with '.$extend_days.' day(s) has been accepted.'
                        );

                        $orderStatusCountdown = (new DateTime())::createFromFormat('Y-m-d H:i:s', $order_info['status_countdown']);

                        $update_order = array(
                            'extend_request' => 0,
                            'status_countdown' => date_plus($extend_days, 'days', $now > $orderStatusCountdown ? $nowFormatedAsString : $order_info['status_countdown'], true),
                            'order_summary' => $order_info['order_summary'].','.json_encode($order_log),

                            'auto_extend' => 0,
                            'request_auto_extend' => 0,
                            'reminder_sent' => 0,
                        );

                        $this->load->model('Auto_Extend_Model', 'auto_extend');
                        $this->auto_extend->delete_extend_request_by_item($request_info['id_item']);

                        $this->orders->change_order($request_info['id_item'], $update_order);

                        if(in_array($order_info['status_alias'], array('shipper_assigned', 'payment_processing'))){
                            $this->load->model('User_Bills_Model', 'user_bills');
                            $bills = $this->user_bills->get_user_bills(array('id_order' => $order_info['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                            if(!empty($bills)){
                                foreach($bills as $bill){
                                    $billDueDate = (new DateTime())::createFromFormat('Y-m-d H:i:s', $bill['due_date']);
                                    $update_bill = array(
                                        'due_date' => date_plus($extend_days, 'days', $now > $billDueDate ? $nowFormatedAsString : $bill['due_date'], true)
                                    );
                                    $this->user_bills->change_user_bill($bill['id_bill'], $update_bill);
                                }
                            }
                        }

                        $this->notifier->send(
                            (new SystemNotification('extend_order_confirmed', [
								'[ORDER_ID]'   => orderNumber($order_info['id']),
								'[ORDER_LINK]' => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_info['id']),
								'[LINK]'       => \sprintf('%sorder/my', __SITE_URL),
                            ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                            (new Recipient((int) $request_info['id_user']))->withRoomType(RoomType::CARGO())
                        );

                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse('The request has been approved.', 'success');
                    break;
                    case 'bill':
                        $this->load->model('User_Bills_Model', 'user_bills');

                        $bill_info = $this->user_bills->get_user_bill($request_info['id_item']);
                        if(empty($bill_info)){
                            jsonResponse('Error: The bill does not exist.');
                        }

                        if(!in_array($bill_info['status'], array('init','paid'))){
                            jsonResponse('Error: You cannot perform this action now.');
                        }

                        $action_date = date('Y-m-d H:i:s');
                        $note = array(
                            'date_note' => $action_date,
                            'note' => 'The request for extending payment time with '.$extend_days.' day(s) has been accepted.'
                        );

                        $billDueDate = (new DateTime())::createFromFormat('Y-m-d H:i:s', $bill_info['due_date']);

                        $update_bill = array(
                            'extend_request' => 0,
                            'due_date' => date_plus($extend_days, 'days', $now > $billDueDate ? $nowFormatedAsString : $bill_info['due_date'], true),
                            'note' => json_encode($note)
                        );
                        $this->user_bills->change_user_bill($request_info['id_item'], $update_bill);

                        if(in_array($bill_info['id_type_bill'], array(1,2))){
                            $this->load->model('Orders_Model', 'orders');
                            $order_info = $this->orders->get_order($bill_info['id_item']);
                            if(!empty($order_info)){
                                if(in_array($order_info['status_alias'], array('shipper_assigned', 'payment_processing'))){
                                    $bills = $this->user_bills->get_user_bills(array('id_order' => $order_info['id'], 'bills_type'=>'1,2', 'status'=> "'init', 'paid'"));
                                    if(!empty($bills)){
                                        foreach($bills as $bill){
                                            if($bill['id_bill'] == $bill_info['id_bill']){
                                                continue;
                                            }

                                            $billDueDate = (new DateTime())::createFromFormat('Y-m-d H:i:s', $bill['due_date']);

                                            $update_bill = array(
                                                'due_date' => date_plus($extend_days, 'days', $now > $billDueDate ? $nowFormatedAsString : $bill['due_date'], true)
                                            );
                                            $this->user_bills->change_user_bill($bill['id_bill'], $update_bill);
                                        }
                                    }
                                }
                            }

                            $order_log = array(
                                'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                                'user' => 'EP Manager',
                                'message' => 'The request for extending payment time has been accepted and the current order status ('.$order_info['order_status'].') time has been extended with '.$extend_days.' day(s).'
                            );

                            $orderStatusCountdown = (new DateTime())::createFromFormat('Y-m-d H:i:s', $order_info['status_countdown']);

                            $update_order = array(
                                'status_countdown' => date_plus($extend_days, 'days', $now > $orderStatusCountdown ? $nowFormatedAsString : $order_info['status_countdown'], true),
                                'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                            );

                            $this->orders->change_order($request_info['id_item'], $update_order);
                        }

                        $this->notifier->send(
                            (new SystemNotification('extend_bill_confirmed', [
                                '[BILL_ID]'   => orderNumber($bill_info['id_bill']),
								'[BILL_LINK]' => \sprintf('%sbilling/my/bill/%s', __SITE_URL, $bill_info['id_bill']),
								'[LINK]'      => \sprintf('%sbilling/my', __SITE_URL),
                            ]))->channels([(string) SystemChannel::STORAGE()]),
                            new Recipient((int) $request_info['id_user'])
                        );

                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse('The request has been approved.', 'success');
                    break;
                }
            break;
            case 'decline' :
                if(!have_right('manage_bills') && !have_right('administrate_orders')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'id_extend',
                        'label' => 'Extend request detail',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'extend_comment',
                        'label' => 'Decline reason',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_extend = (int)$_POST['id_extend'];
                $extend_comment = cleanInput($_POST['extend_comment']);
                $request_info = $this->extend->get_extend_request($id_extend);

                if(empty($request_info))
                    jsonResponse(translate("systmess_error_request_does_not_exist"));

                switch($request_info['item_type']){
                    case 'order':
                        $this->load->model('Orders_Model', 'orders');
                        $order_info = $this->orders->get_order($request_info['id_item']);
                        $action_date = date('Y-m-d H:i:s');
                        $order_log = array(
                            'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                            'user' => 'EP Manager',
                            'message' => 'The request for extending current order status ('.$order_info['order_status'].') time with '.$request_info['days'].' day(s) has been declined.<br><strong>Reason: </strong>'.$extend_comment
                        );

                        $update_order = array(
                            'extend_request' => 0,
                            'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                        );

                        $this->orders->change_order($request_info['id_item'], $update_order);

                        $this->notifier->send(
                            (new SystemNotification('extend_order_declined', [
								'[REASON]'     => cleanOutput($extend_comment),
                                '[ORDER_ID]'   => orderNumber($order_info['id']),
								'[ORDER_LINK]' => \sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_info['id']),
								'[LINK]'       => \sprintf('%sorder/my', __SITE_URL),
                            ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                            (new Recipient((int) $request_info['id_user']))->withRoomType(RoomType::CARGO())
                        );

                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse('The request has been declined.', 'success');
                    break;
                    case 'bill':
                        $this->load->model('User_Bills_Model', 'user_bills');
                        $bill_info = $this->user_bills->get_user_bill($request_info['id_item']);
                        if(!empty($bill_info)){
                            $action_date = date('Y-m-d H:i:s');
                            $note = array(
                                'date_note' => $action_date,
                                'note' => 'The request for extending payment time with '.$request_info['days'].' day(s) has been declined.<br><strong>Reason: </strong>'.$extend_comment
                            );

                            $update_bill = array(
                                'extend_request' => 0,
                                'note' => json_encode($note)
                            );
                            $this->user_bills->change_user_bill($request_info['id_item'], $update_bill);


                            $this->notifier->send(
                                (new SystemNotification('extend_bill_declined', [
									'[REASON]'    => cleanOutput($extend_comment),
                                    '[BILL_ID]'   => orderNumber($bill_info['id_bill']),
									'[BILL_LINK]' => \sprintf('%sbilling/my/bill/%s', __SITE_URL, $bill_info['id_bill']),
									'[LINK]'      => \sprintf('%sbilling/my', __SITE_URL),
                                ]))->channels([(string) SystemChannel::STORAGE()]),
                                new Recipient((int) $request_info['id_user'])
                            );
                        }

                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse('The request has been declined.', 'success');
                    break;
                }
			break;
            case 'decline_user' :
                if(!have_right('buy_item') && !have_right('manage_seller_orders')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'extend',
                        'label' => 'Extend request detail',
                        'rules' => array('required' => '', 'integer' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_extend = (int)$_POST['extend'];
                $request_info = $this->extend->get_extend_request($id_extend);

                if(empty($request_info))
                    jsonResponse(translate("systmess_error_request_does_not_exist"));

                if(!is_privileged('user', $request_info['id_user'], true)){
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                if(have_right('buy_item')){
                    $user_type = 'Buyer';
                } else{
                    $user_type = 'Seller';
                }

                switch($request_info['item_type']){
                    case 'order':
                        $this->load->model('Orders_Model', 'orders');
                        $order_info = $this->orders->get_order($request_info['id_item']);
                        $action_date = date('Y-m-d H:i:s');
                        $order_log = array(
                            'date' => formatDate($action_date, 'm/d/Y H:i:s'),
                            'user' => $user_type,
                            'message' => 'The request for extending current order status ('.$order_info['order_status'].') time with '.$request_info['days'].' day(s) has been canceled.'
                        );

                        $update_order = array(
                            'extend_request' => 0,
                            'order_summary' => $order_info['order_summary'].','.json_encode($order_log)
                        );

                        $this->orders->change_order($request_info['id_item'], $update_order);
                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse(translate('systmess_billing_request_extend_declined_success_message'), 'success', array('order'=>$request_info['id_item']));
                    break;
                    case 'bill':
                        $this->load->model('User_Bills_Model', 'user_bills');
                        $bill_info = $this->user_bills->get_user_bill($request_info['id_item']);
                        if(!empty($bill_info)){
                            $action_date = date('Y-m-d H:i:s');
                            $note = array(
                                'date_note' => $action_date,
                                'note' => 'The request for extending payment time with '.$request_info['days'].' day(s) has been canceled.<br><strong>Reason: </strong>'.$extend_comment
                            );

                            $update_bill = array(
                                'extend_request' => 0,
                                'note' => json_encode($note)
                            );
                            $this->user_bills->change_user_bill($request_info['id_item'], $update_bill);
                        }

                        $this->extend->delete_extend_request($id_extend);
                        jsonResponse(translate('systmess_bill_request_decline_message'), 'success', array('bill'=>$request_info['id_item']));
                    break;
                }
            break;
        }
    }
}

?>

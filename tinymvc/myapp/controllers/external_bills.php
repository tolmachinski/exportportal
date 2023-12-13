<?php

use App\Common\Buttons\ChatButton;
use Sample_Orders_Model;
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class External_Bills_Controller extends TinyMVC_Controller
{
    function administration(){
        checkAdmin('manage_content');

        $data['title'] = 'External bills';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/external_bills/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_external_bills_admin_dt(){
        $this->load->model('Billing_Model', 'ext_bills');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id' => 'eb.id',
                'dt_type' => 'eb.type',
                'dt_money' => 'eb.money',
                'dt_date' => 'eb.date_time',
                'dt_status' => 'eb.status'
            ])
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'type', 'key' => 'type', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'status', 'type' => 'cleanInput'],
            ['as' => 'start_date', 'key' => 'start', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'finish_date', 'key' => 'finish', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords', 'key' => 'search', 'type' => 'cleanInput'],
            ['as' => 'realUsers', 'key' => 'real_users', 'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2]
        ]);

        if (!isset($conditions['realUsers'])) {
            $conditions['realUsers'] = 1;
        } elseif (2 === $conditions['realUsers']) {
            unset($conditions['realUsers']);
        }

        if (isset($_POST['user'])){
            $temp = explode('-',$_POST['user']);
            $params['id_user'] = intval($temp[0]);
            $params['type'] = cleanInput($temp[1]);
        }

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["eb.id-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $conditions, ['limit' => true]);

        /** @var Billing_Model $billingModel */
        $billingModel = model(Billing_Model::class);

        $external_bills = $billingModel->get_external_bills($params);
        $records_total = $billingModel->get_external_bills_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_total,
            "iTotalDisplayRecords" => $records_total,
            "aaData" => array()
        );
        $temp = array();
        $ids = array();
        $statuses = array(
            'waiting' => array(
                'text' => 'Waiting',
                'icon' => '<i class="ep-icon ep-icon_hourglass-processing txt-orange fs-30"></i>'
            ),
            'processed' => array(
                'text' => 'Processed',
                'icon' => '<i class="ep-icon ep-icon_ok-circle txt-green fs-30"></i>'
            )
        );
        foreach ($external_bills as $external_bill) {
            $ids[$external_bill['type']][$external_bill['to_user']] = $external_bill['to_user'];

            $edit_btn = '';
            if($external_bill['status'] != 'processed')
                $edit_btn = "<a href='".__SITE_URL."external_bills/popup_forms/edit_form/".$external_bill['id']."' class='ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT fs-16' data-title='Edit this external bill request' title='Edit this external bill request'></a>";

            $confirm_btn = '';
            if($external_bill['status'] != 'processed')
                $confirm_btn = "<a class='ep-icon ep-icon_ok-circle confirm-dialog fs-16 txt-green' data-callback='confirm_external_bill' data-message='Are you sure want complete this external bill?' data-id-ext-bill='".$external_bill['id']."' title='Confirm external bill'></a>";

            $delete_btn = '';
            if($external_bill['special_request'] == 1)
                $delete_btn = "<a class='ep-icon ep-icon_remove confirm-dialog fs-16 txt-red' data-callback='delete_external_bill' data-message='Are you sure want delete this external bill?' data-id_request='".$external_bill['id']."' data-title='Delete external bill request'></a>";
            $temp[] = array(
                'dt_id' => $external_bill['id'],
                'dt_user' => '',
                'dt_money' => '$ '.get_price($external_bill['money'], false),
                'dt_date' => formatDate($external_bill['date_time']),
                'dt_type' => '<div class="clearfix tal">'
                                .'<a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Type" title="Filter by ' . $external_bill['type'] . '" data-value-text="' . ucfirst($external_bill['type']) . '" data-value="' . $external_bill['type'] . '" data-name="type"></a>'
                            . '</div>'
                            .'<a class="dt_filter" data-title="Type" title="Filter by ' . $external_bill['type'] . '" data-value-text="' . ucfirst($external_bill['type']) . '" data-value="' . $external_bill['type'] . '" data-name="type">'.ucfirst($external_bill['type']).'</a>',
                'dt_comment' => $external_bill['comment'],
                'dt_status' => '<a class="dt_filter" data-title="Status" title="Filter by ' . ucfirst($external_bill['status']) . '" data-value-text="' . ucfirst($external_bill['status']) . '" data-value="' . $external_bill['status'] . '" data-name="status">'.$statuses[$external_bill['status']]['icon'].'<br>'.$statuses[$external_bill['status']]['text'].'</a>',
                'dt_actions' => $edit_btn
                        . "<a href='".__SITE_URL."external_bills/popup_forms/notice/".$external_bill['id']."' class='ep-icon ep-icon_notice fancybox.ajax fancyboxValidateModal fs-16' data-title='Notes' title='Notes'></a>"
                        . $confirm_btn
                        . $delete_btn
            );
        }
        $users = $this->ext_bills->get_users($ids);

        foreach($external_bills as $key => $external_bill){
            $contact_user = '';
            $pers_page = '';
            if(isset($users[$external_bill['type']][$external_bill['to_user']])){
                $user_name = $users[$external_bill['type']][$external_bill['to_user']][$external_bill['type'].'_name'];

                //TODO: admin chat hidden
                $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $external_bill['to_user'], 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
                $contact_user = $btnChatUser->button();

                switch($external_bill['type']){
                    case 'seller':
                        $pers_page = '<a class="fs-16 ep-icon ep-icon_user" title="View personal page of ' . $user_name . '" href="' . __SITE_URL . 'usr/' . strForURL($user_name) . '-' . $external_bill['to_user'] . '"></a>';
                        $image = getDisplayImageLink(array('{ID}' => $external_bill['to_user'], '{FILE_NAME}' => $users['seller'][$external_bill['to_user']]['seller_logo']), 'users.main', array( 'thumb_size' => 0 ));
                    break;
                    case 'buyer':
                        $pers_page = '<a class="fs-16 ep-icon ep-icon_user" title="View personal page of ' . $user_name . '" href="' . __SITE_URL . 'usr/' . strForURL($user_name) . '-' . $external_bill['to_user'] . '"></a>';
                        $image = getDisplayImageLink(array('{ID}' => $external_bill['to_user'], '{FILE_NAME}' => $users['buyer'][$external_bill['to_user']]['buyer_logo']), 'users.main', array( 'thumb_size' => 0 ));
                    break;
                    case 'shipper':
                        $contact_user = '';
                        $image = getDisplayImageLink(array('{ID}' => $users[$external_bill['type']][$external_bill['to_user']]['id'], '{FILE_NAME}' => $users[$external_bill['type']][$external_bill['to_user']]['shipper_logo']), 'users.main', array( 'thumb_size' => 0 ));
                    break;
                }

                $user =	'<div class="img-prod pull-left w-30pr">'
                            . '<img class="w-100pr" src="' . $image . '" alt="Logo"/>'
                        . '</div>'
                        . '<div class="pull-right pl-10 w-70pr">'
                            . '<div class="pull-left w-100pr">'
                                . '<a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="User" title="Filter by ' . $user_name . '" data-value-text="' . $user_name . '" data-value="' . $external_bill['to_user'] . '-'.$external_bill['type'].'" data-name="user"></a>'
                                . $pers_page
                                . $contact_user
                            . '</div><br/>'
                            . '<div class="clearfix">'.$user_name.'</div>'
                        . '</div>';
            }else{
                $user = translate("systmess_error_user_does_not_exist");
            }


            $output['aaData'][$key] = $temp[$key];
            $output['aaData'][$key]['dt_user'] = $user;
        }
        jsonResponse('', 'success', $output);
    }

    function popup_forms(){
        if (!isAjaxRequest())
            show_404();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"));

        $action = cleanInput($this->uri->segment(3));
        switch ($action) {
            case 'notice':
                checkAdmin('manage_content');
                $id_ext_bill = $this->uri->segment(4);
                $this->load->model('Billing_Model', 'ext_bill');

                $data['external_bill'] = $this->ext_bill->get_ext_bill($id_ext_bill);
                $data['external_bill']['notice'] = json_decode('['.$data['external_bill']['notice'].']', true);

                if(!empty($_GET['return_to'])){
                    $data['return_to_modal_url'] = $_GET['return_to'];
                }

                $this->view->display('admin/external_bills/notice_form_view', $data);
            break;
            case 'add_form':
                checkAdminAjaxModal('manage_content');
                $type = $this->uri->segment(4);
                switch($type){
                    case 'order' :
                        $this->load->model('Orders_Model', 'orders');
                        $this->load->model('Billing_Model', 'billing');
                        $id_order = (int)$this->uri->segment(5);
                        $data['order_detail'] = $this->orders->get_order($id_order);
                        if(empty($data['order_detail']))
                            messageInModal('Error: The order '.orderNumber($id_order).' does not exist.');

                        if(!empty($data['order_detail']['external_bills_requests'])){
                            $data['requests'] = $this->billing->get_external_bills(array('request_list' => $data['order_detail']['external_bills_requests']));
                        }

                        // CHECK FOR PAID AND CONFIRMED BILLS
                        // AND DISPLAY THIS DETAILS
                        $this->load->model('User_Bills_Model', 'user_bills');
                        $params = array('id_item' => $id_order, 'encript_detail' => 1, 'bills_type' => '1,2', 'pagination' => false);
                        $data['bills'] = $this->user_bills->get_user_bills($params);
                        $data['amount_confirmed'] = $this->user_bills->summ_bills_by_order($id_order, "'confirmed'", '1,2');

                        $data['request_type'] = $type;
                        $this->view->display('admin/external_bills/add_by_order_form_view', $data);
                    break;
                    case 'sample_order':
                        $id_order = (int) uri()->segment(5);
                        $data['order_detail'] = model('sample_orders')->findOneBy(array('conditions' => array('sample' => $id_order)));

                        if (empty($data['order_detail'])) {
                            messageInModal('The sample order '. orderNumber($id_order) . ' does not exist.');
                        }

                        if (!empty($data['order_detail']['external_bills_requests'])){
                            $data['requests'] = model('billing')->get_external_bills(array('request_list' => $data['order_detail']['external_bills_requests']));
                        }

                        $data['bills'] = model('user_bills')->get_user_bills(array('id_item' => $id_order, 'encript_detail' => 1, 'bills_type' => Sample_Orders_Model::ORDER_BILL_TYPE, 'pagination' => false));
                        $data['amount_confirmed'] = model('user_bills')->summ_bills_by_order($id_order, "'confirmed'", Sample_Orders_Model::ORDER_BILL_TYPE);

                        $data['request_type'] = $type;
                        $this->view->display('admin/external_bills/add_by_order_form_view', $data);
                    break;
                    case 'other':
                        $this->load->model('Shippers_Model', 'shippers');
                        $data['shippers'] = $this->shippers->get_shippers();
                        $data['request_type'] = $type;
                        $this->view->display('admin/external_bills/add_other_form_view', $data);
                    break;
                    default:
                        messageInModal('Error: You did not specified the operation.');
                    break;
                }
            break;
            case 'edit_form':
                checkAdmin('manage_content');
                $id_ext_bill = $this->uri->segment(4);
                $this->load->model('Billing_Model', 'ext_bill');

                $data['external_bill'] = $this->ext_bill->get_ext_bill($id_ext_bill);
                $this->view->display('admin/external_bills/edit_form_view', $data);
            break;
            case 'refund_form':
                checkAdmin('manage_content');
                $id_bill = (int)$this->uri->segment(4);
                $this->load->model('User_Bills_Model', 'user_bills');
                $this->load->model('Orders_Model', 'orders');

                $data['bill'] = $this->user_bills->get_user_bill($id_bill);
                if(empty($data['bill'])){
                    messageInModal('Error: This bill does not exist.');
                }

                if($data['bill']['status'] != 'confirmed'){
                    messageInModal('Error: The payment for this bill has not been confirmed.');
                }

                if($data['bill']['refund_bill_request'] > 0){
                    $this->load->model('Billing_Model', 'billing');
                    $data['requests'] = $this->billing->get_external_bills(array('request_list' => $data['bill']['refund_bill_request']));
                }

                $pay_detail = $this->user_bills->get_encrypt_data($id_bill, array('pay_detail') );
                $data['pay_detail'] = unserialize($pay_detail['pay_detail']);
                if(empty($data['pay_detail'])){
                    messageInModal('Info: The payment detail is empty. Please check the bill payment status.', 'info');
                }
                $pay_methods = $this->orders->get_pay_method($data['pay_detail']['pay_method']['value']);
                $data['pay_detail']['pay_method']['value'] = $pay_methods['method'];

                if(!empty($_GET['return_to'])){
                    $data['return_to_modal_url'] = $_GET['return_to'];
                }

                $this->view->display('admin/external_bills/refund_form_view', $data);
            break;
        }
    }

    function ajax_external_bills_operation(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));
        $this->load->model('Dispute_Model', 'dispute');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_notice':
                checkAdminAjax('manage_content');
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'External bill',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'notice',
                        'label' => 'Notice',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $this->load->model('Billing_Model', 'ext_bill');
                $id_ext_bill = intVal($_POST['id']);

                $notice = array(
                            'title' => "New notice.",
                            'notice' => cleanInput($_POST['notice']),
                            'add_date' => formatDate(date('Y-m-d H:i:s')),
                            'add_by' => $this->session->lname . ' '. $this->session->fname
                        );
                if($this->ext_bill->append_notice($id_ext_bill, json_encode($notice)))
                    jsonResponse ("Your notice has been successfuly updated.", "success", $notice);
                jsonResponse("Error. Can't add new notice. Try later");
            break;
            case 'confirm_external_bill':
                checkAdminAjax('manage_content');

                $id_ext_bill = $this->uri->segment(4);
                $this->load->model('Billing_Model', 'billing');

                $notice = array(
                    'title' => "The external bill has been processed.",
                    'notice' => '',
                    'add_date' => formatDate(date('Y-m-d H:i:s')),
                    'add_by' => $this->session->lname . ' '. $this->session->fname
                );

                if($this->billing->update_ext_bill($id_ext_bill, array('status' => 'processed'))){
                    $this->billing->append_notice($id_ext_bill, json_encode($notice));
                    jsonResponse("The external bill has been processed successfully.", "success");
                }
                jsonResponse("Error: Cannot process this external bill. Please try again later.");

            break;
            case 'delete_external_bill':
                checkAdminAjax('manage_content');

                $id_request = $this->uri->segment(4);
                $this->load->model('Billing_Model', 'billing');
                $request = $this->billing->get_ext_bill($id_request);

                if(empty($request)){
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                if($request['special_request'] == 0){
                    jsonResponse('Error: This request type cannot be deleted.');
                }

                $this->billing->delete_external_bill_request($id_request);
                jsonResponse("The external bill request has been deleted successfully.", "success");
            break;
            case 'add_request' :
                checkAdminAjax('manage_content');
                $action = $this->uri->segment(4);
                switch($action){
                    default:
                    case 'by_order' :
                        $this->load->model('User_Model', 'user');
                        $this->load->model('Orders_Model', 'orders');
                        $this->load->model('Shippers_Model', 'shippers');
                        $this->load->model('Billing_Model', 'billing');
                        $id_order = (int)$this->uri->segment(5);
                        $order_detail = $this->orders->get_order($id_order);
                        if(empty($order_detail))
                            jsonResponse('Error: The order '.orderNumber($id_order).' does not exist.');

                        $finished_statuses = array(
                            'order_completed',
                            'late_payment',
                            'canceled_by_buyer',
                            'canceled_by_seller',
                            'canceled_by_ep'
                        );
                        if(!in_array($order_detail['status_alias'], $finished_statuses)){
                            jsonResponse('Please complet the order before perform this action.', 'warning');
                        }

                        $external_bills_requests = array();
                        if(!empty($_POST['refund_buyer'])){
                            $user_info = $this->user->getSimpleUser($order_detail['id_buyer'], 'users.fname, users.lname, users.email');
                            $comment = 'To refund the buyer ' . $user_info['fname'] . ' ' . $user_info['lname'] . ', ' . $user_info['email'] . ', according to the order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $id_order . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($id_order) . '</a>';
                            $refund_buyer_notes = cleanInput($_POST['refund_buyer_notes']);
                            if($refund_buyer_notes != '')
                                $comment .= '<br>' . '<strong>Notes: </strong>'. $refund_buyer_notes;

                            $refund_amount = (float)$_POST['refund_buyer'];
                            $insert = array(
                                'to_user' => $order_detail['id_buyer'],
                                'type' => 'buyer',
                                'money' => $refund_amount,
                                'comment' => $comment,
                                'date_time' => date('Y-m-d H:i:s'),
                                'notice' => json_encode(
                                    array(
                                        'title' => "The external bill request has been initiated.",
                                        'notice' => $comment,
                                        'add_date' => formatDate(date('Y-m-d H:i:s')),
                                        'add_by' => user_name_session()
                                    )
                                )
                            );
                            $external_bills_requests[] = $this->billing->create_ext_bill($insert);
                        }

                        if(!empty($_POST['pay_seller'])){
                            $seller_info = $this->user->getSimpleUser($order_detail['id_seller'], 'users.fname, users.lname, users.email');

                            $comment = 'To pay the seller ' . $seller_info['fname'] . ' ' . $seller_info['lname'] . ' ( ' . $order_detail['id_seller'] . ' ), ' . $seller_info['email'] . ', according to the order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $id_order . '" class="fancybox.ajax fancybox" data-title="Order detail">' . orderNumber($id_order) . '</a>';
                            $pay_seller_notes = cleanInput($_POST['pay_seller_notes']);
                            if($pay_seller_notes != '')
                                $comment .= '<br>' . '<strong>Notes: </strong>'. $pay_seller_notes;

                            $pay_seller_amount = (float)$_POST['pay_seller'];
                            $insert = array(
                                'to_user' => $order_detail['id_seller'],
                                'type' => 'seller',
                                'money' => $pay_seller_amount,
                                'comment' => $comment,
                                'date_time' => date('Y-m-d H:i:s'),
                                'notice' => json_encode(
                                    array(
                                        'title' => "The external bill request has been initiated.",
                                        'notice' => $comment,
                                        'add_date' => formatDate(date('Y-m-d H:i:s')),
                                        'add_by' => user_name_session()
                                    )
                                )
                            );
                            $external_bills_requests[] = $this->billing->create_ext_bill($insert);
                        }

                        if(!empty($_POST['pay_shipper']) && $order_detail['shipper_type'] == 'ep_shipper'){
                            $shipper_info = $this->shippers->get_shipper_by_user($order_detail['id_shipper']);
                            $comment = 'To pay the freight forwarder ' . $shipper_info['co_name'] . ' ( ' . $order_detail['id_shipper'] . ' ), email:  ' . $shipper_info['email'] . ', according to the order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $id_order . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($id_order) . '</a>';

                            $pay_shipper_notes = cleanInput($_POST['pay_shipper_notes']);
                            if($pay_shipper_notes != '')
                                $comment .= '<br>' . '<strong>Notes: </strong>'. $pay_shipper_notes;

                            $pay_shipper_amount = (float)$_POST['pay_shipper'];
                            $insert = array(
                                'to_user' => $order_detail['id_shipper'],
                                'type' => 'shipper',
                                'money' => $pay_shipper_amount,
                                'comment' => $comment,
                                'date_time' => date('Y-m-d H:i:s'),
                                'notice' => json_encode(
                                    array(
                                        'title' => "The external bill request has been initiated.",
                                        'notice' => $comment,
                                        'add_date' => formatDate(date('Y-m-d H:i:s')),
                                        'add_by' => user_name_session()
                                    )
                                )
                            );
                            $external_bills_requests[] = $this->billing->create_ext_bill($insert);
                        }
                        if(empty($external_bills_requests))
                            jsonResponse('Error: You did not select any user for external bill request.');

                        if(empty($order_detail['external_bills_requests'])){
                            $update['external_bills_requests'] = implode(',',$external_bills_requests);
                        } else{
                            $update['external_bills_requests'] = $order_detail['external_bills_requests'] . ',' . implode(',',$external_bills_requests);
                        }

                        $this->orders->change_order($id_order, $update);
                        jsonResponse('The external bill request has been initiated.', 'success');
                    break;
                    case 'refund' :
                        $validator_rules = array(
                            array(
                                'field' => 'bill',
                                'label' => 'Bill info',
                                'rules' => array('required' => '', 'integer' => '')
                            ),
                            array(
                                'field' => 'refund_amount',
                                'label' => 'Refund amount',
                                'rules' => array('required' => '', 'positive_number' => '', 'min[0]' => '')
                            )
                        );

                        $this->validator->set_rules($validator_rules);
                        if (!$this->validator->validate())
                            jsonResponse($this->validator->get_array_errors());

                        $this->load->model('Billing_Model', 'billing');
                        $this->load->model('User_Bills_Model', 'user_bills');
                        $this->load->model('User_Model', 'user');
                        $id_bill = (int)$_POST['bill'];
                        $bill_info = $this->user_bills->get_user_bill($id_bill);

                        if (empty($bill_info)) {
                            jsonResponse('Info: This bill does not exist.', 'info');
                        }

                        if($bill_info['refund_bill_request']){
                            jsonResponse('This bill already has an refund request.', 'info');
                        }

                        $user_info = $this->user->getUser($bill_info['id_user']);
                        $comment = 'To refund the user ' . $user_info['fname'] . ' ' . $user_info['lname'] . ', ' . $user_info['email'] . ', according to the bill <a href="' . __SITE_URL . 'payments/popups_payment/payment_detail_admin/'.$bill_info['id_bill'].'?type_bill='.$bill_info['name_type'].'" class="fancybox.ajax fancybox" data-title="Bill details">' . orderNumber($bill_info['id_bill']) . '</a>';
                        $notes = cleanInput($_POST['notes']);
                        if($notes != '')
                            $comment .= '<br>' . '<strong>Notes: </strong>'. $notes;
                        $refund_amount = (float)$_POST['refund_amount'];
                        $insert = array(
                            'to_user' => $bill_info['id_user'],
                            'type' => strtolower($user_info['gr_type']),
                            'money' => $refund_amount,
                            'comment' => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'notice' => json_encode(
                                array(
                                    'title' => "The external bill request has been initiated.",
                                    'notice' => $comment,
                                    'add_date' => formatDate(date('Y-m-d H:i:s')),
                                    'add_by' => user_name_session()
                                )
                            )
                        );
                        $id_request = $this->billing->create_ext_bill($insert);

                        $bill_note = array(
                            'date_note' => date('Y-m-d H:i:s'),
                            'note' => 'The bill refund process has been started. The refund amount is $ '.get_price($refund_amount, false).'.'
                        );
                        $bill_note = json_encode($bill_note);
                        $update = array(
                            'refund_bill_request' => $id_request,
                            'note' => $bill_info['note'] .','. $bill_note
                        );
                        $this->user_bills->update_user_bill($id_bill, $update);
                        $responce = array(
                            'request' => $id_request
                        );
                        jsonResponse('The external bill request has been initiated.', 'success', $responce);
                    break;
                    case 'special' :
                        $validator_rules = array(
                            array(
                                'field' => 'type',
                                'label' => 'Request for',
                                'rules' => array('required' => '')
                            ),
                            array(
                                'field' => 'pay_amount',
                                'label' => 'Pay amount',
                                'rules' => array('required' => '', 'positive_number' => '', 'min[0]' => '')
                            ),
                            array(
                                'field' => 'pay_notes',
                                'label' => 'Notes',
                                'rules' => array('required' => '')
                            )
                        );

                        $type_for = $_POST['type'];
                        switch($type_for){
                            case 'buyer' :
                            case 'seller' :
                                $validator_rules[] = array(
                                    'field' => 'user',
                                    'label' => 'User',
                                    'rules' => array('required' => '', 'integer' => '')
                                );
                            break;
                            case 'shipper' :
                                $validator_rules[] = array(
                                    'field' => 'shipper',
                                    'label' => 'Freight Forwarder',
                                    'rules' => array('required' => '', 'integer' => '')
                                );
                            break;
                            default :
                                jsonResponse('Info: Please select the "Request for" field first.');
                            break;
                        }
                        $this->validator->set_rules($validator_rules);
                        if (!$this->validator->validate())
                            jsonResponse($this->validator->get_array_errors());

                        $this->load->model('Billing_Model', 'billing');

                        switch($type_for){
                            case 'buyer' :
                            case 'seller' :
                                $this->load->model('User_Model', 'user');
                                $request_to = (int)$_POST['user'];
                                $user_info = $this->user->getUser($request_to);
                                if(empty($user_info)){
                                    jsonResponse('Error: The user you have been selected does not exist.');
                                }
                                $comment = 'To refund the user ' . $user_info['fname'] . ' ' . $user_info['lname'] . ', ' . $user_info['email'];
                            break;
                            case 'shipper' :
                                $this->load->model('Shippers_Model', 'shippers');
                                $request_to = (int)$_POST['shipper'];
                                $shipper_info = $this->shippers->get_shipper_by_user($request_to);
                                if(empty($shipper_info)){
                                    jsonResponse('Error: The freight forwarder you have been selected does not exist.');
                                }
                                $comment = 'To pay the freight forwarder ' . $shipper_info['co_name'] . ' ( ' . $request_to . ' ), email:  ' . $shipper_info['email'];
                            break;
                            default :
                                jsonResponse('Info: Please select the "Request for" field first.');
                            break;
                        }

                        $notes = cleanInput($_POST['pay_notes']);
                        if($notes != '')
                            $comment .= '<br>' . '<strong>Notes: </strong>'. $notes;

                        $refund_amount = (float)$_POST['pay_amount'];
                        $insert = array(
                            'to_user' => $request_to,
                            'type' => strtolower($type_for),
                            'money' => $refund_amount,
                            'comment' => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'notice' => json_encode(
                                array(
                                    'title' => "The external bill request has been initiated.",
                                    'notice' => $comment,
                                    'add_date' => formatDate(date('Y-m-d H:i:s')),
                                    'add_by' => user_name_session()
                                )
                            ),
                            'special_request' => 1
                        );
                        $id_request = $this->billing->create_ext_bill($insert);
                        jsonResponse('The external bill request has been initiated.', 'success', $responce);
                    break;
                }
            break;
            case 'edit_request':
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'External bill',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'notice',
                        'label' => 'Notice',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'money',
                        'label' => 'Money',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $this->load->model('Billing_Model', 'ext_bill');
                $id_ext_bill = intVal($_POST['id']);
                $ext_bill = $this->ext_bill->get_ext_bill($id_ext_bill, 'money');

                if(empty($ext_bill))
                    jsonResponse(translate("systmess_error_request_does_not_exist"));

                $money = floatval($_POST['money']);

                $update = array('money' => $money);
                $additional = '';
                if($money != $ext_bill['money']){
                    $additional = "(from $".$ext_bill['money']." to $".floatval($money).")";
                }

                if($this->ext_bill->update_ext_bill($id_ext_bill, $update)){
                    $notice = array(
                        'notice' => cleanInput($_POST['notice']),
                        'title' => "The external bill has been changed. " . $additional,
                        'add_date' => formatDate(date('Y-m-d H:i:s')),
                        'add_by' => $this->session->lname . ' '. $this->session->fname
                    );
                    $this->ext_bill->append_notice($id_ext_bill, json_encode($notice));
                    jsonResponse ("External bill request has been successfuly updated.", "success", $notice);
                }else{
                    jsonResponse("Error: Can't update External bill request. Try later");
                }
            break;
            // SEARCH THE USERS FOR EXTERNAL BILL REQUEST
            case 'search_recipient' :
                $validator_rules = array(
                    array(
                        'field' => 'type',
                        'label' => 'Request for',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'keywords',
                        'label' => 'Search keywords',
                        'rules' => array('required' => '','min_len[3]'=>'')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $params = array(
                    'keywords' => cleanInput(cut_str($_POST['keywords']))
                );

                if (strlen($params['keywords']) < 3)
                    jsonResponse("Error: Field \"Search keywords\" cannot contain less than 3 characters");

                //GET ONLY THE USERS BY SPECIFIED GROUP TYPE - BUYER OR SELLER
                $type_for = cleanInput($_POST['type']);
                switch($type_for){
                    case 'buyer' :
                        $this->load->model('User_Model', 'user');
                        $this->load->model('UserGroup_Model', 'user_group');
                        $groups = $this->user_group->getGroupsByType(array('type' => "'Buyer'"));
                        if(!empty($groups)){
                            $user_groups = array();
                            foreach($groups as $group){
                                $user_groups[] = $group['idgroup'];
                            }
                            if(!empty($user_groups)){
                                $params['group'] = implode(',', $user_groups);
                            }
                        }
                    break;
                    case 'seller' :
                        $this->load->model('User_Model', 'user');
                        $this->load->model('UserGroup_Model', 'user_group');
                        $groups = $this->user_group->getGroupsByType(array('type' => "'Seller'"));
                        if(!empty($groups)){
                            $user_groups = array();
                            foreach($groups as $group){
                                $user_groups[] = $group['idgroup'];
                            }
                            if(!empty($user_groups)){
                                $params['group'] = implode(',', $user_groups);
                            }
                        }
                    break;
                    default :
                        jsonResponse('Info: Please select the user type.');
                    break;
                }

                $users = $this->user->getUsers($params);
                if(empty($users)){
                    jsonResponse('Info: There are no users found by this keywords.', 'info');
                }

                jsonResponse('', 'success', array('users' => $users, 'type' => $type_for));
            break;
        }
    }
}
?>

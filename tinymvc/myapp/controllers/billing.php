<?php

use App\Common\Buttons\ChatButton;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\QueryException;
use App\Common\Traits\ModalUriReferenceTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Email\EmailUserAboutBill;
use App\Services\SampleServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use const App\Common\DB_DATE_FORMAT;
use const App\Logger\Activity\OperationTypes\CONFIRM_BILLING;
use const App\Logger\Activity\ResourceTypes\BILLING;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Billing_Controller extends TinyMVC_Controller
{
    use ModalUriReferenceTrait;
    private const SAMPLE_ORDER_BILL_TYPE = 7;

    private $breadcrumbs = array();

    private function _load_main() {
		$this->load->model('Category_Model', 'category');
		$this->load->model('Orders_model', 'orders');
		$this->load->model('User_model', 'user');
		$this->load->model('User_Bills_Model', 'user_bills');
    }

	public function update_search(){
		$this->load->model('User_Bills_Model', 'user_bills');
		$this->user_bills->update_bills_search_info();
	}

    public function administration() {
        checkAdmin('manage_bills');

        $this->_load_main();
        $data['bills_types'] = $this->user_bills->get_bills_types();
        $data['bills_types'] = arrayByKey($data['bills_types'], 'id_type');
        $data['pay_methods'] = $this->orders->get_pay_methods();
        $data['last_bills_id'] = $this->user_bills->get_bills_last_id();
        $data['filters'] = array(
            'bill' => request()->query->getInt('bill'),
        );

        $this->view->assign($data);
        $this->view->assign('title', 'Billing');
        $this->view->display('admin/header_view');
        $this->view->display('admin/billing/index_view');
        $this->view->display('admin/footer_view');
    }

    public function my() {
		if (!logged_in()) {
			$this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
			headerRedirect(__SITE_URL . 'login');
		}

		if (user_type('users_staff')) {
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"), 'errors');
			headerRedirect();
		}

		$this->_load_main();
		$this->load->model('Video_Tour_model', 'video_tour');

		$id_user = id_session();

		$conditions = array('id_user' => $id_user);

		if(!have_right('feature_item')){
			$conditions['exclude_statuses'][] = 3;
		}

		if(!have_right('highlight_item')){
			$conditions['exclude_statuses'][] = 4;
		}

		$data['count_bills'] = arrayByKey($this->user_bills->count_user_bills_by_status($conditions), 'status');
		$count_bills_status_type = $this->user_bills->count_user_bills_by_status_type($conditions);

		foreach ($count_bills_status_type as $item) {
			$data['count_bills'][$item['status']][$item['name_type']] = $item['counter'];
		}

		$uri = $this->uri->uri_to_assoc();

        $data['active_status'] = 'all';

        // GET SELECTED STATUS FROM URI - IF EXIST
		if(isset($uri['status'])){
        	$data['active_status'] = $uri['status'];
		}

		if(user_group_type() == 'Buyer'){

			$data['active_type'] = 'order';
            $data['types_array'] = $this->user_bills->get_bills_types_array();

            unset($data['types_array']['feature_item'], $data['types_array']['highlight_item']);

		} elseif(user_group_type() == 'Seller'){
            $data['types_array'] = $this->user_bills->get_bills_types_array();

			if(!have_right('feature_item')){
				unset($data['types_array']['feature_item']);
			}

			if(!have_right('highlight_item')){
                unset($data['types_array']['highlight_item']);
            }

            unset($data['types_array']['order'], $data['types_array']['ship']);

			$type_keys = array_keys($data['types_array']);
			$data['active_type'] = $type_keys[0];
		} else{
			$data['active_type'] = '';
		}
        // GET SELECTED STATUS FROM URI - IF EXIST

        $data['active_type'] = $uri['type'] ?? 'all';

        // ARRAY WITH FULL STATUSES DETAILS
        $data['status_array'] = $this->user_bills->get_bills_statuses();

        // GET SELECTED BILL NUMBER FROM URI - IF EXIST
		if(isset($uri['bill'])){
        	$data['id_bill'] = toId($uri['bill']);
			$data['active_status'] = 'bill_number';
		}

        // GET SELECTED ITEM NUMBER FROM URI - IF EXIST
		if(isset($uri['order'])){
        	$data['id_item'] = toId($uri['order']);
			$data['active_status'] = 'order_number';
		}
		if(isset($uri['featured'])){
        	$data['id_item'] = toId($uri['featured']);
			$data['active_status'] = 'featured_number';
		}
		if(isset($uri['highlight'])){
        	$data['id_item'] = toId($uri['highlight']);
			$data['active_status'] = 'highlight_number';
		}
		if(isset($uri['group'])){
        	$data['id_item'] = toId($uri['group']);
			$data['active_status'] = 'group_number';
		}
		if(isset($uri['right'])){
        	$data['id_item'] = toId($uri['right']);
			$data['active_status'] = 'right_number';
		}

		$data['video_tour'] = $this->video_tour->get_video_tour(array("page" => "billing/my", "user_group" => user_group_type()));

		global $tmvc;
		$data['bills_per_page'] = $tmvc->my_config['user_bills_per_page'];

        $bill_type = 'all';
        $bills_per_page = $tmvc->my_config['user_bills_per_page'];

        $data['status'] = $this->user_bills->get_bills_statuses();
        $data['types'] = $this->user_bills->get_bills_types_array();
        $page = 1;
        $per_page = $bills_per_page;
        $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

        $params_bills = array(
            'id_user' => $id_user,
            'type' => $bill_type,
            'limit' => $start_from . ", " . $per_page
        );

        if(isset($uri['type'])){
            $params_bills['type'] = $uri['type'];
        }

        if (!empty($data['id_item'])) {
            $params_bills['id_item'] = $data['id_item'];
        }

        if (isset($uri['bill'])) {
            $data['id_bill'] = $params_bills['bill_number'] = (int) $uri['bill'];
            $data['active_status'] = 'bill_number';
        }

        $data['bills'] = $this->user_bills->get_user_bills($params_bills);
        $data['status_select_count'] = $this->user_bills->count_bills_by_status_type($params_bills);

        $this->view->assign($data);
        $this->view->display("new/header_view");
        $this->view->display("new/user/bills/index_view");
        $this->view->display("new/footer_view");
    }

    public function ajax_update_sidebar_counters() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('manage_seller_orders') && !have_right('buy_item'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->_load_main();

        $id_user = id_session();

        $conditions = array('id_user' => $id_user);

		if(!have_right('feature_item')){
			$conditions['exclude_statuses'][] = 3;
		}

		if(!have_right('highlight_item')){
			$conditions['exclude_statuses'][] = 4;
		}

        $count_bills = $this->user_bills->count_user_bills_by_status($conditions);
        $count_bills_status_type = $this->user_bills->count_user_bills_by_status_type($conditions);
        $statuses_counters = array();

        foreach ($count_bills_status_type as $count_bill) {
            $statuses_counters[$count_bill['status'] . '_' . $count_bill['name_type']] = $count_bill['counter'];
        }

        foreach ($count_bills as $bill) {
            $statuses_counters[$bill['status'] . '_total'] = $bill['counter'];
        }

        jsonResponse('', 'success', array('counters' => $statuses_counters));
    }

    public function ajax_bills_administration() {
        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $params = array('per_p' => (int) $_POST['iDisplayLength'], 'start' => (int) $_POST['iDisplayStart']);
        $this->_load_main();

        if (isset($_POST['status'])) {

            $params['status'] = "'".cleanInput($_POST['status'])."'";

            switch ($params['status']) {
                case 'init': $params['date_column'] = 'create_date';break;
                case 'paid': $params['date_column'] = 'pay_date';break;
                case 'confirmed': $params['date_column'] = 'confirmed_date';break;
                case 'unvalidated': $params['date_column'] = 'decline_date';break;
                default: $params['date_column'] = 'create_date';
            }
        }

        $params['realUsers'] = 1;
        $conditions = [
            ['as' => 'expire_status',           'key' => 'expire_status',           'type' => 'cleanInput'],
            ['as' => 'amount_from',             'key' => 'amount_from',             'type' => 'float'],
            ['as' => 'amount_to',               'key' => 'amount_to',               'type' => 'float'],
            ['as' => 'id_item',                 'key' => 'item',                    'type' => 'int'],
            ['as' => 'bills_type',              'key' => 'bill_type',               'type' => 'int'],
            ['as' => 'id_user',                 'key' => 'user',                    'type' => 'int'],
            ['as' => 'id_bill',                 'key' => 'bill',                    'type' => 'int'],
            ['as' => 'pay_method',              'key' => 'pay_method',              'type' => 'int'],
            ['as' => 'search',                  'key' => 'search',                  'type' => 'cleanInput'],
            ['as' => 'date_column',             'key' => 'date_column',             'type' => 'cleanInput'],
            ['as' => 'date_from',               'key' => 'date_from',               'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'date_to',                 'key' => 'date_to',                 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'realUsers',               'key' => 'real_users',              'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2],
        ];

        $sort_by = [
            'dt_bill' => 'b.id_bill',
            'dt_bill_type' => 'bt.name_type',
            'dt_order' => 'b.id_item',
            'dt_buyer' => 'user_name',
            'dt_paid' => 'b.amount',
            'dt_amount' => 'b.balance',
            'dt_status' => 'b.status',
            'dt_pay_method' => 'pm.method',
            'dt_all_dates' => 'b.create_date',
        ];

        $sort_by = flat_dt_ordering($_POST, $sort_by);

        $params['sort_by'] = empty($sort_by) ? ['b.create_date-desc'] : $sort_by;
        $params['date_column'] = 'create_date';

        $params = array_merge($params, dtConditions($_POST, $conditions));

        if (2 === $params['realUsers']) {
            unset($params['realUsers']);
        }

        $bills = array();
        $bills_count = 0;

        /** @var User_Bills_Model $usersBillsModel */
        $usersBillsModel = model(User_Bills_Model::class);

        $params['count'] = $bills_count = $usersBillsModel->get_bills_count($params);
        $bills = $usersBillsModel->get_user_bills($params);
        $bills_statuses_count = arrayByKey($usersBillsModel->get_bills_counts_by_status($params), 'status');

        $bills_statuses_count['expire_soon'] = array(
            'status' => 'expire_soon',
            'counter' => $usersBillsModel->get_soon_expire_bills_count($params)
        );
        $bills_statuses_count['expired'] = array(
            'status' => 'expired',
            'counter' => $usersBillsModel->get_expired_bills_count($params)
        );

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $bills_count,
            "iTotalDisplayRecords" => $bills_count,
            "bills_statuses_count" => $bills_statuses_count,
            "aaData" => array()
        );

		if(empty($bills))
			jsonResponse('', 'success', $output);

        $status = $this->user_bills->get_bills_statuses();
        $bill_type_icon = $this->user_bills->get_bills_types_array();

        switch($params['date_column']){
            default:
            case 'create_date':
                $create_date_active = 'txt-bold';
            break;
            case 'pay_date':
                $pay_date_active = 'txt-bold';
            break;
            case 'confirmed_date':
                $confirmed_date_active = 'txt-bold';
            break;
            case 'change_date':
                $change_date_active = 'txt-bold';
            break;
            case 'declined_date':
                $declined_date_active = 'txt-bold';
            break;
            case 'due_date':
                $due_date_active= 'txt-bold';
            break;
        }

        foreach ($bills as $row) {
            $btn_confirm = '';
            $btn_reimburse = '';
            if($row['status'] == 'confirmed' && $row['amount'] > $row['balance'] && !in_array($row['name_type'], array('order', 'ship'))){
                if(!$row['refund_bill_request']){
                    $btn_reimburse = '<a class="ep-icon ep-icon_reply-circle txt-orange fancybox.ajax fancyboxValidateModalDT" href="'.__SITE_URL.'external_bills/popup_forms/refund_form/'.$row['id_bill'].'" data-title="Refund the user" title="Refund the user"></a>';
                } else{
                    $btn_reimburse = '<a class="ep-icon ep-icon_notice fancybox.ajax fancyboxValidateModalDT" href="'.__SITE_URL.'external_bills/popup_forms/notice/'.$row['refund_bill_request'].'" data-title="Notes" title="Refund user detail"></a>';
                }
            }
            $edit_amount = '';
            if ($row['status'] == 'paid') {
                $btn_confirm = '<a class="ep-icon ep-icon_ok txt-green confirm-dialog" data-callback="confirm_bill" data-bill="' . $row['id_bill'] . '" href="#" title="Confirm Bill"  data-message="Are you sure you want to confirm this payment?"></a>
                            <a class="ep-icon ep-icon_remove txt-red fancybox.ajax fancyboxValidateModal" data-title="Decline Bill" href="'.__SITE_URL.'billing/popup_forms/decline_bill/' . $row['id_bill'] . '" title="Decline Bill"></a>';

                $edit_amount .= '<div class="amount-edit-' . $row['id_bill'] . '" style="display: none;">
                                    <input class="w-70" type="text" name="amount" value="' . $row['amount'] . '"/>
                                    <a class="ep-icon ep-icon_ok txt-green lh-30 mb-0 confirm-dialog" title="Confirm amount"  data-message="Are you sure you want to change the bill amount?" data-callback="confirm_new_amount" data-bill="' . $row['id_bill'] . '"></a>
                                    <a class="ep-icon ep-icon_remove txt-red lh-30 mb-0 call-function" data-callback="cancel_amount" title="Cancel amount" data-bill="' . $row['id_bill'] . '"></a>
                                </div>
                                <a class="ep-icon ep-icon_pencil mb-0 btn-edit-amount call-function" data-callback="edit_amount" title="Edit amount" data-bill="' . $row['id_bill'] . '"></a>';
            }

            if ($row['method']) {
                $btn_pay_method = '<div class="pull-left"><a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Paid by ' . $row['method'] . '" title="Paid by ' . $row['method'] . '" data-value-text="' . $row['method'] . '" data-value="' . $row['pay_method'] . '" data-name="pay_method"></a></div><div class="clearfix"></div>
                            <i class="ico-pay-method i-' . strForURL($row['method']) . '"></i> <span class="lh-21 pl-3">' . $row['method'] . '</span>';
            } else {
                $btn_pay_method = '<div class="tac">--</div>';
            }

            $item_link = orderNumber($row['id_item']);
            if(in_array($row['name_type'], array('order', 'ship'))){
                $item_link = '<a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $row['id_item'] . '" data-title="Order details" class="fancybox.ajax fancybox">' . orderNumber($row['id_item']) . '</a>';
            }

            $document_link = '';
            if(!empty($row['payment_form'])){
            	$document_link = '<a class="ep-icon ep-icon_upload" href="' . __SITE_URL . 'payments/save_bill_document/'.$row['id_bill'].'" title="Download payment form"></a>';
            }

            $extend_btn = '';
            if(in_array($row['status'], array('init', 'paid'))){
                if ($row['extend_request']){
                    $extend_btn = '<a href="' . __SITE_URL . 'extend/popup_form/detail_admin/' . $row['extend_request'] . '" title="Extend request" data-title="Extend request" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_hourglass-plus txt-orange"></i></a>';
                } else{
                    $extend_btn = '<a href="' . __SITE_URL . 'extend/popup_form/extend_time/bill/' . $row['id_bill'] . '" title="Extend payment time" data-title="Extend payment time" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_hourglass-plus txt-green"></i></a>';
                }
            }

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $row['id_user'], 'recipientStatus' => 'active', 'module' => 1, 'item' => $row['id_bill']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            $output['aaData'][] = array(
                'dt_checkbox' => '<input class="checkbox-17-dark-gray" type="checkbox" name="" value="' . $row['id_type_bill'] . '-' . $row['name_type'] . '-' . $row['id_bill'] . '-' . $row['status'] . '-' . $row['id_item'] . '-' . $row['id_user'] . '"/>',
                'dt_bill' =>
					'<div class="tal">
						'.$document_link.'
						<a class="ep-icon ep-icon_clock fancybox fancybox.ajax" href="' . __SITE_URL . 'billing/popup_forms/bill_timeline/'.$row['id_bill'].'" title="View bill timeline" data-title="Bill ' . orderNumber($row['id_bill']) . ' - Timeline"></a>
					</div>
                    <a class="fancybox.ajax fancybox" href="' . __SITE_URL . 'payments/popups_payment/payment_detail_admin/' . $row['id_bill'] . '?type_bill=order" data-title="Bill details">' . orderNumber($row['id_bill']) . '</a>',
                'dt_bill_type' => '<a class="dt_filter" data-title="Bill type" title="Filter by bill type ' . $row['show_name'] . '" data-value-text="' . $row['show_name'] . '" data-value="' . $row['id_type_bill'] . '" data-name="bill_type"><i class="'.$bill_type_icon[$row['name_type']].'"></i> ' . $row['show_name'] . '</a>',
                'dt_order' => '<div class="pull-left"><a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Item" title="Filter by item ' . orderNumber($row['id_item']) . '" data-value-text="' . orderNumber($row['id_item']) . '" data-value="' . $row['id_item'] . '" data-name="item"></a></div><div class="clearfix"></div>' . $item_link,
                'dt_buyer' =>
                '<div class="pull-left">'
                . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $row['user_name'] . '" data-value-text="' . $row['user_name'] . '" data-value="' . $row['id_user'] . '" data-name="user"></a>'
                . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $row['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($row['user_name'] . ' ' . $row['id_user']) . '"></a>'
                . $btnChat
                . '<a class="ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'contact/popup_forms/email_user/' . $row['id_user'] . '" title="Email this user" data-title="Email user '. $row['user_name'] .'"></a>'
                . '</div>'
                . '<div class="clearfix"></div>'
                . '<span>' . $row['user_name'] . '<br>'.$row['email'].'</span>',
                'dt_amount' => '$' . get_price($row['balance'], false),
                'dt_status' => '<div class="pull-left">
                                    <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Bills status" data-value-text="' . $status[$row['status']]['title'] . '" data-value="' . $row['status'] . '" data-name="status"></a>
                                </div>
                                <div class="clearfix"></div>
                                <span class="status-b"><i class="ep-icon ep-icon_' . $status[$row['status']]['icon'] . ' fs-30"></i><br> ' . $status[$row['status']]['title'] . '</span>',
                'dt_pay_method' => $btn_pay_method,
                'dt_all_dates' => '<div class="tal">
                    <a class="dt_filter '.$create_date_active.'" data-title="Date type" title="Date type" data-value-text="Create date" data-value="create_date" data-name="date_column">
                        Created: '.formatDate($row['create_date'])
                    .'</a><br>
                    <a class="dt_filter '.$due_date_active.'" data-title="Date type" title="Date type" data-value-text="Due date" data-value="due_date" data-name="date_column">
                        Pay due: '.formatDate($row['due_date'])
                    .'</a><br>
                    <a class="dt_filter '.$pay_date_active.'" data-title="Date type" title="Date type" data-value-text="Paid date" data-value="pay_date" data-name="date_column">
                        Paid: '.formatDate($row['pay_date'])
                    .'</a><br>
                    <a class="dt_filter '.$confirmed_date_active.'" data-title="Date type" title="Date type" data-value-text="Confirmed date" data-value="confirmed_date" data-name="date_column">
                        Confirmed: '.formatDate($row['confirmed_date'])
                    .'</a><br>
                    <a class="dt_filter '.$declined_date_active.'" data-title="Date type" title="Date type" data-value-text="Declined date" data-value="declined_date" data-name="date_column">
                        Declined: '.formatDate($row['declined_date'])
                    .'</a><br>
                    <a class="dt_filter '.$change_date_active.'" data-title="Date type" title="Date type" data-value-text="Change date" data-value="change_date" data-name="date_column">
                        Changed: '.formatDate($row['change_date'])
                    .'</a>
                </div>',
                'dt_actions' => $btn_confirm.$btn_reimburse.$extend_btn
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $op = $this->uri->segment(3);
        switch($op){
            case 'decline_bill':
                // CHECK USER RIGHTS - EP BILLING MANAGER
                if(!have_right('manage_bills')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

				$id_bill = intVal($this->uri->segment(4));
                $data['bill_info'] = $this->user_bills->get_user_bill($id_bill);

                if(empty($data['bill_info'])){
                    messageInModal('Error: This bill does not exist.');
                }

                if ($data['bill_info']['status'] != 'paid'){
                    messageInModal('Error: The bill has not been paid.');
                }

                $data['user_info'] = $this->user->getSimpleUser($data['bill_info']['id_user']);
                if(empty($data['user_info'])){
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

                $data['user_info']['photo'] = getDisplayImageLink(array('{ID}' => $data['user_info']['idu'], '{FILE_NAME}' => $data['user_info']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $data['user_info']['user_group'] ));
                $data['notification_messages'] = arrayByKey($this->user->get_notification_messages(array('message_module' => 'billing')), 'id_message');

				$this->view->assign($data);
                $this->view->display('admin/billing/decline_reason_form_view');
            break;
            case 'bill_timeline':
                if(!have_right('manage_bills'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $id_bill = (int)$this->uri->segment(4);
                $data['bill_info'] = $this->user_bills->get_user_bill($id_bill);
                if (empty($data['bill_info'])) {
                    messageInModal('Error: This bill does not exist.');
                }

                $this->view->assign($data);
                $this->view->display('admin/billing/bill_detail_view');
            break;
			case 'bill_detail':
				$id_bill = (int) $this->uri->segment(4);
				$bill_info = model('user_bills')->get_simple_bill(array(
					'id_bill' => $id_bill,
					'id_user' => privileged_user_id()
				));

				if (empty($bill_info)) {
					messageInModal(translate('systmess_info_no_bills_for_payment'), 'info');
                }

                $transaction_details = null;
                if(!empty($bill_info['pay_detail'])){
                    $transaction_details = model('user_bills')->get_encrypt_data($id_bill, array('pay_detail'));
                    $transaction_details = unserialize($transaction_details['pay_detail']);
                }


                $status = $this->user_bills->get_bills_statuses();
                views(
                    array('new/billing/popup_bill_detail_view'),
                    compact(
                        'bill_info',
                        'transaction_details',
                        'status'
                    )
                );
            break;
            case 'user_bills':
                checkPermisionAjaxModal('moderate_content');

                //region User
                $user_id = (int) uri()->segment(4);
                if (
                    empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))
                ) {
                    messageInModal('The user is not found on this server.');
                }
                //endregion User

                //region Upgrade packages
                $upgrade_package = null;
                if (!empty($upgrade_request = model('upgrade')->get_latest_request(array(
                    'with'       => array(
                        'package' => function (RelationInterface $relation) {
                            $table = $relation->getRelated()->getTable();
                            $relation
                                ->getQuery()
                                    ->leftJoin($table, 'user_groups', 'user_groups', "user_groups.idgroup = {$table}.gr_to")
                            ;
                        }
                    ),
                    'conditions' => array(
                        'user'           => (int) $user_id,
                        'status'         => array('new'),
                        'is_not_expired' => true,
                    ),
                )))) {
                    $upgrade_package = arrayGet($upgrade_request, 'package');
                    if (empty($upgrade_package)) {
                        messageInModal(translate('systmess_info_no_bills_for_payment'), 'info');
                    }
                }
                //endregion Upgrade packages

                //region Bills
                $status = uri()->segment(5);
                $bill_status = !empty($status) ? "'" . cleanInput($status) . "'" : null;
                $bill_statuses = model('user_bills')->get_bills_statuses();
                $bills = model('user_bills')->get_user_bills(
                    array_filter(
                        array(
                            'encript_detail' => 1,
                            'bills_type'     => '5',
                            'pagination'     => false,
                            'id_user'        => $user_id,
                            'status'         => $bill_status,
                        ),
                        function ($param) { return null !== $param; }
                    )
                );
                if (empty($bills)) {
                    messageInModal(translate('systmess_info_no_bills_for_payment'), 'info');
                }
                //endregion Bills

                //region Reference title
                $reference_titles = array(
                    'init'        => 'New bills',
                    'paid'        => 'Paid bills',
                    'confirmed'   => 'Confirmed bills',
                    'unvalidated' => 'Cancelled bills',
                );
                $reference_title = arrayGet($reference_titles, $status, 'All bills');
                //endregion Reference title

                views('admin/billing/bills_view', array(
                    'user'                => $user,
                    'bills'               => $bills,
                    'status'              => $bill_statuses,
                    'group_package'       => $upgrade_package,
                    'return_to_modal_url' => $this->makeUriReferenceQuery(
                        'bills_popup',
                        rtrim("/billing/popup_forms/bills/{$user_id}/{$status}", '/'),
                        'View bills',
                        array(),
                        array(),
                        true,
                        $reference_title
                    ),
                ));


                break;
        }
    }

    public function ajax_bill_operations() {
        if (!isAjaxRequest())
            headerRedirect();

        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Invoices_Model', 'invoices');

        $type = $this->uri->segment(3);
        $id_user = $this->session->id;

        switch ($type) {
            case 'check_new':
                if (!have_right('manage_bills'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $lastId = $_POST['lastId'];
                $bills_count = $this->user_bills->get_count_new_bills($lastId);

                if ($bills_count) {
                    $last_bills_id = $this->user_bills->get_bills_last_id();
                    jsonResponse('', 'success', array('nr_new' => $bills_count, 'lastId' => $last_bills_id));
                } else{
                    jsonResponse(translate('systmess_bill_does_not_exist_error_message'));
                }
            break;
            case 'change_amount':
                if (!have_right('manage_bills'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_bill = cleanInput($_POST['bill']);
                $new_amount = floatVal($_POST['amount']);
                $bill_info = $this->user_bills->get_user_bill($id_bill);
                if (empty($bill_info)) {
                    jsonResponse('Error: This bill does not exist.');
                }

                if (in_array($bill_info['id_type_bill'], array(1, 2))) {
                    $type = '1,2';
                    $order_detail = $this->orders->get_order($bill_info['id_item']);
                    $total_balance = $order_detail['final_price'] + $order_detail['ship_price'];
                } else {
                    $type = $bill_info['id_type_bill'];
                    $total_balance = $bill_info['total_balance'];
                }

                $total_paid = $this->user_bills->summ_bills_by_item($bill_info['id_user'], $bill_info['id_item'], "'paid', 'confirmed'", $type);
                $new_total_amount = $total_paid - $bill_info['amount'] + $new_amount;
                $rez = array(
                    'total_paid' => '$' . get_price($new_total_amount, false),
                    'total_balance' => '$' . get_price($total_balance - $new_total_amount, false),
                    'paid' => '$' . get_price($new_amount, false)
                );

                if ($this->user_bills->change_user_bill($id_bill, array('amount' => $new_amount, 'note' => json_encode(array('date_note' => date('Y-m-d H:i:s'), 'note' => 'Bill amount was changed to: $ ' . get_price($new_amount, false) . '.'))))) {
                    $this->load->model('User_Model', 'users');
                    $this->load->model('Notify_Model', 'notify');

                    $date_send = date('Y-m-d H:i:s');

                    if (in_array($bill_info['id_type_bill'], array(1, 2))) {
                        // Update Order log
                        $order_log = array(
                            'date' => $date_send,
                            'user' => 'EP Manager',
                            'message' => cleanInput('The amount for "' . $bill_info['show_name'] . '" bill: ' . orderNumber($id_bill) . ' has been changed from $' . $bill_info['amount'] . ' to $ ' . get_price($new_amount, false) . '.')
                        );
                        $this->orders->change_order_log($bill_info['id_item'], json_encode($order_log));
                    }

					$data_systmess = [
						'mess_code' => 'bill_change_amount',
						'id_item'   => $id_bill,
						'id_users'  => [$bill_info['id_user']],
						'replace'   => [
							'[BILL_ID]'   => orderNumber($id_bill),
							'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[FROM]'      => $bill_info['amount'],
							'[TO]'        => $new_amount
						],
						'systmess' => true
					];

                    $this->notify->send_notify($data_systmess);
                    jsonResponse('The bill ' . orderNumber($id_bill) . ' amount has been successfully changed.', 'success', $rez);
                } else {
                    jsonResponse('Error: You cannot perform this operation now. Please try again late.');
                }
            break;
            case 'decline_bill':
                checkPermisionAjax('manage_bills');

                $validator_rules = array(
					array(
						'field' => 'notification_message',
						'label' => 'Decline reason',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'reason_text',
						'label' => 'Message',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'bill',
						'label' => 'Bill info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

                if($_POST['notification_message'] == 'other'){
                    $validator_rules[] = array(
                        'field' => 'subject',
						'label' => 'Decline reason title',
						'rules' => array('required' => '')
                    );
                }

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $idBill = (int) $_POST['bill'];
                $bill_info = $this->user_bills->get_user_bill($idBill);
                if(empty($bill_info)){
                    jsonResponse('This bill does not exist.');
                }

                if ($bill_info['status'] != 'paid'){
                    jsonResponse('The bill has not been paid yet.');
                }

                $user = $this->user->getSimpleUser($bill_info['id_user']);

                if($_POST['notification_message'] == 'other'){
                    $subject = cleanInput($_POST['subject']);
                } else{
                    $notification_message = $this->user->get_notification_message(intval($_POST['notification_message']));
                    if(empty($notification_message)){
                        jsonResponse('Error: This message does not exist.');
                    }
                    $subject = $notification_message['message_title'];
                }

                $reasonText = cleanInput($_POST['reason_text']);
                $date_send = date('Y-m-d H:i:s');
                $update_bill = array(
                    'status' => 'unvalidated',
                    'declined_date' => $date_send,
                    'note' => json_encode(
                        array(
                            'date_note' => $date_send,
                            'note' => 'The bill has been declined by EP manager.<br><strong>Reason: </strong>' . $reasonText
                        )
                    )
                );

                $billNumber = orderNumber($bill_info['id_bill']);

                if(!$this->user_bills->change_user_bill($idBill, $update_bill)){
                    jsonResponse('Error: The bill has not been declined.');
                }

                $insert_new_bill = array(
                    'bill_description' => $bill_info['bill_description'].' This bill has been created based on bill ' . $billNumber .', because the previous bill has been declined.',
                    'id_user' => $bill_info['id_user'],
                    'notes' => $bill_info['notes'],
                    'balance' => $bill_info['balance'],
                    'id_type_bill' => $bill_info['id_type_bill'],
                    'id_item' => $bill_info['id_item'],
                    'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
                );
                $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);
                $new_bill_number = orderNumber($id_new_bill);
                // NOTIFY ABOUT DECLINED BILL
                $this->load->model('Notify_Model', 'notify');

				$data_systmess = [
					'mess_code' => 'bill_declined',
					'id_item'   => $idBill,
					'id_users'  => [$bill_info['id_user']],
					'replace'   => [
						'[BILL_ID]'       => $billNumber,
						'[BILL_LINK]'     => __SITE_URL . 'billing/my/bill/' . $idBill,
						'[NEW_BILL_ID]'   => $new_bill_number,
						'[NEW_BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill
					],
					'systmess' => true,
				];

                $this->notify->send_notify($data_systmess);

				// NOTIFY ABOUT NEW BILL
				$data_systmess = [
					'mess_code' => 'bill_created',
					'id_item'   => $id_new_bill,
					'id_users'  => [$bill_info['id_user']],
					'replace'   => [
						'[BILL_ID]'   => $new_bill_number,
						'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
						'[BALANCE]'   => '$' . get_price($bill_info['balance'], false)
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

                // UPDATE ORDER LOG
                if(in_array($bill_info['name_type'], array('order', 'ship'))){
                    $order_log_decline_bill = array(
                        'date' => formatDate($date_send, 'm/d/Y h:i:s A'),
                        'user' => 'EP Manager',
                        'message' => 'The paid bill: ' . $billNumber . ' has been declined.'
                    );
                    $order_log_new_bill = array(
                        'date' => formatDate($date_send, 'm/d/Y h:i:s A'),
                        'user' => 'EP Manager',
                        'message' => 'The new bill: ' . $new_bill_number . ' has been created on the basis of bill ' . $billNumber . ', because the previous bill was declined.'
                    );
                    $order_log = json_encode($order_log_decline_bill) . ',' . json_encode($order_log_new_bill);
                    $this->orders->change_order_log($bill_info['id_item'], $order_log);
                }

                if ($bill_info['name_type'] === 'sample_order') {
                    $sample_order = model('sample_orders')->findOneBy(array('conditions' => array('sample' => $bill_info['id_item'])));
                    if (empty($sample_order)) {
                        jsonResponse('This sample order does not exist.');
                    }

                    try {
                        $order_timeline = json_decode($sample_order['purchase_order_timeline'], true, JSON_THROW_ON_ERROR);
                    } catch (\Exception $exception) {
                        $order_timeline = array();
                    }

                    $log_date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date_send)->format(DATE_ATOM);

                    $order_timeline_addition = array(
                        array(
                            'date' => $log_date,
                            'user' => 'EP Manager',
                            'message' => 'The paid bill: ' . $billNumber . ' has been declined.'
                        ),
                        array(
                            'date' => $log_date,
                            'user' => 'EP Manager',
                            'message' => 'The new bill: ' . $new_bill_number . ' has been created on the basis of bill ' . $billNumber . ', because the previous bill was declined.'
                        )
                    );

                    $purchase_order_timeline = (new ArrayCollection(array_merge($order_timeline, $order_timeline_addition)))->toArray();

                    try {
                        if ( ! model('sample_orders')->updateOne($bill_info['id_item'], array('purchase_order_timeline' => $purchase_order_timeline))) {
                            throw QueryException::executionFailed(model('sample_orders')->db, null, SampleServiceInterface::STORAGE_UPDATE_ERROR);
                        }
                    } catch (\Exception $exception) {
                        if (!$exception instanceof QueryException) {
                            $exception = QueryException::executionFailed(model('sample_orders')->db, $exception, SampleServiceInterface::STORAGE_UPDATE_ERROR);
                        }

                        throw $exception;
                    }
                }

                // EMAIL USER ABOUT BILL
                $accreditationAction = $user['upgrade_package'] > 0 ? 'upgrade' : 'verify';
                $fname = $user['fname'];
                $lname = $user['lname'];
                $email = $user['email'];

                if(!empty($user['accreditation_transfer'])){
                    $transferUser = json_decode($user['accreditation_transfer'], true);

                    $fname = $transferUser['fname'];
                    $lname = $transferUser['lname'];
                    $email = $transferUser['email'];
                }

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailUserAboutBill("{$fname} {$lname}", "Payment declined for bill {$billNumber}<br><span style=\"font-style:italic;\"><strong>Reason: </strong>{$reasonText}</span>", __SITE_URL . "billing/my/bill/{$idBill}"))
                            ->to(new RefAddress((string) $user['idu'], new Address($email)))
                            ->subject("Payment declined for bill {$billNumber} - {$subject}")
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => '"Decline payment for bill '.$billNumber.'" email has been sent.'
                );

                $this->user->set_notice($bill_info['id_user'], $notice);

                jsonResponse('The bill has been successfully declined.', 'success');
            break;
            case 'confirm_bill':
                $bill = intVal($_POST['bill']);
                $bill_info = $this->user_bills->get_user_bill($bill);
                if (empty($bill_info))
                    return false;

                // Call handler operation by type: e.g. 'handler_bill_'.$type($handler_vars)
                $handler = 'handler_bill_' . $bill_info['name_type'];

                if (!method_exists('Billing_Controller', $handler)) {
                    jsonResponse('Handler does not exist.');
                }

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array($bill_info['id_user'] => array('bills_payment_confirmed' => 1)));

				$responce = $this->$handler($bill_info);

                if ($responce) {
                    //region Update Activity Log
                    $user = model('user')->getSimpleUser((int) $bill_info['id_user']);
                    $fullname = $user['fname'] . ' ' . $user['lname'];
                    $context = array_merge(
                        array(
                            'billing'     => array('id' => $bill_info['id_bill']),
                            'target_user' => array(
                                'id'      => $bill_info['id_user'],
                                'name'    => $fullname,
                                'profile' => array(
                                    'url' => getUserLink($fullname, $bill_info['id_user'], $user['gr_type'])
                                )
                            )
                        ),
                        get_user_activity_context()
                    );

                    $this->activity_logger->setResourceType(BILLING);
                    $this->activity_logger->setOperationType(CONFIRM_BILLING);
                    $this->activity_logger->setResource($bill_info['id_bill']);
                    $this->activity_logger->info(model('activity_log_messages')->get_message(BILLING, CONFIRM_BILLING), $context);
                    //endregion Update Activity Log

                    switch($bill_info['name_type']){
                        case 'ship':
                        case 'order':
                            $data['order_info'] = $this->orders->get_order($bill_info['id_item']);
                            $params = array('id_item' => $bill_info['id_item'], 'encript_detail' => 1, 'bills_type' => '1,2');

                            $data['bills'] = $this->user_bills->get_user_bills($params);
                            if (empty($data['bills'])) {
                                jsonResponse('Info: There are no bills for this status.', 'info');
                            }
                            $data['balance_paid'] = $this->user_bills->summ_bills_by_order($bill_info['id_item'], "'paid'", '1,2');
                            $data['balance_confirmed'] = $this->user_bills->summ_bills_by_order($bill_info['id_item'], "'confirmed'", '1,2');
                            $data['status'] = $this->user_bills->get_bills_statuses();

                            $bills_list = $this->view->fetch('admin/order/manager_assigned/bills_list_view', $data);

                            jsonResponse('All changes were successfully saved.', 'success', array('bills_list' => $bills_list));

                            break;
                        case 'sample_order':
                            $params = array('id_item' => $bill_info['id_item'], 'encript_detail' => 1, 'bills_type' => self::SAMPLE_ORDER_BILL_TYPE);

                            $data['bills'] = model('user_bills')->get_user_bills($params);
                            if (empty($data['bills'])) {
                                jsonResponse('Info: There are no bills for this status.', 'info');
                            }

                            $data['balance_paid'] = model('user_bills')->summ_bills_by_order($bill_info['id_item'], "'paid'", self::SAMPLE_ORDER_BILL_TYPE);
                            $data['balance_confirmed'] = model('user_bills')->summ_bills_by_order($bill_info['id_item'], "'confirmed'", self::SAMPLE_ORDER_BILL_TYPE);
                            $data['status'] = model('user_bills')->get_bills_statuses();

                            $bills_list = $this->view->fetch('admin/payments/bills_list_view', $data);

                            jsonResponse('All changes were successfully saved.', 'success', array('bills_list' => $bills_list));

                            break;
                        case 'feature_item':
                            $params = array('id_user' => $bill_info['id_user'], 'id_item' => $bill_info['id_item'], 'encript_detail' => 1, 'bills_type' => '3');
                            $data['bills'] = $this->user_bills->get_user_bills($params);
                            if (empty($data['bills'])) {
                                jsonResponse('Info: There are no bills.', 'info');
                            }
                            $data['status'] = $this->user_bills->get_bills_statuses();

							$bills_list = $this->view->fetch('admin/item/bills_list_view', $data);

                            jsonResponse('The bill has been confirmed successfully.', 'success', array('bills_list' => $bills_list));

                            break;
                        case 'highlight_item':
                            $params = array('id_user' => $bill_info['id_user'], 'id_item' => $bill_info['id_item'], 'encript_detail' => 1, 'bills_type' => '4');
                            $data['bills'] = $this->user_bills->get_user_bills($params);
                            if (empty($data['bills'])) {
                                jsonResponse('Info: There are no bills.', 'info');
                            }
                            $data['status'] = $this->user_bills->get_bills_statuses();

							$bills_list = $this->view->fetch('admin/item/bills_list_view', $data);

                            jsonResponse('The bill has been confirmed successfully.', 'success', array('bills_list' => $bills_list));

                            break;
                        case 'group':
							$this->load->model('Packages_Model', 'packages');
                            $data['group_package'] = $this->packages->getGrPackage($bill_info['id_item']);
                            $params = array('id_user' => $bill_info['id_user'], 'id_item' => $bill_info['id_item'], 'encript_detail' => 1, 'bills_type' => '5');

                            $data['bills'] = $this->user_bills->get_user_bills($params);
                            if (empty($data['bills'])) {
                                jsonResponse('Info: There are no bills.', 'info');
                            }

                            $data['status'] = $this->user_bills->get_bills_statuses();

							$bills_list = $this->view->fetch('admin/billing/bills_list_view', $data);

                            jsonResponse('All changes were successfully saved.', 'success', array('bills_list' => $bills_list));

                            break;
                        default:
                            jsonResponse('The bill has been confirmed successfully.', 'success', array('r' => $responce));

                            break;
                    }
                } else {
                    jsonResponse('You cannot perform this action now. Please try again late.');
                }
            break;
            case 'my_bill_info':
                $id_user = id_session();
                $bill = (int) $_POST['bill'];
                $data['bill'] = $this->user_bills->get_user_bill($bill, array('id_user' => $id_user));

                if (empty($data['bill'])) {
                    jsonResponse(translate('systmess_bill_does_not_exist_error_message'));
                }

                $data['bill']['note'] = array_reverse(json_decode('[' . $data['bill']['note'] . ']', true));

				// CALCULATE EXPIRE TIME IN MILISECONDS FOR COUNTDOWN TIMER
				$show_expire = false;
				if (in_array($data['bill']['status'], array('init','paid'))) {
					$show_expire = true;
					$data['expire'] = $expire = (strtotime($data['bill']['due_date']) - time()) * 1000;

                    $data['extend_btn'] = ! ($data['bill']['extend_request'] || 'paid' === $data['bill']['status']);
                    $data['show_extend_btn'] = $data['bill']['extend_request'];
                }

                $data['status'] = $this->user_bills->get_bills_statuses();
                $data['types'] = $this->user_bills->get_bills_types_array();

                $content = $this->view->fetch('new/user/bills/bill_detail_view', $data);

                jsonResponse('', 'success', array('expire' => $expire, 'show_timeline' => $show_expire, 'bill' => $content));
            break;
            case 'my_bills':
                $id_user = id_session();
                $bill_status = cleanInput($_POST['status']);

                if($bill_status == 'featured_number' || $bill_status == 'highlight_number'){
                    $bill_status = 'all';
                }

                if (!in_array($bill_status, array('all', 'init', 'paid', 'confirmed', 'unvalidated'))) {
                    jsonResponse(translate('systmess_bill_status_not_correct_message'));
                }

                $bill_type = cleanInput($_POST['type']);
                $types = ['group', 'right'];

                if (have_right('sell_item')) {
                    $types = array_merge($types, ['all', 'feature_item', 'highlight_item']);
                }

                if (have_right('buy_item')) {
                    $types = array_merge($types, ['all', 'ship', 'order']);
                }

                if (have_right_or('request_sample_order,create_sample_order')) {
                    $types = array_merge($types, ['sample_order']);
                }

                if (!empty($bill_type) && !in_array($bill_type, $types)) {
                    jsonResponse(translate('systmess_bill_invalid_type_error_message'));
                }

                $data['status'] = $this->user_bills->get_bills_statuses();
                $data['types'] = $this->user_bills->get_bills_types_array();
                $page = abs(empty($page = (int) $_POST['page']) ? 1 : $page);
                $perPage = (int) config('user_bills_per_page', 10);
                $start_from = $perPage * ($page - 1);

                $params_bills = [
                    'id_user'   => $id_user,
                    'type'      => empty($bill_type) ? 'all' : $bill_type,
                    'limit'     => $start_from . ", " . $perPage
                ];

                if($bill_status !== 'all'){
                    $params_bills['status'] = "'" . $bill_status . "'";
                }

                $data['bills'] = $this->user_bills->get_user_bills($params_bills);

                if($bill_status !== 'all'){
                    $params_bills['status'] = $bill_status;
                }

                $total_bills_by_status = $this->user_bills->count_bills_by_status_type($params_bills);

                $content = $this->view->fetch('new/user/bills/bills_list_view', $data);

                jsonResponse('', 'success', array('bills' => $content, 'total_bills_by_status' => $total_bills_by_status));
            break;
            case 'search_bills':
                $keywords = cleanInput(cut_str($_POST['keywords']));
                if (empty($keywords))
                    jsonResponse(translate('systmess_bill_search_keywords_required_message'));

                global $tmvc;
                $per_page = $tmvc->my_config['user_bills_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                $bill_type = cleanInput($_POST['type']);
                $bill_status = cleanInput($_POST['status']);

                $conditions = array('id_user' => $id_user, 'limit' => $start_from . ", " . $per_page);

				$number_types = array(
					'order_number' => array(1, 2),
					'featured_number' => 3,
					'highlight_number' => 4,
					'group_number' => 5,
					'right_number' => 6
				);

                if (!empty($bill_type)) {
					if($bill_type == 'bill_number'){
						$conditions['bill_number'] = toId($keywords);
					} elseif(isset($number_types[$bill_type])){
						$conditions['id_item'] = toId($keywords);
						if(is_array($number_types[$bill_type])){
							$conditions['bills_type'] = implode(',', $number_types[$bill_type]);
						} else{
							$conditions['bills_type'] = $number_types[$bill_type];
						}
					} else{
                    	$conditions['type'] = $bill_type;
						$conditions['search'] = $keywords;
						if (empty($bill_status))
							jsonResponse(translate('systmess_bill_search_status_required_message'));

						$conditions['status'] = "'" . $bill_status . "'";
					}
                } else{
					$conditions['search'] = $keywords;
                }

                $data['status'] = $this->user_bills->get_bills_statuses();
                $data['types'] = $this->user_bills->get_bills_types_array();

                $total_bills_by_status = $this->user_bills->get_bills_count($conditions);
                $conditions['count'] = $total_bills_by_status;
                $data['bills'] = $this->user_bills->get_user_bills($conditions);

                if (empty($data['bills'])) {
                    jsonResponse(translate('systmess_bill_zero_found_by_search_message'), 'info');
                }

                $content = $this->view->fetch('new/user/bills/bills_list_view', $data);

                jsonResponse('', 'success', array('bills' => $content, 'total_bills_by_status' => $total_bills_by_status, 'status' => $bill_status, 'type' => $bill_type));
            break;
        }
    }

    public function invoice()
	{
		checkIsLogged();
		checkPermision('manage_personal_bills,manage_bills');

		/** @var \TinyMVC_Library_Make_Pdf $pdf_maker */
		$pdf_maker = library('make_pdf');
		/** @var \User_Model $users */
        $users = model('user');
        /** @var \Country_Model $countries */
        $countries = model('country');
        /** @var \Company_Model $companies */
        $companies = model('company');
        /** @var \User_Bills_Model $bills */
        $bills = model('user_bills');

        $bill_id = (int) $this->uri->segment(3);
		if (
			empty($bill_id) || empty($bill = $bills->get_user_bill($bill_id))
		) {
			redirectWithMessage("/", translate('systmess_error_invalid_data'), 'errors');
        }

        $auxiliary_bills = array();
        $bill['auxiliary'] = &$auxiliary_bills;
        $auxiliary_bills_ids = arrayGet($_GET, 'additional_bills', array());
        if (!empty($auxiliary_bills_ids)) {
            $auxiliary_bills = $bills->get_bill_list(array_map('intval', $auxiliary_bills_ids));
            if (
                empty($auxiliary_bills)
                || (
                    count($auxiliary_bills_ids) !== count($auxiliary_bills)
                )
            ) {
                redirectWithMessage("/", translate('systmess_error_invalid_data'), 'errors');
            }
        }

		if ($is_administration = have_right('manage_bills')) {
			$user_id = (int) $bill['id_user'];
		} else {
			if (!is_privileged('user', (int) $bill['id_user'])) {
				redirectWithMessage("/", translate('systmess_error_invalid_data'), 'errors');
			}

			$user_id = (int) privileged_user_id();
        }

        foreach ($auxiliary_bills as $auxiliary_bill) {
            if ($user_id !== (int) $auxiliary_bill['id_user']) {
                redirectWithMessage(
                    "/",
                    $is_administration
                        ? "One or more additional bills doesn't belong to the same user"
                        : translate('systmess_billing_bill_not_belong_message'),
                    'errors'
                );
            }

            if ((int) $bill['id_item'] !== (int) $auxiliary_bill['id_item']) {
                redirectWithMessage("/", translate('systmess_billing_belong_different_order_message'), 'errors');
            }
        }

		$user = $users->getSimpleUser($user_id);
        $company = null;

        if ('Seller' === $user['gr_type']) {
            $company = $companies->get_seller_base_company($user_id);
            $country_state_city = $countries->get_country_state_city((int) $company['id_city']);
            $zip = !empty($company['zip_company']) ? ", {$company['zip_company']}, " : ', ';
            $company['address_company'] = implode(', ', array_filter($country_state_city)) . "{$zip}{$company['address_company']}";
        } else if ('Buyer' === $user['gr_type']) {
            $company = array_filter((array) model('company_buyer')->get_company_by_user($user_id));
            $zip = !empty($user['zip']) ? ", {$user['zip']}, " : ', ';
            $country_state_city = $countries->get_country_state_city((int) $user['city']);
            $user['address'] = implode(', ', array_filter($country_state_city)) . "{$zip}{$user['address']}";
        } else {
            $zip = !empty($user['zip']) ? ", {$user['zip']}, " : ', ';
            $country_state_city = $countries->get_country_state_city((int) $user['city']);
            $user['address'] = implode(', ', array_filter($country_state_city)) . "{$zip}{$user['address']}";
        }

		try {
			$pdf = $pdf_maker->make_bill_invoice($user, $bill, $company);
		} catch (\Exception $exception) {
			// @todo log exception
			redirectWithMessage("/", translate('systmess_bill_invoice_failed_download_message'), 'errors');
		}

		$pdf->Output('Bill-invoice-'. orderNumber($bill_id) . '.pdf', Destination::INLINE);
    }

    private function handler_bill_order($bill_info) {
        $this->load->model('User_Model', 'users');
        $this->load->model('Notify_Model', 'notify');

        $action_date = date('Y-m-d H:i:s');

        $order_info = $this->orders->get_order($bill_info['id_item']);
        if (empty($order_info))
            return false;

		$data_systmess = [
			'mess_code' => 'bill_payment_confirmed',
			'id_item'   => $bill_info['id_bill'],
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'order',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		];

        $this->notify->send_notify($data_systmess);

        $balance = $bill_info['balance'] - $bill_info['amount'];
        $paid_percents = $bill_info['amount'] * $bill_info['pay_percents'] / $bill_info['balance'];
        $remain_percents = $bill_info['pay_percents'] - $paid_percents;

		if ($bill_info['amount'] < $bill_info['balance']) {
            // Generate new bill and set to user calendar
            $insert_new_bill = array(
                'id_user' => $bill_info['id_user'],
                'bill_description' => 'This bill has been created on the basis of the remaining parts of the bill ' . orderNumber($bill_info['id_bill']) . ' for the order ' . orderNumber($bill_info['id_item']) . '.',
                'balance' => $balance,
                'total_balance' => $bill_info['total_balance'],
                'id_type_bill' => $bill_info['id_type_bill'],
                'id_item' => $bill_info['id_item'],
                'pay_percents' => $remain_percents,
                'create_date' => formatDate($action_date, 'Y-m-d H:i:s'),
                'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
            );
            $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);

			$this->notify->send_notify([
				'mess_code' => 'bill_created',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'   => orderNumber($id_new_bill),
					'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
					'[BALANCE]'   => '$' . get_price($balance, false)
				],
				'systmess' => true
			]);

        }

        $this->user_bills->change_user_bill($bill_info['id_bill'], array('status' => 'confirmed',
            'confirmed_date' => $action_date,
            'note' => json_encode(array(
                'date_note' => $action_date,
                'note' => 'The payment was confirmed by EP manager.'
            )),
            'pay_percents' => $paid_percents
            )
        );

        $order_number = orderNumber($order_info['id']);
        // Update Order log
        $order_log = array(
            'date' => formatDate($action_date, 'm/d/Y h:i:s A'),
            'user' => 'EP Manager',
            'message' => 'The payment of $ ' . get_price($bill_info['amount'], false) . ', for order ' . orderNumber($bill_info['id_item']) . ', has been confirmed by EP Manager.'
        );
        $this->orders->change_order_log($bill_info['id_item'], json_encode($order_log));

        // Calculate amount and total payed for this order
        $total_payed = $this->user_bills->summ_bills_by_item($bill_info['id_user'], $bill_info['id_item'], "'confirmed'", '1,2');
        $total_amount = $order_info['final_price'] + $order_info['ship_price'];
        $exist_other_bills = $this->user_bills->count_bills_by_item($bill_info['id_user'], $bill_info['id_item'], "'init', 'paid'", '1,2');

        if (compareFloatNumbers($total_amount ,$total_payed, '<=') && !$exist_other_bills) {
            // Update Order
            $order_log = array(
				'date' => formatDate($action_date, 'm/d/Y h:i:s A'),
				'user' => 'EP Manager',
				'message' => 'The order: ' . orderNumber($bill_info['id_item']) . ' has been completely paid by the buyer.'
            );
            $id_status_n = 6;
            $new_status_info = $this->orders->get_status_detail($id_status_n);
            $update_order = array(
				'status' => $id_status_n,
				'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
				'order_summary' => $order_info['order_summary'] . ',' . json_encode($order_log)
            );
            $this->orders->change_order($bill_info['id_item'], $update_order);
        }
        return true;
    }

    private function handler_bill_sample_order($bill_info) {
        $current_date_time = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        //region Update payment
        model('user_bills')->change_user_bill((int) $bill_info['id_bill'], array(
            'status' => 'confirmed',
            'confirmed_date' => $current_date_time,
            'note' => json_encode(array(
                'date_note' => $current_date_time,
                'note' => 'The payment has been confirmed by EP manager.'
            ))
        ));
        //endregion Update payment

        //region Notify user about payment confirmed
		$data_systmess = [
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'order',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		];

        model('notify')->send_notify($data_systmess);
        //endregion Notify user about payment confirmed

        return true;
    }

    private function handler_bill_ship($bill_info) {
        $this->load->model('User_Model', 'users');
        $this->load->model('Notify_Model', 'notify');

        $action_date = date('Y-m-d H:i:s');

        $order_info = $this->orders->get_order($bill_info['id_item']);
        if (empty($order_info)){
            return false;
        }

		$this->notify->send_notify([
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'shipping',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		]);

        $balance = $bill_info['balance'] - $bill_info['amount'];
        $paid_percents = $bill_info['amount'] * $bill_info['pay_percents'] / $bill_info['balance'];
        $remain_percents = $bill_info['pay_percents'] - $paid_percents;
        if ($bill_info['amount'] < $bill_info['balance']) {
            // Generate new bill and set to user calendar
            $insert_new_bill = array(
                'id_user' => $bill_info['id_user'],
                'bill_description' => 'This bill has been created on the basis of the remaining parts of the shipping bill ' . orderNumber($bill_info['id_bill']) . ' for the order ' . orderNumber($bill_info['id_item']) . '.',
                'balance' => $balance,
                'total_balance' => $bill_info['total_balance'],
                'id_type_bill' => $bill_info['id_type_bill'],
                'id_item' => $bill_info['id_item'],
                'pay_percents' => $remain_percents,
                'create_date' => formatDate($action_date, 'Y-m-d H:i:s'),
                'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
            );
            $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);

			$this->notify->send_notify([
				'mess_code' => 'bill_created',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'   => orderNumber($id_new_bill),
					'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
					'[BALANCE]'   => '$' . get_price($balance, false)
				],
				'systmess' => true
			]);

        }

        $note = array(
            'date_note' => $action_date,
            'note' => 'The payment was confirmed by EP manager.'
        );
        $this->user_bills->change_user_bill($bill_info['id_bill'], array('status' => 'confirmed',
            'confirmed_date' => $action_date,
            'note' => json_encode($note),
            'pay_percents' => $paid_percents
            )
        );

        // Update Order log
        $order_log = array(
            'date' => formatDate($action_date, 'm/d/Y h:i:s A'),
            'user' => 'EP Manager',
            'message' => 'The payment of $ ' . get_price($bill_info['amount'], false) . ', for order ' . orderNumber($bill_info['id_item']) . ', has been confirmed by EP Manager.'
        );
        $this->orders->change_order_log($bill_info['id_item'], json_encode($order_log));

        // Calculate amount and total payed for this order
        $total_payed = $this->user_bills->summ_bills_by_item($bill_info['id_user'], $bill_info['id_item'], "'confirmed'", '1,2');
        $total_amount = $order_info['final_price'] + $order_info['ship_price'];
        $exist_other_bills = $this->user_bills->count_bills_by_item($bill_info['id_user'], $bill_info['id_item'], "'init', 'paid'", '1,2');

		if (compareFloatNumbers($total_amount, $total_payed, '<=') && !$exist_other_bills) {
            // Update Order
            $order_log = array(
                'date' => formatDate($action_date, 'm/d/Y h:i:s A'),
                'user' => 'EP Manager',
                'message' => 'The order: ' . orderNumber($bill_info['id_item']) . ' has been completely paid by the buyer.'
            );
            $id_status_n = 6;
            $new_status_info = $this->orders->get_status_detail($id_status_n);
            $update_order = array(
                'status' => $id_status_n,
                'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                'order_summary' => $order_info['order_summary'] . ',' . json_encode($order_log)
            );
            $this->orders->change_order($bill_info['id_item'], $update_order);
        }

        return true;
    }

    private function handler_bill_feature_item($bill_info) {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'users');
        $this->load->model('Notify_Model', 'notify');
        global $tmvc;

        $action_date = date('Y-m-d H:i:s');

        // Change bill
        $note = array(
            'date_note' => $action_date,
            'note' => 'Bill payment was confirmed by EP manager.'
        );
        $this->user_bills->change_user_bill($bill_info['id_bill'], array('status' => 'confirmed', 'confirmed_date' => date('Y-m-d H:i:s'), 'note' => json_encode($note)));

		$this->notify->send_notify([
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'feature item',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		]);

		$this->load->model('Items_Featured_Model', 'items_feat');
		$info_featured_item = $this->items_feat->get_featured_item($bill_info['id_item']);
        $item_detail = $this->items->get_item($info_featured_item['id_item'], 'id, id_cat, title, featured');
        $current_amount = $this->category->get_cat_feature_price($item_detail['id_cat']);

        $balance = $bill_info['balance'] - $bill_info['amount'];
        $paid_percents = $bill_info['amount'] * $bill_info['pay_percents'] / $bill_info['balance'];
        $remain_percents = $bill_info['pay_percents'] - $paid_percents;

        if (compareFloatNumbers($bill_info['amount'], $bill_info['balance'], '<')) {
            // Generate new bill and set to user calendar
            $insert_new_bill = array(
                'id_user' => $bill_info['id_user'],
                'bill_description' => 'This bill has been created on the basis of the remaining parts of the "Feature item" bill ' . orderNumber($bill_info['id_bill']) . '.',
                'balance' => $balance,
                'total_balance' => $bill_info['total_balance'],
                'id_type_bill' => $bill_info['id_type_bill'],
                'id_item' => $bill_info['id_item'],
                'pay_percents' => $remain_percents,
                'create_date' => formatDate($action_date, 'Y-m-d H:i:s'),
                'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
            );
            $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);

			$this->notify->send_notify([
				'mess_code' => 'bill_created',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'   => orderNumber($id_new_bill),
					'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
					'[BALANCE]'   => '$' . get_price($balance, false)
				],
				'systmess' => true
			]);


            return true;
        } else {
            $remain_days = intval($this->items->get_featured_remains_days($bill_info['id_item']));
            $period = config('item_featured_default_period') + $remain_days;
            // $end_date = date('Y-m-d', strtotime("+" . $period . " days"));
            $end_date = date_plus($period);

            $this->items_feat->update_featured_item(
                $bill_info['id_item'],
                array(
                    'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                    'end_date'              => $end_date,
                    'status'                => 'active',
                    'extend'                => 0,
                    'price'                 => $current_amount,
                    'paid'                  => 1,
                )
            );

            $this->items->update_item(array('id' => $info_featured_item['id_item'], 'featured' => 1));
            model('Elasticsearch_Items')->index($info_featured_item['id_item']);

            if($item_detail['featured'] == 0){
                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array(
                    $bill_info['id_user'] => array('active_featured_items' => 1)
                ));
            }

            $json_notice = json_encode(
                array(
                'add_date' => $action_date,
                'add_by' => $this->session->lname . ' ' . $this->session->fname,
                'notice' => 'The item has been featured.'
                )
            );
            $this->items_feat->set_notice($bill_info['id_featured'], $json_notice);

			$this->notify->send_notify([
				'mess_code' => 'feature_item_bill_payment_finished',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'    => orderNumber($bill_info['id_bill']),
					'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill'],
					'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
					'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item_detail['id'],
					'[END_DATE]'   => getDateFormat($end_date, null, 'j M, Y'),
					'[LINK]'       => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
				],
				'systmess' => true
			]);


            return true;
        }
    }

    private function handler_bill_highlight_item($bill_info) {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'users');
        $this->load->model('Notify_Model', 'notify');

        global $tmvc;
        $highlight_item = $this->items->get_highlight($bill_info['id_item']);
        $action_date = date('Y-m-d H:i:s');

        // Change bill
        $note = array(
            'date_note' => $action_date,
            'note' => 'Bill payment was confirmed by EP manager.'
        );
        $this->user_bills->change_user_bill($bill_info['id_bill'], array('status' => 'confirmed', 'confirmed_date' => date('Y-m-d H:i:s'), 'note' => json_encode($note)));

		$this->notify->send_notify([
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'highlight item',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		]);

        // Calculate total_payed, amount and balance for current bill type

		$this->load->model('Items_Highlight_Model', 'items_high');
		$info_highlight_item = $this->items_high->get_highlight_item($bill_info['id_item']);
        $item_detail = $this->items->get_item($info_highlight_item['id_item'], 'id, id_cat, id_seller, title, highlight');
        $current_amount = $this->category->get_cat_feature_price($item_detail['id_cat']);

        // If current payment amount is less than user have to realy pay for this bill we generate new bill with rests
        $balance = $bill_info['balance'] - $bill_info['amount'];
        $paid_percents = $bill_info['amount'] * $bill_info['pay_percents'] / $bill_info['balance'];
        $remain_percents = $bill_info['pay_percents'] - $paid_percents;
        if (compareFloatNumbers($bill_info['amount'], $bill_info['balance'], '<')) {
            // Generate new bill and set to user calendar
            $insert_new_bill = array(
                'id_user' => $bill_info['id_user'],
                'bill_description' => 'This bill has been created on the basis of the remaining parts of the "Highlight item" bill ' . orderNumber($bill_info['id_bill']) . '.',
                'balance' => $balance,
                'total_balance' => $bill_info['total_balance'],
                'id_type_bill' => $bill_info['id_type_bill'],
                'id_item' => $bill_info['id_item'],
                'pay_percents' => $remain_percents,
                'create_date' => formatDate($action_date, 'Y-m-d H:i:s'),
                'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
            );
            $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);


			$this->notify->send_notify([
				'mess_code' => 'bill_created',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'   => orderNumber($id_new_bill),
					'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
					'[BALANCE]'   => '$' . get_price($balance, false)
				],
				'systmess' => true
			]);


            return true;
        } else {
            $remain_days = intval($this->items->get_highlighted_remains_days($bill_info['id_item']));
            $period = $tmvc->my_config['item_highlight_default_period'] + $remain_days;
            $end_date = date_plus($period);
            $this->items->update_item(array('id' => $info_highlight_item['id_item'], 'highlight' => 1));
            model('Elasticsearch_Items')->index($info_highlight_item['id_item']);

            $this->items_high->update_highlight_item($bill_info['id_item'], array('end_date' => $end_date, 'status' => 'active', 'price' => $current_amount, 'paid' => 1, 'extend' => 0));
            if($item_detail['highlight'] == 0){
                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array(
                    $bill_info['id_user'] => array('active_highlight_items' => 1)
                ));
            }

            $json_notice = json_encode(
                array(
                    'add_date' => formatDate(date("Y-m-d H:i:s")),
                    'add_by' => $this->session->lname . ' ' . $this->session->fname,
                    'notice' => 'The item has been hilghlighted.'
                )
            );
            $this->items_high->set_notice($bill_info['id'], $json_notice);

			$this->notify->send_notify([
				'mess_code' => 'highlight_item_bill_payment_finished',
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item_detail['id'],
					'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
					'[END_DATE]'   => getDateFormat($end_date, null, 'j M, Y'),
					'[BILL_ID]'    => orderNumber($bill_info['id_bill']),
					'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
				],
				'systmess' => true
			]);


            return true;
        }
    }

    private function handler_bill_group($bill_info) {
        //region Update payment
        $action_date = date('Y-m-d H:i:s');
        model('user_bills')->change_user_bill((int) $bill_info['id_bill'], array(
            'status' => 'confirmed',
            'confirmed_date' => $action_date,
            'note' => json_encode(array(
                'date_note' => $action_date,
                'note' => 'The payment has been confirmed by EP manager.'
            ))
        ));
        //endregion Update payment

		//region Notify user about payment confirmed
		model('notify')->send_notify([
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [(int) $bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'Account Upgrade',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		]);
		//endregion Notify user about payment confirmed

        return true;
    }

    private function handler_bill_right($bill_info) {
        $this->load->model('User_Model', 'users');
        $this->load->model('Packages_Model', 'packages');
        $this->load->model('User_Bills_Model', 'user_bills');
        $this->load->model('Notify_Model', 'notify');

        $action_date = date('Y-m-d H:i:s');

        // Change bill
        $note = array(
            'date_note' => $action_date,
            'note' => 'The Payment has been confirmed by EP manager.'
        );
        $this->user_bills->change_user_bill($bill_info['id_bill'], array('status' => 'confirmed', 'confirmed_date' => date('Y-m-d H:i:s'), 'note' => json_encode($note)));

		$data_systmess = [
			'mess_code' => 'bill_payment_confirmed',
			'id_users'  => [$bill_info['id_user']],
			'replace'   => [
				'[TYPE]'      => 'right package',
				'[BILL_ID]'   => orderNumber($bill_info['id_bill']),
				'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill']
			],
			'systmess' => true
		];

        $this->notify->send_notify($data_systmess);

        // If current payment amount is less than user have to realy pay for this bill we generate new bill with rests
        $balance = $bill_info['balance'] - $bill_info['amount'];
        $paid_percents = $bill_info['amount'] * $bill_info['pay_percents'] / $bill_info['balance'];
        $remain_percents = $bill_info['pay_percents'] - $paid_percents;

        if (compareFloatNumbers($bill_info['amount'], $bill_info['balance'], '<')) {
            // Generate new bill and set to user calendar
            $insert_new_bill = array('id_user' => $bill_info['id_user'],
                                     'bill_description' => 'This bill has been created on the basis of the remaining parts of the "Right package" bill ' . orderNumber($bill_info['id_bill']) . '.',
                                     'balance' => $balance,
                                     'total_balance' => $bill_info['total_balance'],
                                     'id_type_bill' => $bill_info['id_type_bill'],
                                     'id_item' => $bill_info['id_item'],
                                     'pay_percents' => $remain_percents,
                                     'create_date' => formatDate($action_date, 'Y-m-d H:i:s'),
                                     'due_date' => formatDate($bill_info['due_date'], 'Y-m-d H:i:s')
                                    );
            $id_new_bill = $this->user_bills->set_user_bill($insert_new_bill);

			$data_systmess = [
				'mess_code' => 'bill_created',
				'id_item'   => $bill_info['id_item'],
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'   => orderNumber($id_new_bill),
					'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_new_bill,
					'[BALANCE]'   => '$' . get_price($balance, false)
				],
				'systmess' => true
			];


            $this->notify->send_notify($data_systmess);
            return true;
        } else {
            $right_package = $this->packages->getRightPackage($bill_info['id_item']);

            $this->load->model('Usergroup_Model', 'user_group');
            $right_info = $this->user_group->get_aditional_right($bill_info['id_user'], $right_package['id_right']);
            $action = 'insert';
            if(!empty($right_info)){
                $action = 'extend';
            }

            switch ($action) {
                default:
                case 'insert':
                    switch($right_package['abr']){
                        case 'F':
                            $end_date = 'Permanently';
                            $paid_until = '0000-00-00';
                        break;
                        default:
                            $end_date = formatDate(date_plus($right_package['days']));
                            $paid_until = formatDate($end_date, 'Y-m-d');
                        break;
                    }
                    $insert = array(
                        'id_user' => $bill_info['id_user'],
                        'id_right' => $right_package['id_right'],
                        'right_paid_until' => $paid_until

                    );
                    $this->user_group->set_aditional_rights($insert);
                    $mess_code = 'right_package_bill_payment_finished';
                break;
                case 'extend':
                    $paid_until = formatDate(date_plus($right_package['days'], 'days', $right_info['right_paid_until']), 'Y-m-d');
                    $end_date = formatDate($paid_until);
					$update = array(
						'right_paid_until' => $paid_until
					);
					$this->user_group->update_user_aditional_right($right_info['id_user'], $right_info['id_right'], $update);
                    $mess_code = 'extend_right_package_bill_payment_finished';
                break;
            }

			$data_systmess = [
				'mess_code' => $mess_code,
				'id_users'  => [$bill_info['id_user']],
				'replace'   => [
					'[BILL_ID]'    => orderNumber($bill_info['id_bill']),
					'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $bill_info['id_bill'],
					'[RIGHT_NAME]' => $right_package['r_name'],
					'[END_DATE]'   => $end_date
				],
				'systmess' => true
			];

            $this->notify->send_notify($data_systmess);
            return true;
        }
    }

    private function bill_for($bill_info = array()){
        if(empty($bill_info))
            return;

        switch($bill_info['name_type']){
            default:
            case 'order':
            case 'ship':
                $bill_for = '"Order" '. orderNumber($bill_info['id_item']);
            break;
            case 'feature_item':
                $bill_for = '"Feature item" '. orderNumber($bill_info['id_item']);
            break;
            case 'highlight_item':
                $bill_for = '"Highlight item" '. orderNumber($bill_info['id_item']);
            break;
            case 'group':
                $bill_for = '"Account upgrade"';
            break;
        }

        return $bill_for;
    }
}

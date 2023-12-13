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
class Invoices_Controller extends TinyMVC_Controller
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

	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Orders_model', 'orders');
		$this->load->model('User_model', 'user');
		$this->load->model('Invoices_Model', 'invoices');
	}

	function savepdf_invoice() {
		if(!have_right('buy_item') && !have_right('manage_seller_orders')){
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"),'errors');
			headerRedirect(__SITE_URL.'order/my');
		}

		$this->_load_main();
		$id_invoice = intVal($_GET['invoice']);
		$invoice_info = $this->invoices->get_invoice($id_invoice);
		$id_user = $this->session->id;
		if($invoice_info['id_buyer'] != $id_user && $invoice_info['id_seller'] != $id_user){
			$this->session->setMessages(translate("systmess_error_rights_perform_this_action"),'errors');
			headerRedirect(__SITE_URL.'order/my');
		}

		file_force_download('shipp_invoices/'.substr($invoice_info['id_invoices'], 1).'.pdf');
	}


    /**
     * @author @Alexei Tolmachinski
     * @todo Remove [16.05.2022]
     * Isn't used anywhere.
     */

	// function view_invoice() {
	// 	$this->_load_main();

	// 	$id_order = (int)$this->uri->segment(3);
	// 	$id_user = privileged_user_id();

	// 	if(((have_right('buy_item') || have_right('manage_seller_orders')) && !$this->orders->isMyOrder($id_order, $id_user))
	// 		|| (have_right('administrate_orders') && !$this->orders->isOrderManager($id_order, $id_user))){
	// 		$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
	// 		show_404();
	// 	}

	// 	$invoice_type = $this->uri->segment(4);
	// 	$invoice_name = $this->uri->segment(5);
	// 	$file_name = $invoice_type.'_'.$invoice_name.'.pdf';
	// 	$this->_show_invoice($id_order, $file_name);
	// }

	// function shipper_view_invoice() {
	// 	$this->_load_main();
	// 	global $tmvc;

	// 	$id_order = (int)$this->uri->segment(3);
	// 	$id_user = intval($this->uri->segment(4));
	// 	$secret_key = $this->uri->segment(5);

	// 	if($secret_key != $tmvc->my_config['cross_domain_key']){
	// 		return false;
	// 	}

	// 	$order_info = $this->orders->get_order($id_order);
	// 	if(empty($order_info)){
	// 		return false;
	// 	}

	// 	if($order_info['shipper_type'] != 'ep_shipper' && $order_info['id_shipper'] != $id_user){
	// 		return false;
	// 	}

    //     $user = $this->user->getUser($id_user);
	// 	if(empty($user)){
	// 		return false;
	// 	}

	// 	if($user['logged'] == 0){
	// 		return false;
	// 	}

	// 	$this->load->model('Shippers_Model', 'shippers');
	// 	$shipper = $this->shippers->get_shipper_by_user($id_user);

	// 	if(empty($shipper)){
	// 		return false;
	// 	}

	// 	if($shipper['accreditation'] != 1){
	// 		return false;
	// 	}

	// 	$invoice_type = 'ship';
	// 	$invoice_name = orderNumberOnly($id_order);
	// 	$file_name = $invoice_type.'_'.$invoice_name.'.pdf';
	// 	$this->_show_invoice($id_order, $file_name);
	// }

	// private function _show_invoice($id_order, $file_name){
	// 	$path = $_SERVER['DOCUMENT_ROOT'] . '/public/invoices/orders/'.$id_order.'/';
    //     de(11);
	// 	if(!file_exists($path.$file_name)){
	// 		$this->session->setMessages('Error: This invoice does not exist.','errors');
	// 		show_404();
	// 	}

	// 	header('Content-type: application/pdf');
	// 	header('Content-Disposition: inline; filename="' . $file_name . '"');
	// 	header('Content-Transfer-Encoding: binary');
	// 	header('Accept-Ranges: bytes');
	// 	echo file_get_contents($path.$file_name);
	// }

	function ajax_invoice_options(){
		checkIsAjax();
		checkIsLoggedAjax();

		$this->_load_main();
		$this->load->model('User_Bills_Model', 'user_bills');
		$this->load->model('Company_Model', 'company');
		$this->load->model('Notify_Model', 'notify');

		$op = $this->uri->segment(3);
		switch($op){
			case 'confirm_invoice':
				// CHECK USER RIGHT - ONLY BUYER CAN CONFIRM INVOICE
				checkPermisionAjax('buy_item');

				// GET ORDER DETAIL
				$id_order = (int) arrayGet($_POST, 'id_order');
				if (empty($id_order) || empty($order_detail = model('orders')->get_order($id_order))) {
					jsonResponse(translate('systmess_error_invalid_data').'1');
				}

				// CHECK ORDER STATUS - MUST BE INVOICE SENT TO BUYER
				if($order_detail['status_alias'] != 'invoice_sent_to_buyer') {
					jsonResponse(translate('systmess_error_invalid_data').'2');
				}

				if(!is_privileged('user', $order_detail['id_buyer'], true)) {
					jsonResponse(translate('systmess_error_invalid_data').'3');
				}

				// GET INVOICE DETAIL
				$id_invoice = (int) $order_detail['id_invoice'];
				$invoice_info = $this->invoices->get_invoice($id_invoice);

				// CHECK INVOICE IF EXIST AND STATUS
				if(empty($invoice_info) || $invoice_info['status'] != 'sent') {
					jsonResponse(translate('systmess_error_invalid_data').'4');
				}

				// UPDATE THE INVOICE STATUS - ACCEPTED
				if(!$this->invoices->update_invoice($id_invoice, array('status' => 'accepted'))){
					jsonResponse(translate('systmess_internal_server_error'));
				}

				// UPDATE ORDER LOG AND STATUS - INVOICE CONFIRMED
				$current_status_alias = 'invoice_confirmed';
				$new_status_info = $this->orders->get_status_by_alias($current_status_alias);
				$current_status_name = $new_status_info['status'];
				$update_order = array(
					'status' => $new_status_info['id'],
					'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
					'order_summary' => $order_detail['order_summary'] .','. json_encode(array(
						'date' => date('m/d/Y h:i:s A'),
						'user' => 'Buyer',
						'message' => 'The Invoice: '.orderNumber($id_invoice).' has been confirmed.'
					))
				);
				$this->orders->change_order($id_order, $update_order);

				// NOTIFY SELLER ABOUT INVOICE CONFIRMATION
                $this->notifier->send(
                    (new SystemNotification('order_invoice_approved_buyer', [
						'[ORDER_ID]'   => orderNumber($id_order),
						'[INVOICE_ID]' => orderNumber($id_invoice),
						'[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $invoice_info['id_seller']))->withRoomType(RoomType::CARGO())
                );

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $countFakeParticipants = $usersModel->countAllBy([
                    'scopes' => [
                        'IsRealUser' => false,
                        'ids'        => [(int) $order_detail['id_buyer'], (int) $order_detail['id_seller']],
                    ],
                ]);

				$potential_shippers = array_column(model('shippers')->get_potential_shippers(null, (int) $order_detail['ship_from_country'], 0 !== $countFakeParticipants), 'id_user');
                if (!empty($potential_shippers)) {
                    $this->notifier->send(
                        (new SystemNotification('shipping_upcoming_order_initiated', [
							'[ORDER_ID]' => orderNumber($id_order),
							'[LINK]'     => getUrlForGroup("orders_bids/upcoming/order/{$id_order}", 'shipper'),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $potential_shippers)
                    );
				}

				jsonResponse(translate('systmess_success_order_confirm_invoice'), 'success', array('order' => $id_order, 'order_status_alias' => $current_status_alias, 'order_status_name' => $current_status_name));
			break;
			case 'send_invoice':
				checkPermisionAjax('manage_seller_orders');

				$id_order = (int) arrayGet($_POST, 'id_order');
				if (empty($id_order) || empty($order_info = model('orders')->get_full_order($id_order))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if($order_info['status_alias'] != 'purchase_order_confirmed') {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if(!is_privileged('user', $order_info['id_seller'], true)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$purchase_order = !empty($order_info) ? json_decode($order_info['purchase_order'], true) : array();
				if (empty($purchase_order)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				// INSERT INVOICE TO DATABASE
				$insert = array(
					'id_order' 		=> $id_order,
					'po_number' 	=> $purchase_order['invoice']['po_number'],
					'discount' 		=> $purchase_order['invoice']['discount'],
					'amount' 		=> $purchase_order['invoice']['amount'],
					'final_amount' 	=> $purchase_order['invoice']['final_amount'],
					'issue_date' 	=> $purchase_order['invoice']['issue_date'],
					'due_date' 		=> $purchase_order['invoice']['due_date'],
					'products' 		=> json_encode($purchase_order['invoice']['products']),
					'subject' 		=> $purchase_order['invoice']['subject'],
					'invoice_map' 	=> json_encode($purchase_order['invoice']['invoice_map']),
					'status' 		=> 'sent'
				);
				$id_invoice = $this->invoices->set_invoice($insert);

				if(false == (bool) $id_invoice){
					jsonResponse(translate('systmess_internal_server_error'));
				}

				// IF ALL IS OK - UPDATE ORDER AND NOTIFY USERS
				$current_status_alias = 'invoice_sent_to_buyer';
				$new_status_info = $this->orders->get_status_by_alias($current_status_alias);
				$current_status_name = $new_status_info['status'];

				// PREPARE ORDER LOGS
				$order_logs = array();
				if((int) $order_info['extend_request'] > 0){
					$update_order['extend_request'] = 0;
					$this->load->model('Extend_model', 'extend');
					model('extend')->delete_extend_request((int) $order_info['extend_request']);

					$order_logs[] = json_encode(array(
						'date' => date('m/d/Y h:i:s A'),
						'user' => 'Seller',
						'message' => 'The request for extend order status time has been canceled.'
					));
				}

				$order_logs[] = json_encode(array(
					'date' => date('m/d/Y h:i:s A'),
					'user' => 'Seller',
					'message' => 'The invoice has been created and sent to Buyer.'
				));

				// UPDATE ORDER
				$this->orders->change_order($id_order, array(
					'id_invoice' 		=> $id_invoice,
					'status' 			=> $new_status_info['id'],
					'status_countdown' 	=> date_plus($new_status_info['countdown'], 'days', false, true),
					'order_summary' 	=> $order_info['order_summary'] .','. implode(',', $order_logs)
				));

	            // NOTIFY BUYER ABOUT INVOICE
                $this->notifier->send(
                    (new SystemNotification('order_invoice_created', [
						'[ORDER_ID]'   => orderNumber($id_order),
						'[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_info['id_buyer']))->withRoomType(RoomType::CARGO())
                );

				jsonResponse(translate('systmess_success_order_send_invoice'), 'success', array('order' => $id_order, 'order_status_alias' => $current_status_alias, 'order_status_name' => $current_status_name));
			break;
		}
	}

	function popups_invoice(){
		checkIsAjax();
		checkIsLoggedAjaxModal();

		$this->_load_main();
		$this->load->model('Company_Model', 'company');

		// !!!DO NOT CHANGE THIS DATA
		$type = $this->uri->segment(3);
		$id_order = $this->uri->segment(4);
		$id_user = privileged_user_id();

		switch($type){
			case 'view_invoice':
				checkPermisionAjaxModal('manage_seller_orders,buy_item');

				if(!$this->orders->isMyOrder($id_order, $id_user)){
					messageInModal(translate('systmess_error_invalid_data') . 'Code #1');
				}

				// GET ORDER DETAILS
				$order_detail = $this->orders->get_order($id_order);
				// CHECK IF ORDER INVOICE HAS BEEN CONFIRMED BY BUYER
				if(have_right('manage_seller_orders') && !in_array($order_detail['status_alias'], array('purchase_order_confirmed', 'invoice_sent_to_buyer', 'invoice_confirmed')) || have_right('buy_item') && !in_array($order_detail['status_alias'], array('invoice_sent_to_buyer', 'invoice_confirmed'))){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				// CLEAR ALL DATA - FOR SECURITY REASON
				$data = array();

				// PREPARING DATA FOR INVOICE
				// GET BUYER DETAIL
				$this->load->model('Company_Buyer_Model', 'company_buyer');
				$data['buyer_info'] = $this->user->getSimpleUser($order_detail['id_buyer'], "users.idu, IF(users.legal_name IS NULL or users.legal_name = '', CONCAT(users.fname, ' ', users.lname), users.legal_name) as buyer_name, users.fax, users.fax_code, users.phone, users.phone_code, users.email, users.zip as buyer_zip, users.city as buyer_city, users.address as buyer_address");
				$data['company_buyer_info'] = $this->company_buyer->get_company_by_user($order_detail['id_buyer']);
				$buyer_location = model('country')->get_country_state_city($data['buyer_info']['buyer_city']);
				$buyer_location[] = $data['buyer_info']['buyer_zip'];
				$buyer_location[] = $data['buyer_info']['buyer_address'];
				$data['buyer_info']['buyer_location'] = implode(', ', array_filter($buyer_location));

				// GET SELLER COMPANY DETAIL
				$data['seller_info'] = model('company')->get_seller_base_company($order_detail['id_seller'], "cb.name_company, cb.legal_name_company, cb.phone_code_company, cb.fax_code_company, cb.fax_company, cb.phone_company, cb.id_city, cb.zip_company, cb.address_company, u.fname, u.lname, u.email", true);
				$company_location = model('country')->get_country_state_city($data['seller_info']['id_city']);
				$company_location[] = $data['seller_info']['zip_company'];
				$company_location[] = $data['seller_info']['address_company'];
				$data['seller_info']['company_location'] = implode(', ', array_filter($company_location));

				// PREPARE ORDER & INVOICE DETAILS
				$order_detail['purchase_order'] = !empty($order_detail['purchase_order']) ? json_decode($order_detail['purchase_order'], true) : array();
				$data['order'] = $order_detail;
				$data['invoice_info'] = $order_detail['purchase_order']['invoice'];
				$data['products'] = $order_detail['purchase_order']['invoice']['products'];

				// PREPARE BUTTONS FOR ACTIONS
				$data['show_buyer_buttons'] = have_right('buy_item') && $order_detail['status_alias'] == 'invoice_sent_to_buyer';
				$data['show_seller_buttons'] = have_right('manage_seller_orders') && $order_detail['status_alias'] == 'purchase_order_confirmed';

				// DISPLAY THE INVOICE
				$this->view->assign($data);
				$this->view->display('new/order/invoice/preview_view');
			break;
		}
	}
}

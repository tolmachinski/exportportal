<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ItemPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Inquiry_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();
    private $inquiry_logs = array(
        'inquiry_init' => 'Inquiry has been initiated.',
        'order_summary' => 'Order has been initiated in base of "Inquiry": ',
        'inquiry_confirmed' => 'Inquiry has been confirmed.',
        'sold_by_inquiry' => 'Sold on the base of inquiry',
        'decline_inquiry' => 'Inquiry has been declined.',
        'prototype_created' => 'The prototype has been created.',
        'prototype_init' => 'The prototype has been created on the base of the item: '
    );

    private $inquiry_statuses = array(
        'initiated' => array(
            'icon' => 'new txt-green',
            'icon_new' => 'new-stroke',
            'title' => 'New inquiries',
            'title_color' => '',
            'description' => 'Waiting for the seller to make changes to the prototype and activate it.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'mandatory' => '<p>The buyer must <strong>wait</strong> until the <strong>seller activates</strong> the <strong>Prototype</strong>.</p>',
						'optional' 	=> '<p>The buyer can <strong>send more details</strong> about the <strong>Prototype</strong> to the seller using the <strong>Discuss</strong> button.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
                        'mandatory' => '<p>The seller has to <strong>Edit the Prototype’s</strong> details according to the <strong>buyer’s request</strong>.</p>
                                        <p>The seller has to <strong>Activate the Prototype</strong> to make it available to the buyer. The Inquiry’s <strong>status</strong> will be changed to <strong>In Process</strong>.</p>',
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'prototype' => array(
            'icon' => 'hourglass-processing txt-blue',
            'icon_new' => 'clock-stroke2',
            'title' => 'In process',
            'title_color' => '',
            'description' => 'Waiting for the buyer to confirm the prototype.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>The buyer has to <strong>check the Prototype</strong> by clicking <strong>View the Prototype</strong> button.</p>
                                        <p>The buyer has to <strong>Confirm</strong> or <strong>Decline</strong> the Prototype. The Inquiry’s <strong>status</strong> will be changed <strong>depending on the selection</strong>.</p>',
						'optional' 	=> '<p>The buyer can <strong>send more details</strong> about the <strong>Prototype</strong> to the seller using the <strong>Discuss</strong> button.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
                        'optional' 	=> '<p>The seller can <strong>check the Prototype</strong> by clicking <strong>View the Prototype</strong> button.</p>
                                        <p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>
                                        <p>The seller can <strong>Edit the Prototype’s</strong> details according to the <strong>buyer’s request</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'prototype_confirmed' => array(
            'icon' => 'thumbup txt-green',
            'icon_new' => 'ok-circle',
            'title' => 'Prototype confirmed',
            'title_color' => '',
            'description' => 'Waiting for the buyer to add shipping to address and starting the order.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>The buyer has to <strong>start a New Order</strong> using the <strong>Start Order</strong> button, and the Inquiry’s <strong>status</strong> will be changed to <strong>Order initiated</strong>.</p>'
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
        'completed' => array(
            'icon' => 'file-confirm txt-green',
            'icon_new' => 'file',
            'title' => 'Order initiated',
            'title_color' => 'txt-green',
            'description' => 'The order has been initiated based on this Inquiry.'
        ),
        'declined' => array(
            'icon' => 'minus-circle txt-red',
            'icon_new' => 'remove-circle',
            'title' => 'Declined',
            'title_color' => 'txt-red',
            'description' => 'The Inquiry has been declined.'
        ),
        'archived' => array(
            'icon' => 'archive txt-blue',
            'icon_new' => 'folder',
            'title' => 'Archived',
            'title_color' => 'txt-blue',
            'description' => 'The Inquiry has been archived.'
        ),
        'inquiry_number' => array(
            'icon' => 'magnifier txt-blue',
            'icon_new' => 'folder',
            'title' => 'Search result',
            'title_color' => 'txt-blue',
        ),
    );

    function index() {
       header('location: ' . __SITE_URL);
    }

    private function _load_main() {
        $this->load->model('Inquiry_Model', 'inquiry');
        $this->load->model('Items_Model', 'items');
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
    }

    public function inquiry_administration() {
        checkAdmin('manage_content');

        $this->_load_main();

        $data['statuses'] = arrayByKey($this->inquiry->count_inquiries_by_statuses(), 'status');
        $data['last_inquiries_id'] = $this->inquiry->get_inquiries_last_id();

        $this->view->assign($data);
        $this->view->assign('title', 'Inquiry');
        $this->view->display('admin/header_view');
        $this->view->display('admin/inquiry/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_inquiry_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_inquiry'   => 'ii.id_inquiry',
                'dt_status'       => 'ii.status',
                'dt_prototype'    => 'ip.title',
                'dt_quantity'     => 'ii.quantity',
                'dt_price'        => 'ii.price',
                'dt_date_created' => 'ii.date',
                'dt_date_changed' => 'ii.change_date'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'sSearch', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'status', 'type' => 'cleanInput'],
            ['as' => 'seller', 'key' => 'seller', 'type' => 'cleanInput'],
            ['as' => 'buyer', 'key' => 'buyer', 'type' => 'cleanInput'],
            ['as' => 'start_from',  'key' => 'start_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',  'key' => 'start_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_from',  'key' => 'update_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_to',  'key' => 'update_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'item', 'key' => 'item', 'type' => 'cleanInput'],
            ['as' => 'status_prototype', 'key' => 'status_prototype', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["ii.id_inquiry-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        $inquiries = $this->inquiry->get_inquiries($params);
        $inquiries_count = $this->inquiry->counter_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $inquiries_count,
            "iTotalDisplayRecords" => $inquiries_count,
			'aaData' => array()
        );

		if(empty($inquiries))
			jsonResponse('', 'success', $output);

        $items_id = array();
        $users_id = array();

        foreach ($inquiries as $item) {
            $items_id[$item['id_item']] = $item['id_item'];
            $users_id[$item['id_buyer']] = $item['id_buyer'];
            $users_id[$item['id_seller']] = $item['id_seller'];
            $company_users_id[$item['id_seller']] = $item['id_seller'];
        }

        if (!empty($items_id)) {
            $products_list = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $products_list = arrayByKey($products_list, 'id');
        }

        if (!empty($users_id)) {
            $users_list = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.status");
            $users_list = arrayByKey($users_list, 'idu');
        }

        if (!empty($company_users_id)) {
            $this->load->model('Company_Model', 'company');
            $companies_info = arrayByKey($this->company->get_sellers_base_company(implode(',', $company_users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
        }

		foreach ($inquiries as $inquiry) {
			if (!empty($inquiry['log'])) {
                $logs = array_reverse(with(json_decode("[{$inquiry['log']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));
				$logs_html = '<table class="table table-bordered table-hover mb-5">
								<thead>
									<tr role="row">
									<th class="w-90 tac">Date</th>
									<th class="w-90 tac">User</th>
									<th>Note(s)</th>
								</tr>
								</thead>
								<tbody>';

				foreach($logs as $key => $inquiry_timeline){
					$logs_html .= '<tr class="odd">
							<td class="tac">'.formatDate($inquiry_timeline['date'], 'm/d/Y H:i:s').'</td>
							<td class="tac">'.$inquiry_timeline['user'].'</td>
							<td>';

					if(isset($inquiry_timeline['price'])){
						$logs_html .= '<strong>Price: </strong> $ '.$inquiry_timeline['price'].'<br>';
					}

					$logs_html .= '<strong>Message: </strong> '.cleanOutput($inquiry_timeline['message']).'<br>';

					if(isset($inquiry_timeline['changes'])){
						$logs_html .= '<strong>Changes:</strong> '.cleanOutput($inquiry_timeline['changes']).'<br>';
					}

					if(isset($inquiry_timeline['comment'])){
						$logs_html .= '<strong>Comment:</strong> '.cleanOutput($inquiry_timeline['comment']);
					}

					$logs_html .= '</td>
							</tr>';
				}

				$logs_html .= '</tbody>
						</table>';
			} else {
				$logs_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> This inquiry does not have any log(s).</div>';
			}

			if (!empty($inquiry['changes_prototype'])) {
                $changes_prototype = with(json_decode($inquiry['changes_prototype'], true), function ($changes) {
                    return is_array($changes) ? $changes : array();
                });
				$changes_html = '<table class="table table-bordered table-hover">
									<thead>
										<tr role="row">
											<th class="w-40pr tac">Property</th>
											<th class="w-30pr tac">Old value</th>
											<th class="w-30pr tac">New value</th>
										</tr>
									</thead>
									<tbody>';

				foreach ($changes_prototype as $key => $changes_item) {
					$changes_html .= '<tr class="odd">';
					$old_values = (!empty($changes_item['old_values'])) ? '<span class="txt-gray-light">' . $changes_item['old_values'] . ' </span>' : '-';
					$changes_html .= '<td class="tac"><span class="txt-bold fs-14">' . $key . '</span></td>
										<td class="tac">'.$old_values.'</td>
										<td class="tac">'.$changes_item['current_value'].'</td>
									</tr>';
				}

				$changes_html .= '</tbody>
						</table>';
			} else {
				$changes_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> There are no changes for prototype.</div>';
			}

			$seller_name = $users_list[$inquiry['id_seller']]['username'];
			$seller_group = $users_list[$inquiry['id_seller']]['gr_name'];
			$buyer_name = $users_list[$inquiry['id_buyer']]['username'];
			$buyer_group = $users_list[$inquiry['id_buyer']]['gr_name'];
			$company_name = $companies_info[$inquiry['id_seller']]['name_company'];
			$company_link = getCompanyURL($companies_info[$inquiry['id_seller']]);

            $status_prototype_array = array(
				'declined' => array('icon'=>'minus-circle txt-red', 'title'=>'Declined'),
				'accepted' => array('icon'=>'ok-circle txt-green', 'title'=>'Accepted'),
				'in_progress' => array('icon'=>'hourglass-processing txt-blue', 'title'=>'In progress')
			);
            $status_array = $this->inquiry_statuses;

			$company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $company_name . "'  href='" . $company_link . "'></a>";

            $prototype = '<div class="tal clearfix">
                    <a class="ep-icon ep-icon_item txt-orange pull-left" title="View Prototype" href="' . __SITE_URL . 'prototype/item/' . $inquiry['id_prototype'] . '" ></a>
                    <div class="txt-blue pull-left">
                        <span class="ep-icon ep-icon_'.$status_prototype_array[$inquiry['status_prototype']]['icon'].'"></span>'
                        . $status_prototype_array[$inquiry['status_prototype']]['title'] .
                    '</div>
                </div>
                <div>' . $inquiry["title"] . '</div>';

            $item_img_link = getDisplayImageLink(array('{ID}' => $inquiry['id_item'], '{FILE_NAME}' => $products_list[$inquiry['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
            $item_prototype_img_link = getDisplayImageLink(array('{ID}' => $inquiry['id_prototype'], '{FILE_NAME}' => $inquiry['image']), 'items.prototype', array( 'thumb_size' => 1 ));

            //TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $inquiry['id_seller'], 'recipientStatus' => $users_list[$inquiry['id_seller']]['status'], 'module' => 7, 'item' => $inquiry['id_inquiry']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            //TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $inquiry['id_buyer'], 'recipientStatus' => $users_list[$inquiry['id_buyer']]['status'], 'module' => 7, 'item' => $inquiry['id_inquiry']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();

			$output['aaData'][] = array(
				'dt_id_inquiry' => $inquiry['id_inquiry'] .
					"<br /><a rel='log_details' title='View log' class='ep-icon ep-icon_plus'></a>",
				'dt_status' =>
					'<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $status_array[$inquiry['status']]['title'] . '" data-value="' . $inquiry['status'] . '" data-name="status"></a>'
					. '</div>'
					. '<div><span class="ep-icon ep-icon_'.$status_array[$inquiry['status']]['icon'].'"></span> '.$status_array[$inquiry['status']]['title'].'</div>',
				'dt_prototype' =>
					'<div class="img-prod pull-left w-30pr">'
						. '<img class="w-100pr mw-100" src="' . $item_prototype_img_link . '" alt="' . $inquiry['title'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-68pr">'
						. $prototype
					. '</div>',
				'dt_item' =>
					'<div class="img-prod pull-left w-30pr">'
                        . '<img
                                class="w-100pr"
                                src="' . $item_img_link . '"
                                alt="' . $products_list[$inquiry['id_item']]['title'] . '"
                            />'
					. '</div>'
					. '<div class="pull-right w-68pr">'
						. '<div class="clearfix">'
							. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Item" title="Filter by item" data-value-text="' . $products_list[$inquiry['id_item']]['title'] . '" data-value="' . $inquiry['id_item'] . '" data-name="item"></a>'
							. '<a class="ep-icon ep-icon_item txt-orange pull-left" title="View Product" href="' . __SITE_URL . 'item/' . strForURL($products_list[$inquiry['id_item']]['title']) . '-' . $products_list[$inquiry['id_item']]['id'] . '" ></a>'
							. '<div class="pull-right">
									<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $products_list[$inquiry['id_item']]['rating'] . '" data-readonly>
							</div>'
						. '</div>'
						. '<div>' . $products_list[$inquiry['id_item']]['title'] . '</div>'
					. '</div>',
				'dt_buyer' =>
					'<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $buyer_name . '" data-value-text="' . $buyer_name . '" data-value="' . $inquiry['id_buyer'] . '" data-name="buyer"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $buyer_name . '"  href="' . __SITE_URL . 'usr/' . strForURL($buyer_name) . '-' . $inquiry['id_buyer'] . '"></a>'
						. $btnChatBuyerView
					. '</div>
					<a href="usr/' . strForURL($buyer_name) . '-' . $inquiry['id_buyer'] . '" >' . $buyer_name . '</a> <br />'
					. '<span>' . $buyer_group . '</span>',
				'dt_seller' =>
					'<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $seller_name . '" data-value-text="' . $seller_name . '" data-value="' . $inquiry['id_seller'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_list[$inquiry['id_seller']]['fname'] . ' ' . $users_list[$inquiry['id_seller']]['lname'] . '"  href="' . __SITE_URL . 'usr/' . strForURL($seller_name) . '-' . $inquiry['id_seller'] . '"></a>'
						. $company_icon
						. $btnChatSellerView
					. '</div>
					<a href="usr/' . strForURL($seller_name) . '-' . $inquiry['id_seller'] . '" >' . $seller_name . '</a> (' . $company_name . ') <br />'
					. '<span>' . $seller_group . '</span>',
				'dt_quantity' => $inquiry['quantity'],
				'dt_price' => '$' . $inquiry['price'],
				'dt_date_created' => formatDate($inquiry['date']),
				'dt_date_changed' => formatDate($inquiry['change_date']),
				'dt_log' => $logs_html,
				'dt_changes' => $changes_html,
				'dt_comment' => $inquiry['comment'],
				'dt_inquiry_changes' => $inquiry['changes'],
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function my(){
        if (!logged_in()){
            $this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!have_right('buy_item') && !have_right('manage_seller_inquiries')) {
            $this->session->setMessages('Error: This page does not exist.', 'errors');
            headerRedirect();
        }

        if (!i_have_company() && !have_right('buy_item')) {
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
        $data['status_array'] = $this->inquiry_statuses;

        // IF THE STATUS WAS NOT SETTED IN THE URI - DEFAULT STATUS IS "NEW"
        if (!isset($data['status_array'][$data['status_select']])){
            $data['status_select'] = 'all';
        }

        $id_user = privileged_user_id();

        // PREPARING CONDITIONS FOR DATABASE QUERIES
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

        // GET SELECTED OFFER NUMBER FROM URI - IF EXIST
		if(isset($uri['inquiry_number'])){
        	$data['id_inquiry'] = $conditions['inquiry_number'] = toId($uri['inquiry_number']);
			$data['status_select'] = 'inquiry_number';
			$conditions['status'] = 'all';
		}

        global $tmvc;
        $data['inquiry_per_page'] = $conditions['limit'] = $tmvc->my_config['user_inquiry_per_page'];

        // GET INQUIRIES DETAIL
        $data['users_inquiries'] = $this->inquiry->get_inquiries($conditions);

        // GET INQUIRIES STATUSES COUNTERS
        $data['statuses'] = arrayByKey($this->inquiry->count_inquiries_by_statuses($count_conditions), 'status');
        $data['status_select_count'] = $data['statuses'][$data['status_select']]['counter'];

        // COUNT ARCHIVED INQUIRIES
        $archived_counters = $this->inquiry->count_inquiries_by_statuses($archived_conditions);

        if ($data['status_select'] != 'archived')
            $data['status_select_count'] = $this->inquiry->counter_by_conditions($conditions);
        else
            $data['status_select_count'] = $data['statuses']['archived']['counter'];

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

        $items_id = array();
        $users_id = array();

        if (!empty($data['users_inquiries'])) {
            foreach ($data['users_inquiries'] as $item) {
                $items_id[$item['id_item']] = $item['id_item'];

                if (have_right('buy_item')) {
                    $users_id[$item['id_seller']] = $item['id_seller'];
                } else{
                    $users_id[$item['id_buyer']] = $item['id_buyer'];
                }
            }
        }

        // GET ITEMS INFO FOR ALL INQUIRIES
        if (!empty($items_id)) {
            $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $data['products_list'] = arrayByKey($data['products_list'], 'id');
        }

        // GET USERS INFO FOR ALL INQUIRIES
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
        $this->view->display("new/inquiry/index_view");
        $this->view->display("new/footer_view");
    }

    public function ajax_inquiry_operation() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Notify_Model', 'notify');

        $id_user = privileged_user_id();
        $op = $this->uri->segment(3);

        switch ($op) {
            // REMOVE INQUIRY - CHANGE USER STATE TO 2
            // ATTENTION: THIS ACTION DID NOT REMOVE THE INQUIRY DATA FROM DB
            case 'remove_inquiry':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_inquiries'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET INQUIRY
                $id_inquiry = intVal($_POST['inquiry']);
                $inquiry_info = $this->inquiry->get_inquiry($id_inquiry);

                // CHECK IF INQUIRY EXIST
                if (empty($inquiry_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                // CHECK IS UDER IS PRIVILEGED TO CHANGE THIS INQUIRY
                if (!is_privileged('user', $inquiry_info['id_seller'], true) && !is_my($inquiry_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // VIEW STATE
                if (have_right('buy_item')) {
                    $user_state = 'state_buyer';
                } else {
                    $user_state = 'state_seller';
                }

                //PERMIT TO REMOVE inquiry ONLY IF STATUS IS DECLINED, COMPLETED OR INQUIRY STATE IS ARCHIVED
                if (!in_array($inquiry_info['status'], array('completed', 'declined')) && $inquiry_info[$user_state] != 1)
                    jsonResponse(translate('systmess_error_remove_completed_inquiry'), 'info');

                // UPDATE inquiry BY USER TYPE
                $update_inquiry = array($user_state => 2);

                if ($this->inquiry->update_inquiry($id_inquiry, $update_inquiry)) {
                    $statistic = array();
                    $status = $inquiry_info['status'];
                    if($inquiry_info['status'] == 'completed')
                        $status = 'accepted';

                    if (have_right('buy_item')) {
                        $statistic[$id_user] = array('inquiries_sent' => -1, 'inquiries_' . $status => -1);
                    } else {
                        $statistic[$id_user] = array('inquiries_received' => -1, 'inquiries_' . $status => -1);
                    }

                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic($statistic);

                    jsonResponse(translate('systmess_success_remove_inquiry'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // ARCHIVE THE INQUIRY - CHANGE USER STATE TO 1
            // NOTE: EACH USER HAVE HIS STATE
            case 'archived_inquiry':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_inquiries'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET inquiry DETAILS
                $id_inquiry = intVal($_POST['inquiry']);
                $inquiry_info = $this->inquiry->get_inquiry($id_inquiry);

                // CHECK IF inquiry EXIST
                if (empty($inquiry_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                // CHECK RIGHTS
                if (!is_privileged('user', $inquiry_info['id_seller'], true) && !is_my($inquiry_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // CHECK INQUIRY STATUS - MUST BE "completed" OR "declined"
                if (!in_array($inquiry_info['status'], array('completed', 'declined')))
                    jsonResponse(translate('systmess_error_archived_not_completed_inquiry'));

                // VIEW STATE
                if (have_right('buy_item')) {
                    $user_state = 'state_buyer';
                } else {
                    $user_state = 'state_seller';
                }

                // CHECK IF INQUIRY HAS NOT BEEN ARCHIVED BEFORE
                if ($inquiry_info[$user_state] != 0)
                    jsonResponse(translate('systmess_error_archive_inquiry_already_archived'), 'info');

                // UPDATE INQUIRY - ARCHIVED
                $update_inquiry = array($user_state => 1);

                if ($this->inquiry->update_inquiry($id_inquiry, $update_inquiry)) {
                    jsonResponse(translate('systmess_success_arhive_inquiry'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // CHECK FOR NEW INQUIRY AND RETURN COUNTER FOR ADMIN
            case 'check_new':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $lastId = $_POST['lastId'];
                $inquiries_count = $this->inquiry->get_count_new_inquiries($lastId);

                if ($inquiries_count) {
                    $last_inquiries_id = $this->inquiry->get_inquiries_last_id();
                    jsonResponse('', 'success', array('nr_new' => $inquiries_count, 'lastId' => $last_inquiries_id));
                } else
                    jsonResponse('Error: New inquiries do not exist');
            break;
            // INQUIRIES USERS DISCUSIONS
            case 'resend_inquiry':
                if (!(have_right('manage_seller_inquiries') || have_right('buy_item')))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                    'field' => 'message',
                    'label' => 'Message',
                    'rules' => array('required' => '')
                    ),
                    array(
                    'field' => 'inquiry',
                    'label' => 'Inquiry information',
                    'rules' => array('required' => '', 'integer' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if (!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_inquiry = intVal($_POST['inquiry']);
                $params = array();

                if (have_right('manage_seller_inquiries'))
                    $params['seller'] = $id_user;
                else
                    $params['buyer'] = $id_user;

                $inquiry_info = $this->inquiry->get_inquiry($id_inquiry, $params);

                if (empty($inquiry_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'completed');
                if (in_array($inquiry_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_discuss_completed_inquiry'), 'info');

                $message = cleanInput($_POST['message']);
                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    "message" => $message,
                );

                if (have_right('manage_seller_inquiries')) {
                    $user_send = array($inquiry_info['id_buyer']);
                    $log['user'] = 'Seller';
                    $receiver = 'buyer';
                } else {
                    $user_send = array($inquiry_info['id_seller']);
                    $log['user'] = 'Buyer';
                    $receiver = 'seller';
                }

                if ($this->inquiry->change_inquiry_log($id_inquiry, json_encode($log))) {
                    $inquiry_number = orderNumber($id_inquiry);

					$data_systmess = [
						'mess_code' => 'inquiry_changed',
						'id_users'  => $user_send,
						'replace'   => [
							'[INQUIRY_ID]'   => $inquiry_number,
							'[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
							'[LINK]'         => __SITE_URL . 'inquiry/my'
						],
						'systmess' => true
					];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_resend_inquiry', ['{RECIPIENT_USER_GROUP}' => $receiver]), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            //CREATE THE INQUIRY REQUEST
            case 'create_inquiry':
                // CHECK RIGHTS FOR BUYER
                if (!have_right('buy_item')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $this->validator->set_rules([
                    [
                        'field' => 'quantity',
                        'label' => 'Quantity',
                        'rules' => ['required' => '']
                    ],
                    [
                        'field' => 'changes',
                        'label' => 'The necessary changes',
                        'rules' => ['required' => '', 'max_len[1000]' => '']
                    ],
                    [
                        'field' => 'comment',
                        'label' => 'Comment',
                        'rules' => ['max_len[1000]' => '']
                    ],
                    [
                        'field' => 'item',
                        'label' => 'Item informaton',
                        'rules' => ['required' => '', 'integer' => '']
                    ],
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

                if (!$item['inquiry']) {
                    jsonResponse(translate('systmess_error_create_inquiry_not_support'));
                }

                if ($item['is_out_of_stock']) {
                    jsonResponse(translate('translations_out_of_stock_system_message'));
                }

                // CHECK FOR LAST ITEM SNAPSHOT
                $this->load->model('Item_Snapshot_Model', 'item_snapshot');
                $item_info = $this->item_snapshot->get_last_item_snapshot($itemId);
                if(empty($item_info)){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $details = [];
                $finalPrice = moneyToDecimal($item['final_price']);
                $discount = $item['discount'];

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
                    $discount = $itemVariant['discount'];
                    $finalPrice = moneyToDecimal($itemVariant['final_price']);

                    foreach ((array) $variant['options'] as $optionId) {
                        if (!isset($allVariantOptions[$optionId])) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $details[] = $allVariantOptions[$optionId]['propertyName'] . ' : ' . $allVariantOptions[$optionId]['name'];
                    }
                }

                if (!empty($discount)) {
                    array_unshift($details, "Discount: {$discount}%");
                }

                // INSERT THE INQUIRY DATA TO DATABASE
                $date_created = date('m/d/Y H:i:s');
                $id_seller = $item_info['id_seller'];
                $changes = cleanInput($_POST['changes']);
                $log = array(
                    'date' => $date_created,
                    'user' => 'Buyer',
                    'message' => $this->inquiry_logs['inquiry_init'],
                    'changes' => $changes
                );

                $this->load->library('Cleanhtml', 'clean');

                $comment = '';
                if(!empty($_POST['comment'])){
                    $this->clean->defaultTextarea();
                    $comment = $this->clean->sanitize($_POST['comment']);
                    $comment = cleanInput($comment);
                    $log['comment'] = $comment;
                }

                $inquiryQuantity = $request->getInt('quantity');

                $id_inquiry = $this->inquiry->set_inquiry([
                    'id_item' => $itemId,
                    'quantity' => $inquiryQuantity,
                    'id_seller' => $id_seller,
                    'id_buyer' => $id_user,
                    'comment' => $comment,
                    'changes' => $changes,
                    'detail_item' => implode(', ', $details),
                    'log' => json_encode($log),
                    'date' => formatDate($date_created, 'Y-m-d H:i:s'),
                ]);

                if ($id_inquiry) {
                    // CREATE DEFAULT PROTOTYPE FOR INQUIRY
                    $this->load->model('Prototype_Model', 'prototype');

                    $price = array(
                        'old_price' => $finalPrice,
                        'current_price' => ''
                    );

                    $insert_prototype = array(
                        'id_item' => $itemId,
                        'id_seller' => $id_seller,
                        'id_buyer' => $id_user,
                        'id_request' => $id_inquiry,
                        'type_prototype' => 'inquiry',
                        'title' => $item_info['title'],
                        'ship_from' => $item_info['country'],
                        'country_abr' => $item_info['country_abr'],
                        'date' => formatDate($date_created, 'Y-m-d H:i:s'),
                        'image' => $item_info['main_image'],
                        'quantity' => $inquiryQuantity,
                        'hs_tariff_number' => $item_info['hs_tariff_number'],
                        'prototype_weight' => $item_info['item_weight'],
                        'prototype_length' => $item_info['item_length'],
                        'prototype_width' => $item_info['item_width'],
                        'prototype_height' => $item_info['item_height'],
                        'description' => $item_info['description'],
                        'attributes' => $item_info['aditional_info'],
                        'unit_name' => $item_info['unit_name'],
                        'price_history' => serialize($price),
                        'price' => $finalPrice,
                        'detail_item' => implode(', ', $details),
                        'log' => '{"date":"' . $date_created . '","message":"' . $this->inquiry_logs['prototype_created'] . '"}'
                    );

                    $id_prototype = $this->prototype->set_prototype($insert_prototype);

                    // UPDATE THE INQUIRY
                    $update_inquiry = array(
                        'price' => $insert_prototype['price'],
                        'id_prototype' => $id_prototype
                    );

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $snapshotPath = ItemPathGenerator::snapshotDraftUpload($item_info['id_snapshot'], $item_info['main_image']);

                    if($publicDisk->fileExists($snapshotPath)){
                        try {
                            $publicDisk->write(
                                ItemPathGenerator::prototypeDraftUpload($id_prototype, $item_info['main_image']),
                                $publicDisk->read($snapshotPath)
                            );
                            $publicDisk->write(
                                ItemPathGenerator::prototypeDraftUpload($id_prototype, '/thumb_1_' . $item_info['main_image']),
                                $publicDisk->read($snapshotPath)
                            );

                        } catch (\Throwable $th) {
                            /** @var Inquirys_Model $inquiryModel */
                            $inquiryModel = model(Inquirys_Model::class);
                            $inquiryModel->deleteOne($id_inquiry);

                            /** @var Prototypes_Model $prototypeModel */
                            $prototypeModel = model(Prototypes_Model::class);
                            $prototypeModel->deleteOne($id_prototype);

                            try {
                                $publicDisk->deleteDirectory(ItemPathGenerator::prototypeDirectory($id_prototype));
                            } catch (\Throwable $th) {
                                //NOTHIND TO DO
                            }

                            jsonResponse(translate('systmess_error_send_inquiry'));
                        }

                    }

                    // SET USERS STATISTIC
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $users_statistic = array(
                        $insert_prototype['id_seller'] => array('inquiries_received' => 1),
                        $insert_prototype['id_buyer'] => array('inquiries_sent' => 1)
                    );
                    $this->statistic->set_users_statistic($users_statistic);

                    $search_info = array();
                    $users_id = array($id_seller, $id_user);
                    $users_info = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    foreach ($users_info as $user_info)
                        $search_info[] = $user_info['username'];

                    $this->load->model('Company_Model', 'company');
                    $company_info = $this->company->get_seller_base_company($item_info['id_seller'], "cb.id_company, cb.name_company, cb.id_user");

                    $search_info[] = $company_info['name_company'];
                    $search_info[] = orderNumber($id_inquiry);
                    $search_info[] = $item_info['title'];

                    $update_inquiry['for_search'] = implode(', ', $search_info);

                    $this->inquiry->update_inquiry($id_inquiry, $update_inquiry);

                    // SEND USERS NOTIFICATION
                    $inquiry_number = orderNumber($id_inquiry);

					$data_systmess = [
						'mess_code' => 'inquiry_new_to_seller',
						'id_item'   => $id_inquiry,
						'id_users'  => [$id_seller],
						'replace'   => [
							'[INQUIRY_ID]'   => $inquiry_number,
							'[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
							'[ITEM]'         => cleanOutput($item_info['title']),
							'[USER]'         => cleanOutput(user_name_session()),
							'[LINK]'         => __SITE_URL . 'inquiry/my'
						],
						'systmess' => true,
					];

                    $this->notify->send_notify($data_systmess);

					$data_calendar = [
						'mess_code' => 'inquiry_new_to_buyer',
						'id_item'   => $id_inquiry,
						'id_users'  => [$id_user],
						'replace'   => [
							'[INQUIRY_ID]'   => $inquiry_number,
							'[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
							'[ITEM]'         => cleanOutput($item_info['title']),
							'[LINK]'         => __SITE_URL . 'inquiry/my'
						],
						'systmess' => false,
					];

                    $this->notify->send_notify($data_calendar);

                    jsonResponse(translate('systmess_success_send_inquiry'), 'success');
                } else{
                    if (empty($id_inquiry)) {
                        jsonResponse(translate('systmess_internal_server_error'));
                    }
                }
                // CREATE DEFAULT PROTOTYPE FOR INQUIRY
                $this->load->model('Prototype_Model', 'prototype');

                $price = array(
                    'old_price' => $finalPrice,
                    'current_price' => ''
                );

                $insert_prototype = array(
                    'id_item' => $itemId,
                    'id_seller' => $id_seller,
                    'id_buyer' => $id_user,
                    'id_request' => $id_inquiry,
                    'type_prototype' => 'inquiry',
                    'title' => $item_info['title'],
                    'ship_from' => $item_info['country'],
                    'country_abr' => $item_info['country_abr'],
                    'date' => formatDate($date_created, 'Y-m-d H:i:s'),
                    'image' => $item_info['main_image'],
                    'quantity' => $inquiryQuantity,
                    'hs_tariff_number' => $item_info['hs_tariff_number'],
                    'prototype_weight' => $item_info['item_weight'],
                    'prototype_length' => $item_info['item_length'],
                    'prototype_width' => $item_info['item_width'],
                    'prototype_height' => $item_info['item_height'],
                    'description' => $item_info['description'],
                    'attributes' => $item_info['aditional_info'],
                    'unit_name' => $item_info['unit_name'],
                    'price_history' => serialize($price),
                    'price' => $finalPrice,
                    'detail_item' => implode(', ', $details),
                    'log' => '{"date":"' . $date_created . '","message":"' . $this->inquiry_logs['prototype_created'] . '"}'
                );

                $id_prototype = $this->prototype->set_prototype($insert_prototype);

                // UPDATE THE INQUIRY
                $update_inquiry = array(
                    'price' => $insert_prototype['price'],
                    'id_prototype' => $id_prototype
                );

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $snapshotDirectory = ItemPathGenerator::snapshotDraftUpload($item_info['id_snapshot'], $item_info['main_image']);

                if($publicDisk->fileExists($snapshotDirectory)){
                    try {
                        $publicDisk->write(
                            ItemPathGenerator::prototypeDraftUpload($id_prototype, $item_info['main_image']),
                            $publicDisk->read($snapshotDirectory)
                        );
                        $publicDisk->write(
                            ItemPathGenerator::prototypeDraftUpload($id_prototype, '/thumb_1_' . $item_info['main_image']),
                            $publicDisk->read($snapshotDirectory)
                        );

                    } catch (\Throwable $th) {
                        /** @var Inquirys_Model $inquiryModel */
                        $inquiryModel = model(Inquirys_Model::class);
                        $inquiryModel->deleteOne($id_inquiry);

                        /** @var Prototypes_Model $prototypeModel */
                        $prototypeModel = model(Prototypes_Model::class);
                        $prototypeModel->deleteOne($id_prototype);

                        try {
                            $publicDisk->deleteDirectory(ItemPathGenerator::prototypeDirectory($id_prototype));
                        } catch (\Throwable $th) {
                            jsonResponse(translate('systmess_error_delete_prototype_dir'));
                        }

                        jsonResponse(translate('systmess_error_send_inquiry'));
                    }

                } else{
                    jsonResponse(translate('systmess_error_send_inquiry'));
                }

                // SET USERS STATISTIC
                $this->load->model('User_Statistic_Model', 'statistic');
                $users_statistic = array(
                    $insert_prototype['id_seller'] => array('inquiries_received' => 1),
                    $insert_prototype['id_buyer'] => array('inquiries_sent' => 1)
                );
                $this->statistic->set_users_statistic($users_statistic);

                $search_info = array();
                $users_id = array($id_seller, $id_user);
                $users_info = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                foreach ($users_info as $user_info)
                    $search_info[] = $user_info['username'];

                $this->load->model('Company_Model', 'company');
                $company_info = $this->company->get_seller_base_company($item_info['id_seller'], "cb.id_company, cb.name_company, cb.id_user");

                $search_info[] = $company_info['name_company'];
                $search_info[] = orderNumber($id_inquiry);
                $search_info[] = $item_info['title'];

                $update_inquiry['for_search'] = implode(', ', $search_info);

                $this->inquiry->update_inquiry($id_inquiry, $update_inquiry);

                // SEND USERS NOTIFICATION
                $inquiry_number = orderNumber($id_inquiry);

                $data_systmess = [
                    'mess_code' => 'inquiry_new_to_seller',
                    'id_item'   => $id_inquiry,
                    'id_users'  => [$id_seller],
                    'replace'   => [
                        '[INQUIRY_ID]'   => $inquiry_number,
                        '[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
                        '[ITEM]'         => cleanOutput($item_info['title']),
                        '[USER]'         => cleanOutput(user_name_session()),
                        '[LINK]'         => __SITE_URL . 'inquiry/my'
                    ],
                    'systmess' => true,
                ];

                $this->notify->send_notify($data_systmess);

                $data_calendar = [
                    'mess_code' => 'inquiry_new_to_buyer',
                    'id_item'   => $id_inquiry,
                    'id_users'  => [$id_user],
                    'replace'   => [
                        '[INQUIRY_ID]'   => $inquiry_number,
                        '[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
                        '[ITEM]'         => cleanOutput($item_info['title']),
                        '[LINK]'         => __SITE_URL . 'inquiry/my'
                    ],
                    'systmess' => false,
                ];

                $this->notify->send_notify($data_calendar);

                jsonResponse(translate('systmess_success_send_inquiry'), 'success');
            break;
            // DECLINE THE INQUIRY
            case 'declined_inquiry':
                if (!(have_right('manage_seller_inquiries') || have_right('buy_item')))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_inquiry = intVal($_POST['inquiry']);
                $params = array();

                if (have_right('manage_seller_inquiries'))
                    $params['seller'] = $id_user;
                else
                    $params['buyer'] = $id_user;

                $inquiry_info = $this->inquiry->get_inquiry($id_inquiry, $params);

                if (empty($inquiry_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'completed');
                if (in_array($inquiry_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_decline_completed_inquiry'));

                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    "message" => $this->inquiry_logs['decline_inquiry'],
                );
                if (have_right('manage_seller_inquiries')) {
                    $id_user_send = $inquiry_info['id_buyer'];
                    $status = 'inquiry_declined_seller';
                    $log['user'] = 'Seller';
                } else {
                    $id_user_send = $inquiry_info['id_seller'];
                    $status = 'inquiry_declined_buyer';
                    $log['user'] = 'Buyer';
                }
                $update_inquiry = array(
                    'status' => 'declined',
                    'log' => $inquiry_info['log'] .','. json_encode($log)
                );
                if ($this->inquiry->update_inquiry($id_inquiry, $update_inquiry)) {
                    // SET USERS STATISTIC
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        $inquiry_info['id_seller'] => array('inquiries_declined' => 1),
                        $inquiry_info['id_buyer'] => array('inquiries_declined' => 1)
                    ));

                    $inquiry_number = orderNumber($id_inquiry);
					$data_systmess = [
						'mess_code' => $status,
						'id_item'   => $id_inquiry,
						'id_users'  => [$id_user_send],
						'replace'   => [
							'[INQUIRY_ID]'   => $inquiry_number,
							'[INQUIRY_LINK]' => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
							'[LINK]'         => __SITE_URL . 'inquiry/my'
						],
						'systmess' => true,
					];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_decline_inquiry'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // CONFIRM THE INQUIRY
            case 'create_order':
                checkPermisionAjax('buy_item');

                $this->load->model('Item_Snapshot_Model', 'snapshot');
                $this->load->model('Orders_Model', 'orders');
                $this->load->model('Country_Model', 'country');
                $this->load->model('Prototype_Model', 'prototype');

                // VALIDATE POST DATA
                $validator_rules = array(
                    array(
                        'field' => 'id_inquiry',
                        'label' => 'Inquiry detail',
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

                $id_inquiry = (int) $_POST['id_inquiry'];
                $inquiry_info = $this->inquiry->get_inquiry($id_inquiry, array('buyer' => $id_user));

                if (empty($inquiry_info)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #4');
                }

                if ($inquiry_info['status'] != 'prototype_confirmed'){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #5');
                }

                // UPDATE USERS STATISTIC
                model('user_statistic')->set_users_statistic(array(
                    $inquiry_info['id_seller'] => array('inquiries_accepted' => 1),
                    $inquiry_info['id_buyer'] => array('inquiries_accepted' => 1)
                ));

                $prototype_info = $this->prototype->get_prototype($inquiry_info['id_prototype']);
                $inquiry_number = orderNumber($id_inquiry);
                $order_log = array(
                    'date' => date('m/d/Y h:i:s A'),
                    'user' => 'Buyer',
                    'message' => "{$this->inquiry_logs['order_summary']} {$inquiry_number}"
                );

                $new_status_info = $this->orders->get_status_by_alias('new_order');
				if(empty($new_status_info)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

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

                $total_order_weight = $prototype_info['quantity'] * $prototype_info['prototype_weight'];
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
                    'id_buyer' => $id_user,
                    'id_seller' => $prototype_info['id_seller'],
                    'price' => $prototype_info['quantity'] * $prototype_info['price'],
                    'final_price' => $prototype_info['quantity'] * $prototype_info['price'],
                    'weight' => $total_order_weight,
                    'comment' => $this->inquiry_logs['sold_by_inquiry'] . ': ' . $inquiry_number,
                    'order_type' => 'inquiry',
                    'id_by_type' => $id_inquiry,
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
                        'message' => "{$this->inquiry_logs['order_summary']} {$inquiry_number}"
                    )))
                );

                $id_order = $this->orders->insert_order($order);
                if (!$id_order){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $order_number = orderNumber($id_order);

                // PREPARE SEARCH INFO
                $users = $this->user->getSimpleUsers(implode(',', array($inquiry_info['id_buyer'], $inquiry_info['id_seller'])), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                $this->load->model('Company_Model', 'company');
                $company = $this->company->get_seller_base_company($inquiry_info['id_seller'], 'cb.name_company');
                $search_info = $order_number . ', ' . $users[0]['username'] . ', ' . $users[1]['username'] .', '. $company['name_company'] .', '.$prototype_info['title'];

                // UPDATE SEARCH INFO - ADD ORDER ID
                $this->orders->change_order($id_order, array('search_info' => $search_info));

                $insert_snapshot = array(
                    'id_item' => $prototype_info['id_item'],
                    'id_seller' => $prototype_info['id_seller'],
                    'additional_id' => $prototype_info['id_prototype'],
                    'title' => $prototype_info['title'],
                    'hs_tariff_number' => $prototype_info['hs_tariff_number'],
                    'country' => $prototype_info['ship_from'],
                    'country_abr' => $prototype_info['country_abr'],
                    'price' => $prototype_info['price'],
                    'item_weight' => $prototype_info['prototype_weight'],
                    'item_length' => $prototype_info['prototype_length'],
                    'item_width' => $prototype_info['prototype_width'],
                    'item_height' => $prototype_info['prototype_height'],
                    'currency' => '$',
                    'description' => $prototype_info['description'],
                    'aditional_info' => $prototype_info['changes'],
                    'main_image' => $prototype_info['image'],
                    'unit_name' => $prototype_info['unit_name'],
                    'type' => 'prototype'
                );

                $this->snapshot->update_item_snapshots($prototype_info['id_item'], 'prototype', array('is_last_snapshot' => 0));
                $id_snapshot = $this->snapshot->insert_item_snapshot($insert_snapshot);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $prototypeDirectory = ItemPathGenerator::prototypeDraftUpload($prototype_info['id_prototype'], $prototype_info['image']);

                if ($publicDisk->fileExists($prototypeDirectory)) {
                    try {
                        $publicDisk->write(
                            ItemPathGenerator::snapshotDraftUpload($id_snapshot, $prototype_info['image']),
                            $publicDisk->read($prototypeDirectory)
                        );
                        $publicDisk->write(
                            ItemPathGenerator::snapshotDraftUpload($id_snapshot, '/thumb_1_' . $prototype_info['image']),
                            $publicDisk->read($prototypeDirectory)
                        );

                    } catch (UnableToWriteFile $th) {
                        /** @var Product_Orders_Model $orderModel */
                        $orderModel = model(Product_Orders_Model::class);
                        $orderModel->deleteOne($id_order);
                        try {
                            $publicDisk->deleteDirectory(ItemPathGenerator::snapshotDirectory($id_snapshot));
                        } catch (UnableToDeleteFile $th) {
                            jsonResponse(translate('systmess_error_delete_inquiry_file'));
                        }
                        jsonResponse(translate('systmess_error_initiate_order'));
                    }
                } else {
                    jsonResponse(translate('systmess_error_send_inquiry'));
                }

                //UPDATE INQUIRY STATUS
                if (!$this->inquiry->update_inquiry($id_inquiry, array('status' => 'completed'))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $ordered_item = array(
                    'id_order' => $id_order,
                    'id_item' => $prototype_info['id_item'],
                    'id_snapshot' => $id_snapshot,
                    'price_ordered' => $prototype_info['price'],
                    'quantity_ordered' => $prototype_info['quantity'],
                    'weight_ordered' => $prototype_info['prototype_weight'],
                    'detail_ordered' => 'The item has been sold by inquiry ' . $inquiry_number,
                );
                $this->orders->set_ordered_item($ordered_item);

                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    "message" => 'The order '.$order_number.' has been initiated.',
                    "user" => 'Buyer'
                );
                $this->inquiry->change_inquiry_log($id_inquiry, json_encode($log));

				$data_systmess = [
					'mess_code' => 'inquiry_confirmed',
					'id_item'   => $id_inquiry,
					'id_users'  => [$id_user, $inquiry_info['id_seller']],
					'replace'   => [
						'[INQUIRY_ID]' => $inquiry_number,
						'[ORDER_ID]'   => orderNumber($id_order),
						'[ORDER_LINK]' => __SITE_URL . 'order/my/order_number/' . $id_order,
						'[LINK]'       => __SITE_URL . 'inquiry/my/inquiry_number/' . $id_inquiry,
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_inquiry_create_order', ['{ORDER_NUMBER}' => orderNumber($id_order)]), 'success', array('order' => $id_order));

            break;
        }
    }

    function ajax_inquiry_info() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $id_user = privileged_user_id();

        switch ($_POST['type']) {
            case 'inquiry':
                checkPermisionAjax('manage_seller_inquiries,buy_item');

                //region Check if Inquiry exists and User permition
                $id_inquiry = (int) $_POST['inquiry'];
                $data['inquiry'] = $this->inquiry->get_inquiry($id_inquiry);

                if (empty($data['inquiry']) || !in_array(privileged_user_id(), array((int) $data['inquiry']['id_seller'], (int) $data['inquiry']['id_buyer']))){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                //endregion Check if Inquiry exists and User permition

                //region Prototype
                $data['prototype'] = model('prototype')->get_prototype((int) $data['inquiry']['id_prototype']);
                //endregion Prototype

                //region Prepare Inquiry timeline
                $data['inquiry']['log'] = array_reverse(with(json_decode("[{$data['inquiry']['log']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));
                //endregion Prepare Inquiry timeline

                //region user information
                if(have_right('buy_item')){
                    $data['seller_info'] = model('company')->get_seller_base_company(
                        (int) $data['inquiry']['id_seller'],
                        "cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company, u.user_group",
                        true
                    );
                } else{
                    $data['buyer_info'] = $this->user->getSimpleUser((int) $data['inquiry']['id_buyer'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name, users.user_group, users.user_photo");
                }
                //endregion user information

                //region Inquiry status details
                $data['inquiry_status'] = $this->inquiry_statuses[$data['inquiry']['status']];
                $data['inquiry_status_user'] = have_right('buy_item') ? 'buyer' : 'seller';
                //endregion Inquiry status details

                if(have_right('buy_item')){
					$btnChatSeller = new ChatButton(['recipient' => $data['inquiry']['id_seller'], 'recipientStatus' => 'active', 'module' => 7, 'item' => $data['inquiry']['id_inquiry']], ['text' => 'Chat with seller']);
					$data['btnChatSeller'] = $btnChatSeller->button();

					$btnChatSeller2 = new ChatButton(['recipient' => $data['inquiry']['id_seller'], 'recipientStatus' => 'active', 'module' => 7, 'item' => $data['inquiry']['id_inquiry']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with seller']);
					$data['btnChatSeller2'] = $btnChatSeller2->button();
				}else{
					$btnChatBuyer = new ChatButton(['recipient' => $data['inquiry']['id_buyer'], 'recipientStatus' => 'active', 'module' => 7, 'item' => $data['inquiry']['id_inquiry']], ['text' => 'Chat with buyer']);
					$data['btnChatBuyer'] = $btnChatBuyer->button();

					$btnChatBuyer2 = new ChatButton(['recipient' => $data['inquiry']['id_buyer'], 'recipientStatus' => 'active', 'module' => 7, 'item' => $data['inquiry']['id_inquiry']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with buyer']);
					$data['btnChatBuyer2'] = $btnChatBuyer2->button();
				}

                $content = $this->view->fetch('new/inquiry/inquiry_detail_view', $data);

                jsonResponse('', 'success', array('content' => $content));
            break;
            case 'inquiry_list':
                $statuses = array('inquiry_number', 'all', 'declined', 'initiated', 'completed', 'prototype', 'prototype_confirmed', 'archived');
                if (!in_array($_POST['status'], $statuses)) {
                    jsonResponse('Error: The status you selected is not correct.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $keywords = cleanInput(cut_str(arrayGet($_POST, 'keywords', '')));
                $per_page = config('user_inquiry_per_page');
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                if (!(have_right('manage_seller_inquiries') || have_right('buy_item'))) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                if (have_right('buy_item')) {
                    $conditions = array('buyer' => $id_user);
                } else {
                    $conditions = array('seller' => $id_user);
                }

                if ($_POST['status'] === 'inquiry_number') {
                    if (!empty($keywords)) {
                        $conditions['inquiry_number'] = toId($keywords);
                    }
                } else if ($_POST['status'] != 'archived') {
                    $conditions['status'] = cleanInput($_POST['status']);
                } else {
                    if (have_right('buy_item')) {
                        $conditions['state_buyer'] = 1;
                    } else {
                        $conditions['state_seller'] = 1;
                    }
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $data['users_inquiries'] = $this->inquiry->get_inquiries($conditions);
                $total_inquiry_by_status = $this->inquiry->counter_by_conditions($conditions);

                if (empty($data['users_inquiries'])) {
                    jsonResponse('0 inquiries found by this search.', 'info', array('total_inquiry_by_status' => 0));
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_inquiries'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];

                    if (have_right('buy_item')) {
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    } elseif (have_right('manage_seller_inquiries')) {
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                    }
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->inquiry_statuses;

                $inquiries_list = $this->view->fetch('new/inquiry/inquiry_list_view', $data);

                jsonResponse('', 'success', array('inquiries_list' => $inquiries_list, 'total_inquiry_by_status' => $total_inquiry_by_status));
            break;
            case 'update_sidebar_counters':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('manage_seller_inquiries') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $this->_load_main();

                // PREPARING CONDITIONS
                if (have_right('buy_item')) {
                    $count_conditions = array('id_buyer' => $id_user);
                    $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
                } else {
                    $count_conditions = array('id_seller' => $id_user);
                    $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
                }

                // GET COUNTERS
                $statuses_counters = arrayByKey($this->inquiry->count_inquiries_by_statuses($count_conditions), 'status');
                $archived_counters = $this->inquiry->count_inquiries_by_statuses($archived_conditions);
                $statuses_counters['archived'] = array('status' => 'archived', 'counter' => 0);
                if (!empty($archived_counters)) {
                    foreach ($archived_counters as $status_couter)
                    $statuses_counters['archived']['counter'] += $status_couter['counter'];
                }

                // RETURN RESPONCE
                jsonResponse('', 'success', array('counters' => $statuses_counters));
            break;
            case 'search_inquiry':
                $keywords = cleanInput(cut_str($_POST['keywords']));
                if (empty($keywords))
                    jsonResponse('Error: Search keywords is required.');

                global $tmvc;
                $per_page = $tmvc->my_config['user_inquiry_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

				$search_filter = cleanInput($_POST['search_filter']);
				if (!empty($search_filter)) {
					switch($search_filter){
						case 'inquiry_number' :
							$conditions = array('inquiry_number' => toId($keywords));
						break;
						case 'archived' :
							$conditions = array('keywords' => $keywords);
						break;
						default:
							$conditions = array(
                                'keywords' => $keywords,
                                'status' => $search_filter
                            );
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
                $total_inquiry_by_status = $this->inquiry->counter_by_conditions($conditions);
                $data['users_inquiries'] = $this->inquiry->get_inquiries($conditions);

                if (empty($data['users_inquiries'])) {
                    jsonResponse('0 inquiry found by this search.', 'info');
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_inquiries'] as $item) {
                    if (!in_array($item['id_item'], $items_id))
                        $items_id[] = $item['id_item'];

                    if (have_right('buy_item')) {
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    } elseif (have_right('manage_seller_inquiries')) {
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                    }
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->inquiry_statuses;

                $inquiries_list = $this->view->fetch('new/inquiry/inquiry_list_view', $data);

                jsonResponse('', 'success', array('inquiries_list' => $inquiries_list, 'total_inquiry_by_status' => $total_inquiry_by_status, 'status' => $search_filter));
            break;
        }
    }

    public function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $id_user = privileged_user_id();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_inquiry_form':
                checkPermisionAjaxModal('buy_item');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = (int) uri()->segment(4))
                    || empty($item = $productsModel->findOne($itemId, ['with' => ['productUnitType']]))
                ) {
                    messageInModal(translate('systmess_error_item_does_not_exist'));
                }

                if (!$item['inquiry']) {
                    messageInModal(translate('systmess_error_item_not_disponible_for_inquiry'));
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
                    'new/inquiry/item_inquiry_form_view',
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
            case 'resend_inquiry':
                if (!(have_right('manage_seller_inquiries') || have_right('buy_item')))
                        messageInModal(translate("systmess_error_rights_perform_this_action"));

                $id_inquiry = intVal($this->uri->segment(4));
                $params = array();

                if (have_right('manage_seller_inquiries'))
                    $params['seller'] = $id_user;
                elseif (have_right('buy_item'))
                    $params['buyer'] = $id_user;

                $data['inquiry'] = $this->inquiry->get_inquiry($id_inquiry, $params);

                if (empty($data['inquiry']))
                    messageInModal(translate('systmess_error_invalid_data'));

                if(!is_privileged('user', $data['inquiry']['id_seller'], true) && !is_my($data['inquiry']['id_buyer']))
                    messageInModal(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'completed');
                if (in_array($data['inquiry']['status'], $status_finished))
                    messageInModal(translate('systmess_error_discuss_completed_inquiry'), 'info');

                $this->view->assign($data);

                $this->view->display('new/inquiry/resend_inquiry_form_view');

            break;
            // ADD SHIP-TO ADDRESS AND CREATE THE ORDER
            case 'ship_to':
                // CHECK USER FOR BUYER RIGHTS
                if (!have_right('buy_item'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // LOAD ADDITIONAL MODELS - COUNTRY_MODEL
                $this->load->model('Country_Model', 'country');

                // GET INQUIRY ID FROM URI SEGMENT
                $data['id_inquiry'] = (int) $this->uri->segment(4);

                // GET INQUIRY DETAIL
                $params['buyer'] = $id_user;
                $inquiry_info = $this->inquiry->get_inquiry($data['id_inquiry'], $params);

                // CHECK IF EXIST INQUIRY
                if (empty($inquiry_info))
                    messageInModal('Error: This inquiry does not exist.');

                // CHECK INQUIRY STATUS - MUST BE "ACCEPTED"
                if ($inquiry_info['status'] != 'prototype_confirmed')
                    messageInModal(translate('systmess_error_inquiry_ship_to_wrong_status'), 'info');

                // GET ADDITIONAL USER DATA
                $data['user_info'] = $this->user->getSimpleUser($id_user);

                // GET COUNTRIES LIST
                $data['port_country'] = $this->country->fetch_port_country();

                if ($data['user_info']['country'])
                    $data['states'] = $this->country->get_states($data['user_info']['country']);

				$data['city_selected'] = $this->country->get_city($data['user_info']['city']);

                $this->view->display('new/inquiry/ship_view', $data);
            break;
        }
    }
}

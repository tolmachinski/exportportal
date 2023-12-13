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
class Offers_Controller extends TinyMVC_Controller
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

    private $offers_statuses = array(
        'new' => array(
            'icon' => 'new txt-green',
            'icon_new' => 'new-stroke',
            'title' => 'New offers',
            'title_color' => '',
            'description' => 'Waiting for the seller\'s response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'optional' 	=> '<p>The buyer can <strong>negotiate with the seller</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'mandatory' => '<p>If the <strong>seller agrees</strong> with the <strong>buyer’s offer</strong>, the seller has to accept it and the Offer’s status will be changed to <strong>Accepted</strong>.</p>',
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
            'description' => 'Waiting for the buyer\'s response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>If the <strong>buyer agrees</strong> with the <strong>seller’s offer</strong>, the buyer has to accept it and the Offer’s status will be changed to <strong>Accepted</strong>.</p>',
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
            'description' => 'Waiting for the seller\'s response.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'optional' 	=> '<p>The buyer can <strong>negotiate with the seller</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>',
						'mandatory' => '<p>If the <strong>seller agrees</strong> with the <strong>buyer’s offer</strong>, the seller has to accept it and the offer’s status will be changed to <strong>Accepted</strong>.</p>'
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
            'description' => 'The offer has been accepted.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p><strong>The buyer</strong> has to <strong>start a New Order</strong> using the <strong>Start Order</strong> button, and the Offer’s status will be changed to <strong>Order initiated</strong>.</p>'
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
            'description' => 'The order has been initiated based on this Offer.'
        ),
        'declined' => array(
            'icon' => 'minus-circle txt-red',
            'icon_new' => 'remove-circle',
            'title' => 'Declined',
            'title_color' => 'txt-red',
            'description' => 'The Offer has been declined.'
        ),
        'expired' => array(
            'icon' => 'hourglass-timeout txt-red',
            'icon_new' => 'hourglass',
            'title' => 'Expired',
            'title_color' => 'txt-red',
            'description' => 'The Offer has been expired.'
        ),
        'archived' => array(
            'icon' => 'archive txt-blue',
            'icon_new' => 'folder',
            'title' => 'Archived',
            'title_color' => 'txt-red',
            'description' => 'The Offer has been archived.'
        ),
    );

    function index() {
        headerRedirect();
    }

    private function _load_main() {
        $this->load->model('Offers_Model', 'offers');
        $this->load->model('Items_Model', 'items');
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
    }

    public function administration() {
        checkAdmin('manage_content');

        $this->_load_main();

        // GET OFFERS STATUSES COUNTERS
        $data['statuses'] = arrayByKey($this->offers->count_offers_by_statuses(), 'status');
        $data['last_offers_id'] = $this->offers->get_offers_last_id();

        $this->view->assign($data);
        $this->view->assign('title', 'Offers');
        $this->view->display('admin/header_view');
        $this->view->display('admin/offers/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_offers_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $params = [
            'start' => intVal($_POST['iDisplayStart']),
            'per_p' => $_POST['iDisplayLength'],
            'check_state' => false,
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_offer'     => 'io.id_offer',
                'dt_status'       => 'io.status',
                'dt_item'         => 'it.title',
                'dt_buyer'        => 'id_buyer',
                'dt_seller'       => 'id_seller',
                'dt_date_crate'   => 'update_op',
                'dt_date_expired' => 'expired_date'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'seller', 'key' => 'seller', 'type' => 'cleanInput'],
            ['as' => 'buyer', 'key' => 'buyer', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'status', 'type' => 'cleanInput'],
            ['as' => 'item', 'key' => 'item', 'type' => 'cleanInput'],
            ['as' => 'update_from',  'key' => 'update_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_to',  'key' => 'update_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d']
        ]);

        $params['sort_by'] = empty($params['sort_by']) ? ["io.id_offer-asc"] : $params['sort_by'];

        $params = array_merge($params, $filters);

        $status_array = array('new' => 'New',
            'wait_buyer' => 'Waiting for the buyer',
            'wait_seller' => 'Waiting for the seller',
            'accepted' => 'Accepted',
            'initiated' => 'Order initiated',
            'declined' => 'Declined',
            'expired' => 'Expired'
        );

        $offers = $this->offers->get_offers($params);
        $offers_count = $this->offers->counter_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $offers_count,
            "iTotalDisplayRecords" => $offers_count,
			'aaData' => array()
        );

		if(empty($offers))
			jsonResponse('', 'success', $output);

        $items_id = array();
        $users_id = array();
        $company_users_id = array();

        foreach ($offers as $item) {
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
            $users_list = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
            $users_list = arrayByKey($users_list, 'idu');
        }

        if (!empty($company_users_id)) {
            $this->load->model('Company_Model', 'company');
            $companies_info = arrayByKey($this->company->get_sellers_base_company(implode(',', $company_users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
        }

		foreach ($offers as $offer) {
			if (!empty($offer['comments'])) {
                $logs = array_reverse(with(json_decode("[{$offer['comments']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));
				$logs_html = '<table class="table table-bordered table-hover mb-5">
								<thead>
									<tr role="row">
										<th class="w-90 tac">Date</th>
										<th class="w-70 tac">User</th>
										<th class="w-250 tac">Offer</th>
										<th>Note(s)</th>
									</tr>
								</thead>
								<tbody>';

				foreach ($logs as $log_item) {
					$logs_html .= '<tr class="odd">
										<td class="tac">'.formatDate($log_item['date'], 'm/d/Y H:i:s').'</td>
										<td>
											<strong>'.$log_item['user'].'</strong>
										</td>
										<td>
											$ '.$log_item['price'].' for '.$log_item['quantity'] .' item(s)
										</td>
										<td>'
											.$log_item['message'].
										'</td>
									</tr>';
				}

				$logs_html .= '</tbody>
					</table>';
			} else{
				$logs_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> This offer does not have any log(s).</div>';
			}

			$seller_name = $users_list[$offer['id_seller']]['username'];
			$seller_group = $users_list[$offer['id_seller']]['gr_name'];
			$buyer_name = $users_list[$offer['id_buyer']]['username'];
			$buyer_group = $users_list[$offer['id_buyer']]['gr_name'];
			$company_name = $companies_info[$offer['id_seller']]['name_company'];
			$company_link = getCompanyURL($companies_info[$offer['id_seller']]);

			$company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $company_name . "' target='_blank' href='" . $company_link . "'></a>";
            $item_img_link = getDisplayImageLink(array('{ID}' => $offer['id_item'], '{FILE_NAME}' => $products_list[$offer['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));

            //TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $offer['id_seller'], 'recipientStatus' => $users_list[$offer['id_seller']]['status'], 'module' => 16, 'item' => $offer['id_offer']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            //TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $offer['id_buyer'], 'recipientStatus' => $users_list[$offer['id_buyer']]['status'], 'module' => 16, 'item' => $offer['id_offer']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();

			$output['aaData'][] = array(
				'dt_id_offer'       =>  $offer['id_offer']
										."<br /><a class='ep-icon ep-icon_plus' rel='log_details' title='View log'></a>",
				'dt_status'         =>  '<div class="tal">'
										.'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $status_array[$offer['status']] . '" data-value="' . $offer['status'] . '" data-name="status"></a>'
										.'</div>'
										.$status_array[$offer['status']],
                'dt_item'           =>  '<div class="pull-left w-30pr">
                                            <img
                                                class="w-100pr"
                                                src="' . $item_img_link . '"
                                                alt="' . $products_list[$offer['id_item']]['title'] . '"
                                            />
                                        </div>'
										.'<div class="pull-right w-68pr">'
										.'<div class="clearfix">'
										.'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Item" title="Filter by item" data-value-text="' . $products_list[$offer['id_item']]['title'] . '" data-value="' . $offer['id_item'] . '" data-name="item"></a>'
										.'<a class="ep-icon ep-icon_item txt-orange pull-left" title="View Product" href="' . __SITE_URL . 'item/' . strForURL($products_list[$offer['id_item']]['title']) . '-' . $offer['id_item'] . '"></a>'
										.'<div class="pull-right">
											<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $products_list[$offer['id_item']]['rating'] . '" data-readonly>
										</div>'
										.'</div>'
										.'<div>' . $offer["title"] . '</div>'
										.'</div>',
				'dt_buyer'          =>  '<div >'
										.'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $buyer_name . '" data-value-text="' . $buyer_name . '" data-value="' . $offer['id_buyer'] . '" data-name="buyer"></a>'
										.'<a class="ep-icon ep-icon_user" title="View personal page of ' . $buyer_name . '" href="' . __SITE_URL . 'usr/' . strForURL($buyer_name) . '-' . $offer['id_buyer'] . '"></a>'
										. $btnChatBuyerView
										.'</div>'
										.'<a href="usr/' . strForURL($buyer_name) . '-' . $offer['id_buyer'] . '">' . $buyer_name . '</a> <br /><span>' . $buyer_group . '</span>',
				'dt_seller'         =>  '<div >'
										.'<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $seller_name . '" data-value-text="' . $seller_name . '" data-value="' . $offer['id_seller'] . '" data-name="seller"></a>'
										.'<a class="ep-icon ep-icon_user" title="View personal page of ' . $seller_name . '" href="' . __SITE_URL . 'usr/' . strForURL($seller_name) . '-' . $offer['id_seller'] . '"></a>'
										.$company_icon
										.$btnChatSellerView
										.'</div>'
										.'<a href="usr/' . strForURL($seller_name) . '-' . $offer['id_seller'] . '">' . $seller_name . '</a> (' . $company_name . ') <br /><span>' . $seller_group . '</span>',
				'dt_quantity'       =>  $offer['quantity'],
				'dt_price'          =>  '$' . $offer['new_price'],
				'dt_date_crate'     =>  formatDate($offer['update_op']),
				'dt_date_expired'   => $offer['days'] . ' days <br/>' . formatDate($offer['update_op'] . ' + ' . $offer['days'] . ' day'),
				'dt_log'            => $logs_html,
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function my() {
        // CHECK USER RIGHTS FOR THIS PAGE
        if (!logged_in()) {
            $this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!i_have_company() && !have_right('buy_item')) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

        if (i_have_company() && !have_right('manage_seller_offers')) {
            $this->session->setMessages(translate("systmess_error_page_permision"), 'errors');
            headerRedirect(__SITE_URL);
        }

		checkGroupExpire();

        $this->_load_main();
		$uri = $this->uri->uri_to_assoc();

        // GET SELECTED STATUS FROM URI - IF EXIST
		if(isset($uri['status'])){
            $data['status_select'] = $uri['status'];
		}

        // ARRAY WITH FULL STATUSES DETAILS

        $data['status_array'] = $data['offers_statuses'] = $this->offers_statuses;
		$data['status_array']['offer_number'] = array(
            'icon' => 'magnifier',
            'title' => 'Search result'
        );
		$data['status_array']['expire_soon'] = array(
            'icon' => 'hourglass-timeout txt-orange',
            'title' => 'Expire soon'
        );

        // // IF THE STATUS WAS NOT SETTED IN THE URI - DEFAULT STATUS IS "NEW"
        if (!isset($data['status_array'][$data['status_select']])){
            $data['status_select'] = 'all';
        }

        $id_user = privileged_user_id();
        // PREPARING CONDITIONS FOR DATABASE QUERIES
        if (have_right('buy_item')) {
            $conditions = array('buyer' => $id_user);
            $count_conditions = array('id_buyer' => $id_user);

            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_buyer'] = 1;
            }

            $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
        } else {
            $conditions = array('seller' => $id_user);
            $count_conditions = array('id_seller' => $id_user);

            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_seller'] = 1;
            }

            $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
        }

        // GET SELECTED OFFER NUMBER FROM URI - IF EXIST
		if(isset($uri['offer'])){
        	$data['id_offer'] = $conditions['offer_number'] = toId($uri['offer']);
			$data['keywords'] = orderNumber($data['id_offer']);
			$data['status_select'] = 'offer_number';
			$conditions['status'] = 'all';
		}

		if(isset($uri['expire'])){
			$data['status_select'] = 'expire_soon';
			$data['expire_days'] = $conditions['expire_soon'] = intval($uri['expire']);
			$data['keywords'] = $data['expire_days'];
			$conditions['status'] = 'all';
		}

        global $tmvc;
        $data['offers_per_page'] = $conditions['limit'] = $tmvc->my_config['user_offers_per_page'];

        // GET OFFERS DETAIL
        $data['users_offers'] = $this->offers->get_offers($conditions);
        // GET OFFERS STATUSES COUNTERS
        $data['statuses'] = arrayByKey($this->offers->count_offers_by_statuses($count_conditions), 'status');

        // COUNT ARCHIVED OFFERS
        $archived_counters = $this->offers->count_offers_by_statuses($archived_conditions);

        // SET DEFAULT ARCHIVED COUNTER
        $data['statuses']['archived'] = array('status' => 'archived', 'counter' => 0);
        // SET ARCHIVED COUNTER NEW DATA - IF EXIST
        if (!empty($archived_counters)) {
            foreach ($archived_counters as $status_couter)
                $data['statuses']['archived']['counter'] += $status_couter['counter'];
        }

        if ($data['status_select'] != 'archived')
            $data['status_select_count'] = $this->offers->counter_by_conditions($conditions);
        else
            $data['status_select_count'] = $data['statuses']['archived']['counter'];

        foreach ($data['status_array'] as $key => $statuses_item){
            $data['status_array'][$key]['counter'] = (int)$data['statuses'][$key]['counter'];
        }

        $items_id = array();
        $users_id = array();

        if (!empty($data['users_offers'])) {
            foreach ($data['users_offers'] as $item) {
                $items_id[$item['id_item']] = $item['id_item'];

                if (have_right('buy_item')) {
                    $users_id[$item['id_seller']] = $item['id_seller'];
                } elseif (have_right('manage_seller_offers')) {
                    $users_id[$item['id_buyer']] = $item['id_buyer'];
                }
            }
        }

        // GET ITEMS INFO FOR ALL OFFERS
        if (!empty($items_id)) {
            $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $data['products_list'] = arrayByKey($data['products_list'], 'id');
        }

        // GET USERS INFO FOR ALL OFFERS
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
        $this->view->display('new/offers/index_view');
        $this->view->display('new/footer_view');
    }

    function ajax_offer_info() {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();
        $id_user = privileged_user_id();

        switch ($_POST['type']) {
            //OFFER DETAILS
            case 'offer':
                checkPermisionAjax('manage_seller_offers,make_offers');

                $id_offer = (int) $_POST['offer'];
                $data['offer'] = $this->offers->get_offer($id_offer);

                if (empty($data['offer']) || !in_array(privileged_user_id(), array((int) $data['offer']['id_seller'], (int) $data['offer']['id_buyer']))){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $data['offer']['comments'] = array_reverse(with(json_decode("[{$data['offer']['comments']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));

                // CALCULATE EXPIRE TIME IN MILISECONDS FOR COUNTDOWN TIMER
                $expire = ($data['offer']['date_offer'] + $data['offer']['days'] * 86400 - time()) * 1000;

                //region user information for tablet/mobile
                if(have_right('make_offers')){
                    $data['seller_info'] = model('company')->get_seller_base_company(
                        (int) $data['offer']['id_seller'],
                        "cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company, u.user_group",
                        true
                    );
                } else{
                    $data['buyer_info'] = $this->user->getSimpleUser((int) $data['offer']['id_buyer'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name, users.user_group, users.user_photo");
                }
                //endregion user information for tablet/mobile

                $data['offer_status'] = $this->offers_statuses[$data['offer']['status']];
                $data['offer_status_user'] = have_right('make_offers') ? 'buyer' : 'seller';

                if(have_right('buy_item')){
					$btnChatSeller = new ChatButton(['recipient' => $data['offer']['id_seller'], 'recipientStatus' => 'active', 'module' => 16, 'item' => $data['offer']['id_offer']], ['text' => 'Chat with seller']);
					$data['btnChatSeller'] = $btnChatSeller->button();

					$btnChatSeller2 = new ChatButton(['recipient' => $data['offer']['id_seller'], 'recipientStatus' => 'active', 'module' => 16, 'item' => $data['offer']['id_offer']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with seller']);
					$data['btnChatSeller2'] = $btnChatSeller2->button();
				}else{
					$btnChatBuyer = new ChatButton(['recipient' => $data['offer']['id_buyer'], 'recipientStatus' => 'active', 'module' => 16, 'item' => $data['offer']['id_offer']], ['text' => 'Chat with buyer']);
					$data['btnChatBuyer'] = $btnChatBuyer->button();

					$btnChatBuyer2 = new ChatButton(['recipient' => $data['offer']['id_buyer'], 'recipientStatus' => 'active', 'module' => 16, 'item' => $data['offer']['id_offer']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with buyer']);
					$data['btnChatBuyer2'] = $btnChatBuyer2->button();
				}

                $content = $this->view->fetch('new/offers/offers_detail_view', $data);

                jsonResponse('', 'success', array('expire' => $expire, 'content' => $content));
            break;
            // OFFERS LIST
            case 'offer_list':
                $statuses = array('all', 'new', 'wait_buyer', 'wait_seller', 'accepted', 'initiated', 'declined', 'expired', 'archived');
                if (!in_array($_POST['status'], $statuses)) {
                    jsonResponse('Error: The status you selected is not correct.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_offers_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                if (have_right('buy_item')) {
                    $conditions = array('buyer' => $id_user);
                    if ($_POST['status'] != 'archived')
                        $conditions['status'] = cleanInput($_POST['status']);
                    else
                        $conditions['state_buyer'] = 1;
                } else {
                    $conditions = array('seller' => $id_user);
                    if ($_POST['status'] != 'archived')
                        $conditions['status'] = cleanInput($_POST['status']);
                    else
                        $conditions['state_seller'] = 1;
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $data['users_offers'] = $this->offers->get_offers($conditions);
                $total_offers_by_status = $this->offers->counter_by_conditions($conditions);

                if (empty($data['users_offers'])) {
                    jsonResponse('0 offers found by this search.', 'info', array('total_offers_by_status' => 0));
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_offers'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];

                    if (have_right('buy_item'))
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    else
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
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

                $data['status_array'] = $this->offers_statuses;

                $offers_list = $this->view->fetch('new/offers/offer_list_view', $data);

                jsonResponse('', 'success', array('offers_list' => $offers_list, 'total_offers_by_status' => $total_offers_by_status));
            break;
            // SEARCH OFFERS
            case 'search_offer':
                if(!have_right('manage_seller_offers') && ! have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $keywords = cleanInput(cut_str($_POST['keywords']));
                if ($keywords == '')
                    jsonResponse('Error: Search keywords is required.');

                global $tmvc;
                $per_page = $tmvc->my_config['user_offers_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                $search_filter = cleanInput($_POST['search_filter']);

				if (!empty($search_filter)) {
					switch($search_filter){
						case 'offer_number' :
							$conditions = array('offer_number' => toId($keywords));
						break;
						case 'expire_soon' :
							$conditions = array('expire_soon' => intval($keywords));
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
                $total_offers_by_status = $this->offers->counter_by_conditions($conditions);

                $data['users_offers'] = $this->offers->get_offers($conditions);
                if (empty($data['users_offers'])) {
                    jsonResponse('0 offers found by this search.', 'info');
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_offers'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];

                    if (have_right('buy_item'))
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    else
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
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

                $data['status_array'] = $this->offers_statuses;

                $offers_list = $this->view->fetch('new/offers/offer_list_view', $data);

                jsonResponse('', 'success', array('offers_list' => $offers_list, 'total_offers_by_status' => $total_offers_by_status));
            break;
        }
    }

    public function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();

        $id_user = $this->session->id;

        switch (uri()->segment(3)) {
            case 'add_offer_form':
                checkPermisionAjaxModal('buy_item');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = (int) uri()->segment(4))
                    || empty($item = $productsModel->findOne($itemId, ['with' => ['productUnitType']]))
                ) {
                    messageInModal(translate('systmess_error_item_does_not_exist'));
                }

                if (!$item['offers']) {
                    messageInModal(translate('systmess_error_item_not_disponible_for_offer'));
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
                    'new/offers/item_offer_form_view',
                    [
                        'availableQuantity'         => $quantityInStock,
                        'variantOptions'            => $usedVariantOptions ?? null,
                        'sold_counter'              => $this->items->soldCounter($itemId),
                        'itemVariant'               => $itemVariant ?? null,
                        'photo'                     => $this->items->get_items_photo($itemId, 1),
                        'item'                      => $item,
                    ]
                );

            break;
            case 'ship_to':
                // CHECK USER FOR BUYER RIGHTS
                if (!have_right('buy_item'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // LOAD ADDITIONAL MODELS - COUNTRY_MODEL
                $this->load->model('Country_Model', 'country');

                // GET OFFER ID FROM URI SEGMENT
                $data['id_offer'] = (int) $this->uri->segment(4);

                // GET OFFER DETAIL
                $offer_info = $this->offers->get_offer($data['id_offer']);

                // CHECK IF EXIST OFFER WITH THIS ID FOR THIS BUYER
                if (empty($offer_info) || !is_my($offer_info['id_buyer']))
                    messageInModal('Error: This offer does not exist.');

                // CHECK OFFER STATUS - MUST BE "ACCEPTED"
                if ($offer_info['status'] != 'accepted')
                    messageInModal('Info: The offer must be accepted before adding "Shipping to address".', 'info');

                // GET ADDITIONAL USER DATA
                $data['user_info'] = $this->user->getSimpleUser($id_user);

                // GET COUNTRIES LIST
                $data['port_country'] = $this->country->fetch_port_country();

                if ($data['user_info']['country'])
                    $data['states'] = $this->country->get_states($data['user_info']['country']);

				$data['city_selected'] = $this->country->get_city($data['user_info']['city']);

                // IF ALL IS OK - SHOW THE FORM
                $this->view->assign($data);

                $this->view->display('new/offers/ship_view');
            break;
            case 'resend_offer':
                // CHECK USER RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_offers'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // GET OFFER DETAIL
                $id_offer = intVal($this->uri->segment(4));
                $data['offer'] = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($data['offer']))
                    messageInModal(translate('systmess_error_invalid_data'));

                if(!is_privileged('user', $data['offer']['id_seller'], true) && !is_my($data['offer']['id_buyer']))
                    messageInModal(translate('systmess_error_invalid_data'));

                // CHECK OFFER STATUS
                if (in_array($data['offer']['status'], array('accepted', 'expired', 'declined')))
                    messageInModal(translate('systmess_error_discuss_completed_offer'));

                // GET ITEM DETAIL
                $data['item_info'] = $this->items->get_item($data['offer']['id_item']);

                // CHECK IF ITEM EXIST
                if (empty($data['item_info']))
                    messageInModal(translate('systmess_error_item_does_not_exist'));

                if (!$data['item_info']['offers'])
                    messageInModal(translate('systmess_error_item_not_disponible_for_offer'), 'info');

                // SHOW THE FORM
                $this->view->assign($data);

                $this->view->display('new/offers/resend_offer_form_view');

            break;
        }
    }

    public function ajax_offers_operation() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Notify_Model', 'notify');
        $id_user = privileged_user_id();
        $op = $this->uri->segment(3);
        switch ($op) {
            case 'check_new':
                // CHECK ROGHTS - ONLY ADMIN
                if (!have_right('manage_content'))
                    jsonResponse('Error: You do not have permission to perfrm this action.');

                // GET LAST OFFER ID FROM ADMIN PAGE
                $lastId = $_POST['lastId'];
                $offers_count = $this->offers->get_count_new_offers($lastId);

                // RETURN RESULTS
                if ($offers_count) {
                    $last_offers_id = $this->offers->get_offers_last_id();
                    jsonResponse('', 'success', array('nr_new' => $offers_count, 'lastId' => $last_offers_id));
                } else {
                    jsonResponse('Error: New offers does not exists.');
                }
            break;
            // REMOVE THE OFFER
            case 'remove_offer':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_offers'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET OFFER
                $id_offer = intVal($_POST['offer']);
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info))
                    jsonResponse('Error: This offer does not exist.');

                // CHECK IS UDER IS PRIVILEGED TO CHANGE THIS OFFER
                if (!is_privileged('user', $offer_info['id_seller'], true) && !is_my($offer_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                //PERMIT TO REMOVE OFFER ONLY IF STATUS IS DECLINED, EXPIRED OR OFFER STATE IS ARCHIVED
                if (have_right('buy_item')) {
                    $user_state = 'state_buyer';
                } else {
                    $user_state = 'state_seller';
                }
                if (!in_array($offer_info['status'], array('initiated', 'declined', 'expired')) && $offer_info[$user_state] != 1)
                    jsonResponse('Info: You cannot remove this offer now. Please try again late.', 'info');

                // UPDATE OFFER BY USER TYPE
                $update_offer = array($user_state => 2);

                if ($this->offers->update_offer($id_offer, $update_offer)) {
                    $status = $offer_info['status'];
                    if($offer_info['status'] == 'initiated')
                        $status = 'finished';

                    if (have_right('buy_item')) {
                        $statistic = array(
                            $offer_info['id_buyer'] => array('offers_sent' => -1, 'offers_'.$status => -1)
                        );
                    } elseif(have_right('manage_seller_offers')){
                        $statistic = array(
                            $offer_info['id_seller'] => array('offers_received' => -1, 'offers_'.$status => -1)
                        );
                    }

                    if(!empty($statistic)){
                        $this->load->model('User_Statistic_Model', 'statistic');
                        $this->statistic->set_users_statistic($statistic);
                    }
                    jsonResponse('The offer has been successfully deleted.', 'success');
                } else {
                    jsonResponse('Error: The offer has not been deleted. Please try again later.');
                }
            break;
            // Archive the offer
            case 'archived_offer':
                if(!have_right('manage_seller_offers') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET OFFER DETAILS
                $id_offer = intVal($_POST['offer']);
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info))
                    jsonResponse('Error: The offer does not exist.');

                // CHECK OFFER STATUS - MUST BE "ORDER INITIATED"
                if (!in_array($offer_info['status'], array('initiated', 'declined', 'expired')))
                    jsonResponse('Error: This offer has not been completed.');

                // CHECK RIGHTS
                if (!is_privileged('user', $offer_info['id_seller'], true) && !is_my($offer_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if (have_right('buy_item'))
                    $user_state = 'state_buyer';
                else
                    $user_state = 'state_seller';

                // CHECK IF OFFER WAS NOT ARCHIVED BEFORE
                if ($offer_info[$user_state] != 0)
                    jsonResponse('Info: The offer is already archived.', 'info');

                // UPDATE OFFER - ARCHIVED
                $update_offer = array($user_state => 1);
                if ($this->offers->update_offer($id_offer, $update_offer)) {
                    jsonResponse('The offer has been successfully archived.', 'success');
                } else {
                    jsonResponse('Error: You cannot archive this offer now. Please try again later.');
                }
            break;
            // CREATE THE ORDER
            case 'create_order':
                // CHECK USER RIGHTS - MUST BE BUYER
                checkPermisionAjax('buy_item');

                // LOADING MODELS
                $this->load->model('Item_Snapshot_Model', 'snapshot');
                $this->load->model('Orders_model', 'orders');
                $this->load->model('Country_Model', 'country');

                // VALIDATE POST DATA
                $validator_rules = array(
                    array(
                        'field' => 'offer',
                        'label' => 'Offer detail',
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

                // GET OFFER DETAILS
                $id_offer = (int) $_POST['offer'];
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #4');
                }

                // CHECK IF IS BUYER OFFER
                if (!is_my($offer_info['id_buyer'])){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #5');
                }

                // CHECK IF OFFER STATUS - MUST BE ACCEPTED
                if ($offer_info['status'] != 'accepted'){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #6');
                }

                // GET ITEM DETAIL
                $item = $this->items->get_item($offer_info['id_item']);

                // CHECK IF ITEM EXIST
                if (empty($item)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #7');
                }

                // CHECK IF REQUESTED QUANTITY IS NOT GREATHER THAN DISPONIBLE
                if ($item['quantity'] < $offer_info['quantity']){
                    jsonResponse(translate('systmess_error_offer_create_order_necessary_quantity_not_available'));
                }

                $offer_number = orderNumber($id_offer);
                // PREPARING ORDER FIRST LOG HISTORY
                $order_log = array(
                    'date' => date('m/d/Y h:i:s A'),
                    'user' => 'Buyer',
                    'message' => "The order has been initiated in base of the offer: {$offer_number}."
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

                $total_order_weight = $offer_info['quantity'] * $item['weight'];
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

                // PREPARING DATA FOR ORDER
                $order = array(
                    'id_buyer' => $offer_info['id_buyer'],
                    'id_seller' => $offer_info['id_seller'],
                    'price' => $offer_info['new_price'],
                    'final_price' => $offer_info['new_price'],
                    'weight' => $total_order_weight,
                    'comment' => 'The item(s) has been sold by the offer ' . $offer_number,
                    'order_summary' => json_encode($order_log),
                    'order_type' => 'offer',
                    'id_by_type' => $id_offer,
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
                        'message' => "The order has been initiated in base of the offer: {$offer_number}."
                    )))
                );

                // INSERT ORDER
                $id_order = $this->orders->insert_order($order);
                if (!$id_order){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $order_number = orderNumber($id_order);

                // PREPARE SEARCH INFO
                $users = $this->user->getSimpleUsers(implode(',', array($offer_info['id_buyer'], $offer_info['id_seller'])), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                $this->load->model('Company_Model', 'company');
                $company = $this->company->get_seller_base_company($offer_info['id_seller'], 'cb.name_company');
                $search_info = $order_number . ', ' . $users[0]['username'] . ', ' . $users[1]['username'] .', '. $company['name_company'] .', '.$item['title'];

                // UPDATE SEARCH INFO - ADD ORDER ID
                $this->orders->change_order($id_order, array('search_info' => $search_info));

                // GET LAST ITEM SNAPSHOT DETAILS
                $snapshot = $this->snapshot->get_last_item_snapshot($offer_info['id_item']);

                // SET ORDERED ITEM
                $ordered_item = array(
                    'id_order' => $id_order,
                    'id_item' => $offer_info['id_item'],
                    'id_snapshot' => $snapshot['id_snapshot'],
                    'price_ordered' => $offer_info['new_price']/$offer_info['quantity'],
                    'quantity_ordered' => $offer_info['quantity'],
                    'weight_ordered' => $item['weight'],
                    'detail_ordered' => 'The item(s) has been sold by offer ' . orderNumber($id_offer) . '.'
                );

                $this->orders->set_ordered_item($ordered_item);

                // CHANGE ITEM QUANTITY
                $this->items->update_item(array('id' => $offer_info['id_item'],'quantity' => ($item['quantity'] - $offer_info['quantity'])));

                // NOTIFY USERS ABOUT NEW ORDER
                $date_order = date('Y-m-d H:i:s');

                $this->notifier->send(
                    (new SystemNotification('order_created', [
						'[ORDER_ID]'   => $order_number,
						'[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
						'[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $offer_info['id_buyer'],
                        $offer_info['id_seller'],
                    ])
                );

                // IF ALL IS OK - CHANGE OFFER STATUS TO "ORDER INITIATED"
                $comment = array(
                    "user" => 'Buyer',
                    "date" => date('Y-m-d H:i:s'),
                    "quantity" => $offer_info['quantity'],
                    "price" => $offer_info['new_price'],
                    "message" => 'The order '.$order_number.' has been initiated.',
                );
                $update_offer = array(
                    'status' => 'initiated',
                    'comments' => $offer_info['comments'].','.json_encode($comment)
                );
                $this->offers->update_offer($id_offer, $update_offer);

                // CHANGE USER STATISTIC
                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(array(
                    $offer_info['id_seller'] => array('offers_finished' => 1, 'offers_accepted' => -1),
                    $offer_info['id_buyer'] => array('offers_finished' => 1, 'offers_accepted' => -1)
                ));

                jsonResponse(translate('systmess_success_offer_create_order', ['{ORDER_NUMBER}' => orderNumber($id_order)]), 'success', array('order' => $id_order));

            break;
            // DISCUSS ABOUT OFFER
            case 'resend_offer':
                // CHECK USER RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_offers'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // CHECK VALIDATION RULES
                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'price',
                        'label' => 'Price',
                        'rules' => array('required' => '', 'positive_number' => '', 'min[0]' => '', 'max_len[12]' => '')
                    ),
                    array(
                        'field' => 'quantity',
                        'label' => 'Quantity',
                        'rules' => array('required' => '', 'natural' => '', 'min[1]' => '')
                    ),
                    array(
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'offer',
                        'label' => 'Offer detail',
                        'rules' => array('required' => '', 'natural' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if (!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                // GET OFFER DETAIL
                $id_offer = intVal($_POST['offer']);
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info))
                    jsonResponse('Error: This offer does not exist.');

                // CHECK IF IS USER OFFER
                if(!is_privileged('user', $offer_info['id_seller'], true) && !is_my($offer_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // CHECK OFFER STATUS
                if (in_array($offer_info['status'], array('accepted', 'expired', 'declined')))
                    jsonResponse(translate('systmess_error_discuss_completed_offer'));

                // GET ITEM DETAIL
                $item_info = $this->items->get_item($offer_info['id_item']);

                // CHECK IF ITEM EXIST
                if (empty($item_info))
                    jsonResponse(translate('systmess_error_item_does_not_exist'));

                if (!$item_info['offers'])
                    jsonResponse(translate('systmess_error_item_not_disponible_for_offer'), 'info');

                // CHECK IF REQUESTED QUANTITY IS NOT GREATHER THAN DISPONIBLE
                $quantity = intVal($_POST['quantity']);
                if ($item_info['quantity'] < $quantity)
                    jsonResponse(translate('systmess_error_offer_necessary_quantity_not_available'));

                // PREPARE DATA FOR UPDATES
                if (have_right('manage_seller_offers')) {
                    $status = 'wait_buyer';
                    $user_send = array($offer_info['id_buyer']);
                    $user_type = 'Seller';
                    $id_user_send = $offer_info['id_buyer'];
                }
                else {
                    $status = 'wait_seller';
                    $user_send = array($offer_info['id_seller']);
                    $user_type = 'Buyer';
                    $id_user_send = $offer_info['id_seller'];
                }

                $user_message = cleanInput($_POST['message']);
                // UPDATE OFFER DATA
                $comment = array(
                    "user" => $user_type,
                    "date" => date('Y-m-d H:i:s'),
                    "quantity" => $quantity,
                    "price" => floatVal($_POST['price']) * $quantity,
                    "message" => $user_message
                );
                $update_offer = array(
                    'status' => $status,
                    'unit_price' => floatVal($_POST['price']),
                    'new_price' => floatVal($_POST['price']) * $quantity,
                    'quantity' => $quantity,
                    'comments' => $offer_info['comments'] . ',' . json_encode($comment)
                );
                if ($this->offers->update_offer($id_offer, $update_offer)) {

					// NOTIFY USER(SELLER OR BUYER) ABOUT CHANGES
					$data_systmess = [
						'mess_code' => 'offer_message_sent',
						'id_item'   => $id_offer,
						'id_users'  => $user_send,
						'replace'   => [
							'[OFFER_LINK]' => __SITE_URL . 'offers/my/offer/' . $id_offer,
							'[OFFER_ID]'   => orderNumber($id_offer),
							'[LINK]'       => __SITE_URL . 'offers/my'
						],
						'systmess' => true,
					];


                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_discuss_offer'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // ADD NEW OFFER
            case 'add':
                if (!have_right('buy_item')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                // VALIDATION RULES FOR POST DATA
                $validator_rules = array(
                    array(
						'field' => 'price',
						'label' => 'Price',
						'rules' => array('required' => '', 'positive_number' => '', 'min[0.01]' => '', 'max[9999999999.99]' => '')
                    ),
                    array(
						'field' => 'quantity',
						'label' => 'Quantity',
						'rules' => array('required' => '', 'natural' => '', 'min[1]' => '')
                    ),
                    array(
						'field' => 'days',
						'label' => 'Days',
						'rules' => array('required' => '', 'natural' => '','min[1]' => '','max[14]' => '')
                    ),
                    array(
						'field' => 'comments',
						'label' => 'Comments',
						'rules' => array('required' => '', 'max_len[1000]' => '')
                    ),
                    array(
						'field' => 'item',
						'label' => 'Item',
						'rules' => array('required' => '', 'natural' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $request = request()->request;

                // GET ITEM DETAIL
                $itemId = $request->getInt('item');

                if (empty($item = $productsModel->findOne($itemId, ['with' => ['category']]))) {
                    jsonResponse(translate('systmess_error_item_does_not_exist'));
                }

                if (!$item['offers']) {
                    jsonResponse(translate('systmess_error_item_not_disponible_for_offer'), 'info');
                }

                if ($item['is_out_of_stock']) {
                    jsonResponse(translate('translations_out_of_stock_system_message'), 'info');
                }

                $quantityInStock = $item['quantity'];
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

                    $quantityInStock = $itemVariant['quantity'];
                }

                $offerQuantity = $request->getInt('quantity');

                if ($offerQuantity > $quantityInStock) {
                    jsonResponse(translate('systmess_error_offer_necessary_quantity_not_available'));
                }

                if ($offerQuantity < $item['min_sale_q']) {
                    jsonResponse(translate('systmess_error_add_offer_quantity_less_than_min_sale_quantity', ['{{MIN_SALE_QUANTITY}}' => $item['min_sale_q']]));
                }

                // PREPARING SEARCH INFO AND ADD OFFER
                $search_info = [];

                $users_id = array($item['id_seller'], $id_user);
                $users_info = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                foreach ($users_info as $user_info)
                    $search_info[] = $user_info['username'];

                $this->load->model('Company_Model', 'company');
                $company_info = $this->company->get_seller_base_company($item['id_seller'], "cb.id_company, cb.name_company, cb.id_user");

                $search_info[] = $item['title'] . ', ' . $item['category']['name'];
                $search_info[] = $company_info['name_company'];

                $comments = cleanInput($request->get('comments'));
                $unitPrice = (float) $request->get('price');
                $insert = array(
                    'id_buyer' => $id_user,
                    'id_seller' => $item['id_seller'],
                    'id_item' => $itemId,
                    'days' => $request->getInt('days'),
                    'date_offer' => time(),
                    'unit_price' => $unitPrice,
                    'new_price' => $unitPrice * $offerQuantity,
                    'quantity' => $offerQuantity,
                    'detail_item' => implode(', ', $details),
                    'comments' => '{"date":"' . date('Y-m-d H:i:s') . '","quantity":"' . $offerQuantity . '","price":"' . $unitPrice * $offerQuantity . '","user":"Buyer","message":"' . $comments . '"}',
                );

                // CHECK IF OFFER WAS CREATED
                if(!$id_offer = $this->offers->set_offer($insert)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $search_info[] = orderNumber($id_offer);
                $this->offers->update_offer($id_offer, array('for_search' => implode(', ', $search_info)));

                // SET USER STATISTIC FOR SELLER AND BUYER
                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(
                    array(
                        $insert['id_buyer'] => array('offers_sent' => 1),
                        $insert['id_seller'] => array('offers_received' => 1)
                    )
                );


                // NOTIFY SELLER ABOUT NEW OFFER
				$data_systmess = [
					'mess_code'     => 'offer_new',
					'id_item'       => $id_offer,
					'id_users'      => [$item['id_seller']],
					'replace'       => [
						'[OFFER_LINK]' => __SITE_URL . 'offers/my/offer/' . $id_offer,
						'[OFFER_ID]'   => orderNumber($id_offer),
						'[LINK]'       => __SITE_URL . 'offers/my',
						'[ITEM]'       => cleanInput(request()->request->get('title')),
						'[USER]'       => user_name_session(),
					],
					'systmess' => true
				];


                $this->notify->send_notify($data_systmess);
                jsonResponse(translate('systmess_success_sent_offer'), 'success');
            break;
            // ACCEPT THE OFFER
            case 'accept_offer':
                // CHECK USER RIGHTS - ONLY SELLER, BUYER, STAFF USERS
                if (!have_right('manage_seller_offers') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET OFFER DETAIL
                $id_offer = intVal($_POST['offer']);
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                // CHECK IF IS USER OFFER
                if (!is_privileged('user', $offer_info['id_seller'], true) && !is_my($offer_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // CHECK OFFER STATUS
                $status_finished = array('accepted', 'expired', 'declined', 'initiated');
                if (in_array($offer_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_accept_completed_offer'));

                // GET ITEM DETAIL
                $item_info = $this->items->get_item($offer_info['id_item']);

                // CHECK IF ITEM EXIST
                if (empty($item_info))
                    jsonResponse(translate('systmess_error_item_does_not_exist'));

                if (!$item_info['offers'])
                    jsonResponse(translate('systmess_error_item_not_disponible_for_offer'), 'info');

                // CHECK IF REQUESTED QUANTITY IS NOT GREATHER THAN DISPONIBLE
                $quantity = intVal($_POST['quantity']);
                if ($item_info['quantity'] < $offer_info['quantity'])
                    jsonResponse(translate('systmess_error_offer_necessary_quantity_not_available'));

                if (have_right('manage_seller_offers')){
                    $id_user_send = $offer_info['id_buyer'];
                    $user_send = 'Seller';
                } else{
                    $id_user_send = $offer_info['id_seller'];
                    $user_send = 'Buyer';
                }

                // CHANGE OFFER STATUS - ACCEPTED
                $comment = array(
                    "user" => $user_send,
                    "date" => date('Y-m-d H:i:s'),
                    "quantity" => $offer_info['quantity'],
                    "price" => $offer_info['new_price'],
                    "message" => 'The offer has been accepted.'
                );
                $update_offer = array(
                    'status' => 'accepted',
                    'comments' => $offer_info['comments'].','.json_encode($comment)
                );
                if ($this->offers->update_offer($id_offer, $update_offer)) {
                    // CHANGE USER STATISTIC
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        $offer_info['id_seller'] => array('offers_accepted' => 1),
                        $offer_info['id_buyer'] => array('offers_accepted' => 1)
                    ));

                    // NOTIFY USER - SELLER OR BUYER (DEPENDS ON OFFER STATUS)
                    $offer_number = orderNumber($id_offer);

                    $data_systmess = [
                        'mess_code'     => 'offer_accepted',
                        'id_item'       => $id_offer,
                        'id_users'      => [$id_user_send],
                        'type'          => 'offers',
                        'replace'       => [
                            '[OFFER_ID]'   => $offer_number,
                            '[OFFER_LINK]' => __SITE_URL . 'offers/my/offer/' . $id_offer,
                            '[ITEM]'       => cleanOutput($offer_info['title']),
                            '[LINK]'       => __SITE_URL . 'offers/my'
                        ],
                        'systmess'      => true
                    ];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_accept_offer'), 'success');
                } else
                    jsonResponse(translate('systmess_internal_server_error'));
            break;
            // DECLINE THE OFFER
            case 'declined_offer':
                // CHECK USER RIGHTS - ONLY SELLER, BUYER, STAFF USERS
                if (!have_right('manage_seller_offers') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // GET OFFER DETAIL
                $id_offer = intVal($_POST['offer']);
                $offer_info = $this->offers->get_offer($id_offer);

                // CHECK IF OFFER EXIST
                if (empty($offer_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                // CHECK IF IS UDER OFFER
                if (!is_privileged('user', $offer_info['id_seller'], true) && !is_my($offer_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                // CHECK OFFER STATUS
                $status_finished = array('accepted', 'expired', 'declined', 'initiated');
                if (in_array($offer_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_decline_completed_offer'));

                // CHANGE OFFER STATUS - DECLINED
                if (have_right('manage_seller_offers')){
                    $id_user_send = $offer_info['id_buyer'];
                    $user_send = 'Seller';
                } else{
                    $id_user_send = $offer_info['id_seller'];
                    $user_send = 'Buyer';
                }

                $comment = array(
                    "user" => $user_send,
                    "date" => date('Y-m-d H:i:s'),
                    "quantity" => $offer_info['quantity'],
                    "price" => $offer_info['new_price'],
                    "message" => 'The offer has been declined.'
                );
                $update_offer = array(
                    'status' => 'declined',
                    'comments' => $offer_info['comments'].','.json_encode($comment)
                );

                if ($this->offers->update_offer($id_offer, $update_offer)) {
                    // CHANGE USER STATISTIC
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(
                        array(
                            $offer_info['id_seller'] => array('offers_declined' => 1),
                            $offer_info['id_buyer'] => array('offers_declined' => 1)
                        )
                    );

                    // NOTIFY USER ABOUT NEW OFFER STATUS
                    $offer_number = orderNumber($id_offer);

                    $data_systmess = [
                        'mess_code' => 'offer_declined',
                        'id_item'   => $id_offer,
                        'id_users'  => [$id_user_send],
                        'replace'   => [
                            '[OFFER_LINK]' => __SITE_URL . 'offers/my/offer/' . $id_offer,
                            '[OFFER_ID]'   => $offer_number,
                            '[LINK]'       => __SITE_URL . 'offers/my',
                            '[ITEM]'       => cleanOutput($offer_info['title']),
                        ],
                        'systmess' => true
                    ];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_decline_offer'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // UPDATE STATUSES COUNTERS
            case 'update_sidebar_counters':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('manage_seller_offers') && !have_right('buy_item'))
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
                $statuses_counters = arrayByKey($this->offers->count_offers_by_statuses($count_conditions), 'status');
                $archived_counters = $this->offers->count_offers_by_statuses($archived_conditions);
                $statuses_counters['archived'] = array('status' => 'archived', 'counter' => 0);
                if (!empty($archived_counters)) {
                    foreach ($archived_counters as $status_couter)
                    $statuses_counters['archived']['counter'] += $status_couter['counter'];
                }

                // RETURN RESPONCE
                jsonResponse('', 'success', array('counters' => $statuses_counters));
            break;
        }
    }
}

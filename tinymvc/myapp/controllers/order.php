<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Notifier\SystemChannel;
use App\Email\EmailOrderCancelledByBuyer;
use App\Email\EmailOrderCancelledByManager;
use App\Email\EmailOrderCancelledBySeller;
use App\Email\PoShippingMethod;
use App\Envelope\Bridge\EPDocs\FileStorage;
use App\Envelope\Bridge\Order\Command\CreateOrderContract;
use App\Envelope\Bridge\Order\Command\CreateOrderInvoice;
use App\Envelope\Bridge\Order\ContractMakerAdapter;
use App\Envelope\Bridge\Order\InvoiceMakerAdapter;
use App\Envelope\Bridge\Order\Message\CreateOrderContractMessage;
use App\Envelope\Bridge\Order\Message\CreateOrderInvoiceMessage;
use App\Envelope\EnvelopeStatuses;
use App\Plugins\EPDocs\Credentials\JwtCredentials;
use App\Plugins\EPDocs\Http\Auth;
use App\Plugins\EPDocs\Http\Authentication\Bearer;
use App\Plugins\EPDocs\Rest\RestClient;
use App\Plugins\EPDocs\Storage\JwtTokenStorage;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 */
class Order_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = [];

    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notifier = $container->get(NotifierInterface::class);
    }

    public function index()
    {
        headerRedirect();
    }

    public function my()
    {
        checkIsLogged();
        checkPermisionAndRedirect('administrate_orders', '/order/admin_assigned');
        checkPermision('buy_item,manage_seller_orders,manage_shipper_orders', '/403');

        checkDomainForGroup();

        checkGroupExpire();

        // LOADING MAIN MODELS
        $this->_load_main();
        $this->load->model('Video_Tour_model', 'video_tour');

        // GET SELECTED STATUS FROM URI - IF EXIST
        $uri = $this->uri->uri_to_assoc();

        // GET SELECTED STATUS FROM URI - IF EXIST
        if (isset($uri['status'])) {
            $data['status_select'] = $uri['status'];
        }

        // SET PRIVILEGED USER ID FROM SESSION - IF STAFF USER: TAKE ID_SELLER
        $id_user = privileged_user_id();

        // PREPARING CONDITIONS FOR ORDERS, ORDERS COUNTERS BY STATUSES

        $user_type = 'buyer';
        if (have_right('buy_item')) {
            $conditions = ['id_user' => $id_user];
            $count_conditions = ['id_buyer' => $id_user];
            $count_archived = ['id_buyer' => $id_user, 'state_buyer' => 1];
        } elseif (have_right('manage_seller_orders')) {
            $user_type = 'seller';
            $conditions = ['id_seller' => $id_user];
            $count_conditions = ['id_seller' => $id_user];
            $count_archived = ['id_seller' => $id_user, 'state_seller' => 1];
        } elseif (have_right('manage_shipper_orders')) {
            $conditions = ['id_shipper' => $id_user];
            $count_conditions = ['id_shipper' => $id_user, 'shipper_status' => 1];
            $count_archived = ['id_shipper' => $id_user];
        }

        if (empty($data['status_select'])) {
            $data['status_select'] = 'all';
            $conditions['order_by'] = 'os.position ASC, io.update_date DESC';
        }

        // GET SELECTED OFFER NUMBER FROM URI - IF EXIST
        if (isset($uri['order_number'])) {
            $data['id_order'] = $conditions['id_order'] = toId($uri['order_number']);
            $data['status_select'] = 'order_number';
        }

        $item_detail = '';
        if (isset($uri['item'])) {
            $conditions['id_item'] = intval($uri['item']);
            $data['status_select'] = 'by_item';

            $this->load->model('Items_Model', 'items');
            if ($this->items->item_exist($conditions['id_item'])) {
                $item_detail = $this->items->get_item($conditions['id_item'], 'title');
            }
        }

        // GET COUNTERS BY STATUSES
        $statuses = $this->orders->count_orders_by_statuses($count_conditions);
        $data['archived_count'] = $this->orders->simple_count_orders($count_archived);

        if ('all' == $data['status_select']) {
            $data['status_select_count'] = $this->orders->simple_count_orders($count_conditions);
        }

        $finished_statuses_array = [
            'order_completed',
            'late_payment',
            'canceled_by_buyer',
            'canceled_by_seller',
            'canceled_by_ep', ];

        $canceled_statuses_array = [
            'late_payment',
            'canceled_by_buyer',
            'canceled_by_seller',
            'canceled_by_ep', ];

        $data['counter_finished'] = $data['counter_process'] = 0;

        foreach ($statuses as $status) {
            if ($status['alias'] == $data['status_select']) {
                $conditions['status'] = $status['id'];
                $data['status_select_name'] = $status['status'];
                $data['status_select_count'] = $status['counter'];
                $data['status_select_icon'] = $status['icon'];
            } elseif ('archived' == $data['status_select']) {
                if (have_right('buy_item')) {
                    $conditions['state_buyer'] = 1;
                } elseif (have_right('manage_seller_orders')) {
                    $conditions['state_seller'] = 1;
                }

                $data['status_select_name'] = 'Archived';
                $data['status_select_count'] = $data['archived_count'];
                $data['status_select_icon'] = 'ep-icon_archive';
            } elseif ('order_number' == $data['status_select']) {
                $data['status_select_name'] = 'Search result';
                $data['status_select_icon'] = 'ep-icon_magnifier';
            } elseif ('by_item' == $data['status_select'] && !empty($item_detail)) {
                $data['status_select_name'] = 'Search by: ' . $item_detail['title'];
                $data['status_select_icon'] = 'ep-icon_item txt-orange';
            }

            if (in_array($status['alias'], $finished_statuses_array)) {
                $data['counter_finished'] += $status['counter'];

                if (in_array($status['alias'], $canceled_statuses_array)) {
                    if (empty($data['statuses_finished']['canceled'])) {
                        $data['statuses_finished']['canceled'] = [
                            'alias'    => 'canceled',
                            'icon_new' => 'ep-icon_remove-circle',
                            'status'   => 'Canceled',
                            'counter'  => $status['counter'],
                        ];
                    } else {
                        $data['statuses_finished']['canceled']['counter'] += $status['counter'];
                    }
                } else {
                    $data['statuses_finished'][$status['alias']] = $status;
                }
            } else {
                $data['statuses_process'][$status['alias']] = $status;
                $data['counter_process'] += $status['counter'];
            }
        }
        // END GET COUNTERS BY STATUSES

        global $tmvc;
        $data['orders_per_page'] = $conditions['limit'] = $tmvc->my_config['user_orders_per_page'];
        // $data['orders_per_page'] = $conditions['limit'] = 1;

        // GET ORDERS
        $data['users_orders'] = $this->orders->get_users_orders($conditions);

        if (isset($uri['order_number'])) {
            if (!empty($data['users_orders'])) {
                $data['status_select_count'] = 1;
            } else {
                $data['status_select_count'] = 0;
            }
        }
        // GET USERS/COMPANIES DETAIL
        $users_id = [];
        if (!empty($data['users_orders'])) {
            foreach ($data['users_orders'] as $order) {
                if (have_right('buy_item')) {
                    $users_id[] = $order['id_seller'];
                } elseif (have_right('manage_seller_orders')) {
                    $users_id[] = $order['id_buyer'];
                } elseif (have_right('manage_shipper_orders')) {
                    $users_id[] = $order['id_seller'];
                }
            }
        }

        if (!empty($users_id)) {
            $data['users_info'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.user_group, users.user_photo");
            $data['users_info'] = arrayByKey($data['users_info'], 'idu');

            if (have_right('buy_item') || have_right('manage_shipper_orders')) {
                $this->load->model('Company_Model', 'company');
                $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), 'id_company, name_company, index_name, id_user, type_company, logo_company'), 'id_user');
            }
        }

        $data['video_tour'] = $this->video_tour->get_video_tour(['page' => 'order/my', 'user_group' => user_group_type()]);

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->ordersEpl($data);
        } else {
            $this->ordersAll($data);
        }
    }

    public function ajax_update_sidebar_counters()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('manage_shipper_orders') && !have_right('manage_seller_orders') && !have_right('buy_item')) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $this->_load_main();

        $id_user = privileged_user_id();

        if (have_right('buy_item')) {
            $count_conditions = ['id_buyer' => $id_user];
            $count_archived = ['id_buyer' => $id_user, 'state_buyer' => 1];
        } elseif (have_right('manage_seller_orders')) {
            $count_conditions = ['id_seller' => $id_user];
            $count_archived = ['id_seller' => $id_user, 'state_seller' => 1];
        } elseif (have_right('manage_shipper_orders')) {
            $count_conditions = ['id_shipper' => $id_user];
            $count_archived = ['id_shipper' => $id_user];
        }

        $statuses = $this->orders->count_orders_by_statuses($count_conditions);
        $statuses_counters = [];
        $finished_statuses_array = [
            'order_completed',
            'late_payment',
            'canceled_by_buyer',
            'canceled_by_seller',
            'canceled_by_ep', ];

        $canceled_statuses_array = [
            'late_payment',
            'canceled_by_buyer',
            'canceled_by_seller',
            'canceled_by_ep', ];

        $statuses_counters['total_finished'] = $statuses_counters['total_processing'] = 0;

        foreach ($statuses as $status) {
            if (in_array($status['alias'], $finished_statuses_array)) {
                $statuses_counters['total_finished'] += $status['counter'];
            } else {
                $statuses_counters['total_processing'] += $status['counter'];
            }

            if (in_array($status['alias'], $canceled_statuses_array)) {
                $statuses_counters['canceled'] += $status['counter'];
            } else {
                $statuses_counters[$status['alias']] = $status['counter'];
            }
        }

        $statuses_counters['archived'] = $this->orders->simple_count_orders($count_archived);
        jsonResponse('', 'success', ['counters' => $statuses_counters]);
    }

    public function ajax_order_info()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('manage_shipper_orders') && !have_right('manage_seller_orders') && !have_right('buy_item')) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        // SET PRIVILEGED USER ID FROM SESSION - IF STAFF USER: TAKE ID_SELLER
        $id_user = privileged_user_id();

        $this->_load_main();
        $this->load->model('Company_Model', 'company');
        $this->load->model('Country_Model', 'country');
        $this->load->model('Invoices_Model', 'invoices');
        $this->load->model('User_Bills_Model', 'user_bills');

        switch ($_POST['type']) {
            case 'search_orders':
                $keywords = cleanInput(cut_str($_POST['keywords']));
                if (empty($keywords)) {
                    jsonResponse(translate('systmess_error_my_orders_no_search_keywords'));
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_orders_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intval($_POST['page']) > 1) {
                    $page = intval($_POST['page']);
                }

                $start_from = (1 == $page) ? 0 : ($page * $per_page) - $per_page;

                // GET STATUSES LIST
                $statuses = arrayByKey($this->orders->get_orders_status(), 'alias');
                $search_filter = cleanInput($_POST['search_filter']);

                if (!empty($search_filter)) {
                    switch ($search_filter) {
                        case 'order_number':
                            $conditions = ['id_order' => toId($keywords)];

                        break;
                        case 'archived':
                            $conditions = ['keywords' => $keywords];

                        break;
                        case 'canceled':
                            $conditions = ['keywords' => $keywords];
                            $conditions['statuses'] = '12,13,14,15';

                        break;

                        default:
                            if (empty($statuses[$search_filter])) {
                                jsonResponse(translate('systmess_error_invalid_data'));
                            }

                            $conditions = ['keywords' => $keywords];
                            $conditions['status'] = $statuses[$search_filter]['id'];

                        break;
                    }
                } else {
                    $conditions = ['keywords' => $keywords];
                }

                if (have_right('buy_item')) {
                    $conditions['id_user'] = $id_user;
                    if ('archived' == $search_filter) {
                        $conditions['state_buyer'] = 1;
                    }
                } elseif (have_right('manage_seller_orders')) {
                    $conditions['id_seller'] = $id_user;
                    if ('archived' == $search_filter) {
                        $conditions['state_seller'] = 1;
                    }
                } elseif (have_right('manage_shipper_orders')) {
                    $conditions['id_shipper'] = $id_user;
                }

                $conditions['limit'] = $start_from . ', ' . $per_page;

                $data['users_orders'] = $this->orders->get_users_orders($conditions);

                // TOTAL ORDERS BY SELECTED CONDITIONS
                // NEED FOR DASHBOARD PAGINATION TO UPDATE PAGINATION IN CASE OF CHANGES
                if ('order_number' == $search_filter) {
                    if (!empty($data['users_orders'])) {
                        $data['total_orders_by_status'] = 1;
                    } else {
                        $data['total_orders_by_status'] = 0;
                    }
                } else {
                    $data['total_orders_by_status'] = $this->orders->get_orders_count($conditions);
                }

                // GET USERS/COMPANIES DETAIL
                $users_id = [];
                if (!empty($data['users_orders'])) {
                    $ishippers = [];
                    foreach ($data['users_orders'] as $order) {
                        if (have_right('buy_item')) {
                            $users_id[] = $order['id_seller'];
                        } elseif (have_right('manage_seller_orders')) {
                            $users_id[] = $order['id_buyer'];
                        } elseif (have_right('manage_shipper_orders')) {
                            $users_id[] = $order['id_seller'];
                        }

                        if ('ishipper' == $order['shipper_type']) {
                            $ishippers[] = $order['id_shipper'];
                        }
                    }

                    if (!empty($ishippers)) {
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $data['orders_shippers'] = arrayByKey($this->ishippers->get_shippers(['shippers_list' => implode(',', $ishippers)]), 'id_shipper');
                    }
                }

                if (!empty($users_id)) {
                    $data['users_info'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.user_group, users.user_photo");
                    $data['users_info'] = arrayByKey($data['users_info'], 'idu');

                    if (have_right('buy_item') || have_right('manage_shipper_orders')) {
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), 'id_company, name_company, index_name, id_user, type_company, logo_company'), 'id_user');
                    }
                }

                // DISPLAY VIEW BY USER TYPE
                $this->view->assign($data);

                $list = $this->view->fetch('new/order/order_list_view');

                jsonResponse('', 'success', ['orders_list' => $list, 'total_orders_by_status' => $data['total_orders_by_status']]);

            break;
            case 'order_list':
                // STATUS IS REQUIRED - IF NOT ISSET SET DEFAULT: NEW_ORDER
                if (empty($_POST['status'])) {
                    $status = 'new_order';
                } else {
                    $status = $_POST['status'];
                }

                // GET STATUSES LIST
                $statuses = arrayByKey($this->orders->get_orders_status(), 'alias');

                if (
                    empty($statuses[$status])
                    && 'order_number' != $status
                    && 'new_orders' != $status
                    && 'archived' != $status
                    && 'all' != $status
                    && 'canceled' != $status
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_orders_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intval($_POST['page']) > 1) {
                    $page = intval($_POST['page']);
                }

                $start_from = (1 == $page) ? 0 : ($page * $per_page) - $per_page;

                if ('order_number' == $status) {
                    $id_order_selected = intval($_POST['order']);
                }

                // PREPARING CONDITIONS FOR ORDERS, ORDERS COUNTERS BY STATUSES
                if (have_right('buy_item')) {
                    $conditions = ['id_user' => $id_user];
                    $count_conditions = ['id_buyer' => $id_user];

                    if ('all' == $status) {
                        $conditions['order_by'] = 'os.position ASC, io.update_date DESC';
                    } elseif ('canceled' == $status) {
                        $conditions['statuses'] = '12,13,14,15';
                        $count_conditions['statuses'] = '12,13,14,15';
                    } elseif ('order_number' == $status) {
                        $conditions['id_order'] = $id_order_selected;
                        $count_conditions['id_order'] = $id_order_selected;
                    } elseif ('archived' != $status) {
                        $conditions['status'] = $statuses[$status]['id'];
                        $count_conditions['status'] = $statuses[$status]['id'];
                    } else {
                        $conditions['state_buyer'] = 1;
                        $count_conditions['state_buyer'] = 1;
                    }
                } elseif (have_right('manage_seller_orders')) {
                    $conditions = ['id_seller' => $id_user];
                    $count_conditions = ['id_seller' => $id_user];

                    if ('canceled' == $status) {
                        $conditions['statuses'] = '12,13,14,15';
                        $count_conditions['statuses'] = '12,13,14,15';
                    } elseif ('order_number' == $status) {
                        $conditions['id_order'] = $id_order_selected;
                        $count_conditions['id_order'] = $id_order_selected;
                    } elseif ('archived' != $status) {
                        $conditions['status'] = $statuses[$status]['id'];
                        $count_conditions['status'] = $statuses[$status]['id'];
                    } else {
                        $conditions['state_seller'] = 1;
                        $count_conditions['state_seller'] = 1;
                    }
                } elseif (have_right('manage_shipper_orders')) {
                    $conditions = ['id_shipper' => $id_user];
                    $count_conditions = ['id_shipper' => $id_user];

                    if ('canceled' == $status) {
                        $conditions['statuses'] = '12,13,14,15';
                        $count_conditions['statuses'] = '12,13,14,15';
                    } elseif ('order_number' == $status) {
                        $conditions['id_order'] = $id_order_selected;
                        $count_conditions['id_order'] = $id_order_selected;
                    } elseif ('archived' != $status) {
                        if ('new_orders' != $status) {
                            $conditions['status'] = $statuses[$status]['id'];
                            $count_conditions['status'] = $statuses[$status]['id'];
                            // } else {
                            // $conditions['state_shipper'] = 1;
                            // $count_conditions['state_shipper'] = 1;
                        }
                    }
                }

                $conditions['limit'] = $start_from . ', ' . $per_page;
                $data['users_orders'] = $this->orders->get_users_orders($conditions);

                // TOTAL ORDERS BY SELECTED CONDITIONS
                // NEED FOR DASHBOARD PAGINATION TO UPDATE PAGINATION IN CASE OF CHANGES
                $data['total_orders_by_status'] = $this->orders->simple_count_orders($count_conditions);

                // GET USERS/COMPANIES DETAIL
                $users_id = [];
                if (!empty($data['users_orders'])) {
                    $ishippers = [];
                    foreach ($data['users_orders'] as $order) {
                        if (have_right('buy_item')) {
                            $users_id[] = $order['id_seller'];
                        } elseif (have_right('manage_seller_orders')) {
                            $users_id[] = $order['id_buyer'];
                        } elseif (have_right('manage_shipper_orders')) {
                            $users_id[] = $order['id_seller'];
                        }

                        if ('ishipper' == $order['shipper_type']) {
                            $ishippers[] = $order['id_shipper'];
                        }
                    }

                    if (!empty($ishippers)) {
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $data['orders_shippers'] = arrayByKey($this->ishippers->get_shippers(['shippers_list' => implode(',', $ishippers)]), 'id_shipper');
                    }
                }

                if (!empty($users_id)) {
                    $data['users_info'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.user_group, users.user_photo");
                    $data['users_info'] = arrayByKey($data['users_info'], 'idu');

                    if (have_right('buy_item') || have_right('manage_shipper_orders')) {
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), 'id_company, name_company, index_name, id_user, type_company, logo_company'), 'id_user');
                    }
                }
                // DISPLAY VIEW BY USER TYPE
                $this->view->assign($data);

                $list = $this->view->fetch('new/order/order_list_view');

                jsonResponse('', 'success', ['orders_list' => $list, 'total_orders_by_status' => $data['total_orders_by_status']]);

            break;
            case 'order':
                $id_order = intval($_POST['order']);

                $data['order'] = $this->orders->get_full_order($id_order);
                if (empty($data['order'])) {
                    jsonResponse(translate('systmess_error_order_doesnt_exist'));
                }

                if (!is_privileged('user', $data['order']['id_shipper'], 'manage_shipper_orders')
                    && !is_privileged('user', $data['order']['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $data['order']['id_buyer'], 'buy_item')) {
                    jsonResponse(translate('systmess_error_order_doesnt_exist'));
                }

                $data['purchased_products'] = !empty($data['order']['purchased_products']) ? json_decode($data['order']['purchased_products'], true) : [];
                if ('new_order' == $data['order']['status_alias'] || empty($data['purchased_products'])) {
                    foreach ($data['order']['ordered'] as $_item_ordered) {
                        $data['purchased_products'][] = [
                            'id_item'          => $_item_ordered['id_item'],
                            'id_ordered_item'  => $_item_ordered['id_ordered_item'],
                            'id_snapshot'      => $_item_ordered['id_snapshot'],
                            'type'             => 'item',
                            'name'             => $_item_ordered['title'],
                            'unit_price'       => $_item_ordered['price_ordered'],
                            'quantity'         => $_item_ordered['quantity_ordered'],
                            'total_price'      => floatval($_item_ordered['price_ordered'] * $_item_ordered['quantity_ordered']),
                            'detail_ordered'   => $_item_ordered['detail_ordered'],
                            'item_weight'      => $_item_ordered['item_weight'],
                            'item_length'      => $_item_ordered['item_length'],
                            'item_width'       => $_item_ordered['item_width'],
                            'item_height'      => $_item_ordered['item_height'],
                            'hs_tariff_number' => $_item_ordered['hs_tariff_number'],
                            'country_abr'      => $_item_ordered['country_abr'],
                            'image'            => $_item_ordered['main_image'],
                            'reviews_count'    => $_item_ordered['snapshot_reviews_count'],
                            'rating'           => $_item_ordered['snapshot_rating'],
                        ];
                    }
                }

                // $data['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : array();
                // $data['products'] = !empty($data['purchase_order']['invoice']['products']) ? json_decode($data['purchase_order']['invoice']['products'], true) : $data['order']['ordered'];

                $data['order']['ordered'] = arrayByKey($data['order']['ordered'], 'id_ordered_item');
                $total_paid = $this->user_bills->summ_bills_by_item($data['order']['id_buyer'], $id_order, "'confirmed'", 1);
                $data['bills_percent'] = compareFloatNumbers($data['order']['final_price'], 0, '>') ? ($total_paid * 100) / $data['order']['final_price'] : 100;
                $data['bills_counter'] = $this->user_bills->isset_bills_by_order($id_order);

                $ordered_items_ids = array_keys($data['order']['ordered']);

                // FOR SHOWING FEEDBACK AND REVIEWS BUTTONS
                if ('order_completed' == $data['order']['status_alias']) {
                    $this->load->model('UserFeedback_Model', 'userfeedbacks');
                    $this->load->model('ItemsReview_Model', 'itemreviews');

                    $data['order_feedbacks'] = $this->userfeedbacks->get_order_feedbacks($id_order);

                    $data['user_ordered_items_for_reviews'] = $this->itemreviews->check_user_review(['id_buyer' => $data['order']['id_buyer'], 'id_seller' => $data['order']['id_seller'], 'id_ordered_item' => implode(',', $ordered_items_ids)]);

                    if (!empty($data['user_ordered_items_for_reviews'])) {
                        $data['user_ordered_items_for_reviews'] = arrayByKey($data['user_ordered_items_for_reviews'], 'id_ordered_item');
                    }
                }

                // CALCULATE EXPIRE TIME IN MILISECONDS FOR COUNTDOWN TIMER
                $show_expire = false;
                if (!in_array($data['order']['status_alias'], ['order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'])) {
                    $show_expire = true;
                    $data['expire'] = $expire = (strtotime($data['order']['status_countdown']) - time()) * 1000;
                    $data['extend_btn'] = false;
                    $data['show_extend_btn'] = false;
                    if (
                        is_privileged('user', $data['order']['id_buyer'], 'buy_item')
                        && in_array($data['order']['status_alias'], ['invoice_sent_to_buyer', 'shipper_assigned', 'payment_processing', 'shipping_completed'])
                        || is_privileged('user', $data['order']['id_seller'], 'manage_seller_orders')
                        && in_array($data['order']['status_alias'], ['new_order', 'invoice_confirmed', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress'])
                    ) {
                        if ($data['order']['extend_request']) {
                            $data['show_extend_btn'] = true;
                        } else {
                            $data['extend_btn'] = true;
                        }
                    }
                }
                if (
                    1 == $data['order']['cancel_request']
                    && !in_array($data['order']['status_alias'], ['shipping_completed', 'shipping_in_progress', 'order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'])
                ) {
                    $show_expire = false;
                }

                // FOR SHOWING DISPUTE BUTTON
                $this->load->model('Dispute_Model', 'dispute');
                if ('shipping_in_progress' == $data['order']['status_alias']) {
                    $data['order_disputes'] = $this->dispute->get_order_disputes($id_order, ['id_user' => $id_user, 'order_status' => 9, 'status' => "'init','processing','closed'", 'id_ordered' => 0]);
                }
                if ('shipping_completed' == $data['order']['status_alias']) {
                    $ordered_items_ids[] = 0;
                    $order_disputes = $this->dispute->get_order_disputes($id_order, ['id_user' => $id_user, 'order_status' => 10, 'status' => "'init','processing','closed'", 'id_ordered' => implode(',', $ordered_items_ids)]);

                    $data['order_disputes'] = arrayByKey($order_disputes, 'id_ordered');
                }

                $user_seller_id = 0;
                $user_buyer_id = 0;
                if (have_right('buy_item')) {
                    $user_seller_id = $data['order']['id_seller'];
                } elseif (have_right('manage_seller_orders')) {
                    $user_buyer_id = $data['order']['id_buyer'];
                } elseif (have_right('manage_shipper_orders')) {
                    $user_seller_id = $data['order']['id_seller'];
                    $user_buyer_id = $data['order']['id_buyer'];
                }

                // FOR SHOWING DOCUMENTS BUTTONS
                $data['documents_count'] = with(model(Envelopes_Model::class), fn (Envelopes_Model $envelopes) => $envelopes->countBy(['conditions' => [
                    'for_orders' => true,
                    'for_user'   => (int) privileged_user_id(),
                    'order'      => (int) $id_order,
                    'not_status' => EnvelopeStatuses::VOIDED,
                ]]));

                if (0 != $user_buyer_id) {
                    $this->load->model('Company_Buyer_Model', 'company_buyer');
                    $data['user_buyer_info'] = $this->user->getSimpleUser($user_buyer_id, "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.user_photo");
                    $data['company_buyer_info'] = $this->company_buyer->get_company_by_user($user_buyer_id);
                }

                if (0 != $user_seller_id && (have_right('buy_item') || have_right('manage_shipper_orders'))) {
                    $this->load->model('Company_Model', 'company');
                    $data['company_info'] = $this->company->get_seller_base_company($user_seller_id, 'cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company');
                }

                if ($data['order']['id_shipper'] > 0) {
                    if ('ep_shipper' == $data['order']['shipper_type']) {
                        $this->load->model('Shippers_Model', 'shippers');
                        $shipper_info = $this->shippers->get_shipper_by_user($data['order']['id_shipper']);
                        $data['shipper_info'] = [
                            'shipper_name' => $shipper_info['co_name'],
                            'shipper_logo' => getDisplayImageLink(['{ID}' => $shipper_info['id'], '{FILE_NAME}' => $shipper_info['logo']], 'shippers.main', ['thumb_size' => 1]),
                            'shipper_url'  => getShipperURL($shipper_info),
                        ];
                    } else {
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $shipper_info = $this->ishippers->get_shipper($data['order']['id_shipper']);
                        $data['shipper_info'] = [
                            'shipper_name'     => $shipper_info['shipper_original_name'],
                            'shipper_logo'     => __IMG_URL . 'public/img/ishippers_logo/' . $shipper_info['shipper_logo'],
                            'shipper_contacts' => $shipper_info['shipper_contacts'],
                        ];
                    }
                }

                if ($data['order']['ep_manager'] > 0) {
                    $data['ep_manager_info'] = $this->user->getSimpleUser($data['order']['ep_manager'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name");
                }

                if (!empty($data['order']['status_description'])) {
                    $data['description_title'] = json_decode($data['order']['status_description'], true);

                    $type_status_description = null;
                    if (have_right('buy_item')) {
                        $type_status_description = 'buyer';
                    } elseif (have_right('manage_seller_orders')) {
                        $type_status_description = 'seller';
                    } elseif (have_right('manage_shipper_orders')) {
                        $type_status_description = 'shipper';
                    } elseif (have_right('administrate_orders')) {
                        $type_status_description = 'ep_manager';
                    }

                    if (null !== $type_status_description) {
                        $description_title_array = $data['description_title'][$type_status_description];

                        $data['description_title'] = $description_title_array['text'];
                    }
                }

                $data['extend_days'] = config('order_extend_days');
                $this->load->model('Auto_Extend_model', 'auto_extend');
                $data['extend_info'] = $this->auto_extend->get_extend_request_by_order($id_order);

                if (have_right('buy_item') || have_right('manage_shipper_orders')) {
                    $btnChatSeller = new ChatButton(['recipient' => $data['order']['id_seller'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['text' => 'Chat with seller']);
                    $data['btnChatSeller'] = $btnChatSeller->button();

                    $btnChatSeller2 = new ChatButton(['recipient' => $data['order']['id_seller'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['classes' => 'link-ajax p-0 w-auto display-ib bg-n', 'icon' => '', 'text' => 'Chat with seller']);
                    $data['btnChatSeller2'] = $btnChatSeller2->button();
                }

                if (have_right('manage_seller_orders') || have_right('manage_shipper_orders')) {
                    $btnChatBuyer = new ChatButton(['recipient' => $data['order']['id_buyer'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['text' => 'Chat with buyer']);
                    $data['btnChatBuyer'] = $btnChatBuyer->button();

                    $btnChatBuyer2 = new ChatButton(['recipient' => $data['order']['id_buyer'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['classes' => 'link-ajax p-0 w-auto display-ib bg-n', 'icon' => '', 'text' => 'Chat with buyer']);
                    $data['btnChatBuyer2'] = $btnChatBuyer2->button();
                }

                if (!have_right('manage_shipper_orders') && !empty($data['shipper_info']) && ('ep_shipper' === $data['order']['shipper_type'])) {
                    $btnChatShipper = new ChatButton(['recipient' => $data['order']['id_shipper'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['text' => 'Chat with freight forwarder']);
                    $data['btnChatShipper'] = $btnChatShipper->button();

                    $btnChatShipper2 = new ChatButton(['recipient' => $data['order']['id_shipper'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['classes' => 'link-ajax p-0 w-auto display-ib bg-n', 'icon' => '', 'text' => 'Chat with freight forwarder']);
                    $data['btnChatShipper2'] = $btnChatShipper2->button();
                }

                if ($data['order']['ep_manager']) {
                    // TODO: admin chat hidden
                    $btnChatManager = new ChatButton(['hide' => true, 'recipient' => $data['order']['ep_manager'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['text' => 'Chat with manager']);
                    $data['btnChatManager'] = $btnChatManager->button();

                    // TODO: admin chat hidden
                    $btnChatManager2 = new ChatButton(['hide' => true, 'recipient' => $data['order']['ep_manager'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['classes' => 'link-ajax p-0 w-auto display-ib bg-n', 'icon' => '', 'text' => 'Chat with manager']);
                    $data['btnChatManager2'] = $btnChatManager2->button();
                }

                $this->view->assign($data);
                $content = $this->view->fetch('new/order/order_detail_view');

                jsonResponse('', 'success', ['order_info' => $content, 'expire' => $expire, 'show_timeline' => $show_expire]);

            break;
            case 'producing_status':
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'status_text',
                        'label' => 'Producing status detail',
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order detail',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = intval($_POST['order']);

                $order = $this->orders->get_order($id_order);

                if (empty($order) || !is_privileged('user', $order['id_seller'], 'manage_seller_orders')) {
                    jsonResponse(translate('systmess_error_order_doesnt_exist'));
                }

                if ('po' != $order['order_type']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!in_array($order['status_alias'], ['payment_processing', 'order_paid', 'payment_confirmed'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $status_text = cleanInput($_POST['status_text']);
                $action_date = date('Y-m-d H:i:s');
                $order_log = [
                    'date'    => formatDate($action_date, 'm/d/Y H:i:s'),
                    'user'    => 'Seller',
                    'message' => 'Producing status: ' . $status_text,
                ];

                $update_order = [
                    'producing_status' => $status_text,
                    'order_summary'    => $order['order_summary'] . ',' . json_encode($order_log),
                ];

                if (!$this->orders->change_order($id_order, $update_order)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_order_updated'), 'success', ['order' => $id_order]);

            break;
        }
    }

    // CANCEL ORDER REASONS ADMINISTRATION
    public function admin_reasons()
    {
        checkAdmin('manage_content');

        $this->load->model('Orders_model', 'orders');

        $data['orders_status'] = $this->orders->get_orders_status();
        $data['title'] = 'Cancel order reasons';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/order/manager_assigned/reasons_view');
        $this->view->display('admin/footer_view');
    }

    // CANCEL ORDER REASONS ADMINISTRATION - DT
    public function ajax_admin_reasons_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('moderate_content')) {
            jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $this->load->model('Orders_model', 'orders');

        $sorting = [
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id_reason' => 'r.id',
            ]),
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'order_status', 'key' => 'order_status', 'type' => 'int'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ['r.id-desc'] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        $reasons = $this->orders->get_reasons($params);
        $reasons_count = count($reasons);

        $output = [
            'sEcho'                => intval($_POST['sEcho']),
            'iTotalRecords'        => $reasons_count,
            'iTotalDisplayRecords' => $reasons_count,
            'aaData'               => [],
        ];

        if (empty($reasons)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($reasons as $reason) {
            $output['aaData'][] = [
                'dt_id_reason' => $reason['id'],
                'dt_reason'    => $reason['reason'],
                'dt_message'   => $reason['message'],
                'dt_actions'   => '<a href="' . __SITE_URL . 'order/popups_order/edit_reason/' . $reason['id'] . '" title="Edit the reason" data-title="Edit the reason" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_pencil"></i></a>'
                    . '<a href="#" class="confirm-dialog" data-callback="delete_reason" data-reason="' . $reason['id'] . '" title="Delete reason" data-message="Are you sure you want to delete this reason?"><i class="ep-icon ep-icon_remove txt-red"></i></a>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    // ASSIGNED ORDERS ADMINISTRATION
    public function admin_assigned()
    {
        checkAdmin('manage_content');

        $this->load->model('Orders_model', 'orders');

        $data['orders_status'] = $this->orders->get_orders_status();
        $data['last_order_assigned_id'] = $this->orders->get_order_assigned_last_id(id_session());
        $data['title'] = 'Orders assigned to me';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/order/manager_assigned/index_view');
        $this->view->display('admin/footer_view');
    }

    // ASSIGNED ORDERS ADMINISTRATION - DT
    public function ajax_admin_manager_orders_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('moderate_content')) {
            jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $this->load->model('User_model', 'user');
        $this->load->model('Orders_model', 'orders');
        $this->load->model('Usergroup_Model', 'ugroup');
        $this->load->model('Country_Model', 'country');
        $this->load->model('User_Bills_Model', 'user_bills');

        $sorting = [
            'limit'      => intval(cleanInput($_POST['iDisplayStart'])) . ',' . intval(cleanInput($_POST['iDisplayLength'])),
            'ep_manager' => id_session(),
            'date_val'   => 'update',
            'sort_by'    => flat_dt_ordering($_POST, [
                'dt_id_order'    => 'io.id',
                'dt_buyer'       => 'full_name_bayer',
                'dt_ship_to'     => 'io.ship_to',
                'dt_price'       => 'io.price',
                'dt_create_date' => 'io.order_date',
                'dt_date'        => 'io.update_date',
                'dt_status_date' => 'io.status_countdown',
                'dt_status'      => 'os.position',
            ]),
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'order_status', 'type' => 'cleanInput'],
            ['as' => 'expire_status', 'key' => 'expire_status', 'type' => 'cleanInput'],
            ['as' => 'id_user', 'key' => 'id_buyer', 'type' => 'int'],
            ['as' => 'id_seller', 'key' => 'id_seller', 'type' => 'int'],
            ['as' => 'price_from', 'key' => 'price_from', 'type' => 'cleanInput'],
            ['as' => 'price_to', 'key' => 'price_to', 'type' => 'cleanInput'],
            ['as' => 'ship_to_country', 'key' => 'ship_to_country', 'type' => 'int'],
            ['as' => 'ship_to_city', 'key' => 'ship_to_city', 'type' => 'int'],
            ['as' => 'date_from', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'date_to', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'cancel_request', 'key' => 'cancel_request', 'type' => 'cleanInput'],
            ['as' => 'dispute_opened', 'key' => 'dispute_opened', 'type' => 'cleanInput'],
            ['as' => 'realUsers', 'key' => 'real_users', 'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2],
        ]);

        if (!isset($filters['realUsers'])) {
            $filters['realUsers'] = 1;
        } elseif (2 === $filters['realUsers']) {
            unset($filters['realUsers']);
        }

        $params = array_merge($filters, $sorting);

        if (isset($_POST['id_shipper']) && $_POST['id_shipper']) {
            $params['id_shipper'] = intval($_POST['id_shipper']);
            $params['shipper_type'] = 'ep_shipper';
        }

        if (isset($_POST['id_ishipper']) && $_POST['id_ishipper']) {
            $params['id_shipper'] = intval($_POST['id_ishipper']);
            $params['shipper_type'] = 'ishipper';
        }

        if (2 === $params['realUsers']) {
            unset($params['realUsers']);
        }

        $orders_count = $this->orders->get_orders_count($params);
        $orders = $this->orders->get_users_orders($params);

        $orders_statuses_count = arrayByKey($this->orders->get_statuses_counters($params), 'status');
        $orders_statuses_count['expire_soon'] = [
            'status'         => 'expire_soon',
            'status_counter' => $this->orders->get_soon_expire_orders_count($params),
        ];

        $orders_statuses_count['expired'] = [
            'status'         => 'expired',
            'status_counter' => $this->orders->get_expired_orders_count($params),
        ];

        $output = [
            'sEcho'                 => intval($_POST['sEcho']),
            'iTotalRecords'         => $orders_count,
            'iTotalDisplayRecords'  => $orders_count,
            'orders_statuses_count' => $orders_statuses_count,
            'aaData'                => [],
        ];

        if (empty($orders)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($orders as $item) {
            $id_orders[] = $item['id'];
            $users_list[$item['id_seller']] = $item['id_seller'];
            $users_list[$item['id_buyer']] = $item['id_buyer'];
            if ('ep_shipper' == $item['shipper_type']) {
                $shipper_list[$item['id_shipper']] = $item['id_shipper'];
            } else {
                $ishipper_list[$item['id_shipper']] = $item['id_shipper'];
            }

            $countries_list[$item['ship_to_country']] = $item['ship_to_country'];
            $countries_list[$item['ship_from_country']] = $item['ship_from_country'];

            if (0 < $item['ship_to_state']) {
                $state_cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            } else {
                $cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            }

            if (0 < $item['ship_from_state']) {
                $state_cities_list[$item['ship_from_city']] = $item['ship_from_city'];
            } else {
                $cities_list[$item['ship_from_city']] = $item['ship_from_city'];
            }
        }

        $countries = $this->country->get_simple_countries(implode(',', $countries_list));

        if (!empty($cities_list)) {
            $cities = $this->country->get_simple_cities(implode(',', $cities_list));
        }

        if (!empty($state_cities_list)) {
            $state_cities = $this->country->get_simple_cities_by_state(implode(',', $state_cities_list));
        }

        if (!empty($shipper_list)) {
            $this->load->model('Shippers_Model', 'shippers');
            $shipper_list = $this->shippers->get_shippers(['shippers_list' => implode(',', $shipper_list)]);
            $shipper_list = arrayByKey($shipper_list, 'id_user');
        }

        if (!empty($ishipper_list)) {
            $this->load->model('Ishippers_Model', 'ishippers');
            $ishipper_list = $this->ishippers->get_shippers(['shippers_list' => implode(',', $ishipper_list)]);
            $ishipper_list = arrayByKey($ishipper_list, 'id_shipper');
        }

        $bills_counts = $this->user_bills->get_bills_counts_by_order(['id_orders' => implode(',', $id_orders)]);

        $users_info = $this->user->getUsers(['users_list' => implode(',', $users_list), 'additional' => 1, 'company_info' => 1]);
        $users_info = arrayByKey($users_info, 'idu');

        foreach ($orders as $row) {
            $counts_bill = '--';
            if (!empty($bills_counts[$row['id']])) {
                $counts_bill = ' <div class="tal">
							<div><a class="fancybox fancybox.ajax" data-title="All bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '">All: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_all'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="New bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/init">New: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_init'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Paid bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/paid">Paid: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_paid'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Confirmed bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/confirmed">Confirmed: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_confirmed'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Cancelled bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/unvalidated">Cancelled: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_unvalidated'] . '</strong></a></div>
						</div> ';
            }

            if (!empty($users_info[$row['id_seller']]['index_name'])) {
                $link = $users_info[$row['id_seller']]['index_name'];
            } else {
                $link = 'seller/' . strForURL($users_info[$row['id_seller']]['name_company']) . '-' . $users_info[$row['id_seller']]['id_company'];
            }

            if (0 < $row['ship_to_state']) {
                $city_to = $state_cities[$row['ship_to_city']];
            } else {
                $city_to = $cities[$row['ship_to_city']];
            }

            if (0 < $row['ship_from_state']) {
                $city_from = $state_cities[$row['ship_from_city']];
            } else {
                $city_from = $cities[$row['ship_from_city']];
            }

            $links = [];
            if ('order_paid' == $row['status_alias']) {
                $links[] = sprintf(
                    <<<'CONFIRM_ORDER_PAYMENT_BTN'
                    <a class="confirm-dialog" data-callback="confirm_order_paid" href="#" data-order="%s" data-message="%s" title="Confirm total order payment.">
                        <i class="ep-icon ep-icon_thumbup txt-green"></i>
                    </a>
                    CONFIRM_ORDER_PAYMENT_BTN,
                    $row['id'],
                    translate('systmess_confirm_total_order_payment', null, true),
                );
            }

            if ('preparing_for_shipping' == $row['status_alias']) {
                $links[] = sprintf(
                    <<<'CHANGE_ORDER_STATUS_BTN'
                    <a class="confirm-dialog" data-callback="change_order_status" href="#" data-order="%s" data-message="%s" title="Change order status to Shipping in progress.">
                        <i class="ep-icon ep-icon_truck-move txt-orange fs-24 lh-16"></i>
                    </a>
                    CHANGE_ORDER_STATUS_BTN,
                    $row['id'],
                    translate('systmess_confirm_order_set_status_shipping_in_progress', null, true)
                );
            }

            if ('shipping_in_progress' == $row['status_alias']) {
                $links[] = sprintf(
                    <<<'CHANGE_ORDER_STATUS_BTN'
                    <a class="confirm-dialog" data-callback="change_order_status" href="#" data-order="%s" data-message="%s" title="Change order status to Ready for pickup.">
                        <i class="ep-icon ep-icon_truck-ok txt-green fs-24 lh-16"></i>
                    </a>
                    CHANGE_ORDER_STATUS_BTN,
                    $row['id'],
                    translate('systmess_confirm_order_set_status_ready_for_pickup', null, true)
                );
            }

            if ('shipping_ready_for_pickup' == $row['status_alias']) {
                $links[] = sprintf(
                    <<<'CHANGE_ORDER_STATUS_BTN'
                    <a class="confirm-dialog" data-callback="change_order_status" href="#" data-order="%s" data-message="%s" title="Change order status to Shipping completed.">
                        <i class="ep-icon ep-icon_battery-level-75 txt-orange fs-24 lh-16"></i>
                    </a>
                    CHANGE_ORDER_STATUS_BTN,
                    $row['id'],
                    translate('systmess_confirm_order_set_status_shipping_completed', null, true)
                );
            }

            if ('shipping_completed' == $row['status_alias']) {
                $links[] = sprintf(
                    <<<'CHANGE_ORDER_STATUS_BTN'
                    <a class="confirm-dialog" data-callback="change_order_status" href="#" data-order="%s" data-message="%s" title="Change order status to Order completed.">
                        <i class="ep-icon ep-icon_ok-circle txt-green"></i>
                    </a>
                    CHANGE_ORDER_STATUS_BTN,
                    $row['id'],
                    translate('systmess_confirm_order_set_status_order_completed', null, true)
                );
            }

            if (in_array($row['status_alias'], ['shipping_in_progress', 'shipping_completed'])) {
                $links[] = '<a href="' . __SITE_URL . 'order/popups_order/edit_tracking_info/' . $row['id'] . '" title="Edit tracking info" data-title="Edit tracking info" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_file-edit"></i></a>';
            }

            if (!in_array($row['status_alias'], ['order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'])) {
                $links[] = '<a href="' . __SITE_URL . 'order/popups_order/cancel_order/' . $row['id'] . '" title="Cancel the order" data-title="Cancel the order" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_minus-circle txt-red"></i></a>';

                if ((bool) $row['extend_request']) {
                    $links[] = '<a href="' . __SITE_URL . 'extend/popup_form/detail_admin/' . $row['extend_request'] . '" title="Extend request" data-title="Extend request" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_hourglass-plus txt-orange"></i></a>';
                } else {
                    $links[] = '<a href="' . __SITE_URL . 'extend/popup_form/extend_time/order/' . $row['id'] . '" title="Extend order status time" data-title="Extend order status time" class="fancyboxValidateModal fancybox.ajax"><i class="ep-icon ep-icon_hourglass-plus txt-green"></i></a>';
                }

                if ('0000-00-00 00:00:00' == $row['status_countdown']) {
                    $row['status_countdown'] = '2000-00-00 00:00:00';
                }

                $expire_time = strtotime($row['status_countdown']);
                $expire = "<strong><span class='countdown-dt' data-expire='" . (($expire_time - time()) * 1000) . "'></span></strong>";
            } else {
                $expire = '<strong class="txt-green">Finished</strong>';
                if (!empty($row['external_bills_requests'])) {
                    $links[] = '<a class="fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'external_bills/popup_forms/add_form/order/' . $row['id'] . '" data-title="View external bills" title="View external bills"><i class="ep-icon ep-icon_billing txt-green"></i></a>';
                } else {
                    $links[] = '<a class="fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'external_bills/popup_forms/add_form/order/' . $row['id'] . '" data-title="Create external bills" title="Create external bills"><i class="ep-icon ep-icon_billing txt-orange"></i></a>';
                }
            }

            $problems_btns = '';
            if (0 == $row['cancel_request']) {
                $problems_btns .= '<i class="ep-icon ep-icon_minus-circle txt-gray-light" title="Cancel order requests"></i>';
            } elseif (1 == $row['cancel_request']) {
                $problems_btns .= '<a class="display-ib fancybox fancybox.ajax" data-title="Cancel order requests" title="Cancel order requests" href="' . __SITE_URL . 'order/popups_order/order_cancel_requests/' . $row['id'] . '"><i class="ep-icon ep-icon_minus-circle txt-red"></i></a>';
            } else {
                $problems_btns .= '<a class="display-ib fancybox fancybox.ajax" data-title="Cancel order requests" title="Cancel order requests" href="' . __SITE_URL . 'order/popups_order/order_cancel_requests/' . $row['id'] . '"><i class="ep-icon ep-icon_minus-circle txt-blue"></i></a>';
            }

            if (0 == $row['dispute_opened']) {
                $problems_btns .= '<i class="ep-icon ep-icon_low txt-gray-light" title="View dispute"></i>';
            } elseif (1 == $row['dispute_opened']) {
                $problems_btns .= '<a class="display-ib" href="' . __SITE_URL . 'dispute/administration/order/' . $row['id'] . '" title="View dispute"><i class="ep-icon ep-icon_low txt-red"></i></a>';
            } else {
                $problems_btns .= '<a class="display-ib" href="' . __SITE_URL . 'dispute/administration/order/' . $row['id'] . '" title="View dispute"><i class="ep-icon ep-icon_low txt-blue"></i></a>';
            }

            $shipper = 'Not assigned';
            if ($row['id_shipper']) {
                if ('ep_shipper' == $row['shipper_type']) {
                    $shipper_img_url = getDisplayImageLink(['{ID}' => $shipper_list[$row['id_shipper']]['id'], '{FILE_NAME}' => $shipper_list[$row['id_shipper']]['logo']], 'shippers.main', ['thumb_size' => 1]);
                    $shipper = '<div class="pull-left w-100pr tal">';
                    $shipper .= '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by freight forwarder" data-value="' . $shipper_list[$row['id_shipper']]['id_user'] . '" data-value-text="' . $shipper_list[$row['id_shipper']]['co_name'] . '" data-title="Freight Forwarder" data-name="id_shipper"></a>';
                    $shipper .= '<a class="ep-icon ep-icon_item txt-orange" title="View freight forwarder" target="_blank" href="' . __SITE_URL . 'shipper/' . strForURL($shipper_list[$row['id_shipper']]['co_name'] . ' ' . $shipper_list[$row['id_shipper']]['id']) . '"></a>';
                    $shipper .= '</div>';
                    $shipper .= '<img class="mw-80 mh-40" src="' . $shipper_img_url . '" alt="' . $shipper_list[$row['id_shipper']]['co_name'] . '">';
                    $shipper .= '<br>' . $shipper_list[$row['id_shipper']]['co_name'];
                } else {
                    $shipper = '<div class="pull-left w-100pr tal">';
                    $shipper .= '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by freight forwarder" data-value="' . $row['id_shipper'] . '" data-value-text="' . $ishipper_list[$row['id_shipper']]['shipper_original_name'] . '" data-title="Freight Forwarder" data-name="id_ishipper"></a>';
                    $shipper .= '</div>';
                    $shipper_img_url = __IMG_URL . 'public/img/ishippers_logo/' . $ishipper_list[$row['id_shipper']]['shipper_logo'];
                    $shipper .= '<img class="mw-80 mh-40" src="' . $shipper_img_url . '" alt="' . $ishipper_list[$row['id_shipper']]['shipper_original_name'] . '">';
                    $shipper .= '<br>' . $ishipper_list[$row['id_shipper']]['shipper_original_name'];
                }
            }

            // TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $row['id_seller'], 'recipientStatus' => $users_info[$row['id_seller']]['status'], 'module' => 9, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            // TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $row['id_buyer'], 'recipientStatus' => $users_info[$row['id_buyer']]['status'], 'module' => 9, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();


            $shipFromCountry = "";
            if ($row['ship_from_country']) {
                $shipFromCountry = <<<SHIP_FROM_COUNTRY
                    <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship from country"
                        title="Filter by country: {$countries[$row['ship_from_country']]['country']}"
                        data-value-text="{$countries[$row['ship_from_country']]['country']}"
                        data-value="{$row['ship_from_country']}" data-name="ship_from_country">
                    </a>
                    SHIP_FROM_COUNTRY
                ;
            }

            $shipFromCity = "";
            if ($row['ship_from_city']) {
                $shipFromCity = <<<SHIP_FROM_CITY
                    <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship from city"
                        title="Filter by city: {$city_from}"
                        data-value-text="{$city_from}"
                        data-value="{$row['ship_from_city']}" data-name="ship_from_city">
                    </a>
                    SHIP_FROM_CITY
                ;
            }

            $shipToCountry = "";
            if ($row['ship_to_country']) {
                $shipToCountry = <<<SHIP_TO_COUNTRY
                    <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship to country"
                        title="Filter by country: {$countries[$row['ship_to_country']]['country']}"
                        data-value-text="{$countries[$row['ship_to_country']]['country']}"
                        data-value="{$row['ship_to_country']}" data-name="ship_to_country">
                    </a>
                    SHIP_TO_COUNTRY
                ;
            }

            $shipToCity = "";
            if ($row['ship_to_city']) {
                $shipToCity = <<<SHIP_TO_CITY
                    <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship to city"
                        title="Filter by city: {$city_to}"
                        data-value-text="{$city_to}"
                        data-value="{$row['ship_to_city']}" data-name="ship_to_city">
                    </a>
                    SHIP_TO_CITY
                ;
            }

            $output['aaData'][] = [
                'dt_id_order'   => '<a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $row['id'] . '" class="fancybox.ajax fancybox clearfix" data-title="Order details">' . orderNumber($row['id']) . '</a>'
                                    . '<a class="display-ib" href="#" title="View detail" rel="order_details"><i class="ep-icon ep-icon_plus"></i></a>'
                                    . '<a class="display-ib fancybox fancybox.ajax" href="' . __SITE_URL . 'order/popups_order/admin_order_timeline/' . $row['id'] . '" title="View order timeline" data-title="Order ' . orderNumber($row['id']) . ' - Timeline"><i class="ep-icon ep-icon_clock lh-20 fs-20"></i></a>'
                                    . '<a class="display-ib fancybox fancybox.ajax" href="' . __SITE_URL . 'order_documents/popup_admin_forms/list-envelopes/' . $row['id'] . '" title="View order documents" data-title="Order ' . orderNumber($row['id']) . ' - Documents"><i class="ep-icon ep-icon_items lh-20 fs-20"></i></a>',
                'dt_users'      => '<span><strong>Seller:</strong>'
                                    . '<a class="dt_filter" data-title="Seller" title="Filter by ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value-text="' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value="' . $row['id_seller'] . '" data-name="id_seller">' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '</a>'
                                    . ' </span>'
                                    . '<div>'
                                    . '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . __SITE_URL . $link . '"></a>'
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . ' ' . $row['id_seller']) . '"></a>'
                                    . $btnChatSellerView
                                    . '</div>'
                                    . '<span><strong>Buyer:</strong> '
                                    . '<a class="dt_filter" data-title="Buyer" title="Filter by ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value-text="' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value="' . $row['id_buyer'] . '" data-name="id_buyer">' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '</a>'
                                    . '</span>'
                                    . '<div>'
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . ' ' . $row['id_buyer']) . '"></a>'
                                    . $btnChatBuyerView
                                    . '</div>',
                'dt_shiper'     => $shipper,
                'dt_bills'      => $counts_bill,
                'dt_price'      => '<p title="total price">Total: $ ' . get_price($row['final_price'], false)
                                    . '<p title="ship price">Ship: $ ' . get_price($row['ship_price'], false) . '</p>',
                'dt_create_date'=> formatDate($row['order_date']),
                'dt_date'       => formatDate($row['update_date']),
                'dt_status_date'=> $expire,
                'dt_problems'   => $problems_btns,
                'dt_status'     => '<span><i class="ep-icon ' . $row['icon'] . ' fs-30"></i><br> ' . $row['status'] . '</span>',
                'dt_actions'    => implode(' ', $links),

                'dt_ship_from'  => '<div class="pull-left">'
                                    . $shipFromCountry
                                    . $shipFromCity
                                    . '<div class="clearfix"></div>'
                                    . $row['ship_from'],
                'dt_ship_to'    => '<div class="pull-left">'
                                    . $shipToCountry
                                    . $shipToCity
                                    . '<div class="clearfix"></div>'
                                    . $row['ship_to'],
            ];
        }

        jsonResponse('', 'success', $output);
    }

    // ALL ORDERS
    public function all()
    {
        if (!have_right('read_all_orders')) {
            show_404();
        }

        /** @var Orders_Model $ordersModel */
        $ordersModel = model(Orders_Model::class);

        views(
            [
                'admin/header_view',
                'admin/order/all_orders/index_view',
                'admin/footer_view',
            ],
            [
                'orders_status' => $ordersModel->get_orders_status(),
                'title'         => 'All orders',
            ]
        );
    }

    // ASSIGNED ORDERS ADMINISTRATION - DT
    public function ajax_admin_all_orders_dt()
    {
        checkIsAjax();
        checkPermisionAjax('read_all_orders');

        $request = request()->request;

        $ordersConditions = array_merge(
            array_filter(
                [
                    'limit'     => $request->getInt('iDisplayStart') . ',' . $request->getInt('iDisplayLength', 10),
                    'date_val'  => 'update',
                    'realUsers' => 1,
                    'sort_by'   => dtOrdering(
                        $request->all(),
                        [
                            'dt_id_order'    => 'id',
                            'dt_buyer'       => 'full_name_bayer',
                            'dt_ship_to'     => 'io.ship_to',
                            'dt_price'       => 'io.price',
                            'dt_create_date' => 'io.order_date',
                            'dt_date'        => 'io.update_date',
                            'dt_status_date' => 'io.status_countdown',
                            'dt_status'      => 'os.position',
                        ],
                        fn ($ordering) => $ordering['column'] . '-' . $ordering['direction']
                    ) ?: null,
                ],
            ),
            dtConditions($request->all(), [
                ['as' => 'keywords',                    'key' => 'keywords',            'type' => 'cleanInput'],
                ['as' => 'status',                      'key' => 'order_status',        'type' => 'cleanInput'],
                ['as' => 'expire_status',               'key' => 'expire_status',       'type' => 'cleanInput'],
                ['as' => 'id_user',                     'key' => 'id_buyer',            'type' => 'int'],
                ['as' => 'id_seller',                   'key' => 'id_seller',           'type' => 'int'],
                ['as' => 'price_from',                  'key' => 'price_from',          'type' => fn ($price) => is_numeric($price) ? $price : null],
                ['as' => 'price_to',                    'key' => 'price_to',            'type' => fn ($price) => is_numeric($price) ? $price : null],
                ['as' => 'ship_to_country',             'key' => 'ship_to_country',     'type' => 'int'],
                ['as' => 'ship_to_city',                'key' => 'ship_to_city',        'type' => 'int'],
                ['as' => 'date_from',                   'key' => 'start_date',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'date_to',                     'key' => 'finish_date',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'cancel_request',              'key' => 'cancel_request',      'type' => fn ($cancelRequestStatus) => in_array($cancelRequestStatus, [1, 2]) ? $cancelRequestStatus : null],
                ['as' => 'dispute_opened',              'key' => 'dispute_opened',      'type' => fn ($disputeStatus) => in_array($disputeStatus, [1, 2]) ? $disputeStatus : null],
                ['as' => 'shipper_type',                'key' => 'id_shipper',          'type' => fn ($shipperId) => empty($shipperId) ? null : 'ep_shipper'],
                ['as' => 'id_shipper',                  'key' => 'id_shipper',          'type' => fn ($shipperId) => $shipperId ?: null],
                ['as' => 'shipper_type',                'key' => 'id_ishipper',         'type' => fn ($iShipperId) => empty($iShipperId) ? null : 'ishipper'],
                ['as' => 'id_shipper',                  'key' => 'id_ishipper',         'type' => fn ($iShipperId) => $iShipperId ?: null],
                ['as' => 'assigned_manager_email',      'key' => 'manager_email',       'type' => 'trim'],
                ['as' => 'realUsers',                   'key' => 'real_users',          'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2],
            ])
        );

        /** @var Orders_Model $ordersModel */
        $ordersModel = model(Orders_Model::class);

        if (2 === $ordersConditions['realUsers']) {
            unset($ordersConditions['realUsers']);
        }

        $orders_count = $ordersModel->get_orders_count($ordersConditions);
        $orders = $ordersModel->get_users_orders($ordersConditions);

        $orders_statuses_count = arrayByKey($ordersModel->get_statuses_counters($ordersConditions), 'status');
        $orders_statuses_count['expire_soon'] = [
            'status'         => 'expire_soon',
            'status_counter' => $ordersModel->get_soon_expire_orders_count($ordersConditions),
        ];
        $orders_statuses_count['expired'] = [
            'status'         => 'expired',
            'status_counter' => $ordersModel->get_expired_orders_count($ordersConditions),
        ];

        $output = [
            'sEcho'                     => $request->getInt('sEcho'),
            'iTotalRecords'             => $orders_count,
            'iTotalDisplayRecords'      => $orders_count,
            'orders_statuses_count'     => $orders_statuses_count,
            'aaData'                    => [],
        ];

        if (empty($orders)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($orders as $item) {
            $id_orders[] = $item['id'];
            $users_list[$item['id_seller']] = $item['id_seller'];
            $users_list[$item['id_buyer']] = $item['id_buyer'];

            if ('ep_shipper' == $item['shipper_type']) {
                $shipper_list[$item['id_shipper']] = $item['id_shipper'];
            } else {
                $ishipper_list[$item['id_shipper']] = $item['id_shipper'];
            }

            $countries_list[$item['ship_to_country']] = $item['ship_to_country'];
            $countries_list[$item['ship_from_country']] = $item['ship_from_country'];

            if ($item['ship_to_state'] > 0) {
                $state_cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            } else {
                $cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            }

            if ($item['ship_from_state'] > 0) {
                $state_cities_list[$item['ship_from_city']] = $item['ship_from_city'];
            } else {
                $cities_list[$item['ship_from_city']] = $item['ship_from_city'];
            }
        }

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        $countries = $countryModel->get_simple_countries(implode(',', $countries_list));

        if (!empty($cities_list)) {
            $cities = $countryModel->get_simple_cities(implode(',', $cities_list));
        }

        if (!empty($state_cities_list)) {
            $state_cities = $countryModel->get_simple_cities_by_state(implode(',', $state_cities_list));
        }

        if (!empty($shipper_list)) {
            /** @var Shippers_Model $shippersModel */
            $shippersModel = model(Shippers_Model::class);

            $shipper_list = $shippersModel->get_shippers(['shippers_list' => implode(',', $shipper_list)]);
            $shipper_list = arrayByKey($shipper_list, 'id_user');
        }

        if (!empty($ishipper_list)) {
            /** @var Ishippers_Model $iShippersModel */
            $iShippersModel = model(Ishippers_Model::class);

            $ishipper_list = $iShippersModel->get_shippers(['shippers_list' => implode(',', $ishipper_list)]);
            $ishipper_list = arrayByKey($ishipper_list, 'id_shipper');
        }

        /** @var User_Bills_Model $userBillsModel */
        $userBillsModel = model(User_Bills_Model::class);

        $bills_counts = $userBillsModel->get_bills_counts_by_order(['id_orders' => implode(',', $id_orders)]);

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $users_info = $userModel->getUsers(['users_list' => implode(',', $users_list), 'additional' => 1, 'company_info' => 1]);
        $users_info = arrayByKey($users_info, 'idu');

        if (!empty($assignedEpManagersIds = array_filter(array_unique(array_column($orders, 'ep_manager'))))) {
            $epManagers = $userModel->getUsers(['users_list' => implode(',', $assignedEpManagersIds)]);
            $epManagers = array_column($epManagers, null, 'idu');
        }

        foreach ($orders as $row) {
            $counts_bill = '--';
            if (!empty($bills_counts[$row['id']])) {
                $counts_bill = ' <div class="tal">
							<div><a class="fancybox fancybox.ajax" data-title="All bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '">All: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_all'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="New bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/init">New: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_init'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Paid bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/paid">Paid: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_paid'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Confirmed bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/confirmed">Confirmed: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_confirmed'] . '</strong></a></div>
							<div><a class="fancybox fancybox.ajax" data-title="Cancelled bills" href="' . __SITE_URL . 'order/popups_order/admin_bills_list/' . $row['id'] . '/unvalidated">Cancelled: <strong class="txt-red">' . $bills_counts[$row['id']]['counter_unvalidated'] . '</strong></a></div>
						</div> ';
            }

            $link = getCompanyURL($users_info[$row['id_seller']], false);

            if ($row['ship_to_state'] > 0) {
                $city_to = $state_cities[$row['ship_to_city']];
            } else {
                $city_to = $cities[$row['ship_to_city']];
            }

            if ($row['ship_from_state'] > 0) {
                $city_from = $state_cities[$row['ship_from_city']];
            } else {
                $city_from = $cities[$row['ship_from_city']];
            }

            if (!in_array($row['status_alias'], ['order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'])) {
                if ('0000-00-00 00:00:00' == $row['status_countdown']) {
                    $row['status_countdown'] = '2000-00-00 00:00:00';
                }

                $expire = "<strong><span class='countdown-dt' data-expire='" . ((strtotime($row['status_countdown']) - time()) * 1000) . "'></span></strong>";
            } else {
                $expire = '<strong class="txt-green">Finished</strong>';
            }

            $problems_btns = '';

            if (0 == $row['cancel_request']) {
                $problems_btns .= '<i class="ep-icon ep-icon_minus-circle txt-gray-light" title="Order cancellation request not initiated"></i>';
            } elseif (1 == $row['cancel_request']) {
                $problems_btns .= '<i class="ep-icon ep-icon_minus-circle txt-red" title="Order cancellation request is initiated"></i>';
            } else {
                $problems_btns .= '<i class="ep-icon ep-icon_minus-circle txt-blue" title="Cancel requests Processed"></i>';
            }

            if (0 == $row['dispute_opened']) {
                $problems_btns .= '<i class="ep-icon ep-icon_low txt-gray-light" title="View dispute"></i>';
            } elseif (1 == $row['dispute_opened']) {
                $problems_btns .= '<a class="display-ib" href="' . __SITE_URL . 'dispute/all/order/' . $row['id'] . '" title="View dispute"><i class="ep-icon ep-icon_low txt-red"></i></a>';
            } else {
                $problems_btns .= '<a class="display-ib" href="' . __SITE_URL . 'dispute/all/order/' . $row['id'] . '" title="View dispute"><i class="ep-icon ep-icon_low txt-blue"></i></a>';
            }

            $shipper = 'Not assigned';
            if ($row['id_shipper']) {
                if ('ep_shipper' == $row['shipper_type']) {
                    $shipper_img_url = getDisplayImageLink(['{ID}' => $shipper_list[$row['id_shipper']]['id'], '{FILE_NAME}' => $shipper_list[$row['id_shipper']]['logo']], 'shippers.main', ['thumb_size' => 1]);
                    $shipper = '<div class="pull-left w-100pr tal">';
                    $shipper .= '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by freight forwarder" data-value="' . $shipper_list[$row['id_shipper']]['id_user'] . '" data-value-text="' . $shipper_list[$row['id_shipper']]['co_name'] . '" data-title="Freight Forwarder" data-name="id_shipper"></a>';
                    $shipper .= '<a class="ep-icon ep-icon_item txt-orange" title="View freight forwarder" target="_blank" href="' . __SITE_URL . 'shipper/' . strForURL($shipper_list[$row['id_shipper']]['co_name'] . ' ' . $shipper_list[$row['id_shipper']]['id']) . '"></a>';
                    $shipper .= '</div>';
                    $shipper .= '<img class="mw-80 mh-40" src="' . $shipper_img_url . '" alt="' . $shipper_list[$row['id_shipper']]['co_name'] . '">';
                    $shipper .= '<br>' . $shipper_list[$row['id_shipper']]['co_name'];
                } else {
                    $shipper = '<div class="pull-left w-100pr tal">';
                    $shipper .= '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by freight forwarder" data-value="' . $row['id_shipper'] . '" data-value-text="' . $ishipper_list[$row['id_shipper']]['shipper_original_name'] . '" data-title="Freight Forwarder" data-name="id_ishipper"></a>';
                    $shipper .= '</div>';
                    $shipper_img_url = __IMG_URL . 'public/img/ishippers_logo/' . $ishipper_list[$row['id_shipper']]['shipper_logo'];
                    $shipper .= '<img class="mw-80 mh-40" src="' . $shipper_img_url . '" alt="' . $ishipper_list[$row['id_shipper']]['shipper_original_name'] . '">';
                    $shipper .= '<br>' . $ishipper_list[$row['id_shipper']]['shipper_original_name'];
                }
            }

            $assignedManager = '';
            if (!empty($row['ep_manager'])) {
                $assignedManager = "{$epManagers[$row['ep_manager']]['fname']} {$epManagers[$row['ep_manager']]['lname']}, {$epManagers[$row['ep_manager']]['email']}";
            }

            $output['aaData'][] = [
                'dt_id_order'   => '<a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $row['id'] . '" class="fancybox.ajax fancybox clearfix" data-title="Order details">' . orderNumber($row['id']) . '</a>'
                                    . '<a class="display-ib" href="#" title="View detail" rel="order_details"><i class="ep-icon ep-icon_plus"></i></a>'
                                    . '<a class="display-ib fancybox fancybox.ajax" href="' . __SITE_URL . 'order/popups_order/admin_order_timeline/' . $row['id'] . '" title="View order timeline" data-title="Order ' . orderNumber($row['id']) . ' - Timeline"><i class="ep-icon ep-icon_clock lh-20 fs-20"></i></a>'
                                    . '<a class="display-ib fancybox fancybox.ajax" href="' . __SITE_URL . 'order_documents/popup_admin_forms/list-envelopes/' . $row['id'] . '" title="View order documents" data-title="Order ' . orderNumber($row['id']) . ' - Documents"><i class="ep-icon ep-icon_items lh-20 fs-20"></i></a>',
                'dt_users'      => '<span><strong>Seller:</strong>'
                                    . '<a class="dt_filter" data-title="Seller" title="Filter by ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value-text="' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value="' . $row['id_seller'] . '" data-name="id_seller">' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '</a>'
                                    . ' </span>'
                                    . '<div>'
                                    . '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . __SITE_URL . $link . '"></a>'
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . ' ' . $row['id_seller']) . '"></a>'
                                    . '</div>'
                                    . '<span><strong>Buyer:</strong> '
                                    . '<a class="dt_filter" data-title="Buyer" title="Filter by ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value-text="' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value="' . $row['id_buyer'] . '" data-name="id_buyer">' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '</a>'
                                    . '</span>'
                                    . '<div>'
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . ' ' . $row['id_buyer']) . '"></a>'
                                    . '</div>',
                'dt_shiper'     => $shipper,
                'dt_bills'      => $counts_bill,
                'dt_price'      => '<p title="total price">Total: $ ' . get_price($row['final_price'], false)
                                    . '<p title="ship price">Ship: $ ' . get_price($row['ship_price'], false) . '</p>',
                'dt_create_date'=> formatDate($row['order_date']),
                'dt_date'       => formatDate($row['update_date']),
                'dt_status_date'=> $expire,
                'dt_problems'   => $problems_btns,
                'dt_status'     => '<span><i class="ep-icon ' . $row['icon'] . ' fs-30"></i><br> ' . $row['status'] . '</span>',
                'dt_manager'    => $assignedManager,
                'dt_ship_from'  => '<div class="pull-left">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship from country" title="Filter by country: ' . $countries[$row['ship_from_country']]['country'] . '" data-value-text="' . $countries[$row['ship_from_country']]['country'] . '" data-value="' . $row['ship_from_country'] . '" data-name="ship_from_country"></a>'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship from city" title="Filter by city: ' . $city_from . '" data-value-text="' . $city_from . '" data-value="' . $row['ship_from_city'] . '" data-name="ship_from_city"></a>'
                                    . '</div>'
                                    . '<div class="clearfix"></div>'
                                    . $row['ship_from'],
                'dt_ship_to'    => '<div class="pull-left">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship to country" title="Filter by country: ' . $countries[$row['ship_to_country']]['country'] . '" data-value-text="' . $countries[$row['ship_to_country']]['country'] . '" data-value="' . $row['ship_to_country'] . '" data-name="ship_to_country"></a>'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Ship to city" title="Filter by city: ' . $city_to . '" data-value-text="' . $city_to . '" data-value="' . $row['ship_to_city'] . '" data-name="ship_to_city"></a>'
                                    . '</div>'
                                    . '<div class="clearfix"></div>'
                                    . $row['ship_to'],
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function admin_not_assigned()
    {
        checkAdmin('manage_content');

        $this->load->model('Orders_model', 'orders');

        $data['orders_status'] = $this->orders->get_orders_status();
        $data['last_order_notassigned_id'] = $this->orders->get_order_notassigned_last_id();

        $data['title'] = 'Not assigned orders';
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/order/not_asigned/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_admin_new_orders_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        if (!logged_in()) {
            jsonDTResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('moderate_content')) {
            jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $this->load->model('User_model', 'user');
        $this->load->model('Orders_model', 'orders');
        $this->load->model('Usergroup_Model', 'ugroup');
        $this->load->model('Country_Model', 'country');

        // $sorting = [
        //     'ep_manager' => 0,
        //     'date_val' => 'update',
        //     'limit' => intVal($_POST['iDisplayStart']) . ',' . intVal($_POST['iDisplayLength']),
        //     'sort_by' => flat_dt_ordering($_POST, [
        //         'dt_buyer'   => 'full_name_bayer',
        //         'dt_ship_to' => 'io.ship_to',
        //         'dt_price'   => 'io.price',
        //         'dt_date'    => 'io.update_date',
        //         'dt_status'  => 'os.status'
        //     ])
        // ];

        // $filters = dtConditions($_POST, [
        //     ['as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput'],
        //     ['as' => 'status', 'key' => 'order_status', 'type' => 'int'],
        //     ['as' => 'expire_status', 'key' => 'expire_status', 'type' => 'cleanInput'],
        //     ['as' => 'id_user', 'key' => 'id_buyer', 'type' => 'int'],
        //     ['as' => 'id_seller', 'key' => 'id_seller', 'type' => 'int'],
        //     ['as' => 'price_from', 'key' => 'price_from', 'type' => 'float'],
        //     ['as' => 'price_to', 'key' => 'price_to', 'type' => 'float'],
        //     ['as' => 'ship_to_country', 'key' => 'ship_to_country', 'type' => 'int'],
        //     ['as' => 'ship_to_city', 'key' => 'ship_to_city', 'type' => 'int'],
        //     ['as' => 'date_from', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        //     ['as' => 'date_to', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        //     ['as' => 'cancel_request', 'key' => 'cancel_request', 'type' => 'cleanInput']
        // ]);

        // $sorting['sort_by'] = empty($sorting['sort_by']) ? ["full_name_bayer-desc"] : $sorting['sort_by'];

        // $params = array_merge($sorting, $filters);

        $params['limit'] = intval(cleanInput($_POST['iDisplayStart'])) . ',' . intval(cleanInput($_POST['iDisplayLength']));
        $params['ep_manager'] = 0;
        $params['date_val'] = 'update';
        $params['realUsers'] = 1;
        $params['sort_by'] = flat_dt_ordering($_POST, [
            'dt_id_order'    => 'io.id',
            'dt_buyer'       => 'io.id_buyer',
            'dt_ship_to'     => 'io.ship_to',
            'dt_price'       => 'io.price',
            'dt_create_date' => 'io.order_date',
            'dt_date'        => 'io.update_date',
            'dt_status_date' => 'io.status_countdown',
            'dt_status'      => 'os.status',
        ]);

        $filters = dtConditions($_POST, [
            ['as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'order_status', 'type' => 'int'],
            ['as' => 'expire_status', 'key' => 'expire_status', 'type' => 'cleanInput'],
            ['as' => 'id_user', 'key' => 'id_buyer', 'type' => 'int'],
            ['as' => 'id_seller', 'key' => 'id_seller', 'type' => 'int'],
            ['as' => 'price_from', 'key' => 'price_from', 'type' => 'float'],
            ['as' => 'price_to', 'key' => 'price_to', 'type' => 'float'],
            ['as' => 'ship_to_country', 'key' => 'ship_to_country', 'type' => 'int'],
            ['as' => 'ship_to_city', 'key' => 'ship_to_city', 'type' => 'int'],
            ['as' => 'date_from',  'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'date_to',  'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'cancel_request', 'key' => 'cancel_request', 'type' => 'cleanInput'],
            ['as' => 'realUsers', 'key' => 'real_users', 'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2],
        ]);

        $params['sort_by'] = empty($params['sort_by']) ? ['io.id_buyer-asc'] : $params['sort_by'];

        $params = array_merge($params, $filters);

        if (2 === $params['realUsers']) {
            unset($params['realUsers']);
        }

        $orders_count = $this->orders->get_orders_count($params);
        $orders = $this->orders->get_users_orders($params);

        $orders_statuses_count = arrayByKey($this->orders->get_statuses_counters($params), 'status');
        $orders_statuses_count['expire_soon'] = [
            'status'         => 'expire_soon',
            'status_counter' => $this->orders->get_soon_expire_orders_count($params),
        ];
        $orders_statuses_count['expired'] = [
            'status'         => 'expired',
            'status_counter' => $this->orders->get_expired_orders_count($params),
        ];

        $output = [
            'sEcho'                 => intval($_POST['sEcho']),
            'iTotalRecords'         => $orders_count,
            'iTotalDisplayRecords'  => $orders_count,
            'orders_statuses_count' => $orders_statuses_count,
            'aaData'                => [],
        ];

        if (empty($orders)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($orders as $item) {
            $id_orders[] = $item['id'];
            $users_list[$item['id_seller']] = $item['id_seller'];
            $users_list[$item['id_buyer']] = $item['id_buyer'];
            $countries_list[$item['ship_to_country']] = $item['ship_to_country'];

            if ($item['ship_to_state'] > 0) {
                $state_cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            } else {
                $cities_list[$item['ship_to_city']] = $item['ship_to_city'];
            }
        }

        $countries = $this->country->get_simple_countries(implode(',', $countries_list));

        if (!empty($cities_list)) {
            $cities = $this->country->get_simple_cities(implode(',', $cities_list));
        }

        if (!empty($state_cities_list)) {
            $state_cities = $this->country->get_simple_cities_by_state(implode(',', $state_cities_list));
        }

        $users_info = $this->user->getUsers(['users_list' => implode(',', $users_list), 'additional' => 1, 'company_info' => 1]);
        $users_info = arrayByKey($users_info, 'idu');

        foreach ($orders as $row) {
            if (!empty($users_info[$row['id_seller']]['index_name'])) {
                $link = $users_info[$row['id_seller']]['index_name'];
            } else {
                $link = 'seller/' . strForURL($users_info[$row['id_seller']]['name_company']) . '-' . $users_info[$row['id_seller']]['id_company'];
            }

            if ($row['ship_to_state'] > 0) {
                $city = $state_cities[$row['ship_to_city']];
            } else {
                $city = $cities[$row['ship_to_city']];
            }

            $problems_btns = '';
            if (0 == $row['cancel_request']) {
                $problems_btns .= '<i class="ep-icon ep-icon_minus-circle txt-gray-light" title="Cancel order requests"></i>';
            } elseif (1 == $row['cancel_request']) {
                $problems_btns .= '<a class="display-ib fancybox fancybox.ajax" data-title="Cancel order requests" title="Cancel order requests" href="' . __SITE_URL . 'order/popups_order/order_cancel_requests/' . $row['id'] . '"><i class="ep-icon ep-icon_minus-circle txt-red"></i></a>';
            } else {
                $problems_btns .= '<a class="display-ib fancybox fancybox.ajax" data-title="Cancel order requests" title="Cancel order requests" href="' . __SITE_URL . 'order/popups_order/order_cancel_requests/' . $row['id'] . '"><i class="ep-icon ep-icon_minus-circle txt-blue"></i></a>';
            }

            $expire_time = strtotime($row['status_countdown']);
            $expire = "<strong><span class='countdown-dt' data-expire='" . (($expire_time - time()) * 1000) . "'></span></strong>";

            // TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $row['id_seller'], 'recipientStatus' => $users_info[$row['id_seller']]['status'], 'module' => 9, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            // TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $row['id_buyer'], 'recipientStatus' => $users_info[$row['id_buyer']]['status'], 'module' => 9, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();

            $output['aaData'][] = [
                'dt_id_order'   => '<a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $row['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($row['id']) . '</a>',
                'dt_seller'     => '<div class="pull-left">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value-text="' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" data-value="' . $row['id_seller'] . '" data-name="id_seller"></a>'
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . ' ' . $row['id_seller']) . '"></a>'
                                    . $btnChatSellerView
                                    . '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . __SITE_URL . $link . '"></a>'
                                    . '</div>'
                                    . '<div class="clearfix"></div>'
                                    . '<span>' . $users_info[$row['id_seller']]['fname'] . ' ' . $users_info[$row['id_seller']]['lname'] . '</span>',
                'dt_buyer'      => '<div class="pull-left">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value-text="' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" data-value="' . $row['id_buyer'] . '" data-name="id_buyer"></a>'
                                    . $btnChatBuyerView
                                    . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . ' ' . $row['id_buyer']) . '"></a>'
                                    . '</div>'
                                    . '<div class="clearfix"></div>'
                                    . '<span>' . $users_info[$row['id_buyer']]['fname'] . ' ' . $users_info[$row['id_buyer']]['lname'] . '</span>',
                'dt_ship_to'    => '<div class="pull-left">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Country" title="Filter by country: ' . $countries[$row['ship_to_country']]['country'] . '" data-value-text="' . $countries[$row['ship_to_country']]['country'] . '" data-value="' . $row['ship_to_country'] . '" data-name="ship_to_country"></a>'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="City" title="Filter by city: ' . $city . '" data-value-text="' . $city . '" data-value="' . $row['ship_to_city'] . '" data-name="ship_to_city"></a>'
                                    . '</div>'
                                    . '<div class="clearfix"></div>'
                                    . $row['ship_to'],
                'dt_price'      => '<p title="total price">Total: ' . $row['total_price']
                                    . '</p><p title="fee price">Fees: ' . $row['fee'] . '</p>'
                                    . '<p title="ship price">Ship: ' . $row['ship_price'] . '</p>',
                'dt_date'       => formatDate($row['update_date']),
                'dt_problems'   => $problems_btns,
                'dt_status_date'=> $expire,
                'dt_status'     => '<span><i class="ep-icon ' . $row['icon'] . ' fs-30"></i><br> ' . $row['status'] . '</span>',
                'dt_actions'    => '<a class="confirm-dialog" data-callback="assign_manager" href="order-' . $row['id'] . '" data-message="' . translate('systmess_confirm_assign_as_manager_to_the_order', null, true) . '" title="Assign me as manager"><i class="ep-icon ep-icon_user-plus "></i></a>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function assign_order()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax(['administrate_orders', 'read_all_orders']);

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);

        $orderId = request()->request->getInt('order');
        if (empty($orderId) || !$productOrdersModel->has($orderId)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $this->_load_main();

        if ($this->orders->order_manager_exist($orderId)) {
            jsonResponse(translate('systmess_error_order_assign_as_manager_already_assigned'));
        }

        // UPDATE ORDER
        $order_detail = $this->orders->get_order($orderId);
        $order_log = [
            'date'    => date('m/d/Y h:i:s A'),
            'user'    => 'EP Manager',
            'message' => 'EP Manager has been assigned to this order.',
        ];
        $update_order = [
            'ep_manager'    => id_session(),
            'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
        ];
        $this->orders->change_order($orderId, $update_order);

        $this->notifier->send(
            (new SystemNotification('order_manager_assigned', [
                '[ORDER_ID]'   => orderNumber($order_detail['id']),
                '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_detail['id']),
                '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
            ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
            ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                $order_detail['id_buyer'],
                $order_detail['id_seller'],
            ])
        );
        if ((int) $order_detail['id_shipper'] > 0 && 'ep_shipper' == $order_detail['shipper_type']) {
            $this->notifier->send(
                (new SystemNotification('order_manager_assigned', [
                    '[ORDER_ID]'   => orderNumber($order_detail['id']),
                    '[ORDER_LINK]' => getUrlForGroup('order/my/order_number/' . $order_detail['id'], 'shipper'),
                    '[LINK]'       => getUrlForGroup('order/my', 'shipper'),
                ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
            );
        }

        jsonResponse(translate('systmess_success_assign_to_order_as_manager'), 'success');
    }

    public function ajax_order_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Invoices_Model', 'invoices');

        $type = $this->uri->segment(3);
        $id_user = privileged_user_id();

        switch ($type) {
            case 'check_new_order_assigned':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $lastId = (int) $_POST['lastId'];
                $order_assigned_count = $this->orders->get_count_new_order_assigned($lastId, id_session());

                if (empty($order_assigned_count)) {
                    jsonResponse();
                }

                jsonResponse(
                    '',
                    'success',
                    [
                        'nr_new' => $order_assigned_count,
                        'lastId' => $this->orders->get_order_assigned_last_id(id_session()),
                    ]
                );

            break;
            case 'check_new_order_notassigned':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $lastId = $_POST['lastId'];
                $order_notassigned_count = $this->orders->get_count_new_order_notassigned($lastId);

                if (empty($order_notassigned_count)) {
                    jsonResponse();
                }

                jsonResponse(
                    '',
                    'success',
                    [
                        'nr_new' => $order_notassigned_count,
                        'lastId' => $this->orders->get_order_notassigned_last_id(),
                    ]
                );

            break;
            case 'confirm_order_paid':
                $id_order = (int) $_POST['order'];
                if (!(have_right('administrate_orders') && $this->orders->isOrderManager($id_order, $id_user))) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $order_detail = $this->orders->get_order($id_order);
                $order_number = orderNumber($order_detail['id']);
                if ('order_paid' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_order_confirm_paid'));
                }

                // UPDATE ORDER LOG
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'EP Manager',
                    'message' => 'The order payment has been confirmed.',
                ];
                $id_status_n = 7;
                $new_status_info = $this->orders->get_status_detail($id_status_n);
                $update_order = [
                    'status'           => $id_status_n,
                    'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                    'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];

                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER AND BUYER
                $this->notifier->send(
                    (new SystemNotification('order_payment_confirmed', [
                        '[ORDER_ID]'   => orderNumber($order_detail['id']),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_detail['id']),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_buyer'],
                        $order_detail['id_seller'],
                    ])
                );

                // NOTIFY SHIPPER
                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->notifier->send(
                        (new SystemNotification('order_payment_confirmed', [
                            '[ORDER_ID]'   => orderNumber($order_detail['id']),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $order_detail['id']),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }

                jsonResponse(translate('systmess_success_order_confirm_payment'), 'success');

            break;
            case 'change_order_status':
                $id_order = (int) $_POST['order'];
                if (!(have_right('administrate_orders') && $this->orders->isOrderManager($id_order, $id_user))) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $order_detail = $this->orders->get_order($id_order);
                $order_number = orderNumber($order_detail['id']);
                switch ($order_detail['status_alias']) {
                    case 'preparing_for_shipping':
                        $order_log = [
                            'date'    => date('m/d/Y h:i:s A'),
                            'user'    => 'EP Manager',
                            'message' => 'The order status has been changed to "Shipping in progress".',
                        ];
                        $id_status_n = 9;
                        $timeline_countdowns = json_decode($order_detail['timeline_countdowns'], true);
                        $update_order = [
                            'status'           => $id_status_n,
                            'status_countdown' => date_plus($timeline_countdowns['delivery_days'], 'days', false, true),
                            'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                        ];
                        $new_status = 'Shipping in progress';

                    break;
                    case 'shipping_in_progress':
                        if (1 == $order_detail['dispute_opened']) {
                            jsonResponse(translate('systmess_error_order_change_status_has_dispute'));
                        }

                        $order_log = [
                            'date'    => date('m/d/Y h:i:s A'),
                            'user'    => 'EP Manager',
                            'message' => 'The order status has been changed to "Ready for pickup".',
                        ];
                        $id_status_n = 18;
                        $new_status_info = $this->orders->get_status_detail($id_status_n);
                        $update_order = [
                            'status'           => $id_status_n,
                            'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                            'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                        ];
                        $new_status = 'Ready for pickup';

                    break;
                    case 'shipping_ready_for_pickup':
                        if (1 == $order_detail['dispute_opened']) {
                            jsonResponse(translate('systmess_error_order_change_status_has_dispute'));
                        }

                        $order_log = [
                            'date'    => date('m/d/Y h:i:s A'),
                            'user'    => 'EP Manager',
                            'message' => 'The order status has been changed to "Shipping completed".',
                        ];
                        $id_status_n = 10;
                        $new_status_info = $this->orders->get_status_detail($id_status_n);
                        $update_order = [
                            'status'           => $id_status_n,
                            'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                            'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                        ];
                        $new_status = 'Shipping completed';

                    break;
                    case 'shipping_completed':
                        $this->load->model('Dispute_Model', 'dispute');
                        if (1 == $order_detail['dispute_opened']) {
                            jsonResponse(translate('systmess_error_order_change_status_has_dispute'));
                        }

                        $order_log = [
                            'date'    => date('m/d/Y h:i:s A'),
                            'user'    => 'EP Manager',
                            'message' => 'The order status has been changed to "Order completed".',
                        ];
                        $id_status_n = 11;
                        $update_order = [
                            'status'        => $id_status_n,
                            'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
                        ];
                        $new_status = 'Order completed';

                        if (empty($order_detail['external_bills'])) {
                            $seller_info = $this->user->getUser($order_detail['id_seller']);
                            $this->load->model('Billing_Model', 'ext_bills');
                            $insert_ext_bill_seller = [];
                            $insert_ext_bill_shipper = [];
                            $seller_amount = $order_detail['final_price'];
                            // CREATE EXTERNAL BILL NOTICE FOR SELLER
                            $comment = 'To pay the seller ' . $seller_info['user_name'] . ' ( ' . $order_detail['id_seller'] . ' ), ' . $seller_info['email'] . '.';
                            if ('ishipper' == $order_detail['shipper_type']) {
                                // CREATE EXTERNAL BILL NOTICE FOR SELLER, SHIPPING WITH INTERNATIONAL SHIPPER
                                $this->load->model('Ishippers_Model', 'ishippers');
                                $shipper_info = $this->ishippers->get_shipper($order_detail['id_shipper']);
                                $comment .= '<br>The amount include the payment for shipping with ' . $shipper_info['shipper_original_name'] . '.';
                                $seller_amount += $order_detail['ship_price'];
                            }
                            $comment .= '<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been completed.';
                            $insert_ext_bill_seller = [
                                'to_user'   => $order_detail['id_seller'],
                                'user_type' => 'seller',
                                'money'     => $seller_amount,
                                'comment'   => $comment,
                                'date_time' => date('Y-m-d H:i:s'),
                                'add_by'    => 'System',
                            ];

                            if ('ep_shipper' == $order_detail['shipper_type']) {
                                // CREATE EXTERNAL BILL NOTICE FOR EP SHIPPER
                                $this->load->model('Shippers_Model', 'shippers');
                                $shipper_info = $this->shippers->get_shipper_by_user($order_detail['id_shipper']);
                                $comment = 'To pay the freight forwarder ' . $shipper_info['co_name'] . ' ( ' . $shipper_info['id'] . ' ), ' . $shipper_info['email'] . '.<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been completed.';
                                $insert_ext_bill_shipper = [
                                    'to_user'   => $order_detail['id_shipper'],
                                    'user_type' => 'shipper',
                                    'money'     => $order_detail['ship_price'],
                                    'comment'   => $comment,
                                    'date_time' => date('Y-m-d H:i:s'),
                                    'add_by'    => 'System',
                                ];
                            }

                            $external_bills = [];
                            if (!empty($insert_ext_bill_seller)) {
                                $external_bills[] = json_encode($insert_ext_bill_seller);
                            }

                            if (!empty($insert_ext_bill_shipper)) {
                                $external_bills[] = json_encode($insert_ext_bill_shipper);
                            }

                            $update_order['external_bills'] = implode(',', $external_bills);
                        }

                    break;
                }

                if (empty($update_order)) {
                    jsonResponse(translate('systmess_error_order_change_status_wrong_current_status'));
                }

                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER AND BUYER
                $this->notifier->send(
                    (new SystemNotification('order_change_status', [
                        '[STATUS]'     => $new_status,
                        '[ORDER_ID]'   => orderNumber($order_detail['id']),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_detail['id']),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_buyer'],
                        $order_detail['id_seller'],
                    ])
                );

                // NOTIFY SHIPPER
                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->notifier->send(
                        (new SystemNotification('order_change_status', [
                            '[STATUS]'     => $new_status,
                            '[ORDER_ID]'   => orderNumber($order_detail['id']),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $order_detail['id']),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }
                jsonResponse(translate('systmess_success_order_change_status'), 'success');

            break;
            case 'start_packaging':
                $id_order = (int) $_POST['order'];
                if (!(have_right('manage_seller_orders') && $this->orders->isMyOrder($id_order, $id_user))) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $order_detail = $this->orders->get_order($id_order);

                if ('payment_confirmed' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_order_start_packaging_wrong_status'));
                }

                // Update Order log
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Seller',
                    'message' => 'Starting preparing item(s) for the shipping.',
                ];
                $id_status_n = 8;
                $timeline_countdowns = json_decode($order_detail['timeline_countdowns'], true);
                $update_order = [
                    'status'           => $id_status_n,
                    'status_countdown' => date_plus($timeline_countdowns['time_for_packaging'], 'days', false, true),
                    'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                $order_number = orderNumber($order_detail['id']);

                // NOTIFY SELLER AND BUYER
                $this->notifier->send(
                    (new SystemNotification('order_start_packaging', [
                        '[ORDER_ID]'   => orderNumber($order_detail['id']),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_detail['id']),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_detail['id_buyer']))->withRoomType(RoomType::CARGO())
                );

                // NOTIFY SHIPPER
                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->notifier->send(
                        (new SystemNotification('order_start_packaging', [
                            '[ORDER_ID]'   => orderNumber($order_detail['id']),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $order_detail['id']),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }

                jsonResponse(translate('systmess_success_order_change_status'), 'success');

            break;
            // ADD CANCEL REQUEST - SELLER & BUYER
            case 'cancel_request':
                // CHECK USER RIGHTS
                if (!have_right('cancel_order_request')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => 'Cancel reason',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'id_order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['id_order'];
                $order_info = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($order_info)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS BUYER OR SELLER ORDER
                if (
                    !is_my($order_info['id_buyer'])
                    && !is_privileged('user', $order_info['id_seller'], true)
                    && !is_privileged('user', $order_info['id_shipper'], true)
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (1 == $order_info['cancel_request']) {
                    $conditions = [
                        'status'   => "'init'",
                        'id_user'  => $id_user,
                        'id_order' => $id_order,
                    ];

                    $count_requests = $this->orders->count_cancel_order_requests($conditions);
                    if ($count_requests > 0) {
                        jsonResponse(translate('systmess_error_add_order_cancel_already_exist_one'));
                    }
                }

                $user_message = cleanInput($_POST['message']);
                if (have_right('buy_item')) {
                    $user_type = 'buyer';
                    $user_log = 'Buyer';
                } elseif (have_right('manage_seller_orders')) {
                    $user_type = 'seller';
                    $user_log = 'Seller';
                } elseif (have_right('manage_shipper_orders')) {
                    $user_type = 'shipper';
                    $user_log = 'Freight Forwarder';
                }

                $insert_request = [
                    'id_order'  => $id_order,
                    'id_user'   => $id_user,
                    'user_type' => $user_type,
                    'message'   => $user_message,
                ];

                $id_request = $this->orders->add_cancel_order_requests($insert_request);
                // Update Order log
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => $user_log,
                    'message' => 'The cancel order request has been added.',
                ];

                $update_order = [
                    'cancel_request' => 1,
                    'order_summary'  => $order_info['order_summary'] . ',' . json_encode($order_log),
                ];

                $this->orders->change_order($id_order, $update_order);
                jsonResponse(translate('systmess_succes_add_order_cancel_request'), 'success', ['order' => $id_order]);

            break;
            // ADD CANCEL REQUEST - EP_MANAGER
            case 'change_cancel_request_status':
                // CHECK USER RIGHTS
                if (!have_right('administrate_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'request',
                        'label' => 'Cancel request info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'status',
                        'label' => 'Status',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_request = (int) $_POST['request'];
                $id_order = (int) $_POST['order'];
                $status = $_POST['status'];

                if (!in_array($status, ['accepted', 'declined'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $request_detail = $this->orders->get_cancel_order_request($id_request);

                if (empty($request_detail)) {
                    jsonResponse(translate('systmess_error_request_does_not_exist'));
                }

                // CHECK IF ORDER EXIST
                $order_info = $this->orders->get_order($id_order);
                if (empty($order_info)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                // UPDATE ORDER LOG
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'EP Manager',
                    'message' => 'The ' . ucfirst($request_detail['user_type']) . ' cancel order request has been ' . $status . '.',
                ];

                $update_order = [
                    'order_summary'  => $order_info['order_summary'] . ',' . json_encode($order_log),
                    'cancel_request' => 2,
                ];

                $this->orders->change_order($id_order, $update_order);

                $this->orders->update_cancel_order_request($id_request, ['status' => $status]);

                $this->notifier->send(
                    (new SystemNotification('change_cancel_request_status', [
                        '[STATUS]'     => $status,
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_info['id']),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $request_detail['id_user']))->withRoomType(RoomType::CARGO())
                );

                jsonResponse(
                    'accepted' == $status
                        ? translate('systmess_success_order_cancellation_request_accepted')
                        : translate('systmess_success_order_cancellation_request_declined'),
                    'success',
                    ['order' => $id_order]
                );

            break;
            case 'add_reason':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'reason',
                        'label' => 'Reason',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['max_len[300]' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (empty($_POST['statuses'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $insert = [
                    'reason'  => cleanInput(request()->input->get('reason')),
                    'message' => cleanInput(request()->input->get('message')),
                ];
                $id_reason = $this->orders->set_reason($insert);

                $relations = [];
                foreach ($_POST['statuses'] as $status) {
                    $relations[] = [
                        'id_reason' => $id_reason,
                        'id_status' => $status,
                    ];
                }
                $total = $this->orders->set_reason_statuses_relation($relations);
                jsonResponse(translate('systmess_success_order_add_cancel_reason'), 'success');

            break;
            case 'edit_reason':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'reason',
                        'label' => 'Reason',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'id_reason',
                        'label' => 'Reason info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['max_len[300]' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (empty($_POST['statuses'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $id_reason = (int) $_POST['id_reason'];
                $update = [
                    'reason'  => cleanInput(request()->request->get('reason')),
                    'message' => cleanInput(request()->request->get('message')),
                ];
                $this->orders->update_reason($id_reason, $update);
                $this->orders->delete_reason_statuses_relation($id_reason);

                $relations = [];
                foreach ($_POST['statuses'] as $status) {
                    $relations[] = [
                        'id_reason' => $id_reason,
                        'id_status' => $status,
                    ];
                }
                $total = $this->orders->set_reason_statuses_relation($relations);
                jsonResponse(translate('systmess_success_order_edit_cancel_reason'), 'success');

            break;
            case 'delete_reason':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'reason',
                        'label' => 'Reason info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_reason = (int) $_POST['reason'];
                $this->orders->delete_reason($id_reason);
                $this->orders->delete_reason_statuses_relation($id_reason);

                jsonResponse(translate('systmess_success_order_delete_cancel_reason'), 'success');

            break;
            // ADD TRACKING INFO - ONLY SELLER
            case 'add_tracking_info':
                if (!have_right('manage_seller_orders') && !have_right('manage_shipper_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'track_info',
                        'label' => 'Tracking info',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $order_detail = $this->orders->get_order($id_order);
                if ('preparing_for_shipping' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_order_add_tracking_info_wrong_status'));
                }

                // Update Order log
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => have_right('manage_seller_orders') ? 'Seller' : 'Freight Forwarder',
                    'message' => 'Tracking information has been updated. The ordered items have been transfered to the shipping company.',
                ];

                $id_status_n = 9;
                $timeline_countdowns = json_decode($order_detail['timeline_countdowns'], true);
                $update_order = [
                    'status'           => $id_status_n,
                    'status_countdown' => date_plus($timeline_countdowns['delivery_days'], 'days', false, true),
                    'tracking_info'    => cleanInput($_POST['track_info']),
                    'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY EP_MANAGER AND BUYER
                $users_systmess = [$order_detail['id_buyer']];
                if (have_right('manage_shipper_orders')) {
                    $users_systmess[] = $order_detail['id_seller'];
                }

                $this->notifier->send(
                    (new SystemNotification('order_tracking_information', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $users_systmess)
                );
                if (!empty($order_detail['ep_manager'] ?? null)) {
                    $this->notifier->send(
                        (new SystemNotification('order_tracking_information', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                        ]))->channels([(string) SystemChannel::STORAGE()]),
                        new Recipient((int) $order_detail['ep_manager'])
                    );
                }

                jsonResponse(translate('systmess_success_order_add_tracking_info'), 'success', ['order' => $order_detail['id']]);

            break;
            // UPDATE TRACKING INFO - ONLY SELLER AND EP ORDER MANAGER
            case 'edit_tracking_info':
                if (!have_right('manage_seller_orders') && !have_right('administrate_orders') && !have_right('manage_shipper_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'track_info',
                        'label' => 'Tracking info',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];

                $order_detail = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($order_detail)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (
                    !is_my($order_detail['ep_manager'])
                    && !is_privileged('user', $order_detail['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $order_detail['id_shipper'], 'manage_shipper_orders')
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ('shipping_in_progress' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_order_add_tracking_info_wrong_status'), 'info');
                }

                $user_log = 'EP Manager';
                if (have_right('manage_seller_orders')) {
                    $user_log = 'Seller';
                } elseif (have_right('manage_shipper_orders')) {
                    $user_log = 'Freight Forwarder';
                }

                // Update Order log
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => $user_log,
                    'message' => 'Tracking information has been updated.',
                ];
                $update_order = [
                    'tracking_info' => cleanInput($_POST['track_info']),
                    'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER AND BUYER
                $this->notifier->send(
                    (new SystemNotification('order_tracking_information', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_seller'],
                        $order_detail['id_buyer'],
                    ])
                );
                // NOTIFY SHIPPER
                if (have_right('manage_shipper_orders')) {
                    $this->notifier->send(
                        (new SystemNotification('order_tracking_information', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }
                // NOTIFY MANAGER
                if (!empty($order_detail['ep_manager'] ?? null)) {
                    $this->notifier->send(
                        (new SystemNotification('order_tracking_information', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                        ]))->channels([(string) SystemChannel::STORAGE()]),
                        new Recipient((int) $order_detail['ep_manager'])
                    );
                }

                jsonResponse(translate('systmess_success_order_edit_tracking_info'), 'success', ['order' => $order_detail['id']]);

            break;
            case 'shipper_confirm_delivery':
                if (!have_right('manage_shipper_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $id_order = intval($_POST['order']);

                $order_detail = $this->orders->get_order($id_order);
                if ($id_user != $order_detail['id_shipper']) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                if ('shipping_ready_for_pickup' != $order_detail['status_alias'] && 0 == (int) $order_detail['shipper_confirm_delivery']) {
                    jsonResponse(translate('systmess_error_order_shipper_confirm_delivery_wrong_status'), 'info');
                }

                // Update Order log
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Freight Forwarder',
                    'message' => 'Confirm delivery.',
                ];

                $update_order = [
                    'shipper_confirm_delivery' => 1,
                    'order_summary'            => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                $this->notifier->send(
                    $notification = (new SystemNotification('order_shipper_confirm_delivery', [
                        '[ORDER_ID]'   => orderNumber($order_detail['id']),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $order_detail['id']),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_buyer'],
                        $order_detail['id_seller'],
                    ])
                );
                $this->notifier->send($notification->channels([(string) SystemChannel::STORAGE()]), new Recipient((int) $order_detail['ep_manager']));

                jsonResponse(translate('systmess_success_order_shipper_confirm_delivery'), 'success', ['order' => $order_detail['id']]);

            break;
            // CONFIRM SHIPPING COMPLETED
            case 'confirm_shipping_complete':
                if (!have_right('buy_item') && !have_right('administrate_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];

                $order_detail = $this->orders->get_order($id_order);
                if (!in_array($id_user, [$order_detail['id_buyer'], $order_detail['ep_manager']])) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Dispute_Model', 'dispute');
                if ($this->dispute->is_disputed_order($id_order, ['id_user' => $id_user, 'order_status' => 18, 'status' => "'init','processing'"])) {
                    jsonResponse(translate('systmess_error_order_buyer_confirm_delivery_has_dispute'));
                }

                // UPDATE ORDER TIMELINE
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => (have_right('buy_item')) ? 'Buyer' : 'EP Manager',
                    'message' => 'The item(s) has been delivered.',
                ];
                $id_status_n = 10;
                $new_status_info = $this->orders->get_status_detail($id_status_n);
                $update_order = [
                    'status'           => $id_status_n,
                    'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                    'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER, BUYER
                $this->notifier->send(
                    $notification = (new SystemNotification('order_buyer_confirmed_delivery', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_seller'],
                        $order_detail['id_buyer'],
                    ])
                );
                // NOTIFY SHIPPER
                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->notifier->send(
                        (new SystemNotification('order_buyer_confirmed_delivery', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }
                // Notify manager
                $this->notifier->send($notification->channels([(string) SystemChannel::STORAGE()]), new Recipient((int) $order_detail['ep_manager']));

                jsonResponse(translate('systmess_success_order_buyer_confirm_shipping'), 'success');

            break;
            // CONFIRM READY PICKUP
            case 'confirm_extend':
                $id_order = (int) $_POST['order'];

                $order_detail = $this->orders->get_order($id_order);
                if (!in_array($id_user, [$order_detail['id_shipper'], $order_detail['id_seller'], $order_detail['id_buyer']])) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Auto_Extend_model', 'auto_extend');

                $extend_info = $this->auto_extend->get_extend_request_by_order($id_order);

                if (empty($extend_info)) {
                    jsonResponse(translate('systmess_error_request_does_not_exist'));
                }

                if (
                    (have_right('manage_seller_orders') && 'approved' == $extend_info['status_seller'])
                    || (have_right('buy_item') && 'approved' == $extend_info['status_buyer'])
                    || (have_right('manage_shipper_orders') && 'approved' == $extend_info['status_shipper'])
                ) {
                    jsonResponse(translate('systmess_error_order_confirm_extend_already_confirmed'));
                }

                $update_extend_info = [];

                // UPDATE ORDER TIMELINE
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'message' => 'The extend time was accepted.',
                ];

                if (have_right('buy_item')) {
                    $update_extend_info['status_buyer'] = 'approved';
                    $order_log['user'] = 'Buyer';
                } elseif (have_right('manage_seller_orders')) {
                    $update_extend_info['status_seller'] = 'approved';
                    $order_log['user'] = 'Seller';
                } elseif (have_right('manage_shipper_orders')) {
                    $update_extend_info['status_shipper'] = 'approved';
                    $order_log['user'] = 'Freight Forwarder';
                }

                $update_order = [
                    'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];

                $this->auto_extend->update_extend_request($extend_info['id_auto_extend'], $update_extend_info);
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER, BUYER, SHIPPER
                $this->notifier->send(
                    $notification = (new SystemNotification('confirm_auto_extend', [
                        '[USER]'       => $order_log['user'],
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_seller'],
                        $order_detail['id_buyer'],
                    ])
                );
                // NOTIFY SHIPPER
                if ('ep_shipper' === $order_detail['shipper_type'] && (int) $order_detail['id_shipper'] > 0) {
                    $this->notifier->send(
                        (new SystemNotification('confirm_auto_extend', [
                            '[USER]'       => $order_log['user'],
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }
                // Notify manager
                if (!empty($order_detail['ep_manager'])) {
                    $this->notifier->send($notification->channels([(string) SystemChannel::STORAGE()]), new Recipient((int) $order_detail['ep_manager']));
                }

                jsonResponse(translate('systmess_success_order_confirm_extend'), 'success');

            break;
            case 'confirm_ready_pickup':
                checkPermisionAjax('manage_seller_orders,administrate_orders,manage_shipper_orders');

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'pickup_date',
                        'label' => 'Date for pick-up',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];
                $pickup_date = cleanInput($_POST['pickup_date']);

                $order_detail = $this->orders->get_order($id_order);
                if (!in_array($id_user, [$order_detail['id_seller'], $order_detail['id_shipper'], $order_detail['ep_manager']])) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Dispute_Model', 'dispute');
                if ($this->dispute->is_disputed_order($id_order, ['id_user' => $id_user, 'order_status' => 9, 'status' => "'init','processing'"])) {
                    jsonResponse(translate('systmess_error_order_confirm_ready_for_pickup_has_dispute'));
                }

                if (strtotime(date('m/d/Y')) >= strtotime($pickup_date)) {
                    jsonResponse(translate('systmess_error_order_confirm_ready_for_pickup_wrong_date'));
                }

                // UPDATE ORDER TIMELINE

                $user_log = 'EP Manager';
                if (have_right('manage_seller_orders')) {
                    $user_log = 'Seller';
                } elseif (have_right('manage_shipper_orders')) {
                    $user_log = 'Freight Forwarder';
                }

                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => $user_log,
                    'message' => 'The pickup due date has been added.',
                ];
                $id_status_n = 18;
                $new_status_info = $this->orders->get_status_detail($id_status_n);
                $update_order = [
                    'status'           => $id_status_n,
                    'status_countdown' => date('Y-m-d H:i:s', strtotime($pickup_date . ' 23:59:59')),
                    'order_summary'    => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER, BUYER, EP MANAGER
                $this->notifier->send(
                    $notification = (new SystemNotification('order_ready_for_pickup', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_seller'],
                        $order_detail['id_buyer'],
                    ])
                );
                // NOTIFY SHIPPER
                if (have_right('manage_shipper_orders') && !empty((int) $order_detail['id_shipper'] ?? null)) {
                    $this->notifier->send(
                        (new SystemNotification('order_ready_for_pickup', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }
                // Notify manager
                $this->notifier->send($notification->channels([(string) SystemChannel::STORAGE()]), new Recipient((int) $order_detail['ep_manager']));

                jsonResponse(translate('systmess_success_order_add_pickup_date'), 'success');

            break;
            // CONFIRM ORDER COMPLETED
            case 'confirm_order_completed':
                if (!have_right('buy_item')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];

                $order_detail = $this->orders->get_order($id_order);
                if (!in_array($id_user, [$order_detail['id_buyer']])) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Dispute_Model', 'dispute');
                if ($this->dispute->is_disputed_order($id_order, ['id_user' => $id_user, 'order_status' => 10, 'status' => "'init','processing'"])) {
                    jsonResponse(translate('systmess_error_order_confirm_has_disput'));
                }

                // UPDATE ORDER LOG
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Buyer',
                    'message' => 'The order has been completed successfully.',
                ];
                $id_status_n = 11;
                $new_status_info = $this->orders->get_status_detail($id_status_n);
                $update_order = [
                    'status'        => $id_status_n,
                    'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
                ];

                if (empty($order_detail['external_bills'])) {
                    $seller_info = $this->user->getUser($order_detail['id_seller']);
                    $this->load->model('Billing_Model', 'ext_bills');
                    $insert_ext_bill_seller = [];
                    $insert_ext_bill_shipper = [];

                    $seller_amount = $order_detail['final_price'];
                    // CREATE EXTERNAL BILL NOTICE FOR SELLER
                    $comment = 'To pay the seller ' . $seller_info['user_name'] . ' ( ' . $order_detail['id_seller'] . ' ), ' . $seller_info['email'] . '.';
                    if ('ishipper' == $order_detail['shipper_type']) {
                        // CREATE EXTERNAL BILL NOTICE FOR SELLER, SHIPPING WITH INTERNATIONAL SHIPPER
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $shipper_info = $this->ishippers->get_shipper($order_detail['id_shipper']);
                        $comment .= '<br>The amount include the payment for shipping with ' . $shipper_info['shipper_original_name'] . '.';
                        $seller_amount += $order_detail['ship_price'];
                    }
                    $comment .= '<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been completed.';
                    $insert_ext_bill_seller = [
                        'to_user'   => $order_detail['id_seller'],
                        'user_type' => 'seller',
                        'money'     => $seller_amount,
                        'comment'   => $comment,
                        'date_time' => date('Y-m-d H:i:s'),
                        'add_by'    => 'System',
                    ];

                    if ('ep_shipper' == $order_detail['shipper_type']) {
                        // CREATE EXTERNAL BILL NOTICE FOR EP SHIPPER
                        $this->load->model('Shippers_Model', 'shippers');
                        $shipper_info = $this->shippers->get_shipper_by_user($order_detail['id_shipper']);
                        $comment = 'To pay the freight forwarder ' . $shipper_info['co_name'] . ' ( ' . $shipper_info['id'] . ' ), ' . $shipper_info['email'] . '.<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been completed.';
                        $insert_ext_bill_shipper = [
                            'to_user'   => $order_detail['id_shipper'],
                            'user_type' => 'shipper',
                            'money'     => $order_detail['ship_price'],
                            'comment'   => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'add_by'    => 'System',
                        ];
                    }

                    $external_bills = [];
                    if (!empty($insert_ext_bill_seller)) {
                        $external_bills[] = json_encode($insert_ext_bill_seller);
                    }

                    if (!empty($insert_ext_bill_shipper)) {
                        $external_bills[] = json_encode($insert_ext_bill_shipper);
                    }

                    $update_order['external_bills'] = implode(',', $external_bills);
                }
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY SELLER
                $this->notifier->send(
                    $notification = (new SystemNotification('order_completed', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_detail['id_seller']))->withRoomType(RoomType::CARGO())
                );
                // Notify manager
                $this->notifier->send($notification->channels([(string) SystemChannel::STORAGE()]), new Recipient((int) $order_detail['ep_manager']));
                // NOTIFY SHIPPER
                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->notifier->send(
                        (new SystemNotification('order_completed', [
                            '[ORDER_ID]'   => orderNumber($id_order),
                            '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                            '[LINK]'       => sprintf('%sorder/my', __SHIPPER_URL),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $order_detail['id_shipper']))->withRoomType(RoomType::CARGO())
                    );
                }

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic([
                    $order_detail['id_seller'] => ['orders_finished' => 1, 'orders_active' => -1],
                    $order_detail['id_buyer']  => ['orders_finished' => 1, 'orders_active' => -1],
                ]);

                if ('ep_shipper' == $order_detail['shipper_type']) {
                    $this->statistic->set_users_statistic([
                        $order_detail['id_shipper'] => ['orders_finished' => 1, 'orders_active' => -1],
                    ]);
                }

                jsonResponse(translate('systmess_success_order_confirmed'), 'success');

            break;
            // ADD SHIPPING FROM ADDRESS - SELLER, STAFF USERS
            case 'ship_from':
                // CHECK USER RIGHTS - MUST BE SELLER OR STAFF USER OF SELLER
                if (!have_right('manage_seller_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Country_Model', 'country');
                // VALIDATE POST DATA
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order details',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'port_country',
                        'label' => 'Country',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'states',
                        'label' => 'State or province',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'port_city',
                        'label' => 'City',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'zip',
                        'label' => 'ZIP',
                        'rules' => ['required' => '', 'zip_code' => '', 'max_len[20]' => ''],
                    ],
                    [
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'packaging',
                        'label' => 'Estimate time for packaging',
                        'rules' => ['required' => '', 'integer' => '', 'min[1]' => '', 'max[180]' => ''],
                    ],
                    [
                        'field' => 'delivery_area',
                        'label' => 'Available area for delivering',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => '', 'max[40000]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = intval($_POST['order']);
                $order_detail = $this->orders->get_order($id_order);

                // CHECK PERMITIONS
                if (!is_privileged('user', $order_detail['id_seller'], true)) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                if ($order_detail['quote_requested']) {
                    jsonResponse(translate('systmess_error_order_ship_from_already_sent_shipping_rate_request'));
                }

                $order_number = orderNumber($order_detail['id']);

                // PREPARING SHIPPING FROM LOCATION ADDRESS
                $location = [];
                $country = (int) $_POST['port_country'];
                $city = (int) $_POST['port_city'];
                $location = $this->country->get_country_state_city($city);
                $location[] = cleanInput($_POST['zip']);
                $location[] = cleanInput($_POST['address']);
                $ship_from = implode(', ', $location);

                $update = [
                    'ship_from'            => $ship_from,
                    'ship_from_country'    => $country,
                    'ship_from_state'      => intval($_POST['states']),
                    'ship_from_city'       => $city,
                    'ship_from_zip'        => cleanInput($_POST['zip']),
                    'ship_from_address'    => cleanInput($_POST['address']),
                    'seller_delivery_area' => (int) $_POST['delivery_area'],
                ];

                // UPDATE ORDER LOG
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Seller',
                    'message' => 'The shiping from address has been added.',
                ];

                // UPDATE ORDER SUMMARY
                $update['order_summary'] = $order_detail['order_summary'] . ',' . json_encode($order_log);

                // UPDATE ORDER SEARCH INFO
                $update['search_info'] = $order_detail['search_info'] . ',' . $ship_from;

                // SET ESTIMATE TIME FOR PACKAGING
                if (!empty($order_detail['timeline_countdowns'])) {
                    $timeline_countdowns = json_decode($order_detail['timeline_countdowns'], true);
                    $timeline_countdowns['time_for_packaging'] = intval($_POST['packaging']);
                } else {
                    $timeline_countdowns['time_for_packaging'] = intval($_POST['packaging']);
                }
                $update['timeline_countdowns'] = json_encode($timeline_countdowns);

                // UPDATE ORDER SHIPPING FROM ADDRESS DETAIL
                $this->orders->change_order($id_order, $update);
                $resp = ['id_order' => $id_order];
                jsonResponse(translate('systmess_success_order_ship_from'), 'success', $resp);

            break;
            // ADD SHIPPING TO ADDRESS - BUYER
            case 'ship_to':
                // CHECK USER RIGHTS - MUST BE BUYER ONLY
                if (!have_right('buy_item')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('Country_Model', 'country');
                // VALIDATE POST DATA
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order details',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'port_country',
                        'label' => 'Country',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'states',
                        'label' => 'State or province',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'port_city',
                        'label' => 'City',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'zip',
                        'label' => 'ZIP',
                        'rules' => ['required' => '', 'zip_code' => '', 'max_len[20]' => ''],
                    ],
                    [
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = intval($_POST['order']);
                $order_detail = $this->orders->get_order($id_order);

                // CHECK IF EXIST ORDER
                if (empty($order_detail)) {
                    jsonResponse(translate('systmess_error_order_doesnt_exist'));
                }

                // CHECK PERMITIONS
                if (!is_my($order_detail['id_buyer'])) {
                    jsonResponse(translate('systmess_error_order_doesnt_exist'));
                }

                if (!empty($order_detail['ship_to'])) {
                    jsonResponse(translate('systmess_error_order_ship_to_already_selected'));
                }

                if ('new_order' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_order_ship_to_wrong_status'));
                }

                // PREPARING SHIPPING TO LOCATION ADDRESS
                $location = [];

                $country_city = $this->country->get_country_city($_POST['port_country'], $_POST['port_city']);

                if (!empty($country_city['country'])) {
                    $location[] = $country_city['country'];
                }

                if (!empty($country_city['city'])) {
                    $location[] = $country_city['city'];
                }

                $location[] = cleanInput($_POST['zip']);
                $location[] = cleanInput($_POST['address']);
                $ship_to = implode(', ', $location);

                $update = [
                    'ship_to'         => $ship_to,
                    'ship_to_country' => intval($_POST['port_country']),
                    'ship_to_state'   => intval($_POST['states']),
                    'ship_to_city'    => intval($_POST['port_city']),
                    'ship_to_zip'     => cleanInput($_POST['zip']),
                    'ship_to_address' => cleanInput($_POST['address']),
                ];

                // UPDATE ORDER LOG
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Buyer',
                    'message' => 'The shiping to address has been added.',
                ];

                // UPDATE ORDER SUMMARY
                $update['order_summary'] = $order_detail['order_summary'] . ',' . json_encode($order_log);

                // UPDATE ORDER SEARCH INFO
                $update['search_info'] = $order_detail['search_info'] . ',' . $ship_to;

                // UPDATE ORDER
                if (!$this->orders->change_order($id_order, $update)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_order_add_ship_to'), 'success', ['id_order' => $id_order]);

            break;
            case 'cancel_order':
                // CHECK USER RIGHTS - MUST BE ADMIN ONLY
                if (!have_right('administrate_orders')) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                // VALIDATE POST DATA
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'reason',
                        'label' => 'Reason',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'reason_mess',
                        'label' => 'Comment',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'status',
                        'label' => 'Cancel status',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'order',
                        'label' => 'Order details',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                if (isset($_POST['external_bill_buyer'])) {
                    $validator_rules[] = [
                        'field' => 'external_bill_buyer_amount',
                        'label' => 'Amount to refund the buyer',
                        'rules' => ['required' => ''],
                    ];
                }

                if (isset($_POST['external_bill_seller'])) {
                    $validator_rules[] = [
                        'field' => 'external_bill_seller_amount',
                        'label' => 'Amount to pay the seller',
                        'rules' => ['required' => ''],
                    ];
                }

                if (isset($_POST['external_bill_shipper'])) {
                    $validator_rules[] = [
                        'field' => 'external_bill_shipper_amount',
                        'label' => 'Amount to pay the freight forwarder',
                        'rules' => ['required' => ''],
                    ];
                }

                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_order = (int) $_POST['order'];
                $id_reason = (int) $_POST['reason'];
                $id_status = (int) $_POST['status'];
                $order_detail = $this->orders->get_order($id_order);
                if (empty($order_detail)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (1 == $order_detail['cancel_request']) {
                    jsonResponse(translate('systmess_error_order_cancel_order_has_cancel_request'));
                }

                if (1 == $order_detail['dispute_opened']) {
                    jsonResponse(translate('systmess_error_order_cancel_order_has_opened_dispute'));
                }

                $this->load->model('User_Bills_Model', 'user_bills');
                if (in_array($order_detail['status_alias'], ['shipper_assigned', 'payment_processing'])) {
                    $count_bills = $this->user_bills->count_bills_by_item($order_detail['id_buyer'], $id_order, "'paid','init'", '1,2');
                    if ($count_bills > 0) {
                        jsonResponse(translate('systmess_error_order_cancel_order_has_unvalidated_bills'), 'info');
                    }
                }

                $reason = $this->orders->get_reason($id_reason);
                $action_date = date('m/d/Y H:i:s');
                $pre_reason = '';

                if ((bool) $order_detail['last_extend']) {
                    $pre_reason = 'Time expired - ';
                }

                $users_list = [$order_detail['id_seller'], $order_detail['id_buyer']];
                $users_info = $this->user->get_simple_users(['users_list' => implode(',', $users_list)]);
                $users_info = array_column($users_info, null, 'idu');

                $sendEmailParams = [
                    'users'       => [
                        'buyer'  => $users_info[$order_detail['id_buyer']],
                        'seller' => $users_info[$order_detail['id_seller']],
                    ],
                    'orderNumber' => orderNumber($id_order),
                    'reason'      => $reason['reason'],
                    'ordersLink'  => __SITE_URL . 'order/my/order_number/' . $id_order,
                    'idSeller'    => $order_detail['id_seller'],
                ];

                switch ($id_status) {
                    case 14:
                        $systemssCode = 'order_canceled_seller';
                        $userCancelled = 'Seller';

                        $this->sendEmailCancelledBySeller($sendEmailParams);

                        break;
                    case 13:
                        $systemssCode = 'order_canceled_buyer';
                        $userCancelled = 'Buyer';

                        $this->sendEmailCancelledByBuyer($sendEmailParams);

                        break;

                    default:
                        $systemssCode = 'order_canceled_manager';
                        $userCancelled = 'EP Manager';

                        $this->sendEmailCancelledByManager($sendEmailParams);
                }

                $order_log = [
                    'date'    => formatDate($action_date, 'm/d/Y H:i:s'),
                    'user'    => $userCancelled,
                    'message' => 'The order has been canceled.<br><strong>Reason: </strong>' . $pre_reason . $reason['reason'] . '.<br><strong>Comment: </strong>' . cleanInput($_POST['reason_mess']),
                ];

                $update_order = [
                    'status'        => $id_status,
                    'order_summary' => $order_detail['order_summary'] . ',' . json_encode($order_log),
                    'reason'        => $pre_reason . $reason['reason'],
                ];

                if ($this->orders->count_cancel_order_requests(['id_order' => $id_order])) {
                    $update_order['cancel_request'] = 2;
                }

                $this->notifier->send(
                    (new SystemNotification($systemssCode, [
                        '[ORDER_ID]'   => $order_number = orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), $users_list)
                );

                if (in_array($order_detail['status_alias'], ['payment_processing', 'order_paid', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress', 'shipping_completed'])) {
                    $this->load->model('Billing_Model', 'ext_bills');
                    $insert_ext_bill_buyer = [];
                    $insert_ext_bill_seller = [];
                    $insert_ext_bill_shipper = [];
                    if (isset($_POST['external_bill_buyer'])) {
                        $buyer_amount = floatval($_POST['external_bill_buyer_amount']);
                        $comment = 'To refund the buyer ' . $users_info[$order_detail['id_buyer']]['fname'] . ' ' . $users_info[$order_detail['id_buyer']]['lname'] . ' ( ' . $order_detail['id_buyer'] . ' ), ' . $users_info[$order_detail['id_buyer']]['email'] . '.<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been canceled.';

                        $insert_ext_bill_buyer = [
                            'to_user'   => $order_detail['id_buyer'],
                            'user_type' => 'buyer',
                            'money'     => $buyer_amount,
                            'comment'   => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'add_by'    => 'EP Manager - ' . user_name_session(),
                        ];
                    }

                    if (isset($_POST['external_bill_seller'])) {
                        $seller_amount = floatval($_POST['external_bill_seller_amount']);
                        if ($seller_amount > 0) {
                            $comment = 'To pay the seller ' . $users_info[$order_detail['id_seller']]['fname'] . ' ' . $users_info[$order_detail['id_seller']]['lname'] . ' ( ' . $order_detail['id_seller'] . ' ), ' . $users_info[$order_detail['id_seller']]['email'] . '.';
                            if ('ishipper' == $order_detail['shipper_type']) {
                                // CREATE EXTERNAL BILL NOTICE FOR SELLER, SHIPPING WITH INTERNATIONAL SHIPPER
                                $this->load->model('Ishippers_Model', 'ishippers');
                                $shipper_info = $this->ishippers->get_shipper($order_detail['id_shipper']);
                                $comment .= '<br>The payment include amount for shipping with ' . $shipper_info['shipper_original_name'] . '.';
                            }
                        } else {
                            $comment = '<strong class="txt-red">To request from the seller ' . $users_info[$order_detail['id_seller']]['fname'] . ' ' . $users_info[$order_detail['id_seller']]['lname'] . ' ( ' . $order_detail['id_seller'] . ' ), ' . $users_info[$order_detail['id_seller']]['email'] . '.</strong>';
                        }
                        $comment .= '<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been canceled.';

                        $insert_ext_bill_seller = [
                            'to_user'   => $order_detail['id_seller'],
                            'user_type' => 'seller',
                            'money'     => $seller_amount,
                            'comment'   => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'add_by'    => 'EP Manager - ' . user_name_session(),
                        ];
                    }

                    if (isset($_POST['external_bill_shipper']) && 'ep_shipper' == $order_detail['shipper_type']) {
                        $this->load->model('Shippers_Model', 'shippers');
                        $shipper_amount = floatval($_POST['external_bill_shipper_amount']);
                        $shipper_info = $this->shippers->get_shipper_by_user($order_detail['id_shipper']);
                        $comment = 'To pay the freight forwarder ' . $shipper_info['co_name'] . ' ( ' . $shipper_info['id'] . ' ), ' . $shipper_info['email'] . '.<br>The order <a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $order_detail['id'] . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($order_detail['id']) . '</a> has been canceled.';

                        $insert_ext_bill_shipper = [
                            'to_user'   => $order_detail['id_shipper'],
                            'user_type' => 'shipper',
                            'money'     => $shipper_amount,
                            'comment'   => $comment,
                            'date_time' => date('Y-m-d H:i:s'),
                            'add_by'    => 'EP Manager - ' . user_name_session(),
                        ];
                    }

                    $external_bills = [];
                    if (!empty($insert_ext_bill_buyer)) {
                        $external_bills[] = json_encode($insert_ext_bill_buyer);
                    }

                    if (!empty($insert_ext_bill_seller)) {
                        $external_bills[] = json_encode($insert_ext_bill_seller);
                    }

                    if (!empty($insert_ext_bill_shipper)) {
                        $external_bills[] = json_encode($insert_ext_bill_shipper);
                    }

                    if (!empty($external_bills)) {
                        if (empty($order_detail['external_bills'])) {
                            $update_order['external_bills'] = implode(',', $external_bills);
                        } else {
                            $update_order['external_bills'] = $order_detail['external_bills'] . ',' . implode(',', $external_bills);
                        }
                    }
                }
                $this->orders->change_order($id_order, $update_order);

                $statistic = [
                    $order_detail['id_buyer']  => ['orders_canceled' => 1, 'orders_active' => -1],
                    $order_detail['id_seller'] => ['orders_canceled' => 1, 'orders_active' => -1],
                ];

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic($statistic);
                jsonResponse(translate('systmess_success_order_cancel_order'), 'success');

            break;
            case 'assign_shipper':
                checkPermisionAjax('buy_item');

                // VALIDATE POST DATA
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order details',
                        'rules' => ['required' => '', 'is_natural_no_zero' => ''],
                    ],
                    [
                        'field' => 'shipping_quote_type',
                        'label' => 'Shipping quote type',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'shipping_quote',
                        'label' => 'Shipping quote',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = privileged_user_id();
                $id_order = (int) $_POST['order'];
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    jsonResponse(translate('systmess_error_invalid_data') . ' Code #1');
                }

                // GET ORDER DETAILS
                $order_detail = $this->orders->get_order($id_order);
                // CHECK IF ORDER INVOICE HAS BEEN CONFIRMED BY BUYER
                if ('invoice_confirmed' != $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_invalid_data') . ' Code #2');
                }

                // CHECK IF SHIPPER WAS ALLREADY ASSIGNED
                if ('shipper_assigned' == $order_detail['status_alias']) {
                    jsonResponse(translate('systmess_error_invalid_data') . ' Code #3');
                }

                // CLEAR ALL DATA - FOR SECURITY REASON
                $data = [];

                $shipping_insurance_accepted = 0;
                $shipping_insurance_details = [];
                $shipping_quote_details = [];
                $order_number = orderNumber($id_order);
                $current_date = date('Y-m-d H:i:s');

                $shipping_quote = (int) $_POST['shipping_quote'];
                $shipper_type = cleanInput($_POST['shipping_quote_type']);
                switch ($shipper_type) {
                    case 'ishipper':
                        $ishippers_quotes = !empty($order_detail['ishippers_quotes']) ? json_decode($order_detail['ishippers_quotes'], true) : [];
                        if (!array_key_exists($shipping_quote, $ishippers_quotes)) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.1.1');
                        }

                        $shipper_info = model('ishippers')->get_shipper($shipping_quote);
                        if (empty($shipper_info)) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.1.3');
                        }

                        $data['shipper_info'] = [
                            'co_name'  => $shipper_info['shipper_original_name'],
                            'contacts' => $shipper_info['shipper_contacts'],
                        ];

                        $base_shipping_price = $shipping_price = priceToUsdMoney($ishippers_quotes[$shipping_quote]['amount']);
                        $delivery_time = (int) $ishippers_quotes[$shipping_quote]['delivery_to'];

                        $insurance_option = isset($_POST['insurance_option']) && 'no' != $_POST['insurance_option'] ? (int) $_POST['insurance_option'] : null;
                        if (null !== $insurance_option) {
                            if (!isset($ishippers_quotes[$shipping_quote]['insurance_options'][$insurance_option])) {
                                jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.1.2');
                            }

                            $shipping_insurance_accepted = 1;
                            $shipping_insurance_details = $ishippers_quotes[$shipping_quote]['insurance_options'][$insurance_option];
                            $shipping_price = $shipping_price->add(priceToUsdMoney($shipping_insurance_details['amount']));
                        }

                        $id_shipper = $shipping_quote;
                        $shipping_quote_details = [
                            'id_quote'            => $shipping_quote,
                            'type'                => $shipper_type,
                            'delivery_from_days'  => (int) $ishippers_quotes[$shipping_quote]['delivery_from'],
                            'delivery_to_days'    => (int) $ishippers_quotes[$shipping_quote]['delivery_to'],
                            'amount'              => moneyToDecimal($base_shipping_price),
                            'shipment_type'       => $ishippers_quotes[$shipping_quote]['shipment_type'],
                            'shipment_conditions' => '',
                        ];

                    break;
                    case 'ep_shipper':
                        $this->load->model('Shippers_Model', 'shippers');
                        $request_details = $this->shippers->get_order_quote_request($shipping_quote);
                        if (empty($request_details)) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.2.1');
                        }

                        if ('awaiting' != $request_details['quote_status']) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.2.2');
                        }

                        if ($request_details['id_order'] != $order_detail['id']) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.2.3');
                        }

                        $id_shipper = $request_details['id_shipper'];
                        $data['shipper_info'] = $this->shippers->get_shipper_by_user($id_shipper);
                        if (empty($data['shipper_info'])) {
                            jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.2.4');
                        }

                        /** @var Shipping_Types_Model $shippingTypesModel */
                        $shippingTypesModel = model(Shipping_Types_Model::class);
                        $shipping_type = $shippingTypesModel->findOne((int) $order_detail['shipment_type'], ['conditions' => ['isVisible' => 1]]);

                        $base_shipping_price = $shipping_price = priceToUsdMoney($request_details['shipping_price']);
                        $delivery_time = (int) $request_details['delivery_days_to'];

                        $insurance_option = isset($_POST['insurance_option']) && 'no' != $_POST['insurance_option'] ? (int) $_POST['insurance_option'] : null;
                        $insurance_options = !empty($request_details['insurance_options']) ? json_decode($request_details['insurance_options'], true) : [];
                        if (null !== $insurance_option) {
                            if (!isset($insurance_options[$insurance_option])) {
                                jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.2.5');
                            }

                            $shipping_insurance_accepted = 1;
                            $shipping_insurance_details = $insurance_options[$insurance_option];
                            $shipping_price = $shipping_price->add(priceToUsdMoney($shipping_insurance_details['amount']));
                        }

                        $shipping_quote_details = [
                            'id_quote'            => $shipping_quote,
                            'type'                => $shipper_type,
                            'delivery_from_days'  => (int) $request_details['delivery_days_from'],
                            'delivery_to_days'    => (int) $request_details['delivery_days_to'],
                            'amount'              => moneyToDecimal($base_shipping_price),
                            'shipment_type'       => $shipping_type['type_name'],
                            'shipment_conditions' => $request_details['comment_shipper'],
                        ];

                        $this->shippers->update_order_quote_request(['quote_status' => 'declined'], [
                            'id_order'     => $request_details['id_order'],
                            'not_id_quote' => $request_details['id_quote'],
                        ]);

                        $this->shippers->update_order_quote_request(['quote_status' => 'confirmed'], [
                            'id_order' => $request_details['id_order'],
                            'id_quote' => $request_details['id_quote'],
                        ]);

                        $this->notifier->send(
                            (new SystemNotification('shipping_quote_accepted', [
                                '[SHIPPING_REQUESTS_LINK]' => getUrlForGroup("orders_bids/my/bid/{$request_details['id_quote']}", 'shipper'),
                                '[SHIPPING_REQUESTS_ID]'   => orderNumber($request_details['id_quote']),
                                '[ORDER_LINK]'             => getUrlForGroup("order/my/order_number/{$request_details['id_order']}", 'shipper'),
                                '[ORDER_ID]'               => $order_number,
                                '[LINK]'                   => getUrlForGroup('orders_bids/my', 'shipper'),
                            ]))->channels([(string) SystemChannel::STORAGE()]),
                            (new Recipient($request_details['id_shipper']))->withRoomType(RoomType::CARGO())
                        );

                    break;

                    default:
                        jsonResponse(translate('systmess_error_invalid_data') . ' Code #4.3');

                    break;
                }

                $this->load->model('User_Bills_Model', 'user_bills');
                // CREATING THE BILLS FOR ORDER
                $insert_bills = [];
                $billing_due_date = date_plus(config('default_bill_period', 7));
                if ($shipping_price->isZero()) {
                    $insert_bill = [
                        'id_user'          => $id_user,
                        'bill_description' => 'Payment for delivering the Order: ' . $order_number . '.',
                        'id_type_bill'     => 2,
                        'id_item'          => $order_detail['id'],
                        'due_date'         => $billing_due_date,
                        'balance'          => 0,
                        'pay_percents'     => 100,
                        'total_balance'    => 0,
                    ];
                    $this->user_bills->set_free_user_bill($insert_bill);
                } else {
                    $insert_bills[] = [
                        'id_user'          => $id_user,
                        'bill_description' => 'Payment for delivering the Order: ' . $order_number . '.',
                        'id_type_bill'     => 2,
                        'id_item'          => $order_detail['id'],
                        'create_date'      => $current_date,
                        'due_date'         => $billing_due_date,
                        'balance'          => moneyToDecimal($shipping_price),
                        'pay_percents'     => 100,
                        'total_balance'    => moneyToDecimal($shipping_price),
                        'note'             => json_encode(
                            [
                                'date_note' => $current_date,
                                'note'      => 'The bill has been created.',
                            ]
                        ),
                    ];
                }

                // GENERATING ORDER BILL(S) - FROM INVOICE MAP;
                // IF ORDER TYPE PO - GENERATES MULTIPLE BILLS ACORDING TO PERCENTS
                $invoice_info = $this->invoices->get_invoice($order_detail['id_invoice']);
                $invoice_map = !empty($invoice_info['invoice_map']) ? json_decode($invoice_info['invoice_map'], true) : [];
                $bill_messages = [
                    'full_payment'    => 'Payment of the Order: [ORDER_NUMBER].',
                    'partial_payment' => 'Partial payment of the Order: [ORDER_NUMBER].',
                ];

                $_bill_payment_days = 0;
                foreach ($invoice_map as $map_item) {
                    $payment_amount_type = compareFloatNumbers($map_item['percent'], 100, '=') ? 'full_payment' : 'partial_payment';

                    $_bill_payment_days += (int) $map_item['due_date'];
                    $insert_bills[] = [
                        'id_user'          => $id_user,
                        'bill_description' => str_replace('[ORDER_NUMBER]', $order_number, $bill_messages[$payment_amount_type]),
                        'id_type_bill'     => 1,
                        'id_item'          => $order_detail['id'],
                        'create_date'      => formatDate($map_item['issue_date'], 'Y-m-d H:i:s'),
                        'due_date'         => date_plus($_bill_payment_days, 'days', $current_date, true),
                        'balance'          => $map_item['price'],
                        'pay_percents'     => $map_item['percent'],
                        'total_balance'    => $map_item['price'],
                        'note'             => json_encode(
                            [
                                'date_note' => $current_date,
                                'note'      => 'The bill has been created.',
                            ]
                        ),
                    ];
                }

                // SET ORDER BILLS
                $this->user_bills->set_user_bills($insert_bills);

                // UPDATE ORDER
                $order_log = [
                    'date'    => formatDate($current_date, 'm/d/Y H:i:s A'),
                    'user'    => 'Buyer',
                    'message' => 'The Freight Forwarder has been assigned.',
                ];

                // SET TIME FOR DELIVERY
                if (!empty($order_detail['timeline_countdowns'])) {
                    $timeline_countdowns = json_decode($order_detail['timeline_countdowns'], true);
                    $timeline_countdowns['delivery_days'] = $delivery_time;
                } else {
                    $timeline_countdowns['delivery_days'] = $delivery_time;
                }

                $current_status_alias = 'shipper_assigned';
                $new_status_info = $this->orders->get_status_by_alias($current_status_alias);
                $current_status_name = $new_status_info['status'];

                $update_order = [
                    'status'                      => $new_status_info['id'],
                    'status_countdown'            => date_plus($new_status_info['countdown'], 'days', false, true),
                    'id_shipper'                  => $id_shipper,
                    'shipping_quote_details'      => json_encode($shipping_quote_details),
                    'shipper_type'                => $shipper_type,
                    'ship_price'                  => moneyToDecimal($shipping_price),
                    'ship_confirmed'              => 1,
                    'order_summary'               => $order_detail['order_summary'] . ',' . json_encode($order_log),
                    'search_info'                 => $order_detail['search_info'] . ',' . $data['shipper_info']['co_name'],
                    'shipping_insurance_accepted' => $shipping_insurance_accepted,
                    'shipping_insurance_details'  => json_encode($shipping_insurance_details),
                    'timeline_countdowns'         => json_encode($timeline_countdowns),
                ];
                $this->orders->change_order($id_order, $update_order);

                // NOTIFY USERS ABOUT CHANGES
                $this->notifier->send(
                    (new SystemNotification('order_shipper_assigned', [
                        '[ORDER_ID]'   => $order_number,
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    ...array_map(fn ($id) => (new Recipient((int) $id))->withRoomType(RoomType::CARGO()), [
                        $order_detail['id_buyer'],
                        $order_detail['id_seller'],
                    ])
                );
                // NOTIFY SHIPPER ABOUT NEW ORDER
                $shipper_details = null;
                if ('ep_shipper' === $shipper_type) {
                    // Get the real information about shipper
                    // Given that `$request_details['id_shipper']` already contains the value we need,
                    // we just add this to array
                    $shipper_details = ['id_user' => $request_details['id_shipper']];

                    $this->notifier->send(
                        (new SystemNotification('shipper_order_created', [
                            '[ORDER_ID]' => $order_number,
                            '[LINK]'     => sprintf('%sorder/my/order_number/%s', __SHIPPER_URL, $id_order),
                        ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                        (new Recipient((int) $request_details['id_shipper']))->withRoomType(RoomType::CARGO())
                    );

                    model('user_statistic')->set_users_statistic([
                        $id_shipper => ['orders_total' => 1, 'orders_active' => 1],
                    ]);
                }

                $add_contract = $this->_assign_order_documents([
                    'id_user'       => (int) ($order_detail['id_buyer'] ?? null) ?: null,
                    'id_seller'     => (int) ($order_detail['id_seller'] ?? null) ?: null,
                    'id_shipper'    => (int) ($shipper_details['id_user'] ?? null) ?: null,
                    'id_order'      => $id_order,
                    'user_type'     => 'buyer',
                    'document_type' => 'contract',
                ]);

                $add_invoice = $this->_assign_order_documents([
                    'id_user'       => (int) ($order_detail['id_buyer'] ?? null) ?: null,
                    'id_seller'     => (int) ($order_detail['id_seller'] ?? null) ?: null,
                    'id_shipper'    => (int) ($shipper_details['id_user'] ?? null) ?: null,
                    'id_order'      => $id_order,
                    'user_type'     => 'buyer',
                    'document_type' => 'invoice',
                ]);

                jsonResponse(translate('systmess_success_order_asign_shipper'), 'success', ['add_docs' => [$add_contract, $add_invoice], 'order' => $id_order, 'order_status_alias' => $current_status_alias, 'order_status_name' => $current_status_name]);

            break;
            case 'ishipper_quotes':
                // CHECK USER RIGHT - ONLY SELLER AND STAFF USERS
                checkPermisionAjax('manage_seller_orders');
                $errors = [];

                // CHECK VALIDATION RULES
                $validator_rules = [
                    [
                        'field' => 'order',
                        'label' => 'Order detail',
                        'rules' => ['required' => '', 'natural' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    $errors = array_merge($errors, $this->validator->get_array_errors());
                }
                $this->validator->reset_postdata();
                $this->validator->clear_array_errors();

                // GET ORDER DETAIL
                $id_order = (int) $_POST['order'];
                $order_info = $this->orders->get_full_order($id_order);
                if (empty($order_info)) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid') . ' Code #1');
                }

                // CHECK IF IS SELLER ORDER
                if (!is_privileged('user', $order_info['id_seller'], true)) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid') . ' Code #2');
                }

                // CHECK ORDER STATUS
                if ('invoice_confirmed' != $order_info['status_alias']) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid') . ' Code #3');
                }

                // ISHIPPERS - PARTNERS
                $this->load->model('Ishippers_Model', 'ishippers');
                $data['ishippers'] = arrayByKey($this->ishippers->get_seller_shipper_ipartners($order_info['id_seller']), 'id_shipper');
                if (empty($data['ishippers'])) {
                    jsonResponse(translate('systmess_error_order_ishipper_quotes_no_partners', ['{PARTNERS_PAGE_URL}' => __SITE_URL . 'shippers/my_partners']));
                }

                $purchase_order = !empty($order_info['purchase_order']) ? json_decode($order_info['purchase_order'], true) : [];
                $ishippers = [];
                $ishippers_quotes = !empty($order_info['ishippers_quotes']) ? json_decode($order_info['ishippers_quotes'], true) : [];
                if (!empty($_POST['ishippers_cache'])) {
                    foreach ($ishippers_quotes as $key_ishipper => $ishipper) {
                        if (in_array($key_ishipper, $_POST['ishippers_cache'])) {
                            $ishippers[$key_ishipper] = $ishipper;
                        }
                    }
                }

                if (!empty($_POST['ishippers_quotes'])) {
                    foreach ($_POST['ishippers_quotes'] as $id_shipper => $ishippers_quote) {
                        $id_shipper = (int) $id_shipper;
                        if (empty($data['ishippers'][$id_shipper])) {
                            continue;
                        }

                        $shipper_name = cleanOutput($data['ishippers'][$id_shipper]['shipper_name']);

                        $ishippers_validator_rules = [
                            [
                                'field' => 'shipment_type',
                                'label' => '<strong>' . $shipper_name . '</strong>: Shipping Service',
                                'rules' => ['required' => '', 'max_len[100]' => ''],
                            ],
                            [
                                'field' => 'delivery_days_from',
                                'label' => '<strong>' . $shipper_name . '</strong>: Delivery time, from',
                                'rules' => [
                                    'required'           => '',
                                    'is_natural_no_zero' => '',
                                    'max[' . config('ep_shippers_max_delivery_days', 180) . ']' => '',
                                    'matchFromToValue[delivery_days_to]' => translate('validation_order_ishipper_quotes_delivery_time_from_to', ['{{SHIPPER_NAME}}' => $shipper_name]),
                                ],
                            ],
                            [
                                'field' => 'delivery_days_to',
                                'label' => '<strong>' . $shipper_name . '</strong>: Delivery time, to',
                                'rules' => [
                                    'required'           => '',
                                    'is_natural_no_zero' => '',
                                    'max[' . config('ep_shippers_max_delivery_days', 180) . ']' => '',
                                    'matchToFromValue[delivery_days_from]' => translate('validation_order_ishipper_quotes_delivery_time_from_to', ['{{SHIPPER_NAME}}' => $shipper_name]),
                                ],
                            ],
                            [
                                'field' => 'price',
                                'label' => '<strong>' . $shipper_name . '</strong>: Shipping price',
                                'rules' => ['required' => '', 'positive_number' => '', 'min[0.01]' => '', 'max[9999999999.99]' => ''],
                            ],
                        ];

                        $this->validator->validate_data = [
                            'shipment_type'      => $ishippers_quote['shipment_type'],
                            'delivery_days_from' => $ishippers_quote['delivery_from'],
                            'delivery_days_to'   => $ishippers_quote['delivery_to'],
                            'price'              => $ishippers_quote['amount'],
                        ];

                        $this->validator->set_rules($ishippers_validator_rules);
                        if (!$this->validator->validate()) {
                            $errors = array_merge($errors, $this->validator->get_array_errors());
                        }
                        $this->validator->reset_postdata();
                        $this->validator->clear_array_errors();

                        $ishippers[$id_shipper] = [
                            'id_shipper'    => $id_shipper,
                            'shipment_type' => cleanInput($ishippers_quote['shipment_type']),
                            'delivery_from' => (int) $ishippers_quote['delivery_from'],
                            'delivery_to'   => (int) $ishippers_quote['delivery_to'],
                            'amount'        => (float) $ishippers_quote['amount'],
                        ];

                        $insurance_options = [];
                        $postdata_insurance_options = $ishippers_quote['insurance_options'];
                        if (empty($postdata_insurance_options)) {
                            $errors[] = translate('systmess_error_order_ishipper_quotes_no_insurance_options', ['{{SHIPPER_NAME}}' => $shipper_name]);
                        } else {
                            $insurance_option_index = 0;
                            foreach ($postdata_insurance_options as $key => $insurance_option) {
                                ++$insurance_option_index;

                                $insurance_options_validation_rules = [
                                    [
                                        'field' => 'title',
                                        'label' => "<strong>{$shipper_name}</strong>: Title of the Insurance option nr. {$insurance_option_index}",
                                        'rules' => ['required' => '', 'valide_title' => '', 'max_len[100]' => ''],
                                    ],
                                    [
                                        'field' => 'amount',
                                        'label' => "<strong>{$shipper_name}</strong>: Amount of the Insurance option nr. {$insurance_option_index}",
                                        'rules' => ['required' => '', 'positive_number' => '', 'min[0]' => '', 'max[999999]' => ''],
                                    ],
                                    [
                                        'field' => 'description',
                                        'label' => "<strong>{$shipper_name}</strong>: Description of the Insurance option nr. {$insurance_option_index}",
                                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                                    ],
                                ];

                                $this->validator->validate_data = $insurance_option;
                                $this->validator->set_rules($insurance_options_validation_rules);
                                if (!$this->validator->validate()) {
                                    $errors = array_merge($errors, array_values($this->validator->get_array_errors()));
                                }

                                $insurance_options[] = [
                                    'title'       => cleanInput($insurance_option['title']),
                                    'description' => cleanInput($insurance_option['description']),
                                    'amount'      => (float) $insurance_option['amount'],
                                ];

                                $this->validator->reset_postdata();
                                $this->validator->clear_array_errors();
                            }
                        }

                        $ishippers[$id_shipper]['insurance_options'] = $insurance_options;
                    }
                }

                if (!empty($errors)) {
                    jsonResponse($errors);
                }

                // IF ALL IS OK - UPDATE ORDER
                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Seller',
                    'message' => 'The International freight forwarders\' rates has been updated.',
                ];
                $this->orders->change_order($id_order, [
                    'ishippers_quotes' => !empty($ishippers) ? json_encode($ishippers) : null,
                    'order_summary'    => !empty($order_info['order_summary']) ? $order_info['order_summary'] . ',' . json_encode($order_log) : json_encode($order_log),
                ]);

                // NOTIFY BUYER
                $this->notifier->send(
                    (new SystemNotification('order_ishippers_updated', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_info['id_buyer']))->withRoomType(RoomType::CARGO())
                );

                jsonResponse(translate('systmess_success_order_ishipper_quotes'), 'success');

            break;
            case 'purchase_order':
                checkPermisionAjax('manage_seller_orders');

                // region Order
                $id_order = (int) arrayGet($_POST, 'id_order');
                if (empty($id_order) || empty($order_info = model('orders')->get_full_order($id_order))) {
                    jsonResponse(translate('systmess_error_invalid_data') . ' Code #1');
                }
                // endregion Order

                // region Access check
                if (!in_array($order_info['status_alias'], ['new_order', 'purchase_order'])) {
                    jsonResponse(translate('systmess_error_create_purchase_order_already_exists'));
                }

                if (!is_privileged('user', $order_info['id_seller'], true)) {
                    jsonResponse(translate('systmess_error_invalid_data') . ' Code #2');
                }
                // endregion Access check

                // region Validation
                $errors = [];
                if (!$this->_validate_purchase_order($order_info, $errors)) {
                    jsonResponse($errors);
                }
                // endregion Validation

                $order_number = orderNumber($id_order);
                $products = $this->_prepare_ordered_products($order_info);
                $amount = $this->_calculate_order_amount($order_info);
                $order_discount = normalize_discount($_POST['discount']);
                $total_weight = $this->_calculate_order_weight($order_info);

                $filtered_package = $this->_prepare_order_package();
                if ($filtered_package['weight'] < $total_weight) {
                    jsonResponse(translate('systmess_error_create_purchase_order_wrong_weight', ['{{TOTAL_WEIGHT}}' => $total_weight]));
                }

                $final_amount = minusPercent($amount, $order_discount);
                $issue_date = date('Y-m-d H:i:s');
                $bill_messages = [
                    'full_payment'    => 'Payment of the Order: [ORDER_NUMBER].',
                    'partial_payment' => 'Partial payment of the Order: [ORDER_NUMBER].',
                ];

                if ('po' == $order_info['order_type']) {
                    // SPLITTED INVOICE MAP ELEMENTS FOR EACH X% - ONLY FOR PO
                    foreach ($_POST['bill'] as $key => $bill_item) {
                        $payment_amount_type = compareFloatNumbers($final_amount, $bill_item['amount'], '=') ? 'full_payment' : 'partial_payment';
                        $bills[] = [
                            'type'       => 'po',
                            'issue_date' => $issue_date,
                            'due_date'   => (int) $bill_item['due_date'],
                            'percent'    => calculate_percent($final_amount, $bill_item['amount']),
                            'note'       => str_replace('[ORDER_NUMBER]', $order_number, $bill_messages[$payment_amount_type]) . (empty($bill_item['note'])) ? ' ' . cleanInput($bill_item['note']) : '',
                            'price'      => (float) $bill_item['amount'],
                        ];
                    }
                } else {
                    // BY DEFAULT GENERATE SINGLE MAP ELEMENT FOR 100%
                    $payment_amount_type = 'full_payment';
                    $bills[] = [
                        'type'       => 'order',
                        'issue_date' => $issue_date,
                        'due_date'   => config('default_bill_period', 7),
                        'percent'    => 100,
                        'note'       => 'Payment of the Order: ' . $order_number,
                        'price'      => floatval($final_amount),
                    ];
                }

                // SET ISSUE DATE AND DUE DATE USING MAP ELEMENTS
                $po_invoice_due_date = getDateFormat($_POST['invoice_due_date'], 'm/d/Y', 'Y-m-d');

                // INSERT INVOICE TO DATABASE
                $invoice = [
                    'id_order'     => $id_order,
                    'po_number'    => cleanInput($_POST['po_number']),
                    'discount'     => $order_discount,
                    'amount'       => $amount,
                    'final_amount' => $final_amount,
                    'issue_date'   => $issue_date,
                    'due_date'     => $po_invoice_due_date,
                    'products'     => $products,
                    'invoice_map'  => $bills,
                    'subject'      => cleanInput($_POST['subject']),
                    'notes'        => cleanInput($_POST['notes']),
                ];

                // PREPARING SHIPPING FROM LOCATION ADDRESS
                $location = [];
                $ship_from_country = (int) $_POST['port_country'];
                $ship_from_state = (int) $_POST['states'];
                $ship_from_city = (int) $_POST['port_city'];
                $ship_from_zip = cleanInput($_POST['zip']);
                $ship_from_address = cleanInput($_POST['address']);
                $location = model('country')->get_country_state_city($ship_from_city);
                $location[] = $ship_from_zip;
                $location[] = $ship_from_address;
                $ship_from = implode(', ', array_filter($location));

                $shipping_from = [
                    'country'      => $ship_from_country,
                    'state'        => $ship_from_state,
                    'city'         => $ship_from_city,
                    'zip'          => $ship_from_zip,
                    'address'      => $ship_from_address,
                    'full_address' => $ship_from,
                ];

                $purchase_order = !empty($order_info['purchase_order']) ? json_decode($order_info['purchase_order'], true) : [];
                $purchase_order['invoice'] = $invoice;
                $purchase_order['shipping_from'] = $shipping_from;
                $purchase_order['due_date'] = $po_invoice_due_date;
                $purchase_order['products_weight'] = $total_weight;

                // SET ESTIMATE TIME FOR PACKAGING
                $timeline_countdowns = !empty($order_info['timeline_countdowns']) ? json_decode($order_info['timeline_countdowns'], true) : [];
                $timeline_countdowns['time_for_packaging'] = (int) $_POST['packaging'];

                // SET PURCHASE ORDER TIMELINE
                $purchase_order_timeline = !empty($order_info['purchase_order_timeline']) ? json_decode($order_info['purchase_order_timeline'], true) : [];
                $purchase_order_timeline[] = [
                    'date'    => date('Y-m-d H:i:s'),
                    'user'    => 'Seller',
                    'message' => 'Purchase order has been updated.',
                ];

                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Seller',
                    'message' => 'Purchase order has been updated.',
                ];

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);
                $shipmentTypeId = request()->request->getInt('shipment_type');
                $shipmentTypeDetail = $shippingTypesModel->findOne((int) $shipmentTypeId, ['conditions' => ['isVisible' => 1]]);

                $order_log = [
                    'date'    => date('m/d/Y h:i:s A'),
                    'user'    => 'Seller',
                    'message' => "{$shipmentTypeDetail['type_name']} has been chosen as a shipping method.",
                ];

                // IF ALL IS OK - UPDATE ORDER AND NOTIFY USERS
                $update_order = [
                    'discount'                => $order_discount,
                    'price'                   => $amount,
                    'final_price'             => $final_amount,
                    'package_detail'          => json_encode($filtered_package),
                    'weight'                  => $filtered_package['weight'],
                    'purchased_products'      => json_encode($products),
                    'purchase_order'          => json_encode($purchase_order),
                    'purchase_order_timeline' => json_encode($purchase_order_timeline),
                    'ship_from'               => $ship_from,
                    'ship_from_country'       => $ship_from_country,
                    'ship_from_state'         => $ship_from_state,
                    'ship_from_city'          => $ship_from_city,
                    'ship_from_zip'           => $ship_from_zip,
                    'ship_from_address'       => $ship_from_address,
                    'seller_delivery_area'    => (int) $_POST['delivery_area'],
                    'shipment_type'           => $shipmentTypeId,
                    'timeline_countdowns'     => json_encode($timeline_countdowns),
                    'extend_request'          => 0,
                    'order_summary'           => $order_info['order_summary'] . ',' . json_encode($order_log),
                ];

                $current_status_alias = $order_info['status_alias'];
                $current_status_name = $order_info['status'];
                if ($order_info['status_alias'] = 'new_order') {
                    $current_status_alias = 'purchase_order';
                    $new_status_info = $this->orders->get_status_by_alias($current_status_alias);
                    $current_status_name = $new_status_info['status'];
                    $update_order['status'] = $new_status_info['id'];

                    $update_order['status_countdown'] = date_plus($new_status_info['countdown'], 'days', false, true);
                    $update_order['status'] = $new_status_info['id'];
                }

                $this->orders->change_order($id_order, $update_order);

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);
                $usersInfo = $userModel->getUsers(['users_list' => $order_info['id_buyer'] . ', ' . $order_info['id_seller']]);
                $usersInfo = arrayByKey($usersInfo, 'idu');
                $buyerInfo = $usersInfo[$order_info['id_buyer']];
                $sellerInfo = $usersInfo[$order_info['id_seller']];

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new PoShippingMethod($buyerInfo['user_name'], orderNumber($id_order), $shipmentTypeDetail))
                        ->to(new RefAddress((string) $order_info['id_buyer'], new Address($buyerInfo['email'])))
                );

                $mailer->send(
                    (new PoShippingMethod($sellerInfo['user_name'], orderNumber($id_order), $shipmentTypeDetail))
                        ->to(new RefAddress((string) $order_info['id_seller'], new Address($sellerInfo['email'])))
                );

                // NOTIFY BUYER ABOUT INVOICE
                $this->notifier->send(
                    (new SystemNotification('purchase_order_updated', [
                        '[ORDER_ID]'   => $order_number,
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                        '[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_info['id_buyer']))->withRoomType(RoomType::CARGO())
                );

                jsonResponse(translate('systmess_succes_created_purchase_order'), 'success', ['order' => $id_order, 'order_status_alias' => $current_status_alias, 'order_status_name' => $current_status_name]);

            break;
            case 'purchase_order_notes':
                checkPermisionAjax('buy_item,manage_seller_orders');

                $validator_rules = [
                    [
                        'field' => 'id_order',
                        'label' => 'Purchase Order',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = privileged_user_id();
                $id_order = (int) $_POST['id_order'];
                if (empty($order_info = $this->orders->get_full_order($id_order))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ('purchase_order' != $order_info['status_alias']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $_user_type = have_right('buy_item') ? 'Buyer' : 'Seller';
                $_notify_user_id = have_right('buy_item') ? $order_info['id_seller'] : $order_info['id_buyer'];

                $purchase_order_timeline = !empty($order_info['purchase_order_timeline']) ? json_decode($order_info['purchase_order_timeline'], true) : [];
                $purchase_order_timeline[] = [
                    'date' 		   => date('Y-m-d H:i:s'),
                    'user'   	  => $_user_type,
                    'message'   => cleanInput($_POST['message']),
                ];

                $order_log = [
                    'date' 		   => date('m/d/Y h:i:s A'),
                    'user'   	  => $_user_type,
                    'message'   => cleanInput($_POST['message']),
                ];

                $this->orders->change_order($id_order, [
                    'purchase_order_timeline' => json_encode($purchase_order_timeline),
                    'order_summary'           => $order_info['order_summary'] . ',' . json_encode($order_log),
                ]);

                // NOTIFY BUYER ABOUT INVOICE
                $this->notifier->send(
                    (new SystemNotification('purchase_order_new_message', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[USER_TYPE]'  => $_user_type,
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $_notify_user_id))->withRoomType(RoomType::CARGO())
                );

                jsonResponse(translate(have_right('buy_item') ? 'systmess_succes_buyer_add_po_notice' : 'systmess_succes_seller_add_po_notice'), 'success');

            break;
            case 'purchase_order_confirm':
                checkPermisionAjax('buy_item');

                $validator_rules = [
                    [
                        'field' => 'id_order',
                        'label' => 'Purchase Order',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = privileged_user_id();
                $id_order = (int) $_POST['id_order'];
                if (empty($order_info = $this->orders->get_full_order($id_order))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ('purchase_order' != $order_info['status_alias']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $new_status_info = $this->orders->get_status_by_alias('purchase_order_confirmed');
                if (empty($new_status_info)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $purchase_order_timeline = !empty($order_info['purchase_order_timeline']) ? json_decode($order_info['purchase_order_timeline'], true) : [];
                $purchase_order_timeline[] = [
                    'date' 		   => date('Y-m-d H:i:s'),
                    'user'   	  => 'Buyer',
                    'message'   => 'Purchase Order has been confirmed.',
                ];

                $order_log = [
                    'date' 		   => date('m/d/Y h:i:s A'),
                    'user'   	  => 'Buyer',
                    'message'   => 'Purchase Order has been confirmed.',
                ];

                $this->orders->change_order($id_order, [
                    'status'                  => $new_status_info['id'],
                    'status_countdown'        => date_plus($new_status_info['countdown'], 'days', false, true),
                    'purchase_order_timeline' => json_encode($purchase_order_timeline),
                    'order_summary'           => $order_info['order_summary'] . ',' . json_encode($order_log),
                ]);

                // NOTIFY BUYER ABOUT INVOICE
                $this->notifier->send(
                    (new SystemNotification('purchase_order_confirmed', [
                        '[ORDER_ID]'   => orderNumber($id_order),
                        '[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $order_info['id_seller']))->withRoomType(RoomType::CARGO())
                );
                $id_ep_manager = (int) $order_info['ep_manager'];
                if ($id_ep_manager > 0) {
                    $this->notifier->send(
                        (new SystemNotification('purchase_order_confirmed_admin', [
                            '[ORDER_ID]' => orderNumber($id_order),
                        ]))->channels([(string) SystemChannel::STORAGE()]),
                        new Recipient((int) $id_ep_manager)
                    );
                }

                jsonResponse(translate('systmess_succes_confrim_purchase_order'), 'success', ['order' => $id_order, 'order_status_alias' => $new_status_info['alias'], 'order_status_name' => $new_status_info['status']]);

            break;
        }
    }

    public function popups_order()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $this->load->model('Company_Model', 'company');
        $this->load->model('ItemsReview_Model', 'reviews');
        $this->load->model('Country_Model', 'country');
        $this->load->model('Userfeedback_Model', 'user_feedback');
        $this->load->model('User_Bills_Model', 'user_bills');

        $type = $this->uri->segment(3);
        $id_order = intval($this->uri->segment(4));

        switch ($type) {
            case 'add_date_ready_for_pickup':
                if (!have_right('manage_seller_orders') && !have_right('manage_shipper_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (
                    !is_privileged('user', $data['order_info']['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $data['order_info']['id_shipper'], 'manage_shipper_orders')
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if ('shipping_in_progress' != $data['order_info']['status_alias']) {
                    messageInModal(translate('systmess_error_order_add_date_ready_for_pickup_wrong_status'));
                }

                $this->view->assign($data);
                $this->view->display('new/order/popup_date_for_pickup_view');

            break;
            // GET ORDER BILLS LIST
            case 'bills_list':
                // CHECK USER RIGHTS - BUYER ONLY
                if (!have_right('buy_item')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS BUYER ORDER
                if (!is_my($data['order_info']['id_buyer'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $params = ['id_item' => $id_order, 'encript_detail' => 1, 'bills_type' => '1,2', 'pagination' => false];
                $status = $this->uri->segment(5);
                if (!empty($status)) {
                    $params['status'] = "'" . cleanInput($status) . "'";
                }

                $data['bills'] = $this->user_bills->get_user_bills($params);
                if (empty($data['bills'])) {
                    messageInModal(translate('systmess_error_order_detail_bills_list_no_bills'), 'info');
                }

                $data['status'] = $this->user_bills->get_bills_statuses();

                $this->view->assign($data);
                $this->view->display('new/order/modal_bills_list_view');

            break;
            // GET ORDER BILLS LIST - EP MANAGER ASSIGNED
            case 'admin_bills_list':
                // CHECK USER RIGHTS - EP ORDER MANAGER
                if (!have_right('administrate_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS MANAGER ASSIGNED ORDER
                if (!(have_right('read_all_orders') || is_my($data['order_info']['ep_manager']))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $params = ['id_item' => $id_order, 'encript_detail' => 1, 'bills_type' => '1,2', 'pagination' => false];
                $status = $this->uri->segment(5);
                if (!empty($status)) {
                    $params['status'] = "'" . cleanInput($status) . "'";
                }

                $data['bills'] = $this->user_bills->get_user_bills($params);
                if (empty($data['bills'])) {
                    messageInModal(translate('systmess_error_order_detail_bills_list_no_bills'), 'info');
                }

                $data['status'] = $this->user_bills->get_bills_statuses();

                $this->view->assign($data);
                $this->view->display('admin/order/manager_assigned/popup_bills_list_view');

            break;
            // GET ORDER TIMELINE - EP MANAGER ASSIGNED
            case 'admin_order_timeline':
                // CHECK USER RIGHTS - EP ORDER MANAGER
                if (!have_right('administrate_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS MANAGER ASSIGNED ORDER
                if (!(is_my($data['order_info']['ep_manager']) || have_right('read_all_orders'))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $this->view->display('admin/order/manager_assigned/popup_order_timeline_view', $data);

            break;
            // EDIT TRACKING INFO - SELLER & EP MANAGER ASSIGNED
            case 'add_tracking_info':
                // CHECK USER RIGHTS
                if (!have_right('manage_seller_orders') && !have_right('manage_shipper_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS MANAGER ASSIGNED OR SELLER ORDER
                if (
                    !is_privileged('user', $data['order_info']['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $data['order_info']['id_shipper'], 'manage_shipper_orders')
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if ('preparing_for_shipping' != $data['order_info']['status_alias']) {
                    messageInModal(translate('systmess_error_order_add_tracking_info_wrong_status'));
                }

                if ('ishipper' == $data['order_info']['shipper_type']) {
                    $this->load->model('Ishippers_Model', 'ishippers');
                    $shipper_info = $this->ishippers->get_shipper($data['order_info']['id_shipper']);

                    $data['shipper_info'] = [
                        'shipper_name' => $shipper_info['shipper_original_name'],
                        'shipper_logo' => __IMG_URL . 'public/img/ishippers_logo/' . $shipper_info['shipper_logo'],
                    ];
                }

                $this->view->display('new/order/popup_track_info_view', $data);

            break;
            // EDIT TRACKING INFO - SELLER & EP MANAGER ASSIGNED
            case 'edit_tracking_info':
                // CHECK USER RIGHTS
                if (!have_right('manage_seller_orders') && !have_right('administrate_orders') && !have_right('manage_shipper_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS MANAGER ASSIGNED OR SELLER ORDER
                if (
                    !is_my($data['order_info']['ep_manager'])
                    && !is_privileged('user', $data['order_info']['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $data['order_info']['id_shipper'], 'manage_shipper_orders')
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if ('shipping_in_progress' != $data['order_info']['status_alias']) {
                    messageInModal(translate('systmess_error_order_add_tracking_info_wrong_status'), 'info');
                }

                if (have_right('manage_seller_orders') || have_right('manage_shipper_orders')) {
                    if ('ishipper' == $data['order_info']['shipper_type']) {
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $shipper_info = $this->ishippers->get_shipper($data['order_info']['id_shipper']);

                        $data['shipper_info'] = [
                            'shipper_name' => $shipper_info['shipper_original_name'],
                            'shipper_logo' => __IMG_URL . 'public/img/ishippers_logo/' . $shipper_info['shipper_logo'],
                        ];
                    }

                    $this->view->display('new/order/popup_edit_tracking_info_view', $data);
                } else {
                    $this->view->display('admin/order/manager_assigned/popup_edit_tracking_info_view', $data);
                }

            break;
            // ADD CANCEL REQUEST - SELLER & BUYER
            case 'cancel_request':
                // CHECK USER RIGHTS
                if (!have_right('cancel_order_request')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // CHECK IF IS BUYER OR SELLER ORDER
                if (
                    !is_my($data['order_info']['id_buyer'])
                    && !is_privileged('user', $data['order_info']['id_seller'], true)
                    && !is_privileged('user', $data['order_info']['id_shipper'], true)
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (1 == $data['order_info']['cancel_request']) {
                    $conditions = [
                        'status'   => "'init'",
                        'id_user'  => privileged_user_id(),
                        'id_order' => $id_order,
                    ];

                    $count_requests = $this->orders->count_cancel_order_requests($conditions);
                    if ($count_requests > 0) {
                        messageInModal(translate('systmess_error_add_order_cancel_already_exist_one'), 'info');
                    }
                }

                $this->view->display('new/order/cancel_request_form_view', $data);

            break;
            case 'order_cancel_requests':
                // CHECK USER RIGHTS
                if (!have_right('administrate_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_info'] = $this->orders->get_order($id_order);

                // CHECK IF ORDER EXIST
                if (empty($data['order_info'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (!have_right('read_order_details') && !is_my($data['order_info']['ep_manager'])) {
                    messageInModal(translate('systmess_error_order_cancel_request_not_assigned_manager'), 'info');
                }

                if (0 == $data['order_info']['cancel_request']) {
                    messageInModal(translate('systmess_error_order_cancel_request_not_found'), 'info');
                }

                $data['cancel_requests'] = $this->orders->get_cancel_order_requests(['id_order' => $id_order]);
                if (empty($data['cancel_requests'])) {
                    messageInModal(translate('systmess_error_order_cancel_request_not_found'), 'info');
                }

                $this->view->display('admin/order/manager_assigned/cancel_requests_view', $data);

            break;
            // ADD CANCEL REASON - EP MANAGER
            case 'add_reason':
                // CHECK USER RIGHTS
                if (!have_right('manage_content')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['orders_status'] = $this->orders->get_orders_status();
                $this->view->display('admin/order/manager_assigned/add_reason_form_view', $data);

            break;
            // EDIT CANCEL REASON - EP MANAGER
            case 'edit_reason':
                // CHECK USER RIGHTS
                if (!have_right('manage_content')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $id_reason = (int) $this->uri->segment(4);
                $data['reason'] = $this->orders->get_reason($id_reason);
                if (empty($data['reason'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $relations = $this->orders->get_reason_statuses_relation($id_reason);
                foreach ($relations as $relation) {
                    $data['relations'][] = $relation['id_status'];
                }

                $data['orders_status'] = $this->orders->get_orders_status();
                $this->view->display('admin/order/manager_assigned/edit_reason_form_view', $data);

            break;
            case 'producing_status':
                if (!have_right('manage_seller_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order'] = $this->orders->get_order($id_order);

                if (empty($data['order']) || !is_privileged('user', $data['order']['id_seller'], true)) {
                    messageInModal(translate('systmess_error_order_doesnt_exist'));
                }

                if ('po' != $data['order']['order_type']) {
                    messageInModal(translate('systmess_error_order_invalid_action'));
                }

                if (!in_array($data['order']['status_alias'], ['payment_processing', 'order_paid', 'payment_confirmed'])) {
                    messageInModal(translate('systmess_error_order_invalid_action'));
                }

                $this->view->display('new/order/producing_status_form_view', $data);

            break;
            case 'feedbacks':
                if (!have_right('manage_seller_orders') && !have_right('buy_item')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('UserFeedback_Model', 'userfeedbacks');
                $id_user = privileged_user_id();
                $data['user_feedbacks'] = $this->userfeedbacks->get_user_feedbacks(['id_order' => $id_order, 'poster' => $id_user]);
                $data['id_order'] = $id_order;

                if (have_right('manage_seller_orders')) {
                    $params_order['id_seller'] = $id_user;
                } else {
                    $params_order['id_buyer'] = $id_user;
                }

                $order_info = $this->orders->get_order($id_order, ['id_buyer', 'id_seller'], $params_order);
                if (empty($order_info)) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                if (have_right('manage_seller_orders')) {
                    $data['user_main'] = $this->user->getUser($order_info['id_buyer']);
                } elseif (have_right('buy_item')) {
                    $data['user_main'] = $this->user->getUser($order_info['id_seller']);
                }

                $data['user_services_form'] = $this->userfeedbacks->getServiceByGroup($data['user_main']['user_group']);
                // feedbacks
                $data['feedbacks'] = $this->userfeedbacks->get_user_feedbacks(['poster_list' => $order_info['id_seller'] . ',' . $order_info['id_buyer'], 'id_order' => $id_order, 'db_keys' => 'id_feedback']);

                if (!empty($data['feedbacks'])) {
                    $feedbacks_keys = implode(',', array_keys($data['feedbacks']));
                    $data['count_feedbacks'] = $this->userfeedbacks->counter_by_conditions(['user' => $id_user]);

                    // unserialize services/order_summary
                    foreach ($data['feedbacks'] as $key => $value) {
                        if (!empty($value['services'])) {
                            $data['feedbacks'][$key]['services'] = unserialize($value['services']);
                        }
                        if (!empty($value['order_summary'])) {
                            $data['feedbacks'][$key]['order_summary'] = unserialize($value['order_summary']);
                        }
                    }

                    $data['helpful_feedbacks'] = $this->userfeedbacks->get_helpful_by_feedback($feedbacks_keys, $id_user);
                } else {
                    $data['count_feedbacks'] = 0;
                }

                $this->view->assign($data);
                $this->view->display('new/users_feedbacks/popup_feedback_view');

            break;
            case 'order_detail':
                checkPermisionAjaxModal('manage_seller_orders,buy_item,see_orders,manage_shipper_orders');

                $params = [];

                if (have_right('buy_item')) {
                    $params['id_buyers'] = id_session();
                } elseif (have_right('manage_seller_orders')) {
                    $params['id_sellers'] = privileged_user_id();
                } elseif (have_right('manage_shipper_orders')) {
                    $params['id_shipper'] = privileged_user_id();
                }

                $data['order'] = $this->orders->get_full_order($id_order, $params);

                if (empty($data['order'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['purchased_products'] = !empty($data['order']['purchased_products']) ? json_decode($data['order']['purchased_products'], true) : [];
                if ('new_order' == $data['order']['status_alias'] || empty($data['purchased_products'])) {
                    foreach ($data['order']['ordered'] as $_item_ordered) {
                        $data['purchased_products'][] = [
                            'id_item'          => $_item_ordered['id_item'],
                            'id_ordered_item'  => $_item_ordered['id_ordered_item'],
                            'id_snapshot'      => $_item_ordered['id_snapshot'],
                            'type'             => 'item',
                            'name'             => $_item_ordered['title'],
                            'unit_price'       => $_item_ordered['price_ordered'],
                            'quantity'         => $_item_ordered['quantity_ordered'],
                            'total_price'      => floatval($_item_ordered['price_ordered'] * $_item_ordered['quantity_ordered']),
                            'detail_ordered'   => $_item_ordered['detail_ordered'],
                            'item_weight'      => $_item_ordered['item_weight'],
                            'item_length'      => $_item_ordered['item_length'],
                            'item_width'       => $_item_ordered['item_width'],
                            'item_height'      => $_item_ordered['item_height'],
                            'hs_tariff_number' => $_item_ordered['hs_tariff_number'],
                            'country_abr'      => $_item_ordered['country_abr'],
                            'image'            => $_item_ordered['main_image'],
                            'reviews_count'    => $_item_ordered['snapshot_reviews_count'],
                            'rating'           => $_item_ordered['snapshot_rating'],
                        ];
                    }
                }

                $data['order']['ordered'] = arrayByKey($data['order']['ordered'], 'id_ordered_item');

                $show_expire = false;
                $expire = (strtotime($data['order']['status_countdown']) - time()) * 1000;
                if (!in_array($data['order']['status_alias'], ['order_completed', 'late_payment', 'canceled_by_buyer', 'canceled_by_seller', 'canceled_by_ep'])) {
                    // CALCULATE EXPIRE TIME IN MILISECONDS FOR COUNTDOWN TIMER
                    $show_expire = true;
                    $data['extend_btn'] = false;
                    $data['show_extend_btn'] = false;
                    if (is_privileged('user', $data['order']['id_buyer'], 'buy_item') && in_array($data['order']['status_alias'], ['invoice_sent_to_buyer', 'shipper_assigned', 'payment_processing', 'shipping_completed']) || is_privileged('user', $data['order']['id_seller'], 'manage_seller_orders') && in_array($data['order']['status_alias'], ['new_order', 'invoice_confirmed', 'preparing_for_shipping'])) {
                        if ((bool) $data['order']['extend_request']) {
                            $data['show_extend_btn'] = true;
                        } else {
                            $data['extend_btn'] = true;
                        }
                    }
                }

                $data['expire'] = $expire;
                $data['show_timeline'] = $show_expire;

                $params = [
                    'id_order' => $id_order,
                ];

                $user_seller_id = 0;
                $user_buyer_id = 0;

                if (have_right('buy_item')) {
                    $user_seller_id = $data['order']['id_seller'];
                } elseif (have_right('manage_seller_orders')) {
                    $user_buyer_id = $data['order']['id_buyer'];
                } elseif (have_right('manage_shipper_orders')) {
                    $user_seller_id = $data['order']['id_seller'];
                    $user_buyer_id = $data['order']['id_buyer'];
                }

                if (0 != $user_buyer_id) {
                    $this->load->model('Company_Buyer_Model', 'company_buyer');
                    $data['user_buyer_info'] = $this->user->getSimpleUser($user_buyer_id, "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.user_photo");
                    $data['company_buyer_info'] = $this->company_buyer->get_company_by_user($user_buyer_id);
                }

                if (0 != $user_seller_id && (have_right('buy_item') || have_right('manage_shipper_orders'))) {
                    $this->load->model('Company_Model', 'company');
                    $data['company_info'] = $this->company->get_seller_base_company($user_seller_id, 'cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company');
                }

                if ($data['order']['id_shipper'] > 0) {
                    if ('ep_shipper' == $data['order']['shipper_type']) {
                        $this->load->model('Shippers_Model', 'shippers');
                        $shipper_info = $this->shippers->get_shipper_by_user($data['order']['id_shipper']);
                        $btnChatShipper = new ChatButton(['recipient' => $data['order']['id_shipper'], 'recipientStatus' => 'active', 'module' => 9, 'item' => $data['order']['id']], ['icon' => '', 'classes' => 'btn-contact', 'text' => 'Chat with shipper']);

                        $data['shipper_info'] = [
                            'shipper_name' => $shipper_info['co_name'],
                            'shipper_logo' => getDisplayImageLink(['{ID}' => $shipper_info['id'], '{FILE_NAME}' => $shipper_info['logo']], 'shippers.main', ['thumb_size' => 1]),
                            'shipper_url'  => getShipperURL($shipper_info),
                            'btnChat'      => $btnChatShipper->button(),
                        ];
                    } else {
                        $this->load->model('Ishippers_Model', 'ishippers');
                        $shipper_info = $this->ishippers->get_shipper($data['order']['id_shipper']);
                        $data['shipper_info'] = [
                            'shipper_name'     => $shipper_info['shipper_original_name'],
                            'shipper_logo'     => __IMG_URL . 'public/img/ishippers_logo/' . $shipper_info['shipper_logo'],
                            'shipper_contacts' => $shipper_info['shipper_contacts'],
                        ];
                    }
                }

                if ($data['order']['ep_manager'] > 0) {
                    $data['ep_manager_info'] = $this->user->getSimpleUser($data['order']['ep_manager'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name");
                }

                if (!empty($data['order']['status_description'])) {
                    $data['description_title'] = json_decode($data['order']['status_description'], true);

                    $type_status_description = null;

                    if (have_right('buy_item')) {
                        $type_status_description = 'buyer';
                    } elseif (have_right('manage_seller_orders')) {
                        $type_status_description = 'seller';
                    } elseif (have_right('manage_shipper_orders')) {
                        $type_status_description = 'shipper';
                    } elseif (have_right('administrate_orders')) {
                        $type_status_description = 'ep_manager';
                    }

                    if (null !== $type_status_description) {
                        $description_title_array = $data['description_title'][$type_status_description];

                        $data['description_title'] = $description_title_array['text'];
                    }
                }

                $this->view->assign($data);
                if (have_right_or('manage_seller_orders,manage_shipper_orders,buy_item')) {
                    $this->view->display('new/order/popup_order_details_view');
                } else {
                    $this->view->display('admin/order/popup_order_detail_view');
                }

            break;
            case 'shipping_address':
                if (!have_right('manage_seller_orders') && !have_right('buy_item')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_order_doesnt_exist'));
                }

                $data['id_order'] = $id_order;
                $data['order'] = $this->orders->get_order($id_order);
                $data['user_info'] = $this->user->getSimpleUser($id_user);
                $data['port_country'] = $this->country->fetch_port_country();

                if (have_right('buy_item')) {
                    if ($data['order']['ship_to_country'] > 0) {
                        $data['shipping'] = [
                            'country' => $data['order']['ship_to_country'],
                            'state'   => $data['order']['ship_to_state'],
                            'city'    => $data['order']['ship_to_city'],
                            'zip'     => $data['order']['ship_to_zip'],
                            'address' => $data['order']['ship_to_address'],
                        ];
                    } else {
                        $data['shipping'] = [
                            'country' => $data['user_info']['country'],
                            'state'   => $data['user_info']['state'],
                            'city'    => $data['user_info']['city'],
                            'zip'     => $data['user_info']['zip'],
                            'address' => $data['user_info']['address'],
                        ];
                    }

                    $view_name = 'new/order/ship_to_view';
                } else {
                    if ($data['order']['ship_from_country'] > 0) {
                        $data['shipping'] = [
                            'country' => $data['order']['ship_from_country'],
                            'state'   => $data['order']['ship_from_state'],
                            'city'    => $data['order']['ship_from_city'],
                            'zip'     => $data['order']['ship_from_zip'],
                            'address' => $data['order']['ship_from_address'],
                        ];
                    } else {
                        $data['shipping'] = [
                            'country' => $data['user_info']['country'],
                            'state'   => $data['user_info']['state'],
                            'city'    => $data['user_info']['city'],
                            'zip'     => $data['user_info']['zip'],
                            'address' => $data['user_info']['address'],
                        ];
                    }

                    $view_name = 'new/order/ship_from_view';
                }
                $data['states'] = $this->country->get_states($data['shipping']['country']);
                $data['city_selected'] = $this->country->get_city($data['shipping']['city']);
                $this->view->assign($data);

                $this->view->display($view_name);

            break;
            case 'write_reason':
                if (!have_right('buy_item') && !have_right('manage_seller_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                if (have_right('buy_item')) {
                    $reason = 1;
                } else {
                    $reason = 2;
                }

                $data['orders_reason'] = $this->orders->get_reasons($reason);
                $data['id_order'] = $id_order;
                $order_detail = $this->orders->get_order($id_order);

                if (have_right('buy_item') && !is_my($order_detail['id_buyer'])) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                if (have_right('manage_seller_orders') && $order_detail['id_seller'] != privileged_user_id()) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['id_status'] = $order_detail['status'];

                $this->view->assign($data);
                $this->view->display('order/buyer/popup_reason_view');

            break;
            case 'cancel_order':
                if (!have_right('administrate_orders')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data['order_detail'] = $this->orders->get_order($id_order);

                if (1 == $data['order_detail']['cancel_request']) {
                    messageInModal(translate('systmess_error_order_cancel_order_has_cancel_request'), 'info');
                }

                if (1 == $data['order_detail']['dispute_opened']) {
                    messageInModal(translate('systmess_error_order_cancel_order_has_opened_dispute'), 'info');
                }

                $this->load->model('User_Bills_Model', 'user_bills');
                if (in_array($data['order_detail']['status_alias'], ['shipper_assigned', 'payment_processing'])) {
                    $count_bills = $this->user_bills->count_bills_by_item($data['order_detail']['id_buyer'], $id_order, "'paid','init'", '1,2');
                    if ($count_bills > 0) {
                        messageInModal(translate('systmess_error_order_cancel_order_has_unvalidated_bills'), 'info');
                    }
                }

                // CHECK FOR PAID AND CONFIRMED BILLS
                // AND DISPLAY THIS DETAILS
                if (in_array($data['order_detail']['status_alias'], ['payment_processing', 'order_paid', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress', 'shipping_completed'])) {
                    $params = ['id_item' => $id_order, 'encript_detail' => 1, 'bills_type' => '1,2', 'pagination' => false];
                    $data['bills'] = $this->user_bills->get_user_bills($params);
                    $data['amount_confirmed'] = $this->user_bills->summ_bills_by_order($id_order, "'confirmed'", '1,2');
                }

                $data['orders_reason'] = $this->orders->get_reasons(['order_status' => $data['order_detail']['status']]);

                $data['status'] = $this->user_bills->get_bills_statuses();

                $this->view->assign($data);
                $this->view->display('admin/order/popup_reason_view');

            break;
            case 'ishipper_quotes':
                checkPermisionAjaxModal('manage_seller_orders');

                // region User access
                $user_id = (int) privileged_user_id();
                $order_id = (int) uri()->segment(4);
                if (
                    empty($order_id)
                    || !model('orders')->isMyOrder($order_id, $user_id)
                ) {
                    messageInModal(translate('systmess_error_sended_data_not_valid'));
                }
                // endregion User access

                // region Order
                if (
                    empty($order_id)
                    || empty($order = $this->orders->get_order($order_id))
                ) {
                    messageInModal(translate('systmess_error_sended_data_not_valid'));
                }

                // region Order step check
                if ('invoice_confirmed' !== $order['status_alias']) {
                    messageInModal(translate('systmess_error_sended_data_not_valid'));
                }
                // endregion Order step check
                // endregion Order

                // region Vars
                // region User international partners
                $international_shippers = arrayByKey(model('ishippers')->get_seller_shipper_ipartners($user_id), 'id_shipper');
                if (empty($international_shippers)) {
                    messageInModal(translate('systmess_error_order_ishipper_quotes_no_partners', ['{PARTNERS_PAGE_URL}' => __SITE_URL . 'shippers/my_partners']));
                }

                foreach ($international_shippers as &$shipper) {
                    $shipper['logo'] = __IMG_URL . getImage(
                        "public/img/ishippers_logo/{$shipper['shipper_logo']}",
                        'public/img/no_image/noimage-shipper-125.jpg'
                    );
                }
                // endregion User international partners

                // region Decoded information
                $purchase_order = with(\json_decode(arrayGet($order, 'purchase_order'), true), function ($purchase_order) {
                    return null === $purchase_order || !is_array($purchase_order) ? [] : $purchase_order;
                });
                $ishippers_quotes = array_filter(
                    array_map(
                        function ($quote) use ($international_shippers) {
                            if (!isset($international_shippers[$quote['id_shipper']])) {
                                return null;
                            }
                            $quote['shipper'] = $international_shippers[$quote['id_shipper']];

                            return $quote;
                        },
                        with(
                            \json_decode(arrayGet($order, 'ishippers_quotes'), true),
                            function ($ishippers_quotes) {
                                return null === $ishippers_quotes || !is_array($ishippers_quotes) ? [] : $ishippers_quotes;
                            }
                        )
                    )
                );
                // endregion Decoded information
                // endregion Vars

                // region Assign vars
                views()->assign([
                    'action'           => getUrlForGroup('order/ajax_order_operations/ishipper_quotes'),
                    'order'            => $order,
                    'ishippers'        => $international_shippers,
                    'purchase_order'   => $purchase_order,
                    'ishippers_quotes' => $ishippers_quotes,
                ]);
                // endregion Assign vars

                views()->display('new/order/popup_quote_request_from_view');

            break;
            case 'order_shipping_quotes':
                checkPermisionAjaxModal('buy_item,manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #1');
                }

                $data['order'] = $this->orders->get_order($id_order);

                if (!in_array($data['order']['status_alias'], ['invoice_confirmed'])) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #1');
                }

                $this->load->model('Shippers_Model', 'shippers');
                $data['order_quote_requests'] = $this->shippers->get_order_quote_requests_shippers($id_order);

                // ISHIPPERS - PARTNERS
                $this->load->model('Ishippers_Model', 'ishippers');
                $data['ishippers'] = arrayByKey($this->ishippers->get_seller_shipper_ipartners($data['order']['id_seller']), 'id_shipper');
                $data['ishippers_quotes'] = !empty($data['order']['ishippers_quotes']) ? json_decode($data['order']['ishippers_quotes'], true) : [];
                uasort($data['ishippers_quotes'], function ($a, $b) {
                    return ($a['amount'] <= $b['amount']) ? -1 : 1;
                });

                if ($data['order']['id_shipper'] > 0) {
                    $data['shipper_assigned'] = true;
                } else {
                    $data['shipper_assigned'] = false;
                }

                $this->view->assign($data);
                $this->view->display('new/order/popup_shipping_quotes_view');

            break;
            case 'order_shipping_quote_detail':
                checkPermisionAjaxModal('buy_item,manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #1');
                }

                $this->load->model('Shippers_Model', 'shippers');
                $shipping_quote = (int) $this->uri->segment(5);
                $data['request_details'] = $this->shippers->get_order_quote_request($shipping_quote);
                if (empty($data['request_details'])) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #2');
                }

                if ($data['request_details']['id_order'] != $id_order) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #3');
                }

                $data['shipper'] = $this->shippers->get_shipper_by_user($data['request_details']['id_shipper']);
                if (empty($data['shipper'])) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #4');
                }

                $order = $this->orders->get_order($id_order);
                if ($order['id_shipper'] > 0) {
                    $data['shipper_assigned'] = true;
                } else {
                    $data['shipper_assigned'] = false;
                }

                $this->view->assign($data);
                $this->view->display('new/order/shipping_quote_details_view');

            break;
            case 'order_ishipping_quote_detail':
                checkPermisionAjaxModal('buy_item,manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #1');
                }

                $data['order_info'] = $this->orders->get_order($id_order);
                $ishippers_quotes = !empty($data['order_info']['ishippers_quotes']) ? json_decode($data['order_info']['ishippers_quotes'], true) : [];
                $id_shipper = (int) $this->uri->segment(5);
                if (!isset($ishippers_quotes[$id_shipper])) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #2');
                }

                $data['ishipper'] = model('ishippers')->get_shipper($id_shipper);
                if (empty($data['ishipper'])) {
                    messageInModal(translate('systmess_error_sended_data_not_valid') . 'Code #3');
                }

                $data['ishippers_quote'] = $ishippers_quotes[$id_shipper];

                if ($data['order_info']['id_shipper'] > 0) {
                    $data['shipper_assigned'] = true;
                } else {
                    $data['shipper_assigned'] = false;
                }

                $this->view->assign($data);
                $this->view->display('new/order/ishipping_quote_details_view');

            break;
            case 'purchase_order':
                checkPermisionAjaxModal('manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                $data['id_order'] = $id_order;
                $data['order'] = $this->orders->get_full_order($id_order);
                $data['order']['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : [];
                $data['user_info'] = $this->user->getSimpleUser($id_user);
                $data['shipping_types'] =  arrayByKey($shippingTypesModel->findAllBy(['conditions' => ['isVisible' => 1]]), 'id_type');

                if (!empty($data['order']['purchase_order']['shipping_from'])) {
                    $data['shipping_from'] = $data['order']['purchase_order']['shipping_from'];
                } else {
                    $data['shipping_from'] = [
                        'country' => $data['user_info']['country'],
                        'state'   => $data['user_info']['state'],
                        'city'    => $data['user_info']['city'],
                        'zip'     => $data['user_info']['zip'],
                        'address' => $data['user_info']['address'],
                    ];
                }

                if (!empty($items = arrayGet($data, 'order.purchase_order.invoice.products', []))) {
                    $data['order']['purchase_order']['invoice']['ordered_items'] = array_filter($items, function ($item) {
                        return 'item' === $item['type'];
                    });
                    $data['order']['purchase_order']['invoice']['additional_items'] = array_filter($items, function ($item) {
                        return 'aditional' === $item['type'];
                    });
                }

                $data['countries'] = $this->country->get_countries();
                $data['states'] = $this->country->get_states((int) $data['shipping_from']['country']);
                $data['city_selected'] = $this->country->get_city((int) $data['shipping_from']['city']);
                $data['weightCalc'] = $this->_calculate_order_weight($data['order']);
                $data['block_info']['hr_tariff_number'] = model('user_guide')->get_user_guide_by_alias('hr_tariff_number');

                $this->view->assign($data);
                $this->view->display('new/order/po_form/form_view');

            break;
            case 'view_purchase_order':
                checkPermisionAjaxModal('buy_item,manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // GET ORDER DETAILS, PREPARE PO DETAILS ARRAY
                $data['order'] = $this->orders->get_full_order($id_order);
                $data['order']['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : [];

                if (!empty($items = arrayGet($data, 'order.purchase_order.invoice.products', []))) {
                    $data['order']['purchase_order']['invoice']['ordered_items'] = array_filter($items, function ($item) {
                        return 'item' === $item['type'];
                    });
                    $data['order']['purchase_order']['invoice']['additional_items'] = array_filter($items, function ($item) {
                        return 'aditional' === $item['type'];
                    });
                }
                $data['order']['purchase_order_timeline'] = with(json_decode(arrayGet($data, 'order.purchase_order_timeline'), true), function ($timeline) {
                    return !empty($timeline) && is_array($timeline) ? $timeline : [];
                });

                // GET SHIPPING TYPE DETAILS
                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);
                $data['shipping_type'] = $shippingTypesModel->findOne((int) $data['order']['shipment_type'], ['conditions' => ['isVisible' => 1]]);

                // CHECK FOR MEMBER BUTTONS ACCESS
                $data['show_seller_buttons'] = in_array($data['order']['status_alias'], ['purchase_order']) && have_right('manage_seller_orders') && !isExpiredDate(DateTime::createFromFormat('Y-m-d H:i:s', $data['order']['status_countdown']));
                $data['show_buyer_buttons'] = in_array($data['order']['status_alias'], ['purchase_order']) && have_right('buy_item') && !isExpiredDate(DateTime::createFromFormat('Y-m-d H:i:s', $data['order']['status_countdown']));
                $data['is_confirmed'] = !in_array($data['order']['status_alias'], ['new_order', 'purchase_order']);

                $this->view->assign($data);
                $this->view->display('new/order/po_form/details_view');

            break;
            case 'purchase_order_notes':
                checkPermisionAjaxModal('buy_item,manage_seller_orders');

                $id_user = privileged_user_id();
                if (!$this->orders->isMyOrder($id_order, $id_user)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['order'] = $this->orders->get_full_order($id_order);
                $data['order']['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : [];
                $data['order']['purchase_order_timeline'] = !empty($data['order']['purchase_order_timeline']) ? json_decode($data['order']['purchase_order_timeline'], true) : [];

                if (!empty($data['order']['purchase_order_timeline'])) {
                    usort($data['order']['purchase_order_timeline'], function ($t1, $t2) {
                        return $t1['date'] < $t2['date'];
                    });
                }

                $this->view->assign($data);
                $this->view->display('new/order/po_form/notes_view');

            break;
            case 'order_timeline':
                $data['order'] = $this->orders->get_full_order($id_order);
                if (empty($data['order'])) {
                    messageInModal(translate('systmess_error_order_doesnt_exist'));
                }

                if (!is_privileged('user', $data['order']['id_shipper'], 'manage_shipper_orders')
                    && !is_privileged('user', $data['order']['id_seller'], 'manage_seller_orders')
                    && !is_privileged('user', $data['order']['id_buyer'], 'buy_item')) {
                    messageInModal(translate('systmess_error_order_doesnt_exist'));
                }

                $this->view->assign($data);
                $this->view->display('new/order/order_timeline_view');

            break;
        }
    }

    public function invoice_pdf()
    {
        library('make_pdf')->order_invoice(620)->Output('Invoice_for_order_0000001234.pdf', 'I');
    }

    /**
     * @author Usinevici Alexandr
     *
     * @todo Remove [06.04.2022]
     * Not used
     *
     * @param mixed $document_info
     */
    // public function order_text_statuses()
    // {
    // 	// return;

    // 	$template = array(
    // 		'buyer' => array(
    // 			'text' =>  array(
    // 				'mandatory' => '',
    // 				'optional' => ''
    // 			),
    // 		),
    // 		'seller' => array(
    // 			'text' => array(
    // 				'mandatory' => '',
    // 				'optional' => ''
    // 			),
    // 		),
    // 		'shipper' => array(
    // 			'text' => array(
    // 				'mandatory' => '',
    // 				'optional' => ''
    // 			),
    // 		),
    // 		'ep_manager' => array(
    // 			'text' => array(
    // 				'mandatory' => '',
    // 				'optional' => ''
    // 			),
    // 		)
    // 	);

    // 	$this->load->model('Orders_Model', 'order');
    // 	$statuses_description = array(
    // 		'new_order' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer must wait for the seller to create a <strong>purchase order</strong>;</p>',
    // 					'optional' 	=> '<p>The buyer can upload, sign, or download the required documents for a successful transactions. The buyer can do this at any step of the order process.</p>
    // 									<p>The buyer can ask for the order to be cancelled by clicking the <strong>Cancel Order Request</strong> button. This will be approved or not approved by the Order Manager.</p>'
    // 				),
    // 				'video' => '1_new_order.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller has to create <strong>Purchase Order</strong> and add information: <br>- ship from address; <br>- shipping type; <br>- available area for delivery; <br>- number of the packaging days; <br>- any additional elements (boxes, pallets, etc.)</p>',
    // 					'optional' 	=> '<p>The seller can upload, sign or download the required documents for a successful transaction. This process is available in all order steps.</p>
    // 									<p>The seller can ask for time extension.</p>
    // 									<p>The seller can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>'
    // 				),

    // 				'video' => '1_new_order.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => null
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>'
    // 				),
    // 			)
    // 		),

    // 		'purchase_order' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 							'mandatory' => '<p>The buyer has to confirm the <strong>Purchase Order</strong> and the order’s status will be changed to <strong>PO Confirmed</strong>.</p>',
    // 							'optional' => '<p>The buyer can negotiate with the seller about the <strong>Purchase Order</strong> using the <strong>Discuss Purchase Order</strong> button until they come to an agreement.</p>
    // 											<p>The buyer can ask for time extension.</p>
    // 											<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 						),
    // 				'video' => '2_purchase_order.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 							'optional' => '<p>The seller has to edit and re-send the <strong>Purchase Order</strong> until the buyer confirms it.</p>
    // 											<p>The seller can ask for time extension.</p>
    // 											<p>The seller can ask for cancellation of the order by clicking the <strong>"Cancel Order Request"</strong> button, which is approved or disapproved by the Order Manager. </p>',
    // 						),
    // 				'video' => '2_purchase_order.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => null
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>'
    // 				),
    // 			)
    // 		),

    // 		'purchase_order_confirmed' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer must wait for the seller to <strong>create an invoice</strong>.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for a time extension.</p>
    // 									<p>The buyer can ask for the order to be cancelled by clicking the <strong>Cancel Order Request</strong> button. This will be approved or not approved by the Order Manager.</p>',
    // 				),
    // 				'video' => '3_purchase_order_confirmed.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller has to create the <strong>Invoice</strong> based on <strong>PO</strong>. </p>',
    // 					'optional' 	=> '<p>The seller can ask for time extension.</p>
    // 									<p>The seller can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager. </p>',
    // 				),
    // 				'video' => '3_purchase_order_confirmed.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => null
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>'
    // 				),
    // 			)
    // 		),

    // 		'invoice_sent_to_buyer' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer has to <strong>confirm the invoice</strong> and the order’s status will be changed into <strong>Invoice confirmed</strong>.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for time extension.</p>
    // 									<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '4_invoice_sent_to_buyer.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller is waiting for the buyer to <strong>confirm the invoice</strong>.</p>',
    // 					'optional' 	=> '<p>The seller can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '4_invoice_sent_to_buyer.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => null
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>'
    // 				),
    // 			)
    // 		),

    // 		'invoice_confirmed' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer should wait for bids from <strong>Export Portal Logistics (EPL)</strong> or <strong>International Freight Forwarders\' Rates</strong> from the seller.</p>
    // 									<p>The buyer has to choose the most suitable shipping offer, considering the price and delivery time. Then, the order’s status will be changed into <strong>Freight Forwarder Assigned</strong>.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for time extension.</p>
    // 									<p>The buyer can agree with time extension. If not, the seller can ask for order cancellation.</p>
    // 									<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '5_invoice_confirmed.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller can add <strong>International Freight Forwarders\' Rates</strong></p>
    // 									<p>The seller has to wait for the buyer to assign a freight forwarder based on bids from <strong>Export Portal Logistics (EPL)</strong> or <strong>International Freight Forwarders\' Rates</strong>.</p>',
    // 					'optional' 	=> '<p>The seller can agree with time extension requested by the buyer. If not, the seller can ask for order cancellation.</p>
    // 									<p>The seller can ask for order cancellation by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '5_invoice_confirmed.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarders can see the order’s full information and can make delivery bids. To place a bid go to the <strong>Upcoming Orders</strong>’ page.</p>
    // 									<p>If you already placed a bid, please wait for the buyer’s approval. To see your bids go to  Orders’ Bids.</p>',
    // 				),
    // 				'video' => '5_invoice_confirmed.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The system sends automatically a notification to all parties for approval about time extension for 3 days. </p>
    // 									<p>The Order Manager analyses the request for cancellation <strong>from seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation <strong>from buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>'
    // 				),
    // 			)
    // 		),

    // 		'shipper_assigned' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer can see the <strong>Order Contract</strong> (please check Order Documents) and sign it.</p>
    // 									<p>The buyer needs to request the <strong>Order Contract</strong> to be signed by all the parties before paying.</p>
    // 									<p>The buyer should start the order’s payment process by clicking <strong>Bills List</strong> button. After the payment, the order will get the <strong>Payment processing</strong> status.</p>',
    // 					'optional'  => '<p>The buyer can ask for time extension.</p>
    // 									<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>
    // 									<p>If the buyer doesn\'t make the payment before the due date, or doesn\'t extend the time, the order can be canceled by the Order Manager.</p>',
    // 				),
    // 				'video' => '6_shipper_assigned.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller must message the buyer to request the <strong>Order Contract</strong></p>
    // 									<p>Once the contract is received, the seller must download the contract, sign it, and reupload the signed document.</p>
    // 									<p>After the contract is signed by all the parties, the buyer starts the payment process.</p>
    // 									<p>The seller is waiting for the buyer to start the order’s payment process.</p>
    // 									<p>The seller is waiting for the Order Manager to confirm the payment.</p>',
    // 					'optional' 	=> '<p>The seller can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or not approved by the Order Manager.</p>',
    // 				),
    // 				'video' => '6_shipper_assigned.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder can view the new order which has been approved by the buyer.</p>
    // 									<p>The freight forwarder must request the <strong>Order Contract</strong> from the buyer and sign it. After all the parties have signed the contract, the buyer will start the payment process.</p>
    // 									<p>The freight forwarder must wait for the buyer to start the payment process.</p>
    // 									<p>The freight forwarder must then wait for the Order Manager to confirm the payment.</p>',
    // 					'optional' 	=> '<p>The freight forwarder can upload, sign, or download the required documents for a successful transaction. This process is available throughout the order process.</p>',
    // 				),
    // 				'video' => '6_shipper_assigned.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>If the buyer doesn\'t make the payment before due date, or doesn\'t extend the time, Order Manager can cancel the order with the status <strong>No Payment</strong>.</p>'
    // 				),
    // 			)
    // 		),

    // 		'payment_processing' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer should <strong>pay all the bills</strong> and wait until the Order Manager confirms all the payments.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for time extension.</p>
    // 									<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager. </p>
    // 									<p>If the buyer doesn\'t make the payment before due date, or doesn\' extend the time, the Order Manager can cancel the order.</p>',
    // 				),
    // 				'video' => '7_payment_processing.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller is waiting for the buyer to pay the order and confirmation from the Order Manager.</p>',
    // 					'optional' 	=> '<p>The seller can <strong>Report a problem</strong> for the order.</p>',
    // 				),
    // 				'video' => '7_payment_processing.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder is waiting for the buyer to pay for the order and confirmation from Order Manager.</p>',
    // 				),
    // 				'video' => '7_payment_processing.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The Order Manager should verify all the bills and mark the order as paid. The order will get the <strong>Order paid</strong> status.</p>',
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request of <strong>reporting a problem</strong> for the order <strong>from seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation <strong>from buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>If the buyer doesn\'t make the payment before due date, or doesn\'t extend the time, Order Manager can cancel the order with the status <strong>No Payment</strong>.</p>
    // 									<p>Refund is available</p>'
    // 				),
    // 			)
    // 		),

    // 		'order_paid' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer is waiting for Order Manager to confirm the payment.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '8_order_paid.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller is waiting for the Order Manager to confirm the payment.</p>',
    // 					'optional' 	=> '<p>The seller can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '8_order_paid.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder is waiting for the Order Manager to confirm the payment.</p>',
    // 				),
    // 				'video' => '8_order_paid.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The admin makes sure the order was completely paid and confirms the payment by clicking the <strong>"Confirm total order payment"</strong> button. After the confirmation the order will get the <strong>"Payment confirmed"</strong> status.</p>',
    // 					'optional' 	=> '<p>The admin analyses the request for cancellation <strong>from seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>"Canceled by seller"</strong> status.</p>
    // 									<p>The admin analyses the request for cancellation <strong>from buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>"Canceled by buyer"</strong> status.</p>
    // 									<p>Refund is available</p>'
    // 				),
    // 			)
    // 		),

    // 		'payment_confirmed' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer is waiting for the seller to prepare the products for shipping.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for cancellation of the order by clicking the <strong>Cancel Order Request</strong> button, which is approved or disapproved by the Order Manager.</p>',
    // 				),
    // 				'video' => '9_payment_confirmed.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller clicks the <strong>Start preparing item(s) for shipping</strong> button. After that the order will get the <strong>Preparing for shipping</strong> status.</p>',
    // 					'optional' 	=> '<p>The seller can ask for time extension.</p>
    // 									<p>The seller can <strong>Report a problem</strong> for the order.</p>',
    // 				),
    // 				'video' => '9_payment_confirmed.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder is waiting for the seller to prepare the products for shipping.</p>',
    // 				),
    // 				'video' => '9_payment_confirmed.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request of <strong>reporting a problem</strong> for the order <strong>from seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request for cancellation <strong>from buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>
    // 									<p>Refund is available</p>'
    // 				),
    // 			)
    // 		),

    // 		'preparing_for_shipping' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer is waiting for the seller to prepare the products for delivering and transfer them to the shipping company. After the tracking information will be added, the order will get the <strong>Shipping in progress</strong> status.</p>',
    // 					'optional' 	=> '<p>The buyer can <strong>Report a problem</strong> for the order.</p>',
    // 				),
    // 				'video' => '10_preparing_for_shipping.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller prepares the products for delivering and transfers them to the shipping company.</p>
    // 									<p>If the buyer has assigned an <strong>International Freight Forwarder</strong> to the order, the seller should <strong>fill in the tracking information</strong> by clicking the <strong>Finish packaging</strong> button. Otherwise, the <strong>Export Portal freight forwarder</strong> will be responsible for adding the tracking information. The order will get the <strong>Shipping in progress</strong> status.</p>',
    // 					'optional' 	=> '<p>The seller can ask for time extension.</p>
    // 									<p>The seller can <strong>Report a problem</strong> for the order.</p>',
    // 				),
    // 				'video' => '10_preparing_for_shipping.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder receives the product and fills in the <strong>Tracking info</strong>. The order will get the <strong>Shipping in progress</strong> status.</p>',
    // 					'optional' 	=> ''
    // 				),
    // 				'video' => '10_preparing_for_shipping.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager analyses the request of <strong>reporting a problem</strong> for the order from <strong>seller</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by seller</strong> status.</p>
    // 									<p>The Order Manager analyses the request of <strong>reporting a problem</strong> for the order from <strong>buyer</strong> and approve or disapprove it. Once approved, the order changes into <strong>Canceled by buyer</strong> status.</p>
    // 									<p>The Order Manager can cancel the order in case of time expiration and nobody asks for its extension. The order changes into <strong>Canceled by EP</strong> status.</p>
    // 									<p>Refund is available</p>'
    // 				),
    // 			)
    // 		),

    // 		'shipping_in_progress' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'optional' => '<p>The buyer can ask for a <strong>Dispute</strong> in case the delivery exceeds the indicated deadline.</p>',
    // 				),
    // 				'video' => '11_shipping_in_progress.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller should wait until the freight forwarder delivers the products and the buyer confirms the delivery.</p>',
    // 					'optional' 	=> '<p>The seller is involved in the <strong>Dispute</strong></p>
    // 									<p>The seller can ask for time extension.</p>',
    // 				),
    // 				'video' => '11_shipping_in_progress.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder should add pickup due date by clicking the <strong>Ready for pickup</strong> button. The order changes into <strong>Ready for pickup</strong> status. </p>',
    // 					'optional' 	=> '<p>The freight forwarder is involved in the <strong>Dispute</strong>.</p>
    // 									<p>The freight forwarder can ask for time extension, if the buyer has selected the Export Portal freight forwarder.</p>',
    // 				),
    // 				'video' => '11_shipping_in_progress.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'optional' 	=> '<p>The Order Manager should analyze the reason of the <strong>Dispute</strong>. If the reason is justified the admin starts the dispute, thus all the parties of the order will be notified about the new dispute. The order cannot get the next status until the dispute is not resolved.</p>
    // 									<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager can cancel the order based on dispute. The order changes into <strong>Canceled by EP</strong> status.</p>
    // 									<p>Refund is available based on dispute.</p>'
    // 				),
    // 			)
    // 		),

    // 		'shipping_ready_for_pickup' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The buyer should click the <strong>Confirm delivery</strong> button to confirm the delivery. The order will get the <strong>Shipping completed</strong> status.</p>',
    // 				),
    // 				'video' => '12_shipping_ready_for_pickup.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller should wait until the freight forwarder delivers the products and the buyer confirms the delivery. </p>',
    // 				),
    // 				'video' => '12_shipping_ready_for_pickup.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder should mark the order as delivered.</p>',
    // 				),
    // 				'video' => '12_shipping_ready_for_pickup.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The admin can move the order to the <strong>Shipping completed</strong> status, even if the buyer doesn\'t click the <strong>Confirm delivery</strong> button, but the time was expired and he gets proofs from freight forwarder or seller that the buyer received the products.</p>',
    // 				),
    // 			)
    // 		),

    // 		'shipping_completed' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>If the products received have no defects and correspond to the order, the buyer should confirm it by clicking the <strong>Order completed</strong> button.</p>',
    // 					'optional' 	=> '<p>The buyer can ask for a <strong>Dispute</strong> against the whole order or on the specific product from the order, if he detects any problems.</p>
    // 									<p>The buyer can ask for time extension.</p>',
    // 				),
    // 				'video' => '13_shipping_completed.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The seller should wait until the buyer marks order as completed. </p>',
    // 					'optional' 	=> '<p>The seller is involved in the <strong>Dispute</strong></p>',
    // 				),
    // 				'video' => '13_shipping_completed.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The freight forwarder should wait until the buyer marks order as completed.</p>',
    // 					'optional' 	=> '<p>The freight forwarder is involved in the <strong>Dispute</strong>.</p>',
    // 				),
    // 				'video' => '13_shipping_completed.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The Order Manager can move the order to the <strong>Order completed</strong> status, even if the buyer doesn\'t click the <strong>Order completed</strong> button, but the time was expired and he gets proofs from freight forwarder or seller that the buyer received the products.</p>',
    // 					'optional' 	=> '<p>The Order Manager should analyze the reason of the <strong>Dispute</strong>. If the reason is justified the admin starts the dispute, thus all the parties of the order will be notified about the new dispute. The order cannot get the next status until the dispute is not resolved.</p>
    // 									<p>The Order Manager analyses the requests for time extension and approve or disapprove them.</p>
    // 									<p>The Order Manager can cancel the order based on dispute. The order changes into <strong>Canceled by EP</strong> status.</p>
    // 									<p>Refund is available based on dispute.</p>'
    // 				),
    // 			)
    // 		),

    // 		'order_completed' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order is completed.</p>',
    // 				),
    // 				'video' => '14_order_completed.gif'
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order is completed.</p>',
    // 				),
    // 				'video' => '14_order_completed.gif'
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order is completed.</p>',
    // 				),
    // 				'video' => '14_order_completed.gif'
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order is completed.</p>'
    // 				),
    // 			)
    // 		),

    // 		'late_payment' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The payment deadline has expired.</p>'
    // 				),
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The payment deadline has expired.</p>'
    // 				),
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The payment deadline has expired.</p>'
    // 				),
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The payment deadline has expired.</p>'
    // 				),
    // 			)
    // 		),

    // 		'canceled_by_buyer' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by buyer.</p>'
    // 				),
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by buyer.</p>'
    // 				),
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by buyer.</p>'
    // 				),
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by buyer.</p>'
    // 				),
    // 			)
    // 		),

    // 		'canceled_by_seller' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by seller.</p>'
    // 				),
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by seller.</p>'
    // 				),
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by seller.</p>'
    // 				),
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by seller.</p>'
    // 				),
    // 			)
    // 		),

    // 		'canceled_by_ep' => array(
    // 			'buyer' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by EP Order Manager</p>'
    // 				),
    // 			),
    // 			'seller' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by EP Order Manager</p>'
    // 				),
    // 			),
    // 			'shipper' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by EP Order Manager</p>'
    // 				),
    // 			),
    // 			'ep_manager' => array(
    // 				'text' => array(
    // 					'mandatory' => '<p>The order has been cancelled by EP Order Manager</p>'
    // 				),
    // 			)
    // 		),
    // 	);

    // 	foreach($statuses_description as $key => $statuses_description_item){
    // 		$encode = str_replace(array('\t','\n','\r'), '', json_encode($statuses_description_item));
    // 		$this->order->update_order_status_by_alias($key, array('description' => $encode));
    // 	}

    // 	echo 'Text where updated, ;)';
    // }

    /**
     * @author Usinevici Alexandr
     *
     * @todo Remove [06.04.2022]
     * Not used
     */
    // public function test()
    // {
    // 	$ishippers_validator_rules = array(
    // 		array(
    // 			'field' => 'f1',
    // 			'label' => 'F1',
    // 			'rules' => array('required' => '', 'matchFromToValue[f2]' => 'Field F1 musth contain value less than F2')
    // 		),
    // 		array(
    // 			'field' => 'f2',
    // 			'label' => 'F2',
    // 			'rules' => array('required' => '', 'matchToFromValue[f1]' => 'Field F2 musth contain value greater than F1')
    // 		)
    // 	);

    // 	$this->validator->validate_data = array(
    // 		'f1' => 10,
    // 		'f2' => 5
    // 	);

    // 	$this->validator->set_rules($ishippers_validator_rules);
    // 	if(!$this->validator->validate()){
    // 		jsonResponse($this->validator->get_array_errors());
    // 	}

    // 	// $id_order = (int) $this->uri->segment(3);
    // 	// $order = model('orders')->get_full_order($id_order);
    // 	// $purchase_order = !empty($order['purchase_order']) ? json_decode($order['purchase_order'], true) : array();

    // 	// $add_contract = $this->_assign_order_documents(array(
    // 	// 	'id_user' => 2,
    // 	// 	'id_order' => 515,
    // 	// 	'user_type' => 'buyer',
    // 	// 	'document_type' => 'contract'
    // 	// ));

    // 	// $add_invoice = $this->_assign_order_documents(array(
    // 	// 	'id_user' => 2,
    // 	// 	'id_order' => 515,
    // 	// 	'user_type' => 'buyer',
    // 	// 	'document_type' => 'invoice'
    // 	// ));
    // }

    public function _assign_order_documents($document_info = [])
    {
        if (!isset($document_info['id_user'], $document_info['id_order'], $document_info['user_type'], $document_info['document_type'])) {
            return 'Code #ADD-ORDER-DOCUMENT-1';
        }

        $httpClient = new Client(['base_uri' => config('env.EP_DOCS_HOST', 'http://localhost')]);
        $configs = (new \App\Plugins\EPDocs\Configuration())->setHttpOrigin(config('env.EP_DOCS_REFERRER'))->setDefaultUserId(config('env.EP_DOCS_ADMIN_SALT'));
        $auth = new Auth($httpClient, new Bearer(), new JwtTokenStorage($httpClient, new JwtCredentials(
            config('env.EP_DOCS_API_USERNAME'),
            config('env.EP_DOCS_API_SECRET')
        )));
        $restClient = new RestClient($httpClient, $auth, $configs);

        $userId = (int) $document_info['id_user'];
        $orderId = (int) $document_info['id_order'];
        $sellerId = (int) ($document_info['id_seller'] ?? null) ?: null;
        $shipperId = (int) ($document_info['id_shipper'] ?? null) ?: null;
        $orderNumber = orderNumber($orderId);
        $documentType = $document_info['document_type'];

        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        /** @var TinyMVC_Library_Make_Pdf $pdfMaker */
        $pdfMaker = library(TinyMVC_Library_Make_Pdf::class);
        $fileStorage = new FileStorage(
            $restClient,
            config('env.EP_DOCS_REFERRER', 'http://localhost'),
            config('env.EP_DOCS_ADMIN_SALT'),
            library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
        );
        $createOrder = new CreateOrderContract($envelopes, new ContractMakerAdapter($pdfMaker), $fileStorage, $this->notifier);
        $createInvoice = new CreateOrderInvoice($envelopes, new InvoiceMakerAdapter($pdfMaker), $fileStorage, $this->notifier);

        try {
            switch ($documentType) {
                case 'contract':
                    $createOrder->__invoke(
                        (new CreateOrderContractMessage(
                            config('env.EP_DOCS_ADMIN_SALT'),
                            $orderId,
                            $sellerId,
                            $userId,
                            $shipperId,
                            "Contract for the Order: {$orderNumber}",
                            'Contract',
                            'This document is a Contract.',
                            "contract_for_order_{$orderId}.pdf"
                        ))->withAccessRulesList(['monitor_documents'])
                    );

                    break;
                case 'invoice':
                    $createInvoice->__invoke(
                        (new CreateOrderInvoiceMessage(
                            config('env.EP_DOCS_ADMIN_SALT'),
                            $orderId,
                            $userId,
                            $sellerId,
                            "Invoice for the Order: {$orderNumber}",
                            'Invoice',
                            'This document is a Invoice.',
                            "invoice_for_order_{$orderId}.pdf"
                        ))->withAccessRulesList(['monitor_documents'])
                    );

                    break;
            }
        } catch (Exception $e) {
            return 'Code #ADD-ORDER-DOCUMENT-3';
        }

        return true;
    }

    private function ordersEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'order/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function ordersAll($data)
    {
        views(['new/header_view', 'new/order/index_view', 'new/footer_view'], $data);
    }

    private function _load_main()
    {
        $this->load->model('Orders_model', 'orders');
        $this->load->model('User_model', 'user');
    }

    private function _validate_purchase_order(array $order, &$errors = [])
    {
        // region Base fields validation
        /** @var Validator $validator */
        $validator = library('validator');
        $validator_rules = [
            [
                'field' => 'id_order',
                'label' => 'Order details',
                'rules' => ['required' => '', 'natural'  => ''],
            ],
            [
                'field' => 'port_country',
                'label' => 'Country',
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'states',
                'label' => 'State / Region',
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'port_city',
                'label' => 'City',
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'zip',
                'label' => 'ZIP',
                'rules' => ['required' => '', 'zip_code' => '', 'max_len[20]' => ''],
            ],
            [
                'field' => 'address',
                'label' => 'Address',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'packaging',
                'label' => 'Estimate time for packaging',
                'rules' => ['required' => '', 'integer' => '', 'min[' . config('order_min_estimate_time_for_packaging', 1) . ']' => '', 'max[' . config('order_max_estimate_time_for_packaging', 180) . ']' => ''],
            ],
            [
                'field' => 'delivery_area',
                'label' => 'Available area for delivering',
                'rules' => ['required' => '', 'integer' => '', 'min[0]' => '', 'max[' . config('order_min_available_area_for_delivery', 40000) . ']' => ''],
            ],
            [
                'field' => 'shipment_type',
                'label' => 'Type of shipment',
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'invoice_due_date',
                'label' => 'Invoice Due date',
                'rules' => ['required' => '', 'valid_date[m/d/Y]' => '', 'valid_date_future[m/d/Y]' => ''],
            ],
            [
                'field' => 'po_number',
                'label' => 'PO number',
                'rules' => ['required' => '', 'alpha_numeric' => '', 'max_len[12]' => ''],
            ],
            [
                'field' => 'discount',
                'label' => 'Discount',
                'rules' => ['required' => '', 'natural' => '', 'min[0]' => '', 'max[99]' => ''],
            ],
            [
                'field' => 'notes',
                'label' => 'Notes',
                'rules' => ['max_len[1000]' => ''],
            ],
            [
                'field' => 'package',
                'label' => 'Box/Package sizes',
                'rules' => [
                    'required' => '',
                    function ($attribute, $value, $fail) {
                        $package = arrayGet($_POST, 'package');
                        if (!is_array($package)) {
                            $fail('Box/Package sizes details are required.');
                        }
                    },
                ],
            ],
        ];

        $validator->set_rules($validator_rules);
        if (!$validator->validate()) {
            $errors = array_merge($errors, $validator->get_array_errors());
        }

        $validator->reset_postdata();
        $validator->clear_array_errors();
        // endregion Base fields validation

        // region Box details validation
        $validator->validate_data = $this->_prepare_order_package();
        $package_validator_rules = [
            [
                'field' => 'width',
                'label' => 'Box/Package Width (cm)',
                'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
            ],
            [
                'field' => 'height',
                'label' => 'Box/Package Height (cm)',
                'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
            ],
            [
                'field' => 'length',
                'label' => 'Box/Package Length (cm)',
                'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
            ],
            [
                'field' => 'weight',
                'label' => 'Box/Package Weight (kg)',
                'rules' => ['required' => '', 'float' => '', 'min[0.001]' => '', 'max[50000000]' => ''],
            ],
        ];
        $validator->set_rules($package_validator_rules);
        if (!$validator->validate()) {
            $errors = array_merge($errors, $validator->get_array_errors());
        }

        $validator->reset_postdata();
        $validator->clear_array_errors();
        // endregion Box details validation

        // region New items validation
        if (!empty($_POST['po_items'])) {
            foreach ($_POST['po_items'] as $index => $po_item) {
                $key = $index + 1;
                $po_items_validation_rules = [
                    [
                        'field' => 'title',
                        'label' => "Title of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'valide_title' => '', 'max_len[250]' => ''],
                    ],
                    [
                        'field' => 'quantity',
                        'label' => "Quantity of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'is_natural_no_zero' => '', 'min[1]' => '', 'max[999999]' => ''],
                    ],
                    [
                        'field' => 'unit_price',
                        'label' => "Unit price of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'positive_number' => '', 'min[0]' => '', 'max[999999]' => ''],
                    ],
                    [
                        'field' => 'hs_code',
                        'label' => "H.S. Code of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'hs_tarif_number' => '', 'max_len[13]' => ''],
                    ],
                    [
                        'field' => 'country_abr',
                        'label' => "Origin country of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'max_len[2]' => ''],
                    ],
                    [
                        'field' => 'width',
                        'label' => "Width of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'height',
                        'label' => "Height of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'length',
                        'label' => "Length of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'weight',
                        'label' => "Weight of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[0.001]' => '', 'max[500000]' => ''],
                    ],
                ];
                $validator->validate_data = $po_item;
                $validator->set_rules($po_items_validation_rules);
                if (!$validator->validate()) {
                    $errors = array_merge($errors, array_values($validator->get_array_errors()));
                }

                $validator->reset_postdata();
                $validator->clear_array_errors();
            }
        }
        // endregion New items validation

        // region New items validation
        if (!empty($_POST['new_items'])) {
            foreach ($_POST['new_items'] as $index => $new_item) {
                $key = $index + 1;
                $new_items_validation_rules = [
                    [
                        'field' => 'title',
                        'label' => "Title of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'valide_title' => '', 'max_len[250]' => ''],
                    ],
                    [
                        'field' => 'quantity',
                        'label' => "Quantity of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'is_natural_no_zero' => '', 'min[1]' => '', 'max[999999]' => ''],
                    ],
                    [
                        'field' => 'unit_price',
                        'label' => "Unit price of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'positive_number' => '', 'min[0]' => '', 'max[999999]' => ''],
                    ],
                    [
                        'field' => 'hs_code',
                        'label' => "H.S. Code of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'hs_tarif_number' => '', 'max_len[13]' => ''],
                    ],
                    [
                        'field' => 'country_abr',
                        'label' => "Origin country of the additional item nr. {$key}",
                        'rules' => ['required' => '', 'max_len[2]' => ''],
                    ],
                    [
                        'field' => 'width',
                        'label' => "Width of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'height',
                        'label' => "Height of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'length',
                        'label' => "Length of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[1]' => '', 'max[5000]' => ''],
                    ],
                    [
                        'field' => 'weight',
                        'label' => "Weight of the additional item nr. {$key} ",
                        'rules' => ['required' => '', 'float' => '', 'min[0.001]' => '', 'max[500000]' => ''],
                    ],
                ];
                $validator->validate_data = $new_item;
                $validator->set_rules($new_items_validation_rules);
                if (!$validator->validate()) {
                    $errors = array_merge($errors, array_values($validator->get_array_errors()));
                }

                $validator->reset_postdata();
                $validator->clear_array_errors();
            }
        }
        // endregion New items validation

        // region Bill percentage validation
        if ('po' === $order['order_type'] && !empty($_POST['bill'])) {
            $total_amount = 0;
            foreach ($_POST['bill'] as $index => $post_bill) {
                $key = $index + 1;
                $bill_validation_rules = [
                    [
                        'field' => 'amount',
                        'label' => "Amount of bill nr. {$key}",
                        'rules' => ['required' => '', 'positive_number' => '', 'min[1]' => ''],
                    ],
                    [
                        'field' => 'due_date',
                        'label' => "Pay in N days for bill nr. {$key}",
                        'rules' => ['required' => '', 'is_natural_no_zero' => '', 'min[1]' => '', 'max[30]' => ''],
                    ],
                    [
                        'field' => 'note',
                        'label' => "Note of bill nr. {$key}",
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                ];

                $validator->validate_data = $post_bill;
                $validator->set_rules($bill_validation_rules);
                if (!$validator->validate()) {
                    $errors = array_merge($errors, array_values($validator->get_array_errors()));
                } else {
                    $total_amount += (float) $post_bill['amount'];
                }

                $validator->reset_postdata();
                $validator->clear_array_errors();
            }

            $order_amount = $this->_calculate_order_amount($order);
            $final_amount = minusPercent($order_amount, normalize_discount($_POST['discount']));

            if (!compareFloatNumbers($final_amount, $total_amount, '=')) {
                $errors[] = translate('systmess_error_order_purchase_order_not_valid_amount');
            }
        }
        // endregion Bill percentage validation

        return empty($errors);
    }

    private function _prepare_ordered_products(array $order)
    {
        $products = [];
        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM ITEMS ORDERED
        foreach ($order['ordered'] as $key => $item) {
            $products[] = [
                'id_item'          => $item['id_item'],
                'id_ordered_item'  => $item['id_ordered_item'],
                'id_snapshot'      => $item['id_snapshot'],
                'type'             => 'item',
                'name'             => $item['title'],
                'unit_price'       => $item['price_ordered'],
                'quantity'         => $item['quantity_ordered'],
                'total_price'      => floatval($item['price_ordered'] * $item['quantity_ordered']),
                'detail_ordered'   => $item['detail_ordered'],
                'item_weight'      => $item['item_weight'],
                'item_length'      => $item['item_length'],
                'item_width'       => $item['item_width'],
                'item_height'      => $item['item_height'],
                'hs_tariff_number' => $item['hs_tariff_number'],
                'country_abr'      => $item['country_abr'],
                'image'            => $item['main_image'],
                'reviews_count'    => $item['snapshot_reviews_count'],
                'rating'           => $item['snapshot_rating'],
            ];
        }

        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM NEW INVOICE ITEMS
        if (!empty($_POST['po_items'])) {
            foreach ($_POST['po_items'] as $key => $po_item) {
                $products[] = [
                    'type'             => 'aditional',
                    'name'             => cleanInput($po_item['title']),
                    'unit_price'       => floatval($po_item['unit_price']),
                    'quantity'         => intval($po_item['quantity']),
                    'total_price'      => floatval($po_item['unit_price'] * $po_item['quantity']),
                    'item_weight'      => $po_item['weight'],
                    'item_length'      => $po_item['length'],
                    'item_width'       => $po_item['width'],
                    'item_height'      => $po_item['height'],
                    'hs_tariff_number' => $po_item['hs_code'],
                    'country_abr'      => $po_item['country_abr'],
                ];
            }
        }

        if (!empty($_POST['new_items'])) {
            foreach ($_POST['new_items'] as $key => $new_item) {
                $products[] = [
                    'type'             => 'aditional',
                    'name'             => cleanInput($new_item['title']),
                    'unit_price'       => floatval($new_item['unit_price']),
                    'quantity'         => intval($new_item['quantity']),
                    'total_price'      => floatval($new_item['unit_price'] * $new_item['quantity']),
                    'item_weight'      => $new_item['weight'],
                    'item_length'      => $new_item['length'],
                    'item_width'       => $new_item['width'],
                    'item_height'      => $new_item['height'],
                    'hs_tariff_number' => $new_item['hs_code'],
                    'country_abr'      => $new_item['country_abr'],
                ];
            }
        }

        return $products;
    }

    private function _calculate_order_amount(array $order)
    {
        $amount = 0;
        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM ITEMS ORDERED
        foreach ($order['ordered'] as $item) {
            $amount += floatval($item['quantity_ordered'] * $item['price_ordered']);
        }

        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM NEW INVOICE ITEMS
        if (!empty($_POST['po_items'])) {
            foreach ($_POST['po_items'] as $po_item) {
                $amount += floatval($po_item['quantity'] * $po_item['unit_price']);
            }
        }

        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM NEW INVOICE ITEMS
        if (!empty($_POST['new_items'])) {
            foreach ($_POST['new_items'] as $new_item) {
                $amount += floatval($new_item['quantity'] * $new_item['unit_price']);
            }
        }

        return $amount;
    }

    private function _calculate_order_weight(array $order)
    {
        $total_weight = 0;
        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM ITEMS ORDERED
        foreach ($order['ordered'] as $item) {
            $total_weight += $item['item_weight'] * $item['quantity_ordered'];
        }

        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM NEW INVOICE ITEMS
        if (!empty($_POST['po_items'])) {
            foreach ($_POST['po_items'] as $po_item) {
                $total_weight += $po_item['weight'] * intval($po_item['quantity']);
            }
        }

        // PREPARING PRODUCTS LIST FOR THE INVOICE - FROM NEW INVOICE ITEMS
        if (!empty($_POST['new_items'])) {
            foreach ($_POST['new_items'] as $new_item) {
                $total_weight += $new_item['weight'] * intval($new_item['quantity']);
            }
        }

        return $total_weight;
    }

    private function _prepare_order_package()
    {
        return [
            'width'  => arrayGet($_POST, 'package.width'),
            'height' => arrayGet($_POST, 'package.height'),
            'length' => arrayGet($_POST, 'package.length'),
            'weight' => arrayGet($_POST, 'package.weight'),
        ];
    }

    private function sendEmailCancelledBySeller($params)
    {
        /** @var Seller_Companies_Model $sellerCompaniesModel */
        $sellerCompaniesModel = model(Seller_Companies_Model::class);

        $sellerCompany = $sellerCompaniesModel->findOneBy([
            'conditions'    => [
                'userId'    => (int) $params['idSeller'],
            ],
        ]);
        foreach ($params['users'] as $user) {
            try {
                /** @var MailerInterface $mailer */
                $mailer = container()->get(MailerInterface::class);
                $mailer->send(
                    (new EmailOrderCancelledBySeller($params['orderNumber'], $user['fname'] . ' ' . $user['lname'], $sellerCompany['name_company'], $params['reason'], $params['ordersLink']))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                        ->subjectContext([
                            '[orderNumber]' => $params['orderNumber'],
                        ])
                );
            } catch (\Throwable $th) {
                jsonResponse(translate('email_has_not_been_sent'));
            }
        }
    }

    private function sendEmailCancelledByBuyer($params)
    {
        foreach ($params['users'] as $user) {
            try {
                /** @var MailerInterface $mailer */
                $mailer = container()->get(MailerInterface::class);
                $mailer->send(
                    (new EmailOrderCancelledByBuyer($params['orderNumber'], $user['fname'] . ' ' . $user['lname'], $params['reason'], $params['ordersLink']))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                        ->subjectContext([
                            '[orderNumber]' => $params['orderNumber'],
                        ])
                );
            } catch (\Throwable $th) {
                jsonResponse(translate('email_has_not_been_sent'));
            }
        }
    }

    private function sendEmailCancelledByManager($params)
    {
        foreach ($params['users'] as $user) {
            try {
                /** @var MailerInterface $mailer */
                $mailer = container()->get(MailerInterface::class);
                $mailer->send(
                    (new EmailOrderCancelledByManager(
                        $params['orderNumber'],
                        "{$user['fname']} {$user['lname']}",
                        "{$params['users']['buyer']['fname']} {$params['users']['buyer']['lname']}",
                        $params['reason'],
                        $params['ordersLink'],
                    ))
                    ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                    ->subjectContext([
                        '[orderNumber]' => $params['orderNumber'],
                    ])
                );
            } catch (\Throwable $th) {
                jsonResponse(translate('email_has_not_been_sent'));
            }
        }
    }
}

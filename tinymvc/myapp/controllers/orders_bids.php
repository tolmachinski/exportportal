<?php

use App\Common\Database\Relations\RelationInterface;
use App\Common\Traits\DatatableRequestAwareTrait;
use App\Common\Buttons\ChatButton;
use App\Common\Contracts\User\UserStatus;

/**
 * Controller Orders_bids.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Orders_bids_Controller extends TinyMVC_Controller
{
    use DatatableRequestAwareTrait;

    /**
     * Orders' bids index page.
     */
    public function index()
    {
        headerRedirect('/orders_bids/my', 301);
    }

    /**
     * Bids page.
     */
    public function my()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_quote_requests');
        checkGroupExpire();

        $uri = uri()->uri_to_assoc();

        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        $data = [
            'title'          => "Orders' bids",
            'countries'      => model('country')->get_countries(),
            'shipment_types' => $shippingTypesModel->findAllBy(['conditions' => ['isVisible' => 1]]),
            'filters'        => array(
                'bid'  => with(arrayGet($uri, 'bid'), function ($bid_id) {
                    return null === $bid_id ? null : array('value' => (int) trim($bid_id, '#'), 'placeholder' => orderNumber((int) trim($bid_id, '#')));
                }),
                'order'  => with(arrayGet($uri, 'order'), function ($order_id) {
                    return null === $order_id ? null : array('value' => (int) trim($order_id, '#'), 'placeholder' => orderNumber((int) trim($order_id, '#')));
                }),
                'status' => with(arrayGet($uri, 'status'), function ($status) {
                    return null !== $status && isset(model('orders_quotes')->statuses[$status]) ? array('value' => $status) : null;
                }),
            ),
        ];

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/orders_bids/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    /**
     * Upcoming orders page.
     */
    public function upcoming()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_quote_requests');
        checkGroupExpire();

        $this->show_upcoming_orders_page(uri()->uri_to_assoc());
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('manage_quote_requests');
        checkGroupExpire();

        switch (uri()->segment(3)) {
            case 'bid':
                $this->show_bid_popup((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'details':
                $this->show_details_popup((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            default:
                messageInModal(translate('sysmtess_provided_path_not_found'));

                break;
        }
    }

    /**
     * Executes AJAX actions.
     */
    public function ajax_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();
        checkPermisionAjax('manage_quote_requests');
        checkGroupExpire();

        switch (uri()->segment(3)) {
            case 'bids':
                $this->show_bids_list((int) privileged_user_id());

                break;
            case 'place_bid':
                $this->place_bid((int) privileged_user_id(), (int) arrayGet($_POST, 'order'));

                break;
            case 'delete_bid':
                $this->delete_bid((int) privileged_user_id(), (int) arrayGet($_POST, 'bid'));

                break;
            case 'upcoming_orders':
                $this->show_upcoming_orders_list((int) privileged_user_id());

                break;
            default:
                jsonResponse(translate('sysmtess_provided_path_not_found'));

                break;
        }
    }

    /**
     * Shows the upcoming orders page.
     *
     * @param array $uri
     */
    protected function show_upcoming_orders_page(array $uri = array())
    {
        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        $data = [
            'title'          => 'Upcoming orders',
            'countries'      => model('country')->get_countries(),
            'shipment_types' => $shippingTypesModel->findAllBy(['conditions' => ['isVisible' => 1]]),
            'filters'        => [
                'order'  => with(arrayGet($uri, 'order'), function ($order_id) {
                    return null === $order_id ? null : ['value' => (int) trim($order_id, '#'), 'placeholder' => orderNumber((int) trim($order_id, '#'))];
                }),
            ],
        ];

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/orders_bids/upcoming/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    /**
     * Shows the list of the orders' bids placed by shipper formatted for DT.
     *
     * @param int $shipper_id
     */
    protected function show_bids_list($shipper_id)
    {
        //region Query parameters
        $skip = (int) arrayGet($_POST, 'iDisplayStart', 0);
        $limit = (int) arrayGet($_POST, 'iDisplayLength', 0);
        $with = array();
        $joins = array('orders');
        $order = array_column(dt_ordering($_POST, array(
            'created_dt'  => 'create_date',
            'updated_dt'  => 'update_date',
            'pickup_dt'   => 'pickup_date',
            'delivery_dt' => 'delivery_date',
        )), 'direction', 'column');
        $columns = array(
            '`BIDS`.*',
            '`ORDERS`.`ship_to`',
            '`ORDERS`.`ship_from`',
            '`ORDERS`.`shipment_type`',
            '`ORDERS`.`id_buyer`',
            '`ORDERS`.`id_seller`',
            '`ORDERS`.`ep_manager`',
        );
        $conditions = array_merge(
            array('shipper' => (int) $shipper_id),
            dtConditions($_POST, array(
                array('as' => 'id',                        'key' => 'bid',            'type'    => 'trim:#|intval:10'),
                array('as' => 'order',                     'key' => 'order',          'type'    => 'trim:#|intval:10'),
                array('as' => 'order_shipment_type',       'key' => 'shipment_type',  'type'    => 'intval:10'),
                array('as' => 'order_departure_country',   'key' => 'from_country',   'type'    => 'intval:10'),
                array('as' => 'order_departure_region',    'key' => 'from_state',     'type'    => 'intval:10'),
                array('as' => 'order_departure_city',      'key' => 'from_city',      'type'    => 'intval:10'),
                array('as' => 'order_destination_country', 'key' => 'to_country',     'type'    => 'intval:10'),
                array('as' => 'order_destination_region',  'key' => 'to_state',       'type'    => 'intval:10'),
                array('as' => 'order_destination_city',    'key' => 'to_city',        'type'    => 'intval:10'),
                array('as' => 'status',                    'key' => 'status',         'type'    => 'cleanInput|trim'),
                array('as' => 'created_from_date',         'key' => 'created_from',   'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', null)),
                array('as' => 'created_to_date',           'key' => 'created_to',     'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', new DateInterval('PT23H59M59S'))),
                array('as' => 'updated_from_date',         'key' => 'updated_from',   'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', null)),
                array('as' => 'updated_to_date',           'key' => 'updated_to',     'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', new DateInterval('PT23H59M59S'))),
                array('as' => 'delivery_from_date',        'key' => 'delivery_from',  'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', null)),
                array('as' => 'delivery_to_date',          'key' => 'delivery_to',    'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', new DateInterval('PT23H59M59S'))),
                array('as' => 'pickup_from_date',          'key' => 'pickup_from',    'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', null)),
                array('as' => 'pickup_to_date',            'key' => 'pickup_to',      'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d', new DateInterval('PT23H59M59S'))),
            ))
        );
        //endregion Query parameters

        //region Make output
        $list = array();
        /** @var Orders_quotes_Model $orderBids */
        $orderBids = model(Orders_quotes_Model::class);
        $bids = $orderBids->get_bids(compact('columns', 'conditions', 'order', 'limit', 'skip', 'with', 'joins'));
        $total = $orderBids->count_bids(compact('conditions', 'joins'));
        if (!empty($bids)) {
            /** @var Shipping_Types_Model $shippingTypes */
            $shippingTypes = model(Shipping_Types_Model::class);
            $list = $this->get_bids_list(
                $bids,
                arrayByKey(
                    $shippingTypes->findAllBy(['conditions' => ['isVisible' => 1]]),
                    'id_type'
                )
            );
        }
        //endregion Make output

        jsonResponse(null, 'success', array(
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'aaData'               => $list,
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
        ));
    }

    /**
     * Shows the list of the orders formatted for upcoming orders DT.
     *
     * @param int $shipper_id
     */
    protected function show_upcoming_orders_list($shipper_id)
    {
        $currentUserStatus = userStatus();
        if ($currentUserStatus !== UserStatus::ACTIVE()) {
            jsonDTResponse(
                cleanOutput(translate('shipper_dashboard_account_not_active_alert_text')),
                [],
                'warning'
            );
        }

        //region Countries
        if (
            !model('shipper_countries')->worldwide_shipper_countries($shipper_id)
            && empty($countries = model('shipper_countries')->get_shipper_countries(array('id_user' => $shipper_id)))
        ) {
            jsonDTResponse(
                translate('shipper_dashboard_location_alert_text', array(
                    '[[LINK_OPEN_TAG]]'  => sprintf('<a href="%s" target="_blank">', getUrlForGroup('shipping_countries')),
                    '[[LINK_CLOSE_TAG]]' => '</a>',
                )),
                [],
                'warning'
            );
        }
        //endregion Countries

        //region Query parameters
        $params = array_merge(
            array(
                'per_p'            => (int) arrayGet($_POST, 'iDisplayLength', 0),
                'start'            => (int) arrayGet($_POST, 'iDisplayStart', 0),
                'id_country'       => array_column($countries, 'id_country'),
                'not_expired'      => true,
                'sort_by'          => flat_dt_ordering($_POST, array(
                    'created_dt' => 'order_date',
                    'updated_dt' => 'update_date',
                    'expires_dt' => 'status_countdown',
                )),
            ),
            dtConditions($_POST, array(
                array('as' => 'order',               'key' => 'order',         'type'    => 'trim:#|intval:10'),
                array('as' => 'id_shipment_type',    'key' => 'shipment_type', 'type'    => 'intval:10'),
                array('as' => 'departure_country',   'key' => 'from_country',  'type'    => 'intval:10'),
                array('as' => 'departure_region',    'key' => 'from_state',    'type'    => 'intval:10'),
                array('as' => 'departure_city',      'key' => 'from_city',     'type'    => 'intval:10'),
                array('as' => 'destination_country', 'key' => 'to_country',    'type'    => 'intval:10'),
                array('as' => 'destination_region',  'key' => 'to_state',      'type'    => 'intval:10'),
                array('as' => 'destination_city',    'key' => 'to_city',       'type'    => 'intval:10'),
                array('as' => 'created_from',        'key' => 'created_from',  'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', null)),
                array('as' => 'created_to',          'key' => 'created_to',    'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', new DateInterval('PT23H59M59S'))),
                array('as' => 'updated_from',        'key' => 'updated_from',  'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', null)),
                array('as' => 'updated_to',          'key' => 'updated_to',    'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', new DateInterval('PT23H59M59S'))),
                array('as' => 'expires_from',        'key' => 'expires_from',  'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', null)),
                array('as' => 'expires_to',          'key' => 'expires_to',    'type'    => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d H:i:s', new DateInterval('PT23H59M59S'))),
                array('as' => 'package',             'key' => null,            'default' => function () {
                    $package = array();
                    foreach (array('weight', 'length', 'height', 'width') as $key) {
                        if (isset($_POST["min_{$key}"]) && is_numeric($_POST["min_{$key}"])) {
                            $package[$key]['min'] = (int) $_POST["min_{$key}"];
                        }
                        if (isset($_POST["max_{$key}"]) && is_numeric($_POST["max_{$key}"])) {
                            $package[$key]['max'] = (int) $_POST["max_{$key}"];
                        }
                    }

                    return $package;
                }),
            ))
        );
        //endregion Query parameters

        //region Make output
        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        $list = array();
        $orders = model('orders')->get_orders_for_bidding($shipper_id, $params);
        $total = model('orders')->count_orders_for_bidding($shipper_id, $params);
        $list = empty($orders) ? array() : $this->get_upcomin_orders_list(
            $orders,
            arrayByKey(
                $shippingTypesModel->findAllBy(['conditions' => ['isVisible' => 1]]),
                'id_type'
            )
        );
        //endregion Make output

        jsonResponse(null, 'success', array(
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'aaData'               => $list,
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
        ));
    }

    /**
     * Shows the ofrm where shipper can place bid to order.
     *
     * @param int $shipper_id
     * @param int $order_id
     */
    protected function show_bid_popup($shipper_id, $order_id)
    {
        //region Order
        if (
            empty($order_id)
            || empty($order = model('orders')->get_order($order_id))
        ) {
            messageInModal(translate('systmess_error_order_bid_not_exist'));
        }
        //endregion Order

        //region Access check
        if ('invoice_confirmed' !== $order['status_alias']) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        if (model('orders_quotes')->has_bid($shipper_id, $order_id)) {
            messageInModal(translate('systmess_error_you_already_placed_order_bid'));
        }
        //endregion Access check

        //region Vars
        $current_date = new DateTimeImmutable();
        //endregion Vars

        //region Assign vars
        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        views()->assign(array(
            'order'          => $order,
            'action'         => getUrlForGroup("/orders_bids/ajax_operations/place_bid/{$order_id}"),
            'min_date'       => $current_date->add(new DateInterval("P1D")),
            'shipment_type'  => $shippingTypesModel->findOne((int) $order['shipment_type'], ['conditions' => ['isVisible' => 1]]),
        ));
        //endregion Assign vars

        views('new/epl/orders_bids/place_bid_form_view');
    }

    /**
     * Shows the popup with order bid details.
     *
     * @param int $shipper_id
     * @param int $bid_id
     */
    protected function show_details_popup($shipper_id, $bid_id)
    {
        //region Bid
        if (
            empty($bid_id)
            || empty($bid = model('orders_quotes')->get_bid($bid_id, array(
                'with' => array(
                    'user',
                    'shipper',
                    'order' => function (RelationInterface $relation) {
                        $table = $relation->getRelated()->getTable();
                        $relation
                            ->getQuery()
                                ->select(
                                    "{$table}.*, " .
                                    'orders_status.status as order_status_name, orders_status.alias as order_status_alias, ' .
                                    "orders_status.description->>'$.shipper.text' as order_status_description"
                                )
                                ->innerJoin($table, 'orders_status', 'orders_status', "{$table}.status = orders_status.id")
                        ;
                    },
                ),
            )))
        ) {
            messageInModal(translate('systmess_error_order_bid_not_exist'));
        }
        //endregion Bid

        //region Access check
        if ($shipper_id !== (int) $bid['id_shipper']) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        //endregion Access check

        //region Assign vars
        //region Vars
        $user = arrayGet($bid, 'user');
        $order = arrayGet($bid, 'order');
        $shipper = arrayGet($bid, 'shipper');
        $shipment_type = null;

        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        $insurance_options = with(json_decode(arrayGet($bid, 'insurance_options'), true), function ($options) {
            return null === $options || !is_array($options) ? array() : $options;
        });
        $status_description = with(json_decode(arrayGet($order, 'order_status_description'), true), function ($description) {
            return null === $description || !is_array($description) ? array() : $description;
        });
        if (null !== $order) {
            $shipment_type = $shippingTypesModel->findOne((int) $order['shipment_type'], ['conditions' => ['isVisible' => 1]]);
        }
        //endregion Vars

        views()->assign(array(
            'bid'                => $bid,
            'user'               => $user,
            'order'              => $order,
            'shipper'            => $shipper,
            'shipment_type'      => $shipment_type,
            'insurance_options'  => $insurance_options,
            'status_description' => $status_description,
        ));
        //endregion Assign vars

        views('new/epl/orders_bids/details_view');
    }

    /**
     * Returns the list of orders formatted for upcoming orders DT.
     *
     * @param array $bids
     */
    protected function get_bids_list(array $bids, array $shipping_types = array())
    {
        $output = array();
        foreach ($bids as $bid) {
            //region Vars
            $bid_id = (int) $bid['id_quote'];
            $order_id = (int) $bid['id_order'];
            $buyer_id = (int) $bid['id_buyer'];
            $seller_id = (int) $bid['id_seller'];
            $manager_id = (int) $bid['ep_manager'];
            $shipping_type_id = (int) $bid['shipment_type'];
            $shipping_type = arrayGet($shipping_types, $shipping_type_id);
            $bid_status = arrayGet($bid, 'quote_status', 'declined');
            //endregion Vars

            //region Bid
            //region Order
            $order_number = orderNumber($order_id);
            $order_url = getUrlForGroup("order/popups_order/order_detail/{$order_id}");
            $order_text = translate('orders_bids_dashboard_dt_column_order_label_text', array('[[ORDER]]' => $order_number), true);
            $order_title = translate('orders_bids_dashboard_dt_column_order_label_title', array('[[ORDER]]' => $order_number), true);
            $order_modal_title = translate('orders_bids_dashboard_dt_column_order_modal_title', array('[[ORDER]]' => $order_number), true);
            $order_preview = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <a class=\"link-black txt-medium text-nowrap fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$order_url}\"
                        data-title=\"{$order_modal_title}\"
                        title=\"{$order_title}\"
                        target=\"_blank\">
                        {$order_text}
                    </a>
                </div>
            ";
            //endregion Order

            //region Incoterms
            $incoterms_label = '&mdash;';
            $incoterms_label_title = translate('orders_bids_dashboard_dt_column_incoterms_label_title', null, true);
            if (null !== $shipping_type) {
                $incoterms_title = cleanOutput($shipping_type['type_name']);
                $incoterms_description = cleanOutput($shipping_type['type_description']);
                $incoterms_label = "
                    {$incoterms_title}
                    <a class=\"info-dialog\"
                        data-message=\"{$incoterms_description}\"
                        data-title=\"{$incoterms_title}\"
                        title=\"{$incoterms_title}\">
                        <i class=\"ep-icon ep-icon_info fs-16\"></i>
                    </a>
                ";
            }

            $incoterms_preview = "
                <div class=\"main-data-table__item-ttl\">
                    <span class=\"txt-medium\">{$incoterms_label_title}</span>
                    {$incoterms_label}
                </div>
            ";
            //endregion Incoterms

            //region Status
            $status_text = translate("orders_bids_dashboard_dt_column_bid_status_{$bid_status}_text", null, true);
            $status_label = translate('orders_bids_dashboard_dt_column_bid_status_label_title', null, true);
            $status_preview = "
                <div class=\"main-data-table__item-ttl\">
                    <span class=\"txt-medium\">{$status_label}</span>
                    <span class=\"txt-gray\">{$status_text}</span>
                </div>
            ";
            //endregion Status

            $bid_number = orderNumber($bid_id);
            $bid_details_url = getUrlForGroup("/orders_bids/popup_forms/details/{$bid_id}");
            $bid_text = translate('orders_bids_dashboard_dt_column_bid_label_text', array('[[BID]]' => $bid_number), true);
            $bid_title = translate('orders_bids_dashboard_dt_column_bid_label_title', array('[[BID]]' => $bid_number), true);
            $bid_modal_title = translate('orders_bids_dashboard_dt_column_bid_modal_title', array('[[BID]]' => $bid_number), true);
            $bid_preview = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <a class=\"link-black txt-medium text-nowrap fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$bid_details_url}\"
                        data-title=\"{$bid_modal_title}\"
                        title=\"{$bid_title}\"
                        target=\"_blank\">
                        {$bid_text}
                    </a>
                </div>
                {$order_preview}
                {$incoterms_preview}
                {$status_preview}
            ";
            //endregion Bid

            //region Destination
            //region From
            $departure_location = cleanOutput(arrayGet($bid, 'ship_from', '&mdash;'));
            $destination_initial_text = translate('orders_bids_dashboard_dt_column_destination_initial_label_text', null, true);
            $destination_initial_title = translate('orders_bids_dashboard_dt_column_destination_initial_label_title', null, true);
            $destination_initial_label = "
                <div title=\"{$destination_initial_title}\">
                    <strong>{$destination_initial_text}</strong> <span>{$departure_location}</span>
                </div>
            ";
            //endregion From

            //region To
            $destianation_location = cleanOutput(arrayGet($bid, 'ship_to', '&mdash;'));
            $destination_final_text = translate('orders_bids_dashboard_dt_column_destination_final_label_text', null, true);
            $destination_final_title = translate('orders_bids_dashboard_dt_column_destination_final_label_title', null, true);
            $destination_final_label = "
                <div title=\"{$destination_final_title}\">
                    <strong>{$destination_final_text}</strong> <span>{$destianation_location}</span>
                </div>
            ";
            //endregion To

            $destination_preview = "
                <div class=\"dtable__params\">
                    {$destination_initial_label}
                    {$destination_final_label}
                </div>
            ";
            //endregion Destination

            //region Actions
            //region Bid details button
            $bid_details_button_text = translate('general_button_details_text', null, true);
            $bid_details_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$bid_details_url}\"
                    data-title=\"{$bid_modal_title}\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$bid_details_button_text}</span>
                </a>
            ";
            //endregion Bid details button

            //region Contact buyer button
            $contact_buyer_button = null;
            if (!empty($buyer_id)) {
                $btnChatBuyer = new ChatButton(['recipient' => $buyer_id, 'recipientStatus' => 'active', 'module' => 33, 'item' => $bid_id], ['text' => 'Chat with buyer']);
                $contact_buyer_button = $btnChatBuyer->button();
            }
            //endregion Contact buyer button

            //region Contact seller button
            $contact_seller_button = null;
            if (!empty($seller_id)) {
                $btnChatSeller = new ChatButton(['recipient' => $seller_id, 'recipientStatus' => 'active', 'module' => 33, 'item' => $bid_id], ['text' => 'Chat with seller']);
                $contact_seller_button = $btnChatSeller->button();
            }
            //endregion Contact seller button

            //region Contact manager button
            $contact_manager_button = null;
            if (!empty($manager_id)) {
                //TODO: admin chat hidden
                $btnChatManager = new ChatButton(['hide' => true, 'recipient' => $manager_id, 'recipientStatus' => 'active', 'module' => 33, 'item' => $bid_id], ['text' => 'Chat with manager']);
                $contact_manager_button = $btnChatManager->button();
            }
            //endregion Contact manager button

            //region Delete button
            $delete_button = null;
            if (in_array($bid_status, array('awaiting', 'declined'))) {
                $delete_button_text = translate('general_button_delete_text', null, true);
                $delete_button_message = translate('orders_bids_dashboard_dt_column_delete_bid_button_message', null, true);
                $delete_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-message=\"{$delete_button_message}\"
                        data-callback=\"deleteBid\"
                        data-bid=\"{$bid_id}\">
                        <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                        <span>{$delete_button_text}</span>
                    </a>
                ";
            }
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$bid_details_button}
                        {$contact_buyer_button}
                        {$contact_seller_button}
                        {$contact_manager_button}
                        {$delete_button}
                        {$all_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = array(
                'bid_dt'         => $bid_preview,
                'location_dt'    => $destination_preview,
                'created_dt'     => getDateFormatIfNotEmpty(arrayGet($bid, 'create_date'), 'Y-m-d H:i:s'),
                'updated_dt'     => getDateFormatIfNotEmpty(arrayGet($bid, 'update_date'), 'Y-m-d H:i:s'),
                'delivery_dt'    => getDateFormatIfNotEmpty(arrayGet($bid, 'delivery_date'), 'Y-m-d H:i:s', 'j M, Y'),
                'pickup_dt'      => getDateFormatIfNotEmpty(arrayGet($bid, 'pickup_date'), 'Y-m-d H:i:s', 'j M, Y'),
                'actions_dt'     => $actions,
            );
        }

        return $output;
    }

    /**
     * Returns the list of orders formatted for upcoming orders DT.
     *
     * @param array $orders
     * @param array $shipping_types
     */
    protected function get_upcomin_orders_list(array $orders, array $shipping_types = array())
    {
        $output = array();
        foreach ($orders as $order) {
            //region Vars
            $order_id = (int) $order['id'];
            $buyer_id = (int) $order['id_buyer'];
            $seller_id = (int) $order['id_seller'];
            $manager_id = (int) $order['ep_manager'];
            $shipping_type_id = (int) $order['shipment_type'];
            $shipping_type = arrayGet($shipping_types, $shipping_type_id);
            $package_detail = with(json_decode($order['package_detail'], true), function ($package) {
                return null === $package || !is_array($package) ? array() : $package;
            });
            //endregion Vars

            //region Order
            //region Package
            $package_width = arrayGet($package_detail, 'width', 0);
            $package_height = arrayGet($package_detail, 'height', 0);
            $package_length = arrayGet($package_detail, 'length', 0);
            $package_sizes_label_text = translate('orders_bids_dashboard_dt_column_package_sizes_label_text', null, true);
            $package_sizes_label = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"text-nowrap\">
                        <span class=\"txt-medium\">{$package_sizes_label_text}</span> {$package_length} x {$package_width} x {$package_height}
                    </div>
                </div>
            ";
            //endregion Package

            //region Weight
            $package_weight = arrayGet($package_detail, 'weight', 0);
            $package_weight_label_text = translate('orders_bids_dashboard_dt_column_package_weight_label_text', null, true);
            $package_weight_label = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"text-nowrap\">
                        <span class=\"txt-medium\">{$package_weight_label_text}</span> {$package_weight}
                    </div>
                </div>
            ";
            //endregion Weight

            $order_number = orderNumber($order_id);
            $order_url = getUrlForGroup("order/popups_order/order_detail/{$order_id}");
            $order_text = translate('orders_bids_dashboard_dt_column_order_label_text', array('[[ORDER]]' => $order_number), true);
            $order_title = translate('orders_bids_dashboard_dt_column_order_label_title', array('[[ORDER]]' => $order_number), true);
            $order_modal_title = translate('orders_bids_dashboard_dt_column_order_modal_title', array('[[ORDER]]' => $order_number), true);
            $order_preview = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <a class=\"link-black txt-medium text-nowrap fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$order_url}\"
                        data-title=\"{$order_modal_title}\"
                        title=\"{$order_title}\"
                        target=\"_blank\">
                        {$order_text}
                    </a>
                </div>
                {$package_sizes_label}
                {$package_weight_label}
            ";
            //endregion Order

            //region Destination
            //region From
            $departure_location = cleanOutput(arrayGet($order, 'ship_from', '&mdash;'));
            $estimate_destination_initial_text = translate('orders_bids_dashboard_dt_column_destination_initial_label_text', null, true);
            $estimate_destination_initial_title = translate('orders_bids_dashboard_dt_column_destination_initial_label_title', null, true);
            $estimate_destination_initial_label = "
                <div title=\"{$estimate_destination_initial_title}\">
                    <strong>{$estimate_destination_initial_text}</strong> <span>{$departure_location}</span>
                </div>
            ";
            //endregion From

            //region To
            $destianation_location = cleanOutput(arrayGet($order, 'ship_to', '&mdash;'));
            $estimate_destination_final_text = translate('orders_bids_dashboard_dt_column_destination_final_label_text', null, true);
            $estimate_destination_final_title = translate('orders_bids_dashboard_dt_column_destination_final_label_title', null, true);
            $estimate_destination_final_label = "
                <div title=\"{$estimate_destination_final_title}\">
                    <strong>{$estimate_destination_final_text}</strong> <span>{$destianation_location}</span>
                </div>
            ";
            //endregion To

            $destination_label = "
                <div class=\"dtable__params\">
                    {$estimate_destination_initial_label}
                    {$estimate_destination_final_label}
                </div>
            ";
            //endregion Destination

            //region Incoterms
            $incoterms_preview = null;
            if (null !== $shipping_type) {
                $incoterms_title = cleanOutput($shipping_type['type_name']);
                $incoterms_description = cleanOutput($shipping_type['type_description']);
                $incoterms_preview = "
                    {$incoterms_title}
                    <a class=\"info-dialog\"
                        data-message=\"{$incoterms_description}\"
                        data-title=\"{$incoterms_title}\"
                        title=\"{$incoterms_title}\">
                        <i class=\"ep-icon ep-icon_info fs-16\"></i>
                    </a>
                ";
            }
            //endregion Incoterms

            //region Actions
            //region Place bid button
            $place_bid_button_url = getUrlForGroup("/orders_bids/popup_forms/bid/{$order_id}");
            $place_bid_button_text = translate('orders_bids_dashboard_dt_column_bid_button_text', null, true);
            $place_bid_button_modal_title = translate('orders_bids_dashboard_dt_column_bid_button_modal_title', array('[[ORDER]]' => $order_number), true);
            $place_bid_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$place_bid_button_url}\"
                    data-title=\"{$place_bid_button_modal_title}\">
                    <i class=\"ep-icon ep-icon_plus-circle\"></i>
                    <span>{$place_bid_button_text}</span>
                </a>
            ";
            //endregion Place bid button

            //region Order details button
            $order_details_button_text = translate('general_button_details_text', null, true);
            $order_details_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$order_url}\"
                    data-title=\"{$order_modal_title}\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$order_details_button_text}</span>
                </a>
            ";
            //endregion Order details button

            //region Contact buyer button
            $contact_buyer_button = null;
            if (!empty($buyer_id)) {
                $btnChatBuyer = new ChatButton(['recipient' => $buyer_id, 'recipientStatus' => 'active', 'module' => 32, 'item' => $order_id], ['text' => 'Chat with buyer']);
                $contact_buyer_button = $btnChatBuyer->button();
            }
            //endregion Contact buyer button

            //region Contact seller button
            $contact_seller_button = null;
            if (!empty($seller_id)) {
                $btnChatSeller = new ChatButton(['recipient' => $seller_id, 'recipientStatus' => 'active', 'module' => 32, 'item' => $order_id], ['text' => 'Chat with seller']);
                $contact_seller_button = $btnChatSeller->button();
            }
            //endregion Contact seller button

            //region Contact manager button
            $contact_manager_button = null;
            if (!empty($manager_id)) {
                //TODO: admin chat hidden
                $btnChatManager = new ChatButton(['hide' => true, 'recipient' => $manager_id, 'recipientStatus' => 'active', 'module' => 32, 'item' => $order_id], ['text' => 'Chat with manager']);
                $contact_manager_button = $btnChatManager->button();
            }
            //endregion Contact manager button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$place_bid_button}
                        {$order_details_button}
                        {$contact_buyer_button}
                        {$contact_seller_button}
                        {$contact_manager_button}
                        {$all_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = array(
                'order_dt'      => $order_preview,
                'location_dt'   => $destination_label,
                'incoterms_dt'  => $incoterms_preview,
                'updated_dt'    => getDateFormatIfNotEmpty(arrayGet($order, 'update_date'), 'Y-m-d H:i:s'),
                'created_dt'    => getDateFormatIfNotEmpty(arrayGet($order, 'order_date'), 'Y-m-d H:i:s'),
                'expires_dt'    => getDateFormatIfNotEmpty(arrayGet($order, 'status_countdown'), 'Y-m-d H:i:s'),
                'actions_dt'    => $actions,
            );
        }

        return $output;
    }

    /**
     * Places the bid to the order.
     *
     * @param int $shipper_id
     * @param int $order_id
     */
    protected function place_bid($shipper_id, $order_id)
    {
        //region Validation
        $postdata = $_POST;
        $delivery_days = config('ep_shippers_max_delivery_days', 180);
        $validator_rules = array(
            array(
                'field' => 'delivery_days_from',
                'label' => 'Delivery from',
                'rules' => array(
                    'required'                                      => '',
                    'is_natural_no_zero'                            => '',
                    "max[{$delivery_days}]"                         => '',
                    'less_than_or_equal_to_field[delivery_days_to]' => translate('validation_order_bids_delivery_days_from_greater_than_delivery_days_to'),
                ),
            ),
            array(
                'field' => 'delivery_days_to',
                'label' => 'Delivery to',
                'rules' => array(
                    'required'                                           => '',
                    'is_natural_no_zero'                                 => '',
                    "max[{$delivery_days}]"                              => '',
                ),
            ),
            array(
                'field' => 'price',
                'label' => 'Shipping price',
                'rules' => array(
                    'required'        => '',
                    'positive_number' => '',
                ),
            ),
            array(
                'field' => 'shipment_cfs',
                'label' => 'Container Freight Station',
                'rules' => array(
                    'required'     => '',
                    'max_len[250]' => '',
                ),
            ),
            array(
                'field' => 'shipment_ff',
                'label' => 'Freight Forwarder',
                'rules' => array(
                    'required'     => '',
                    'max_len[250]' => '',
                ),
            ),
            array(
                'field' => 'shipment_pickup',
                'label' => 'Scheduling pickup',
                'rules' => array(
                    'required'           => '',
                    'in[shipper,seller]' => translate('validation_order_bids_scheduling_pickup'),
                ),
            ),
            array(
                'field' => 'delivery_date',
                'label' => 'Delivery date',
                'rules' => array(
                    'required'                 => '',
                    'valid_date[m/d/Y]'        => '',
                    'valid_date_future[m/d/Y]' => '',
                ),
            ),
            array(
                'field' => 'pickup_date',
                'label' => 'Pickup date',
                'rules' => array(
                    'required'                 => '',
                    'valid_date[m/d/Y]'        => '',
                    'valid_date_future[m/d/Y]' => '',
                    function ($attribute, $value, $fail) {
                        if (
                            !empty($_POST['delivery_date'])
                            && false !== ($deliveryStartDate = \DateTime::createFromFormat('m/d/Y', $_POST['delivery_date']))
                        ) {
                            if (\DateTime::createFromFormat('m/d/Y', $value) > $deliveryStartDate) {
                                $fail(translate('validation_order_bids_pickup_date_must_be_before_delivery_date'));
                            }
                        }
                    },
                ),
            ),
            array(
                'field' => 'comment',
                'label' => 'Notes',
                'rules' => array(
                    'max_len[500]' => '',
                ),
            ),
            array(
                'field' => 'insurance_option',
                'label' => 'Insurance options',
                'rules' => array(
                    'required' => translate('validation_order_bids_insurance_options_are_required'),
                ),
            ),
        );

        if (!empty($insurance_options = arrayGet($_POST, 'insurance_option', array()))) {
            $insurance_options_key = array_keys($insurance_options);
            foreach ($insurance_options as $key => $insurance_option) {
                $index = (int) array_search($key, $insurance_options_key) + 1;
                $key_prefix = "insurance-option::{$key}";
                $postdata["{$key_prefix}::title"] = arrayGet($insurance_option, 'title');
                $postdata["{$key_prefix}::amount"] = arrayGet($insurance_option, 'amount');
                $postdata["{$key_prefix}::description"] = arrayGet($insurance_option, 'description');

                $validator_rules[] = array(
                    'field' => "{$key_prefix}::title",
                    'label' => "Title of the Insurance option nr. {$index}",
                    'rules' => array(
                        'required'     => '',
                        'valide_title' => '',
                        'max_len[100]' => '',
                    ),
                );
                $validator_rules[] = array(
                    'field' => "{$key_prefix}::amount",
                    'label' => "Amount of the Insurance option nr. {$index}",
                    'rules' => array(
                        'required'        => '',
                        'positive_number' => '',
                        'min[0]'          => '',
                        'max[999999]'     => '',
                    ),
                );
                $validator_rules[] = array(
                    'field' => "{$key_prefix}::description",
                    'label' => "Description of the Insurance option nr. {$index}",
                    'rules' => array(
                        'required'      => '',
                        'max_len[1000]' => '',
                    ),
                );
            }
        }

        $this->validator->reset_postdata();
        $this->validator->clear_array_errors();
        $this->validator->validate_data = $postdata;
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Order
        if (
            empty($order_id)
            || empty($order = model('orders')->get_order($order_id))
        ) {
            jsonResponse(translate('systmess_error_order_bid_not_exist'));
        }
        //endregion Order

        //region Access check
        if ('invoice_confirmed' !== $order['status_alias']) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        if (model('orders_quotes')->has_bid($shipper_id, $order_id)) {
            jsonResponse(translate('systmess_error_you_already_placed_order_bid'));
        }
        //endregion Access check

        //region Vars
        $buyer_id = (int) $order['id_buyer'];
        $seller_id = (int) $order['id_seller'];
        $shipping_amount = priceToUsdMoney($postdata['price']);
        $delivery_date = \DateTimeImmutable::createFromFormat('m/d/Y', $postdata['delivery_date'])->setTime(0, 0, 0);
        $pickup_date = \DateTimeImmutable::createFromFormat('m/d/Y', $postdata['delivery_date'])->setTime(0, 0, 0);

        //region Bid
        $bid = array(
            'id_shipper'         => $shipper_id,
            'id_order'           => $order_id,
            'shipment_cfs'       => cleanInput($postdata['shipment_cfs']),
            'shipment_ff'        => cleanInput($postdata['shipment_ff']),
            'shipment_pickup'    => cleanInput($postdata['shipment_pickup']),
            'comment_shipper'    => cleanInput($postdata['comment']),
            'quote_status'       => 'awaiting',
            'shipping_price'     => moneyToDecimal($shipping_amount),
            'delivery_days_from' => (int) $postdata['delivery_days_from'],
            'delivery_days_to'   => (int) $postdata['delivery_days_to'],
            'delivery_date'      => $delivery_date,
            'pickup_date'        => $pickup_date,
            'insurance_options'  => array_map(
                function ($insurance_option) {
                    $insurance_amount = priceToUsdMoney(arrayGet($insurance_option, 'amount', 0));

                    return array(
                        'amount'      => moneyToDecimal($insurance_amount),
                        'title'       => cleanInput($insurance_option['title']),
                        'description' => cleanInput($insurance_option['description']),
                    );
                },
                array_values($insurance_options)
            ),
        );
        //endregion Bid
        //endregion Vars

        //region Create bid
        if (!model('orders_quotes')->create_bid($bid)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Create bid

        //region Notifications

		if (!empty($buyer_id)) {
			model('notify')->send_notify([
				'mess_code' => 'shipping_quote_updated',
				'id_users'  => [$buyer_id],
				'replace'   => [
					'[ORDER_ID]'    => orderNumber($order_id),
					'[ORDER_LINK]'  => getUrlForGroup("order/my/order_number/{$order_id}", 'buyer'),
					'[LINK]'        => getUrlForGroup('order/my', 'buyer'),
				],
				'systmess' => true,
			]);
		}


		if (!empty($seller_id)) {
			model('notify')->send_notify([
				'mess_code' => 'shipping_quote_updated',
				'id_users'  => [$seller_id],
				'replace'   => [
					'[ORDER_ID]'    => orderNumber($order_id),
					'[ORDER_LINK]'  => getUrlForGroup("order/my/order_number/{$order_id}", 'seller'),
					'[LINK]'        => getUrlForGroup('order/my', 'seller'),
				],
				'systmess' => true,
			]);
		}

        //endregion Notifications

        jsonResponse(translate('systmess_success_bid_placed'), 'success');
    }

    /**
     * Deletes the bid.
     *
     * @param int $shipper_id
     * @param int $bid_id
     */
    protected function delete_bid($shipper_id, $bid_id)
    {
        //region Bid
        if (
            empty($bid_id)
            || empty($bid = model('orders_quotes')->get_bid($bid_id, array(
                'with' => array('order'),
            )))
        ) {
            jsonResponse(translate('systmess_error_order_bid_not_exist'));
        }
        //endregion Bid

        //region Access check
        if ($shipper_id !== (int) $bid['id_shipper']) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        if (!in_array($bid['quote_status'], array('awaiting', 'declined'))) {
            jsonResponse(translate('systmess_error_bid_cannot_be_deleted'));
        }
        //endregion Access check

        //region Vars
        $order_id = (int) $bid['id_order'];
        $buyer_id = (int) arrayGet($bid, 'order.id_buyer');
        $seller_id = (int) arrayGet($bid, 'order.id_seller');
        //endregion Vars

        //region Delete
        if (!model('orders_quotes')->delete_bid($bid_id)) {
            jsonResponse(translate('systmess_error_cannot_delete_bid'));
        }
        //endregion Delete

        //region Notifications
        if (!empty($buyer_id)) {

			model('notify')->send_notify([
				'systmess'  => true,
				'mess_code' => 'shipping_quote_deleted',
				'id_users'  => [$buyer_id],
				'replace'   => [
					'[ORDER_ID]'               => orderNumber($order_id),
					'[SHIPPING_REQUESTS_ID]'   => orderNumber($bid_id),
					'[SHIPPING_REQUESTS_LINK]' => getUrlForGroup("orders_bids/my/bid/{$bid_id}", 'buyer'),
					'[ORDER_LINK]'             => getUrlForGroup("order/my/order_number/{$order_id}", 'buyer'),
					'[LINK]'                   => getUrlForGroup('orders_bids/my', 'buyer'),
				],
			]);

        }

        if (!empty($seller_id)) {

			model('notify')->send_notify([
				'systmess'  => true,
				'mess_code' => 'shipping_quote_deleted_alternate',
				'id_users'  => [$seller_id],
				'replace'   => [
					'[ORDER_ID]'               => orderNumber($order_id),
					'[SHIPPING_REQUESTS_ID]'   => orderNumber($bid_id),
					'[ORDER_LINK]'             => getUrlForGroup("order/my/order_number/{$order_id}", 'seller'),
					'[LINK]'                   => getUrlForGroup('order/my', 'seller'),
				],
			]);

        }
        //endregion Notifications

        jsonResponse(translate('systmess_success_bid_deleted'), 'success');
    }
}

// End of file orders_bids.php
// Location: /tinymvc/myapp/controllers/orders_bids.php

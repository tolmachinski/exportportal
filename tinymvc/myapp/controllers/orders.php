<?php

declare(strict_types=1);

use App\Common\Contracts\Bill\BillTypes;
use App\Common\Contracts\Shipper\ShipperType;

class Orders_Controller extends TinyMVC_Controller
{
    public function index(): void
    {
        show_404();
    }

    public function all(): void
    {
        if (!have_right('read_all_orders')) {
            show_404();
        }

        /** @var Product_Orders_Statuses_Model $productOrderStatuses */
        $productOrderStatuses = model(Product_Orders_Statuses_Model::class);

        views(
            [
                'admin/header_view',
                'admin/order/ep_orders/index_view',
                'admin/footer_view'
            ],
            [
                'title'         => 'All orders',
                'orderStatuses' => $productOrderStatuses->findAll([
                    'order' => [
                        "`{$productOrderStatuses->getTable()}`.`position`" => "ASC"
                    ],
                ]),
            ]
        );
    }

    public function ajax_admin_all_orders_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('read_all_orders');

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);
        $productsOrdersTable = $productOrdersModel->getTable();

        $request = request()->request;

        $queryOrderBy = dtOrdering(
            $request->all(),
            [
                'orderNumber'       => "`{$productsOrdersTable}`.`id`",
                'orderCreateDate'   => "`{$productsOrdersTable}`.`order_date`",
                'orderUpdateDate'   => "`{$productsOrdersTable}`.`update_date`",
                'orderStatus'       => "`{$productsOrdersTable}`.`status`",
            ],
            fn ($ordering) => [$ordering['column'] => strtoupper($ordering['direction'])]
        ) ?: [["`{$productsOrdersTable}`.`id`" => 'DESC']];

        $queryConditions = dtConditions($request->all(), [
            ['as' => 'orderStatusGroup',            'key' => 'status_group',        'type' => fn ($orderStatusGroup) => in_array($orderStatusGroup, ['new', 'active', 'passed']) ? $orderStatusGroup : null],
            ['as' => 'orderStatus',                 'key' => 'order_status',        'type' => 'int'],
            ['as' => 'finalPriceGte',               'key' => 'price_from',          'type' => fn ($price) => numericToUsdMoney($price)],
            ['as' => 'finalPriceLte',               'key' => 'price_to',            'type' => fn ($price) => numericToUsdMoney($price)],
            ['as' => 'orderCreateDateGte',          'key' => 'start_date_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'orderCreateDateLte',          'key' => 'start_date_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords',                    'key' => 'keywords',            'type' => 'trim'],
            ['as' => 'isCancelRequestOpened',       'key' => 'cancel_request',      'type' => fn ($cancelRequestStatus) => in_array($cancelRequestStatus, [1, 2]) ? (int) $cancelRequestStatus : null],
            ['as' => 'isDisputeOpened',             'key' => 'dispute_opened',      'type' => fn ($disputeStatus) => in_array($disputeStatus, [1, 2]) ? (int) $disputeStatus : null],
            ['as' => 'assignedManagerEmail',        'key' => 'manager_email',       'type' => 'trim'],
            ['as' => 'realUsers',                   'key' => 'real_users',          'type' => fn ($onlyRealUsers) => 0 == $onlyRealUsers ? 0 : 2],
        ]);

        if (!isset($queryConditions['realUsers'])) {
            $queryConditions['realUsers'] = 1;
        } elseif (2 === $queryConditions['realUsers']) {
            unset($queryConditions['realUsers']);
        }

        $ordersQueryParams = [
            'columns'       => [
                "`{$productsOrdersTable}`.`id`",
                "`{$productsOrdersTable}`.`order_date`",
                "`{$productsOrdersTable}`.`update_date`",
                "`{$productsOrdersTable}`.`status`",
                "`{$productsOrdersTable}`.`final_price`",
                "`{$productsOrdersTable}`.`ship_price`",
            ],
            'conditions'    => $queryConditions,
            'joins'         => array_filter([
                isset($queryConditions['orderStatusGroup']) ? 'orderStatus' : null,
            ]),
            'with'          => ['orderStatus'],
            'order'         => array_shift($queryOrderBy),
            'skip'          => abs($request->getInt('iDisplayStart')),
            'limit'         => abs($request->getInt('iDisplayLength', 10)),
        ];

        $ordersCount = $productOrdersModel->countAllBy(array_diff_key($ordersQueryParams, array_flip(['skip', 'limit', 'order'])));
        $orders = empty($ordersCount) ? [] : $productOrdersModel->findAllBy($ordersQueryParams);

        $output = [
			'sEcho'                     => $request->getInt('sEcho'),
			'iTotalRecords'             => $ordersCount,
			'iTotalDisplayRecords'      => $ordersCount,
			'aaData'                    => []
        ];

        foreach ($orders ?: [] as $order) {
            $output['aaData'][] = [
                'orderNumber'           => orderNumber($order['id']),
                'orderCreateDate'       => $order['order_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'orderUpdateDate'       => $order['update_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'orderProductsPrice'    => get_price($order['final_price']),
                'orderDeliveryPrice'    => get_price($order['ship_price']),
                'orderStatus'           => "<span><i class=\"ep-icon {$order['order_status']['icon']} fs-30\"></i><br>{$order['order_status']['status']}</span>",
                'orderActions'          => sprintf(
                    <<<ORDER_DETAILS_LINK
                        <a href="%s" target="_blank" class="btn btn-primary">View details</a>
                    ORDER_DETAILS_LINK,
                    __SITE_URL . 'orders/detail/' . $order['id']
                ),
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function detail(): void
    {
        if (!have_right('read_order_details')) {
            show_404();
        }

        if (!ctype_digit((string) $orderId = uri()->segment(3))) {
            session()->setMessages(translate('systmess_error_order_id_wrong_format'), 'errors');

            headerRedirect(__SITE_URL . 'orders/all');
        }

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);

        if (empty($order = $productOrdersModel->findOne((int) $orderId, ['with' => ['orderStatus']]))) {
            session()->setMessages(translate('systmess_error_order_not_exist'), 'errors');

            headerRedirect(__SITE_URL . 'orders/all');
        }

        /** @var Ordered_Items_Model $orderedItemsModel */
        $orderedItemsModel = model(Ordered_Items_Model::class);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        if (!empty($order['id_shipper'])) {
            switch ($order['shipper_type']) {
                case ShipperType::SHIPPER():
                    $orderShipper = $usersModel->findOne(
                        (int) $order['id_shipper'],
                        [
                            'columns'   => [
                                "`$usersTable`.`idu`",
                                "`$usersTable`.`fname`",
                                "`$usersTable`.`lname`",
                                "`$usersTable`.`registration_date`",
                                "`$usersTable`.`last_active`",
                                "`$usersTable`.`email`",
                                "`$usersTable`.`address`",
                                "`$usersTable`.`phone`",
                                "`$usersTable`.`phone_code`",
                                "`$usersTable`.`fax_code`",
                                "`$usersTable`.`fax`",
                            ],
                            'with'      => ['shipperCompany']
                        ]
                    );

                    break;
                case ShipperType::INTERNATIONAL_SHIPPER():
                    /** @var International_Shippers_Model $internationalShippersModel */
                    $internationalShippersModel = model(International_Shippers_Model::class);

                    $orderShipper = $internationalShippersModel->findOne((int) $order['id_shipper']);

                    break;
            }
        }

        views(
            [
                'admin/header_view',
                'admin/order/details/index_view',
                'admin/footer_view'
            ],
            [
                'title'         => sprintf('Order %s details', orderNumber($orderId)),
                'order'         => $order,
                'orderBuyer'    => $usersModel->findOne(
                    (int) $order['id_buyer'],
                    [
                        'columns'   => [
                            "`$usersTable`.`idu`",
                            "`$usersTable`.`fname`",
                            "`$usersTable`.`lname`",
                            "`$usersTable`.`registration_date`",
                            "`$usersTable`.`last_active`",
                            "`$usersTable`.`email`",
                            "`$usersTable`.`address`",
                            "`$usersTable`.`phone`",
                            "`$usersTable`.`phone_code`",
                            "`$usersTable`.`fax_code`",
                            "`$usersTable`.`fax`",
                        ],
                        'with'      => ['buyerCompany']
                    ]
                ),
                'orderSeller'   => $usersModel->findOne(
                    (int) $order['id_seller'],
                    [
                        'columns'   => [
                            "`$usersTable`.`idu`",
                            "`$usersTable`.`fname`",
                            "`$usersTable`.`lname`",
                            "`$usersTable`.`registration_date`",
                            "`$usersTable`.`last_active`",
                            "`$usersTable`.`email`",
                            "`$usersTable`.`address`",
                            "`$usersTable`.`phone`",
                            "`$usersTable`.`phone_code`",
                            "`$usersTable`.`fax_code`",
                            "`$usersTable`.`fax`",
                        ],
                        'with'      => ['sellerCompany']
                    ]
                ),
                'orderShipper'  => $orderShipper ?: [],
                'orderManager'  => empty($order['ep_manager']) ? [] : $usersModel->findOne(
                    (int) $order['ep_manager'],
                    [
                        "`$usersTable`.`idu`",
                        "`$usersTable`.`fname`",
                        "`$usersTable`.`lname`",
                        "`$usersTable`.`email`",
                    ]
                ),
                'orderedItems'  => $orderedItemsModel->findAllBy([
                    'conditions' => [
                        'orderId' => $order['id']
                    ],
                    'with' => ['snapshot'],
                ]),
            ]
        );
    }

    public function ajax_admin_order_timeline_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('read_all_orders');

        $request = request()->request;

        $output = [
			'sEcho'                     => $request->getInt('sEcho'),
			'iTotalRecords'             => 0,
			'iTotalDisplayRecords'      => 0,
			'aaData'                    => []
        ];

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);

        $orderId = (int) uri()->segment(3);
        $skip = abs($request->getInt('iDisplayStart'));

        if (
            empty($orderId)
            || empty($order = $productOrdersModel->findOne($orderId))
            || empty($order['order_summary'])
        ) {
            jsonResponse('', 'success', $output);
        }

        $output['iTotalRecords'] = $output['iTotalDisplayRecords'] = count($order['order_summary']);

        foreach ((array) array_slice(array_reverse($order['order_summary']), $skip, 10) as $timelineRecord) {
            $output['aaData'][] = [
                'timelineDate' => $timelineRecord['date'],
                'timelineMember' => $timelineRecord['user'],
                'timelineActions' => $timelineRecord['message'],
            ];
        }

        jsonResponse('', 'success', $output);

    }

    public function ajax_admin_order_bills_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('read_all_orders');

        $request = request()->request;

        $output = [
			'sEcho'                     => $request->getInt('sEcho'),
			'iTotalRecords'             => 0,
			'iTotalDisplayRecords'      => 0,
			'aaData'                    => []
        ];

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);

        if (
            empty($orderId = (int) uri()->segment(3))
            || empty($order = $productOrdersModel->findOne($orderId))
        ) {
            jsonResponse('', 'success', $output);
        }

        /** @var Bills_Model $billsModel */
        $billsModel = model(Bills_Model::class);

        $billsQueryParams = [
            'conditions'    => [
                'itemId'    => $orderId,
                'typeIds'   => [
                    BillTypes::getId(BillTypes::ORDER()),
                    BillTypes::getId(BillTypes::SHIPPING()),
                ],
            ],
            'with'          => [
                'type'
            ],
            'order'         => [
                "`{$billsModel->getTable()}`.`create_date`" => "DESC",
            ],
            'skip'          => abs($request->getInt('iDisplayStart')),
            'limit'         => 10,
        ];

        $output['iTotalRecords'] = $output['iTotalDisplayRecords'] = $billsModel->countAllBy(array_diff_key($billsQueryParams, array_flip(['skip', 'limit', 'order'])));
        $orderBills = empty($output['iTotalRecords']) ? [] : $billsModel->findAllBy($billsQueryParams);

        if (!empty($orderBills)) {
            /** @var User_Bills_Model $userBillsModel */
            $userBillsModel = model(User_Bills_Model::class);

            $billStatuses = $userBillsModel->get_bills_statuses();
        }

        foreach ($orderBills ?: [] as $orderBill) {
            //region make actions
            $actions = [
                sprintf(
                    <<<VIEW_DETAILS_BTN
                        <li>
                            <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-title="Bill details">
                                <span class="ep-icon ep-icon_visible txt-blue"></span> View bill details
                            </a>
                        </li>
                    VIEW_DETAILS_BTN,
                    __SITE_URL . 'payments/popups_payment/payment_detail_admin/' . $orderBill['id_bill']
                )
            ];

            if ('paid' === $orderBill['status']) {
                $actions[] = sprintf(
                    <<<CONFIRM_BILL_BTN
                        <li>
                            <a class="confirm-dialog" data-callback="confirmBill" data-bill="{$orderBill['id_bill']}" href="#" title="Confirm Bill" data-message="%s">
                                <span class="ep-icon ep-icon_ok txt-green"></span> Confirm bill
                            </a>
                        </li>
                    CONFIRM_BILL_BTN,
                    translate('systmess_confirm_bill', null, true)
                );

                $actions[] = sprintf(
                    <<<DECLINE_BILL_BTN
                        <li>
                            <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-title="Decline Bill">
                                <span class="ep-icon ep-icon_remove txt-red"></span> Decline Bill
                            </a>
                        </li>
                        DECLINE_BILL_BTN,
                    __SITE_URL . 'billing/popup_forms/decline_bill/' . $orderBill['id_bill']
                );
            }

            if (in_array($orderBill['status'], ['init', 'paid'])) {
                if (empty($orderBill['extend_request'])) {
                    $actions[] = sprintf(
                        <<<EXTEND_PAYMENT_TIME_BTN
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-title="Extend payment time">
                                    <span class="ep-icon ep-icon_hourglass-plus txt-green"></span> Extend payment time
                                </a>
                            </li>
                        EXTEND_PAYMENT_TIME_BTN,
                        __SITE_URL . 'extend/popup_form/extend_time/bill/' . $orderBill['id_bill']
                    );
                } else {
                    $actions[] = sprintf(
                        <<<EXTEND_REQUEST_DETAILS_BTN
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-title="Extend request">
                                    <span class="ep-icon ep-icon_hourglass-plus txt-orange"></span> Extend request
                                </a>
                            </li>
                        EXTEND_REQUEST_DETAILS_BTN,
                        __SITE_URL . 'extend/popup_form/detail_admin/' . $orderBill['extend_request']
                    );
                }
            }
            //endregion make actions

            $output['aaData'][] = [
                'billId'            => orderNumber($orderBill['id_bill']),
                'billType'          => $orderBill['type']['show_name'],
                'billCreateDate'    => $orderBill['create_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'billUpdateDate'    => $orderBill['change_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'billStatus'        => <<<BILL_STATUS
                    <span class="status-b"><i class="ep-icon ep-icon_{$billStatuses[$orderBill['status']]['icon']} fs-20"></i> {$billStatuses[$orderBill['status']]['title']}</span>
                BILL_STATUS,
                'billActions'       => sprintf(
                    <<<DROPDOWN_ACTIONS
                        <div class="dropup">
                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                %s
                            </ul>
                        </div>
                    DROPDOWN_ACTIONS,
                    implode('', $actions)
                ),
            ];
        }

        jsonResponse('', 'success', $output);

    }

    public function ajax_admin_order_comments_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('read_all_orders');

        $request = request()->request;

        $output = [
			'sEcho'                     => $request->getInt('sEcho'),
			'iTotalRecords'             => 0,
			'iTotalDisplayRecords'      => 0,
			'aaData'                    => []
        ];

        /** @var Product_Orders_Model $productOrdersModel */
        $productOrdersModel = model(Product_Orders_Model::class);

        if (
            empty($orderId = (int) uri()->segment(3))
            || empty($order = $productOrdersModel->findOne($orderId))
        ) {
            jsonResponse('', 'success', $output);
        }

        /** @var Product_Order_Comments_Model $productOrderCommentsModel */
        $productOrderCommentsModel = model(Product_Order_Comments_Model::class);

        $commentsQueryParams = [
            'conditions'    => [
                'orderId'    => $orderId,
            ],
            'with'          => [
                'user'
            ],
            'order'         => [
                "`{$productOrderCommentsModel->getTable()}`.`create_date`" => "DESC",
            ],
            'skip'          => abs($request->getInt('iDisplayStart')),
            'limit'         => 10,
        ];

        $output['iTotalRecords'] = $output['iTotalDisplayRecords'] = $productOrderCommentsModel->countAllBy(array_diff_key($commentsQueryParams, array_flip(['with', 'skip', 'limit', 'order'])));
        $orderComments = empty($output['iTotalRecords']) ? [] : $productOrderCommentsModel->findAllBy($commentsQueryParams);

        foreach ($orderComments as $orderComment) {
            $output['aaData'][] = [
                'date'      => $orderComment['create_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'manager'   => $orderComment['user']['fname'] . ' ' . $orderComment['user']['lname'],
                'comment'   => $orderComment['message'],
            ];
        }

        jsonResponse('', 'success', $output);
    }
}

// End of file orders.php
// Location: /tinymvc/myapp/controllers/orders.php

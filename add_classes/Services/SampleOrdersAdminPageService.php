<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Buttons\ChatButton;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use Ishippers_Model;
use Sample_Orders_Model;
use Sample_Orders_Statuses_Model;
use Symfony\Component\HttpFoundation\Request;
use User_Model;

final class SampleOrdersAdminPageService implements SampleServiceInterface
{
    use SampleOrdersEntitiesAwareTrait;
    private const ORDER_BILL_TYPE = 7;

    /**
     * The samples repository.
     *
     * @var Sample_Orders_Model
     */
    private $sampleOrdersRepository;

    /**
     * The statuses repository.
     *
     * @var Sample_Orders_Statuses_Model
     */
    private $statusesRepository;

    /**
     * Creates the instance of the service.
     */
    public function __construct(
        ?Sample_Orders_Model $samples = null,
        ?Sample_Orders_Statuses_Model $statuses = null
    ) {
        $this->sampleOrdersRepository = $samples ?? model(Sample_Orders_Model::class);
        $this->statusesRepository = $statuses ?? model(Sample_Orders_Statuses_Model::class);
    }

    public function getTableContent(): array
    {
        $request = request();
        $per_page = $request->request->getInt('iDisplayLength', 10);
        $offset = $request->request->getInt('iDisplayStart', 0);
        $page = $offset / $per_page + 1;

        $conditions = array_merge(
            array('require_buyer_assigned' => true),
            dtConditions($request->request->all(), array(
                array('as' => 'shipper',           'key' => 'id_ishipper',      'type' => 'intval'),
                array('as' => 'status',            'key' => 'order_status',     'type' => 'intval'),
                array('as' => 'buyer',             'key' => 'id_buyer',         'type' => 'intval'),
                array('as' => 'seller',            'key' => 'id_seller',        'type' => 'intval'),
                array('as' => 'final_price_from',  'key' => 'price_from',       'type' => fn($price) => \priceToUsdMoney($price)),
                array('as' => 'final_price_to',    'key' => 'price_to',         'type' => fn($price) => \priceToUsdMoney($price)),
                array('as' => 'search',            'key' => 'keywords',         'type' => 'cleanInput|cut_str:200|decodeCleanInput'),
                array('as' => 'creation_from',     'key' => 'created_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'creation_to',       'key' => 'created_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'update_from',       'key' => 'updated_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'update_to',         'key' => 'updated_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'),
            )
        ));

        $ordering = array_column(dt_ordering($request->request->all(), array(
            'dt_id_order'       => 'id',
            'dt_price'          => 'final_price',
            'dt_create_date'    => 'creation_date',
            'dt_update_date'    => 'update_date',
        )), 'direction', 'column');

        $orders = $this->sampleOrdersRepository->paginate_samples($page, $per_page, $conditions, $ordering);

        if (empty($orders['data'])) {
            return $orders;
        }

        $table_content = array();

        $buyers_ids = array_column($orders['data'], 'id_buyer', 'id_buyer');
        $sellers_ids = array_column($orders['data'], 'id_seller', 'id_seller');
        $international_shippers_ids = array_filter(array_column($orders['data'], 'id_shipper', 'id_shipper'));

        /** @var User_Model $userRepository */
        $userRepository = model(User_Model::class);
        $involved_users_ids = array_filter(array_merge($buyers_ids, $sellers_ids));
        $involved_users_raw = $userRepository->getUsers(array('users_list' => implode(',', $involved_users_ids), 'additional' => 1, 'company_info' => 1));
        $involved_users = array_column($involved_users_raw, null, 'idu');

        if (!empty($international_shippers_ids)) {
            /** @var Ishippers_Model $shippersRepository */
            $shippersRepository = model(Ishippers_Model::class);
            $international_shippers_raw = $shippersRepository->get_shippers(array('shippers_list' => implode(',', $international_shippers_ids)));
            $international_shippers = array_column($international_shippers_raw, null, 'id_shipper');
        }

        foreach ($orders['data'] as $order) {
            $buffer = array();

            //region dt_id_order
            $buffer['dt_id_order'] = '<a href="' . __SITE_URL . 'sample_orders/popup_forms/order_details/' . $order['id'] . '" class="fancybox.ajax fancybox clearfix" data-title="Order details">' . orderNumber($order['id']) . '</a>';
            //endregion dt_id_order

            //region dt_users
            $seller = $buyer = '';

            $chatResourceType = (new ResourceOptions())->type(ResourceType::from(ResourceType::SAMPLE_ORDER))->id((string) $order['id'] ?: null);
            $sellerId = $order['id_seller'];
            $sellerData = $involved_users[$sellerId] ?? null;
            if (null !== $sellerData) {
                $sellerFullName = trim($sellerData['fname'] . ' ' . $sellerData['lname']);
                $safeSellerFullName = \cleanOutput($sellerFullName);
                //TODO: admin chat is hidden for now
                // $contactButton = 'active' === $sellerData['status'] ? \contactUserButton($sellerId, $chatResourceType, null, null, ['class' => 'btn-chat-now']) : null;
                // $contactButton = null;
                $contactButton = new ChatButton(['hide' => true, 'recipient' => $order['id_seller'], 'recipientStatus' => $involved_users[$order['id_seller']]['status'], 'module' => 35, 'item' => $order['id']], ['classes' => 'btn-chat-now', 'text' => '']);
                $contactButton = $contactButton->button();
                $companyPageButton = \sprintf('<a class="ep-icon ep-icon_building" title="View company\'s profile" href="%s" target="_blank"></a>', getCompanyURL($sellerData));
                $personalPageButton = \sprintf(
                    '<a class="ep-icon ep-icon_user" title="View personal page of %s" href="%s" target="_blank"></a>',
                    $safeSellerFullName,
                    getUserLink($sellerFullName, $sellerId, 'seller')
                );

                $seller = \sprintf(
                    <<<OUTPUT
                    <span>
                        <strong>Seller: </strong>
                        <a class="dt_filter" data-title="Seller" title="Filter by %s" data-value-text="%s" data-value="%s" data-name="id_seller">%s</a>
                    </span>
                    <div>%s%s%s</div>
                    OUTPUT,
                    $safeSellerFullName,
                    $safeSellerFullName,
                    $sellerId,
                    $safeSellerFullName,
                    $companyPageButton,
                    $personalPageButton,
                    $contactButton
                );
            }

            $buyerId = $order['id_buyer'];
            $buyerData = $involved_users[$buyerId] ?? null;
            if (null !== $buyerData) {
                $buyerFullName = trim($buyerData['fname'] . ' ' . $buyerData['lname']);
                $safeBuyerFullName = \cleanOutput($buyerFullName);
                //TODO: admin chat is hidden for now
                // $contactButton = 'active' === $buyerData['status'] ? \contactUserButton($buyerId, $chatResourceType, null, null, ['class' => 'btn-chat-now']) : null;
                // $contactButton = null;
                $contactButton = new ChatButton(['hide' => true, 'recipient' => $order['id_buyer'], 'recipientStatus' => $involved_users[$order['id_buyer']]['status'], 'module' => 35, 'item' => $order['id']], ['classes' => 'btn-chat-now', 'text' => '']);
                $contactButton = $contactButton->button();
                $personalPageButton = \sprintf(
                    '<a class="ep-icon ep-icon_user" title="View personal page of %s" href="%s" target="_blank"></a>',
                    $safeBuyerFullName,
                    getUserLink($buyerFullName, $buyerId, 'buyer')
                );
                $buyer = \sprintf(
                    <<<OUTPUT
                    <span>
                        <strong>Buyer: </strong>
                        <a class="dt_filter" data-title="Buyer" title="Filter by %s" data-value-text="%s" data-value="%s" data-name="id_buyer" target="_blank">%s</a>
                    </span>
                    <div>%s%s</div>
                    OUTPUT,
                    $safeBuyerFullName,
                    $safeBuyerFullName,
                    $buyerId,
                    $safeBuyerFullName,
                    $personalPageButton,
                    $contactButton
                );
            }

            $buffer['dt_users'] = $seller . $buyer;
            //endregion dt_users

            //region dt_shipper
            $buffer['dt_shiper'] = '&mdash;';
            if (!empty($order['id_shipper'])) {
                $buffer['dt_shiper'] =  '<div class="pull-left w-100pr tal">'
                                        . '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by freight forwarder" data-value="' . $order['id_shipper'] . '" data-value-text="' . $international_shippers[$order['id_shipper']]['shipper_original_name'] . '" data-title="Freight Forwarder" data-name="id_ishipper"></a>'
                                        . '</div>'
                                        . '<img class="mw-80 mh-40" src="' . __SITE_URL . 'public/img/ishippers_logo/' . $international_shippers[$order['id_shipper']]['shipper_logo'] . '" alt="' . $international_shippers[$order['id_shipper']]['shipper_original_name'] . '">';
            }
            //endregion dt_shipper

            $buffer['dt_price'] = '$' . get_price($order['final_price'], false);
            $buffer['dt_create_date'] = getDateFormat($order['creation_date']);
            $buffer['dt_update_date'] = getDateFormat($order['update_date']);
            $buffer['dt_problems'] = 'comming soon';

            //region dt_status
            $buffer['dt_status'] = '<span><i class="ep-icon ' . $order['status']['icon'] . ' fs-30"></i><br> ' . $order['status']['name'] . '</span>';
            //endregion dt_status

            //region dt_actions
            //region payment_detail_btn
            if ($order['status']['alias'] === 'new-order') {
                $payment_detail_btn =   '<li class="disabled">
                                            <a class="call-systmess" href="' . __SITE_URL . 'payments/popups_payment/manage_bills_by_type/' . self::ORDER_BILL_TYPE . '/' . $order['id'] . '" data-message="' . translate("systmess_info_order_doesnt_payment_yet") . '" data-type="info" title="Payment detail" data-title="Payment detail">
                                                <span class="ep-icon ep-icon_bank-notes"></span> Payment detail
                                            </a>
                                        </li>';
            } else {
                $payment_detail_btn =   '<li>
                                            <a class="fancybox.ajax fancybox" href="' . __SITE_URL . 'payments/popups_payment/manage_bills_by_type/' . self::ORDER_BILL_TYPE . '/' . $order['id'] . '" title="Payment detail" data-title="Payment detail">
                                                <span class="ep-icon ep-icon_bank-notes"></span> Payment detail
                                            </a>
                                        </li>';
            }
            //endregion payment_detail_btn

            //region $confirm_payment_btn
            if ($order['status']['alias'] === 'payment-processing') {
                $confirm_payment_btn =  '<li>
                                            <a class="confirm-dialog txt-green" href="#" data-callback="confirm_payment" data-message="' . translate("systmess_confirm_order_payment") . '" data-order="' . $order['id'] . '" title="Confirm payment">
                                                <span class="ep-icon ep-icon_ok-circle"></span> Confirm payment
                                            </a>
                                        </li>';
            } else {
                $confirm_payment_btn =  '<li class="disabled">
                                            <a class="call-systmess" href="#" data-message="' . translate("systmess_info_allowed_only_for_orders_in_payment_status") . '" data-type="info" title="Confirm payment">
                                                <span class="ep-icon ep-icon_ok-circle"></span> Confirm payment
                                            </a>
                                        </li>';
            }
            //endregion $confirm_payment_btn

            //region create_external_bill_btn
            $create_external_bill_btn = '';
            // if (empty($order['external_bills_requests']) && ($order['status']['alias'] === 'order-completed' || ($order['status']['alias'] === 'canceled' && array_search('confirmed', array_column($order['bills'], 'status')) !== false))) {
            //     $create_external_bill_btn = '<li>
            //                                     <a href="' . __SITE_URL . 'external_bills/popup_forms/add_form/sample_order/' .  $order['id'] . '" class="fancyboxValidateModal fancybox.ajax" data-title="Create external bills">
            //                                         <span class="ep-icon ep-icon_get-paid-stroke"></span> Create external bills
            //                                     </a>
            //                                 </li>';
            // }
            //endregion create_external_bill_btn

            $buffer['dt_actions'] = '<div class="dropdown">
                                        <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></a>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">' .
                                            $confirm_payment_btn .
                                            $payment_detail_btn .
                                            $create_external_bill_btn .
                                        '</ul>
                                    </div>';
            //endregion dt_actions

            $table_content[] = $buffer;
        }

        $orders['data'] = $table_content;

        return $orders;
    }
}

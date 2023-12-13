<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
            <span><?php echo 'Order ' . orderNumber($order['id']);?></span>
            <div class="dropdown pull-right">
                <button class="btn btn-default dropdown-toggle" type="button" id="actionsMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Actions on order
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu mnw-180" aria-labelledby="actionsMenu">
                    <?php if (empty($orderManager)) {?>
                        <li>
                            <a class="confirm-dialog pt-0 pb-0 pl-5" data-callback="assignManager" data-order="<?php echo $order['id'];?>" href="#" data-message="<?php echo translate('systmess_confirm_assign_as_manager_to_the_order', null, true);?>" title="Assign me as manager">
                                <i class="ep-icon ep-icon_user-plus fs-15"></i> Assign me as manager
                            </a>
                        </li>
                    <?php }?>

                    <?php if (\App\Common\Contracts\Order\ProductOrderStatusAlias::ORDER_PAID() === $order['order_status']['alias']) {?>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="confirm-dialog pt-0 pb-0 pl-5" data-callback="confirmOrderPaid" href="#" data-order="<?php echo $order['id'];?>" data-message="<?php echo translate('systmess_confirm_total_order_payment', null, true);?>" title="Confirm total order payment.">
                                    <i class="ep-icon ep-icon_thumbup txt-green fs-15"></i> Confirm order payment
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Confirm total order payment.">
                                    <i class="ep-icon ep-icon_thumbup fs-15"></i> Confirm order payment
                                </a>
                            <?php }?>
                        </li>
                    <?php }?>

                    <?php if (\App\Common\Contracts\Order\ProductOrderStatusAlias::PREPARING_FOR_SHIPPING() === $order['order_status']['alias']) {?>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="confirm-dialog pt-0 pb-0 pl-5" data-callback="changeOrderStatus" href="#" data-order="<?php echo $order['id'];?>" data-message="<?php echo translate('systmess_confirm_order_set_status_shipping_in_progress', null, true);?>" title="Change order status to Shipping in progress.">
                                    <i class="ep-icon ep-icon_truck-move txt-orange fs-15"></i> Set to Shipping in progress
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Change order status to Shipping in progress.">
                                    <i class="ep-icon ep-icon_truck-move fs-15"></i> Set to Shipping in progress
                                </a>
                            <?php }?>
                        </li>
                    <?php }?>

                    <?php if (\App\Common\Contracts\Order\ProductOrderStatusAlias::SHIPPING_IN_PROGRESS() === $order['order_status']['alias']) {?>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="confirm-dialog pt-0 pb-0 pl-5" data-callback="changeOrderStatus" href="#" data-order="<?php echo $order['id'];?>" data-message="<?php echo translate('systmess_confirm_order_set_status_ready_for_pickup', null, true);?>" title="Change order status to Ready for pickup.">
                                    <i class="ep-icon ep-icon_truck-ok txt-green fs-15"></i> Set to Ready for pickup
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Change order status to Ready for pickup.">
                                    <i class="ep-icon ep-icon_truck-ok fs-15"></i> Set to Ready for pickup
                                </a>
                            <?php }?>
                        </li>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="fancyboxValidateModal fancybox.ajax pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'order/popups_order/edit_tracking_info/' . $order['id'];?>" data-title="Edit tracking info" title="Edit tracking info">
                                    <i class="ep-icon ep-icon_file-edit fs-15"></i> Edit tracking info
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Edit tracking info">
                                    <i class="ep-icon ep-icon_file-edit fs-15"></i> Edit tracking info
                                </a>
                            <?php }?>
                        </li>
                    <?php }?>

                    <?php if (\App\Common\Contracts\Order\ProductOrderStatusAlias::SHIPPING_COMPLETED() === $order['order_status']['alias']) {?>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="confirm-dialog pt-0 pb-0 pl-5" data-callback="changeOrderStatus" href="#" data-order="<?php echo $order['id'];?>" data-message="<?php echo translate('systmess_confirm_order_set_status_order_completed', null, true);?>" title="Change order status to Order completed.">
                                    <i class="ep-icon ep-icon_ok-circle txt-green fs-15"></i> Set to Order completed
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Change order status to Order completed.">
                                    <i class="ep-icon ep-icon_ok-circle fs-15"></i> Set to Order completed
                                </a>
                            <?php }?>
                        </li>
                    <?php }?>

                    <?php if (in_array($order['order_status']['alias'], \App\Common\Contracts\Order\ProductOrderStatusAlias::getGroupStatuses('passed'))) {?>
                        <li>
                            <?php
                                $btnTitle = empty($order['external_bills_requests']) ? 'Create external bills' : 'View external bills';
                                $btnIconColor = empty($order['external_bills_requests']) ? 'txt-orange' : 'txt-green';
                            ?>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="fancyboxValidateModal fancybox.ajax pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'external_bills/popup_forms/add_form/order/' . $order['id'];?>" data-title="<?php echo $btnTitle;?>" title="<?php echo $btnTitle;?>">
                                    <i class="ep-icon ep-icon_billing <?php echo $btnIconColor;?> fs-15"></i> <?php echo $btnTitle;?>
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="<?php echo $btnTitle;?>">
                                    <i class="ep-icon ep-icon_billing fs-15"></i> <?php echo $btnTitle;?>
                                </a>
                            <?php }?>
                        </li>
                    <?php } else {?>
                        <li>
                            <?php if (id_session() === $order['ep_manager']) {?>
                                <a class="fancyboxValidateModal fancybox.ajax pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'order/popups_order/cancel_order/' . $order['id'];?>" data-title="Cancel the order" title="Cancel the order">
                                    <i class="ep-icon ep-icon_minus-circle txt-red fs-15"></i> Cancel the order
                                </a>
                            <?php } else {?>
                                <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Cancel the order">
                                    <i class="ep-icon ep-icon_minus-circle fs-15"></i> Cancel the order
                                </a>
                            <?php }?>
                        </li>
                        <li>
                            <?php if (empty($order['extend_request'])) {?>
                                <?php if (id_session() === $order['ep_manager']) {?>
                                    <a class="fancyboxValidateModal fancybox.ajax pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'extend/popup_form/extend_time/order/' . $order['id'];?>" data-title="Extend order status time" title="Extend order status time">
                                        <i class="ep-icon ep-icon_hourglass-plus txt-green fs-15"></i> Extend order status time
                                    </a>
                                <?php } else {?>
                                    <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Extend order status time">
                                        <i class="ep-icon ep-icon_hourglass-plus fs-15"></i> Extend order status time
                                    </a>
                                <?php }?>
                            <?php } else {?>
                                <?php if (id_session() === $order['ep_manager']) {?>
                                    <a class="fancyboxValidateModal fancybox.ajax pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'extend/popup_form/detail_admin/' . $order['extend_request'];?>" data-title="Extend request" title="Extend request">
                                        <i class="ep-icon ep-icon_hourglass-plus txt-orange fs-15"></i> Extend request
                                    </a>
                                <?php } else {?>
                                    <a class="call-systmess bg-gray-lighter txt-gray-nlight pt-0 pb-0 pl-5" href="#" data-message="<?php echo translate('systmess_error_order_action_for_assigned_manager', null, true);?>" data-type="info" title="Extend request">
                                        <i class="ep-icon ep-icon_hourglass-plus fs-15"></i> Extend request
                                    </a>
                                <?php }?>
                            <?php }?>
                        </li>
                    <?php }?>

                    <?php if (have_right('add_order_comments')) {?>
                        <li>
                            <a class="fancybox.ajax fancyboxValidateModalDT pt-0 pb-0 pl-5" href="<?php echo __SITE_URL . 'product_order_comments/popup_forms/add_comment/' . $order['id'];?>" data-title="Add comment" title="Add comment" data-table="orderCommentsDt">
                                <i class="ep-icon ep-icon_comments-stroke fs-15"></i> Add comment
                            </a>
                        </li>
                    <?php }?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-6 col-lg-3">
        <div class="panel panel-primary">
            <div class="panel-heading">Seller</div>
            <div class="panel-body">
                <p class="mb-5">
                    <span class="txt-bold">Registered on: </span>
                    <?php echo empty($orderSeller['registration_date']) ? '&mdash;' : $orderSeller['registration_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Last active: </span>
                    <?php echo empty($orderSeller['last_active']) ? '&mdash;' : $orderSeller['last_active']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Company legal name: </span>
                    <a href="<?php echo getCompanyURL($orderSeller['seller_company']);?>" target="_blank" title="Seller company page">
                        <?php echo $orderSeller['seller_company']['legal_name_company'];?>
                    </a>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Company representative: </span>
                    <a href="<?php echo getUserLink($orderSeller['fname'] . ' ' . $orderSeller['lname'], $orderSeller['idu'], 'seller')?>" target="_blank" title="Seller profile">
                        <?php echo $orderSeller['fname'] . ' ' . $orderSeller['lname'];?>
                    </a>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Email: </span>
                    <?php echo $orderSeller['email'];?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Address: </span>
                    <?php echo $orderSeller['address'] ?: '&mdash;';?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Phone: </span>
                    <?php echo empty($orderSeller['phone']) ? '&mdash;' : $orderSeller['phone_code'] . ' ' . $orderSeller['phone'];?>
                </p>
                <p>
                    <span class="txt-bold">Fax: </span>
                    <?php echo empty($orderSeller['fax']) ? '&mdash;' : $orderSeller['fax_code'] . ' ' . $orderSeller['fax'];?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-lg-3">
        <div class="panel panel-primary">
            <div class="panel-heading">Buyer</div>
            <div class="panel-body">
                <p class="mb-5">
                    <span class="txt-bold">Registered on: </span>
                    <?php echo empty($orderBuyer['registration_date']) ? '&mdash;' : $orderBuyer['registration_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Last active: </span>
                    <?php echo empty($orderBuyer['last_active']) ? '&mdash;' : $orderBuyer['last_active']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Company legal name: </span>
                    <?php echo $orderBuyer['buyer_company']['company_legal_name'] ?: '&mdash;';?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Company representative: </span>
                    <a href="<?php echo getUserLink($orderBuyer['fname'] . ' ' . $orderBuyer['lname'], $orderBuyer['idu'], 'buyer')?>" target="_blank" title="Buyer profile">
                        <?php echo $orderBuyer['fname'] . ' ' . $orderBuyer['lname'];?>
                    </a>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Email: </span>
                    <?php echo $orderBuyer['email'];?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Address: </span>
                    <?php echo $orderBuyer['address'] ?: '&mdash;';?>
                </p>
                <p class="mb-5">
                    <span class="txt-bold">Phone: </span>
                    <?php echo empty($orderBuyer['phone']) ? '&mdash;' : $orderBuyer['phone_code'] . ' ' . $orderBuyer['phone'];?>
                </p>
                <p>
                    <span class="txt-bold">Fax: </span>
                    <?php echo empty($orderBuyer['fax']) ? '&mdash;' : $orderBuyer['fax_code'] . ' ' . $orderBuyer['fax'];?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-lg-3">
        <div class="panel panel-primary">
            <div class="panel-heading">Freight Forwarder</div>
            <div class="panel-body">
                <?php if (!empty($orderShipper)) {?>
                    <?php if (\App\Common\Contracts\Shipper\ShipperType::SHIPPER() === $order['shipper_type']) {?>
                        <p class="mb-5">
                            <span class="txt-bold">Registered on: </span>
                            <?php echo empty($orderShipper['registration_date']) ? '&mdash;' : $orderShipper['registration_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Last active: </span>
                            <?php echo empty($orderShipper['last_active']) ? '&mdash;' : $orderShipper['last_active']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Company legal name: </span>
                            <a href="<?php echo getShipperURL($orderShipper['shipper_company']);?>" target="_blank" title="Shipper company page">
                                <?php echo $orderShipper['shipper_company']['legal_co_name'];?>
                            </a>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Company representative: </span>
                            <a href="<?php echo getUserLink($orderShipper['fname'] . ' ' . $orderShipper['lname'], $orderShipper['idu'], 'shipper')?>" target="_blank" title="Shipper profile">
                                <?php echo $orderShipper['fname'] . ' ' . $orderShipper['lname'];?>
                            </a>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Email: </span>
                            <?php echo $orderShipper['email'];?>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Address: </span>
                            <?php echo $orderShipper['address'] ?: '&mdash;';?>
                        </p>
                        <p class="mb-5">
                            <span class="txt-bold">Phone: </span>
                            <?php echo empty($orderShipper['phone']) ? '&mdash;' : $orderShipper['phone_code'] . ' ' . $orderShipper['phone'];?>
                        </p>
                        <p>
                            <span class="txt-bold">Fax: </span>
                            <?php echo empty($orderShipper['fax']) ? '&mdash;' : $orderShipper['fax_code'] . ' ' . $orderShipper['fax'];?>
                        </p>
                    <?php } elseif (\App\Common\Contracts\Shipper\ShipperType::INTERNATIONAL_SHIPPER() === $order['shipper_type']) {?>
                        <p>
                            <span class="txt-bold">International shipper: </span>
                            <?php echo $orderShipper['shipper_original_name'];?>
                        </p>
                    <?php }?>
                <?php } else {?>
                    Not assigned
                <?php }?>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-lg-3">
        <div class="panel panel-primary">
            <div class="panel-heading">Manager</div>
            <div class="panel-body">
                <?php if (!empty($orderManager)) {?>
                    <p class="mb-5">
                        <span class="txt-bold">Name: </span>
                        <?php echo $orderManager['fname'] . ' ' . $orderManager['lname'];?>
                    </p>
                    <p>
                        <span class="txt-bold">Email: </span>
                        <?php echo $orderManager['email'];?>
                    </p>
                <?php } else {?>
                    Not assigned
                <?php }?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-lg-6">
        <table style="border-collapse:collapse" class="lh-10 bd-1-gray w-100pr mb-16">
            <tr>
                <td width="150px" class="p-5 bd-1-gray">
                    <span class="txt-bold">Order status</span>
                </td>
                <td class="pl-5 pt-2 bd-1-gray">
                    <i class="<?php echo 'ep-icon fs-20 ' . $order['order_status']['icon'];?>"></i>
                    <?php echo $order['order_status']['status'];?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Create date</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php echo empty($order['order_date']) ? '&mdash;' : $order['order_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Update date</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php echo empty($order['update_date']) ? '&mdash;' : $order['update_date']->format(\App\Common\PUBLIC_DATETIME_FORMAT);?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Status countdown</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php $now = new \DateTimeImmutable();?>
                    <?php if (in_array($order['order_status']['alias'], \App\Common\Contracts\Order\ProductOrderStatusAlias::getGroupStatuses('passed'))) {?>
                        <div class="txt-green txt-bold">Finished</div>
                    <?php } elseif ($order['status_countdown'] <= $now) {?>
                        <div class="txt-red">Expired!</div>
                    <?php } else {?>
                        <?php $dateDiff = $now->diff($order['status_countdown']);?>
                        <div class="<?php echo $dateDiff->days < 1 ? 'txt-orange' : 'txt-green';?>"><?php echo sprintf('%02d days %02d : %02d', $dateDiff->days, $dateDiff->h, $dateDiff->i);?></div>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Cancelation request</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php if (empty($order['cancel_request'])) {?>
                        &mdash;
                    <?php } elseif (1 === $order['cancel_request']) {?>
                        There is a "Cancel request" opened for this order.
                        <a href="<?php echo __SITE_URL . 'order/popups_order/order_cancel_requests/' . $order['id'];?>" target="_blank" title="Cancelation request" class="fancybox fancybox.ajax" data-title="Cancel order requests">View details</a>
                    <?php } else {?>
                        There is a "Cancel request" opened for this order.
                        <a href="<?php echo __SITE_URL . 'order/popups_order/order_cancel_requests/' . $order['id'];?>" target="_blank" title="Cancelation request" class="fancybox fancybox.ajax" data-title="Cancel order requests">View details</a>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Dispute</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php if (empty($order['dispute_opened'])) {?>
                        &mdash;
                    <?php } elseif (1 === $order['dispute_opened']) {?>
                        There is a "Dispute" opened for this order.
                        <a href="<?php echo __SITE_URL . 'dispute/all/order/' . $order['id'];?>" target="_blank" title="View dispute">View details</a>
                    <?php } else {?>
                        There is a "Dispute" opened for this order.
                        <a href="<?php echo __SITE_URL . 'dispute/all/order/' . $order['id'];?>" target="_blank" title="View dispute">View details</a>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Delivery from</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php echo $order['ship_from'] ?: '&mdash;'?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Delivery to</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php echo $order['ship_to'] ?: '&mdash;'?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray">
                    <span class="txt-bold">Tracking info</span>
                </td>
                <td class="p-5 bd-1-gray">
                    <?php echo $order['tracking_info'] ?: '&mdash;'?>
                </td>
            </tr>
        </table>

        <table style="border-collapse:collapse" class="lh-10 bd-1-gray w-100pr mb-16">
            <tr class="tal bg-blue txt-white lh-35">
                <td colspan="6">
                    <span class="p-5 fs-14 font-arial"></span>
                    Ordered products
                </td>
            </tr>
            <tr class="lh-25">
                <td class="p-5 mw-10 bd-1-gray tal txt-bold fs-12 font-arial">
                    #
                </td>
                <td class="p-5 mw-75 bd-1-gray tal txt-bold fs-12 font-arial">
                    HS code
                </td>
                <td class="p-5 bd-1-gray tal txt-bold fs-12 font-arial">
                    Product
                </td>
                <td class="p-5 mw-35 bd-1-gray tal txt-bold fs-12 font-arial">
                    Quantity
                </td>
                <td class="p-5 mw-80 bd-1-gray tar txt-bold fs-12 font-arial">
                    Amount
                </td>
                <td class="p-5 mw-80 bd-1-gray tar txt-bold fs-12 font-arial">
                    Total amount
                </td>
            </tr>
            <?php $itemNr = 1;?>
            <?php foreach ($orderedItems ?: [] as $orderedItem) {?>
                <tr>
                    <td class="p-5 mw-10 bd-1-gray tal">
                        <?php echo $itemNr++;?>
                    </td>
                    <td class="p-5 mw-75 bd-1-gray tal">
                        <?php echo $orderedItem['snapshot']['hs_tariff_number'];?>
                    </td>
                    <td class="p-5 bd-1-gray tal">
                        <span class="display-b mb-5">
                            <a href="<?php echo makeItemUrl($orderedItem['id_item'], $orderedItem['snapshot']['title']);?>" title="<?php echo cleanOutput($orderedItem['snapshot']['title']);?>" target="_blank">
                                <?php echo $orderedItem['snapshot']['title'];?>
                            </a>
                        </span>
                        <?php if (!empty($orderedItem['detail_ordered'])) {?>
                            <span class="txt-gray-light fs-12"><?php echo $orderedItem['detail_ordered'];?></span>
                        <?php }?>
                    </td>
                    <td class="p-5 mw-35 bd-1-gray tal">
                        <?php echo $orderedItem['quantity_ordered'];?>
                    </td>
                    <td class="p-5 mw-80 bd-1-gray tar">
                        <?php echo get_price($orderedItem['price_ordered']);?>
                    </td>
                    <td class="p-5 mw-80 bd-1-gray tar">
                        <?php echo get_price($orderedItem['price_ordered']->multiply($orderedItem['quantity_ordered']));?>
                    </td>
                </tr>
            <?php }?>
            <tr>
                <td class="p-5 bd-1-gray txt-bold tar" colspan="5">
                    Subtotal
                </td>
                <td class="p-5 bd-1-gray txt-bold tar">
                    <?php echo get_price($order['price']);?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray txt-bold tar" colspan="5">
                    Discount
                </td>
                <td class="p-5 bd-1-gray txt-bold tar">
                    <?php echo $order['discount'] . '%';?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray txt-bold tar" colspan="5">
                    Final amount
                </td>
                <td class="p-5 bd-1-gray txt-bold tar">
                    <?php echo get_price($order['final_price']);?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray txt-bold tar" colspan="5">
                    Delivery amount
                </td>
                <td class="p-5 bd-1-gray txt-bold tar">
                    <?php echo get_price($order['ship_price']);?>
                </td>
            </tr>
            <tr>
                <td class="p-5 bd-1-gray txt-bold tar" colspan="5">
                    Total
                </td>
                <td class="p-5 bd-1-gray txt-bold tar">
                    <?php echo get_price($order['final_price']->add($order['ship_price']));?>
                </td>
            </tr>
        </table>

        <table id="orderTimelineDt" class="data table-striped table-bordered vam-table mt-15 mb-0 w-100pr">
            <thead>
                <tr>
                    <th class="tal" colspan="3">Order timeline</th>
                </tr>
                <tr>
                    <th class="dt_date w-135">Date</th>
                    <th class="dt_member w-75">Member</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="col-xs-12 col-lg-6">
        <table id="envelopes-datagrid--detached-list" class="data table-striped table-bordered vam-table mb-0 w-100pr">
            <thead>
                <tr>
                    <th class="tal" colspan="6">Order documents</th>
                </tr>
                <tr>
                    <th class="dt-details">#</th>
                    <th class="dt-envelope">Document</th>
                    <th class="dt-status">Status</th>
                    <th class="dt-created-at">Created at</th>
                    <th class="dt-updated-at">Updated at</th>
                    <th class="dt-actions"></th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall"></tbody>
        </table>

        <table id="orderBillsDt" class="data table-striped table-bordered vam-table mb-0 w-100pr">
            <thead>
                <tr>
                    <th class="tal" colspan="6">Billing</th>
                </tr>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_type">Document</th>
                    <th class="dt_created_at w-150">Created at</th>
                    <th class="dt_updated_at w-150">Updated at</th>
                    <th class="dt_status w-100">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <table id="orderCommentsDt" class="data table-striped table-bordered vam-table mb-0 w-100pr">
            <thead>
                <tr>
                    <th class="tal" colspan="6">Admin comments</th>
                </tr>
                <tr>
                    <th class="dt_date w-125">Date</th>
                    <th class="dt_manager mw-250">Manager</th>
                    <th class="dt_comment">Comment</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    let orderTimelineDt;
    let orderBillsDt;
    let orderCommentsDt;

    $(globalThis).on('order:success-add-comment', function () {
        orderCommentsDt.fnDraw();
    });

    $(globalThis).on('billing:success-decline-bill', function () {
        orderBillsDt.fnDraw();
    });

    $(globalThis).on('billing:success-extend-payment-time', function () {
        orderBillsDt.fnDraw();
    });

    $(globalThis).on('billing:success-decline-extend-request', function () {
        orderBillsDt.fnDraw();
    });

    $(globalThis).on('billing:success-confirm-extend-request', function () {
        orderBillsDt.fnDraw();
    });

    $(globalThis).on('order:success-edit-tracking-info', function () {
        globalThis.location.reload();
    });

    $(globalThis).on('external-bills:success-add-request-by-order', function () {
        globalThis.location.reload();
    });

    $(globalThis).on('order:success-cancel-order', function () {
        globalThis.location.reload();
    });

    $(globalThis).on('order:success-extend-payment-time', function () {
        globalThis.location.reload();
    });

    $(globalThis).on('order:success-confirm-extend-request', function () {
        globalThis.location.reload();
    });

    $(globalThis).on('order:success-decline-extend-request', function () {
        globalThis.location.reload();
    });

    async function confirmBill(btn) {
        try {
            const data = await postRequest('<?php echo __SITE_URL . 'billing/ajax_bill_operations/confirm_bill';?>', {bill: $(btn).data('bill')});
            const { mess_type: messageType, message } = data;

            systemMessages(message, `message-${messageType}`);

            if ('success' == messageType) {
                orderBillsDt.fnDraw();
            }
        } catch(e) {
            onRequestError(e);
        }
    }

    async function assignManager (btn) {
        try {
            const data = await postRequest('<?php echo __SITE_URL . 'order/assign_order';?>', {order: $(btn).data('order')});
            const { mess_type: messageType, message } = data;

            systemMessages(message, `message-${messageType}`);

            if ('success' == messageType) {
                globalThis.location.reload();
            }
        } catch(e) {
            onRequestError(e);
        }
    }

    async function confirmOrderPaid (btn) {
        try {
            const data = await postRequest('<?php echo __SITE_URL . 'order/ajax_order_operations/confirm_order_paid';?>', {order: $(btn).data('order')});
            const { mess_type: messageType, message } = data;

            systemMessages(message, `message-${messageType}`);

            if ('success' == messageType) {
                globalThis.location.reload();
            }
        } catch(e) {
            onRequestError(e);
        }
    }

    async function changeOrderStatus (btn) {
        try {
            const data = await postRequest('<?php echo __SITE_URL . 'order/ajax_order_operations/change_order_status';?>', {order: $(btn).data('order')});
            const { mess_type: messageType, message } = data;

            systemMessages(message, `message-${messageType}`);

            if ('success' == messageType) {
                globalThis.location.reload();
            }
        } catch(e) {
            onRequestError(e);
        }
    }

    $(function() {
        orderTimelineDt = $('#orderTimelineDt').dataTable( {
            "sDom": '<"top"f>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'orders/ajax_admin_order_timeline_dt/' . $order['id'];?>",
            "sServerMethod": "POST",
            "ordering": false,
            "aoColumnDefs": [
                {"sClass": "w-135 vam dt-body-left", "aTargets": ['dt_date'], "mData": "timelineDate", 'bSortable': false},
                {"sClass": "w-75 vam dt-body-left", "aTargets": ['dt_member'], "mData": "timelineMember", 'bSortable': false},
                {"sClass": "vam dt-body-left", "aTargets": ['dt_actions'], "mData": "timelineActions", 'bSortable': false},
            ],
            "sPaginationType": "full_numbers",
            "pageLength": 10,
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };
                try {
                    const data = await postRequest(sSource, aoData);
                    const { mess_type: messageType, message } = data;
                    renderData = data;

                    if (messageType !== "success") {
                        systemMessages(message, `message-${messageType}`);
                    }
                } catch(e) {
                    onRequestError(e);
                } finally {
                    fnCallback(renderData);
                }
            },
            "fnDrawCallback": function(oSettings) {

            }
        });

        //Order documents DT
        getScript('<?php echo asset('public/plug_admin/js/documents/orders/detached-grid.js', 'legacy'); ?>', true).then(function () {
            DetachedDocumentsGridModule.default(
                <?php echo json_encode([
                    'listEnvelopesUrl'    => getUrlForGroup('/order_documents/ajax_admin_operation/list-detached-envelopes'),
                    'addEnvelopeTabsUrl'  => getUrlForGroup('/order_documents/start_envelope_edit'),
                    'downloadDocumentUrl' => getUrlForGroup('/order_documents/ajax_admin_operation/download-document'),
                ]); ?>,
                {
                    'datagrid': '#envelopes-datagrid--detached-list',
                    'datepicker': "#envelopes-datagrid--detached-list .date-picker",
                    'rowDetails': "#envelopes-datagrid--detached-list .js-open-row-details",
                },
                <?php echo json_encode(['orderId' => $order['id']]);?>
            );
        });

        orderBillsDt = $('#orderBillsDt').dataTable( {
            "sDom": '<"top"f>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'orders/ajax_admin_order_bills_dt/' . $order['id'];?>",
            "sServerMethod": "POST",
            "ordering": false,
            "aoColumnDefs": [
                {"sClass": "w-135 vam dt-body-left", "aTargets": ['dt_id'], "mData": "billId", 'bSortable': false},
                {"sClass": "w-75 vam dt-body-left", "aTargets": ['dt_type'], "mData": "billType", 'bSortable': false},
                {"sClass": "w-150 vam dt-body-left", "aTargets": ['dt_created_at'], "mData": "billCreateDate", 'bSortable': false},
                {"sClass": "w-150 vam dt-body-left", "aTargets": ['dt_updated_at'], "mData": "billUpdateDate", 'bSortable': false},
                {"sClass": "w-100 vam dt-body-left", "aTargets": ['dt_status'], "mData": "billStatus", 'bSortable': false},
                {"sClass": "vam dt-body-center", "aTargets": ['dt_actions'], "mData": "billActions", 'bSortable': false},
            ],
            "sPaginationType": "full_numbers",
            "pageLength": 10,
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };
                try {
                    const data = await postRequest(sSource, aoData);
                    const { mess_type: messageType, message } = data;
                    renderData = data;

                    if (messageType !== "success") {
                        systemMessages(message, `message-${messageType}`);
                    }
                } catch(e) {
                    onRequestError(e);
                } finally {
                    fnCallback(renderData);
                }
            },
            "fnDrawCallback": function(oSettings) {

            }
        });

        orderCommentsDt = $('#orderCommentsDt').dataTable( {
            "sDom": '<"top"f>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'orders/ajax_admin_order_comments_dt/' . $order['id'];?>",
            "sServerMethod": "POST",
            "ordering": false,
            "aoColumnDefs": [
                {"sClass": "w-125 vam dt-body-left", "aTargets": ['dt_date'], "mData": "date", 'bSortable': false},
                {"sClass": "mw-250 vam dt-body-left", "aTargets": ['dt_manager'], "mData": "manager", 'bSortable': false},
                {"sClass": "vam dt-body-left", "aTargets": ['dt_comment'], "mData": "comment", 'bSortable': false},
            ],
            "sPaginationType": "full_numbers",
            "pageLength": 10,
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };
                try {
                    const data = await postRequest(sSource, aoData);
                    const { mess_type: messageType, message } = data;
                    renderData = data;

                    if (messageType !== "success") {
                        systemMessages(message, `message-${messageType}`);
                    }
                } catch(e) {
                    onRequestError(e);
                } finally {
                    fnCallback(renderData);
                }
            },
            "fnDrawCallback": function(oSettings) {

            }
        });

        $('body').on('click', '.toogle_bill_detail', function(e){
            e.preventDefault();
            var toggle_element = $(this).data('toggle');
            if($(this).hasClass('active')){
                $(this).removeClass('active');
            } else{
                $(this).addClass('active');
            }
            $('#'+toggle_element).toggle();
            $.fancybox.reposition();
        });
    });
</script>

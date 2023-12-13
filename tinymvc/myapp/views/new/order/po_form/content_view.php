<div class="container-fluid-modal">
    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mt-15 mb-15">
                <span class="minfo-sidebar-ttl__txt">General information</span>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">Issue date</label>
            <?php echo getDateFormat($order['purchase_order']['invoice']['issue_date'], 'Y-m-d H:i:s', 'j M, Y'); ?>
        </div>
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">Due date</label>
            <?php echo getDateFormat($order['purchase_order']['due_date'], 'Y-m-d', 'j M, Y'); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">PO number</label>
            <?php echo cleanOutput($order['purchase_order']['invoice']['po_number']); ?>
        </div>
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">Order Discount</label>
            <?php echo normalize_discount($order['discount']); ?> %
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mb-15">
                <span class="minfo-sidebar-ttl__txt">Items information</span>
            </div>
        </div>

        <?php if (!empty($order['purchase_order']['invoice']['additional_items'])) { ?>
            <div class="col-12">
                <div class="warning-alert-b">
                    <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                    <span>
                        The Seller added New Items to Purchase Order (PO), please take a look and make sure you accept the changes.
                        Pay attention on Final Price as well.
                    </span>
                </div>
            </div>
        <?php } ?>

        <div class="col-12">
            <table id="purchase-order--ordered-items" class="main-data-table mt-25">
                <thead>
                    <tr>
                        <th>Ordered items</th>
                        <th class="w-150">Quantity</th>
                        <th class="w-150">Unit price</th>
                        <th class="w-150">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['purchase_order']['invoice']['ordered_items'])) { ?>
                        <?php foreach ($order['purchase_order']['invoice']['ordered_items'] as $item) { ?>
                            <?php views()->display('new/order/po_form/item_details_view', array(
                                'item'          => $item,
                                'is_additional' => false,
                            )); ?>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($order['purchase_order']['invoice']['additional_items'])) { ?>
            <div class="col-12">
                <table id="purchase-order--additional-items" class="main-data-table mt-25">
                    <thead>
                        <tr>
                            <th>Additional items</th>
                            <th class="w-150">Quantity</th>
                            <th class="w-150">Unit price</th>
                            <th class="w-150">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['purchase_order']['invoice']['additional_items'] as $item) { ?>
                            <?php views()->display('new/order/po_form/item_details_view', array(
                                'item'          => $item,
                                'is_additional' => true,
                            )); ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <div class="col-12">
            <table class="main-data-table mt-25">
                <tbody>
                    <tr>
                        <td class="tar vam">
                            <strong>Subtotal</strong>
                        </td>
                        <td class="w-150">
                            <strong>$ <?php echo get_price($order['price'], false); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar vam">
                            <strong>Order Discount</strong>
                        </td>
                        <td class="w-150 vam">
                            <strong><?php echo normalize_discount($order['discount']); ?> %</strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="tar">
                            <strong>Amount Due</strong>
                        </td>
                        <td class="w-150 vam">
                            <strong>$ <?php echo get_price($order['final_price'], false); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mb-15">
                <span class="minfo-sidebar-ttl__txt">Shipping Insurance</span>
                <div class="minfo-sidebar-ttl__line"></div>
            </div>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">
                Amount, in USD
            </label>
            <?php echo get_price($order['purchase_order']['insurance']['amount'], false); ?>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">
                Notes
            </label>
            <?php echo !empty($order['purchase_order']['insurance']['notes']) ? cleanOutput($order['purchase_order']['insurance']['notes']) : '&mdash;'; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mb-15">
                <span class="minfo-sidebar-ttl__txt">Shipping information</span>
            </div>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">From address</label>
            <?php echo cleanOutput($order['ship_from']); ?>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">To address</label>
            <?php echo cleanOutput($order['ship_to']); ?>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">
                Type of shipment
                <a class="info-dialog" data-content="#type_of_shipment--info" data-title="What is: <?php echo cleanOutput($shipping_type['type_name']); ?>?" title="What is: <?php echo cleanOutput($shipping_type['type_name']); ?>?">
                    <i class="ep-icon ep-icon_info fs-16"></i>
                </a>
                <div id="type_of_shipment--info" class="display-n">
                    <?php echo cleanOutput($shipping_type['type_description']); ?>
                </div>
            </label>
            <?php echo cleanOutput($shipping_type['type_name']); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">
                Estimate time for packaging
                <a class="info-dialog" data-message="The number of the days that the seller can spent on the product packaging." data-title="What is: Estimate time for packaging?" title="What is: Estimate time for packaging?"><i class="ep-icon ep-icon_info fs-16"></i></a>
            </label>
            <?php $timeline_countdowns = json_decode($order['timeline_countdowns'], true); ?>
            <?php echo !empty($timeline_countdowns['time_for_packaging']) ? cleanOutput($timeline_countdowns['time_for_packaging']) .' days' : '&mdash;'; ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">
                Available area for delivering, in km
                <a class="info-dialog" data-message="The maximum distance the seller can deliver the products to the freight forwarder or another person assigned by the buyer at the seller’s warehouse or another named place." data-title="What is: Available area for delivering?" title="What is: Available area for delivering?"><i class="ep-icon ep-icon_info fs-16"></i></a>
            </label>
            <?php echo (int) $order['seller_delivery_area']; ?>
        </div>
    </div>
    <div class="row">
        <?php $package = json_decode($order['package_detail'], true); ?>
        <div class="col-12 col-lg-6">
            <label class="input-label txt-gray">Box/Package size (LxWxH)</label>
            <?php echo !empty($package) ? implode(' x ', array(cleanOutput($package['length']), cleanOutput($package['width']), cleanOutput($package['height']))) . ' cm<sup>3</sup>' : '&mdash;'; ?>
        </div>

        <div class="col-12 col-lg-6">
            <label class="input-label txt-gray">Box/Package weight</label>
            <?php echo !empty($package) ? cleanOutput($package['weight']) . ' kg' : '&mdash;'; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <label class="input-label txt-gray">Notes (displayed on invoice)</label>
            <?php echo !empty($order['purchase_order']['invoice']['notes']) ? cleanOutput($order['purchase_order']['invoice']['notes']) : '&mdash;'; ?>
        </div>
    </div>
</div>
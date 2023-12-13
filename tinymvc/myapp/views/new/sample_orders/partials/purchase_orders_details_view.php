<div class="container-fluid-modal">
    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mt-15 mb-15">
                <span class="minfo-sidebar-ttl__txt">General information</span>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">Issue date</label>
            <?php echo getDateFormatIfNotEmpty($purchase_order['invoice']['issue_date'] ?? null, DATE_ATOM, 'j M, Y'); ?>
        </div>
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">Due date</label>
            <?php echo getDateFormatIfNotEmpty($purchase_order['invoice']['due_date'] ?? null, DATE_ATOM, 'j M, Y'); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6">
            <label class="input-label txt-gray">PO number</label>
            <?php echo cleanOutput($purchase_order['number']); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="minfo-sidebar-ttl mb-15">
                <span class="minfo-sidebar-ttl__txt">Items information</span>
            </div>
        </div>

        <div class="col-12">
            <table id="purchase-order--ordered-items" class="main-data-table mt-25">
                <thead>
                    <tr>
                        <th>Ordered items</th>
                        <th class="w-150">Quantity</th>
                        <th class="w-150">Full Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products) { ?>
                        <?php foreach ($products as $product) { ?>
                            <tr>
                                <td data-title="Ordered item">
                                    <div class="grid-text">
                                        <div class="grid-text__item">
                                            <a class="order-detail__prod-link" href="<?php echo cleanOutput(makeItemUrl($product['item_id'], $product['name'])); ?>" target="_blank">
                                                <?php echo cleanOutput($product['name']); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <?php echo cleanOutput($product['detail_ordered']); ?>
                                </td>
                                <td data-title="Quantity">
                                    <?php echo cleanOutput($product['quantity']); ?>
                                </td>
                                <td data-title="Full Price">
                                    $ <?php echo get_price($product['total_price'] ?? 0, false); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <td class="tar" colspan="2">
                            <strong>Amount Due</strong>
                        </td>
                        <td class="w-150 vam">
                            <strong>$ <?php echo get_price($order['final_price'], false); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-12">
            <table class="main-data-table mt-25">
                <tbody>

                </tbody>
            </table>
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
            <?php echo cleanOutput($order['ship_from'] ?? '—'); ?>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">To address</label>
            <?php echo cleanOutput($order['ship_to'] ?? '—'); ?>
        </div>

        <div class="col-12">
            <label class="input-label txt-gray">
                Freight Forwarder
            </label>

            <?php if (null !== $shipper) { ?>
                <span class="pr-10">
                    <a class="link-black" href="<?php echo cleanOutput($shipper['shipper_website']); ?>" target="_blank">
                        <img class="h-30 vam"
                            src="<?php echo cleanOutput(getDisplayImageLink(array('{FILE_NAME}' => $shipper['shipper_logo'] ?? null), 'international_shippers.main')); ?>"
                            alt="<?php echo cleanOutput($shipper['shipper_original_name']); ?>">
                        <?php echo cleanOutput($shipper['shipper_original_name']); ?>
                    </a>
                </span>
            <?php } else { ?>
                &mdash;
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <label class="input-label txt-gray">Notes (displayed on invoice)</label>
            <?php echo cleanOutput($purchase_order['invoice']['notes'] ?? $order['description'] ?? '—'); ?>
        </div>
    </div>
</div>

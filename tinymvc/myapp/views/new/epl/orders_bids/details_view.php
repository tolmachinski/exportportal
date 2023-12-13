<div class="wr-modal-flex inputs-40" id="upcoming-orders-bid--form--wrapper">
    <div class="modal-flex__form">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Status</label>
                        <?php echo translate("orders_bids_dashboard_dt_column_bid_status_{$bid['quote_status']}_text", null, true); ?>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Incoterms</label>
                        <?php if (!empty($shipment_type)) { ?>
                            <?php echo cleanOutput($shipment_type['type_name']); ?>
                            <a class="info-dialog"
                                data-message="<?php echo cleanOutput($shipment_type['type_description']); ?>"
                                data-title="<?php echo cleanOutput($shipment_type['type_name']); ?>"
                                title="<?php echo cleanOutput($shipment_type['type_name']); ?>">
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </a>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray">Shipping price (in USD)</label>
                        <?php echo get_price($bid['shipping_price'], false); ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray">Container Freight Station</label>
                        <?php if (!empty($bid['shipment_cfs'])) { ?>
                            <?php echo cleanOutput($bid['shipment_cfs']); ?>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray">Freight Forwarder</label>
                        <?php if (!empty($bid['shipment_ff'])) { ?>
                            <?php echo cleanOutput($bid['shipment_ff']); ?>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Scheduling pickup</label>
                        <?php if ('shipper' === $bid['shipment_pickup']) { ?>
                            Freight Forwarder will pick up the products from the seller's location.
                        <?php } else { ?>
                            Seller must be delivering goods to freight forwarder location.
                        <?php } ?>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Pickup date</label>
                        <?php echo cleanOutput(getDateFormatIfNotEmpty($bid['pickup_date'], 'Y-m-d H:i:s', 'j M, Y')); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Delivery interval</label>
                        From <?php echo cleanOutput((int) $bid['delivery_days_from']); ?> to <?php echo cleanOutput((int) $bid['delivery_days_to']); ?> days.
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="input-label txt-gray">Delivery start date</label>
                        <?php echo cleanOutput(getDateFormatIfNotEmpty($bid['delivery_date'], 'Y-m-d H:i:s', 'j M, Y')); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label class="input-label txt-gray">Insurance options</label>
                        <table id="upcoming-orders-bid--formfield--additional-items" class="main-data-table">
                            <thead>
                                <tr>
                                    <th>Insurance description</th>
                                    <th class="w-200">Amount, in USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($insurance_options)) { ?>
                                    <?php foreach ($insurance_options as $insurance_option) { ?>
                                        <tr class="insurance">
                                            <td data-title="Insurance description">
                                                <strong>
                                                    <?php echo cleanOutput($insurance_option['title']); ?>
                                                </strong>
                                                <div>
                                                    <?php echo cleanOutput($insurance_option['description']); ?>
                                                </div>
                                            </td>
                                            <td data-title="Amount, in USD">
                                                <?php echo cleanOutput(get_price($insurance_option['amount'], false)); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="2">
                                            No shipping insurance options
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray">Buyer's comment</label>
                        <?php if (!empty($bid['comment_user'])) { ?>
                            <?php echo cleanOutput($bid['comment_user']); ?>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray">Freight Forwarder's comment</label>
                        <?php if (!empty($bid['comment_shipper'])) { ?>
                            <?php echo cleanOutput($bid['comment_shipper']); ?>
                        <?php } else { ?>
                            &mdash;
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        var wrapper = $('#upcoming-orders-bid--form--wrapper');
        var additionalItemsTable = $('#upcoming-orders-bid--formfield--additional-items');
        var normalizeTables = function(tables) {
            if(tables.length !== 0){
                if($(window).width() < 768) {
                    tables.addClass('main-data-table--mobile');
				} else {
                    tables.removeClass('main-data-table--mobile');
				}
			}
        };
        var cleanOrientationChangeHandler = function (element) {
			$(element).off('resizestop', onOrientationChange);
		};
        var onOrientationChange = function (wrapper, tables) {
            return function () {
                var normalize = function () {
                    normalizeTables(tables);
                };

                if (!$('body').find('#' + wrapper.attr('id')).length) {
                    cleanOrientationChangeHandler(this);

                    return;
                }

                normalize();
                setTimeout(normalize, 500);
            };
		};

        $(window).on('resizestop', onOrientationChange(wrapper, additionalItemsTable));

        mobileDataTable(additionalItemsTable, false);
        normalizeTables(additionalItemsTable);
    });
</script>

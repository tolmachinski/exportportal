<div class="wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="assign_shipper">
        <ul class="nav nav-tabs nav--borders" style="flex:none;" role="tablist">
            <li class="nav-item">
                <div class="nav-link disabled pb-12">Freight Forwarders: </div>
            </li>
            <li
                class="nav-item"
            >
                <a class="nav-link pb-12<?php if(!empty($order_quote_requests)|| (empty($order_quote_requests) && empty($ishippers_quotes))){?> active<?php }?>" href="#ep_shippers" aria-controls="title" role="tab" data-toggle="tab">
                    <span class="d-none d-sm-inline">Export Portal</span>
                    <span class="d-inline d-sm-none">EP</span>
                </a>
            </li>

            <?php if(!empty($ishippers_quotes)){?>
                <li class="nav-item">
                    <a class="nav-link pb-12<?php if(empty($order_quote_requests)){?> active<?php }?>" href="#ishippers" aria-controls="title" role="tab" data-toggle="tab">
                        <span class="d-none d-sm-inline">International</span>
                        <span class="d-inline d-sm-none">INTL</span>
                    </a>
                </li>
            <?php }?>
        </ul>
		<div class="modal-flex__content mh-500 pb-0">
            <div class="tab-content tab-content--borders js-assign-list-ff pt-15 pb-0">
                <div
                    role="tabpanel"
                    class="tab-pane fade <?php if(!empty($order_quote_requests)|| (empty($order_quote_requests) && empty($ishippers_quotes))){?>show active<?php }?>"
                    id="ep_shippers"
                    data-link="order_shipping_quote_detail"
                    data-title="Shipping rate details"
                >
                    <?php if(!empty($order_quote_requests)){?>
                        <table id="dtShippingResponses" class="main-data-table">
                            <thead>
                                <tr>
                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                    <th class="dt_radio"></th>
                                    <?php }?>
                                    <th class="dt_shipper">Company</th>
                                    <th class="dt_delivery_days">Delivery time</th>
                                    <th class="dt_price">Price, (USD)</th>
                                    <?php if($shipper_assigned && have_right('buy_item')){?>
                                    <th class="dt_details"></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_quote_requests as $order_quote_request){?>
                                    <tr>
                                        <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                        <td class="dt_radio">
                                            <label class="custom-radio">
                                                <input class="radio-shipper validate[required]" type="radio" name="shipper" value="<?php echo $order_quote_request['id_quote'];?>">
                                            </label>
                                        </td>
                                        <?php }?>
                                        <td class="dt_shipper">
                                            <div class="d-flex">
                                                <div class="img-b tac pull-left mr-10 w-55 h-40 relative-b">
                                                    <img class="mw-55 mh-40 img-position-center" src="<?php echo getShipperLogo($order_quote_request['id'], $order_quote_request['logo'], 0);?>">
                                                </div>
                                                <div class="text-b pull-left">
                                                    <div class="top-b lh-20 clearfix"><?php echo $order_quote_request['co_name'];?></div>
                                                    <div class="w-100pr lh-20 txt-gray"><?php echo $order_quote_request['type_name'];?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="dt_delivery_time">
                                            in <?php echo $order_quote_request['delivery_days_from'];?> &mdash; <?php echo $order_quote_request['delivery_days_to'];?> days
                                        </td>
                                        <td class="dt_price">
                                            <?php echo get_price($order_quote_request['shipping_price'], false);?>
                                        </td>
                                        <?php if($shipper_assigned && have_right('buy_item')){?>
                                        <td class="dt_details">
                                            <a
                                                class="btn btn-success btn-block fancyboxValidateModal fancybox.ajax"
                                                href="<?php echo __SITE_URL . 'order/popups_order/order_shipping_quote_detail/' . $order['id'] . '/' . $order_quote_request['id_quote'];?>"
                                                title="Shipping rate details"
                                                data-title="Shipping rate details"
                                            >Details</a>
                                        </td>
                                        <?php }?>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                        <script>
                            var dtShippingResponses = $('#dtShippingResponses').dataTable({
                                bProcessing: true,
                                aoColumnDefs: [
                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                    { sClass: "w-20 vam tac", aTargets: ['dt_radio'], bSortable: false },
                                    <?php }?>
                                    { sClass: "vam", aTargets: ['dt_shipper'], bSortable: false },
                                    { sClass: "w-100 vam text-nowrap dn-xl", aTargets: ['dt_delivery_days'], bSortable: false },
                                    { sClass: "w-100 vam text-nowrap", aTargets: ['dt_price'], bSortable: false },
                                    <?php if($shipper_assigned && have_right('buy_item')){?>
                                    { sClass: "vam w-80", aTargets: ['dt_details'], bSortable: false },
                                    <?php }?>
                                ],
                                sDom: '<"top pt-0"p>rt<"bottom pb-0"p><"clear">',
                                sorting : [[2,'asc'], [1,'asc']],
                                sPaginationType: "simple_numbers"
                            });
                        </script>
                    <?php } else{?>
                        <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> There are no Export Portal Freight Forwarders’ Rates for this order.</div>
                    <?php }?>
                </div>

                <?php if(!empty($ishippers_quotes)){?>
                    <div
                        role="tabpanel"
                        class="tab-pane fade<?php if(empty($order_quote_requests)){?> show active<?php }?>"
                        id="ishippers"
                        data-link="order_ishipping_quote_detail"
                        data-title="International Freight Forwarder’s Rate Details"
                    >
                        <table id="dtIShippingQuotes" class="main-data-table">
                            <thead>
                                <tr>
                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                    <th class="dt_radio"></th>
                                    <?php }?>
                                    <th class="dt_shipper">Company</th>
                                    <th class="dt_delivery_days">Delivery Time</th>
                                    <th class="dt_price">Price, USD</th>
                                    <?php if($shipper_assigned && have_right('buy_item')){?>
                                    <th class="dt_details"></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ishippers_quotes as $ishipper_key => $ishipper_quote){?>
                                    <tr>
                                        <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                        <td class="dt_radio">
                                            <label class="custom-radio">
                                                <input class="radio-shipper validate[required]" type="radio" name="shipper" value="<?php echo $ishipper_key;?>">
                                            </label>
                                        </td>
                                        <?php }?>
                                        <td class="dt_shipper">
                                            <div class="grid-text">
                                                <div class="grid-text__item">
                                                    <div class="ishipper-logo">
                                                        <div class="ishipper-logo__img">
                                                            <img class="image" src="<?php echo __IMG_URL;?>public/img/ishippers_logo/<?php echo $ishippers[$ishipper_key]['shipper_logo'];?>" alt="<?php echo $ishippers[$ishipper_key]['shipper_original_name'];?>">
                                                        </div>
                                                        <span class="ishipper-logo__txt"><?php echo $ishippers[$ishipper_key]['shipper_original_name'];?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="dt_delivery_days">
                                            in <?php echo $ishipper_quote['delivery_from'];?> &mdash; <?php echo $ishipper_quote['delivery_to'];?> days
                                        </td>
                                        <td class="dt_price">
                                            <?php echo get_price($ishipper_quote['amount'], false);?>
                                        </td>
                                        <?php if($shipper_assigned && have_right('buy_item')){?>
                                        <td class="dt_details">
                                            <a
                                                class="btn btn-success btn-block fancyboxValidateModal fancybox.ajax"
                                                href="<?php echo __SITE_URL . 'order/popups_order/order_ishipping_quote_detail/' . $order['id'] . '/' . $ishipper_key;?>"
                                                title="International Freight Forwarder’s Rate Details"
                                                data-title="International Freight Forwarder’s Rate Details">Details</a>
                                        </td>
                                        <?php }?>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                        <script>
                            var dtIShippingQuotes = $('#dtIShippingQuotes').dataTable({
                                ordering: false,
                                bProcessing: true,
                                aoColumnDefs: [
                                    <?php if(!$shipper_assigned && have_right('buy_item')){?>
                                    { sClass: "w-20 vam tac", aTargets: ['dt_radio'], bSortable: false },
                                    <?php }?>
                                    { sClass: "vam", aTargets: ['dt_shipper'], bSortable: false },
                                    { sClass: "w-100 vam text-nowrap dn-xl", aTargets: ['dt_delivery_days'], bSortable: false },
                                    { sClass: "w-100 vam text-nowrap", aTargets: ['dt_price'], bSortable: false },
                                    <?php if($shipper_assigned && have_right('buy_item')){?>
                                    { sClass: "vam w-80", aTargets: ['dt_details'], bSortable: false },
                                    <?php }?>
                                ],
                                sDom: 'rt<"clear">'
                            });
                        </script>
                    </div>
                <?php }?>
            </div>
        </div>
        <?php if(!$shipper_assigned && have_right('buy_item')){?>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-success <?php echo empty($order_quote_requests) && empty($ishippers_quotes) ? 'disabled' : '';?>" type="submit">Assign</button>
            </div>
        </div>
        <?php }?>
	</form>
</div>


<script>
    $(document).ready(function(){
        if(($('.main-data-table').length > 0) && ($(window).width() < 768)){
            $('.main-data-table').addClass('main-data-table--mobile');
        }

        mobileDataTable($('#dtIShippingQuotes'));
        mobileDataTable($('#dtShippingResponses'));

        $(".radio-shipper").first().prop("checked", true);

        $('.modal-flex__form a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fancybox.update();
        });
    });

    <?php if(!$shipper_assigned && have_right('buy_item')){?>
        function assign_shipper(form){
            var $list = $('.js-assign-list-ff');

            var $radio = $list.find('.radio-shipper:checked');
            var shipperKey = parseInt($radio.val(), 10);

            if(shipperKey <= 0 || !$radio.length){
                systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
                return false;
            }

            var $tab = $radio.closest('.tab-pane');
            var link = $tab.data('link');
            var title = $tab.data('title');
            var href = __site_url + 'order/popups_order/' + link + '/<?php echo $order['id'];?>/' + shipperKey;

            openFancyboxValidateModal(href, title);
        }
    <?php }?>
</script>

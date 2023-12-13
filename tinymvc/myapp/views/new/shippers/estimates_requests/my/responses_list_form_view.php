<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__content">
        <div class="container-fluid-modal">
            <table id="dtShippingResponses" class="main-data-table">
                <thead>
                <tr>
                    <th class="shipper_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_details_text"); ?></th>
                    <th class="price_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_price_text"); ?></th>
                    <th class="delivery_dy"><?php echo translate("shipping_estimates_dashboard_dt_column_delivery_text"); ?></th>
                    <th class="actions_dt"></th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        var onServerRequest = function(url, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, 'message-' + response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onDatagridDraw = function() {
            hideDTbottom(this);
            $('.estimate-response-popover[data-toggle="popover"]').popover({});
                mobileDataTable(dtShippingResponses);
        };
        var dtShippingResponses = $('#dtShippingResponses')
        if((dtShippingResponses.length > 0) && ($(window).width() < 768)){
            dtShippingResponses.addClass('main-data-table--mobile');
        }

        dtShippingResponses.dataTable({
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: __site_url + "shippers/ajax_estimates_responses_list_dt/<?php echo $estimate; ?>",
            aoColumnDefs: [
                { sClass: "",             aTargets: ['shipper_dt'],  mData: "shipper",  bSortable: false },
                { sClass: "w-175",        aTargets: ['price_dt'],    mData: "price",    bSortable: true },
                { sClass: "w-175",        aTargets: ['delivery_dy'], mData: "delivery", bSortable: false },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],  mData: "actions",  bSortable: false },
            ],
            sDom: '<"top"l>rt<"bottom"ip><"clear">',
            sorting : [],
            sPaginationType: "full_numbers",
            fnServerData: onServerRequest,
            fnDrawCallback: onDatagridDraw,
        });
    });
</script>

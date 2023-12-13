<?php views('admin/order/ep_orders/filter_view');?>

<div class="row">
    <div class="ship col-xs-12" >
        <h3 class="titlehdr">Orders</h3>
        <div class="clearfix mt-10 wr-filter-list"></div>
        <ul class="pull-left mb-10 clearfix">
            <li class="pull-left mr-5">
                <a class="dt_filter btn btn-default active" data-name="status_group" data-title="Order status" data-value="" data-value-text="All orders">All orders</a>
            </li>
            <li class="pull-left mr-5">
                <a class="dt_filter btn btn-default" data-name="status_group" data-value="new" data-value-text="New orders">New orders</a>
            </li>
            <li class="pull-left mr-5">
                <a class="dt_filter btn btn-default" data-name="status_group" data-value="active" data-value-text="Active orders">Active orders</a>
            </li>
            <li class="pull-left mr-5">
                <a class="dt_filter btn btn-default" data-name="status_group" data-value="passed" data-value-text="Passed orders">Passed orders</a>
            </li>
        </ul>

        <table cellspacing="0" cellpadding="0" id="epOrders" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="mw-40 dt_order_number">Order number</th>
                    <th class="dt_create_date">Date created</th>
                    <th class="dt_update_date">Update date</th>
                    <th class="dt_products_price">Products price</th>
                    <th class="dt_delivery_price">Delivery price</th>
                    <th class="dt_status">Status</th>
                    <th class="mw-35 dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    var epOrders;
    var ordersFilters;

    $(function() {
        epOrders = $('#epOrders').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'orders/ajax_admin_all_orders_dt';?>",
            "sServerMethod": "POST",
            "sorting": [[ 0, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "mw-40 vam tac", "aTargets": ['dt_order_number'], "mData": "orderNumber"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_create_date'], "mData": "orderCreateDate"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_update_date'], "mData": "orderUpdateDate"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_products_price'], "mData": "orderProductsPrice", 'bSortable': false},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_delivery_price'], "mData": "orderDeliveryPrice", 'bSortable': false},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_status'], "mData": "orderStatus"},
                {"sClass": "mw-35 tac vam", "aTargets": ['dt_actions'], "mData": "orderActions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "aLengthMenu": [[25, 50, 100], [25, 50, 100]],
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };

                if (!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            epOrders.fnDraw();
                        },
                        onSet: function(filter, filterObj) {
                            switch (filterObj.name) {
                                case 'status_group':
                                    if($(filter).get(0).tagName == 'A'){
                                        $(filter).addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                                    } else{
                                        var $btn = $('a[data-name="' + filterObj.name + '"][data-value="' + filterObj.value + '"]');
                                        $btn.addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                                    }

                                    break;
                                case 'start_date_from':
                                    $('input[name="start_date_to"]').datepicker("option", "minDate", $(filter).datepicker("getDate"));

                                    break;
                                case 'start_date_to':
                                    $('input[name="start_date_from"]').datepicker("option", "maxDate", $(filter).datepicker("getDate"));

                                    break;
                            }
                        },
                        onDelete: function(filter){
                            switch (filter.name) {
                                case 'status_group':
                                    $btn = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]');
                                    $li = $btn.parent('li');
                                    $li.siblings('li').find('a').removeClass('active');
                                    $btn.addClass('active');

                                    break;
                                case 'start_date_from':
                                    $('input[name="start_date_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'start_date_to':
                                    $('input[name="start_date_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                            }
                        },
                        onReset: function(){
							$('.dt_filter .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
						}
                    });
                }

                aoData = aoData.concat(ordersFilters.getDTFilter());

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
    });
</script>

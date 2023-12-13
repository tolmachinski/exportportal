<script src="<?php echo __FILES_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>
<script type="text/javascript">
	var dtNewOrders;
    var ordersFilters;

	var assign_manager = function(opener){
		var $this = $(opener);
		var order = $this.attr('href').split('-');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>order/assign_order',
			data: { order: order[1]},
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtNewOrders.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			},
			error: function(){alert('ERROR')}
		});
	}

    $(document).ready(function(){
        dtNewOrders = $('#dtNewOrders').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>order/ajax_admin_new_orders_dt",
            "sServerMethod": "POST",
            "sorting": [[ 0, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "w-150 vam tac", "aTargets": ['dt_id_order'], "mData": "dt_id_order"},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_seller'], "mData": "dt_seller", "bSortable": false},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_buyer'], "mData": "dt_buyer"},
                {"sClass": "w-200 vat tac", "aTargets": ['dt_ship_to'], "mData": "dt_ship_to"},
                {"sClass": "w-100 vat tal", "aTargets": ['dt_price'], "mData": "dt_price"},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_date'], "mData": "dt_date"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_status_date'], "mData": "dt_status_date"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_problems'], "mData": "dt_problems", 'bSortable': false},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_status'], "mData": "dt_status"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dtNewOrders.fnDraw();
                        },
                        onSet: function(callerObj, filterObj) {
                            if (filterObj.name == 'start_date') {
                                $("#finish_date").datepicker("option", "minDate", $("#start_date").datepicker("getDate"));
                            }
                            if (filterObj.name == 'finish_date') {
                                $("#start_date").datepicker("option", "maxDate", $("#finish_date").datepicker("getDate"));
                            }

                            if(filterObj.name == 'order_status'){
                                if($(callerObj).get(0).tagName == 'A'){
                                    $(callerObj).parent('li').addClass('active').siblings('li').removeClass('active');
                                } else{
                                    var $btn = $('a[data-name="' + filterObj.name + '"][data-value="' + filterObj.value + '"]').parent('li');
                                    $btn.addClass('active').siblings('li').removeClass('active');
                                }
                            }

                            if(filterObj.name == 'expire_status'){
                                $(callerObj).addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                            }
                        },
                        onDelete: function(filter){
                            if(filter.name == 'order_status'){
                                $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
                                $li.siblings('li').removeClass('active');
                                $li.addClass('active');
                            }
                            if(filter.name == 'expire_status'){
                                $btn = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]');
                                $li = $btn.parent('li');
                                $li.siblings('li').find('a').removeClass('active');
                                $btn.addClass('active');
                            }
                        },
                        onReset: function(){
                            $('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
                                minDate: null,
                                maxDate: null
                            });
                        }
                    });
                }

                aoData = aoData.concat(ordersFilters.getDTFilter());
				$.ajax( {
					"dataType": 'JSON',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);
                        $('.statuses_counters li > a > span.orders_counter').text(0);
                        $.each(data.orders_statuses_count, function(status, status_obj){
                            $('.statuses_counters li > a.status_'+status+' span.orders_counter').text(status_obj.status_counter);
                        });
					}
				});
            },
            "fnDrawCallback": function( oSettings ) {
                $.each($('span.countdown-dt'), function(){
                    var expire = $(this).data('expire');
                    var selectedDate = new Date().valueOf() + expire;
                    $(this).countdown(selectedDate.toString(), function(event) {
                        var format_clock = '<div class="txt-green">%D days %H : %M</div>';
                        if(expire < 86400000){
                            format_clock = '<div class="txt-orange">%D days %H : %M</div>';
                        }
                        $(this).html(event.strftime(format_clock));
                    }).on('finish.countdown', function(event) {
                        $(this).html('<div class="txt-red">Expired!</div>');
                    });
                });
            }
        });

	idStartItemNew = <?php echo $last_order_notassigned_id;?>;
	startCheckAdminNewItems('order/ajax_order_operations/check_new_order_notassigned', idStartItemNew);
    });
</script>

<?php tmvc::instance()->controller->view->display('admin/order/not_asigned/filter_view'); ?>

<div class="mt-10 wr-filter-list clearfix"></div>

<div class="row">
    <div class="ship col-xs-12" >
        <h3 class="titlehdr">Not manager assigned orders</h3>
        <div class="dropdown pull-left">
            <button class="btn btn-default dropdown-toggle" type="button" id="statuses_counters" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Filter by status
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu statuses_counters" aria-labelledby="statuses_counters">
                <li class="display-n"><a class="dt_filter active" data-name="order_status" data-title="Order status" data-value="" data-value-text="All orders">All orders</a></li>
                <?php foreach($orders_status as $status){?>
                    <li>
                        <a class="dt_filter status_<?php echo $status['alias'];?>" data-name="order_status" data-value="<?php echo $status['id'];?>" data-value-text="<?php echo $status['status'];?>"><?php echo $status['status'];?> (<span class="orders_counter">0</span>)</a>
                    </li>
                <?php }?>
            </ul>
        </div>
        <ul class="mb-10 pull-right statuses_counters">
			<!--EXPIRE FILTERS-->
			<li class="display-n"><a class="dt_filter btn btn-default active" data-name="expire_status" data-title="Expire status" data-value="" data-value-text="All">All</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-danger status_expired" data-name="expire_status" data-value="expired" data-value-text="Expired">Expired (<span class="orders_counter">0</span>)</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-warning status_expire_soon" data-name="expire_status" data-value="expire_soon" data-value-text="Expire soon">Expire soon (<span class="orders_counter">0</span>)</a></li>
		</ul>
        <table cellspacing="0" cellpadding="0" id="dtNewOrders" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_order">Nr.</th>
                    <th class="dt_seller">Seller</th>
                    <th class="dt_buyer">Buyer</th>
                    <th class="dt_ship_to">Ship to</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_date">Update date</th>
                    <th class="dt_status_date">Status countdown</th>
                    <th class="dt_problems">Problems</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script src="<?php echo __FILES_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>
<script type="text/javascript">
    var dtMyOrders;
    var ordersFilters;
    var $change_status_btn = null;
    /* Formating function for row details */
    function fnFormatDetails(nTr){
		var aData = dtMyOrders.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
			sOut += '<tr><td class="tar w-150">Ship from address:</td><td>' + aData['dt_ship_from'] + '</td></tr>';
			sOut += '<tr><td class="tar">Ship to address:</td><td>' + aData['dt_ship_to'] +'</td></tr>';
			sOut += '</table> </div>';
		return sOut;
    }
	var confirm_order_paid = function(opener){
		var $this = $(opener);
		var order = $this.data('order');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>order/ajax_order_operations/confirm_order_paid',
			data: { order: order},
            beforeSend: function(){
                $('#dtMyOrders').hide();
                showLoader($('#dtMyOrders').parent());
            },
			dataType: 'JSON',
			success: function(resp){
                $('#dtMyOrders').show();
                hideLoader($('#dtMyOrders').parent());
				if(resp.mess_type == 'success')
					dtMyOrders.fnDraw();
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		})
	}

	var change_order_status = function(opener){
		var $this = $(opener);
		var order = $this.data('order');
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>order/ajax_order_operations/change_order_status',
			data: { order: order},
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success')
					dtMyOrders.fnDraw();
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		})
	}
	function extend_action_callback(){
        dtMyOrders.fnDraw();
        closeFancyBox();
	}

	function dt_redraw_callback(){
        dtMyOrders.fnDraw(false);
	}

    $(document).ready(function(){
        dtMyOrders = $('#dtMyOrders').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>order/ajax_admin_manager_orders_dt",
            "sServerMethod": "POST",
            "sorting": [[ 5, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "w-100 vam tac", "aTargets": ['dt_id_order'], "mData": "dt_id_order"},
                {"sClass": "w-200 vat tal", "aTargets": ['dt_users'], "mData": "dt_users", "bSortable": false},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_shiper'], "mData": "dt_shiper", 'bSortable': false},
                {"sClass": "w-100 vam tal", "aTargets": ['dt_price'], "mData": "dt_price"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_create_date'], "mData": "dt_create_date"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_date'], "mData": "dt_date"},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_bills'], "mData": "dt_bills", 'bSortable': false},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_status_date'], "mData": "dt_status_date"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_problems'], "mData": "dt_problems", 'bSortable': false},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_status'], "mData": "dt_status"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dtMyOrders.fnDraw();
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
            "fnDrawCallback": function(oSettings) {
                $.each($('.dataTable span.countdown-dt'), function(){
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

		$('body').on('click', 'a[rel=order_details]', function(e) {
            e.preventDefault();
			var $thisBtn = $(this);
		    var nTr = $thisBtn.parents('tr')[0];

			if (dtMyOrders.fnIsOpen(nTr))
				dtMyOrders.fnClose(nTr);
		    else
				dtMyOrders.fnOpen(nTr, fnFormatDetails(nTr), 'details');

			$thisBtn.find('.ep-icon').toggleClass('ep-icon_plus ep-icon_minus');
		});

        idStartItemNew = <?php echo $last_order_assigned_id;?>;
        startCheckAdminNewItems('order/ajax_order_operations/check_new_order_assigned', idStartItemNew);

        $('body').on('click', '.show_form_btn', function(e){
            e.preventDefault();
            $change_status_btn = $(this);
            $('#form_submit_btn').data('callback', $change_status_btn.data('callback'));
            $('#form_submit_btn').data('message', $change_status_btn.data('message'));
            $('#in_modal_content').hide();
            $('#in_modal_form').show();
            $.fancybox.reposition();
        });
        $('body').on('click', '.cancel_in_modal_form', function(e){
            $change_status_btn = null;
            e.preventDefault();
            $('#in_modal_content').show();
            $('#in_modal_form').hide();
            $.fancybox.reposition();
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

<?php tmvc::instance()->controller->view->display('admin/order/manager_assigned/filter_view'); ?>

<div class="mt-10 wr-filter-list clearfix"></div>

<div class="row">
    <div class="ship col-xs-12" >
        <h3 class="titlehdr">Orders assigned to me</h3>
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
        <table cellspacing="0" cellpadding="0" id="dtMyOrders" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_order">Nr.</th>
                    <th class="dt_users">Seller/Buyer</th>
                    <th class="dt_shiper">Freight Forwarder</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_create_date">Create date</th>
                    <th class="dt_date">Update date</th>
					<th class="dt_bills">Bills</th>
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

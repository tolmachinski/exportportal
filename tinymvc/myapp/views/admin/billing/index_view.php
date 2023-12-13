<script src="<?php echo __SITE_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>
<script type="text/javascript">
var dtMyBills;
    var confirm_bill = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        billChangeStatus($this, bill, 'confirm');
    }

	function billChangeStatus(btn, bill, status){
		var $thisBtn = btn;
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>billing/ajax_bill_operations/'+status+'_bill',
			data: { bill : bill },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );
				if(resp.mess_type == 'success'){
					dtMyBills.fnDraw();
				}

			}
		});
	}

	function dt_redraw_callback(){
        dtMyBills.fnDraw();
	}

$(document).ready(function(){
	var myFilters;
	dtMyBills = $('#dtMyBills').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bFilter": false,
		"sAjaxSource": "<?php echo __SITE_URL?>billing/ajax_bills_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
            {"sClass": "w-50 tac vat", "aTargets": ['dt_checkbox'], "mData": "dt_checkbox" , 'bSortable': false},
            {"sClass": "w-100 tac vat", "aTargets": ['dt_bill'], "mData": "dt_bill" , 'bSearchable': true},
            {"sClass": "w-120 vat", "aTargets": ['dt_bill_type'], "mData": "dt_bill_type" , 'bSearchable': true},
            {"sClass": "w-100 tac vat", "aTargets": ['dt_order'], "mData": "dt_order"},
            {"sClass": "vat", "aTargets": ['dt_buyer'], "mData": "dt_buyer"},
            {"sClass": "w-150 tac vat", "aTargets": ['dt_amount'], "mData": "dt_amount"},
            {"sClass": "w-50 tac vat", "aTargets": ['dt_status'], "mData": "dt_status"},
            {"sClass": "w-120 tac vat", "aTargets": ['dt_pay_method'], "mData": "dt_pay_method"},
            {"sClass": "tac w-200", "aTargets": ['dt_all_dates'], "mData": "dt_all_dates"},
            {"sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
		],
		"sPaginationType": "full_numbers",
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if(!myFilters){
                myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    callBack: function(){
                        dtMyBills.fnDraw();
                    },
                    onSet: function(callerObj, filterObj){
                        if(filterObj.name == 'status'){
                            if($(callerObj).get(0).tagName == 'A'){
                                $(callerObj).addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                            } else{
                                var $btn = $('a[data-name="' + filterObj.name + '"][data-value="' + filterObj.value + '"]');
                                $btn.addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                            }
                        }
                        if(filterObj.name == 'expire_status'){
                            $(callerObj).addClass('active').parent('li').siblings('li').find('a').removeClass('active');
                        }

                        if(filterObj.name == 'date_from'){
                            $(".date_to").datepicker("option","minDate", $(".date_from").datepicker("getDate")+1);
                        }
                        if(filterObj.name == 'date_to'){
                            $(".date_from").datepicker("option","maxDate", $(".date_to").datepicker("getDate")+1);
                        }
                    },
                    onDelete: function(filter){
                        if(filter.name == 'status'){
                            $btn = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]');
                            $li = $btn.parent('li');
                            $li.siblings('li').find('a').removeClass('active');
                            $btn.addClass('active');
                        }
                        if(filter.name == 'expire_status'){
                            $btn = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]');
                            $li = $btn.parent('li');
                            $li.siblings('li').find('a').removeClass('active');
                            $btn.addClass('active');
                        }
                    }
                });
            }

            aoData = aoData.concat(myFilters.getDTFilter());

			$.ajax( {
			    "dataType": 'json',
			    "type": "POST",
			    "url": sSource,
			    "data": aoData,
			    "success": function (data, textStatus, jqXHR) {
                    if(data.mess_type == 'error')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);

                    $('.statuses_counters li > a > span.bills_counter').text(0);
                    $.each(data.bills_statuses_count, function(status, status_obj){
						$('.statuses_counters li > a[data-value="'+status+'"] span.bills_counter').text(status_obj.counter);
					});
			    },
			} );
		},
		"fnDrawCallback": function( oSettings ) {}
	});

	idStartItemNew = <?php echo $last_bills_id;?>;
	startCheckAdminNewItems('billing/ajax_bill_operations/check_new', idStartItemNew);
});

    var confirm_new_amount = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        var $thisParent = $this.closest('.amount-edit-'+bill);
        var amount = $this.siblings('input[name=amount]').val();
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>billing/ajax_bill_operations/change_amount',
            data: { bill : bill, amount: amount },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    dtMyBills.fnDraw();
                }
            }
        });
    }

    var edit_amount = function(btn){
        var $this = $(btn);
        var bill = $this.data('bill');
        $this.hide().siblings('.total_paid_amount').hide().siblings('.amount-edit-'+bill).show();
    }

    var cancel_amount = function(btn){
        var $this = $(btn);
        var $edit_block = $this.parent();
        var bill = $this.data('bill');
        $edit_block.hide().siblings('.total_paid_amount').show().siblings('.btn-edit-amount').show();
    }

    var reimburse_user = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>external_bills/ajax_external_bills_operation/add_request/reimburse',
            data: { bill : bill },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $this.replaceWith('<a class="ep-icon ep-icon_reply-circle txt-gray fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>external_bills/popup_forms/notice/'+resp.request+'" data-title="Notes" title="Reimburse buyer detail"></a>');
                }
            }
        });
    }

    var create_external_bill = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>external_bills/ajax_external_bills_operation/add_request/pay',
            data: { bill : bill },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $this.replaceWith('<a class="ep-icon ep-icon_billing txt-gray fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>external_bills/popup_forms/notice/'+resp.request+'" data-title="Notes" title="External bill request detail"></a>');
                }
            }
        });
    }

	function extend_action_callback(){
        dtMyBills.fnDraw();
        closeFancyBox();
	}
</script>

<div class="row">
	<div class="col-xs-12" >
		<h3 class="titlehdr"><span>All bills</span></h3>
        <div class="mt-10 wr-filter-list clearfix"></div>

		<?php tmvc::instance()->controller->view->display('admin/billing/filter_view')?>
		<ul class="pull-left mb-10 clearfix statuses_counters">
			<li class="pull-left mr-5"><a class="dt_filter btn btn-default active" data-name="status" data-title="Bills status" data-value="" data-value-text="All">All</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-default" data-name="status" data-value="init" data-value-text="Waiting for payment">Waiting for payment (<span class="bills_counter">0</span>)</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-default" data-name="status" data-value="paid" data-value-text="Payed">Payed (<span class="bills_counter">0</span>)</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-default" data-name="status" data-value="confirmed" data-value-text="Confirmed">Confirmed (<span class="bills_counter">0</span>)</a></li>
			<li class="pull-left mr-5"><a class="dt_filter btn btn-default" data-name="status" data-value="unvalidated" data-value-text="Cancelled">Cancelled (<span class="bills_counter">0</span>)</a></li>
		</ul>
		<ul class="pull-right statuses_counters">
			<!--EXPIRE FILTERS-->
			<li class="display-n"><a class="dt_filter btn btn-default active" data-name="expire_status" data-title="Expire status" data-value="" data-value-text="All">All</a></li>
			<li class="pull-right ml-5"><a class="dt_filter btn btn-danger" data-name="expire_status" data-value="expired" data-value-text="Expired">Expired (<span class="bills_counter">0</span>)</a></li>
			<li class="pull-right ml-5"><a class="dt_filter btn btn-warning" data-name="expire_status" data-value="expire_soon" data-value-text="Expire soon">Expire soon (<span class="bills_counter">0</span>)</a></li>
		</ul>
        <table cellspacing="0" cellpadding="0" id="dtMyBills" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="lh-19 dt_checkbox">#</th>
                    <th class="dt_bill">Bill Nr.</th>
                    <th class="dt_bill_type">Bill type</th>
                    <th class="dt_order">Item Nr.</th>
                    <th class="dt_buyer">Buyer</th>
                    <th class="dt_amount">Amount</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_pay_method">Pay method</th>
                    <th class="tac dt_all_dates">Dates</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

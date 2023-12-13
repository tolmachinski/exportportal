<script>
var dtHighlughtItemsList;
var itemsFilters;

var confirm_bill = function(opener){
	var $thisBtn = $(opener);
	var bill = $thisBtn.data('bill');
	billChangeStatus($thisBtn, bill, 'confirm');
}

var decline_bill = function(opener){
	var $thisBtn = $(opener);
	var bill = $thisBtn.data('bill');
	billChangeStatus($thisBtn, bill, 'decline');
}

var hightlight_item = function(opener){
	var $this = $(opener);
	var dataItem = $this.data("item");
	$.ajax({
		type: "POST",
		context: $(this),
		url: "items/ajax_item_operation/change_highlight",
		data: { dataItem: dataItem },
		dataType: 'JSON',
		success: function(resp){
			if(resp.mess_type == 'success'){
				dtHighlughtItemsList.fnDraw(false);
			}
			systemMessages( resp.message, 'message-' + resp.mess_type );
		}
	});
}

$(document).ready(function(){
	dtHighlughtItemsList = $('#dtHighlughtItemsList').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>items/dt_ajax_administration_highlight_items",
		"sServerMethod": "POST",
		"aoColumnDefs": [
            {"sClass": "w-100", "aTargets": ['dt_image'], "mData": "dt_image" , 'bSortable': false},
            {"sClass": "mnw-190 vam", "aTargets": ['dt_item'], "mData": "dt_item" , 'bSortable': false},
            {"sClass": "mnw-150", "aTargets": ['dt_seller'], "mData": "dt_seller" , 'bSortable': false},
            {"sClass": "mnw-100 vam tal", "aTargets": ['dt_address'], "mData": "dt_address" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_fstatus'], "mData": "dt_fstatus" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_paid'], "mData": "dt_paid" , 'bSortable': false},
            {"sClass": "mnw-100 vam tac", "aTargets": ['dt_price'], "mData": "dt_price" , 'bSortable': false},
            {"sClass": "mnw-80 vam tac", "aTargets": ['dt_update_date'], "mData": "dt_update_date"},
            {"sClass": "mnw-80 vam tac", "aTargets": ['dt_expire_date'], "mData": "dt_expire_date"},
            {"sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
		],
		"sPaginationType": "full_numbers",
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            if(!itemsFilters){
                itemsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug':true,
                    callBack: function(){
                        dtHighlughtItemsList.fnDraw();
                    },
                    onSet: function(callerObj, filterObj){
                        if(filterObj.name == 'ith_status'){
                            $('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li').addClass('active').siblings().removeClass('active');
                        }
                        if(filterObj.name == 'start_from'){
                            $(".start_to").datepicker("option","minDate", $(".start_from").datepicker("getDate"));
                        }
                        if(filterObj.name == 'start_to'){
                            $(".start_from").datepicker("option","maxDate", $(".start_to").datepicker("getDate"));
                        }
                        if(filterObj.name == 'end_from'){
                            $(".end_to").datepicker("option","minDate", $(".end_from").datepicker("getDate"));
                        }
                        if(filterObj.name == 'end_to'){
                            $(".end_from").datepicker("option","maxDate", $(".end_to").datepicker("getDate"));
                        }
                    },
                    onDelete: function(filter){
                        if(filter.name == 'parent'){
                            $('.subcategories').remove();
                        }
                        if(filter.name == 'ith_status'){
                            $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
                            $li.siblings('li').removeClass('active').end()
                               .addClass('active');
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

            aoData = aoData.concat(itemsFilters.getDTFilter());
            $.ajax( {
                "dataType": 'JSON',
                "type": "POST",
                "url": sSource,
                "data": aoData,
                "success": function (data, textStatus, jqXHR) {
                    if(data.mess_type == 'error' || data.mess_type == 'info')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);

                }
            });
        },
        "fnDrawCallback": function(oSettings) {
            $('.rating-bootstrap').rating();
        }
	});

	idStartItemNew = parseInt('<?php echo $last_highlight_items_id; ?>', 10);
	startCheckAdminNewItems('items/ajax_item_operation/check_new_highlight_items', idStartItemNew);
});

function billChangeStatus(btn, bill, status){
    var $thisBtn = btn;
    $.ajax({
        type: 'POST',
        url: 'billing/ajax_bill_operations/'+status+'_bill',
        data: { bill : bill },
        dataType: 'json',
        beforeSend: function(){},
        success: function(data){ //alert(data);
            systemMessages( data.message, 'message-' + data.mess_type );

            if(data.mess_type == 'success'){
                if(status == 'confirm'){
                    $thisBtn.siblings('.btn-bill-decline').remove().end()
                            .closest('li').find('.status-b').text('Confirmed');
                    $thisBtn.remove();
                }

                if(status == 'decline'){
                    $thisBtn.siblings('.btn-bill-confirm').remove().end()
                            .closest('li').find('.status-b').text('Cancelled').removeClass('txt-green').addClass('txt-red');
                    $thisBtn.remove();
                }
                $( "#dialog-"+status+"-bill" ).dialog( "close" );
                dtHighlughtItemsList.fnDraw(false);
            }

        }
    });
}
</script>
<div class="row">
    <div class="col-xs-12">
		<h3 class="titlehdr">
			<span>Highlight items list</span>
		</h3>
		<?php tmvc::instance()->controller->view->display('admin/item/highlight/filter_view'); ?>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="ith_status" data-title="Highlight status" data-value="" data-value-text="All">All</a></li>
			<li><a class="dt_filter" data-name="ith_status" data-value="init" data-value-text="Initiated">Initiated</a></li>
			<li><a class="dt_filter" data-name="ith_status" data-value="active" data-value-text="Active">Active</a></li>
			<li><a class="dt_filter" data-name="ith_status" data-value="expired" data-value-text="Expred">Expred</a></li>
		</ul>
		<div class="wr-filter-list clearfix mt-10"></div>

        <table  class="data table-striped table-bordered w-100pr" id="dtHighlughtItemsList">
            <thead>
                <tr>
                    <th class="dt_image"></th>
                    <th class="dt_item">Item</th>
                    <th class="dt_seller">Seller info</th>
                    <th class="dt_address">Country/State/City</th>
                    <th class="dt_fstatus">Status</th>
                    <th class="dt_paid">Payment</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_update_date">Start</th>
                    <th class="dt_expire_date">Expired</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall"></tbody>
        </table>
    </div>
</div>

<script src="<?php echo __FILES_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>
<script type="text/javascript">
var externalBillsFilters; //obj for filters
var dtExternalBills; //obj of datatable
var confirm_external_bill = function(opener){
	var $this = $(opener);
	var ext_bill = $this.data('id-ext-bill');
	var url = "external_bills/ajax_external_bills_operation/confirm_external_bill/" + ext_bill;
	$.ajax({
		type: "POST",
		url: url,
		dataType: "JSON",
		success: function(resp) {
			if (resp.mess_type == 'success'){
				dtExternalBills.fnDraw();
				closeFancyBox();
			}
			systemMessages(resp.message, 'message-' + resp.mess_type);
		}
	});
}
var delete_external_bill = function(opener){
	var $this = $(opener);
	var id_request = $this.data('id_request');
	var url = "external_bills/ajax_external_bills_operation/delete_external_bill/" + id_request;
	$.ajax({
		type: "POST",
		url: url,
		dataType: "JSON",
		success: function(resp) {
			if (resp.mess_type == 'success'){
				dtExternalBills.fnDraw(false);
			}
			systemMessages(resp.message, 'message-' + resp.mess_type);
		}
	});
}
$(document).ready(function(){
    //var externalBillsFilters; //obj for filters
	dtExternalBills = $('#dtExternalBills').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>external_bills/ajax_external_bills_admin_dt",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id"},
			{ "sClass": "w-250 vam", "aTargets": ["dt_user"], "mData": "dt_user", "bSortable": false},
			{ "sClass": "w-200 tac", "aTargets": ["dt_money"], "mData": "dt_money"},
			{ "sClass": "w-200 tac", "aTargets": ["dt_date"], "mData": "dt_date" },
			{ "sClass": "w-100 tac", "aTargets": ["dt_type"], "mData": "dt_type" },
			{ "sClass": "", "aTargets": ["dt_comment"], "mData": "dt_comment", "bSortable": false  },
			{ "sClass": "w-100 tac", "aTargets": ["dt_status"], "mData": "dt_status"},
			{ "sClass": "w-60 tac", "aTargets": ["dt_actions"], "mData": "dt_actions" , "bSortable": false }
		],
		"sorting" : [[0,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!externalBillsFilters){
				externalBillsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtExternalBills.fnDraw(); },
					onSet: function(callerObj, filterObj){
						if(filterObj.name == 'status'){
							$('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li')
								.addClass('active').siblings().removeClass('active');

						}
					},
					onDelete: function(filter){
						if(filter.name == 'status'){
							$('.menu-level3 a[data-value="' + filter.value + '"]').parent('li')
								.addClass('active').siblings().removeClass('active');

						}
					}
				});
			}

			aoData = aoData.concat(externalBillsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);
					if(data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {
//			$('.fancybox-gal').fancybox();
		}
	});

    function fnFormatDetails(nTr){
		var aData = dtExternalBills.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
			sOut += '<tr><td class="w-100">Photos:</td><td>' + aData['dt_photos']+ '</td></tr>';
			sOut += '<tr><td class="w-100 tac">Reason:</td><td>' + aData['dt_reason'] +'</td></tr>';
			sOut += '</table> </div>';
		return sOut;
    }
});
</script>
<div class="row">
    <div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/external_bills/filter_panel_view')?>
		<div class="titlehdr h-30">
		    <span>External bills</span>
		    <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL;?>external_bills/popup_forms/add_form/other" data-title="Add external bill request" data-table="dtExternalBills"></a>
        </div>
		<div class="wr-filter-list clearfix mt-10"></div>

		<ul class="menu-level3 mb-10 clearfix">
			<li >
				<a class="dt_filter" name="status" data-title="Status" data-name="status" data-value="" data-value-text="">All</a>
			</li>
			<li class="active">
				<a class="dt_filter" name="status" data-title="Status" data-name="status" value="waiting"  data-value="waiting" data-value-text="Waiting">Waiting</a>
			</li>
			<li >
				<a class="dt_filter" name="status" data-title="Status" data-name="status" value="processed" data-value="processed" data-value-text="Processed">Processed</a>
			</li>
		</ul>
		<table class="data table-bordered table-striped w-100pr " id="dtExternalBills">
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="tac dt_user">Request for</th>
                    <th class="tac dt_type">Type</th>
                    <th class="tac dt_money">Money</th>
                    <th class="tac dt_date">Date</th>
                    <th class="tac dt_comment">Comment</th>
                    <th class="tac dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall">
            </tbody>
        </table>
    </div>
</div>

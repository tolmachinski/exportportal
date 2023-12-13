<script type="text/javascript">
var requestsFilters;
var dtRequests;

$(document).ready(function(){

	dtRequests = $('#dtRequests').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>user_cancel/ajax_cancelation_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_request'], "mData": "dt_id_request" },
			{ "sClass": "w-300", "aTargets": ['dt_name'], "mData": "dt_name" },
			{ "sClass": "w-500", "aTargets": ['dt_reason'], "mData": "dt_reason", "bSortable": false  },
			{ "sClass": "w-500", "aTargets": ['dt_feedback'], "mData": "dt_feedback", "bSortable": false  },
			{ "sClass": "tac w-150", "aTargets": ['dt_close_date'], "mData": "dt_close_date" },
			{ "sClass": "tac w-150", "aTargets": ['dt_start_date'], "mData": "dt_start_date" },
			{ "sClass": "tac w-150", "aTargets": ['dt_update_date'], "mData": "dt_update_date" },
			{ "sClass": "w-100 tac", "aTargets": ['dt_status'], "mData": "dt_status"},
			{ "sClass": "w-55 tar", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!requestsFilters){
				requestsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtRequests.fnDraw(); },
					onSet: function(callerObj, filterObj){

						if(filterObj.name == 'status'){
							$('.menu-level3').find('a[data-value="'+filterObj.value+'"]').parent('li').addClass('active').siblings('li').removeClass('active');
						}

					},
					onDelete: function(filter){

						if(filter.name == 'status'){
							var $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
							$li.addClass('active').siblings('li').removeClass('active');
						}
					}
				});
			}

			aoData = aoData.concat(requestsFilters.getDTFilter());
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
		"fnDrawCallback": function( oSettings ) { }
	});

	idStartItemNew = <?php echo $last_requests_id;?>;
	startCheckAdminNewItems('user_cancel/ajax_user_cancel_operation/check_new', idStartItemNew);

});

var more_reason = function(obj){
	var $this = $(obj);
	$this.closest('.reason-text').find('p').toggleClass('h-20').toggleClass('text-nowrap');
	$this.toggleClass('ep-icon_arrows-down ep-icon_arrows-up');
}

var decline_request = function(obj){
	var $this = $(obj);
	var request = $this.data('request');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>user_cancel/ajax_user_cancel_operation/decline_request',
		data: { request : request},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtRequests.fnDraw();
			}
		}
	});
}

var delete_account = function(obj){
	var $this = $(obj);
	var request = $this.data('request');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL . 'user_cancel/ajax_user_cancel_operation/delete_account';?>',
		data: { request : request},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtRequests.fnDraw();
			}
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr"><span>Profile Cancelation Requests</span></div>

		<?php tmvc::instance()->controller->view->display('admin/user_cancel/filter_panel_view'); ?>

		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtRequests" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_request">#</th>
					 <th class="dt_name">User name</th>
					 <th class="dt_reason">Reason</th>
					 <th class="dt_start_date">Requested at</th>
					 <th class="dt_close_date">Close on</th>
					 <th class="dt_status">Status</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>


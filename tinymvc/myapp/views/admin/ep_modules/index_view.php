<script type="text/javascript">
var dtEpModules;
var change_order = function(obj){
	var $this = $(obj);
	var module = $this.data('module');
	var action = $this.data('action');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>ep_modules/ajax_ep_modules_operations/change_order/'+action,
		data: {module: module},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			dtEpModules.fnDraw();
		}
	});
}

var remove_ep_module = function(obj){
	var $this = $(obj);
	var module = $this.data('id');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>ep_modules/ajax_ep_modules_operations/remove_ep_module',
		data: {module: module},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			dtEpModules.fnDraw();

			if(data.mess_type == 'success'){ }
		}
	});
}
$(document).ready(function(){
	dtEpModules = $('#dtEpModules').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"iDisplayLength": 50,
		"sAjaxSource": "<?php echo __SITE_URL?>ep_modules/ajax_ep_modules_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "tal w-200", "aTargets": ['dt_name'], "mData": "dt_name" },
			{ "sClass": "tal w-300", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "tal w-50", "aTargets": ['dt_group'], "mData": "dt_group" },
			{ "sClass": "tal", "aTargets": ['dt_text'], "mData": "dt_text" },
			{ "sClass": "tac w-80", "aTargets": ['dt_position'], "mData": "dt_position" },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[5, "asc"]],
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		}
	});
	
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30"><span>EP modules</span> <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="ep_modules/popup_forms/add_ep_module" data-table="dtEpModules" data-title="Add EP module"></a></div>

		<table class="data table-striped table-bordered w-100pr" id="dtEpModules" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_name">Name</th>
					<th class="dt_title">Title</th>
					<th class="dt_group">Group</th>
					<th class="dt_text">Description</th>
					<th class="dt_position">Position</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

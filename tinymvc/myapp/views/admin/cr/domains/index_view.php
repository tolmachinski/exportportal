<script type="text/javascript">
var dtCr;

$(document).ready(function(){
	dtCr = $('#dtCr').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>cr_domains/ajax_operations/cr_list_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac vam", "aTargets": ['dt_id'], "mData": "dt_id"},
			{ "sClass": "w-30 tac vam", "aTargets": ['dt_flag'], "mData": "dt_flag", "bSortable": false},
			{ "sClass": "tal vam", "aTargets": ['dt_country'], "mData": "dt_country"},
			{ "sClass": "w-200 tal vam", "aTargets": ['dt_domain'], "mData": "dt_domain", "bSortable": false},
			{ "sClass": "w-120 tac vam", "aTargets": ['dt_date'], "mData": "dt_date", "bSortable": false},
			{ "sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function ( aoData ) {

		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
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
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		}
	});
});

var delete_cr_domain = function(btn){
    var $this = $(btn);
    var id_domain = $this.data('domain');

    $.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>cr_domains/ajax_operations/delete_cr_domain',
		data: { id_domain : id_domain},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				manage_cr_domains_callback(resp);
			}
		}
	});
}

var update_cr_configs = function(btn){
    var $this = $(btn);

    $.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>cr_domains/ajax_operations/update_cr_configs',
		data: {},
		beforeSend: function(){},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );
		}
	});
}

function manage_cr_domains_callback(resp){
    dtCr.fnDraw(false);
}
</script>
<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>
<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
    		<span>Countries representatives domains</span>
    		<a class="pull-right fancybox.ajax fancyboxValidateModalDT ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL;?>cr_domains/popup_forms/add_cr_domain" data-table="dtCr" title="Add country representative domain" data-title="Add country representative domain"></a>
			<a class="pull-right confirm-dialog ep-icon ep-icon_branches mr-5" data-callback="update_cr_configs" data-message="Are you sure you want to update configs?"  title="Update config"></a>
    	</div>

		<table id="dtCr" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id">#</th>
					 <th class="dt_flag"><span class="ep-icon ep-icon_globe fs-22"></span></th>
					 <th class="dt_country">Country</th>
					 <th class="dt_domain">Domain</th>
					 <th class="dt_date"><span class="ep-icon ep-icon_calendar fs-22" title="Created date"></span></th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

<script type="text/javascript">
var targetsDT;
var change_target_state = function(opener){
	var $this = $(opener);
	var target_state = $this.data('state');
	var id_target = $this.data('target');
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>analytics/ajax_operations/change_target_state',
		data: {id_target:id_target, target_state:target_state},
		dataType: 'JSON',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				targetsDT.fnDraw(false);
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			systemMessages( 'The request can not be sent. Please try again later.', 'message-error' );
			jqXHR.abort();
		}
	});
}

var delete_target = function(opener){
	var $this = $(opener);
	var id_target = $this.data('target');
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>analytics/ajax_operations/delete_target',
		data: {id_target:id_target},
		dataType: 'JSON',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				targetsDT.fnDraw(false);
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			systemMessages( 'The request can not be sent. Please try again later.', 'message-error' );
			jqXHR.abort();
		}
	});
}

$(function(){
	targetsDT = $('#targetsDT').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>analytics/ajax_operations/list_targets_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false  },
			{ "sClass": "w-200 tac", "aTargets": ['dt_type'], "mData": "dt_type", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_active_ga'], "mData": "dt_active_ga", "bSortable": false },
			{ "sClass": "w-150 tac", "aTargets": ['dt_active_oa'], "mData": "dt_active_oa", "bSortable": false },
			{ "sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"sorting": [[0, "asc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type != 'success'){
						systemMessages(data.message, 'message-' + data.mess_type);
					}

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">
			<span>Analytic targets</span>
			<a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" data-title="Add target" title="Add target" data-table="targetsDT" href="<?php echo __SITE_URL;?>analytics/popup/add_target"></a>
		</div>
		<table class="data table-striped table-bordered w-100pr" id="targetsDT" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id">#</th>
					 <th class="dt_name">Target name</th>
					 <th class="dt_type">Target type</th>
					 <th class="dt_active_ga">Google Analytics</th>
					 <th class="dt_active_oa">Own Analytics</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

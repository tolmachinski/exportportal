<link rel="stylesheet" type="text/css" media="screen" href="<?php echo __FILES_URL;?>public/plug_admin/color-picker/css/bootstrap-colorpicker.min.css"/>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/color-picker/js/bootstrap-colorpicker.min.js"></script>
<script type="text/javascript">
var dtCallingStatusesList;
$(document).ready(function(){
	dtCallingStatusesList = $('#dtCallingStatusesList').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>users/calling_statuses_dt",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aLengthMenu": [
			[10, 25, 50, 100, 0],
			[10, 25, 50, 100, 'All']
		],
		"aoColumnDefs": [
			{ "sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id", "bSortable": false },
			{ "sClass": "w-300 vam", "aTargets": ["dt_title"], "mData": "dt_title", "bSortable": false },
			{ "sClass": "vam", "aTargets": ["dt_description"], "mData": "dt_description" },
			{ "sClass": "tac vam w-150", "aTargets": ["dt_color"], "mData": "dt_color" },
			{ "sClass": "w-70 tac vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false }
		],
		"sorting" : [[0,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			$.ajax( {
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
				if(data.mess_type == 'error')
					systemMessages(data.message, 'message-' + data.mess_type);

				fnCallback(data, textStatus, jqXHR);
				},

			} );
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});

var delete_status = function(obj){
    var $this = $(obj);
    var id_status = $this.data('status');
    $.ajax({
        url: '<?php echo __SITE_URL;?>users/ajax_operations/delete_calling_status',
        type: 'POST',
        data:  {id_status:id_status},
        dataType: 'json',
        success: function(resp){
            systemMessages(resp.message, 'message-' + resp.mess_type );
            if(resp.mess_type == 'success'){
                dtCallingStatusesList.fnDraw(false);
            }
        }
    });
}
</script>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Calling statuses</span>
			<a class="ep-icon ep-icon_plus-circle txt-green pull-right ml-10 fancybox.ajax fancyboxValidateModalDT" data-table="dtCallingStatusesList" href="<?php echo __SITE_URL;?>users/popup_forms/add_calling_status" data-title="Add status" title="Add status"></a>
		</div>

		<table class="data table-bordered table-striped w-100pr" id="dtCallingStatusesList">
			<thead>
				<tr>
					<th class="dt_id">#</th>
					<th class="dt_title">Status name</th>
					<th class="dt_description">Description</th>
					<th class="tac dt_color">Status color</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall">
			</tbody>
		</table>
	</div>
</div>

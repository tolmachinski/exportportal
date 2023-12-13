<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
var dtMediaList;
$(document).ready(function(){

	dtMediaList = $('#dtMediaList').dataTable({
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL . 'mass_media/ajax_media_administration';?>",
		"sServerMethod": "POST",
		"bFilter": false,
		"bPaginate": false,
		"bInfo": false,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_media'], "mData": "dt_id_media", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false },
			{ "sClass": "tac w-100", "aTargets": ['dt_logo'], "mData": "dt_logo", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function(aoData) { },
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function(oSettings) { }
	});

	mediaRemove = function(obj){
		var $this = $(obj);
		var media = $this.data('media');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>mass_media/ajax_media_operation/delete_media',
			data: { media : media},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtMediaList.fnDraw();
				}
			}
		});
	}
})
</script>

<div class="row">
    <div class="col-xs-12">
		<h3 class="titlehdr h-30"><span>Media list</span> <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL . 'mass_media/media_popups/add_media';?>" data-table="dtMediaList" data-title="Add media" title="Add media"></a></h3>

		<table id="dtMediaList" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_media">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_logo">Logo</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

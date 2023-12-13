<script type="text/javascript" src="<?php echo __SITE_URL ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
var dtBanner, groupsFilters;

$(document).ready(function(){

	dtBanner = $('#dtBanner').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>banner/ajax_operation/administration_list_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
			{ "sClass": "", "aTargets": ['dt_name'], "mData": "dt_name"},
			{ "sClass": "", "aTargets": ['dt_type'], "mData": "dt_type"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_link'], "mData": "dt_link"},
			{ "sClass": "tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtBanner.fnDraw(); },
                    onSet: function (callerObj, filterObj) {},
                    onDelete: function (filter) {}
                });
            }

            aoData = aoData.concat(groupsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info') {
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

function callbackManageFaq(resp){
    dtBanner.fnDraw(false);
}

var delete_banner = function(obj){
	var $this = $(obj);
	var id_banner = $this.data('id_banner');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>banner/ajax_operation/delete',
		data: { id_banner : id_banner },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				callbackManageFaq(resp);
			}
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
    		<span>Banners</span>
            <?php if(have_right('manage_content')) { ?>
    		    <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>banner/popup_forms/create" data-table="dtBanner" data-title="Add question">Add banner</a>
            <?php } ?>
    	</div>

		<!-- <?php tmvc::instance()->controller->view->display('admin/banner/filter_panel_view'); ?> -->
        <div class="wr-filter-list clearfix mt-10"></div>

		<table id="dtBanner" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			<thead>
				<tr>
					<th class="dt_id w-50">#</th>
					<th class="dt_name">Template name</th>
					<th class="dt_type">Type</th>
					<th class="dt_link">Link</th>
					<th class="dt_actions w-80">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>

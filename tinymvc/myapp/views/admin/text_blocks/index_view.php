<script type="text/javascript">
var seoFilters;
var dtTextualBlocks;

var remove_text_block = function(obj){
	var $this = $(obj);
	var id = $this.data('id');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>text_block/ajax_text_block_operation/delete',
		data: {id: id},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			if(data.mess_type == 'success'){
				dtTextualBlocks.fnDraw();
			}
		}
	});
}

$(document).ready(function(){
	dtTextualBlocks = $('#dtTextualBlocks').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>text_block/ajax_text_blocks_administration_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "w-400", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "w-400", "aTargets": ['dt_short_name'], "mData": "dt_short_name" },
			{ "sClass": "w-400", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false  },
			{ "sClass": "w-400 tac", "aTargets": ['dt_text'], "mData": "dt_text", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_updated_at'], "mData": "dt_updated_at" },
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
			{ "sClass": "tac w-110", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!seoFilters){
				seoFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtTextualBlocks.fnDraw(); },
					onSet: function(callerObj, filterObj){

					},
					onDelete: function(filter){

					}
				});
			}

			aoData = aoData.concat(seoFilters.getDTFilter());
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

		}
	});
});
</script>

<div class="row">
	<div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Text blocks</span>

            <?php if(have_right('manage_content')) { ?>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="text_block/popup_forms/add_text_block" data-title="Add text block" data-table="dtTextualBlocks"></a>
            <?php } ?>
        </div>

		<?php tmvc::instance()->controller->view->display('admin/text_blocks/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtTextualBlocks" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_title">Title</th>
					<th class="dt_short_name">Short key</th>
					<th class="dt_description">Description</th>
					<th class="dt_updated_at">EN updated at</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

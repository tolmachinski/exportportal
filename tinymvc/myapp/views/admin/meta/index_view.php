<script type="text/javascript">
var dtMeta;
var myFilters;

$(document).ready(function(){
	remove_meta = function(obj){
		var $this = $(obj);
		var id_meta = $this.data('id');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>meta/ajax_meta_operations/remove_meta',
			data: {id: id_meta},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtMeta.fnDraw(false);
				}
			}
		});
	}

	dtMeta = $('#dtMeta').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>meta/ajax_meta_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "tac", "aTargets": ['dt_page_key'], "mData": "dt_page_key" },
			{ "sClass": "tac", "aTargets": ['dt_lang'], "mData": "dt_lang" },
			{ "sClass": "tac", "aTargets": ['dt_link'], "mData": "dt_link" },
			{ "sClass": "w-500", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[0, "desc"]],
		"sPaginationType": "full_numbers",
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!myFilters){
				myFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':true,
					callBack: function(){
						dtMeta.fnDraw();
					},
					onDelete: function(filter){ },
					onSet: function(callerObj, filterObj) { },
					onReset: function(){ }
				});
			}

			aoData = aoData.concat(myFilters.getDTFilter());
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
		}
	});

});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Meta pages</span>
			<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="meta/popup_forms/add_meta" data-table="dtMeta" data-title="Add meta page"></a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/meta/filter_panel_view'); ?>

		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtMeta" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_page_key">Page Key</th>
					<th class="dt_lang">Language</th>
					<th class="dt_title">Title</th>
					<th class="dt_link">Link</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

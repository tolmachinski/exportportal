<script type="text/javascript">

var configsFilters;
var dtConfigs;
$(document).ready(function(){
	dtConfigs = $('#dtConfigs').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>config/ajax_configs_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-200 tac", "aTargets": ['dt_key'], "mData": "dt_key" },
			{ "sClass": "w-600 tac", "aTargets": ['dt_value'], "mData": "dt_value" },
			{ "sClass": " tac", "aTargets": ['dt_description'], "mData": "dt_description" , "bSortable": false },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		'iDisplayLength': 50,
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!configsFilters){
				configsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtConfigs.fnDraw(); },
					onSet: function(callerObj, filterObj){

					},
					onDelete: function(filter){

					}
				});
			}

			aoData = aoData.concat(configsFilters.getDTFilter());
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

			var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
			if( keywordsSearch !== '' )
				$("#dt-country-blogs tbody *").highlight(keywordsSearch, "highlight");
		}
	});

	remove_config = function(obj){
		var $this = $(obj);
		var config = $this.data('config');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>config/ajax_config_operation/delete_config',
			data: {config: config},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtConfigs.fnDraw(false);
				}
			}
		});
	}

	regenerate_configs = function(){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>config/ajax_config_operation/regenerate_configs/',
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
			}
		});
	};
});
</script>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30"><span>Configs</span>
			<a class="fancyboxValidateModal fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="config/popup_forms/add_config/" data-title="Add blog"></a>
			<a class="pull-right confirm-dialog ep-icon ep-icon_branches mr-5" data-callback="regenerate_configs" data-message="Are you sure you want to regenerate configs?"  title="Regenerate configs"></a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/config/config_filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtConfigs" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_key">Key</th>
					<th class="dt_value">Value</th>
					<th class="dt_description">Description</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

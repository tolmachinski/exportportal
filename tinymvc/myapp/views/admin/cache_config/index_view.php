<script>
var block;

var dtCacheConfigList;
$(document).ready(function() {

	var myFilters;
	dtCacheConfigList = $('#dtCacheConfigList').dataTable({
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bFilter": false,
		"sAjaxSource": "<?php echo __SITE_URL; ?>cache_config/ajax_list_dt/",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{"sClass": "w-50 tac vam", "aTargets": ['dt_id_config'],  "mData": "dt_id_config", "bSortable": false},
			{"sClass": "w-200 tac vam", "aTargets": ['dt_cache_key'], "mData": "dt_cache_key"},
			{"sClass": "w-200 tac vam", "aTargets": ['dt_folder'], "mData": "dt_folder"},
			{"sClass": "w-70 tac vam", "aTargets": ['dt_cache_time'], "mData": "dt_cache_time"},
			{"sClass": "tal vam", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false},
			{"sClass": "w-100 vam tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
		],
		"sPaginationType": "full_numbers",
		"fnServerData": function(sSource, aoData, fnCallback) {
			if(!myFilters){
				myFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					callBack: function(){
						dtCacheConfigList.fnDraw();
					},
					onSet: function(callerObj, filterObj){
					}
				});
			}

			aoData = aoData.concat(myFilters.getDTFilter());

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
		"fnDrawCallback": function(oSettings) {

		}
	});

	delete_clean = function(obj){
		 var $this = $(obj);
		 var id = $this.data('id');
		 var op = $this.data('op');
		 $.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>cache_config/ajax_operation/" + op,
			data: {id_config: id},
			dataType: "JSON",
			success: function(resp) {
			   if(resp.mess_type == 'success'){
					dtCacheConfigList.fnDraw();
			   }
			   systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		 });
	}

	clean_checked = function(){
		var list_id = get_checked_from_table();

		if(!list_id.length){
			systemMessages('Please choose any configuration', 'message-error');
			return false;
		}
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>cache_config/ajax_operation/clean_checked",
			data: {list_id: list_id},
			dataType: "JSON",
			success: function(resp) {
			   if(resp.mess_type == 'success'){
					dtCacheConfigList.fnDraw();
					$('.check-all').prop("checked", false);
			   }
			   systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		 });
	}

	change_enable = function(obj){
		 var $this = $(obj);
		 var id = $this.data("id");
		 var enable = $this.hasClass('ep-icon_visible') ? 0 : 1;

		 $.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>cache_config/ajax_operation/change_enable",
			data: {
				id_config: id,
				enable: enable
			},
			dataType: "JSON",
			success: function(resp) {
			   if(resp.mess_type == 'success'){
					dtCacheConfigList.fnDraw();
			   }
			   systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		 });
	}


	$('.check-all').on('click', function() {
		var table = $(this).closest('table');
		if ($(this).is(":checked")){
			$('.check-one', table).prop("checked", true);
		} else {
			$('.check-one', table).prop("checked", false);
		}
	});

	$('body').on('click', '.check-one', function(){
		if($('.check-one:checked').length == $('.check-one').length)
			$('.check-all').prop("checked", true);
		else
			$('.check-all').prop("checked", false);
	});

	function get_checked_from_table(){
		var checked = [];
		$(".check-one:checked").each(function(){
			checked.push($(this).data("id"));
		})
		return checked;
	}
});
</script>
<div class="col-xs-6 display-n" id="cat_update"></div>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>Cache configuration</span>
		    <div class="pull-right">
				<a class="ep-icon ep-icon_trash txt-red confirm-dialog" data-callback="clean_checked" data-message="Are you sure you want to clean checked cache?" id="clean_checked" title="Clean checked cache"></a>
		        <a class="ep-icon ep-icon_plus fancyboxValidateModalDT fancybox.ajax" title="Add new configuration" href="<?php echo __SITE_URL?>cache_config/popups_forms/add" data-title="Add new configuraton"></a>
            </div>
			<div class="pull-right btns-actions-all display-n mt-10"></div>
		</div>

		<div class="mt-10 wr-filter-list clearfix"></div>

		<table id="dtCacheConfigList" class="data table-striped table-bordered w-100pr" >
			<thead>
				<tr>
				    <th class="dt_id_config"><input type="checkbox" class="check-all pull-left">#</th>
				    <th class="dt_cache_key">Key</th>
				    <th class="dt_folder">Folder</th>
				    <th class="dt_cache_time">Time</th>
				    <th class="dt_description">Description</th>
				    <th class="dt_actions tac">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>



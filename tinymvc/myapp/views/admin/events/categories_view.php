<script type="text/javascript">
	var dtBlogs;

$(document).ready(function(){

	dtBlogs= $('#dtBlogs').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>events/ajax_events_categories_administration",
		"sServerMethod": "POST",
        "iDisplayLength": 10,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_category'], "mData": "dt_id_category"},
			{ "sClass": "tac", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

        }
	});
});

//remove category blog
var status_category_events = function(obj){
	var $this = $(obj);
	var category = $this.data('category');
	var visible = $this.data('visible');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>events/ajax_event_operation/admin_status_category',
		data: {category: category, visible:visible},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			dtBlogs.fnDraw();
		}
	});
}

var remove_category_events = function(obj){
	var $this = $(obj);//alert($this.data('column'));
	var category = $this.data('category');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>events/ajax_event_operation/admin_remove_category',
		data: {category: category},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			dtBlogs.fnDraw();
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">
			<span>Events categories</span>
			<a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL;?>events/popup_forms/admin_category_form" data-title="Add category" data-table="dtBlogs"></a>
		</div>

        <div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtBlogs" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_category">#</th>
					 <th class="dt_name">Name</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

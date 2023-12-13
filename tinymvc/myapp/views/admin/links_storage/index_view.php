<script type="text/javascript">
var dtLinksStorage;
var myFilters;;

$(document).ready(function(){

	dtLinksStorage = $('#dtLinksStorage').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>links_storage/ajax_links_storage_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_links_storage'], "mData": "dt_id_links_storage" },
			{ "sClass": "w-100", "aTargets": ['dt_link'], "mData": "dt_link" },
			{ "sClass": "", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false },
			{ "sClass": "w-90 tac", "aTargets": ['dt_country'], "mData": "dt_country"},
			{ "sClass": "w-70 tac", "aTargets": ['dt_paid'], "mData": "dt_paid" },
			{ "sClass": "w-90 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false},
		],
		"sorting" : [[0,'desc']],
		"fnServerParams": function ( aoData ) {

		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!myFilters){
				myFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					callBack: function() {
						dtLinksStorage.fnDraw();
					},
					onSet: function(callerObj, filterObj) {

					}
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
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		}
	});
});

var remove_link = function(obj){
	var $this = $(obj);
	var link = $this.data('link');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>links_storage/ajax_links_storage_operation/delete_link',
		data: { link : link},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtLinksStorage.fnDraw();
			}
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">
			<span>Links storage</span>
			<a class="btn btn-primary btn-sm pull-right mb-10 fancyboxValidateModalDT fancybox.ajax" data-title="Add link" title="Add link" data-table="dtLinksStorage" href="<?php echo __SITE_URL;?>links_storage/popup_links_storage/add_link">Add link</a>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/links_storage/filters_view'); ?>
		<div class="wr-filter-list mt-10 clearfix"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtLinksStorage" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_links_storage">#</th>
					 <th class="dt_title">Title</th>
					 <th class="dt_link">Link</th>
					 <th class="dt_description">Description</th>
					 <th class="dt_country">Country</th>
					 <th class="dt_paid">Paid</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

<script type="text/javascript">
var dtNotificationMessages;
var dtNotificationMessagesFilters;
$(document).ready(function(){
	dtNotificationMessages = $('#dtNotificationMessages').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>users/notification_messages_dt",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aLengthMenu": [
			[10, 25, 50, 100, 0],
			[10, 25, 50, 100, 'All']
		],
		"aoColumnDefs": [
			{ "sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id"},
			{ "sClass": "w-350 vam", "aTargets": ["dt_title"], "mData": "dt_title"},
			{ "sClass": "vam", "aTargets": ["dt_description"], "mData": "dt_description", "bSortable": false},
			{ "sClass": "w-150 tac vam", "aTargets": ["dt_module"], "mData": "dt_module" },
			{ "sClass": "w-70 tac vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false }
		],
		"sorting" : [[0,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if(!dtNotificationMessagesFilters){
				dtNotificationMessagesFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug'	: true,
					'autoApply': true,
					callBack: function(filter){
						dtNotificationMessages.fnDraw();
					},
					onSet: function(callerObj, filterObj){},
					onDelete: function(filterObj){}
				});
			}

			aoData = aoData.concat(dtNotificationMessagesFilters.getDTFilter());

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

var delete_message = function(obj){
    var $this = $(obj);
    var id_message = $this.data('message-slug');
    $.ajax({
        url: '<?php echo __SITE_URL;?>users/ajax_operations/delete_notification_message',
        type: 'POST',
        data:  {id_message:id_message},
        dataType: 'json',
        success: function(resp){
            systemMessages(resp.message, 'message-' + resp.mess_type );
            if(resp.mess_type == 'success'){
                dtNotificationMessages.fnDraw(false);
            }
        }
    });
}
</script>
<div class="row">
	<div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/user/notification_messages/filter_view')?>
		<div class="titlehdr h-30">
			<span>Reason messages</span>
			<a class="ep-icon ep-icon_plus-circle txt-green pull-right ml-10 fancybox.ajax fancyboxValidateModalDT" data-table="dtNotificationMessages" href="<?php echo __SITE_URL;?>users/popup_forms/add_notification_message" data-title="Add reason message" title="Add reason message"></a>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-bordered table-striped w-100pr" id="dtNotificationMessages">
			<thead>
				<tr>
					<th class="dt_id">#</th>
					<th class="dt_title">Message title</th>
					<th class="dt_description">Message text</th>
					<th class="dt_module">Message module</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall">
			</tbody>
		</table>
	</div>
</div>

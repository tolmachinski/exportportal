<script type="text/javascript">
var dtTopics, groupsFilters;

var delete_topic= function(obj){
    var $this = $(obj);
    var topic = $this.data('topic');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>topics/ajax_topics_operation/delete_topic',
        data: { topic : topic},
        beforeSend: function(){  },
        dataType: 'json',
        success: function(resp){
            systemMessages( resp.message, 'message-' + resp.mess_type );

            if(resp.mess_type == 'success'){
                callbackManageTopics(resp);
            }
        }
    });
}

function callbackManageTopics(resp){
    dtTopics.fnDraw(false);
}

$(document).ready(function(){
	dtTopics = $('#dtTopics').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>topics/ajax_topics_operation/administration_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "w-120 tac", "aTargets": ['dt_updated_at'], "mData": "dt_updated_at"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
			{ "sClass": "tac w-200", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtTopics.fnDraw(); },
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
            <span>Popular topics</span>
            <?php if(have_right('manage_content')) { ?>
                <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="topics/popup_forms/add_topic" data-title="Add topic"></a>
            <?php } ?>
        </div>

		<?php tmvc::instance()->controller->view->display('admin/topics/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtTopics" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_title">Title</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_updated_at">EN updated at</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>

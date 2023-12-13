<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">
var dtNewsArchive, groupsFilters;

$(document).ready(function(){

    dtNewsArchive = $('#dtNewsArchive').dataTable( {
        "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?php echo __SITE_URL?>newsletter/ajax_ep_news_operations/ep_news_archive",
        "sServerMethod": "POST",
        "aoColumnDefs": [
            { "sClass": "tac w-100", "aTargets": ['dt_id_archive'], "mData": "dt_id_archive", "bSortable": true},
            { "sClass": "", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
            { "sClass": "", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false },
            { "sClass": "tac w-100", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
        ],
        "fnServerParams": function ( aoData ) {},
        "fnServerData": function ( sSource, aoData, fnCallback ) {
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
        "sorting" : [[0,'desc']],
        "sPaginationType": "full_numbers",
        "fnDrawCallback": function( oSettings ) {}
    });
});

function callbackManageNews(resp){
    dtNewsArchive.fnDraw(false);
}

var delete_news = function(obj){
	var $this = $(obj);
	var news = $this.data('news');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>newsletter/ajax_ep_news_operations/delete_news_archive',
		data: { news : news},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				callbackManageNews(resp);
			}
		}
	});
}
</script>

<div class="row">
    <div class="col-12">
        <div class="titlehdr h-30">
            <span>Newsletter archive</span>
            <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL; ?>newsletter/popup_forms/add_news_archive" data-table="dtNewsArchive" data-title="Add newsletter archive">Add newsletter archive</a>
        </div>

        <table id="dtNewsArchive" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_archive">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_description">Description</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

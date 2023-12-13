<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
	var dtOurteamList;
$(document).ready(function(){

	dtOurteamList = $('#dt-ourteam-list').dataTable({
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL ?>our_team/ajax_ourteam_administration/<?php echo $upload_folder;?>",
		"sServerMethod": "POST",
		"bFilter": false,
		"bPaginate": false,
		"bInfo": false,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_team'], "mData": "dt_id_team", "bSortable": false },
			{ "sClass": "w-150 tac", "aTargets": ['dt_logo'], "mData": "dt_logo", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_post'], "mData": "dt_post", "bSortable": false },
			{ "sClass": "tac w-100", "aTargets": ['dt_tel'], "mData": "dt_tel", "bSortable": false },
			{ "sClass": "w-150 tac", "aTargets": ['dt_email'], "mData": "dt_email", "bSortable": false },
			{ "sClass": "w-150 tac", "aTargets": ['dt_office'], "mData": "dt_office", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function(aoData) {

		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function(oSettings) {

		}
	});
});

var teamRemove = function(obj){
	var $this = $(obj);
	var team = $this.data('team');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>our_team/ajax_our_team_operation/delete_team',
		data: { team : team},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				$this.closest('tr').fadeOut(function(){
					$(this).remove();
				});
			}
		}
	});
}
</script>

<div class="row">
    <div class="mt-20 col-xs-12">
       <div class="titlehdr h-30"><span>Our team list</span> <a class="fancyboxValidateModal fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="our_team/ourteam_popups/add_team/<?php echo $upload_folder;?>" data-title="Add team"></a></div>

        <table id="dt-ourteam-list" class="data table-striped table-bordered w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id_team">#</th>
                    <th class="dt_logo">Logo</th>
                    <th class="dt_name">Name</th>
                    <th class="dt_post">Post</th>
                    <th class="dt_tel">Phone</th>
                    <th class="dt_email">Email</th>
                    <th class="dt_office">Office</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

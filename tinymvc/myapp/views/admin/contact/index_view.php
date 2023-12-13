<script type="text/javascript">
var contactFilters, dtContact;
$(document).ready(function () {
	remove_complain = function (obj) {
		var $this = $(obj);
		var id = $this.data('complain');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>complains/ajax_complains_operations/remove_complain',
			data: {id_complain: id},
			beforeSend: function () {
				showLoader('#dtContact');
			},
			dataType: 'json',
			success: function (data) {
				hideLoader('#dtContact');
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtContact.fnDraw();
				}
			}
		});
	}

	dtContact = $('#dtContact').dataTable({
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL ?>contact/ajax_contact_admin_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{"sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
			{"sClass": "w-200 tac", "aTargets": ['dt_user'], "mData": "dt_user"},
			{"sClass": "w-250 tac", "aTargets": ['dt_subject'], "mData": "dt_subject"},
			{"sClass": "tac", "aTargets": ['dt_content'], "mData": "dt_content", "bSortable": false},
			{"sClass": "w-200 tac", "aTargets": ['dt_date_time'], "mData": "dt_date_time"},
			{"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function (sSource, aoData, fnCallback) {

			if (!contactFilters) {
				contactFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug': false,
					callBack: function () {
						dtContact.fnDraw();
					},
					onSet: function (callerObj, filterObj) {

					},
					onDelete: function (filter) {

					}
				});
			}

			aoData = aoData.concat(contactFilters.getDTFilter());
			$.ajax({
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if (data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);
					if (data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function (oSettings) {

		}
	});
});
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30"><span>Contact admin</span></div>

        <?php tmvc::instance()->controller->view->display('admin/contact/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped w-100pr" id="dtContact" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_user">User</th>
                    <th class="dt_subject">subject</th>
					<th class="dt_content">Content</th>
					<th class="dt_date_time">Date Time</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

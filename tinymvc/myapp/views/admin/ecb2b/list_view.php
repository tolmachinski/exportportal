<script>
$(function() {
    var myFilters;
    window.dtEcB2bRequests = $('#dtEcB2bRequests').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bFilter": false,
        "sAjaxSource": "<?php echo __SITE_URL; ?>ecb2b/ajax_list_dt",
        "sServerMethod": "POST",
        "aoColumnDefs": [
            {"sClass": "w-50 tac vam", "aTargets": ['dt_id'],  "mData": "dt_id"},
            {"sClass": "w-400 tac vam", "aTargets": ['dt_full_name'], "mData": "dt_full_name", "bSortable": true},
            {"sClass": "w-400 tac vam", "aTargets": ['dt_email'], "mData": "dt_email",  "bSortable": true},
            {"sClass": "w-300 tac vam", "aTargets": ['dt_phone'], "mData": "dt_phone",  "bSortable": true},
            {"sClass": "w-300 tac vam", "aTargets": ['dt_type'], "mData": "dt_type",  "bSortable": true},
            {"sClass": "w-300 tac vam", "aTargets": ['dt_date_created'], "mData": "dt_date_created",  "bSortable": true},
            {"sClass": "w-300 tac vam", "aTargets": ['dt_date_processed'], "mData": "dt_date_processed",  "bSortable": true},
            {"sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
        ],
        "order": [[5, 'desc']],
        "sPaginationType": "full_numbers",
        "rowCallback": function(row, data) {
            data['is_viewed'] === '0' && row.classList.add('new');
        },
        "fnServerData": function(sSource, aoData, fnCallback) {
            if (!myFilters) {
                myFilters = $('.dt_filter').dtFilters('.dt_filter', {
                    'container': '.wr-filter-list',
                    callBack: function() {
                        dtEcB2bRequests.fnDraw();
                    },
                    onSet: function(callerObj, filterObj) {
                    }
                });
            }

            aoData = aoData.concat(myFilters.getDTFilter());

            $.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                "data": aoData,
                "success": function (data, textStatus, jqXHR) {
                    if(data.mess_type === 'error') {
                        systemMessages(data.message, 'message-' + data.mess_type);
                    }

                    fnCallback(data, textStatus, jqXHR);
                }
            });
        },
        "fnDrawCallback": function(oSettings) {
        }
    });

    $(document).on('click', '.mark-ecb2b-viewed', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: "<?php echo __SITE_URL ?>ecb2b/mark_viewed",
            data: {id: $(this).data('id')},
            dataType: 'JSON',
            success: function(resp) {
                if (resp.mess_type === 'success') {
                    dtEcB2bRequests.fnDraw();
                } else {
                    systemMessages(resp.message, 'message-' + resp.mess_type);
                }
            }
        });
    })
});
</script>

<style>
    table.dataTable tbody tr.new {
        background: #f5a0a0;
    }
</style>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>EC B2B Requests</span>
		</div>

		<div class="mt-10 wr-filter-list clearfix"></div>
        <?php tmvc::instance()->controller->view->display('admin/ecb2b/ecb2b_filter_panel_view'); ?>

		<table id="dtEcB2bRequests" class="data table-striped table-bordered w-100pr">
			<thead>
				<tr>
				    <th class="dt_id">#</th>
				    <th class="dt_full_name">Full name</th>
				    <th class="dt_email">Email</th>
				    <th class="dt_phone">Phone</th>
				    <th class="dt_type">Type</th>
				    <th class="dt_date_created">Request date</th>
				    <th class="dt_date_processed">Processed date</th>
				    <th class="dt_actions rev_text tac">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>	
		</table>
	</div>
</div>



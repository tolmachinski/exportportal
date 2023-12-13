<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Upgrade requests</span>
		</div>
        <?php views('admin/upgrade/filter_view'); ?>

		<div class="mt-10 wr-filter-list clearfix"></div>

		<table id="dtUpgradeRequests" class="data table-bordered table-striped w-100pr dataTable">
			<thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_photo"></th>
                    <th class="dt_user">User</th>
                    <th class="dt_contacts">Contacts</th>
                    <th class="dt_date_created">Created at</th>
                    <th class="dt_date_updated">Updated at</th>
                    <th class="dt_date_expire">Expire at</th>
                    <th class="dt_type">Type</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
	</div>
</div>

<?php views()->display('new/download_script'); ?>
<script>
	$(document).ready(function() {
        var onSetFilters = function (caller, filter) {
            if (filter.name == 'reg_date_from') {
                $('input[name="reg_date_to"]').datepicker("option", "minDate", $('input[name="reg_date_from"]').datepicker("getDate"));
            }

            if (filter.name == 'reg_date_to') {
                $('input[name="reg_date_from"]').datepicker("option", "maxDate", $('input[name="reg_date_to"]').datepicker("getDate"));
            }

            if (filter.name == 'resend_date_from') {
                $('input[name="resend_date_to"]').datepicker("option", "minDate", $('input[name="resend_date_from"]').datepicker("getDate"));
            }

            if (filter.name == 'resend_date_to') {
                $('input[name="resend_date_from"]').datepicker("option", "maxDate", $('input[name="resend_date_to"]').datepicker("getDate"));
            }
            if (filter.name == 'upload_date_from') {
                $('input[name="upload_date_to"]').datepicker("option", "minDate", $('input[name="upload_date_from"]').datepicker("getDate"));
            }

            if (filter.name == 'upload_date_to') {
                $('input[name="upload_date_from"]').datepicker("option", "maxDate", $('input[name="upload_date_to"]').datepicker("getDate"));
            }

            if (filter.name == 'calling_from_date') {
                $('input[name="calling_to_date"]').datepicker("option", "minDate", $('input[name="calling_from_date"]').datepicker("getDate"));
            }

            if (filter.name == 'calling_to_date') {
                $('input[name="calling_from_date"]').datepicker("option", "maxDate", $('input[name="calling_to_date"]').datepicker("getDate"));
            }
        };
        var onResetFilter = function () {
            $('.filter-admin-panel .hasDatepicker').datepicker("option" , {
                minDate: null,
                maxDate: null
            });
        };
        var onFilterChange = function () {
            getTable().fnDraw();
        };
        var onServerRequest = function (filters, url, data, callback) {
            var onRequestSuccess = function(data, status, xhr) {
                if ('error' === data.mess_type) {
                    systemMessages(data.message, data.mess_type);
                }

                callback(data, status, xhr);
            };

            return $.post(url, data.concat(filters.getDTFilter()), null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError);
        };
        var updateTable = function (force) {
            dtUpgradeRequests.fnDraw(typeof force !== 'undefined' ? Boolean(~~force) : false);
        };
        var confirmUpgrade = function (button) {
            var url = __site_url + 'upgrade/ajax_admin_operations/complete';
            var userId = button.data('user') || null;
            var onRequestSuccess = function (resp) {
                systemMessages(resp.message, resp.mess_type);
                if(resp.mess_type == 'success'){
                    callFunction('updateTable');
                }
            };

            if(null === userId) {
                return;
            }

            return $.post(url, { user: userId }, null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError);
        };
        var cancelUpgrade = function (button) {
            var url = __site_url + 'upgrade/ajax_admin_operations/cancel';
            var userId = button.data('user') || null;
            var onRequestSuccess = function (resp) {
                systemMessages(resp.message, resp.mess_type);
                if(resp.mess_type == 'success'){
                    callFunction('updateTable');
                }
            };

            if(null === userId) {
                return;
            }

            return $.post(url, { user: userId }, null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError);
        };
        var getTable = function () {
            return dtUpgradeRequests;
        };

        var filterOptions = {
            container: '.wr-filter-list',
            onSet: onSetFilters,
            onReset: onResetFilter,
            callBack: onFilterChange,
        };
        var filters = $('.dt_filter').dtFilters('.dt_filter', filterOptions);
        var datatableOptions = {
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			sorting : [[4, 'desc']],
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: __site_url + "upgrade/requests_list_dt",
            fnServerData: onServerRequest.bind(null, filters),
			sPaginationType: "full_numbers",
			aoColumnDefs: [
				{ sClass: "vam w-30 tac",  aTargets: ['dt_id'],             mData: "dt_id",            bSortable: false },
				{ sClass: "vam w-50 tac",  aTargets: ['dt_photo'],          mData: "dt_photo",         bSortable: false  },
				{ sClass: "vam mnw-200",   aTargets: ['dt_user'],           mData: "dt_user",          bSortable: false },
				{ sClass: "vam mnw-200",   aTargets: ['dt_contacts'],       mData: "dt_contacts",      bSortable: false },
				{ sClass: "vam w-150 tac", aTargets: ['dt_date_created'],   mData: "dt_date_created",  bSortable: false  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_date_updated'],   mData: "dt_date_updated",  bSortable: true  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_date_expire'],    mData: "dt_date_expire",   bSortable: true  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_status'],         mData: "dt_status",        bSortable: true  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_type'],           mData: "dt_type",          bSortable: true },
				{ sClass: "w-80 tac",      aTargets: ['dt_actions'],        mData: "dt_actions",       bSortable: false },
			],
		};
		var dtUpgradeRequests = $('#dtUpgradeRequests').dataTable(datatableOptions);

        mix(window, {
            updateTable: updateTable,
            dt_redraw_callback: updateTable,
            confirm_upgrade: confirmUpgrade,
            cancel_upgrade: cancelUpgrade,
        });
	});
</script>

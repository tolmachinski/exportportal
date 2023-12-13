<?php views()->display('new/download_script'); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Users' verification</span>
		</div>
        <?php views()->display('admin/verification/filter_view'); ?>

		<div class="mt-10 wr-filter-list clearfix"></div>

		<table id="dtAccreditationDocs" class="data table-bordered table-striped w-100pr dataTable">
			<thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_photo">Photo</th>
                    <th class="dt_user">User</th>
                    <th class="dt_email">Email</th>
                    <th class="dt_group">Group</th>
                    <th class="dt_bills">Bills</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_reg_date">Registered</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_resent_verification">Resend<br>count</th>
                    <th class="dt_resend_email_date">Last resend date</th>
                    <th class="dt_last_upload_date">Last upload date</th>
                    <th class="dt_calling_status">Calling<br>status</th>
                    <th class="dt_calling_date">Last calling<br>date</th>
                    <th class="dt_actions">Actions</th>
                </tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
	</div>
</div>

<script src="<?php echo fileModificationTime('public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js'); ?>"></script>
<script>
	$(document).ready(function() {
        var formatDetails = function (table, row) {
            var data = table.fnGetData(row);

            return '<div class="dt-details">' +
                    '<table class="dt-details__table">' +
                        '<tr><td class="w-100">Company:</td><td>' + data.dt_company +'</td></tr>' +
                        '<tr><td class="w-100">Phone/Fax:</td><td>' + data.dt_phone_fax +'</td></tr>' +
                        '<tr><td class="w-100">Custom Location:</td><td>' + data.dt_custom_location +'</td></tr>' +
                        '<tr><td class="w-100">Address:</td><td>' + data.dt_full_address +'</td></tr>' +
                    '</table>' +
                '</div>';
        };
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
        var onShowDetails = function (table) {
			var cell = $(this);
			var row = cell.parents('tr').get(0);

			if (table.fnIsOpen(row)) {
				table.fnClose(row);
            } else {
				table.fnOpen(row, formatDetails(table, row), 'details');
            }

            cell.toggleClass('ep-icon_plus ep-icon_minus');
		};
        var onDeferredShowDetails = function (e) {
            return onShowDetails.call(this, getTable());
        };
        var onToggleBillDetails = function(e) {
            e.preventDefault();

            var self = $(this);
            var toggleId = self.data('toggle');
            if (self.hasClass('active')) {
                self.removeClass('active');
            } else {
                self.addClass('active');
            }

            $('#' + toggleId).toggle();
            $.fancybox.reposition();
        };
        var updateTable = function (force) {
            dtAccreditationDocs.fnDraw(typeof force !== 'undefined' ? Boolean(~~force) : false);
        };
        var deleteUser = function (button) {
            var url = __group_site_url + 'users/ajax_operations/delete_user';
            var userId = button.data('user') || null;
            var onRequestSuccess = function (data) {
                systemMessages(data.message, data.mess_type);
                if(data.mess_type == 'success'){
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
            return dtAccreditationDocs;
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
			sorting : [[1, 'desc']],
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: __group_site_url + "verification/ajax_users_list_dt",
            fnServerData: onServerRequest.bind(null, filters),
			sPaginationType: "full_numbers",
			aoColumnDefs: [
				{ sClass: "vam w-50 tac",  aTargets: ['dt_id'],                  mData: "dt_id",                  bSortable: true  },
				{ sClass: "vam w-60",      aTargets: ['dt_photo'],               mData: "dt_photo",               bSortable: false },
				{ sClass: "vam tal",       aTargets: ['dt_user'],                mData: "dt_user",                bSortable: true  },
				{ sClass: "vam w-200 tac", aTargets: ['dt_email'],               mData: "dt_email",               bSortable: true  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_group'],               mData: "dt_group",               bSortable: true  },
				{ sClass: "vam w-150 tac", aTargets: ['dt_bills'],               mData: "dt_bills",               bSortable: false },
				{ sClass: "vam w-100 tac", aTargets: ['dt_reg_date'],            mData: "dt_reg_date",            bSortable: true  },
				{ sClass: "vam w-100 tac", aTargets: ['dt_country'],             mData: "dt_country",             bSortable: true  },
				{ sClass: "vam w-100 tac", aTargets: ['dt_status'],              mData: "dt_status",              bSortable: false },
				{ sClass: "vam w-100 tac", aTargets: ['dt_resent_verification'], mData: "dt_resent_verification", bSortable: true  },
				{ sClass: "vam w-100 tac", aTargets: ['dt_last_upload_date'],    mData: "dt_last_upload_date",    bSortable: true  },
				{ sClass: "vam w-100 tac", aTargets: ['dt_resend_email_date'],   mData: "dt_resend_email_date",   bSortable: true  },
				{ sClass: "vam w-100 tac", aTargets: ['dt_calling_status'],      mData: "dt_calling_status",      bSortable: false },
				{ sClass: "vam w-100 tac", aTargets: ['dt_calling_date'],        mData: "dt_calling_date",        bSortable: true  },
				{ sClass: "vam w-80 tac",      aTargets: ['dt_actions'],             mData: "dt_actions",             bSortable: false },
			],
		};
		var dtAccreditationDocs = $('#dtAccreditationDocs').dataTable(datatableOptions);

        $('body').on('click', 'a[rel=user_details]', onDeferredShowDetails);
		$('body').on('click', '.toogle_bill_detail', onToggleBillDetails);

        mix(window, {
            updateTable: updateTable,
            dt_redraw_callback: updateTable,
            delete_user: deleteUser,
        });
	});
</script>

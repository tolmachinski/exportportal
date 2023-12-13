<div class="container-fluid content-dashboard" id="company-edit-requests--list--wrapper">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span><?php echo cleanOutput($title); ?></span>
			</div>

			<?php views()->display('admin/company_edit_requests/filter_panel_view', ['filters' => $filters ?? []]); ?>

			<div class="wr-filter-list mt-10 clearfix"></div>

			<table id="company-edit-requests--list" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                    <tr>
                        <th class="dt-request">#</th>
                        <th class="dt-user">User</th>
                        <th class="dt-company">Company</th>
                        <th class="dt-status">Status</th>
                        <th class="dt-reason">Reason</th>
                        <th class="dt-created-at">Created</th>
                        <th class="dt-updated-at">Updated</th>
                        <th class="dt-accepted-at">Accepted</th>
                        <th class="dt-declined-at">Declined</th>
                        <th class="dt-actions">Actions</th>
                    </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>

<?php views()->display('new/download_script'); ?>
<script><?php echo getPublicScriptContent('plug_admin/js/company_edit_requests/dashboard.js', true); ?></script>
<script>
    $(function () {
		if (!('CompanyEditRequestDashboardModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'CompanyEditRequestDashboardModule' must be defined"))
			}

			return;
        }

        CompanyEditRequestDashboardModule.default(
            <?php echo json_encode([
                'listUrl'          => getUrlForGroup('/company_edit_requests/ajax_operations/list?mode=legacy'),
                'datagrid'         => '#company-edit-requests--list',
                'tableFilter'      => '.dt_filter',
                'activeFilters'    => '.wr-filter-list',
                'datepickerFields' => 'input.datepicker',
            ]); ?>
        );
	});
</script>

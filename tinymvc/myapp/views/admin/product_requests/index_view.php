<div class="container-fluid content-dashboard" id="product-requests--list--wrapper">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span><?php echo cleanOutput($title); ?></span>
			</div>

			<?php views()->display('admin/product_requests/filter_panel_view'); ?>

			<div class="wr-filter-list mt-10 clearfix"></div>

			<table id="product-requests--list" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                    <tr>
                        <th class="dt-request">#</th>
                        <th class="dt-user">User</th>
                        <th class="dt-product">Product</th>
                        <th class="dt-quantity">Quantity</th>
                        <th class="dt-start-price">Price (from)</th>
                        <th class="dt-final-price">Price (to)</th>
                        <th class="dt-departure-country">Country (from)</th>
                        <th class="dt-destination-country">Country (to)</th>
                        <th class="dt-created-at">Created</th>
                        <th class="dt-updated-at">Updated</th>
                    </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>

<script><?php echo getPublicScriptContent('plug_admin/js/product_requests/dashboard.js', true); ?></script>
<script>
    $(function () {
		if (!('ProductRequestDashboardModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'ProductRequestDashboardModule' must be defined"))
			}

			return;
        }

        ProductRequestDashboardModule.default(
            <?php echo json_encode(array(
                'listUrl'   => getUrlForGroup('product_requests/ajax_operations/list'),
                'selectors' => array(
                    'table'            => '#product-requests--list',
                    'wrapper'          => '#product-requests--list--wrapper',
                    'tableFilter'      => '.dt_filter',
                    'activeFilters'    => '.wr-filter-list',
                    'datepickerFields' => 'input.datepicker',
                ),
            )); ?>
        );
	});
</script>

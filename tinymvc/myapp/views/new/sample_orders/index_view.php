<div id="filter-panel" class="display-n">
	<?php views()->display('new/sample_orders/filter_panel_view', array('statuses' => $statuses ?? array(), 'filters' => $filters ?? array())); ?>
</div>

<div class="container-center dashboard-container">

	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Sample Orders</h1>

        <div class="dashboard-line__actions">
			<?php if (have_right('create_sample_order')) { ?>
				<span class="btn btn-primary pl-20 pr-20 fancyboxValidateModal fancybox.ajax"
					data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/create_order"); ?>"
					data-title="Create Sample Order"
					title="Create Sample Order">
					<i class="ep-icon ep-icon_plus-circle fs-20"></i>
					<span class="dn-m-min">Create Order</span>
				</span>
			<?php } ?>

			<a class="btn btn-dark fancybox btn-filter" id="order-samples--filters--button" href="#filter-panel" data-mw="320" data-title="Filter panel">
				<i class="ep-icon ep-icon_filter"></i> Filter
			</a>
		</div>
	</div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo cleanOutput(translate('sample_orders_my_description')); ?></span>
	</div>

    <div class="columns-content">

		<div id="columns-content-left" class="columns-content__one dn-m w-40pr-lg columns-content__one--250">
			<div class="columns-content__ttl">Statuses</div>

			<?php views()->display('new/sample_orders/sidebar_view', array('statuses' => $statuses ?? array(), 'selected_status' => $status ?? null)); ?>
		</div>

		<div id="columns-content-center" class="columns-content__one w-60pr-lg w-100pr-m columns-content__one--350">
			<div id="order-samples--samples-list--label"
				class="columns-content__ttl"
				data-text-not-found="No samples"
				data-text-search="Search samples"
				data-text-all="All samples">
				<?php if (!empty($status)) { ?>
					<?php echo cleanOutput($status['name'] ?? "All samples"); ?>
				<?php } else { ?>
					All samples
				<?php } ?>
			</div>

            <div id="order-samples--list" class="order-users-list jscroll-init">
                <ul class="order-users-list-ul clearfix">
					<?php views()->display('new/sample_orders/samples_block_view', array('samples' => $samples ?? array(), 'is_seller' => $is_seller ?? false)); ?>
                </ul>
            </div>

            <?php views()->display('new/sample_orders/pagination_view', array('paginator' => $paginator ?? array())); ?>
        </div>

        <div id="columns-content-right" class="columns-content__one dn-lg">
			<div class="columns-content__ttl">
				<span>Sample Order information</span>
			</div>

            <div id="order-samples--details" class="wr-orders-detail jscroll-init">
                <div class="info-alert-b no-selected js-no-content" <?php if (!empty ($sample_details['sample'])) { ?>style="display: none;"<?php } ?>>
                    <i class="ep-icon ep-icon_info"></i>
                    <span>Please select a Sample Order.</span>
				</div>

				<?php if (null !== $sample_details) { ?>
					<div class="js-sample-details">
						<?php views()->display('new/sample_orders/sample_details_view', $sample_details); ?>
					</div>
				<?php } else { ?>
					<div class="js-sample-details" style="display: none;"></div>
				<?php } ?>
            </div>
		</div>
	</div>
</div>

<script><?php echo getPublicScriptContent('plug/lodash-custom-4-17-5/lodash.custom.min.js', true); ?></script>

<?php
    echo dispatchDynamicFragment(
        "sample-order:dashboard",
        [
            [
                'baseUrl'      => getUrlForGroup('/sample_orders/my'),
                'ordersUrl'    => getUrlForGroup('sample_orders/ajax_operations/find_orders'),
                'detailsUrl'   => getUrlForGroup('/sample_orders/ajax_operations/sample'),
                'countersUrl'  => getUrlForGroup('/sample_orders/ajax_operations/counters'),
                'pageTemplate' => '<option value="{{value}}" {{selected}}>{{text}}</option>',
                'urlMetadata'  => arrayCamelizeAssocKeys($metadata['url'] ?? array()),
                'filters'      => arrayCamelizeAssocKeys($filters),
                'paginator'    => arrayCamelizeAssocKeys($paginator),
                'selectors'    => [
                    'paginationWrapper' => '#order-samples--pagination',
                    'samplesDetails'    => '#order-samples--details',
                    'statusesList'      => '#order-samples--statuses',
                    'filterButton'      => '#order-samples--filters--button',
                    'samplesLabel'      => '#order-samples--samples-list--label',
                    'searchForm'        => '#order-samples--form',
                    'rightBlock'        => '#columns-content-right',
                    'centerBlock'       => '#columns-content-center',
                    'samplesList'       => '#order-samples--list',
                    'statusCounters'    => '.js-status-counter',
                    'assignedStatus'    => '.js-assigned-status',
                    'activeStatusItem'  => '.js-status-item.active',
                    'previousButton'    => '.js-prev-button',
                    'resetFilters'      => '.js-filters-reset',
                    'statusItem'        => '.js-status-item',
                    'sampleItem'        => '.js-sample-item',
                    'nextButton'        => '.js-next-button',
                    'typesList'         => '.js-types-list',
                    'keywords'          => '.js-keywords',
                    'pagesList'         => '.js-pages-list',
                    'pageLabel'         => '.js-total-text',
                    'popovers'          => '.js-popover',
                    'listAlert'         => 'li.js-no-content',
                    'listElement'       => 'li',
                    'statusTitle'       => '.js-status-title',
                    'detailsAlert'      => '.js-no-content',
                    'detailsScroll'     => '.js-details-scroll',
                    'detailsContent'    => '.js-sample-details',
                    'totalPagesLabel'   => '.js-pages-total',
                ],
            ]
        ],
        true
    );
?>

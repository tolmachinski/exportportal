<ul id="order-samples--statuses" class="dashboard-statuses">
    <li id="processing" class="dashboard-statuses__item js-status-item-group active">
		<ul class="dashboard-sidebar-sub">
            <li class="dashboard-sidebar-sub__item js-status-item <?php if (null === $selected_status) { ?>active<?php } ?>" data-status="all" data-status-ignore="1">
                <div class="dashboard-sidebar-sub__text">
                    <span class="js-status-title">All statuses</span>
                </div>
                <div class="dashboard-sidebar-sub__counter js-status-counter" id="order-samples--counter--all">
                    <?php echo cleanOutput(array_sum(array_column($statuses, 'samples_count'))); ?>
                </div>
            </li>

			<?php foreach ($statuses as $status) { ?>
                <li class="dashboard-sidebar-sub__item js-status-item <?php if (null !== $selected_status && (int) $selected_status['id'] === (int) $status['id']) { ?>active<?php } ?>"
                    data-status="<?php echo cleanOutput($status['alias'] ?? ''); ?>">
					<div class="dashboard-sidebar-sub__text">
						<i class="dashboard-sidebar-sub__icon ep-icon <?php echo cleanOutput($status['icon'] ?? ''); ?>"></i>
						<span class="js-status-title"><?php echo cleanOutput($status['name'] ?? ''); ?></span>
					</div>
                    <div class="dashboard-sidebar-sub__counter js-status-counter" id="order-samples--counter--<?php echo cleanOutput($status['alias'] ?? ''); ?>">
                        <?php echo cleanOutput($status['samples_count'] ?? 0); ?>
                    </div>
				</li>
			<?php } ?>
		</ul>
	</li>
</ul>

<script>
    (function(global, mix) {
        var dashboardStatuses = function(obj) {
            var self = $(obj);

            self.closest('.js-status-item-group')
                .toggleClass('active')
                .siblings()
                    .removeClass('active');
        };

        mix(global, { dashboardStatuses: dashboardStatuses });
    } (window, mix || $.noop));
</script>

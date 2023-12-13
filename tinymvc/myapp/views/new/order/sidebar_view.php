<ul id="dashboard-statuses" class="dashboard-statuses">
	<li class="dashboard-statuses__item-link">
		<a class="link call-function" data-callback="loadAllOrders" href="#">All orders</a>
	</li>
	<li id="processing" class="dashboard-statuses__item active">
		<div class="dashboard-statuses__ttl call-function" data-callback="dashboardStatuses">
			<span class="dashboard-statuses__name">Processing</span>
			<i class="ep-icon ep-icon_plus-stroke"></i>
		</div>
		<ul class="dashboard-sidebar-sub">
			<?php foreach($statuses_process as $status_process){?>
				<li class="dashboard-sidebar-sub__item" data-status="<?php echo $status_process['alias']?>">
					<div class="dashboard-sidebar-sub__text">
						<i class="dashboard-sidebar-sub__icon ep-icon <?php echo $status_process['icon_new']?>"></i>
						<span><?php echo $status_process['status']?></span>
					</div>
					<div class="dashboard-sidebar-sub__counter" id="counter-<?php echo $status_process['alias']?>"><?php echo $status_process['counter']?></div>
				</li>
			<?php }?>
		</ul>
	</li>

	<li id="finished" class="dashboard-statuses__item">
		<div class="dashboard-statuses__ttl call-function" data-callback="dashboardStatuses">
			<span class="dashboard-statuses__name">Finished</span>
			<i class="ep-icon ep-icon_plus-stroke"></i>
		</div>
		<ul class="dashboard-sidebar-sub">
			<?php foreach($statuses_finished as $status_finished){?>
				<li class="dashboard-sidebar-sub__item" data-status="<?php echo $status_finished['alias']?>">
					<div class="dashboard-sidebar-sub__text" <?php echo addQaUniqueIdentifier("page__my-orders__statuses-finished_order-completed")?>>
						<i class="dashboard-sidebar-sub__icon ep-icon <?php echo $status_finished['icon_new']?>"></i>
						<span><?php echo $status_finished['status']?></span>
					</div>
					<div class="dashboard-sidebar-sub__counter" id="counter-<?php echo $status_finished['alias']?>"><?php echo $status_finished['counter']?></div>
				</li>
			<?php }?>
		</ul>
	</li>
</ul>

<script>
	var dashboardStatuses = function(obj){
		var $this = $(obj);

		$this.closest('.dashboard-statuses__item').toggleClass('active')
			.siblings().removeClass('active');
	}
</script>

<ul id="dashboard-statuses" class="dashboard-statuses">
	<li class="dashboard-statuses__item-link">
		<a class="link call-function" data-callback="loadAllEstimate" href="#">All Estimates</a>
	</li>
	
	<li id="processing" class="dashboard-statuses__item active">
		<div class="dashboard-statuses__ttl call-function" data-callback="dashboardStatuses">
			<span class="dashboard-statuses__name">Processing</span>
			<i class="ep-icon ep-icon_plus-stroke"></i>
		</div>
		<ul class="dashboard-sidebar-sub">
			<?php foreach($status_array as $key => $statuses_item){?>
				<?php if($key !== 'estimate_number' && $key !== 'expire_soon' ){?>
				<li class="dashboard-sidebar-sub__item" data-status="<?php echo $key; ?>">
					<div class="dashboard-sidebar-sub__text">
						<i class="dashboard-sidebar-sub__icon ep-icon ep-icon_<?php echo $statuses_item['icon_new']?>"></i>
						<span><?php echo $statuses_item['title']?></span>
					</div>
					<div class="dashboard-sidebar-sub__counter" id="counter-<?php echo $key; ?>"><?php echo $statuses_item['counter']?></div>
				</li>
				<?php }?>
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
<ul id="dashboard-statuses" class="dashboard-statuses">
	<li class="dashboard-statuses__item-link">
		<a class="link call-function" data-callback="loadAllBills" href="#">All Bills</a>
	</li>

	<?php foreach($status_array as $status_key => $status){?>
		<li 
			id="<?php echo $status_key;?>" 
			class="dashboard-statuses__item <?php if($status['title'] == 'Pending') echo 'bdt-none';?> <?php if($active_status == $status_key) echo 'active';?>">
			<div class="dashboard-statuses__ttl call-function" data-callback="dashboardStatuses">
				<span class="dashboard-statuses__name"><?php echo $status['title'];?></span>
				<i class="ep-icon ep-icon_plus-stroke"></i>
			</div>
			<ul class="dashboard-sidebar-sub">
				<?php foreach($types_array as $type_key => $type){?>
					<li class="dashboard-sidebar-sub__item" data-status="<?php echo $status_key;?>" data-type="<?php echo $type_key;?>">
						<div class="dashboard-sidebar-sub__text">
							<i class="dashboard-sidebar-sub__icon ep-icon ep-icon_<?php echo $type['icon_new'];?>"></i>
							<span><?php echo $type['title']?></span>
						</div>
						<div class="dashboard-sidebar-sub__counter" id="counter-<?php echo $status_key; ?>_<?php echo $type_key;?>"><?php echo (int)$count_bills[$status_key][$type_key];?></div>
					</li>
				<?php }?>
			</ul>
		</li>
	<?php }?>
</ul>
<script>
	var dashboardStatuses = function(obj){
		var $this = $(obj);

		$this.closest('.dashboard-statuses__item').toggleClass('active')
			.siblings().removeClass('active');
	}
</script>
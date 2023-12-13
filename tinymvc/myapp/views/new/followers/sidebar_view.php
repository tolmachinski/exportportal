<?php
    $followers_statuses['followers']['counter'] = intval($followers_count);
    $followers_statuses['followed']['counter'] = intval($followed_count);
?>
<ul id="dashboard-statuses" class="dashboard-statuses">
	<li id="processing" class="dashboard-statuses__item active">
		<ul class="dashboard-sidebar-sub">
		<?php foreach($followers_statuses as $key => $followers_statuses_item){?>
			<li class="dashboard-sidebar-sub__item <?php echo ($key == 'followers')?'active':'';?>" data-status="<?php echo $key; ?>">
				<div class="dashboard-sidebar-sub__text">
					<i class="dashboard-sidebar-sub__icon ep-icon ep-icon_<?php echo $followers_statuses_item['icon']?>"></i>
					<span <?php echo addQaUniqueIdentifier('followers-my__sidebar-followers-link') ?>><?php echo $followers_statuses_item['title']?></span>
				</div>
				<div class="dashboard-sidebar-sub__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter') ?> id="counter-<?php echo $key; ?>"><?php echo $followers_statuses_item['counter']?></div>
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

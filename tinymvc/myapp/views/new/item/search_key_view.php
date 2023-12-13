
<div class="container-center-sm">
	<ul class="ep-search-keys clearfix pb-10">
		<?php foreach($search_params as $item){?>
			<li class="ep-search-keys__item">
				<div class="ep-search-keys__ttl"><?php echo $item['param']?>: </div>
				<div class="ep-search-keys__param">
					<span class="ep-search-keys__name"><?php echo $item['title']?></span>
					<a class="ep-search-keys__close" href="<?php echo $item['link'];?>">
						<i class="ep-icon ep-icon_remove-stroke "></i>
					</a>
				</div>
			</li>
		<?php }?>
	</ul>

	<ul class="ep-search-keys clearfix pb-10">
		<?php foreach($search_attr_params as $item){?>
			<li class="ep-search-keys__item">
				<div class="ep-search-keys__ttl"><?php echo $item['param']?>: </div>
				<div class="ep-search-keys__param">
					<span class="ep-search-keys__name"><?php echo $item['title']?></span>
					<a class="ep-search-keys__close" href="<?php echo $item['link'];?>">
						<i class="ep-icon ep-icon_remove-stroke"></i>
					</a>
				</div>
			</li>
		<?php }?>
	</ul>
</div>

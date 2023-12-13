<div class="clearfix">
	<?php if(!empty($search_params)){?>
		<ul class="ep-search-keys clearfix pb-10">
			<?php foreach($search_params as $item){?>
				<li class="ep-search-keys__item">
					<div class="ep-search-keys__ttl"><?php echo $item['param']?>: </div>
					<div class="ep-search-keys__param">
						<span class="ep-search-keys__name"><?php echo $item['title']?></span>
						<a class="ep-search-keys__close" href="<?php echo $item['link']; if($item['param'] != 'Keywords') echo $get_params;?>">
							<i class="ep-icon ep-icon_remove-stroke"></i>
						</a>
					</div>
				</li>
			<?php }?>
		</ul>
	<?php }?>
</div>

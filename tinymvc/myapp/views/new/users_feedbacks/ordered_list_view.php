<?php foreach($ordered_list as $item){?>
	<a class="feedback-popup__items-one flex-card" href="<?php echo __SITE_URL;?>items/ordered/<?php echo strForURL($item['title']).'-'.$item['id_ordered_item'];?>" target="_blank">
		<div class="feedback-popup__items-one-img image-card3 flex-card__fixed">
			<span class="link">
                <img
                    class="image"
                    src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_snapshot'], '{FILE_NAME}' => $item['main_image']), 'items.snapshot', array( 'thumb_size' => 1 )); ?>"
                    alt="<?php echo $item['title']?>"
                />
			</span>
		</div>
		<div class="flex-card__float">
			<span class="feedback-popup__items-txt"><?php echo $item['title']?></span>
		</div>
	</a>
<?php }?>

<link rel="dns-prefetch stylesheet" href="<?php echo asset("public/build/styles_user_pages_general.css");?>" />
<link rel="dns-prefetch stylesheet" href="<?php echo fileModificationTime("public/css/styles_new.css");?>">
<link rel="dns-prefetch stylesheet" href="<?php echo asset("public/build/styles_user_pages.css");?>" />

<div class="review-popup flex-card">
	<div class="review-popup__img flex-card__fixed image-card3">
		<span class="link">
			<?php
				$item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photos[0]['photo_name']), 'items.main', array( 'thumb_size' => 3 ));
			?>
			<img
				class="image"
				src="<?php echo $item_img_link; ?>"
				alt="<?php echo $item["title"]?>"
			/>
		</span>
	</div>
	<div class="review-popup__desc flex-card__float">
		<div class="review-popup__ttl"><?php echo $item["title"]?></div>
		<!-- <span>(<?php echo $sold_counter?> sold)</span> -->
		<div class="review-popup__price">Price:
			<?php if($item['discount']){?>
				<strike><?php echo $item['curr_entity']?><?php echo number_format($item['price'],2) ?></strike>
				(discount <?php echo $item['discount']?>&#37;)
				<span><?php echo $item['curr_entity']?><?php echo $item['final_price']?></span>
			<?php } else {?>
				<span><?php echo $item['curr_entity']?><?php echo number_format($item['price'],2)?></span>
			<?php } ?>
		</div>
	</div>
</div>

<?php tmvc::instance()->controller->view->display('new/item/header_reviews_view');?>
<?php tmvc::instance()->controller->view->display('new/users_reviews/reviews_scripts_view');?>

<div class="scroll-reviews-list mh-300 overflow-y-a">
	<div class="ppersonal-title">
		<h2 class="ppersonal-title__txt">Reviews</h2>
	</div>
	<ul class="product-comments">
		<?php tmvc::instance()->controller->view->display('new/users_reviews/item_view'); ?>
	</ul>

	<?php if($reviews_count > 10){?>
		<a href="items-<?php echo $item['id'];?>" class="button-more-gray">view more</a>
	<?php }?>
</div>

<script>
	$(document).ready(function(){
		if(typeof block != 'undefined'){
			scrollToElementModal('#' + block, '.scroll-reviews-list');
		}

		$('.rating-bootstrap').rating();
	});
</script>

<div class="review-b w-710">
	<div class="product-info-item clearfix fn">
		<div class="pull-left img-b relative-b">
            <img
                src="<?php echo getDisplayImageLink(array('{ID}' => $item["id"], '{FILE_NAME}' => $photos[0]['photo_name']), 'items.main', array( 'thumb_size' => 1 )); ?>"
                alt="<?php echo $item["title"]?>"
            />
		</div>
		<div class="pull-right info-b">
			<div class="info-title"><?php echo $item["title"]?></div>
			<span>(<?php echo $sold_counter?> sold)</span>
			<div class="price">Price:
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

	<div class="center-b clearfix">
		<div class="average-rating">
			<div class="pull-left">
				<h4>Average Rating</h4>
				<div class="rating clearfix">
					<?php
						if($item['rev_numb'] < 1){
							$item['rating'] = 0;
						}
					?>
					<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-23" data-empty="ep-icon ep-icon_star-empty txt-blue fs-23" type="hidden" name="val" value="<?php echo $item['rating']?>" data-readonly>
				</div>
				<span>based on <?php echo $item['rev_numb']?> reviews</span>
			</div>
			<?php $one_mark = round(120 / $item['rev_numb']); ?>

			<ul class="rating-statistic pull-left">
				<?php foreach($rank_counters as $mark => $rank_item){ ?>
				<li>
					<div class="name-b"><?php echo $rank_item['name']; ?></div>
					<div class="line-rating"><span style="width:<?php echo $one_mark * $rank_item['count'] + 0?>px"></span></div>
					<div class="count-b"><?php echo $rank_item['count']; ?></div>
				</li>
				<?php } ?>
			</ul>
		</div>

		<?php if(isset($user_ordered_item) && !empty($user_ordered_item)){?>
			<a class="btn btn-primary btn-lg pull-right mt-30 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL?>reviews/popup_forms/add_review/?item=<?php echo $item["id"]; ?>" data-title="Add review">Add review</a>
		<?php }?>
	</div>

	<div class="scroll-reviews-list clearfix mh-300 overflow-y-a">
		<div class="ppersonal-title">
			<h2 class="ppersonal-title__txt">Reviews</h2>
		</div>
		<ul id="user-reviews-list" class="feedbacks-main-list feedbacks-main-list--pr-10 clearfix">
			<?php views('admin/users_reviews/item_view'); ?>
		</ul>

		<?php if($reviews_count > 10){?>
			<a href="items-<?php echo $item['id'];?>" class="button-more-gray">view more</a>
		<?php }?>
	</div>
</div>
<script>
$(document).ready(function(){
	if(typeof block != 'undefined')
		scrollToElementModal('#' + block, '.scroll-reviews-list');

	$('.rating-bootstrap').rating();
})

delete_review = function(obj){
	var $this = $(obj);
	var review = $this.data('review');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>reviews/ajax_review_operation/delete',
		data: { checked_reviews : review},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				$this.closest('li').remove();
				if($('#user-reviews-list li').length == 0){
					$('#user-reviews-list').html('<div class="info-alert-b no-reviews"><i class="ep-icon ep-icon_info"></i> This seller does not have any reviews yet.</div>');
				}

				deleteReviewCallback(resp);
			}
		}
	});
}
</script>

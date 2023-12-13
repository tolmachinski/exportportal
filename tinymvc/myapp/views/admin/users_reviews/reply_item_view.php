<li class="feedbacks-main-list__res-item" id="li-review-reply-<?php echo $review['id_review']?>">
	<div class="feedbacks-main-list__res-ttl">
		<div class="feedbacks-main-list__res-name">replied by Seller</div>
		<div class="feedbacks-main-list__res-date"><?php echo formatDate($review['reply_date']);?></div>
	</div>
	<div class="feedbacks-main-list__res-text"><?php echo $review['reply'];?></div>

	<div class="clearfix">
	<?php if(logged_in() && !empty($review['reply']) && $review['rev_status'] == 'new' && is_privileged('user',$review['id_seller'], 'reply_reviews')){?>
		<div class="dropdown pull-right">
			<a class="dropdown-toggle fs-12 txt-red" data-toggle="dropdown">
				Actions
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li>
					<a class="ep-actions__link fancybox.ajax fancyboxValidateModal" data-title="Edit review reply" title="Edit review reply" href="reviews/popup_forms/edit_reply/<?php echo $review['id_review'];?>"><i class="ep-icon ep-icon_pencil"></i> Edit</a>
				</li>
			</ul>
		</div>
	<?php }?>
	</div>
</li>

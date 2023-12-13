<?php if(!empty($reviews)){?>
	<?php foreach($reviews as $review){?>
		<li class="feedbacks-main-list__item" id="li-review-<?php echo $review['id_review']?>" itemprop="review" itemscope itemtype="http://schema.org/Review">
			<div class="feedbacks-main-list__img">
				<?php if(!$view_user_review){?>
					<img class="image" src="<?php echo getDisplayImageLink(['{ID}' => $review['id_item'], '{FILE_NAME}' => $review['photo_name']], 'items.main', ['thumbSize' => 1]);?>" alt="<?php echo cleanOutput($review['item_name']);?>" />
				<?php }else{?>
					<img class="image" src="<?php echo $review['userImageUrl']?>" alt="<?php echo $review['fname']?>" />
				<?php }?>

				<?php if(logged_in()){?>
				<div class="did-help <?php if(isset($helpful_reviews[$review['id_review']])){?>rate-didhelp<?php }?> pt-15">
					<div class="did-help__txt">Did it help?</div>
					<?php
						$disable_class = "";
						if($review['id_user'] == id_session())
							$disable_class = "disabled";
					?>
					<span class="i-up didhelp-btn
						<?php if($disable_class == "disabled"){
							echo $disable_class;
						}elseif(isset($helpful_reviews[$review['id_review']])){
							echo equals($helpful_reviews[$review['id_review']],1,'disabled');
						}?>"
						data-item="<?php echo $review['id_review']?>" data-page="reviews" data-type="review" data-action="y">
						<span class="counter-b"><?php echo $review['count_plus']?></span>
						<a class="ep-icon ep-icon_up"></a>
					</span>
					<span class="i-down didhelp-btn
						<?php if($disable_class == "disabled"){
							echo $disable_class;
						}elseif(isset($helpful_reviews[$review['id_review']])){
							echo equals($helpful_reviews[$review['id_review']],0,'disabled');
						}?>"
						data-item="<?php echo $review['id_review']?>" data-page="reviews" data-type="review" data-action="n">
						<a class="ep-icon ep-icon_down"></a>
						<span class="counter-b"><?php echo $review['count_minus']?></span>
					</span>
				</div>
				<?php } ?>
			</div>

			<div class="feedbacks-main-list__detail">
				<div class="feedbacks-main-list__ttl" itemprop="name"><?php echo $review['rev_title'];?></div>

				<div class="feedbacks-main-list__detail-item">
					<div class="flex-display flex-ai--c">
						<img
                            width="24"
                            height="24"
                            src="<?php echo getCountryFlag($review['user_country']); ?>"
                            alt="<?php echo $review['user_country'];?>"
                            title="<?php echo $review['user_country'];?>"
                        />
						<span>
							<a class="feedbacks-main-list__name" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($review['fname'].'-'.$review['lname']).'-'.$review['id_user'];?>"><span itemprop="author"><?php echo $review['fname'].' '.$review['lname'];?></span></a>
						</span>
						<span class="pl-10">about</span>
						<span class="item">
							<a class="feedbacks-main-list__name" href="<?php echo __SITE_URL;?>item/<?php echo strForURL($review['item_name']).'-'.$review['id_item'];?>"><span itemprop="itemReviewed"><?php echo $review['item_name'];?></span></a>
						</span>
					</div>

					<div class="feedbacks-main-list__date lh-24" itemprop="datePublished"><?php echo formatDate($review['rev_date']);?></div>
				</div>

				<div class="feedbacks-main-list__user-rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
					<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-21" data-empty="ep-icon ep-icon_star-empty txt-blue fs-21" type="hidden" name="val" value="<?php echo $review['rev_raiting'];?>" data-readonly>
					<div class="display-n" itemprop="ratingValue"><?php echo $review['rev_raiting'];?></div>
				</div>

				<div class="feedbacks-main-list__text" itemprop="description"><?php echo $review['rev_text'];?></div>

				<div class="clearfix">
					<div class="ep-actions pull-right">
						<?php if(logged_in()){?>
							<?php if(have_right('write_reviews') && is_my($review['id_user']) && empty($review['reply']) && $review['rev_status'] != 'moderated'){?>
								<a class="ep-actions__link fancybox.ajax fancyboxValidateModal" data-title="Edit review" title="Edit review" href="<?php echo __SITE_URL?>reviews/popup_forms/edit_user_review/<?php echo $review['id_review']?>"><i class="ep-icon ep-icon_pencil"></i> Edit</a>
								<a class="ep-actions__link txt-red confirm-dialog" data-callback="delete_review" data-message="Are you sure you want to delete this review?" data-review="<?php echo $review['id_review']?>" href="#" title="Remove review"><i class="ep-icon ep-icon_remove"></i> Remove</a>
							<?php }?>
							<?php if(have_right('moderate_content')){?>
								<?php if($review['rev_status'] != 'moderated'){?>
									<a class="ep-actions__link txt-green confirm-dialog" data-callback="moderate_review" data-message="Are you sure you want to moderate this review?" data-review="<?php echo $review['id_review']?>" href="#" title="Moderate review"><i class="ep-icon ep-icon_sheild-ok"></i> Moderate</a>
								<?php }?>
								<a class="ep-actions__link txt-red confirm-dialog" data-callback="delete_review" data-message="Are you sure you want to delete this review?" data-review="<?php echo $review['id_review']?>" href="#" title="Remove review"><i class="ep-icon ep-icon_remove"></i> Remove</a>
							<?php }?>
							<?php if(empty($review['reply']) && is_privileged('user',$review['id_seller'], 'reply_reviews')){?>
								<a class="ep-actions__link btn-reply fancybox.ajax fancyboxValidateModal" data-title="Add review reply" title="Add review reply" href="reviews/popup_forms/leave_reply/<?php echo $review['id_review'];?>"><i class="ep-icon ep-icon_reply"></i> Reply</a>
							<?php }?>
							<a class="ep-actions__link fancyboxValidateModal fancybox.ajax txt-red" href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/review/<?php echo $review['id_review']; ?>/<?php echo $review['id_user'];?>/" data-title="Report this review" title="Report this review"><i class="ep-icon ep-icon_megaphone"></i> Report this</a>
						<?php }?>
					</div>
				</div>

				<ul class="feedbacks-main-list__res">
                    <?php
                        if(!empty($review['reply'])){
                            views('admin/users_reviews/reply_item_view', array('review' => $review));
                        }
                    ?>
				</ul>
			</div>
		</li>
	<?php }?>
<?php }else{?>
	<li><div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> No reviews.</div></li>
<?php }?>

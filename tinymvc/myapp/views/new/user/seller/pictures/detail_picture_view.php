<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="<?php echo translate('mobile_screen_sidebar_btn', null, true);?>" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	<?php echo translate('mobile_screen_sidebar_btn');?>
</a>

<div class="hmedia">
	<div class="detail-info spersonal-pic-detail">
		<div class="title-public pt-0">
			<h1 class="title-public__txt" <?php echo addQaUniqueIdentifier('page__company-pictures__details-title'); ?>><?php echo $picture['title_photo'];?></h1>
		</div>

		<div class="spersonal-pic-detail__category" <?php echo addQaUniqueIdentifier('page__company-pictures__details-category'); ?>>
			<?php echo $picture['category_title'];?>
		</div>
		<div class="spersonal-pic-detail__img">
			<img class="image" <?php echo addQaUniqueIdentifier('page__company-pictures__details-image'); ?> src="<?php echo $picture['imageLink']; ?>" alt="<?php echo $picture['title_photo'];?>"/>
		</div>

		<div class="spersonal-pic-detail__txt" <?php echo addQaUniqueIdentifier('page__company-pictures__details-text'); ?>>
			<?php echo $picture['description_photo'];?>
		</div>
	</div>

	<div class="detail-info">
		<div class="title-public">
			<h2 class="title-public__txt"><?php echo translate('general_comments_word');?> (<span id="counter_comment" <?php echo addQaUniqueIdentifier('global__comment-counter'); ?>><?php echo $picture['comments_count'];?></span>)</h2>

			<?php if(logged_in() && have_right('write_comments')){?>
			<div class="dropdown">
				<a class="dropdown-toggle" <?php echo addQaUniqueIdentifier('page__company-pictures__comments_dropdown-btn'); ?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
					<a class="dropdown-item fancybox.ajax fancyboxValidateModal" <?php echo addQaUniqueIdentifier('page__company-pictures__comments_dropdown-menu_leave-a-comment-btn'); ?> data-title="<?php echo translate('general_button_add_comment_text', null, true);?>" href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/add_comment/<?php echo $picture['id_photo'];?>">
						<i class="ep-icon ep-icon_pencil"></i>
						<?php echo translate('seller_pictures_leave_comment_text');?>
					</a>
				</div>
			</div>
			<?php }?>
		</div>

		<?php tmvc::instance()->controller->view->display('new/user/seller/pictures/list_comment_view'); ?>
	</div>

	<div class="title-public">
		<h2 class="title-public__txt"><?php echo translate('seller_pictures_more_pictures_text');?> (<?php echo $count_pictures;?>)</h2>
	</div>

	<?php tmvc::instance()->controller->view->display('new/user/seller/pictures/list_pictures_view'); ?>
</div>

<?php if(!isset($preview_item) && !isset($webpackData)){?>
	<?php app()->view->display('new/items_comments/comments_scripts_view'); ?>
	<?php app()->view->display('new/items_questions/scripts_questions_view'); ?>
<?php }?>

<div class="detail-info js-detail-info-wrapper">
	<div class="detail-info__ttl">
		<h2 class="detail-info__ttl-name">B2B</h2>
        <i
            class="ep-icon ep-icon_remove-stroke call-function call-action active"
            data-callback="productDetailToggle"
            data-js-action="item-detail:product-detail-toggle"
            <?php echo addQaUniqueIdentifier('item__toggle-info'); ?>
        >
        </i>
	</div>

	<div class="detail-info__toggle js-detail-info-toggle display-n">
		<?php app()->view->display('new/user/seller/b2b/b2b_tabs_view'); ?>
	</div>
</div>

<div class="detail-info js-detail-info-wrapper">
	<div class="detail-info__ttl">
		<h2 class="detail-info__ttl-name">Product information</h2>
        <i
            class="ep-icon ep-icon_remove-stroke call-function call-action"
            data-callback="productDetailToggle"
            data-js-action="item-detail:product-detail-toggle"
        >
        </i>
	</div>

	<div class="detail-info__toggle js-detail-info-toggle">
		<ul class="product-info">
			<?php $isset_additional_info = false;?>

			<?php if (!empty($item['year'])) {?>
				<?php $isset_additional_info = true;?>
				<li class="product-info__item">
					<span class="product-info__name">Year:</span>
					<span class="product-info__value" <?php echo addQaUniqueIdentifier("item__option-year-val")?>><?php echo $item['year'];?></span>
				</li>
			<?php }?>

			<?php if (!empty($item_attrs)) {
				$isset_additional_info = true;
				foreach ($item_attrs as $attr => $attr_info) {
					if(in_array($attr_info['attr_type'], array('select', 'multiselect'))){
						$attr_val = $attr_info['attr_values'];
					}else{
						$attr_val = $attr_info['attr_value'];
					}

					if(!empty($attr_val)){?>
					<li class="product-info__item">
						<span class="product-info__name" <?php echo addQaUniqueIdentifier("item__option-key")?>><?php echo $attr_info['attr_name'] ?>:</span>
						<span class="product-info__value" <?php echo addQaUniqueIdentifier("item__option-val")?>><?php echo $attr_val;?></span>
					</li>
				<?php }
				}
			}

			if (!empty($user_attrs)) {
				$isset_additional_info = true;
				foreach ($user_attrs as $attr) {
					if(!empty($attr['p_name']) && !empty($attr['p_value'])){?>
					<li class="product-info__item">
						<span class="product-info__name" <?php echo addQaUniqueIdentifier("item__option-key")?>><?php echo $attr['p_name'] ?>:</span>
						<span class="product-info__value" <?php echo addQaUniqueIdentifier("item__option-val")?>><?php echo $attr['p_value'] ?></span>
					</li>
				<?php }
				}
			}

			if (!$isset_additional_info) {?>
				<li class="w-100pr"><div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> No additional info.</div></li>
			<?php }?>
		</ul>

	</div>
</div>

<?php if(isset($vin_info) && !empty($vin_info)){?>
	<div class="detail-info js-detail-info-wrapper">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name">VIN decoded</h2>
            <i
                class="ep-icon ep-icon_remove-stroke call-function call-action"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
            >
            </i>
		</div>

		<div class="detail-info__toggle js-detail-info-toggle">
			<ul class="product-info">
				<?php foreach($vin_info as $vin_attr){ ?>
					<li class="product-info__item">
						<span class="product-info__name"><?php echo $vin_attr['name'] ?>:</span>
						<span class="product-info__value"><?php echo $vin_attr['value'] ?></span>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php } ?>

<?php
    if (
        !empty($item['description'])
        || !empty($item_description)
    ) {
?>
	<div class="detail-info js-detail-info-wrapper">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name">Description</h2>
            <i
                class="ep-icon ep-icon_remove-stroke call-function call-action"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
            >
            </i>
		</div>

		<div class="detail-info__toggle js-detail-info-toggle">
            <?php if (!empty($item_description)) { ?>
                <ul class="nav nav-tabs nav--borders nav--new" role="tablist">
                    <?php if (!empty($item['description'])) { ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="#js-tab-english-description" aria-controls="title" role="tab" data-toggle="tab">English</a>
                        </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a
                            class="nav-link<?php echo (empty($item['description']))?' active':''; ?>"
                            href="#js-tab-other-description"
                            aria-controls="title"
                            role="tab"
                            data-toggle="tab"
                        ><?php echo $item_description['lang_name'] ?></a>
                    </li>
                </ul>
                <div class="tab-content pt-15">
                    <?php if (!empty($item['description'])) { ?>
                    <div role="tabpanel" class="ep-tinymce-text tab-pane fade show active" id="js-tab-english-description">
                        <?php echo $item['description']; ?>
                    </div>
                    <?php } ?>
                    <div role="tabpanel" class="ep-tinymce-text tab-pane fade<?php echo (empty($item['description']))?' show active':''; ?>" id="js-tab-other-description">
                        <?php echo $item_description['item_description']; ?>
                    </div>
                </div>
            <?php }else if (!empty($item['description'])) { ?>
                <div class="ep-tinymce-text" itemprop="description">
                    <?php echo $item['description']; ?>
                </div>
            <?php } ?>
		</div>
	</div>
<?php } ?>

<?php if(isset($photos) && count($photos)){?>
	<div class="detail-info js-detail-info-wrapper">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name">Photos</h2>
            <i
                class="ep-icon ep-icon_remove-stroke call-function call-action"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
            >
            </i>
		</div>

		<div class="detail-info__toggle js-detail-info-toggle">
			<?php foreach ($photos as $photo){?>
				<?php
					$item_img_link = isset($photo['photo_type']) && $photo['photo_type'] === 'temp' ? $photo['photo_name'] : getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photo['photo_name']), 'items.photos');
				?>
			<img
				class="photo js-lazy"
				src="<?php echo getLazyImage($photo['width'], $photo['height']);?>"
				data-src="<?php echo $item_img_link;?>"
                alt="<?php echo $item['title'] ?>"
                <?php echo addQaUniqueIdentifier("item__photo")?>
			/>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php if (isset($item['video']) && !empty($item['video'])) { ?>
	<div class="detail-info js-detail-info-wrapper">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name">Video</h2>
            <i
                class="ep-icon ep-icon_remove-stroke call-function call-action"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
            >
            </i>
		</div>

		<div class="detail-info__toggle js-detail-info-toggle">
			<a
                class="wr-video-link fancybox.iframe fancyboxVideo"
                href="<?php echo get_video_link($item['video_code'], $item['video_source']);?>"
                data-title="Company Overview"
                data-mw="692"
                data-h="67%"
            >
                <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
                <?php if ($item['video_in_porcessing'] ?? false) { ?>
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(675, 380);?>"
                        data-src="<?php echo cleanOutput($item['video_image']) ;?>"
                        alt="<?php echo cleanOutput($item['title']); ?>"
                    >
                <?php } else { ?>
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(675, 380);?>"
                        data-src="<?php echo publicUrl("/storage/items/{$item['id']}/{$item['video_image']}", __IMG_URL) ;?>"
                        alt="<?php echo cleanOutput($item['title']); ?>"
                    >
                <?php } ?>
			</a>
		</div>
	</div>
<?php } ?>

<?php if(!isset($preview_item)){?>
	<div class="detail-info js-detail-info-wrapper js-product-detail-comments-section">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name">
				Comments

				<?php if (logged_in() && have_right('write_comments_on_item')) {?>
					<span class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<span class="dropdown-menu">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add comment" href="<?php echo __SITE_URL . 'items_comments/popup_forms/add_main_comment/' . $item["id"];?>" title="Add comment">
								<i class="ep-icon ep-icon_pencil"></i>
								Leave Comment
							</a>
						</span>
					</span>
				<?php }?>
			</h2>

			<i
                class="ep-icon ep-icon_remove-stroke call-function call-action <?php if(empty($comments)){?>active<?php }?>"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
                <?php echo addQaUniqueIdentifier("item__toggle-info")?>
            >
            </i>
		</div>

        <div class="detail-info__toggle js-detail-info-toggle <?php if(empty($comments)){?>display-n<?php }?>">
            <?php app()->view->display('new/items_comments/list_comments_view', array('comments' => $comments ?? array())); ?>
        </div>
    </div>

	<div class="detail-info js-detail-info-wrapper">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" id="questions-f">
				Questions

				<?php if (logged_in() && have_right('write_questions_on_item')) {?>
					<span class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<span class="dropdown-menu">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add question" href="<?php echo __SITE_URL . 'items_questions/popup_forms/add_question/' . $item["id"];?>" title="Add question">
								<i class="ep-icon ep-icon_question-circle"></i>
								Ask Question
							</a>
						</span>
					</span>
				<?php }?>
			</h2>
			<i
                class="ep-icon ep-icon_remove-stroke call-function call-action <?php if(empty($questions)){?>active<?php }?>"
                data-callback="productDetailToggle"
                data-js-action="item-detail:product-detail-toggle"
                <?php echo addQaUniqueIdentifier("item__toggle-info")?>
            >
            </i>
		</div>

        <div class="detail-info__toggle js-detail-info-toggle <?php if(empty($questions)){?>display-n<?php }?>">
            <?php app()->view->display('new/items_questions/list_questions_view', array('questions' => $questions ?? array(), 'helpful' => $helpful ?? array(), 'about' => 'hide' ));?>
        </div>
    </div>
<?php } ?>

<div class="detail-info js-detail-info-wrapper">
    <div class="detail-info__ttl">
        <h2 class="detail-info__ttl-name">
            <?php echo translate('item_details_info_reviews_title');?>
            <span class="detail-info__ttl-count"><?php echo $countProductReviews;?></span>
        </h2>
        <i
            class="ep-icon ep-icon_remove-stroke call-function call-action <?php echo empty($productReviews) ? 'active' : '';?>"
            data-callback="productDetailToggle"
            data-js-action="item-detail:product-detail-toggle"
            <?php echo addQaUniqueIdentifier("item__toggle-info")?>
        >
        </i>
    </div>
    <div class="detail-info__toggle js-detail-info-toggle <?php echo empty($productReviews) ? 'display-n' : '';?>">
        <ul class="product-comments">
            <?php if (empty($productReviews)) {?>
                <li><div class="default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i>
                    <?php echo translate('item_details_info_no_reviews_title');?>
                </div></li>
            <?php } else {?>
                <?php views('new/users_reviews/item_view', ['reviews' => $productReviews]); ?>

                <?php if ($countProductReviews > $limitReviews) {?>
                    <a class="btn btn-light mw-250 m-auto btn-block mt-20 mb-15" href="<?php echo makeItemUrl($item['id'], $item['title']) . '/reviews_ep';?>">Show all reviews</a>
                <?php }?>
            <?php }?>
        </ul>
    </div>
</div>

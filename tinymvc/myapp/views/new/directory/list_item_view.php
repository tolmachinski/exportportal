<?php $company_link = getCompanyURL($item);?>
<div class="companies__item flex-card <?php echo is_certified((int) $item['user_group']) ? 'border-certified' : '';?>" <?php echo addQaUniqueIdentifier('global__item__company-card')?>>
	<div class="companies__img-wr flex-card__fixed">
        <div class="companies__img image-card3">
            <a class="link" href="<?php echo $company_link;?>" target="_blank">
                <img
                    class="image js-lazy"
                    itemprop="logo"
                    width="140"
                    height="140"
                    <?php echo addQaUniqueIdentifier('global__item__company-card_image')?>
                    src="<?php echo getLazyImage(140, 140); ?>"
                    data-src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_company'], '{FILE_NAME}' => $item['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>"
                    alt="<?php echo $item['name_company'];?>"/>
            </a>
        </div>
	</div>
	<div class="companies__detail flex-card__float">
		<div class="companies__ttl" title="<?php echo $item['name_company'];?>">
			<a class="link" itemprop="url" <?php echo addQaUniqueIdentifier('global__item__company-card_title')?> href="<?php echo $company_link;?>">
				<span itemprop="name" class="notranslate"><?php echo $item['name_company'];?></span>
			</a>
        </div>

        <?php
        $groupName = $item['is_verified'] ? $item['user_group_name'] : trim(str_replace('Verified', '', $item['user_group_name']));?>
		<div class="companies__group <?php echo userGroupNameColor($item['user_group_name']);?>" <?php echo addQaUniqueIdentifier('global__item__company-card_user-group')?>>
            <?php echo $groupName . (isset($distributor) && 1 == $distributor ? $item['user_group_name_sufix'] : '');?>
        </div>

		<?php if($item['type_company'] === 'branch' && !empty($item['main_company'])){?>
			<div class="text-nowrap">
				<?php $parent_company_link = getCompanyURL($item['main_company']); ?>
				<?php echo translate('seller_card_branch_of_company', array('{{COMPANY_NAME}}' => '<a class="pl-5" href="' . $parent_company_link . '" ' . addQaUniqueIdentifier('global__item__company-card_branch') . ' title="' . cleanOutput($item['main_company']['name_company']) . '">' . cleanOutput($item['main_company']['name_company']) . '</a>'));?>
			</div>
        <?php }else{?>
            <div class="companies__date" <?php echo addQaUniqueIdentifier('global__item__company-card_member-from')?> title="<?php echo getDateFormat($item['registered_company'], 'Y-m-d H:i:s', 'M Y');?>"><?php echo translate('text_member_from_date', array('[[DATE]]' => getDateFormat($item['registered_company'], 'Y-m-d H:i:s', 'M Y')));?></div>
		<?php }?>

		<div class="companies__actions">
			<div class="companies__country">
                <img
                    class="image js-lazy"
                    width="24"
                    height="24"
                    <?php echo addQaUniqueIdentifier('global__item__company-card_country-flag')?>
                    src="<?php echo getLazyImage(24, 24); ?>"
                    data-src="<?php echo getCountryFlag($item['country']);?>" alt="<?php echo $item['country'];?>"
                    title="<?php echo $item['country'];?>"
                />
				<span class="text" <?php echo addQaUniqueIdentifier('global__item__company-card_country-name')?>><?php echo $item['country'];?></span>
			</div>

			<div class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Toggle company menu">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu dropdown-menu-right">
				<?php if (logged_in()) { ?>
					<?php if (in_session('company_saved', $item['id_company'])){?>
						<button type="button" class="dropdown-item call-function" data-callback="remove_company" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_favorited_tag_title', null, true);?>" data-company="<?php echo $item['id_company']; ?>" href="#">
							<i class="ep-icon ep-icon_favorite"></i>
							<span><?php echo translate('seller_home_page_sidebar_menu_dropdown_favorited');?></span>
						</button>
					<?php }else{?>
						<button type="button" class="dropdown-item call-function" data-callback="add_company" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite_tag_title', null, true);?>" data-company="<?php echo $item['id_company']; ?>" href="#">
							<i class="ep-icon ep-icon_favorite-empty"></i>
							<span><?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite');?></span>
						</button>
					<?php }?>

					<?php if(in_session('followed', $item['id_user'])){?>
						<button
                            class="dropdown-item call-function"
                            data-user="<?php echo $item['id_user'];?>"
                            data-callback="unfollow_user"
                            title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user', null, true);?>"
                            type="button"
                        >
							<i class="ep-icon ep-icon_reply-left-empty"></i>
							<span><?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user');?></span>
						</button>
					<?php } else{?>
						<button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true);?>"
                            data-fancybox-href="<?php echo __SITE_URL . 'followers/popup_followers/follow_user/' . $item['id_user'];?>"
                            title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true);?>"
                            type="button"
                        >
							<i class="ep-icon ep-icon_reply-right-empty"></i>
							<span><?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user');?></span>
						</button>
					<?php }?>
                <?php } else {?>
                    <button
                        class="dropdown-item js-require-logged-systmess"
                        title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite_tag_title', null, true);?>"
                        type="button"
                    >
						<i class="ep-icon ep-icon_favorite-empty"></i><?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite');?>
					</button>
					<button
                        class="dropdown-item js-require-logged-systmess"
                        title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true);?>"
                        type="button"
                    >
						<i class="ep-icon ep-icon_follow"></i>
						<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user');?>
					</button>
                <?php }?>

                <button
                    class="dropdown-item call-function call-action"
                    title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_email_company_tag_title', null, true);?>"
                    data-callback="userSharePopup"
                    data-js-action="user:share-popup"
                    data-type="company"
                    data-item="<?php echo $item['id_company'];?>"
                    type="button"
                >
                    <i class="ep-icon ep-icon_share-stroke3"></i> <?php echo translate('seller_home_page_sidebar_menu_dropdown_share_company');?>
                </button>

                <?php if (logged_in()) { ?>
					<?php echo !empty($item['btnChat']) ? $item['btnChat'] : ''; ?>

					<a class="dropdown-item" href="<?php echo $company_link . '/feedbacks';?>">
						<i class="ep-icon ep-icon_star-empty "></i>
						<?php echo translate('seller_home_page_sidebar_menu_dropdown_rate_seller');?>
					</a>

					<a class="dropdown-item" href="<?php echo $company_link . '/products';?>">
						<i class="ep-icon ep-icon_box-in"></i>
						<?php echo translate('seller_home_page_sidebar_menu_products_items');?>
					</a>
				<?php } else {?>
                    <button
                        class="dropdown-item js-require-logged-systmess"
                        title="<?php echo translate('chat_button_generic_title', null, true); ?>"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_comment-stroke"></i>
                        <?php echo translate('chat_button_generic_text', null, true); ?>
                    </button>
					<button
                        class="dropdown-item js-require-logged-systmess"
                        title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_rate_seller', null, true);?>"
                        type="button"
                    >
						<i class="ep-icon ep-icon_star-empty"></i>
						<?php echo translate('seller_home_page_sidebar_menu_dropdown_rate_seller');?>
					</button>
					<a class="dropdown-item" href="<?php echo $company_link . '/products';?>">
						<i class="ep-icon ep-icon_item "></i>
						<?php echo translate('seller_home_page_sidebar_menu_products_items');?>
					</a>
				<?php } ?>
				</div>
			</div>

		</div>

		<div class="display-n">
			<div itemprop="description">
				<?php echo strip_tags(truncWords($item['description_company'])); ?>
			</div>

			<?php if($item['rating_count_company'] > 0){ ?>
			<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/aggregateRating">
				<span itemprop="ratingValue"><?php echo $item['rating_company'];?></span>
				<span itemprop="reviewCount"><?php echo $item['rating_count_company']?></span>
			</div>
			<?php } ?>

			<div itemprop="member" itemscope itemtype="http://schema.org/OrganizationRole">
				<div itemprop="member" itemscope itemtype="http://schema.org/Person">
					<span itemprop="name"><?php echo $item['user_name']; ?></span>
				</div>
				<span itemprop="roleName"><?php echo translate('seller_home_page_seller_role');?></span>
			</div>

			<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
				<span itemprop="addressLocality">
					<?php echo $item['country']?>
					<?php if(!empty($item['state'])){ echo ', '.$item['state']; }?>
					<?php if(!empty($item['city'])){ echo ', '.$item['city']; }?>
				</span>
				<span itemprop="streetAddress"><?php echo $item['address_company'];?></span>
				<span itemprop="postalCode"><?php echo $item['zip_company'];?></span>
			</div>
		</div>

	</div>
</div>

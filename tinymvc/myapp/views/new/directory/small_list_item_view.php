<?php
$company_link = getCompanyURL($item);
$stars_gray = intval(5 - $item['rating_company']);
?>
<div class="company-block">
    <div class="company-block__image">
        <a class="link" href="<?php echo $company_link;?>">
            <img
                itemprop="logo"
                src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_company'], '{FILE_NAME}' => $item['logo_company']), 'companies.main', array( 'thumb_size' => 1 )); ?>"
                alt="<?php echo $item['name_company'];?>">
        </a>
    </div>
    <div class="company-block__info">
        <a href="<?php echo $company_link;?>" itemprop="url" class="company-block__name">
            <?php echo $item['name_company'];?>
        </a>
        <div class="fs-14 <?php echo userGroupNameColor($item['user_group_name']);?>">
            <?php echo $item['user_group_name'];?>
        </div>

        <?php if($item['type_company'] != 'branch'){?>
			<div class="company-block__date" title="<?php echo formatDate($item['registered_company'], 'M Y');?>"><?php echo translate('text_member_from_date', array('[[DATE]]' => getDateFormat($item['registered_company'], 'Y-m-d H:i:s', 'M Y')));?></div>
		<?php }?>

        <div class="company-block__options-block">
            <div class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                    <i class="ep-icon ep-icon_menu-circles"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <?php if (logged_in()) { ?>
                        <?php if (in_session('company_saved', $item['id_company'])){?>
                        <button
                            class="dropdown-item call-function"
                            data-callback="remove_company"
                            data-title="Remove from Favorites"
                            data-company="<?php echo $item['id_company']; ?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_favorite"></i>
                            <span>Favorited</span>
                        </button>
                        <?php }else{?>
                        <button
                            class="dropdown-item call-function"
                            data-callback="add_company"
                            data-title="Add to Favorites"
                            data-company="<?php echo $item['id_company']; ?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_favorite-empty"></i>
                            <span>Favorite</span>
                        </button>
                        <?php }?>

                        <?php if(in_session('followed', $item['id_user'])){?>
                        <button
                            class="dropdown-item call-function"
                            data-user="<?php echo $item['id_user'];?>"
                            data-callback="unfollow_user"
                            title="Unfollow user"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_reply-left-empty"></i>
                            <span>Unfollow user</span>
                        </button>
                        <?php } else{?>
                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="Follow user"
                            data-fancybox-href="followers/popup_followers/follow_user/<?php echo $item['id_user'];?>"
                            title="Follow user"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_reply-right-empty"></i>
                            <span>Follow user</span>
                        </button>
                        <?php }?>
                    <?php } else {?>
                        <button
                            class="dropdown-item call-systmess"
                            data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                            data-type="error"
                            title="Add to Favorite"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_favorite-empty"></i> Favorite
                        </button>
                        <button
                            class="dropdown-item call-systmess"
                            data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                            data-type="error"
                            title="Follow this"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_follow"></i> Follow user
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

                        <a class="dropdown-item" href="<?php echo $company_link; ?>/feedbacks">
                            <i class="ep-icon ep-icon_star-empty "></i>
                            Rate this seller
                        </a>

                        <a class="dropdown-item" href="<?php echo $company_link; ?>/products">
                            <i class="ep-icon ep-icon_box-in"></i> See products
                        </a>
                    <?php } else {?>
                        <button
                            class="dropdown-item call-systmess"
                            data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                            data-type="error"
                            title="Chat with seller"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_comment-stroke"></i> Chat with seller
                        </button>
                        <button
                            class="dropdown-item call-systmess"
                            data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                            data-type="error"
                            title="Contact seller"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope"></i> Contact seller
                        </button>
                        <button
                            class="dropdown-item call-systmess"
                            data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                            data-type="error"
                            title="Rate this seller"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_star-empty"></i> Rate this seller
                        </button>
                        <a class="dropdown-item" href="<?php echo $company_link; ?>/products">
                            <i class="ep-icon ep-icon_item "></i> See other products
                        </a>
                    <?php } ?>
                </div>
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
            <span itemprop="roleName">Seller</span>
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

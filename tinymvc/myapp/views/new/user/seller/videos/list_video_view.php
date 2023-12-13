<?php if(!empty($videos)){ ?>
    <ul class="spersonal-pictures">
        <?php foreach($videos as $item){ ?>
            <li class="spersonal-pictures__item" id="video-<?php echo $item['id_video'];?>-block" <?php echo addQaUniqueIdentifier('page__seller-videos__item'); ?>>
                <div class="spersonal-pictures__wr">
                    <div class="spersonal-pictures__img image-card">
                        <a class="link fancybox.iframe fancyboxVideo" rel="videoItem" href="<?php echo get_video_link($item['short_url_video'], $item['source_video']);?>" data-h="350" data-title="<?php echo $item['title_video'];?>">
                            <div class="video-play">
                                <div class="video-play__circle"></div>
                                <i class="ep-icon ep-icon_videos"></i>
                            </div>
                            <img
                                class="image"
                                src="<?php echo $item['imageThumbLink'];?>"
                                alt="<?php echo $item['title_video'];?>"
                                <?php echo addQaUniqueIdentifier('page__seller-videos__item-image'); ?>
                            >
                        </a>
                    </div>

                    <div class="spersonal-pictures__desc">
                        <div class="spersonal-pictures__top">
                            <h4 class="spersonal-pictures__ttl fn" <?php echo addQaUniqueIdentifier('page__seller-videos__item-title'); ?>>
                                <a class="link" href="<?php echo $base_company_url . '/video/' . strForUrl($item['title_video']) . '-' . $item['id_video'];?>"><?php echo $item['title_video'];?></a>
                            </h4>

                            <div class="dropdown">
                                <a
                                    class="dropdown-toggle"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    href="#"
                                    <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-btn'); ?>
                                >
                                    <i class="ep-icon ep-icon_menu-circles"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <?php if(logged_in()){ ?>

                                        <?php if (have_right('share_this')) { ?>
                                            <button
                                                class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                                data-fancybox-href="<?php echo __SITE_URL . "seller_videos/popup_forms/share/{$item['id_video']}";?>"
                                                data-title="<?php echo translate('seller_videos_email_text', null, true); ?>"
                                                title="<?php echo translate('seller_videos_share_text', null, true); ?>"
                                                <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_share-btn'); ?>
                                            >
                                                <i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('general_button_share_text'); ?>
                                            </button>
                                        <?php } ?>

                                        <?php if (have_right('email_this')) { ?>
                                            <button
                                                class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                                data-fancybox-href="<?php echo __SITE_URL . "seller_videos/popup_forms/email/{$item['id_video']}";?>"
                                                data-title="<?php echo translate('seller_videos_email_text', null, true); ?>"
                                                title="<?php echo translate('seller_videos_email_text', null, true); ?>"
                                                <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_email-btn'); ?>
                                            >
                                                <i class="ep-icon ep-icon_envelope-send"></i> <?php echo translate('general_button_mail_text'); ?>
                                            </button>
                                        <?php } ?>

                                        <?php if(is_privileged('company', $item['id_company'], 'have_videos')){ ?>
                                            <?php if(!$item['moderated']){?>
                                                <button
                                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                    data-title="<?php echo translate('seller_videos_edit_text', null, true); ?>"
                                                    title="<?php echo translate('seller_videos_edit_text', null, true); ?>"
                                                    data-fancybox-href="<?php echo __SITE_URL . "seller_videos/popup_forms/edit_video/{$item['id_video']}"; ?>"
                                                    <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_edit-btn'); ?>
                                                >
                                                    <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('general_button_edit_text'); ?>
                                                </button>
                                                <button
                                                    class="dropdown-item confirm-dialog"
                                                    data-callback="delete_video_seller"
                                                    data-video="<?php echo $item['id_video'];?>"
                                                    data-message="<?php echo translate('seller_videos_dashboard_dt_button_delete_video_message', null, true);?>"
                                                    title="<?php echo translate('general_button_delete_text', null, true);?>"
                                                    <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_delete-btn'); ?>
                                                >
                                                    <i class="ep-icon ep-icon_trash-stroke"></i>
                                                    <?php echo translate('general_button_delete_text', null, true);?>
                                                </button>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if(!is_privileged('company', $item['id_company'])){ ?>
                                            <button
                                                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                data-fancybox-href="<?php echo __SITE_URL . "complains/popup_forms/add_complain/company_videos/{$item['id_video']}/{$item['id_seller']}/{$item['id_company']}"; ?>"
                                                data-title="<?php echo translate('seller_videos_report_video_message', null, true); ?>"
                                                title="<?php echo translate('seller_videos_report_video_message', null, true); ?>"
                                                <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_report-btn'); ?>
                                            >
                                                <i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('general_button_report_text'); ?>
                                            </button>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button
                                            class="dropdown-item call-systmess"
                                            data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true); ?>"
                                            data-type="error"
                                            title="<?php echo translate('general_button_share_text', null, true); ?>"
                                            type="button"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_share-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('general_button_share_text'); ?>
                                        </button>

                                        <button
                                            class="dropdown-item call-systmess"
                                            data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true); ?>"
                                            data-type="error"
                                            title="<?php echo translate('seller_news_email_word', null, true); ?>"
                                            type="button"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_email-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_envelope-send"></i> <?php echo translate('general_button_mail_text'); ?>
                                        </button>

                                        <button
                                            class="dropdown-item call-systmess"
                                            data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true); ?>"
                                            data-type="error"
                                            title="<?php echo translate('general_button_report_text', null, true); ?>"
                                            type="button"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__item_dropdown-menu_report-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('general_button_report_text'); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="spersonal-pictures__bottom">
                            <div class="spersonal-pictures__category" <?php echo addQaUniqueIdentifier('page__seller-videos__item-category'); ?>>
                                <?php echo $item['category_title'];?>
                            </div>

                            <a class="spersonal-pictures__comment fancybox.ajax fancyboxValidateModal"
                                data-fancybox-href="<?php echo __SITE_URL . 'seller_videos/popup_forms/add_comment/' . $item['id_video'];?>"
                                data-title="<?php echo translate('general_button_add_comment_text', null, true); ?>"
                                title="<?php echo translate('general_button_add_comment_text', null, true); ?>">
                                <span <?php echo addQaUniqueIdentifier('page__seller-videos__item-comments-count'); ?>>
                                    <?php echo $item['comments_count'];?>
                                </span>
                                <i class="ep-icon ep-icon_comments-stroke"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
    <?php if ($count_videos > count($videos) && !isset($pagination)) {?>
		<div class="flex-display flex-jc--c">
			<a class="btn btn-outline-dark btn-block mw-280" href="<?php echo $more_videos_btn_link;?>"><?php echo translate('general_view_more_btn');?></a>
		</div>
	<?php }?>
<?php } else {?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_news_no_videos_text'); ?></div>
<?php } ?>

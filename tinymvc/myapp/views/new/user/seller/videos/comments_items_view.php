<?php foreach($comments as $item){?>
    <li class="spersonal-pic-comments__item" id="comment-<?php echo $item['id_comment'];?>" <?php echo addQaUniqueIdentifier('global__comment'); ?>>
        <div class="flex-card">
            <div class="spersonal-pic-comments__img image-card2 flex-card__fixed">
                <span class="link">
                    <img
                        class="image"
                        src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_user'], '{FILE_NAME}' => $item['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $item['user_group'] ));?>"
                        alt="<?php echo $item['username'];?>"
                        <?php echo addQaUniqueIdentifier('global__comment-image'); ?>
                    />
                </span>
            </div>

            <div class="spersonal-pic-comments__detail flex-card__float">
                <div class="spersonal-pic-comments__name">
                    <a
                        class="link"
                        href="<?php echo __SITE_URL . 'usr/' . strForUrl($item['username']) . '-' . $item['id_user'];?>"
                        target="_blank"
                        <?php echo addQaUniqueIdentifier('global__comment-name'); ?>
                    >
                        <?php echo $item['username'];?>
                    </a>
                </div>

                <div class="spersonal-pic-comments__text" <?php echo addQaUniqueIdentifier('global__comment-text'); ?>>
                    <?php if(!$item['censored']) echo $item['message_comment']; else echo translate('general_censored_word');?>
                </div>

                <div class="spersonal-pic-comments__bottom">
                    <div class="spersonal-pic-comments__date" <?php echo addQaUniqueIdentifier('global__comment-date'); ?>>
                        <?php echo formatDate($item['date_comment']);?>
                    </div>

                    <?php if (logged_in() && (!is_privileged('user', $item['id_user']) || !$item['moderated'] || (have_right('moderate_content') && !$item['censored']))) {?>
                        <div class="dropdown">
                            <a
                                class="dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                href="#"
                                <?php echo addQaUniqueIdentifier('page__seller-videos__comment_dropdown-btn'); ?>
                            >
                                <i class="ep-icon ep-icon_menu-circles"></i>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <?php if(!is_privileged('user', $item['id_user'])){ ?>
                                    <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                        data-fancybox-href="<?php echo __SITE_URL . "complains/popup_forms/add_complain/seller_video_comment/{$item['id_comment']}/{$item['id_user']}"; ?>"
                                        data-title="<?php echo translate('seller_videos_report_this_comment_message', null, true); ?>"
                                        title="<?php echo translate('seller_videos_report_comment_message', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('page__seller-videos__comment_dropdown-menu_report-btn'); ?>
                                    >
                                        <i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('general_button_report_text'); ?>
                                    </a>
                                <?php } ?>

                                <?php if(!$item['moderated']) { ?>
                                    <?php if ((is_my($item['id_user']) || in_session('my_seller', $item['id_user']) && have_right('have_videos')) && !$item['censored']) { ?>
                                        <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                            data-fancybox-href="<?php echo __SITE_URL;?>seller_videos/popup_forms/edit_comment/<?php echo $item['id_comment'];?>"
                                            data-comment="<?php echo $item['id_comment'];?>"
                                            data-title="<?php echo translate('general_button_edit_comment_text', null, true); ?>"
                                            title="<?php echo translate('general_button_edit_comment_text', null, true); ?>"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__comment_dropdown-menu_edit-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('general_button_edit_text'); ?>
                                        </a>
                                    <?php } ?>
                                    <?php if (have_right('moderate_content') && !$item['censored']) { ?>
                                        <a class="dropdown-item confirm-dialog"
                                            data-callback="moderate_comment"
                                            data-message="<?php echo translate('seller_videos_moderate_comment_question', null, true); ?>"
                                            data-comment="<?php echo $item['id_comment']; ?>"
                                            title="<?php echo translate('seller_videos_moderate_comment_text', null, true); ?>"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__comment_dropdown-menu_moderate-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_sheild-ok"></i> <?php echo translate('general_button_moderate_text'); ?>
                                        </a>
                                        <a class="dropdown-item confirm-dialog"
                                            data-callback="censored_comment"
                                            data-message="<?php echo translate('seller_videos_censored_comment_question', null, true); ?>"
                                            data-comment="<?php echo $item['id_comment']; ?>"
                                            title="<?php echo translate('seller_videos_censore_comment_text', null, true); ?>"
                                            <?php echo addQaUniqueIdentifier('page__seller-videos__comment_dropdown-menu_censor-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_remove-stroke"></i> <?php echo translate('general_button_censor_text'); ?>
                                        </a>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </li>
<?php } ?>

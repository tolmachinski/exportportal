<?php foreach($comments as $item){?>
    <li class="spersonal-pic-comments__item" id="comment-<?php echo $item['id_comment'];?>">
        <div class="flex-card" <?php echo addQaUniqueIdentifier('global__comment'); ?>>
            <div class="spersonal-pic-comments__img image-card2 flex-card__fixed">
                <span class="link">
                    <img class="image" <?php echo addQaUniqueIdentifier('global__comment-image'); ?> src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_user'], '{FILE_NAME}' => $item['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $item['user_group'] ));?>" alt="<?php echo $item['username'];?>"/>
                </span>
            </div>

            <div class="spersonal-pic-comments__detail flex-card__float">
                <div class="spersonal-pic-comments__name">
                    <a class="link" <?php echo addQaUniqueIdentifier('global__comment-name'); ?> href="<?php echo __SITE_URL . 'usr/' . strForUrl($item['username']) . '-' . $item['id_user'];?>" target="_blank"><?php echo $item['username'];?></a>
                </div>

                <div class="spersonal-pic-comments__text" <?php echo addQaUniqueIdentifier('global__comment-text'); ?>><?php echo $item['censored'] ? 'Censored' : $item['message_comment'];?></div>

                <div class="spersonal-pic-comments__bottom">
                    <div class="spersonal-pic-comments__date" <?php echo addQaUniqueIdentifier('global__comment-date'); ?>><?php echo formatDate($item['date_comment']);?></div>

                    <?php if (logged_in()) {?>
                        <?php
                            $can_moderate_word = $can_censor_word = !$item['moderated'] && have_right('moderate_content') && !$item['censored'];
                            $can_edit = !$item['moderated'] && have_right('write_comments') && !$item['censored'] && (is_my($item['id_user']) || in_session('my_seller', $item['id_user']));
                        ?>
                        <?php if ($can_moderate_word || $can_censor_word || $can_edit) {?>
                            <div class="dropdown">
                                <a class="dropdown-toggle" <?php echo addQaUniqueIdentifier('page__company-pictures__comment_dropdown-btn'); ?>  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="ep-icon ep-icon_menu-circles"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <?php if ($can_edit) {?>
                                        <a class="dropdown-item fancybox.ajax fancyboxValidateModal" <?php echo addQaUniqueIdentifier('page__company-pictures__comment_dropdown-menu_edit-btn'); ?>  data-title="<?php echo translate('seller_pictures_edit_comment_text', null, true);?>" data-comment="<?php echo $item['id_comment'];?>" href="<?php echo __SITE_URL . 'seller_pictures/popup_forms/edit_comment/' . $item['id_comment'];?>"><i class="ep-icon ep-icon_pencil"></i> <?php echo translate('seller_pictures_edit_word');?></a>
                                    <?php }?>
                                    <?php if ($can_moderate_word) {?>
                                        <a class="dropdown-item confirm-dialog" <?php echo addQaUniqueIdentifier('page__company-pictures__comment_dropdown-menu_moderate-btn'); ?> data-callback="moderate_comment" data-message="<?php echo translate('seller_pictures_moderate_comment_question', null, true);?>" data-comment="<?php echo $item['id_comment'];?>" href="#"><i class="ep-icon ep-icon_sheild-ok"></i> <?php echo translate('seller_pictures_moderate_word');?></a>
                                    <?php }?>
                                    <?php if ($can_censor_word) {?>
                                        <a class="dropdown-item confirm-dialog" <?php echo addQaUniqueIdentifier('page__company-pictures__comment_dropdown-menu_censor-btn'); ?> data-callback="censored_comment" data-message="<?php echo translate('seller_pictures_censore_comment_question', null, true);?>" data-comment="<?php echo $item['id_comment'];?>" href="#"><i class="ep-icon ep-icon_remove-stroke"></i> <?php echo translate('seller_pictures_censor_word');?></a>
                                    <?php }?>
                                </div>
                            </div>
                        <?php }?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </li>
<?php } ?>

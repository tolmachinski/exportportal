<div class="flex-card" <?php echo addQaUniqueIdentifier("global__comment")?>>
    <div class="product-comments__img flex-card__fixed image-card2">
        <span class="link">
            <img
                class="image js-lazy"
                src="<?php echo getLazyImage(75, 75);?>"
                data-src="<?php echo getDisplayImageLink(['{ID}' => $comment_item['id_user'], '{FILE_NAME}' => $comment_item['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $comment_item['idgroup']]); ?>"
                alt="<?php echo $comment_item['username']; ?>"
                <?php echo addQaUniqueIdentifier('global__comment-image'); ?>
            />
        </span>
    </div>
    <div class="product-comments__detail flex-card__float">
        <div class="product-comments__detail-top">
            <div class="product-comments__ttl">
                <div class="product-comments__name">
                    <a class="link" href="<?php echo __SITE_URL . 'usr/' . strForURL($comment_item['username']) . '-' . $comment_item['id_user']; ?>" <?php echo addQaUniqueIdentifier("global__comment-name")?>><?php echo $comment_item['username']; ?></a>
                </div>

                <?php
                    if (logged_in()) {
                        $is_my_comment = is_my($comment_item['id_user']);
                        $is_not_moderated = 'moderated' != $comment_item['status'];
                        $can_moderate = have_right('moderate_content');
                        $can_change = empty($comment_item['replies']) || $can_moderate;
                        $can_reply = (have_right('write_comments_on_item') || have_right('manage_seller_item_comments') && $is_my_item);
                    }
                ?>

                <?php if (logged_in() && ($can_moderate || $can_reply || $is_my_comment)) { ?>
                    <div class="dropdown flex-as--fe">
                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier("items-comments__reply__open-dropdown") ?>>
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                            <?php if ($can_moderate) { ?>
                                <a class="dropdown-item <?php echo $is_not_moderated ? 'confirm-dialog' : 'disabled call-systmess'; ?>"
                                    <?php if ($is_not_moderated) { ?>
                                        data-callback="moderateComment"
                                        data-message="Are you sure want moderate this comment?"
                                        data-comment="<?php echo $comment_item['id_comm']; ?>"
                                    <?php } else { ?>
                                        data-type="info"
                                        data-message="Comment was already moderated."
                                    <?php } ?>
                                    title="Moderate comment">
                                    <i class="ep-icon ep-icon_sheild-ok"></i> Moderate
                                </a>
                            <?php } ?>

                            <?php if ($can_reply) { ?>
                                <a
                                    <?php if (!($is_dialog ?? false)) { ?>
                                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                        data-fancybox-href="<?php echo getUrlForGroup("/items_comments/popup_forms/add_reply/{$comment_item['id_comm']}"); ?>"
                                    <?php } else { ?>
                                        class="dropdown-item popup-dialog"
                                        data-href="<?php echo getUrlForGroup("/items_comments/popup_forms/add_reply/{$comment_item['id_comm']}?dialog=1"); ?>"
                                    <?php } ?>
                                    data-title="Add reply"
                                    title="Add reply"
                                    <?php echo addQaUniqueIdentifier("items-comments__reply") ?>>
                                    <i class="ep-icon ep-icon_reply-left-empty"></i> Reply
                                </a>
                            <?php } ?>

                            <!-- <?php if (!$is_my_comment && !$can_moderate) { ?>
                                <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-fancybox-href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/item_comment/' . $comment_item['id_item'] . '/' . $comment_item['id_user']; ?>"
                                    data-title="Report this comment"
                                    title="Report this">
                                    <i class="ep-icon ep-icon_warning-circle-stroke"></i> Report this
                                </a>
                            <?php } ?> -->

                            <?php if ($is_my_comment || $can_moderate) { ?>
                                <div class="dropdown-divider"></div>
                            <?php } ?>

                            <?php if ($is_my_comment || $can_moderate) { ?>
                                <a class="dropdown-item <?php echo $can_change ? 'fancybox.ajax fancyboxValidateModal' : 'disabled call-systmess'; ?>"
                                    <?php if ($can_change) { ?>
                                        <?php if (!($is_dialog ?? false)) { ?>
                                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                            data-fancybox-href="<?php echo getUrlForGroup("/items_comments/popup_forms/edit_main_comment/{$comment_item['id_comm']}"); ?>"
                                        <?php } else { ?>
                                            class="dropdown-item popup-dialog"
                                            data-href="<?php echo getUrlForGroup("/items_comments/popup_forms/edit_main_comment/{$comment_item['id_comm']}?dialog=1"); ?>"
                                        <?php } ?>
                                        data-title="Edit comment"
                                    <?php } else { ?>
                                        data-type="info"
                                        data-message="The comment cannot be edited if it has already a reply."
                                    <?php } ?>
                                    title="Edit comment">
                                    <i class="ep-icon ep-icon_pencil"></i> Edit
                                </a>
                            <?php } ?>

                            <!-- <?php if ($is_my_comment || $can_moderate) { ?>
                                <?php $need_disable_delete_btn = !$can_moderate && !$can_change; ?>
                                <a class="dropdown-item <?php echo $need_disable_delete_btn ? 'disabled call-systmess' : 'confirm-dialog'; ?>"
                                    <?php if ($need_disable_delete_btn) { ?>
                                        data-type="info"
                                        data-message="The comment cannot be deleted if it has already a reply."
                                    <?php } else { ?>
                                        data-message="Are you sure want delete this comment?"
                                        data-callback="deleteComment"
                                        data-comment="<?php echo $comment_item['id_comm']; ?>"
                                        data-item="<?php echo $comment_item['id_item']; ?>"
                                    <?php } ?>
                                    title="Delete comment">
                                    <i class="ep-icon ep-icon_trash-stroke"></i> Delete
                                </a>
                            <?php } ?> -->
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="product-comments__date tal" <?php echo addQaUniqueIdentifier("global__comment-date")?>><?php echo getDateFormat($comment_item['comment_date']); ?></div>
        </div>

        <div class="product-comments__text" <?php echo addQaUniqueIdentifier("global__comment-text")?>><?php echo cleanOutput($comment_item['comment']); ?></div>
    </div>
</div>

<?php if (!empty($comments)) { ?>
    <?php $is_my_item = is_privileged('user', $item['id_seller']); ?>
    <?php foreach ($comments as $comment) { ?>
        <li class="product-comments__item <?php echo !empty($more) ? 'product-comments--hide js-product-comments-hide' : 'js-product-comments-item' ?>" id="li-comment-<?php echo $comment['id_comm']; ?>">
            <div class="flex-card" <?php echo addQaUniqueIdentifier("global__comment")?>>
                <div class="product-comments__img flex-card__fixed image-card2">
                    <span class="link">
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(56, 56);?>"
                            data-src="<?php echo getDisplayImageLink(['{ID}' => $comment['id_user'], '{FILE_NAME}' => $comment['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $comment['idgroup']]); ?>"
                            alt="<?php echo $comment['username']; ?>"
                            <?php echo addQaUniqueIdentifier('global__comment-image'); ?>
                        />
                    </span>
                </div>
                <div class="product-comments__detail flex-card__float">
                    <div class="product-comments__detail-top">
                        <div class="product-comments__ttl">
                            <div class="product-comments__name">
                                <a class="link" href="<?php echo __SITE_URL . 'usr/' . strForURL($comment['username']) . '-' . $comment['id_user']; ?>"  <?php echo addQaUniqueIdentifier("global__comment-name")?>>
                                    <?php echo $comment['username']; ?>
                                </a>
                            </div>
                            <?php
                                if (logged_in()) {
                                    $is_my_reply = is_my($comment['id_user']);
                                    $is_not_moderated = 'moderated' != $comment['status'];
                                    $can_moderate = have_right('moderate_content');
                                    $can_change = empty($comment['replies']) || $can_moderate;
                                    $can_reply = (have_right('write_comments_on_item') || have_right('manage_seller_item_comments') && $is_my_item) && $comment['level'] < 9;
                                }
                            ?>
                            <?php if (logged_in() && ($is_my_reply || $can_moderate || $can_reply)) { ?>
                                <div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <?php if ($can_moderate) { ?>
                                            <a class="dropdown-item <?php echo $is_not_moderated ? 'confirm-dialog' : 'disabled call-systmess'; ?>"
                                                <?php if ($is_not_moderated) { ?>
                                                    data-callback="moderateComment"
                                                    data-message="Are you sure want moderate this comment?"
                                                    data-comment="<?php echo $comment['id_comm']; ?>"
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
                                                    data-fancybox-href="<?php echo getUrlForGroup("/items_comments/popup_forms/add_reply/{$comment['id_comm']}"); ?>"
                                                <?php } else { ?>
                                                    class="dropdown-item popup-dialog"
                                                    data-href="<?php echo getUrlForGroup("/items_comments/popup_forms/add_reply/{$comment['id_comm']}?dialog=1"); ?>"
                                                <?php } ?>
                                                data-title="Add reply"
                                                title="Add reply">
                                                <i class="ep-icon ep-icon_reply-left-empty"></i> Reply
                                            </a>
                                        <?php } ?>

                                        <!-- <?php if (!$is_my_reply && !$can_moderate) { ?>
                                            <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                data-fancybox-href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/item_comment/' . $comment['id_item'] . '/' . $comment['id_user']; ?>"
                                                data-title="Report this comment"
                                                title="Report this">
                                                <i class="ep-icon ep-icon_warning-circle-stroke"></i> Report this
                                            </a>
                                        <?php } ?> -->

                                        <?php if ($is_my_reply || $can_moderate) { ?>
                                            <div class="dropdown-divider"></div>
                                        <?php } ?>

                                        <?php if ($is_my_reply || $can_moderate) { ?>
                                            <a
                                                <?php if ($can_change) { ?>
                                                    <?php if (!($is_dialog ?? false)) { ?>
                                                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                        data-fancybox-href="<?php echo getUrlForGroup("/items_comments/popup_forms/edit_reply/{$comment['id_comm']}"); ?>"
                                                    <?php } else { ?>
                                                        class="dropdown-item popup-dialog"
                                                        data-href="<?php echo getUrlForGroup("/items_comments/popup_forms/edit_reply/{$comment['id_comm']}?dialog=1"); ?>"
                                                    <?php } ?>
                                                    data-title="Edit comment"
                                                <?php } else { ?>
                                                    class="dropdown-item disabled call-systmess"
                                                    data-type="info"
                                                    data-message="The comment cannot be edited if it has already a reply."
                                                <?php } ?>
                                                title="Edit comment">
                                                <i class="ep-icon ep-icon_pencil"></i> Edit
                                            </a>
                                        <?php } ?>

                                        <!-- <?php if ($is_my_reply || $can_moderate) { ?>
                                            <?php $need_disable_delete_btn = !$can_moderate && !empty($comment['replies']); ?>
                                            <a class="dropdown-item <?php echo $need_disable_delete_btn ? 'disabled call-systmess' : 'confirm-dialog'; ?>"
                                                <?php if (!$need_disable_delete_btn) { ?>
                                                    data-message="Are you sure want delete this comment?"
                                                    data-callback="deleteComment"
                                                    data-comment="<?php echo $comment['id_comm']; ?>"
                                                    data-item="<?php echo $comment['id_item']; ?>"
                                                <?php } else { ?>
                                                    data-type="info"
                                                    data-message="The comment cannot be deleted if it has already a reply."
                                                <?php } ?>
                                                title="Delete comment">
                                                <i class="ep-icon ep-icon_trash-stroke"></i> Delete
                                            </a>
                                        <?php } ?> -->
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="product-comments__date tal" <?php echo addQaUniqueIdentifier("global__comment-date")?>><?php echo formatDate($comment['comment_date'], 'M d, Y'); ?></div>
                    </div>

                    <div class="product-comments__text" <?php echo addQaUniqueIdentifier("global__comment-text")?>><?php echo cleanOutput($comment['comment']); ?></div>
                </div>
            </div>

            <?php if (!empty($comment['replies'])) { ?>
                <ul class="product-comments product-comments--replies js-product-comments-replies" id="comments-<?php echo $comment['id_comm']; ?>-block">
                    <?php views()->display('new/items_comments/item_reply_view', array('comments' => $comment['replies'])); ?>
                </ul>
            <?php } ?>
        </li>
    <?php } ?>
<?php } ?>

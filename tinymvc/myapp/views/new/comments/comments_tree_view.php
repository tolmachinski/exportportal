<?php if ($comments->isEmpty()) { ?>
    <div class="js-common-comments-empty common-comments__empty default-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('comments_tree_item_not_found'); ?></span>
    </div>
<?php } else { ?>
    <?php $need_include_delete_comment_script = false; ?>

    <?php foreach ($comments as $comment) {?>
        <?php
            $have_right_to_delete_comment = $have_right_to_edit_comment = logged_in() && !empty($comment['author']['id_user']) && is_my($comment['author']['id_user']);
            $need_include_delete_comment_script = $need_include_delete_comment_script || $have_right_to_delete_comment;
        ?>

        <div
            class="js-common-comments-row common-comments__item"
            data-comment-row="<?php echo $comment['id']; ?>"
            <?php echo addQaUniqueIdentifier('global__common-comments_item'); ?>
        >
            <div class="common-comments__container">
                <div class="common-comments__photo">
                    <img
                        class="common-comments__photo-image"
                        <?php echo addQaUniqueIdentifier('global__common-comments_item_image'); ?>
                        src="<?php echo $comment['author']['photo']; ?>"
                        alt="<?php echo $comment['author']['name']; ?> photo"
                    >
                </div>
                <div class="common-comments__info">
                    <?php if (empty($comment['author']['id_user'])) {?>
                        <span
                            class="common-comments__name"
                            <?php echo addQaUniqueIdentifier('global__common-comments_item_user-name'); ?>
                        ><?php echo cleanOutput($comment['author']['name']); ?></span>
                        <span class="common-comments__user-type">(Guest)</span>
                    <?php } elseif ('Shipper' === $comment['author']['group_type']) {?>
                        <span
                            class="common-comments__name"
                            <?php echo addQaUniqueIdentifier('global__common-comments_item_user-name'); ?>
                        ><?php echo cleanOutput($comment['author']['name']); ?></span>
                    <?php } else {?>
                        <a
                            class="common-comments__name link-black"
                            href="<?php echo getUserLink($comment['author']['name'], $comment['author']['id_user'], $comment['author']['group_type']); ?>"
                            target="_blank"
                            <?php echo addQaUniqueIdentifier('global__common-comments_item_user-name'); ?>
                        ><?php echo cleanOutput($comment['author']['name']); ?></a>
                    <?php }?>

                    <div
                        class="common-comments__date"
                        <?php echo addQaUniqueIdentifier('global__common-comments_item_date'); ?>
                    ><?php echo getDateFormat($comment['published_at']); ?></div>

                    <?php if ($have_right_to_edit_comment || $have_right_to_delete_comment) {?>
                        <div class="common-comments__actions">
                            <div class="common-comments__actions-dropdown dropdown">
                                <button
                                    class="dropdown-toggle"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('global__common-comments_item_dropdown-btn'); ?>
                                >
                                    <i class="common-comments__actions-icon ep-icon ep-icon_menu-circles"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <?php if ($have_right_to_edit_comment) {?>
                                        <button
                                            class="dropdown-item fancybox.ajax js-fancybox-validate-modal"
                                            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'comments/popup_forms/edit/comment/' . $comment['id']; ?>"
                                            data-w="535px"
                                            data-title="<?php echo translate('comments_tree_item_button_edit_title'); ?>"
                                            title="<?php echo translate('comments_tree_item_button_edit_title'); ?>"
                                            type="button"
                                            <?php echo addQaUniqueIdentifier('global__common-comments_item_dropdown_edit-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('comments_tree_item_button_edit_text'); ?></span>
                                        </button>
                                    <?php }?>
                                    <?php if ($have_right_to_delete_comment) {?>
                                        <button
                                            class="dropdown-item js-confirm-dialog"
                                            data-js-action="comment:delete"
                                            data-message="<?php echo translate('comments_tree_item_button_delete_question'); ?>"
                                            data-title="<?php echo translate('comments_tree_item_button_delete_title'); ?>"
                                            data-comment="<?php echo $comment['id']; ?>"
                                            type="button"
                                            <?php echo addQaUniqueIdentifier('global__common-comments_dropdown_delete-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt"><?php echo translate('comments_tree_item_button_delete_text'); ?></span>
                                        </button>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                    <?php }?>
                </div>
            </div>

            <div
                class="js-common-comments-message common-comments__message"
                <?php echo addQaUniqueIdentifier('global__common-comments_item_text'); ?>
            ><?php echo cleanOutput($comment['text']); ?></div>
        </div>
    <?php }?>
<?php } ?>

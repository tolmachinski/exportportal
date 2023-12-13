<div class="spersonal-history__top-param">
    <div class="spersonal-history__top-date" <?php echo addQaUniqueIdentifier('seller__wall_item_date'); ?>><?php echo getDateFormat($wall_item['date']); ?></div>

    <?php if (isset($share_product) || isset($share_link) && isset($email_link)) { ?>
        <div class="dropdown">
            <a class="dropdown-toggle" <?php echo addQaUniqueIdentifier('seller__history_wall_dropdown_menu'); ?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <div class="dropdown-menu">
                <?php if (isset($share_product)) { ?>
                    <button
                        class="dropdown-item call-function call-action"
                        title="Share"
                        data-callback="userSharePopup"
                        data-js-action="user:share-popup"
                        data-type="item"
                        data-item="<?php echo $share_product;?>"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_share-stroke3"></i> Share this
                    </button>
                <?php } else { ?>
                    <?php if (isset($share_link)) { ?>
                        <a class="dropdown-item fancyboxValidateModal fancybox.ajax" <?php echo addQaUniqueIdentifier('seller__history_wall_dropdown_menu_share'); ?> href="<?php echo $share_link; ?>" data-title="<?php echo $share_title; ?>" title="<?php echo $share_title; ?>">
                            <i class="ep-icon ep-icon_share-stroke"></i> Share this
                        </a>
                    <?php } ?>
                    <?php if (isset($email_link)) { ?>
                        <a class="dropdown-item fancyboxValidateModal fancybox.ajax" <?php echo addQaUniqueIdentifier('seller__history_wall_dropdown_menu_email'); ?> href="<?php echo $email_link; ?>" data-title="<?php echo $email_title; ?>" title="<?php echo $email_title; ?>">
                            <i class="ep-icon ep-icon_envelope-send"></i> Email this
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

<div id="js-comments-desktop-wrapper">
    <div
        id="js-comments-wrapper"
        class="common-comments display-n"
    >
        <div class="common-comments__header">
            <h2 class="common-comments__title">
                <?php echo translate('comments_main_title'); ?>
                <span
                    id="js-count-comments"
                    class="common-comments__count"
                    <?php echo addQaUniqueIdentifier("global__common-comments_title_counter")?>
                ><?php echo $countComments; ?></span>
            </h2>
            <div class="common-comments__add">
                <button
                    class="common-comments__add-button btn fancybox.ajax js-fancybox-validate-modal"
                    data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . "comments/popup_forms/add/resource/{$resourceId}"; ?>"
                    data-title="<?php echo translate('comments_button_add_title'); ?>"
                    data-w="535px"
                    title="<?php echo translate('comments_button_add_title'); ?>"
                    type="button"
                    <?php echo addQaUniqueIdentifier("global__common-comments_title_button-add"); ?>
                >
                    <?php echo getEpIconSvg('plus-circle', [16, 16]) . translate('comments_button_add_title'); ?>
                </button>
            </div>
        </div>

        <div id="js-comments-list"></div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "comments:init",
        [
            ["resourceId" => (int) $resourceId]
        ],
        true
    );
?>

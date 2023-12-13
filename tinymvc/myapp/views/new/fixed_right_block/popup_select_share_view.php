<button id="btn-social-call"
    data-js-action="languages:open-social-modal"
    data-classes="mw-300"
    class="fixed-rigth-block__item fixed-rigth-block__item--main-social call-action"
    <?php echo addQaUniqueIdentifier('right-sidebar__popup-social'); ?>
    title="<?php echo translate('general_button_share_text');?>"
>
    <span class="fixed-rigth-block__item-icon"><i class="ep-icon ep-icon_share-stroke2"></i> <span>Share</span></span>
    <span class="fixed-rigth-block__item-text">
        <span class="fixed-rigth-block__item-text-inner">
            <?php echo translate('general_button_share_text');?>
        </span>
    </span>
</button>

<div id="share-social" style="display: none;">
    <?php views('new/share_on_socials_view', ['title' => $metaTitle, 'additionalClass' => 'pages-socials-list--size-50']);?>
</div>

<?php echo dispatchDynamicFragment('languages:call-social-modal', null, true); ?>

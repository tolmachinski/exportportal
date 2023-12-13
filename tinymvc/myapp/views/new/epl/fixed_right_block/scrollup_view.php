<button
    id="js-btn-scrollup"
    class="fixed-rigth-block__item fixed-rigth-block__item--scrollup call-action"
    type="button"
    data-js-action="scroll-up:toggle"
    title="<?php echo translate('header_navigation_link_up_title', null, true);?>"
    <?php echo addQaUniqueIdentifier('global__epl-scroll-up-btn'); ?>
>
    <span class="fixed-rigth-block__item-icon">
        <i class="ep-icon ep-icon_arrow-up"></i>
        <span class="fixed-rigth-block__item-text">
            <span class="fixed-rigth-block__item-text-inner">Up</span>
        </span>
    </span>
</button>

<?php echo dispatchDynamicFragment("epl-footer:scroll-up", null, true); ?>

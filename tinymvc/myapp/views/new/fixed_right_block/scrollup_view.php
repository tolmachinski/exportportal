<button
    id="js-btn-scrollup"
    class="fixed-rigth-block__item fixed-rigth-block__item--scrollup"
    title="<?php echo translate('header_navigation_link_up_title', null, true);?>"
    type="button"
>
    <span class="fixed-rigth-block__item-icon"><i class="ep-icon ep-icon_arrow-up"></i></span>
    <span class="fixed-rigth-block__item-text">
        <span class="fixed-rigth-block__item-text-inner">
            <?php echo translate('header_navigation_link_up_title');?>
        </span>
    </span>
</button>

<?php echo dispatchDynamicFragment("footer:scroll-up", null, true); ?>

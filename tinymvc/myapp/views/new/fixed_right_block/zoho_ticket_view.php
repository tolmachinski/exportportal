<button
    class="fixed-rigth-block__item fixed-rigth-block__item--zoho-ticket call-action"
    <?php echo addQaUniqueIdentifier('right-sidebar__popup-ticket'); ?>
    data-js-action="zoho-ticket:open"
    title="Open a modal window to report issue for support"
    type="button"
>
    <span class="fixed-rigth-block__item-icon"><i class="ep-icon ep-icon_ticket2"></i> <span>Add ticket</span></span>
</button>

<?php echo dispatchDynamicFragment('zoho-ticket:boot', null, true);?>

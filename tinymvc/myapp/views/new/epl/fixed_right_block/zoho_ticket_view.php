<button
    class="fixed-rigth-block__item fixed-rigth-block__item--zoho-ticket call-action"
    type="button"
    data-js-action="zoho-ticket:open"
    title="Open a modal window to report issue for support"
    <?php echo addQaUniqueIdentifier('global__epl-right-sidebar_popup-ticket'); ?>
>
    <span class="fixed-rigth-block__item-icon"><i class="ep-icon ep-icon_ticket2"></i> <span>Add ticket</span></span>
</button>

<?php echo dispatchDynamicFragment(
    "zoho-ticket:boot",
    null,
    true
); ?>

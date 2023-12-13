<button
    class="fixed-rigth-block__item fixed-rigth-block__item--main-chat js-btn-call-main-chat call-action"
    <?php echo addQaUniqueIdentifier('right-sidebar__popup-chat'); ?>
    title="<?php echo translate('header_navigation_link_chat_title', null, true);?>"
    data-js-action="zoho-chat:show"
    type="button"
>
	<span class="fixed-rigth-block__item-icon"><i class="ep-icon ep-icon_support5"></i><span>Support Chat</span></span>
</button>

<?php
    echo dispatchDynamicFragment(
        'zoho-chat:boot',
        [
            logged_in() ? config('env.ZOHO_WIDGET_LOGGED') : config('env.ZOHO_WIDGET_NOT_LOGGED'),
            config('env.ZOHO_WIDGET_DOMAIN'),
            logged_in() ? user_name_session() : '',
            logged_in() ? email_session() : '',
            'https://salesiq.zoho.com/widget',
        ],
        true
    );
?>

<button
    class="fixed-rigth-block__item fixed-rigth-block__item--main-chat js-btn-call-main-chat call-action"
    type="button"
    title="<?php echo translate('header_navigation_link_chat_title', null, true); ?>"
    data-js-action="zoho-chat:show"
    <?php echo addQaUniqueIdentifier('global__epl-right-sidebar_popup-chat-btn'); ?>
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
            logged_in() ? session()->email : '',
            "https://salesiq.zoho.com/widget"
        ],
        true
    );
?>

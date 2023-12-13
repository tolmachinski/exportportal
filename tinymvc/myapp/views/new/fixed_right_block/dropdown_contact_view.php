<button
    class="<?php echo (isset($mep_nav_chat))?'mep-header-bottom-nav__link mep-header-bottom-nav__link--chat':'mep-header-user__menu-item mep-header-user__menu-item--chat'; ?> js-btn-call-main-chat call-action"
    data-js-action="zoho-chat:show"
    title="<?php echo translate('header_navigation_link_chat_title', null, true);?>"
    type="button"
>
    <?php echo widgetGetSvgIcon('support-chat', 22, 20, 'js-svg-icon-chat');?>
    <?php echo widgetGetSvgIcon('updates', 24, 24, 'js-svg-icon-updates display-n');?>
</button>

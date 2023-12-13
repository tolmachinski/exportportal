<?php
	$url = __SITE_URL . 'register/ref/' . $id_user;
    $class = logged_in() ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';
?>

<div class="friend-invite">
    <label class="input-label mt-5">Who are you sending the invite to?</label>
    <label class="friend-invite__radio custom-radio">
        <input
                type="radio"
                name="group_radio"
                class="js-radio-blue"
                value="currentClient"
                checked="checked" >
        <span class="friend-invite__message-type txt-black-light custom-radio__text">Current Client</span>
    </label>
    <label class="friend-invite__radio custom-radio">
        <input
                type="radio"
                name="group_radio"
                class="js-radio-blue"
                value="friend">
        <span class="friend-invite__message-type txt-black-light custom-radio__text">Friend/Peer</span>
    </label>
    <label class="friend-invite__radio custom-radio">
        <input
                type="radio"
                name="group_radio"
                class="js-radio-blue"
                value="potentialClient">
        <span class="friend-invite__message-type txt-black-light custom-radio__text">Potential Client</span>
    </label>

    <label class="input-label lh-22">How do you want to send your invite?</label>
    <div class="pages-socials-list">
        <button
            class="pages-socials-list__item pages-socials-list__item--linkedin call-function call-action js-social-invite-button"
            data-js-action="navbar:friend-invite"
            data-callback = "popup_friend_invite"
            data-url="<?php echo $url ?>"
            data-social="linkedin"
            data-social-template="general"
            type="button"
        >
            <?php echo widgetGetSvgIcon('linkedin', 16, 16);?>
        </button>
        <button
            class="pages-socials-list__item pages-socials-list__item--facebook call-function call-action js-social-invite-button"
            data-js-action="navbar:friend-invite"
            data-callback = "popup_friend_invite"
            data-url="<?php echo $url ?>"
            data-social="facebook"
            data-social-template="general"
            type="button"
        >
            <?php echo widgetGetSvgIcon('facebook', 16, 16);?>
        </button>
        <button
            class="pages-socials-list__item pages-socials-list__item--twitter call-function call-action js-social-invite-button"
            data-js-action="navbar:friend-invite"
            data-callback="popup_friend_invite"
            data-url="<?php echo $url ?>"
            data-social="twitter"
            data-social-template="twitter"
            type="button"
        >
            <?php echo widgetGetSvgIcon('twitter', 16, 16);?>
        </button>
        <?php if (__CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) { ?>
            <button
                class="pages-socials-list__item pages-socials-list__item--email <?php echo $class?> js-email-invite-button"
                data-title="<?php echo translate('general_popup_invite_button_friend'); ?>"
                title="<?php echo translate('general_popup_invite_button_friend'); ?>"
                data-social="email"
                data-social-template="general"
                data-w="679"
                type="button"
            >
                <?php echo widgetGetSvgIcon('envelope-fill', 16, 16);?>
            </button>
        <?php } ?>
    </div>
</div>

<?php echo dispatchDynamicFragmentInCompatMode(
    "popup:friend-invite-main",
    asset('public/plug/js/friend-invite/index-invite.js', 'legacy'),
    sprintf('function () { new FriendInvite(%s); }', json_encode($invite_messages)),
    array($invite_messages)
); ?>

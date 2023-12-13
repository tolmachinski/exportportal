<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/share-with-family-friends.css');?>" />
<?php $listenerClass = logged_in() ? 'call-function call-action' : 'js-require-logged-systmess'; ?>

<div
    class="js-share-with-family-friends share-with-family-friends"
    data-url="<?php echo $shareUrl;?>"
    data-title="<?php echo $shareTitle;?>"
    data-img="<?php echo $sharePhoto;?>"
    data-type="<?php echo $type;?>"
    data-id="<?php echo $itemId;?>"
>
    <button
        class="share-with-family-friends__item share-with-family-friends__item--fb call-function call-action"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"
        data-type="facebook"
        type="button"
        title="Share via Facebook"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_facebook')?>
    ><i class="ep-icon ep-icon_facebook"></i></button>

    <button
        class="share-with-family-friends__item share-with-family-friends__item--tw call-function call-action"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"
        data-type="twitter"
        type="button"
        title="Share via Twitter"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_twitter')?>
    ><i class="ep-icon ep-icon_twitter"></i></button>

    <button
        class="share-with-family-friends__item share-with-family-friends__item--ln call-function call-action"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"
        data-type="linkedin"
        type="button"
        title="Share via LinkedIn"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_linkedin')?>
    ><i class="ep-icon ep-icon_linkedin"></i></button>

    <button
        class="share-with-family-friends__item share-with-family-friends__item--pt call-function call-action"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"
        data-type="pinterest"
        type="button"
        title="Share via Pinterest"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_pinterest')?>
    ><i class="ep-icon ep-icon_pinterest"></i></button>

    <button
        class="share-with-family-friends__item share-with-family-friends__item--sh <?php echo $listenerClass;?>"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"

        <?php if ('item' === $type) {?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'items/popup_forms/share_item/' . $itemId;?>"
            data-title="Share item"
        <?php } elseif ('company' === $type) {?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>seller/company/popup_forms/share_company/<?php echo $itemId;?>"
            data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_share_company_tag_title', null, true);?>"
        <?php } elseif ('ep_event' === $type) {?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>ep_events/popup_forms/share_event/<?php echo $itemId;?>"
            data-title="<?php echo translate('events_email_popup_ttl_share');?>"
        <?php }?>

        type="button"
        title="Share with your followers"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_share')?>
    ><i class="ep-icon ep-icon_reply-right"></i></button>

    <button
        class="share-with-family-friends__item share-with-family-friends__item--em <?php echo $listenerClass;?>"
        data-callback="callShareWithFamilyFriends"
        data-js-action="user:call-share-with-family-friends"

        <?php if ('item' === $type) { ?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'items/popup_forms/email_item/' . $itemId;?>"
            data-title="Email item"
        <?php } elseif ('company' === $type) { ?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>seller/company/popup_forms/email_company/<?php echo $itemId;?>"
            data-title="Send info about this company to your contacts by email"
        <?php } elseif ('ep_event' === $type) {?>
            data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>ep_events/popup_forms/email_this/<?php echo $itemId;?>"
            data-title="<?php echo translate('events_email_popup_ttl_mail');?>"
        <?php }?>

        type="button"
        title="Email to your friends"
        <?php echo addQaUniqueIdentifier('global__modal_share_btn_email')?>
    ><i class="ep-icon ep-icon_envelope-letter"></i></button>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'user:share-with-family-friends',
        asset('public/plug/js/share-popup/index.js', 'legacy'),
        null,
        null,
        true
    );
?>

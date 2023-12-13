<?php
    if(!isset($class_select_account) || empty($class_select_account)){
        $class_select_account = '';
    }
?>

<div class="select-account-list <?php echo $class_select_account;?>">

    <?php $accounts = logged_in() ? array_filter(session()->accounts, function($account){ return !is_my((int) $account['idu']); }) : session()->accounts;?>
    <?php foreach($accounts as $account){?>
        <div class="select-account-list__item">
            <div
                <?php echo addQaUniqueIdentifier("global__select-account-" . str_replace(['certified', 'verified', ' '], '', strtolower($account['gr_name'])))?>
                class="select-account-list__inner flex-card call-action"
                data-js-action="login:choose-your-account"
                data-user="<?php echo $account['idu'];?>">
                <?php if ($account['status'] === "restricted") { ?>
                    <span class="select-account-list__restricted">
                        <?php echo widgetGetSvgIcon('restricted', 15, 15); ?>
                    </span>
                <?php } ?>

                <div class="select-account-list__image image-card3 image-card3--full-default flex-card__fixed">
                    <span class="link">
                        <img
                            class="image"
                            src="<?php echo getDisplayImageLink(array('{ID}' => $account['idu'], '{FILE_NAME}' => $account['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $account['user_group'] ));?>"
                            alt="<?php echo $account['user_name']; ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                        >
                    </span>
                </div>
                <div class="select-account-list__info flex-card__float">
                    <div class="select-account-list__name" <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>>
                        <?php echo $account['user_name']; ?>
                    </div>
                    <div class="select-account-list__company" <?php echo addQaUniqueIdentifier('global__user-info_company'); ?>>
                        <?php echo $account['company_name']; ?>
                    </div>
                    <div class="select-account-list__group<?php echo userGroupNameColor($account['gr_name']);?>"><?php echo $account['gr_name']; ?></div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(logged_in() && count(session()->accounts) < 3){?>
        <div class="select-account-list__item">
            <div class="select-account-list__inner flex-card call-action"
                data-js-action="dashboard:add-accounts"
                href="<?php echo __CURRENT_SUB_DOMAIN_URL;?>register/popup_forms/add_another_account"
                data-title="<?php echo translate('login_add_another_account', null, true);?>">
                <div class="select-account-list__image image-card3 image-card3--full-default flex-card__fixed">
                    <span class="link">
                        <i class="ep-icon ep-icon_user-add"></i>
                    </span>
                </div>
                <div class="select-account-list__info flex-card__float">
                    <div class="select-account-list__txt"><?php echo translate('login_add_another_account');?></div>
                </div>
            </div>
        </div>
    <?php }?>
</div>

<?php
    echo dispatchDynamicFragment(
        "login:choose-account",
        null,
        true
    );
?>

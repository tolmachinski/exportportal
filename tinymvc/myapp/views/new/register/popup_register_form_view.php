<?php
    $registered_user_type = returnTrueUserGroupName();

    $show_buyer = ( ($registered_user_type == 'seller' || $registered_user_type == 'manufacturer') && !in_array('buyer', $existing_accounts) )?true: false;
    $show_seller = ( ($registered_user_type == 'buyer' || $registered_user_type == 'manufacturer') && !in_array('seller', $existing_accounts) )?true: false;
    $show_manufacturer = ( ($registered_user_type == 'buyer' || $registered_user_type == 'seller') && !in_array('manufacturer', $existing_accounts) )?true: false;
?>

<div class="wr-modal-flex inputs-40">
    <form
        id="js-popup-register-form"
        class="modal-flex__form validateModal"
        data-js-action="accounts:add-other:create"
    >
        <div class="modal-flex__content">
            <ul id="js-popup-register-nav-tabs" class="nav tabs-circle tabs-circle--pb tabs-circle--hide-mobile tabs-circle--no-click" role="tablist">
                <li class="tabs-circle__item">
                    <a class="link active" href="#js-step-register-first" aria-controls="title" role="tab" data-toggle="tab">
                        <div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

                        <div class="tabs-circle__txt"><?php echo translate('login_account_type'); ?></div>
                    </a>
                </li>

                <?php if ($show_buyer) {?>
                    <li class="tabs-circle__item tabs-circle__item--min-simple display-n" data-type="buyer">
                        <a class="link call-action" href="#js-step-register-buyer" aria-controls="title" role="tab" data-toggle="tab">
                            <div class="tabs-circle__point"></div>

                            <div class="tabs-circle__txt"><?php echo translate('login_buyer_word'); ?></div>
                        </a>
                    </li>
                <?php }?>

                <?php if ($show_seller) {?>
                    <li class="tabs-circle__item tabs-circle__item--min-simple display-n" data-type="seller">
                        <a class="link" href="#js-step-register-seller" aria-controls="title" role="tab" data-toggle="tab">
                            <div class="tabs-circle__point"></div>

                            <div class="tabs-circle__txt"><?php echo translate('login_seller_word'); ?></div>
                        </a>
                    </li>
                <?php }?>

                <?php if ($show_manufacturer) {?>
                    <li class="tabs-circle__item tabs-circle__item--min-simple display-n" data-type="manufacturer">
                        <a class="link" href="#js-step-register-manufacturer" aria-controls="title" role="tab" data-toggle="tab">
                            <div class="tabs-circle__point"></div>

                            <div class="tabs-circle__txt"><?php echo translate('login_manufacturer_word'); ?></div>
                        </a>
                    </li>
                <?php }?>

                <li class="tabs-circle__item">
                    <a class="link" href="#js-step-register-last" aria-controls="title" role="tab" data-toggle="tab">
                        <div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

                        <div class="tabs-circle__txt"><?php echo translate('login_finish_word'); ?></div>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade show active" id="js-step-register-first" data-link="validate_1_step">
                    <label class="input-label"><?php echo translate('register_choose_additional_type_label'); ?>:</label>

                    <div id="js-another-account-checkbox" class="account-registration-another__checkbox">
                        <?php if ($show_buyer) {?>
                            <div class="custom-checkbox-wrap">
                                <label class="js-register-another-account custom-checkbox">
                                    <input type="checkbox" class="validate[required]" name="type_another_account" value="buyer">
                                    <div class="custom-checkbox__text"><?php echo translate('register_buyer_word'); ?></div>
                                </label>
                            </div>
                        <?php }?>

                        <?php if ($show_seller) {?>
                            <div class="custom-checkbox-wrap">
                                <label class="js-register-another-account custom-checkbox">
                                    <input type="checkbox" class="validate[required]" name="type_another_account" value="seller">
                                    <div class="custom-checkbox__text"><?php echo translate('register_seller_word'); ?></div>
                                </label>
                            </div>
                        <?php }?>

                        <?php if ($show_manufacturer) {?>
                            <div class="custom-checkbox-wrap">
                                <label class="js-register-another-account custom-checkbox">
                                    <input type="checkbox" class="validate[required]" name="type_another_account" value="manufacturer">
                                    <div class="custom-checkbox__text"><?php echo translate('register_manufacturer_word'); ?></div>
                                </label>
                            </div>
                        <?php }?>

                        <?php if (count($existing_accounts) < 2) {?>
                            <div class="custom-checkbox-wrap">
                                <label class="js-register-another-account js-register-another-account-all custom-checkbox">
                                    <input type="checkbox" class="validate[required]" name="type_another_account" value="all">
                                    <div class="custom-checkbox__text"><?php echo translate('register_both_word'); ?></div>
                                </label>
                            </div>
                        <?php }?>
                    </div>

                    <?php if ($canCopyPersonalInfo || $canCopyCompanyInfo) {?>
                    <div id="js-use-existing-info-block" class="display-n">
                        <label class="input-label">
                            <?php echo translate('add_another_account_use_existing_info_label');?>
                            <a
                                class="js-information-dialog ep-icon ep-icon_info ml-5"
                                data-message="<?php echo translate('add_another_account_use_existing_info_description', null, true);?>"
                                data-title="<?php echo translate('add_another_account_use_existing_info_modal_title', null, true);?>"
                                title="<?php echo translate('add_another_account_use_existing_info_icon_title', null, true);?>"
                                data-keep-modal="true"
                                href="#"></a>
                        </label>

                        <div class="account-registration-another__checkbox-item">
                            <label class="checkbox-group checkbox-group--inline custom-radio">
                                <input
                                    type="radio"
                                    name="group_radio"
                                    class="js-radio-blue"
                                    value="yes"
                                >
                                <div class="account-registration-another__checkbox-txt custom-radio__text"><?php echo translate('general_option_label_yes');?></div>
                            </label>
                        </div>
                        <div class="account-registration-another__checkbox-item mb-0">
                            <label class="checkbox-group checkbox-group--inline custom-radio">
                                <input
                                    type="radio"
                                    name="group_radio"
                                    id="js-radio-value-no"
                                    class="js-radio-blue"
                                    value="no"
                                    checked="checked"
                                >
                                <div class="account-registration-another__checkbox-txt custom-radio__text"><?php echo translate('general_option_label_no');?></div>
                            </label>
                        </div>

                        <div class="display-n" id="js-copy-information-block">
                            <?php if ($canCopyPersonalInfo) {?>
                                <label class="input-label">
                                    Copy Personal Information from:
                                </label>

                                <?php foreach ($canCopyInformationFrom['account_preferences'] as $accountId => $label) {?>
                                    <label class="js-copy-personal-info custom-radio">
                                        <input type="radio" class="js-copy-info-radio" name="copy_personal_info" value="<?php echo $accountId;?>">
                                        <div class="custom-radio__text"><?php echo $label;?></div>
                                    </label>
                                <?php }?>
                            <?php }?>

                            <?php if ($canCopyCompanyInfo) {?>
                                <div id="js-copy-company-info-block" class="display-n_i">
                                    <label class="input-label">
                                        Copy Company Information from:
                                    </label>

                                    <?php foreach ($canCopyInformationFrom['company_main'] as $accountId => $label) {?>
                                        <label class="js-copy-company-info custom-radio">
                                            <input type="radio" class="js-copy-company-info-radio" id="js-copy_company_info" name="copy_company_info" value="<?php echo $accountId;?>">
                                            <div class="custom-radio__text"><?php echo $label;?></div>
                                        </label>
                                    <?php }?>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                </div>
                <!-- END tab 1 -->

                <?php if ($show_buyer) {?>
                    <div role="tabpanel" class="tab-pane fade" id="js-step-register-buyer" data-link="validate_step_2_buyer">
                        <?php views()->display('new/register/step_2_buyer_inputs_view', array('suffix' => '_additional_buyer'));?>
                    </div>
                <?php }?>

                <?php if ($show_seller) {?>
                    <div role="tabpanel" class="tab-pane fade" id="js-step-register-seller" data-link="validate_step_2_seller">
                        <?php views()->display('new/register/another_account_seller_inputs_view', array('input_name' => 'seller', 'multipleselect_position_class' => 'multiple-epselect-wr--open-top'));?>
                    </div>
                <?php }?>

                <?php if ($show_manufacturer) {?>
                    <div role="tabpanel" class="tab-pane fade" id="js-step-register-manufacturer" data-link="validate_step_2_manufacturer">
                        <?php views()->display('new/register/another_account_seller_inputs_view', array('input_name' => 'manufacturer', 'multipleselect_position_class' => 'multiple-epselect-wr--open-top'));?>
                    </div>
                <?php }?>
                <!-- END tab another -->

                <div role="tabpanel" class="tab-pane fade" id="js-step-register-last">
                    <div class="success-alert-b mt-20"><i class="ep-icon ep-icon_ok-circle"></i> <span><?php echo translate('login_success_additional_accounts_message'); ?></span></div>
                </div>
                <!-- END tab 3 -->
            </div>
        </div>
    </form>
</div>

<?php
    echo dispatchDynamicFragment(
        "account:add-other-account",
        [
            [
                'groupType'           => session()->group_type,
                'canCopyPersonalInfo' => $canCopyPersonalInfo,
            ]
        ]
    );
?>

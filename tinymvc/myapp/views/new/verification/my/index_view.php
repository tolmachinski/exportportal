<div class="inputs-40">
    <div class="top-upgrade">
        <div class="top-upgrade__inner container-center2">
            <div class="top-upgrade__group">
                <div class="top-upgrade__group-img">
                    <img
                        class="image"
                        src="<?php echo cleanOutput($group['thumbnail']); ?>"
                        alt="<?php echo cleanOutput($group['name']); ?>"
                    >
                </div>
                <div>
                    <?php if (!empty($upgrade_package)) { ?>
                        <div class="top-upgrade__info-to">
                            Upgrade to
                        </div>
                    <?php } ?>
                    <div class="top-upgrade__group-name">
                        <?php echo cleanOutput($group['name']); ?>
                    </div>
                    <?php if ($is_verified) { ?>
                        <div class="top-upgrade__info-current top-upgrade__info-current--verify">
                            <?php echo translate('accreditation_documents_verified', null, true); ?>
                        </div>
                    <?php } else { ?>
                        <div class="top-upgrade__info-current top-upgrade__info-current--not-verify">
                            <?php echo translate('accreditation_documents_not_verified', null, true); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if(!empty($upgrade_package) || !$is_verified){?>
                <div class="top-upgrade__actions">
                    <?php if (!$is_verified) { ?>
                        <a
                            <?php echo addQaUniqueIdentifier("verification__whatnext-button")?>
                            id="js-action-show-help"
                            class="btn btn-dark call-function"
                            data-href="<?php echo getUrlForGroup("what_next/popup_forms/status"); ?>"
                            data-callback="openWhatNextModal"
                            data-title="<?php echo translate('accreditation_verify_documents_btn_data_title', null, true); ?>"
                            data-sub-title="<?php echo translate('accreditation_verify_documents_btn_data_subtitle', null, true); ?>"
                            title="<?php echo translate('accreditation_verify_documents_btn', null, true); ?>"
                        >
                            <i class="ep-icon ep-icon_info fs-16"></i>
                            <span class="pl-5"><?php echo translate('accreditation_verify_documents_btn', null, true); ?></span>
                        </a>
                    <?php } ?>

                    <?php if(!empty($upgrade_package)){?>
                        <?php if(!empty($upgrade_bill)){?>
                            <?php if($upgrade_bill['status'] == 'init'){?>
                                <a
                                    <?php echo addQaUniqueIdentifier("verification__paynow-button")?>
                                    id="js-button-upgrade-pay-now"
                                    class="fancybox fancybox.ajax btn btn-success"
                                    href="<?php echo getUrlForGroup("payments/popups_payment/pay_bill/{$upgrade_bill['id_bill']}"); ?>"
                                    data-body-class="fancybox-position-ios-fix"
                                    data-title="Payment"
                                    title="Payment"
                                >
                                    <?php echo translate('accreditation_pay_now'); ?>
                                </a>
                            <?php } else{?>
                                <a
                                    <?php echo addQaUniqueIdentifier("verification__payment-details-button")?>
                                    class="fancybox fancybox.ajax btn btn-success"
                                    href="<?php echo getUrlForGroup("billing/popup_forms/bill_detail/{$upgrade_bill['id_bill']}"); ?>"
                                    data-title="Payment details"
                                    title="Payment details"
                                >
                                    Payment details
                                </a>
                            <?php }?>
                        <?php }?>

                        <?php if(empty($upgrade_bill) || !in_array($upgrade_bill['status'], array('paid', 'confirmed'))){?>
                            <a
                                <?php echo addQaUniqueIdentifier("verification__cancel-upgrade-button")?>
                                id="js-button-upgrade-cancel-button"
                                class="btn btn-outline-dark confirm-dialog"
                                data-message="<?php echo translate('accreditation_sure_want_cancel_upgrade_process', null, true); ?>"
                                data-callback="cancel_upgrade"
                            ><?php echo translate('accreditation_cancel_upgrade'); ?></a>
                        <?php }?>

                        <script
                            id="upgrade-payment-detail--template--button"
                            type="text/template"
                        >
                            <a
                                <?php echo addQaUniqueIdentifier("verification__payment-details-button")?>
                                class="fancybox fancybox.ajax btn btn-success"
                                href="<?php echo getUrlForGroup("billing/popup_forms/bill_detail/{$upgrade_bill['id_bill']}"); ?>"
                                data-title="Payment details"
                                title="Payment details"
                            >
                                Payment details
                            </a>
                        </script>
                    <?php }?>
                </div>
            <?php }?>
        </div>
    </div>

    <div class="container-center dashboard-container pt-0">
        <div class="upgrade-question">
            <div class="upgrade-question__info">
                <p class="upgrade-question__title"><?php echo translate('accreditation_have_questions_title'); ?></p>

                <p class="upgrade-question__desc"><?php echo translate('accreditation_have_questions_desc'); ?></p>
            </div>
        </div>

        <?php if ($is_verified) { ?>
            <?php if ($show_upload_placeholders) { ?>
                <div class="pt-30">
                    <div class="txt-medium pb-10">
                        <?php echo translate('accreditation_please_upload_short_title'); ?>
                    </div>
                    <?php echo translate(
                        'accreditation_please_upload_assures_not_to_be_used_text',
                        array(
                            '[LINK_START]' => '<a ' .
                            addQaUniqueIdentifier("verification__user-guide-link") . '
                            data-title="' .translate('accreditation_user_guide_title', null, true). '"
                            target="_blank"
                            download
                            href="' . __IMG_URL . 'public/img/userguide/' . $group['user_guide'] .'">',
                            '[LINK_END]' => '</a>'
                        )); ?>
                </div>
            <?php } else { ?>
                <div class="pt-30">
                    <p class="txt-medium">
                        <?php echo translate('accreditation_profile_approved_documents_verified', array('[USER_GR_NAME]' => $group['name'])); ?>
                    </p>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="pt-30">
                <div class="txt-medium pb-10">
                    <?php echo translate('accreditation_please_upload_assures_not_to_be_used_title'); ?>
                </div>
                <?php echo translate(
                    'accreditation_please_upload_assures_not_to_be_used_text',
                    array(
                        '[LINK_START]' => '<a' .
                        addQaUniqueIdentifier("verification__user-guide-link") . '
                        data-title="' . translate('accreditation_user_guide_title', null, true) . '"
                        target="_blank"
                        download
                        href="' . __IMG_URL . 'public/img/userguide/' . $group['user_guide'] .'">',
                        '[LINK_END]' => '</a>'
                    )); ?>
            </div>
        <?php } ?>

        <div class="pt-30">
            <div class="txt-medium pb-10">
                <?php echo translate('accreditation_security_placeholder_2_headline'); ?>
            </div>

            <?php echo translate('accreditation_security_placeholder_2_text', array('{{BREAKLINE}}' => '<br>')); ?>
        </div>

        <?php if (!$is_verified || $is_verifying || $show_upload_placeholders) { ?>
            <div class="info-alert-b mt-15"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('accreditation_verification_process_duration'); ?></span></div>
        <?php } ?>

        <?php views()->display('new/verification/my/documents_table_view'); ?>
    </div>
</div>

<?php
    if (!empty($upgrade_packages)) {
        views()->display('new/upgrade/packages_view');
    }
?>

<script>
    <?php if (!$is_verified || !empty($upgrade_package)) { ?>
        var openWhatNextModal = function($this){
            open_result_modal({
                title: $this.data('title'),
                subTitle: $this.data('sub-title'),
                content: $this.data('href'),
                isAjax: true,
                closable: true,
                buttons: []
            });
        }
    <?php } ?>

    <?php if(!empty($upgrade_package)){?>
        var cancelUpgrade = function(obj){
            var $this = $(obj);
            $.ajax({
                url: __group_site_url + 'upgrade/ajax_operations/cancel',
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    showLoader('body');
                },
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        location.reload(true);
                    } else{
                        systemMessages(resp.message, resp.mess_type );
                        hideLoader('body');
                    }
                }
            });
        }

        $(function(){
            var buttonPaymentDetailTemplate = $('#upgrade-payment-detail--template--button').text() || null;

            function paymentCallback(data){
                if((data.mess_type == 'success')){
                    $('#js-button-upgrade-pay-now').replaceWith(buttonPaymentDetailTemplate);
                    $('#js-button-upgrade-cancel-button').remove();
                    closeFancyBox();
                }
            }

            mix(window, {
                payment_callback: paymentCallback,
                cancel_upgrade: cancelUpgrade
            }, false);
        });
    <?php } ?>
</script>

<?php if(!empty($upgrade_package)){?>
    <script>
        function payment_callback(data){
            closeFancyBox();
            showLoader($('#js-upgrade-list'), 'Loading...');
            // removeCookie('upgrade_open_bill_popup');
            location.reload(true);
        }

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

        $(function(){
            var buttonPaymentDetailTemplate = $('#upgrade-payment-detail--template--button').text() || null;

            function paymentCallback(data){
                if((data.mess_type == 'success')){
                    $('#js-button-upgrade-pay-now').replaceWith(buttonPaymentDetailTemplate);
                    $('#js-button-upgrade-cancel-button').remove();
                    closeFancyBox();
                }
            }

            var cancelUpgrade = function(obj){
                var $this = $(obj);
                $.ajax({
                    url: __site_url + 'upgrade/ajax_operations/cancel',
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

            mix(window, {
                payment_callback: paymentCallback,
                cancel_upgrade: cancelUpgrade
            }, false);
        });
    </script>
<?php }?>

<div class="inputs-40 mb-30">

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
                <div class="top-upgrade__info">
                    <div class="top-upgrade__info-to">
                        <?php echo $upgrade_request['type'] == 'upgrade' ? translate('upgrade_top_line_info_to_1') : translate('upgrade_top_line_info_to_2');?>
                    </div>
                    <div class="top-upgrade__group-name">
                        <?php echo cleanOutput($group['name']); ?>
                    </div>
                    <div class="top-upgrade__info-period">
                        <?php
                            $periodLength = '';
                            if((int)$upgrade_package['price'] > 0){
                                $periodLength = '1';
                            }

                            echo translate('upgrade_top_line_info_period') . ' ' . $periodLength;
                        ?> <span class="tt-lowercase"><?php echo $upgrade_package['full'];?></span>
                    </div>
                </div>
            </div>

            <?php if(!empty($upgrade_package)){?>
                <div class="top-upgrade__actions">
                    <?php if(!empty($upgrade_bill)){?>
                        <?php if($upgrade_bill['status'] == 'init'){?>
                            <a
                                <?php echo addQaUniqueIdentifier("upgrade_upgrade-to_pay_btn")?>
                                id="js-button-upgrade-pay-now"
                                class="fancybox fancybox.ajax btn btn-success"
                                href="<?php echo __SITE_URL . 'payments/popups_payment/pay_bill/' . $upgrade_bill['id_bill'];?>"
                                data-body-class="fancybox-position-ios-fix"
                                data-title="Payment"
                                title="Payment"
                            >
                                <?php echo translate('accreditation_pay_now'); ?>
                            </a>
                        <?php } else{?>
                            <a
                                class="fancybox fancybox.ajax btn btn-success"
                                <?php echo addQaUniqueIdentifier("upgrade_upgrade-to_payment-details_btn")?>
                                href="<?php echo __SITE_URL . 'billing/popup_forms/bill_detail/' . $upgrade_bill['id_bill'];?>"
                                data-title="<?php echo translate('upgrade_btn_payment_details'); ?>"
                                title="<?php echo translate('upgrade_btn_payment_details'); ?>"
                            >
                                <?php echo translate('upgrade_btn_payment_details'); ?>
                            </a>
                        <?php }?>
                    <?php }?>

                    <?php if(empty($upgrade_bill) || empty((int) $upgrade_bill['balance']) || !in_array($upgrade_bill['status'], array('paid', 'confirmed'))){?>
                        <a
                            <?php echo addQaUniqueIdentifier("upgrade_upgrade-to_cancel_btn")?>
                            id="js-button-upgrade-cancel-button"
                            class="btn btn-outline-dark confirm-dialog"
                            data-message="<?php echo translate('extend' === $upgrade_request['type'] ? 'accreditation_sure_want_cancel_extend_upgrade_process' : 'accreditation_sure_want_cancel_upgrade_process', null, true); ?>"
                            data-callback="cancel_upgrade"
                        ><?php echo translate('extend' === $upgrade_request['type'] ? 'accreditation_cancel_extend_upgrade_btn' : 'accreditation_cancel_upgrade');?></a>
                    <?php }?>

                    <a
                        <?php echo addQaUniqueIdentifier("upgrade_upgrade-to_whats-next_btn")?>
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

                    <script type="text/template" id="upgrade-payment-detail--template--button">
                        <a
                            class="fancybox fancybox.ajax btn btn-success"
                            href="<?php echo __SITE_URL . 'billing/popup_forms/bill_detail/' . $upgrade_bill['id_bill'];?>"
                            data-title="<?php echo translate('upgrade_btn_payment_details'); ?>"
                            title="<?php echo translate('upgrade_btn_payment_details'); ?>"
                        >
                            <?php echo translate('upgrade_btn_payment_details'); ?>
                        </a>
                    </script>
                </div>
            <?php }?>
        </div>
    </div>

    <div class="container-center">
        <div class="upgrade-question mb-30">
            <div class="upgrade-question__info">
                <div class="upgrade-question__title"><?php echo translate('accreditation_have_questions_title'); ?></div>

                <p class="upgrade-question__desc"><?php echo translate('accreditation_have_questions_desc'); ?></p>
            </div>
        </div>
        <p><?php echo translate(
                        'accreditation_please_upload_assures_not_to_be_used_text',
                        array(
                            '[LINK_START]' => '<a
                            data-title="' .translate('accreditation_user_guide_title', null, true). '"
                            atas="upgrade_questions_download-guide_btn"
                            target="_blank"
                            download
                            href="' . __IMG_URL . 'public/img/userguide/' . $group['user_guide'] .'">',
                            '[LINK_END]' => '</a>'
                        )); ?></p>

        <div class="info-alert-b mt-15"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('accreditation_verification_process_duration'); ?></span></div>

        <?php views()->display('new/verification/my/documents_table_view'); ?>
    </div>
</div>

<div class="content-upgrade">
    <?php views()->display('new/upgrade/upgrade_circle_view');?>
</div>

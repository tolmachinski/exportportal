<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="shipperPopupEmailFormCallBack"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('company_email_popup_input_email_label');?></label>
            <input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo config('email_this_max_email_count'); ?>]]" type="text" name="emails" value="" placeholder="<?php echo translate('company_email_popup_input_email_placeholder', null, true);?>"/>
            <p class="fs-12 txt-red"><?php echo translate('company_email_popup_input_email_auxiliary_text');?></p>

            <label class="input-label input-label--required"><?php echo translate('company_email_popup_input_message_label');?></label>
            <textarea class="validate[required,maxSize[500]] js-textcounter-email-message" data-max="500" name="message" placeholder="<?php echo translate('company_email_popup_input_message_placeholder', null, true);?>"></textarea>
            <input type="hidden" value="<?php echo $id_company ?>" name="shipper"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('company_email_popup_submit_btn');?></button>
            </div>
        </div>
    </form>
</div>

<script>
    $(function () {
        var loadingText = "<?php echo translate("sending_message_form_loader", null, true); ?>";
        var sendEmail = function (formElement) {
            var form = $(formElement);
            var data = form.serializeArray();
            var wrapper = form.closest(".js-modal-flex");

            form.find("button[type=submit]").addClass("disabled");
            showLoader(wrapper, loadingText);
            postRequest("shipper/ajax_send_email/email", data)
                .then(function (response) {
                    hideLoader(wrapper);
                    systemMessages(response.message, response.mess_type);
                    if (response.mess_type === "success") {
                        closeFancyBox();
                    } else {
                        form.find("button[type=submit]").removeClass("disabled");
                    }
                })
                .catch(function (error) {
                    form.find("button[type=submit]").removeClass("disabled");
                    hideLoader(wrapper);
                    onRequestError(error);
                });
        };

        $(".js-textcounter-email-message").textcounter({
            countDown: true,
            countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before" }),
            countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
        });

        mix(globalThis, { shipperPopupEmailFormCallBack: sendEmail }, false);
    });
</script>

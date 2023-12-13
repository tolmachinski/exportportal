<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="shippersPopupShareFormCallBack"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('share_ff_company_form_message_label');?></label>
            <textarea class="validate[required] js-textcounter-share-message" data-max="500" name="message" placeholder="<?php echo translate('share_ff_company_form_message_placeholder', null, true);?>"></textarea>
            <input type="hidden" value="<?php echo $id_company ?>" name="shipper"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('share_ff_company_form_submit_btn');?></button>
            </div>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('.js-textcounter-share-message').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        });
    });

    function shippersPopupShareFormCallBack(form) {
        var $form = $(form);
        var $wrform = $form.closest('.js-modal-flex');
        var fdata = $form.serialize();

        $.ajax({
            type: 'POST',
            url: 'shipper/ajax_send_email/share',
            data: fdata,
            dataType: 'JSON',
            beforeSend: function () {
                showLoader($wrform, '<?php echo translate('sending_message_form_loader', null, true);?>');
                $form.find('button[type=submit]').addClass('disabled');
            },
            success: function (resp) {
                hideLoader($wrform);
                systemMessages(resp.message, resp.mess_type);

                if (resp.mess_type === 'success') {
                    closeFancyBox();
                } else {
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>

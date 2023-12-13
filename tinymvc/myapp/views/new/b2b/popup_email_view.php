<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        id="js-b2b-request-email-form"
        class="modal-flex__form validateModal"
        data-callback="b2bPopupEmailFormCallBack"
        data-js-action="b2b:email-form.submit"
    >
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required">Emails</label>
                <input
                    class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo config('email_this_max_email_count'); ?>]]"
                    type="text"
                    name="inp"
                    value=""
                    placeholder="Insert email addresses"
                />
                <p class="fs-12 txt-red mb-15">*Please use comma as email separators </p>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">Message</label>
                <textarea
                    class="validate[required,maxSize[1000]] js-textcounter-email-message"
                    name="mess"
                    data-max="1000"
                    placeholder="Message"></textarea>
            </div>

            <input type="hidden" name="id" value="<?php echo $id_request; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-new16 btn-primary" type="submit">Send</button>
            </div>
        </div>
    </form>
</div>

<?php
    if ($webpackData) {
        echo dispatchDynamicFragment('lazy-loading:b2b-actions-email');
    } else {
?>
    <!-- For wall page -->
    <script>
        function b2bPopupEmailFormCallBack(form){
            var $form = $(form);
            var fdata = $form.serialize();
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL;?>b2b/ajax_send_email/email',
                dataType: 'JSON',
                data: fdata,
                beforeSend: function(){
                    showLoader('.js-wr-modal');
                },
                success: function(resp){
                    systemMessages(resp.message, resp.mess_type);
                    hideLoader('.js-wr-modal');

                    if(resp.mess_type == 'success'){
                        closeFancyBox();
                    }else{
                        $form.find('button[type=submit]').removeClass('disabled');
                    }
                }
            });
        }

        $(function(){
            $('.js-textcounter-email-message').textcounter({
                countDown: true,
                countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
                countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
            });
        });
    </script>
<?php } ?>

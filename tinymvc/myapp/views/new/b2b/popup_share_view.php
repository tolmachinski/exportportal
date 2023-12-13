<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="b2bPopupShareFormCallBack"
        data-js-action="b2b:share-form.submit"
    >
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required">Message</label>
                <textarea name="mess" class="validate[required] js-textcounter-message" data-max="1000" placeholder="Message"></textarea>
            </div>

            <input type="hidden" name="id" value="<?php echo $id_request; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </div>
    </form>
</div>

<?php
    if ($webpackData) {
        echo dispatchDynamicFragment('lazy-loading:b2b-actions-share');
    } else {
?>
    <!-- For wall page -->
    <script>
        function b2bPopupShareFormCallBack(form){
            var $form = $(form);
            var fdata = $form.serialize();
            $.ajax({
                type: 'POST',
                url: 'b2b/ajax_send_email/share',
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

        $('.js-textcounter-message').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        });
    </script>
<?php } ?>

<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal">
        <div class="modal-flex__content">
            <label class="input-label input-label--required">Message</label>
            <textarea name="message"  class="validate[required,maxSize[500]] js-textcounter-email-message" data-max="500" placeholder="Message"></textarea>
            <input type="hidden" name="person" value="<?php echo $id_person?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('.js-textcounter-email-message').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    });
    function modalFormCallBack(form){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');
        var fdata = $form.serialize();

        $.ajax({
            type: 'POST',
            url: 'our_team/ajax_our_team_operation/contact_person',
            dataType: 'JSON',
            data: fdata,
            beforeSend: function(){
                showLoader($wrapper);
            },
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);
                hideLoader($wrapper);
                if(resp.mess_type == 'success'){
                    closeFancyBox();
                }else{
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>

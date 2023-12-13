<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="simpleUserPopupShareFormCallBack"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required"><?php echo translate('share_user_form_message_label');?></label>
			<textarea name="message" <?php echo addQaUniqueIdentifier('popup__share__form_message-input') ?> class="validate[required]" placeholder="<?php echo translate('share_user_form_message_placeholder', null, true);?>"></textarea>
            <input type="hidden" name="user" value="<?php echo $id_user; ?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__share__form_send-btn') ?> type="submit"><?php echo translate('share_user_form_submit_btn');?></button>
            </div>
		</div>
	</form>
</div>

<script>
    function simpleUserPopupShareFormCallBack(form){
        var $form = $(form);
        var $wrform = $form.closest('.js-modal-flex');
        var fdata = $form.serialize();

        $.ajax({
            type: 'POST',
            url: 'user/ajax_send_email/share',
            data: fdata,
            dataType: 'JSON',
            beforeSend: function(){
                showLoader($wrform, '<?php echo translate('sending_email_form_loader', null, true);?>');
                $form.find('button[type=submit]').addClass('disabled');
            },
            success: function(resp){
                hideLoader($wrform);
                systemMessages( resp.message, resp.mess_type );

                if(resp.mess_type == 'success'){
                    closeFancyBox();
                }else{
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>

<?php if(logged_in()){?>
<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="form-send-order"
        class="modal-flex__form validateModal"
        data-callback="usersFeedbacksAddReplyFormCallBack"
    >
	   <div class="modal-flex__content mh-300">
			<label class="input-label input-label--required"><?php echo translate('edit_feedback_reply_form_message_label');?></label>
			<textarea
                class="validate[required,maxSize[1000]] textcounter-feedback_reply"
                name="description"
                data-max="1000"
                placeholder="<?php echo translate('edit_feedback_reply_form_message_placeholder', null, true);?>"
                <?php echo addQaUniqueIdentifier('popup__add-feedback-reply__form_description-textarea') ?>
            ></textarea>
            <input type="hidden" name="reply" value="<?php echo $feedback['id_feedback'] ?>"/>
	   </div>
	   <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__add-feedback-reply__form_save-btn') ?>>
                    <?php echo translate('add_feedback_reply_form_submit_btn');?>
                </button>
            </div>
	   </div>
   </form>
</div>
<script>
$(document).ready(function(){
	$('.textcounter-feedback_reply').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function usersFeedbacksAddReplyFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'feedbacks/ajax_feedback_operation/edit_reply',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				callFunction('addReplyFeedbackCallback', resp);
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
<?php } ?>

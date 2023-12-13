<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="usersFeedbacksEditFeedbackFormCallBack"
    >
		<div class="modal-flex__content mh-500">
			<label class="input-label input-label--required"><?php echo translate('edit_feedback_form_title_label');?></label>
			<input class="validate[required,maxSize[200]]" type="text" name="title" maxlength="200" value="<?php echo $feedback['title'] ?>" placeholder="<?php echo translate('edit_feedback_form_title_placeholder', null, true);?>"/>
			<label class="input-label input-label--required"><?php echo translate('edit_feedback_form_description_label');?></label>
			<textarea class="validate[required,maxSize[1000]] textcounter-feedback_description" name="description" data-max="1000" placeholder="<?php echo translate('edit_feedback_form_description_placeholder', null, true);?>"><?php echo $feedback['text'] ?></textarea>
            <input type="hidden" name="feedback" value="<?php echo $feedback['id_feedback'] ?>"/>
		</div>
		<div class="modal-flex__btns">
            <?php if (isset($in_modal)) {?>
                <div class="modal-flex__btns-left">
                    <button class="btn btn-default" type="reset"><?php echo translate('edit_feedback_form_cancel_btn');?></button>
                </div>
            <?php }?>

            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('edit_feedback_form_submit_btn');?></button>
            </div>
		</div>
	</form>
</div>
<script>
$(document).ready(function(){
	$('.textcounter-feedback_description').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function usersFeedbacksEditFeedbackFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'feedbacks/ajax_feedback_operation/edit_feedback',
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
				callFunction('editFeedbackCallback', resp);
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

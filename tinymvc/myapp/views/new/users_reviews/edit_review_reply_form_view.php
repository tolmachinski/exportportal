<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="form-send-order"
        class="modal-flex__form validateModal"
        data-callback="usersReviewsEditReplyFormCallBack"
    >
		<div class="modal-flex__content mh-300">
			<label class="input-label input-label--required"><?php echo translate('reply_review_form_mesage_label');?></label>
			<textarea
                class="validate[required,maxSize[500]] textcounter-reviews_reply"
                data-max="500"
                name="text_reply"
                placeholder="<?php echo translate('reply_review_form_mesage_placeholder', null, true);?>"
                <?php echo addQaUniqueIdentifier('popup__reviews__reply-form_description-textarea'); ?>
                ><?php echo empty($review['reply']) ? '' : $review['reply'];?></textarea>
            <input type="hidden" name="review" value="<?php echo $review['id_review'] ?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" <?php echo addQaUniqueIdentifier('popup__reviews__reply-form_submit-btn'); ?>>
                    <?php echo translate('reply_review_form_submit_btn');?>
                </button>
            </div>
		</div>
	</form>
</div>
<script>
$(function(){
	$('.textcounter-reviews_reply').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function usersReviewsEditReplyFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'reviews/ajax_review_operation/edit_reply',
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
				closeFancyBox();
				callFunction('editReviewReplyCallback', resp);
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

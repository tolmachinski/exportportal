<div class="js-modal-flex wr-modal-flex inputs-40">
   <form
        id="form-send-order"
        class="modal-flex__form validateModal"
        data-callback="usersReviewsEditReviewFormCallBack"
    >
        <div class="modal-flex__content">
            <div class="input-group">
                <label class="input-label input-label--required mt-0"><?php echo translate('edit_review_form_title_label');?></label>
                <input
                    class="validate[required,maxSize[200]]"
                    type="text"
                    name="title"
                    maxlength="200"
                    value="<?php echo cleanOutput($review['rev_title']);?>"
                    placeholder="<?php echo translate('edit_review_form_title_placeholder', null, true);?>"
                    <?php echo addQaUniqueIdentifier('popup__reviews__form_title-input'); ?>
                />
            </div>
            <div class="input-group">
                <label class="input-label input-label--required"><?php echo translate('edit_review_form_message_label');?></label>
                <textarea
                    class="validate[required,maxSize[500]] textcounter-reviews_description"
                    name="description"
                    data-max="500"
                    placeholder="<?php echo translate('edit_review_form_message_placeholder', null, true);?>"
                    <?php echo addQaUniqueIdentifier('popup__reviews__form_description-textarea'); ?>
                ><?php echo $review['rev_text'] ?></textarea>
                <input type="hidden" name="review" value="<?php echo $review['id_review'];?>"/>
            </div>

            <?php views('new/users_reviews/image_uploader_view');?>

        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" <?php echo addQaUniqueIdentifier('popup__reviews__form_submit-btn'); ?>>
                    <?php echo translate('edit_review_form_submit_btn');?>
                </button>
            </div>
        </div>
   </form>
</div>

<script>
$(function(){
	$('.textcounter-reviews_description').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function usersReviewsEditReviewFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'reviews/ajax_review_operation/edit_review',
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
				callFunction('editReviewCallback', resp);
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

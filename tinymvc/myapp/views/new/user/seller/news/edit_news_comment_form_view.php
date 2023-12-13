<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="form-send-order"
        class="modal-flex__form validateModal"
        data-callback="sellerNewsEditCommentFormCallBack"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('seller_news_comment_word');?></label>
            <textarea
                class="validate[required,maxSize[500]] textcounter_comment-message"
                data-max="500"
                name="mess"
                placeholder="<?php echo translate('seller_news_write_comment_text', null, true); ?>"
                <?php echo addQaUniqueIdentifier('popup__company-news-detail__edit-comment-form_content-textarea'); ?>
            ><?php if(isset($item)) echo $item['text_comment'];?></textarea>
            <input type="hidden" name="id" value="<?php if(isset($item)) echo $item['id_comment'];?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__company-news-detail__edit-comment-form_submit-btn'); ?>
                >
                    <?php echo translate('seller_news_submit_word');?>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
	$('.textcounter_comment-message').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function sellerNewsEditCommentFormCallBack(form){
	var $form = $(form);
	var fdata = $form.serialize();
	var $wrform = $form.closest('.js-modal-flex');

	$.ajax({
		type: 'POST',
		url: 'seller_news/ajax_news_operations/edit_comment',
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
				_notifyContentChangeCallback();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

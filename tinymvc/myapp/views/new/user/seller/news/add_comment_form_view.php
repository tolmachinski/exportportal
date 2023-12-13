<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerNewsAddCommentFormCallBack"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('seller_news_comment_word');?></label>
            <textarea
                class="validate[required,maxSize[500]] textcounter_comment-message"
                data-max="500"
                name="mess"
                placeholder="<?php echo translate('seller_news_write_comment_text', null, true);?>"
                <?php echo addQaUniqueIdentifier('company-news-detail__add-comment_content-textarea_popup'); ?>
                ></textarea>
            <input type="hidden" name="news" value="<?php echo $news['id_news']; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('company-news-detail__add-comment_submit-btn_popup'); ?>
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

function sellerNewsAddCommentFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'seller_news/ajax_news_operations/add_comment',
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

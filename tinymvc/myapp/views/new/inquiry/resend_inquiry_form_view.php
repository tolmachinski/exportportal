<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal inputs-40" data-callback="resend_inquiry_form">
		<div class="modal-flex__content">
			<label class="input-label">Message:</label>
			<textarea class="validate[required,maxSize[500]] textcounter" data-max="500" name="message" ></textarea>
            <input type="hidden" name="inquiry" value="<?php echo $inquiry['id_inquiry']?>" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script>
    $(document).ready(function(){
        $('.textcounter').textcounter({
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    });

	var resend_inquiry_form = function(form){
		var $form = $(form);
        var fdata = $form.serialize();
        var $wrapper = $form.closest('.js-modal-flex');
        var inquiry = $form.find('input[name=inquiry]').val();
        $form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>inquiry/ajax_inquiry_operation/resend_inquiry',
            data: fdata,
            beforeSend: function(){ showLoader($wrapper); },
            dataType: 'json',
            success: function(resp){
                $form.find('button[type="submit"]').prop('disabled', false);

                if(resp.mess_type == 'success'){
                    showInquiry(inquiry);
                }

                hideLoader($wrapper);
                systemMessages( resp.message, resp.mess_type );
                closeFancyBox();
            }
        });
	}
</script>

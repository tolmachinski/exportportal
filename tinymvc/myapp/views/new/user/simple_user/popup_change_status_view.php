<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="simpleUserPopupChangeStatusFormCallBack"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Status</label>
			<textarea class="validate[maxSize[500]] js-textcounter-user-status" data-max="500" name="text" placeholder="Status"><?php  echo (!empty($user_info['showed_status']))?$user_info['showed_status']:''; ?></textarea>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>
</div>

<script>
$(function(){
	$('.js-textcounter-user-status').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function simpleUserPopupChangeStatusFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
    var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'user/ajax_user_operation/change_status',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, '<?php echo translate('sending_email_form_loader', null, true);?>');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);

			if(resp.mess_type == 'success'){
                var $status_text = $('.ppersonal-status').find(".ppersonal-status__text");
                $status_text.html(resp.text);

                if(resp.text.length > 0){
                    $status_text.removeClass('txt-gray');
                }else if(!$status_text.hasClass('txt-gray')){
                    $status_text.html('Share your status.').addClass('txt-gray');
                }

				closeFancyBox();
			}else{
			    systemMessages( resp.message, resp.mess_type );
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

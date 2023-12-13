<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="sellerInviteExternalFeedbackFormCallBack"
    >
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required"><?php echo translate('invite_external_feedback_form_rating_label');?></label>
						<input id="rating-feedback" class="rating-tooltip" data-filled="ep-icon ep-icon_star txt-orange fs-30" data-empty="ep-icon ep-icon_star-empty txt-orange fs-30" type="hidden" name="feedback_raiting" value="0">
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('invite_external_feedback_form_full_name_label');?></label>
						<input type="text" class="validate[required, maxSize[150]]" name="full_name" placeholder="<?php echo translate('invite_external_feedback_form_full_name_placeholder', null, true);?>">
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('invite_external_feedback_form_email_label');?></label>
						<input type="text" class="validate[required, custom[noWhitespaces],custom[emailWithWhitespaces]]" name="email" placeholder="<?php echo translate('invite_external_feedback_form_email_placeholder', null, true);?>">
					</div>

					<div class="col-12">
						<label class="input-label input-label--required"><?php echo translate('invite_external_feedback_form_message_label');?></label>
						<textarea class="validate[required,maxSize[200]] textcounter-feedback_description" name="description_feedback" data-max="200" placeholder="<?php echo translate('invite_external_feedback_form_message_placeholder', null, true);?>"></textarea>
					</div>
				</div>
			</div>
            <input type="hidden" name="company" value="<?php echo $company['id_company'];?>">
            <input type="hidden" name="code" value="<?php echo $code;?>">
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit"><?php echo translate('invite_external_feedback_form_submit_btn');?></button>
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

	$('.rating-tooltip').rating({
		extendSymbol: function (rate) {
			$(this).attr('title', ratingBootstrapStatus(rate));
		}
	});
});

function sellerInviteExternalFeedbackFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: __site_url + 'external_feedbacks/ajax_operations/add_feedback',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			if(resp.mess_type == 'success'){
				window.location.href = '<?php echo getCompanyURL($company);?>';
			} else{
				systemMessages( resp.message, 'message-' + resp.mess_type );
				$form.find('button[type=submit]').removeClass('disabled');
				hideLoader($wrform);
			}
		}
	});
}
</script>

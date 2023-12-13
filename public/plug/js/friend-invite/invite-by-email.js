$(function(){
	$('.js-textcounter-message').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function sendEmailInvite(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'invite/ajax_send_email/email',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, "Sending email...");
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);

			if(resp.mess_type == 'success'){
				var template = $('#js-template-email-invite-success').text();
				template = template.replace(new RegExp('{{email}}', 'g'), resp.email);

				open_email_success_dialog('Friend invite', template, [
					{
						label: translate_js({ plug: 'general_i18n', text: 'form_button_done_text' }),
						cssClass: 'btn btn-dark mnw-130',
						action: function(dialog){
							dialog.close();
						}
					}
				]);

				closeFancyBox();
			}else{
        		systemMessages( resp.message, resp.mess_type );
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}

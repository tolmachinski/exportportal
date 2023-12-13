$(function(){
	$('.js-textcounter-message').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function popupEmailShareFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-wr-modal');
	var action = $form.data('action');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: __current_sub_domain_url + action,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, 'Sending message...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}

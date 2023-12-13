<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="js-modal-form modal-flex__form validateModal">
		<div class="modal-flex__content">
			<textarea class="validate[required,maxSize[1000]] textcounter-cancel_request-reason" data-max="1000" data-prompt-position="bottomLeft:0" name="message" placeholder="Write your message here ..."></textarea>
            <input type="hidden" value="<?php echo $order_info['id'];?>" name="id_order" />
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary call-function" data-callback="cancelOrderRequest" type="submit">Send</button>
			</div>
		</div>
	</form>
</div>
<script>
$(function(){
	$('.textcounter-cancel_request-reason').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});
function cancelOrderRequest(button){
	var $form = $(button).closest(".js-modal-form");
	var fdata = $form.serialize();
	var $wrform = $form.closest('.js-modal-flex');
	$.ajax({
		type: 'POST',
		url: '<?php echo getUrlForGroup('order/ajax_order_operations/cancel_request');?>',
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
				callFunction('cancel_request_callback', resp);
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

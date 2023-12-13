<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="resend_po_form">
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Message:</label>
            <textarea class="validate[required,maxSize[500]] textcounter" data-max="500" name="message"></textarea>
            <input type="hidden" name="po" value="<?php echo $po['id_po']?>" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>

<script>
$(function(){
	$('.textcounter').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

var resend_po_form = function(form){
	var $form = $(form);
	var fdata = $form.serialize();
	var po = $form.find('input[name=po]').val();
    $form.find('button[type="submit"]').prop('disabled', true);
    var $wrapper = $form.closest('.js-modal-flex');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>po/ajax_po_operation/resend_po',
		data: fdata,
		beforeSend: function(){
			showLoader($wrapper);
		},
		dataType: 'json',
		success: function(resp){
			$form.find('button[type="submit"]').prop('disabled', false);

			if(resp.mess_type == 'success'){
				showPo(po);
			}

			hideLoader($wrapper);
			systemMessages( resp.message, resp.mess_type );
			closeFancyBox();
		}
	});
}
</script>

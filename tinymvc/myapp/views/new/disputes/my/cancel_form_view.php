<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="disputesMyCancelFormCallBack"
    >
		<div class="modal-flex__content mh-600">
			<label class="input-label input-label--required">Reason</label>
			<textarea class="validate[required,maxSize[500]] textcounter-reason_dispute" data-max="500" name="reason"></textarea>
            <input type="hidden" name="disput" value="<?php echo $id_dispute; ?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(function(){
		$('.textcounter-reason_dispute').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});

	function disputesMyCancelFormCallBack(form, data_table){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>dispute/ajax_operation/cancel',
			data: $form.serialize(),
			beforeSend: function () {
				showLoader($wrapper);
			},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					callFunction('callbackCancelDispute');
				} else {
					hideLoader($wrapper);
				}
			}
		});
	}
</script>

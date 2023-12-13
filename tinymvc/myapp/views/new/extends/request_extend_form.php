<div class="js-modal-flex wr-modal-flex">
	<form class="modal-flex__form validateModal inputs-40" data-callback="create_extend_request">
		<div class="modal-flex__content updateValidationErrorsPosition">
			<div>
			<?php if(!empty($order_info)){?>
				<strong>Expire on: <?php echo formatDate($order_info['status_countdown']);?></strong>
			<?php }?>
			<?php if(!empty($bill)){?>
				<strong>Expire on: <?php echo formatDate($bill['due_date']);?></strong>
			<?php }?>
			</div>

			<label class="input-label input-label--required">Extend for N day(s) <span class="fs-10">(Min 1 day, max 90 days)</span>:</label>
			<input class="validate[required,custom[positive_integer],min[1],max[90]]" type="text" name="extend_days" placeholder="Number of days to extend">

			<label class="input-label input-label--required">Write the reason:</label>
			<textarea class="validate[required] textcounter_extend-request" data-max="500" name="extend_reason" placeholder="Extend reason"></textarea>
            <input type="hidden" name="extend_type" value="<?php echo $extend_type;?>"/>
            <input type="hidden" name="id_extend_item" value="<?php echo $id_extend_item;?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Submit request</button>
            </div>
		</div>
	</form>
</div>
<script>
	$(function(){
		$('.textcounter_extend-request').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});
	var create_extend_request = function(form){
		var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>extend/ajax_operation/create_request',
			data: $form.serialize(),
			beforeSend: function(){ showLoader($wrapper); },
			dataType: 'json',
			success: function(resp){
                if(resp.mess_type == 'success'){
                    create_extend_request_callback(resp);
                    closeFancyBox();
                }
				hideLoader($wrapper);
				systemMessages( resp.message, resp.mess_type );
			}
		});
	}
</script>

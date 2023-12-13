<div class="js-modal-flex wr-modal-flex">
	<form class="modal-flex__form validateModal form_processing_status" data-callback="processing_status">
		<div class="modal-flex__content updateValidationErrorsPosition">
			<label class="input-label input-label--required">Select producing stage</label>
			<select name="producing_stages">
				<option value="">Producing stage...</option>
				<option value="Producing stage 1">Producing stage 1</option>
				<option value="Producing stage 2">Producing stage 2</option>
				<option value="Producing stage 3">Producing stage 3</option>
				<option value="Producing stage 4">Producing stage 4</option>
				<option value="Producing stage 5">Producing stage 5</option>
			</select>

			<label class="input-label input-label--required">or write other</label>
			<input type="text" class="validate[required]" name="status_text" placeholder="Write producing stage"/>
			<input type="hidden" name="order" value="<?php echo $order['id'];?>"/>
			<input type="hidden" name="type" value="producing_status"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>
<script>
    $('select[name=producing_stages]').change(function(){
        var status = $(this).val();
        $('input[name=status_text]').val(status);
    });

	var processing_status = function(form){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('order/ajax_order_info');?>',
			data: $form.serialize(),
			beforeSend: function(){ showLoader($wrapper); },
			dataType: 'json',
			success: function(resp){
				hideLoader($wrapper);
                systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					closeFancyBox();
					showOrder(resp.order);
				}
			}
		});
	}
</script>

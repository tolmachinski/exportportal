<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="edit_tracking_info">
		<div class="modal-flex__content updateValidationErrorsPosition">
            <div class="container-fluid-modal">
				<div class="row">

					<?php if(isset($shipper_info) && !empty($shipper_info)){?>
					<div class="col-12 col-md-8">
						<label class="input-label">Shipping company</label>
						<img class="h-25 vam" src="<?php echo $shipper_info['shipper_logo']?>" alt="<?php echo $shipper_info['shipper_name']?>">
						<span class="lh-25"><?php echo $shipper_info['shipper_name']?></span>
					</div>
					<div class="col-12 col-md-4">
					<?php }else{?>
					<div class="col-12">
					<?php }?>

						<label class="input-label">Order number</label>
						<span class="lh-25"><?php echo orderNumber($order_info['id']);?></span>
					</div>
				</div>
            </div>

			<label class="input-label input-label--required">Tracking info</label>
			<textarea class="validate[required,maxSize[1000]] textcounter" data-max="1000" name="track_info" placeholder="Tracking info"><?php echo $order_info['tracking_info'];?></textarea>
            <input type="hidden" name="order" value="<?php echo $order_info['id'];?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
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
	var edit_tracking_info = function(form){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('order/ajax_order_operations/edit_tracking_info');?>',
			data: $form.serialize(),
			beforeSend: function(){ showLoader($wrapper); },
			dataType: 'json',
			success: function(resp){
				if(resp.mess_type == 'success'){
					showOrder(resp.order);
					closeFancyBox();
				}
				hideLoader($wrapper);
				systemMessages( resp.message, resp.mess_type );
			}
		});
	}
</script>

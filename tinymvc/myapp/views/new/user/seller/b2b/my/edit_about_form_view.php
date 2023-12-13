<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerB2bEditAboutFormCallBack"
    >
	   <div class="modal-flex__content">
	   		<label class="input-label">Description block</label>
			<textarea class="validate[maxSize[500] textcounter" name="text" data-max="500" placeholder="Write your text here"><?php echo $seller_b2b[$block_name];?></textarea>
            <input type="hidden" name="block_name" value="<?php echo $block_name;?>"/>
	   </div>
	   <div class="modal-flex__btns">
		    <div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Submit</button>
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

function sellerB2bEditAboutFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'seller_b2b/ajax_seller_b2b_operation/edit_b2b_block',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				callbackEditB2bBlock(resp);
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

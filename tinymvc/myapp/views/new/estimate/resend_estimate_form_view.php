<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal resend-estimate-form inputs-40"
        data-callback="resendEstimateFormCallBack"
    >
		<div class="modal-flex__content">
			<?php if(have_right('sell_item')) { ?>
				<div class="container-fluid-modal">
					<div class="row">
						<div class="col-12 col-md-4">
							<label class="input-label input-label--required">Price/Piece (USD):</label>
							<input class="validate[required,custom[positive_number],min[0.01]]" type="text" name="price" value="<?php echo $estimate['price'];?>" />
						</div>
						<div class="col-12 col-md-4">
							<label class="input-label input-label--required">Total quantity:</label>
							<input class="validate[required,custom[positive_integer],min[1]]" type="text" name="quantity" value="<?php echo (int)$estimate['quantity'];?>"/>
						</div>
						<div class="col-12 col-md-4">
							<label class="input-label input-label--required">Total amount:</label>
							<strong class="txt-nowrap-simple lh-40">$ <span id="total_estimate_price"><?php echo get_price($estimate['price'] * $estimate['quantity'], false);?></span></strong>
						</div>
					</div>
				</div>
			<?php } ?>
			<label class="input-label input-label--required">Message:</label>
			<textarea class="validate[required,maxSize[500]] textcounter" data-max="500" name="message"></textarea>
            <input type="hidden" name="reset_status" value="<?php if(have_right('buy_item')) echo 'wait_buyer'; else echo 'wait_seller';?>" />
            <input type="hidden" name="estimate" value="<?php echo $estimate['id_request_estimate']?>" />
            <input type="hidden" name="status" value="<?php if(have_right('buy_item')) echo 'wait_seller'; else echo 'wait_buyer';?>" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script>
    $(document).ready(function(){
		var onSave = function(form){
			var $form = $(form);
			var data = $form.serializeArray();
			var $wrapper = $form.closest('.js-modal-flex');
			var url = __site_url + 'estimate/ajax_estimate_operation/resend_estimate';
			var onSendRequest = function() {
				$form.find('button[type="submit"]').prop('disabled', true);
				showLoader($wrapper);
			};
			var onRequestEnd = function() {
				$form.find('button[type="submit"]').prop('disabled', false);
				hideLoader($wrapper);
			};
			var onRequestSuccess = function(data){
				systemMessages( data.message, data.mess_type );
				if(data.mess_type == 'success'){
					$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select estimate.</div>');

					current_page = 1;
					current_status = data.new_status;
					search_keywords = "";

					update_status_counter_active('', current_status);
					loadEstimatesList();
					closeFancyBox();
				}
			};

			onSendRequest();
			$.post(url, data, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
		}

        $('.textcounter').textcounter({
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });

        $('.resend-estimate-form input[name=price], .resend-estimate-form input[name=quantity]').keyup(function(){
            var price = parseFloat($('.resend-estimate-form input[name=price]').val()) || 0.00;
            var quantity = parseInt($('.resend-estimate-form input[name=quantity]').val()) || 0;
            var total_amount = (price*quantity).toFixed(2);
            if(total_amount < 0)
                return false;
            else
                $('.resend-estimate-form #total_estimate_price').html(total_amount);
		});

		window.resendEstimateFormCallBack = onSave;
    });
</script>

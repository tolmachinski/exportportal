<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="resend_offer_form" autocomplete="off">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-4">
						<label class="input-label input-label--required">Unit price ($, USD)</label>
						<input class="validate[required,custom[positive_number]]" data-prompt-position="bottomLeft:0" maxlength="12" type="text" name="price" value="<?php echo $offer['unit_price'];?>" />
					</div>
					<div class="col-12 col-md-4">
						<label class="input-label input-label--required">Quantity (<?php echo $item_info['unit_name'];?>)</label>
						<input class="validate[required,min[<?php echo $item_info['min_sale_q']?>],max[<?php echo $item_info['quantity']?>]]" maxlength="12" type="text" name="quantity" value="<?php echo (int)$offer['quantity'];?>"/>
					</div>
					<div class="col-12 col-md-4">
						<label class="input-label">Total offer price</label>
						<span class="js-offer-amount-total lh-40"><?php echo get_price($offer['new_price']);?></span>
					</div>
				</div>

				<label class="input-label input-label--required">Message</label>
				<textarea class="validate[required,maxSize[500]] textcounter" data-max="500" name="message"></textarea>
			</div>
            <input type="hidden" name="offer" value="<?php echo $offer['id_offer']?>" />
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
        $('.textcounter').textcounter({
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });

        $('.modal-flex__content input[name="price"], .modal-flex__content input[name="quantity"]').on('keyup', function(){
			calculate_offer_amount();
		});
    });

	function calculate_offer_amount(){
		var item_price = parseFloat($('.modal-flex__content').find('input[name="price"]').val());
		var item_quantity = parseInt($('.modal-flex__content').find('input[name="quantity"]').val());
		var offer_price = parseFloat(item_price * item_quantity).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

		if(offer_price === 'NaN'){
			offer_price = 0;
		}

		$('.js-offer-amount-total').text('$'+offer_price);
	}

	var resend_offer_form = function(form){
		var $form = $(form);
        var fdata = $form.serialize();
        var $wrapper = $form.closest('.js-modal-flex');
		$form.find('button[type="submit"]').prop('disabled', true);

		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>offers/ajax_offers_operation/resend_offer',
            data: fdata,
            beforeSend: function(){ showLoader($wrapper); },
            dataType: 'json',
            success: function(resp){
                systemMessages( resp.message, resp.mess_type );
                hideLoader($wrapper);
				$form.find('button[type="submit"]').prop('disabled', false);

                if(resp.mess_type == 'success'){
                    $('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select offer.</div>');

                    current_status = '<?php if(have_right('buy_item')) echo 'wait_seller'; else echo 'wait_buyer';?>';
                    current_page = 1;
                    search_keywords = "";

                    loadOffersList();
					closeFancyBox();

					update_status_counter_active("<?php if(have_right('buy_item')) echo 'Waiting for the seller'; else echo 'Waiting for the buyer';?>", current_status);
                }
            }
        });
	}
</script>

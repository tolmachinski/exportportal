<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="js-item-offer-form"
        class="modal-flex__form validateModal"
        data-callback="offerFormCallBack"
    >
		<div id="js-modal-offer-content" class="modal-flex__content">

			<?php app()->view->display('new/item/modal_product_detail_view');?>
            <?php app()->view->display('new/item/modal_variants_view');?>

            <div class="form-group">
                <label class="input-label">Total offer price</label>
                <span class="js-offer-amount-total">$ 0.00</span>
            </div>

			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<div class="js-item-quantity">
							<label class="input-label input-label--required">
								Quantity (<?php echo $item['product_unit_type']['unit_name'];?>)
							</label>
							<input
                            type="number"
                            name="quantity"
                            class="validate[required,custom[positive_integer],min[<?php echo $item['min_sale_q'];?>],max[<?php echo $availableQuantity;?>]]" value="<?php echo $item['min_sale_q'];?>"/>
							<div class="pt-5 fs-14 txt-gray">min <?php echo $item['min_sale_q'];?>, max <?php echo $availableQuantity;?></div>
						</div>
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Offer (price for one piece, USD)</label>
						<input type="number" name="price" step="0.01" maxlength="12" class="validate[required,custom[positive_number],min[0.01],max[9999999999.99]]" placeholder="Enter your offer price, USD"/>
					</div>
				</div>
			</div>

            <div class="form-group">
                <label class="input-label input-label--required">Offer expires in</label>
                <select class='validate[required,min[1],max[14]]' name="days">
                    <option value="1" selected>1 day</option>
                    <option value="2">2 days</option>
                    <option value="3">3 days</option>
                    <option value="5">5 days</option>
                    <option value="7">7 days</option>
                    <option value="10">10 days</option>
                    <option value="14">14 days</option>
                </select>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">Comment</label>
                <textarea name="comments" class="validate[required,maxSize[1000]] js-input-textcounter" placeholder="Enter the comment" data-max="1000"></textarea>
            </div>

			<div class="txt-gray mt-15 fs-12">
				Upon clicking 'Make offer', I acknowledge I have read and agreed to
				<a href="<?php echo __SITE_URL; ?>terms_and_conditions/tc_make_offer" target="_blank">Terms and Conditions</a>
			</div>

            <input type="hidden" name="item" value="<?php echo cleanOutput($item['id']); ?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Make offer</button>
			</div>
		</div>
	</form>

	<div class="modal-flex__wr-actions" style="display: none">
		<div class="modal-flex__actions">
			<div class="ep-medium-text mb-15">
				<p>
					The "Make offer" request has been successfully sent. You may discuss about this Offer with the seller by clicking "Go to offers" button or you can continue shopping.
				</p>
			</div>

			<a class="btn btn-primary call-function" data-callback="closeFancyBox">Continue shopping</a>
			<a class="btn btn-primary" href="<?php echo __SITE_URL . 'offers/my';?>">Go to offers</a>
		</div>
	</div>
</div>

<script>
	$(function(){
		var form = $('#js-item-offer-form');
		var priceRelatedFields = $('#js-item-offer-form input[name="price"], #js-item-offer-form input[name="quantity"]');
		var onSaveContent = function (formElement) {
			var url = __site_url + 'offers/ajax_offers_operation/add';
			var form = $(formElement);
			var data = form.serializeArray();
			var wrapper = form.closest('.js-modal-flex');
			var submitButton = form.find('button[type="submit"]');
			var onSendRequest = function() {
				submitButton.prop('disabled', true);
				showLoader(wrapper);
			};
			var onRequestEnd = function() {
				submitButton.prop('disabled', false);
				hideLoader(wrapper);
			};
			var onRequestSuccess = function(data){
				if (data.mess_type == 'success') {

                    $.fancybox.close();

                    open_result_modal({
                        title: "Success!",
                        subTitle: data.message || null,
						type: "success",
						closable: true,
                        buttons: [
							{
                                label: translate_js({ plug: "general_i18n", text: "js_bootstrap_dialog_send_offer" }),
                                cssClass: "btn btn-primary",
                                action: function (dialog) {
                                    location.href = __site_url + "offers/my";
                                }
                            },
                            {
                                label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                                cssClass: "btn btn-light",
                                action: function (dialog) {
                                    dialog.close();
                                }
                            }
                        ]
                    });
				}else{
                    systemMessages(data.message, data.mess_type);
                }
			};

			onSendRequest();
			$.post(url, data, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
		};
		var calculateOfferAmount = function (form) {
			var price = form.find('input[name="price"]').val() || null;
			var quantity = form.find('input[name="quantity"]').val() || null;
			if (null === price || null === quantity) {
				form.find('.js-offer-amount-total').text('$' + get_price(0, false));

				return;
			}

			form.find('.js-offer-amount-total').text('$' + get_price(parseFloat(price) * parseFloat(quantity), false));
		};
		var getQuantityForOffer = function (form) {
			var quantity = $('#js-quantity-order').val() || null;
			if(null !== quantity) {
				form.find('.js-item-quantity input').val(quantity);
			}
		};

		getQuantityForOffer(form);
		form.find('.js-input-textcounter').textcounter({
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
		priceRelatedFields.on('keyup', calculateOfferAmount.bind(null, form));

		mix(window, { offerFormCallBack: onSaveContent }, false);
	});
</script>

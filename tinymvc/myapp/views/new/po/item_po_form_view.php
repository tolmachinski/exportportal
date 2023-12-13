<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="js-item-po-form"
        class="modal-flex__form validateModal"
        data-callback="poFormCallBack"
    >
		<div class="modal-flex__content">
			<?php app()->view->display('new/item/modal_product_detail_view');?>
            <?php app()->view->display('new/item/modal_variants_view');?>

            <div class="form-group js-item-quantity">
                <label class="input-label input-label--required">
                    Quantity (<?php echo $item['product_unit_type']['unit_name'];?>)
                </label>
                <input type="text" name="quantity" class="validate[required,custom[positive_integer],min[<?php echo $item['min_sale_q'];?>],max[<?php echo $availableQuantity;?>]]" value="<?php echo $item['min_sale_q'];?>"/>
                <div class="pt-5 fs-14 txt-gray"><?php echo "min {$item['min_sale_q']}, max {$availableQuantity}";?></div>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">The necessary changes</label>
                <textarea name="changes" class="validate[required,maxSize[1000]] js-input-textcounter" placeholder="Enter the changes" data-max="1000"></textarea>
            </div>

            <div class="form-group">
                <label class="input-label">Comment</label>
                <textarea name="comment" class="validate[maxSize[1000]] js-input-textcounter" placeholder="Enter the comment" data-max="1000"></textarea>
			</div>

            <input type="hidden" name="item" value="<?php echo $item['id'];?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Send</button>
			</div>
		</div>
	</form>

	<div class="modal-flex__wr-actions" style="display: none">
		<div class="modal-flex__actions">
			<div class="ep-medium-text mb-15">
				<p>
					The <strong>Producing Request</strong> has been successfully sent. You may discuss about this Producing Request with the seller by clicking <strong>Go to Producing Request</strong> button or you can Continue shopping.
				</p>
			</div>

			<a class="btn btn-primary call-function" data-callback="closeFancyBox">Continue shopping</a>
			<a class="btn btn-primary" href="<?php echo __SITE_URL . 'po/my';?>">Go to Producing Request</a>
		</div>
	</div>
</div>

<script>
	$(function(){
		var form = $('#js-item-po-form');
		var onSaveContent = function (formElement) {
			var url = __site_url + 'po/ajax_po_operation/create_po';
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
                    open_result_modal({
                        title: "Success!",
                        subTitle: data.message,
						type: "success",
						closable: true,
                        buttons: [
							{
                                label: translate_js({ plug: "general_i18n", text: "js_bootstrap_dialog_view_requests" }),
                                cssClass: "btn btn-primary",
                                action: function (dialog) {
                                    location.href = __site_url + "po/my";
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
					$.fancybox.close();
				} else{
					systemMessages(data.message, data.mess_type);
				}
			};

			onSendRequest();
			$.post(url, data, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
		};
		var getQuantityForPO = function (form) {
			var quantity = $('#js-quantity-order').val() || null;
			if(null !== quantity) {
				form.find('.js-item-quantity input').val(quantity);
			}
		};

		getQuantityForPO(form);
		form.find('.js-input-textcounter').textcounter({
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });

		mix(window, { poFormCallBack: onSaveContent }, false);
	});
</script>

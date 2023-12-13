<div class="wr-modal-flex" id="add-dispute--formwrapper">
    <form
        id="add-dispute--form"
        class="modal-flex__form validateModal"
        data-callback="disputesAddDisputeFormCallBack"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" value="<?php echo $fileupload['directory']; ?>" name="upload_folder" />
        <input type="hidden" value="<?php echo $order_id?>" name="order_id" />
        <?php if ($item_id)  { ?>
            <input type="hidden" value="<?php echo $item_id; ?>" name="ordered_id" />
        <?php } ?>

		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
                    <div class="col-12 col-md-4">
                        <label class="input-label">Order</label>
                        <?php echo orderNumber($order_id)?>
                    </div>

                    <?php if (!empty($ordered_item)) { ?>
                        <div class="col-12 col-md-4">
                            <label class="input-label">Item price</label>
                            $ <?php echo get_price($final_amount, false);?>
                        </div>
                    <?php } else { ?>
                        <div class="col-12 col-md-4">
                            <label class="input-label">Order price</label>
                            $ <?php echo get_price($order_detail['final_price'], false);?>
                        </div>
                    <?php } ?>

                    <div class="col-12 col-md-4">
                        <label class="input-label">Shipping price ($, USD)</label>
                        $ <?php echo get_price($order_detail['ship_price'], false);?>
                    </div>

					<?php if (!empty($ordered_item)) { ?>
                        <div class="col-12">
                            <label class="input-label">Item</label>
                            <a href="<?php echo __SITE_URL;?>items/ordered/<?php echo strForURL($ordered_item['title']) . '-' . $ordered_item['id_ordered_item'];?>" target="_blank">
                                <?php echo $ordered_item['title'];?>
                            </a> x <?php echo $ordered_item['quantity_ordered']?>
                        </div>
					<?php } ?>

					<div class="col-12">
                        <label class="input-label input-label--required">Comment</label>
                        <textarea name="comment"
                            id="add-dispute--formfield--comment"
                            class="validate[required,maxSize[1000]] textcounter-dispute_comment"
                            placeholder="Enter the comment"
                            data-max="1000"
                        ></textarea>
					</div>

					<div class="col-12">
                        <label class="input-label input-label--required">Refund money</label>
                        <div class="flex-display flex-w--w">
                            <div class="w-50 flex-display flex-d--c lh-20">
                                <label class="mb-0 cur-pointer custom-radio mt-4">
                                    <input type="radio"
                                        id="add-dispute--formfield--refund-option-yes"
                                        name="refund_money"
                                        class="radio add-dispute--formfield--refund-option"
                                        value="1"
                                        checked>
                                    <span class="custom-radio__text">Yes</span>
                                </label>
                                <label class="mb-0 cur-pointer custom-radio">
                                    <input type="radio"
                                        id="add-dispute--formfield--refund-option-no"
                                        name="refund_money"
                                        class="radio add-dispute--formfield--refund-option"
                                        value="0">
                                    <span class="custom-radio__text">No</span>
                                </label>
                            </div>
                            <input type="text"
                                name="money_count"
                                id="add-dispute--formfield--money"
                                class="validate[max[<?php echo $money_formatter->format($final_amount); ?>], custom[positive_number]] money w-100pr mt-14"
                                placeholder="Enter the amount"
                                value="<?php echo $money_formatter->format($final_amount); ?>">
                        </div>
					</div>

					<div class="col-12">
                        <label class="input-label">Video</label>
                        <input type="text"
                            id="add-dispute--formfield--video"
                            class="form-control"
                            placeholder="Enter the Youtube/Vimeo URL"
                            name="video_link"/>
					</div>

					<?php views()->display('new/disputes/my/partials/uploader_view', array('fileupload' => $fileupload)); ?>
				</div>
			</div>
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
		var onSaveContent = function(url, wrapper, formElement) {
            if (null === url) {
                return;
            }

			var form = $(formElement);
			var data = form.serializeArray();
			var submitButton = form.find('button[type="submit"]');
			var normalizeAmount = function() {
				if(form.find('.radio:checked').val() == 0) {
					form.find('.money').val('0');
				}
			};
			var onSendRequest = function() {
				submitButton.prop('disabled', true);
				showLoader(wrapper);
			};
			var onRequestEnd = function() {
				submitButton.prop('disabled', false);
				hideLoader(wrapper);
			};
			var onRequestSuccess = function(data){
				systemMessages(data.message, data.mess_type);
				if(data.mess_type == 'success'){
					callFunction('showOrder', data.order);
					closeFancyBox();
				}
			};

			normalizeAmount();
			onSendRequest();
			$.post(url, data, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
		};
		var onChangeMoneyFlag = function(){
			var self = $(this);
			var money = $('#add-dispute--formfield--money');
			if(self.attr("value") == 1){
				money.show();
			} else{
				money.hide();
			}
		};

        var form = $('#add-dispute--form');
        var wrapper = $('#add-dispute--formwrapper');
        var comment = $('#add-dispute--formfield--comment');
        var refund = $('.add-dispute--formfield--refund-option');
        var url = form.attr('action') || null;

		refund.click(onChangeMoneyFlag);
		comment.textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		window.disputesAddDisputeFormCallBack = onSaveContent.bind(null, url, wrapper);
	});
</script>

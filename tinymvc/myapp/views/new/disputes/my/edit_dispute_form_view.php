<div class="js-modal-flex wr-modal-flex inputs-40" id="edit-dispute--formwrapper">
	<form
        id="edit-dispute--form"
        class="modal-flex__form validateModal"
        data-callback="disputesEditDisputeFormCallBack"
        action="<?php echo $action; ?>"
    >
		<input type="hidden" name="dispute" value="<?php echo $dispute['id']; ?>"/>
		<input type="hidden" value="<?php echo $fileupload['directory']; ?>" name="upload_folder" />
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label">Order</label>
						<?php echo orderNumber($dispute['id_order']);?>
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label">Date</label>
						<?php echo formatDate($dispute['date_time']);?>
					</div>

					<?php if (isset($ordered_item)){?>
						<div class="col-12">
							<label class="input-label">Item</label>
							<a href="<?php echo __SITE_URL;?>items/ordered/<?php echo strForURL($ordered_item['title']) . '-' . $ordered_item['id_ordered_item'];?>" target="_blank">
								<?php echo $ordered_item['title'];?>
							</a> x <?php echo $ordered_item['quantity_ordered']?>
						</div>
					<?php }?>

					<?php if (is_my($dispute['id_buyer'])) { ?>
						<div class="col-12">
							<label class="input-label input-label--required">Refund money</label>
							<div class="flex-display">
								<div class="pl-5 w-70 flex-display flex-d--c lh-20">
									<label class="mb-0 cur-pointer">
										<input type="radio"
											name="refund_money"
											id="edit-dispute--formfield--refund-option-yes"
											class="radio edit-dispute--formfield--refund-option"
											value="1"
											<?php echo (($dispute['money_back_request'] != 0) ? 'checked="checked"' : '')?>> Yes
									</label>
									<label class="mb-0 cur-pointer">
										<input type="radio"
											name="refund_money"
											id="edit-dispute--formfield--refund-option-no"
											class="radio edit-dispute--formfield--refund-option"
											value="0"
											<?php echo (($dispute['money_back_request'] != 0) ? '' : 'checked="checked"')?>> No
									</label>
								</div>
								<input type="text"
									name="money_count"
									id="edit-dispute--formfield--money"
									class="validate[max[<?php echo $dispute['max_price']?>], custom[positive_number]] money ml-20 flex--1"
									value="<?php echo (isset($dispute['money_back_request']) ? $dispute['money_back_request'] : $dispute['max_price'])?>"
									placeholder="Enter the amount"
									<?php echo (($dispute['money_back_request'] != 0) ? '' : 'style="display: none;"')?>>
							</div>
						</div>
					<?php } ?>

					<div class="col-12">
						<label class="input-label input-label--required">Notice</label>
						<textarea
							name="notice"
							placeholder="Enter the notice"
							id="edit-dispute--formfield--notice"
							class="validate[required,maxSize[500]] textcounter-dispute_notice"
							data-max="500"
						></textarea>
					</div>

					<div class="col-12">
						<label class="input-label">Video</label>
						<input type="text"
							name="video_link"
							placeholder="Enter the Youtube/Vomeo URL"
							id="edit-dispute--formfield--video"
							class="validate[maxSize[200]]"/>
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
					callFunction('callbackEditDispute');
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
			var money = $('#edit-dispute--formfield--money');
			if(self.attr("value") == 1){
				money.show();
			} else{
				money.hide();
			}
		};

        var form = $('#edit-dispute--form');
        var wrapper = $('#edit-dispute--formwrapper');
        var notice = $('#edit-dispute--formfield--notice');
        var refund = $('.edit-dispute--formfield--refund-option');
        var url = form.attr('action') || null;

		refund.click(onChangeMoneyFlag);
		notice.textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		window.disputesEditDisputeFormCallBack = onSaveContent.bind(null, url, wrapper);
	});
</script>

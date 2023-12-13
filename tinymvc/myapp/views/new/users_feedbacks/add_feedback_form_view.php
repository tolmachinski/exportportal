<div class="js-modal-flex wr-modal-flex inputs-40" id="add-feedback-form--wrapper">
    <form
        id="add-feedback-form"
        class="modal-flex__form validateModal"
        data-callback="usersFeedbacksAddFeedbackFormCallBack"
    >
	   <div class="modal-flex__content feedback-popup">
	   		<div class="ep-tinymce-text">
			   <?php echo translate('feedback_form_feedback_description');?>
			</div>

			<label class="input-label"><?php echo translate('feedback_form_what_was_ordered');?></label>
			<?php if(count($user_ordered_for_feedback) == 1){?>
				<div class="lh-18"><a
						data-order="<?php echo $user_ordered_for_feedback[0]['id_order'];?>"
						target="_blank"
						id="add-feedback-form--formaction--show-items"
					><?php echo translate('feedback_form_order_number', array('{{ORDER_NUMBER}}' => orderNumber($user_ordered_for_feedback[0]['id_order'])));?></a></div>

				<input
                    class="js-feedback-order-input"
                    type="hidden"
                    name="order"
                    value="<?php echo $user_ordered_for_feedback[0]['id_order'];?>"
                >
			<?php } else{?>
				<div class="feedback-popup__bought-select">
					<div class="wr-select-buy-b">
						<select class="select-buy-b" name="order" id="add-feedback-form--formfield--order-list">
							<option value="0"><?php echo translate('feedback_form_select_order_placeholder');?></option>
							<?php foreach($user_ordered_for_feedback as $item){?>
								<option value="<?php echo $item['id_order'];?>"><?php echo translate('feedback_form_order_number', array('{{ORDER_NUMBER}}' => orderNumber($item['id_order'])));?></option>
							<?php }?>
						</select>
					</div>
				</div>
			<?php }?>

			<div class="feedback-popup__items"></div>

			<label class="input-label input-label--required"><?php echo translate('feedback_form_click_to_rate_label');?></label>
			<div class="feedback-popup__rating pb-0">
				<input id="rating-feedback" class="rating-tooltip" data-filled="ep-icon ep-icon_diamond txt-green fs-30" data-empty="ep-icon ep-icon_diamond txt-gray-light fs-30" type="hidden" name="rating" value="0">
			</div>

			<div class="feedback-popup__middle mt-0 clearfix" id="user_services_block">
				<?php if(!empty($services)){?>
					<?php echo $services;?>
				<?php }?>
			</div>

			<label class="input-label input-label--required"><?php echo translate('feedback_form_title_label');?></label>
			<input class="validate[required,maxSize[200]]" type="text" name="title" maxlength="200" placeholder="<?php echo translate('feedback_form_title_placeholder', null, true);?>"/>

			<label class="input-label input-label--required"><?php echo translate('feedback_form_comment_label');?></label>
			<textarea name="description" id="add-feedback-form--formfield--description" class="validate[required,maxSize[1000]] textcounter-feedback_description" data-max="1000" placeholder="<?php echo translate('feedback_form_content_placeholder', null, true);?>"></textarea>
	   </div>
	   <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                    <button class="btn btn-primary" type="submit"><?php echo translate('feedback_form_submit_btn');?></button>

                    <?php if(isset($in_modal)){ ?>
                        <button class="btn btn-default" type="reset"><?php echo translate('feedback_form_cancel_btn');?></button>
                    <?php }?>
            </div>
	   </div>
   </form>
</div>
<script>
    var showItems = function(orderId) {
        var url = __site_url + 'feedbacks/ajax_feedback_operation/items_list';
        var wrapper = $('#add-feedback-form--wrapper');
        var onRequestSuccess = function(response) {
            if(response.mess_type === 'success') {
                $('.feedback-popup__items').html(response.content);
            } else {
                systemMessages(response.message, response.mess_type );
            }
        };
        var onRequestEnd = function() {
            hideLoader(wrapper);
        };

        if(null !== orderId && '0' !== orderId) {
            showLoader(wrapper);
            $.post(url, { order: orderId }, null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError)
                .always(onRequestEnd);
        }
    };

	$(function() {
		var onSave = function(form) {
			var self = $(form);
			var data = self.serializeArray();
			var url = __site_url + 'feedbacks/ajax_feedback_operation/add_feedback';

			var onRequestStart = function() {
				self.find('button[type=submit]').addClass('disabled');
				showLoader(wrapper);
			};
			var onRequestSuccess = function(response) {
				systemMessages(response.message, response.mess_type);
				if(response.mess_type == 'success'){
					callFunction('addFeedbackCallback', response);
					closeFancyBox();
				}
			};
			var onRequestEnd = function() {
				self.find('button[type=submit]').removeClass('disabled');
				hideLoader(wrapper);
			};

			onRequestStart();
			$.post(url, data, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
		};
		var onRate = function () {
			ratingBootstrap($(this));
		};
		var onExtend = function (rate) {
			$(this).attr('title', ratingBootstrapStatus(rate));
		};
		var onSlide = function(event, ui) {
			var slider = $(ui.handle).closest('.rating-slider')
			var service = slider.data('service');
			var ratingItem = $('#rating-service-' + service);

			ratingItem.val(ui.value);
			slider.next('.feedback-popup__table-status').text(ratingBootstrapStatus(ui.value));
		};
		var onChooseOrder = function() {
			var self = $(this);
			var order = self.val() || null;
			var url = __site_url + 'feedbacks/ajax_feedback_operation/get_poster_services';
			var onRequestSuccess = function(response) {
				if(response.mess_type == 'success'){
					serviceBlock.html(response.services);
					$('.rating-tooltip').rating(ratingOptions);
					$('.rating-slider').slider(sliderOptions);

                    showItems(order);
				} else {
					systemMessages(response.message,response.mess_type );
				}
			};
			var onRequestEnd = function() {
				hideLoader(wrapper);
			};

			if(null !== order && '0' !== order && order > 0) {
				showLoader(wrapper);
				$.post(url, { order: order }, null, 'json')
					.done(onRequestSuccess)
					.fail(onRequestError)
					.always(onRequestEnd);
			} else {
				serviceBlock.html('');
			}
		};
		var formatOrder = function (order) {
			return order.text;
		};

		var rating = $('#rating-feedback');
		var wrapper = $('#add-feedback-form--wrapper');
		var orderList = $('#add-feedback-form--formfield--order-list');
		var description = $('#add-feedback-form--formfield--description');
		var serviceBlock = $('#user_services_block');
		var counterOptions = {
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		};
		var ratingOptions = {
			extendSymbol: onExtend
		};
		var sliderOptions = {
			range: "min",
			min: 0,
			max: 5,
			slide: onSlide
		}
		var selectOptions = {
			width: '100%',
			height: 40,
			minimumResultsForSearch: -1,
			templateResult: formatOrder,
			templateSelection: formatOrder,
			escapeMarkup: function(m) { return m; }
		};

		rating.rating(ratingOptions);
		orderList.select2(selectOptions);
		description.textcounter(counterOptions);

		rating.on('change', onRate);
		orderList.on('select2:select', onChooseOrder);

		<?php if(!empty($services)){?>
			$('.rating-tooltip').rating(ratingOptions);
			$('.rating-slider').slider(sliderOptions);
		<?php }?>

        <?php if (count($user_ordered_for_feedback) == 1){ ?>
            showItems($(".js-feedback-order-input").val());
        <?php } ?>

		window.usersFeedbacksAddFeedbackFormCallBack = onSave;
	});
</script>

<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="sellerInviteExternalReviewFormCallBack"
    >
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12">
						<label class="input-label"><?php echo translate('invite_external_review_form_what_was_ordered');?></label>
						<div class="row">
							<div class="col-12 col-md-6">
								<div class="wr-select-buy-b">
									<select name="item" class="select-buy-b">
										<option data-image="" data-item="javascript:void(0);"><?php echo translate('invite_external_review_form_select_item_placeholder');?></option>
										<?php foreach($items as $item){?>
											<?php
												$item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
											?>
											<option
												value="<?php echo $item['id'];?>"
												data-item="<?php echo makeItemUrl($item['id'], $item['title']);?>"
												data-image="<?php echo $item_img_link; ?>"
												data-title="<?php echo $item['title'];?>"
											><?php echo $item['title'];?></option>
										<?php }?>
									</select>
								</div>
							</div>
							<div class="col-12 col-md-6">
								<a class="feedback-popup__view-item pl-0" id="select-link-item" href="javascript:void(0);" target="_blank"><?php echo translate('invite_external_review_form_link_to_product_detail_page');?></a>
							</div>
						</div>
					</div>

					<div class="col-12">
						<label class="input-label input-label--required"><?php echo translate('invite_external_review_form_rating_label');?></label>
						<div class="feedback-popup__rating pb-0">
							<input id="rating-review" class="rating-tooltip" data-filled="ep-icon ep-icon_star txt-orange fs-30" data-empty="ep-icon ep-icon_star-empty txt-orange fs-30" type="hidden" name="review_rating" value="0">
						</div>
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('invite_external_review_form_full_name_label');?></label>
						<input type="text" class="validate[required, maxSize[150]]" name="full_name" placeholder="<?php echo translate('invite_external_review_form_full_name_placeholder', null, true);?>">
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required"><?php echo translate('invite_external_review_form_email_label');?></label>
						<input type="text" class="validate[required, custom[noWhitespaces],custom[emailWithWhitespaces]]" name="email" placeholder="<?php echo translate('invite_external_review_form_email_placeholder');?>">
					</div>

					<div class="col-12">
						<label class="input-label input-label--required"><?php echo translate('invite_external_review_form_message_label');?></label>
						<textarea class="validate[required, maxSize[200] textcounter-reviews_description" name="description_review" placeholder="<?php echo translate('invite_external_review_form_message_placeholder', null, true);?>"></textarea>
					</div>
				</div>
			</div>
            <input type="hidden" name="company" value="<?php echo $company['id_company'];?>">
            <input type="hidden" name="code" value="<?php echo $code;?>">
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit"><?php echo translate('invite_external_review_form_submit_btn');?></button>
			</div>
		</div>
	</form>
</div>

<script>
$(document).ready(function(){
	$('.textcounter-reviews_description').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});

	var selectBuyParams = {
		width: '100%',
		height: 40,
		templateResult: format,
		templateSelection: format,
		escapeMarkup: function(m) { return m; }
	}

	if($(window).width() < 768){
		selectBuyParams.minimumResultsForSearch = -1;
	}

	$(".select-buy-b").select2(selectBuyParams)
	.on('select2:select', function(e){
		var $selectLinkItem = document.querySelector("#select-link-item");

		if( $(".select-buy-b option:selected").val() == '<?php echo translate('invite_external_review_form_select_item_placeholder', null, true);?>'){
			$selectLinkItem.removeAttribute('target');
		}else{
			$selectLinkItem.setAttribute('target', '_blank');
		}

		$selectLinkItem.setAttribute('href',$(".select-buy-b option:selected").data('item') );
	});

	$('.rating-tooltip').rating({
		extendSymbol: function (rate) {
			$(this).attr('title',ratingBootstrapStatus(rate));
		}
	});

	$('#rating-review').on('change', function () {
		var $this = $(this);
		ratingBootstrap($this);
	});
});

function format(state) {
	originalOption = state.element;
	if(!state.id){
		return state.text;
	}

	if($(originalOption).data('image') != ''){
		return 	'<div class="select-buy-dropdown">\
					<div class="select-buy-dropdown__img image-card3">\
						<span class="link">\
							<img class="image" src="'+$(originalOption).data('image')+'">\
						</span>\
					</div>\
					<span class="select-buy-dropdown__title">'+$(originalOption).data('title')+'</span>\
				</div>';
	} else{
		return state.text;
	}
}

function sellerInviteExternalReviewFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: __site_url + 'external_feedbacks/ajax_operations/add_review',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			if(resp.mess_type == 'success'){
				window.location.href = '<?php echo getCompanyURL($company);?>';
			}else{
				systemMessages( resp.message, 'message-' + resp.mess_type );
				$form.find('button[type=submit]').removeClass('disabled');
				hideLoader($wrform);
			}
		}
	});
}
</script>

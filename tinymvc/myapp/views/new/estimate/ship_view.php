<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="start_order_by_estimate">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Country</label>
						<select class="validate[required] country" id="country" name="port_country">
							<?php echo getCountrySelectOptions($port_country, $user_info['country']);?>
						</select>
					</div>
					<div class="col-12 col-md-6" id="state_td">
						<label class="input-label input-label--required">State / Region</label>
						<select name="states" id="states" class="validate[required] states">
							<option value="">Select state / region</option>
							<?php if(isset($states) && !empty($states)){ ?>
								<?php foreach($states as $state){?>
									<option value="<?php echo $state['id'];?>" <?php echo selected($user_info['state'], $state['id']);?>><?php echo $state['state'];?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-6 wr-select2-h35" id="city_td">
						<label class="input-label input-label--required">City</label>
						<select name="port_city" id="port_city" class="validate[required] select-city">
							<option value="">Select country first</option>
							<?php if(isset($city_selected) && !empty($city_selected)){ ?>
								<option value="<?php echo $city_selected['id'];?>" selected>
									<?php echo $city_selected['city'];?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Zip</label>
						<input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php echo $user_info['zip']?>"/>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Address</label>
						<input class="validate[required,maxSize[250]]" maxlength="250" type="text" name="address" value="<?php echo $user_info['address']?>"/>
					</div>
				</div>
			</div>
            <input type="hidden" name="id_estimate" value="<?php echo $id_estimate;?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Confirm</button>
			</div>
		</div>
	</form>
</div>

<script>
	var $selectCity;
	var selectState = "<?php echo $user_info['state'];?>";

	function start_order_by_estimate(form){
		var $form = $(form);
		var $wrform = $form.closest('.js-modal-flex');
		$form.find('button[type="submit"]').prop('disabled', true);

		$.ajax({
			url: '<?php echo __SITE_URL; ?>estimate/ajax_estimate_operation/create_order',
			type: 'POST',
			data:  $form.serialize(),
			dataType: 'json',
			beforeSend: function(){
				showLoader($wrform);
			},
			success: function(data){
				$form.find('button[type="submit"]').prop('disabled', false);

				if(data.mess_type == 'success'){
					current_status = 'initiated';
					current_page = 1;

					$('.wr-orders-detail-b').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Select estimate.</div>');
					update_status_counter_active('Order initiated', current_status);
					loadEstimatesList();
					closeFancyBox();
					openNewOrderNotificationModal(data.order, data.message);
				} else {
					systemMessages(data.message, data.mess_type);
					hideLoader($wrform);
				}
			}
		});
	}

	$(document).ready(function(){
		$selectCity = $(".select-city");

		initSelectCity($selectCity);

		$('body').on('change', "select#states", function(){
			selectState = this.value;
			$selectCity.empty().trigger("change").prop("disabled", false);

			if(selectState != '' || selectState != 0){
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
			} else{
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
				$selectCity.prop("disabled", true);
			}
			$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
		});

		$('body').on('change', "#country", function(){
			selectCountry($(this), 'select#states');
			selectState = 0;
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

		$('#request_insurance_note--textcounter').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		$('select[name="request_insurance"]').on('change', function(){
			var request_insurance = $(this).val();

			$('.js-insurance__field').hide();
			if(request_insurance == 'yes'){
				$('.js-insurance__field').show();
			}

			$.fancybox.update();
		});
	});

</script>

<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="add_ship_address">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Country</label>
						<select class="validate[required]" id="country" name="port_country">
							<?php echo getCountrySelectOptions($port_country, $shipping['country']);?>
						</select>
					</div>
					<div class="col-12 col-md-6" id="state_block">
						<label class="input-label input-label--required">State or province</label>

						<select class="validate[required]" name="states" id="states">
							<option value="">Select state or province</option>
							<?php if(isset($states) && !empty($states)){ ?>
								<?php foreach($states as $state){?>
									<option value="<?php echo $state['id'];?>" <?php echo selected($shipping['state'], $state['id']);?>><?php echo $state['state'];?></option>
								<?php } ?>
							<?php }?>
						</select>
					</div>
					<div class="col-12 col-md-6 wr-select2-h50" id="city_block">
						<label class="input-label input-label--required">City</label>

						<select class="validate[required] select-city" name="port_city" id="port_city">
							<option value="">Select city</option>
							<?php if(isset($city_selected) && !empty($city_selected)){ ?>
								<option value="<?php echo $city_selected['id'];?>" selected>
									<?php echo $city_selected['city'];?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Zip</label>
						<input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php echo $shipping['zip']?>"/>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Address</label>
						<input class="validate[required]" type="text" name="address" value="<?php echo $shipping['address']?>"/>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Estimate time for packaging (days)</label>
						<?php $timeline_countdowns = json_decode($order['timeline_countdowns'], true);?>
						<div class="input-group">
							<input class="form-control validate[required, min[1]]" type="text" name="packaging" value="<?php if(isset($timeline_countdowns['time_for_packaging'])){echo $timeline_countdowns['time_for_packaging'];};?>" placeholder="e.g. 5"/>
							<div class="input-group-btn">
								<span class="btn btn-primary info-dialog" data-message="The number of the days that the seller can spent on the product packaging." data-title="What is: Estimate time for packaging?">
									<i class="ep-icon ep-icon_info-stroke"></i>
								</span>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Available area for delivering, in km</label>
						<div class="input-group">
							<input class="form-control validate[required, min[0]]" type="text" name="delivery_area" value="<?php echo (int)$order['seller_delivery_area'];?>" placeholder="e.g. 100"/>
							<div class="input-group-btn">
								<span class="btn btn-primary info-dialog" data-message="Maximum distance the seller can deliver the products to the freight forwarder." data-title="What is: Available area for delivering?">
									<i class="ep-icon ep-icon_info-stroke"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<p class="fs-12 mb-0 mt-10">Fields marked with <sup class="required-ico txt-red">*</sup> must be filled out.</p>
            <input type="hidden" name="order" value="<?php echo $id_order;?>"/>
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
	var selectState = <?php echo $user_info['state'];?>;

	$(document).ready(function(){
		$selectCity = $(".select-city");

		initSelectCity($selectCity);

		$("#state_block").on('change', "select#states", function(){
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

		$("#country").on('change', function(){
			selectCountry($(this), 'select#states');
			$selectCity.empty().trigger("change").prop("disabled", true);
		});
	});

	function add_ship_address(form){
		var $form = $(form);
		var $wrform = $form.closest('.js-modal-flex');

		$.ajax({
			url: '<?php echo getUrlForGroup('order/ajax_order_operations/ship_from');?>',
			type: 'POST',
			data:  $form.serialize(),
			dataType: 'json',
			beforeSend: function(){
				showLoader($wrform);
			},
			success: function(data){
				systemMessages(data.message, data.mess_type );
				if(data.mess_type == 'success'){
					showOrder(data.id_order);
					closeFancyBox();
				}else{
					hideLoader($wrform);
				}
			}
		});
	}
</script>

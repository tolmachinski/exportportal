<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<?php if($add_company){?>
				<fieldset class="container-fluid-modal pt-15 pb-15">
					<legend>BUSINESS INFORMATION</legend>
					<div class="row">
						<div class="col-12">
							<label class="input-label input-label--required">Company name</label>
							<input class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]" type="text" name="company_name" placeholder="" value="<?php if(!empty($company)){echo $company['name_company'];}?>">
						</div>
						<div class="col-6">
							<label class="input-label input-label--required">Company type</label>
							<select name="company_type">
								<option value="seller" <?php if(!empty($company) && $company['id_type'] == 2){echo 'selected';}?>>Seller</option>
								<option value="manufacturer" <?php if(!empty($company) && $company['id_type'] == 1){echo 'selected';}?>>Manufacturer</option>
								<option value="distributor" <?php if(!empty($company) && $company['id_type'] == 7){echo 'selected';}?>>Distributor</option>
							</select>
						</div>
						<div class="col-12">
							<label class="input-label input-label--required">Industries</label>
							<select name="industries[]" class="select-groups-list" multiple="multiple">
								<?php foreach($industries as $industry){?>
									<option value="<?php echo $industry['category_id'];?>" <?php if(!empty($company) && in_array($industry['category_id'], $company['industries'])){echo 'selected';}?>><?php echo $industry['name'];?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</fieldset>
			<?php }?>
			<fieldset class="container-fluid-modal pt-15 pb-15">
				<legend>LOCATION INFORMATION</legend>
				<div class="row">
					<div class="col-6">
                        <label class="input-label input-label--required">Country</label>
						<div class="notranslate">
							<select id="country" class="validate[required]" name="country">
								<?php echo getCountrySelectOptions($port_country, empty($user['country']) ? 0 : $user['country']);?>
							</select>
						</div>
					</div>
					<div id="state_td" class="col-6">
						<label class="input-label input-label--required">Region</label>
						<div class="notranslate">
							<select name="states" class="validate[required]" id="country_states">
								<option value=""><?php echo translate('form_placeholder_select2_state');?></option>
								<?php if(!empty($states)){ ?>
									<?php foreach($states as $state){?>
										<option value="<?php echo $state['id'];?>" <?php echo selected($user['state'], $state['id']);?>><?php echo $state['state'];?></option>
									<?php }?>
								<?php }?>
							</select>
						</div>
					</div>
                   	<div class="col-6 wr-select2-h35" id="city_td">
                    	<label class="input-label input-label--required">Town</label>
						<div class="notranslate">
							<select id="port_city" class="select-city validate[required]" name="port_city">
								<option value="">Select country first</option>
								<?php if(!empty($city_selected)){ ?>
									<option value="<?php echo $city_selected['id'];?>" selected><?php echo $city_selected['city'];?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-6">
                    	<label class="input-label">Zip</label>
                        <span class="required-field input-label--required"></span>
                        <input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" placeholder="Zip" value="<?php echo $user['zip'];?>">
                    </div>
					<div class="col-12">
                        <label class="input-label">Address</label>
                        <span class="required-field input-label--required"></span>
                        <input class="validate[required,maxSize[255]]" type="text" name="address" placeholder="Address" value="<?php echo $user['address'];?>">
                    </div>
					<div class="col-6 wr-select2-h35">
						<label class="input-label input-label--required">Code</label>
						<select id="country_code" class="validate[required]" name="country_code">
							<option value=""></option>
							<?php foreach($phone_codes as $phone_code){ ?>
								<option value="<?php echo $phone_code['ccode']?>" <?php echo selected($user['phone_code'], $phone_code['ccode']);?> data-country-flag="<?php echo getCountryFlag($phone_code['country']);?>" data-country-name="<?php echo $phone_code['country']?>" data-country="<?php echo $phone_code['id_country']?>"><?php echo $phone_code['ccode']?> <?php echo $phone_code['country']?></option>
							<?php } ?>
						</select>
                    </div>

					<div class="col-6">
						<label class="input-label input-label--required">Phone</label>
						<input class="validate[required,custom[phoneNumber]]" maxlength="25" type="text" name="phone" placeholder="" value="<?php echo $user['phone'];?>">
                    </div>
				</div>
			</fieldset>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-success" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>
<script>
	var selectState = 0;
	var $selectCity;
	var $selectCcode;

	$(document).ready(function(){
		$selectCity = $(".select-city");
		var $selectGroups = $('.select-groups-list').multipleSelect({
			filter: true,
			width: '100%',
			placeholder: translate_js({plug:'multipleSelect', text: 'placeholder_industries'}),
			selectAllText: translate_js({plug:'multipleSelect', text: 'select_all_text'}),
			allSelected: translate_js({plug:'multipleSelect', text: 'all_selected'}),
			countSelected: translate_js({plug:'multipleSelect', text: 'count_selected'}),
			noMatchesFound: translate_js({plug:'multipleSelect', text: 'no_matches_found'})
		});

		initSelectCity($selectCity);

		$('body').on('change', "select#country_states", function(){
			selectState = this.value;
			$selectCity.empty().trigger("change").prop("disabled", false);

			if(selectState != '' || selectState != 0){
				var select_text = 'Select city';
			} else{
				var select_text = 'Select region first';
				$selectCity.prop("disabled", true);
			}
			$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
		});

		$("#country").change(function(){
			selectCountry($(this), 'select#country_states');
			selectState = 0;
			selectCcode($(this));
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

		$selectCcode = $('select#country_code').select2({
			theme: "default ep-select2-h30",
			templateResult: formatCcode,
			placeholder: "Select country code",
			width: '100%',
			dropdownAutoWidth : true
		});

		$('[data-toggle="popovermodal"]').popover({trigger: 'hover', placement: 'top'});
	});

	function selectCcode(select){
		var country = $(select).val();
		var ccode = $('#country_code option[data-country="'+country+'"]').first().val();
		$selectCcode.val(ccode).trigger('change');
	}

	function modalFormCallBack(form){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');
		var fdata = $form.serialize();
		$.ajax({
			type: 'POST',
			url: 'accreditation/ajax_operations/complete_profile/<?php echo $token;?>',
			dataType: 'JSON',
			data: fdata,
			beforeSend: function(){
				showLoader($wrapper);
			},
			success: function(resp){
				systemMessages(resp.message, 'message-' + resp.mess_type);
				hideLoader($wrapper);
				if(resp.mess_type == 'success'){
					closeFancyBox();
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>


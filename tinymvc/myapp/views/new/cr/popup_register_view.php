<?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions');?>

<script>
	var selectState = 0;
	var $selectCity;

    $(document).ready(function(){
        $selectCity = $(".select-city");
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
            $selectCity.empty().trigger("change").prop("disabled", true);
        });
    });

    var register_ba = function(form){
        googleRecaptchaValidation(recaptcha_parameters, form).then(function(form) {
            var $form = $(form);
            var fdata = $form.serialize();
            $.ajax({
                type: 'POST',
                url: __current_sub_domain_url + 'register/ajax_operations/brand_ambassador',
                data: fdata,
                beforeSend: function () {
                    showLoader($form);
                },
                dataType: 'json',
                success: function (resp) {
                    if(resp.mess_type == 'success'){
                        closeFancyBox();
                    } else{
                        hideLoader($form);
                    }

                    systemMessages(resp.message, resp.mess_type);
                }
            });
        });
        return false;
    }
</script>
<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form pb-0 validateModal" data-callback="register_ba" autocomplete="off">
		<div class="modal-flex__content">
			<div class="clearfix">
				<div class="col-12 col-md-6 pl-0">
					<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_fname');?></label>
					<input class="validate[required,custom[validUserName],minSize[2],maxSize[50]]" type="text"  name="fname" placeholder="<?php echo translate('pre_registration_page_register_form_user_label_fname');?>">
				</div>
				<div class="col-12 col-md-6 pr-0">
					<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_lname');?></label>
					<input class="validate[required,custom[validUserName],minSize[2],maxSize[50]]" type="text"  name="lname" placeholder="<?php echo translate('pre_registration_page_register_form_user_label_lname');?>">
				</div>

				<div class="col-12 col-md-6 pl-0">
					<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_email');?></label>
					<input type="text" class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]" id="email" name="email" placeholder="<?php echo translate('pre_registration_page_register_form_user_label_email');?>" value="<?php echo $email;?>">
				</div>

				<div class="col-12 col-md-6 pr-0">
					<label class="input-label input-label--required"><?php echo translate('pre_registration_page_register_form_user_label_email_confirm');?></label>
					<input type="text" class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100],equals[email]]" name="confirm_email" placeholder="<?php echo translate('pre_registration_page_register_form_user_label_email_confirm');?>" value="<?php echo $email;?>">
				</div>

				<div class="col-12 col-md-6 pl-0">
					<label class="input-label input-label--required"><?php echo translate('form_label_country');?></label>
					<select id="country" class="validate[required]" name="country">
						<?php echo getCountrySelectOptions($port_country);?>
					</select>
				</div>

				<div class="col-12 col-md-6 pr-0">
					<label class="input-label input-label--required"><?php echo translate('form_label_state');?></label>
					<select name="states" class="validate[required]" id="country_states">
						<option value=""><?php echo translate('form_placeholder_select2_country_first');?></option>
					</select>
				</div>

				<div class="col-12 col-md-6 wr-select2-h50 pl-0" id="city_td">
					<label class="input-label input-label--required"><?php echo translate('form_label_city');?></label>
					<select id="port_city" class="select-city validate[required]" name="port_city">
						<option value="" selected disabled><?php echo translate('form_placeholder_select2_country_first');?></option>
					</select>
				</div>

                <div class="col-12 col-md-6 pr-0">
                    <label class="input-label"><?php echo translate('form_label_zip');?></label>
                    <input type="text" class="validate[custom[zip_code],maxSize[20]]" name="zip" placeholder="<?php echo translate('form_label_zip');?>">
                </div>

				<div class="col-12 pl-0 pr-0">
                    <br>
                    <button class="btn btn-block btn-primary" type="submit">Apply</button>
				</div>
				<input type="hidden" name="id_domain" value="<?php echo $id_domain;?>">
			</div>
		</div>
	</form>
</div>

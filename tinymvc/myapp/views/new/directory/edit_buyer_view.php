<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/inputmask-5.x/jquery.inputmask.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/phone-mask/phone-mask-init.js');?>"></script>

<script>
	$(function () {
		//region Functions
		var onCompanyTypeToggle = function () {
			var self = $(this);

			setTimeout(function () {
				if (self.val() == 1 && self.prop('checked')) {
					formWrapper.show();
				} else {
					formWrapper.hide();
				}
			}, 100);
		};
		var onCountryChange = function () {
			selectCountry($(this), regionsList);

			selectState = 0;
			citySearchBox.empty().trigger("change").prop("disabled", true);
		};
		var onRegionChange = function  () {
			var self = $(this);

			selectState = self.val() || 0;
			citySearchBox.empty().trigger("change").prop("disabled", false);
			if (selectState !== '' || selectState !== 0) {
				var selectText = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
			} else {
				var selectText = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});

				citySearchBox.prop("disabled", true);
			}

			citySearchBox.siblings('.select2').find('.select2-selection__placeholder').text(selectText);
		};
		var saveCompany = function (form, dataTable) {
			var url = __group_site_url + 'company/ajax_company_operation/edit_buyer';
			var data = form.serializeArray();
			var onRequestStart = function () {
				$('html, body').animate({ scrollTop: $("body").offset().top }, 500);
				showLoader(form);
			};
			var onRequestEnd = function () {
				hideLoader(form);
			};
			var onRequestSuccess = function (response) {
				if ('success' === response.mess_type) {
                    var legalNameField = $('#edit-company-form--formfield--legal-name');
                    var legalNameText = legalNameField.val() ?? '';
                    if (legalNameField.length && legalNameText.length) {
                        legalNameField.siblings('label.input-label').removeClass('input-label--required');
                        legalNameField.replaceWith(
                            $('<p>').addClass('lh-40').text(legalNameField.val())
                        );
                    }

                    var displayNameField = $('#edit-company-form--formfield--display-name');
                    var displayNameText = displayNameField.val() ?? '';
                    if (displayNameField.length && displayNameText.length) {
                        displayNameField
                            .siblings('label.input-label').removeClass('input-label--required').removeClass('input-label--info')
                            .find('a.info-dialog').remove()
                        ;

                        displayNameField.replaceWith(
                            $('<p>').addClass('lh-40').text(displayNameField.val())
                        );
                    }

                    if (response.is_business && companyTypeSwitcher.length) {
                        companyTypeSwitcher.replaceWith(companyTypeSwitcher.data('replacementText'));
                    }

					companyNotificationModal({ title: "Success!", subTitle: response.message });
				} else {
					systemMessages(response.message, response.mess_type);
				}
			};

			onRequestStart();
			postRequest(url, data)
				.then(onRequestSuccess)
				.catch(onRequestError)
				.then(onRequestEnd);
		};
		//endregion Functions

		//region Variables
		selectState = parseInt('<?php echo (int) arrayGet($company, 'company_id_state', 0); ?>', 10);
		var hasCompany = Boolean(~~parseInt('<?php echo (int) !empty($company); ?>'));
		var infoLink = $('.link-info');
		var formWrapper = $('#js-company-form-elements');
		var countryList = $("select#country");
		var regionsList = $("select#country_states");
		var citySearchBox = $("select#port_city");
		var companyTypeSwitcher = $('#js-buyer-type-check');
		// var phonesCodesList = $('select#phone_code_company');
		// var faxCodesList = $('select#fax_code_company');
		var linkPopoverOptions = {
			html: true,
			container: 'body',
			trigger: 'hover',
		};

		//endregion Variables

		//region Initialization
		initSelectCity(citySearchBox);
		// phonesCodesList.select2(codesListOptions);
		// faxCodesList.select2(codesListOptions);
		infoLink.popover(linkPopoverOptions);
		//endregion Initialization

		//region Validation
		//region City
		citySearchBox.data('select2').$container.attr('id', 'select-city--formfield--tags-container')
			.addClass('validate[required]')
			.setValHookType('select2City');

		$.valHooks.select2City = {
			get: function (el) {
				return citySearchBox.val() || [];
			},
			set: function (el, val) {
				citySearchBox.val(val);
			}
		};
		//endregion City

		//region Phone code
		// phonesCodesList.data('select2').$container.attr('id', 'country-code--formfield--code-container')
		// 	.addClass('validate[required]')
		// 	.setValHookType('selectCcodePhone');

		// $.valHooks.selectCcodePhone = {
		// 	get: function (el) {
		// 		return phonesCodesList.val() || [];
		// 	},
		// 	set: function (el, val) {
		// 		phonesCodesList.val(val);
		// 	}
		// };
		//endregion Phone code
		//endregion Validation

		//region Listeners
		countryList.on('change', onCountryChange);
		regionsList.on('change', onRegionChange);
		if (!hasCompany) {
			companyTypeSwitcher.on('change', 'input', onCompanyTypeToggle);
		}

		mix(window, { edit_company: saveCompany });
        //endregion Listeners

        userPhoneMask.init({
            selectedFax: <?php echo ($selected_fax_code && (int)$selected_fax_code->getId())?$selected_fax_code->getId():0;?>,
            selectedPhone: <?php echo ($selected_phone_code && (int)$selected_phone_code->getId())?$selected_phone_code->getId():0;?>,
            selectorPhoneCod: 'select#js-company-edit-phone-code',
            selectorFaxCod: 'select#js-company-edit-fax-code',
            selectorPhoneNumber: '#js-company-edit-phone-number',
            selectorFaxNumber: '#js-company-edit-fax-number',
            textErorCountryCode: '<?php echo translate('register_error_country_code'); ?>',
            textErorPhoneMask: '<?php echo translate('register_error_phone_mask'); ?>',
        });

        <?php if(
                !empty($selected_region)
                && (int)$selected_region != 0
                && empty($selected_city)
            ){ ?>
            $selectCity.empty().trigger("change").prop("disabled", false);
        <?php }; ?>
	});
</script>

<div id="company-edit-wr" class="container-center dashboard-container inputs-40">
	<div class="dashboard-line">
		<h1 class="dashboard-line__ttl">
			Setup account type
		</h1>
	</div>

	<form class="validengine relative-b" id="edit_company_form" data-callback="edit_company">
		<div class="info-alert-b">
			<i class="ep-icon ep-icon_info-stroke"></i>
			<span>Once you have setup your personal account to a business account you won't able to revert back to a personal account.</span>
		</div>
		<?php if (empty($company)) { ?>
			<div class="row">
				<div class="col-12">
					<label class="input-label input-label--required">Account type</label>
					<div id="js-buyer-type-check" class="account-registration-another__checkbox" data-replacement-text="Business">
						<div class="account-registration-another__checkbox-item">
							<label class="checkbox-group checkbox-group--inline custom-radio" <?php echo addQaUniqueIdentifier("preferences-buyer-company__account-type-personal")?>>
								<input type="radio" name="type_buyer" value="0" <?php echo $is_buyer_company_completed ? 'checked' : ''; ?>>
								<div class="account-registration-another__checkbox-txt custom-radio__text">Personal</div>
							</label>
						</div>

						<div class="account-registration-another__checkbox-item">
							<label class="checkbox-group checkbox-group--inline custom-radio" <?php echo addQaUniqueIdentifier("preferences-buyer-company__account-type-business")?>>
								<input type="radio" name="type_buyer" value="1">
								<div class="account-registration-another__checkbox-txt custom-radio__text">Business</div>
							</label>
						</div>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<input type="hidden" name="type_buyer" value="1">
		<?php } ?>

		<?php //if (!$is_buyer_company_completed || $is_buyer_company_completed && !empty($company)) { ?>
			<div class="row" id="js-company-form-elements" style="<?php echo empty($company) ? 'display:none;' : ''; ?>">
				<?php if (!empty($company)) { ?>
					<div class="col-12">
						<label class="input-label">Account type</label>
						Business
					</div>
				<?php } ?>

				<div class="col-12 col-md-6">
                    <?php if (empty($company['company_legal_name'])) { ?>
                        <label class="input-label input-label--required">
                            <?php echo translate('pre_registration_page_register_form_business_label_company_name'); ?>
                        </label>
                        <input
                            id="edit-company-form--formfield--legal-name"
                            <?php echo addQaUniqueIdentifier("preferences-buyer-company__company-legal-name")?>
                            type="text"
                            name="company_legal_name"
                            class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                            value="<?php echo cleanOutput($company['company_name']); ?>"
                            placeholder="<?php echo translate('buyer_company_edit_form_company_name_field_placeholder', null, true); ?>">
                    <?php } else { ?>
                        <label class="input-label">
                            <?php echo translate('pre_registration_page_register_form_business_label_company_name'); ?>
                        </label>
                        <p class="lh-40"><?php echo cleanOutput($company['company_legal_name']); ?></p>
                    <?php } ?>
				</div>

				<div class="col-12 col-md-6">
                    <?php if (empty($company['company_name'])) { ?>
                        <label class="input-label input-label--required">
                            <?php echo translate('pre_registration_page_register_form_business_label_display_company_name');?>
                            <a href="#"
                                class="info-dialog"
                                data-content="#info-dialog__display-name"
                                data-title="Display company Name"
                                title="View information">
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </a>
                            <div class="display-n" id="info-dialog__display-name">
                                <?php echo translate('pre_registration_page_register_form_business_label_company_name_info');?>
                            </div>
                        </label>
                        <input
                            id="edit-company-form--formfield--display-name"
                            <?php echo addQaUniqueIdentifier("preferences-buyer-company__company-name")?>
                            type="text"
                            name="company_name"
                            class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                            value="<?php echo cleanOutput($company['company_name']); ?>"
                            placeholder="<?php echo translate('buyer_company_edit_form_displayed_company_name_field_placeholder', null, true); ?>">
                    <?php } else { ?>
                        <label class="input-label">
                            <?php echo translate('pre_registration_page_register_form_business_label_display_company_name');?>
                        </label>
                        <p class="lh-40"><?php echo cleanOutput($company['company_name']); ?></p>
                    <?php } ?>
				</div>

				<div class="col-12 col-md-6">
					<label class="input-label input-label--required">Country</label>
					<select id="country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier("preferences-buyer-company__country-select")?>>
						<?php echo getCountrySelectOptions($countries, (int) arrayGet($company, 'company_id_country', 0)); ?>
					</select>
				</div>

				<div class="col-12 col-md-6">
					<div id="state_td" class="clearfix">
						<label class="input-label input-label--required">State</label>
						<select name="states" class="validate[required]" id="country_states" <?php echo addQaUniqueIdentifier("preferences-buyer-company__state-select")?>>
							<option value="">Select state or province</option>
							<?php foreach($regions as $region) { ?>
								<option value="<?php echo cleanOutput($region['id']);?>"
									<?php if ($selected_region) echo selected((int) $selected_region['id'], (int) $region['id']); ?>>
									<?php echo cleanOutput($region['state']); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="col-12 col-md-6">
					<div id="city_td" class="clearfix pb-10 wr-select2-h50">
						<label class="input-label input-label--required">City</label>
						<select name="port_city" class="validate[required] select-city" id="port_city" <?php echo addQaUniqueIdentifier("preferences-buyer-company__city-select")?>>
							<option value="">Select country first</option>
							<?php if(isset($selected_city) && !empty($selected_city)){ ?>
								<option value="<?php echo cleanOutput((int) $selected_city['id']); ?>" selected>
									<?php echo cleanOutput($selected_city['city']); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-12 col-md-6">
					<label class="input-label input-label--required">ZIP</label>
					<input type="text"
                        <?php echo addQaUniqueIdentifier("preferences-buyer-company__zip-input")?>
						name="zip"
						class="validate[required,custom[zip_code],maxSize[20]]"
						value="<?php echo cleanOutput(arrayGet($company, 'company_zip', '')); ?>"
						maxlength="20">
				</div>

				<div class="col-12">
					<label class="input-label input-label--required">Address</label>
					<input type="text"
                        <?php echo addQaUniqueIdentifier("preferences-buyer-company__address-input")?>
						name="address"
						class="validate[required,minSize[3],maxSize[255]]"
						value="<?php echo cleanOutput(arrayGet($company, 'company_address', '')); ?>"
                        placeholder="<?php echo translate('buyer_company_edit_form_company_address_field_placeholder', null, true); ?>">
				</div>

				<div class="col-12 col-md-6">
					<label class="input-label input-label--required">Phone</label>
					<div class="input-group">
						<div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("preferences-buyer-company__phone-code-select")?>>
							<select
								id="js-company-edit-phone-code"
								class="validate[required]"
								name="phone_code_company"
							>
								<option value=""></option>
								<?php /** @var array<\App\Common\Contracts\Entities\CountryCode> $phone_codes */ ?>
								<?php foreach($phone_codes as $phone_code) { ?>
									<option
										value="<?php echo cleanOutput($phone_code->getId()); ?>"
										data-phone-mask="<?php echo cleanOutput($phone_code->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                                        data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
										data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
										data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
										data-code="<?php echo cleanOutput($phone_code->getName()); ?>"
										<?php if ($selected_phone_code && $selected_phone_code->getId() === $phone_code->getId()) { ?>selected<?php } ?>>
										<?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
									</option>
								<?php } ?>
							</select>
						</div>
						<input
                            <?php echo addQaUniqueIdentifier("preferences-buyer-company__phone-input")?>
                            id="js-company-edit-phone-number"
							class="form-control validate[required,funcCall[checkPhoneMask]]"
							type="text"
							name="phone"
							value="<?php echo cleanOutput(arrayGet($company, 'company_phone', '')); ?>"
							maxlength="25"
							placeholder="phone">
					</div>
				</div>
				<div class="col-12 col-md-6">
					<label class="input-label">Fax</label>
					<div class="input-group">
						<div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("preferences-buyer-company__fax-code-select")?>>
							<select
								id="js-company-edit-fax-code"
								class="validate[]"
								name="fax_code_company"
							>
								<option value=""></option>
								<?php /** @var array<\App\Common\Contracts\Entities\CountryCode> $fax_codes */ ?>
								<?php foreach($fax_codes as $fax_code) { ?>
									<option
										value="<?php echo cleanOutput($fax_code->getId()); ?>"
										data-phone-mask="<?php echo cleanOutput($fax_code->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
									    data-country-flag="<?php echo cleanOutput(getCountryFlag($fax_code_country = $fax_code->getCountry()->getName())); ?>"
										data-country-name="<?php echo cleanOutput($fax_code_country); ?>"
										data-country="<?php echo cleanOutput($fax_code->getCountry()->getId()); ?>"
										data-code="<?php echo cleanOutput($fax_code->getName()); ?>"
										<?php if ($selected_fax_code && $selected_fax_code->getId() === $fax_code->getId()) { ?>selected<?php } ?>>
										<?php echo cleanOutput(trim("{$fax_code->getName()} {$fax_code_country}")); ?>
									</option>
								<?php } ?>
							</select>
						</div>
						<input
                            id="js-company-edit-fax-number"
							class="form-control validate[funcCall[checkFaxMask]]"
                            <?php echo addQaUniqueIdentifier("preferences-buyer-company__fax-input")?>
							type="text"
							name="fax"
							value="<?php echo cleanOutput(arrayGet($company, 'company_fax', '')); ?>"
							maxlength="25"
							placeholder="fax">
					</div>
				</div>
			</div>
		<?php //} ?>

		<div class="row">
			<div class="col-12">
				<button class="btn btn-primary w-150 mt-15 pull-right" type="submit" <?php echo addQaUniqueIdentifier("preferences-buyer-company__save-button")?>>Save</button>
			</div>
		</div>
	</form>
</div>

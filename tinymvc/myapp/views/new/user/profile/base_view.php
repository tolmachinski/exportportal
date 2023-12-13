<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/inputmask-5.x/jquery.inputmask.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/phone-mask/phone-mask-init.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/user_preferences/index.js');?>"></script>
<script>
<?php if (!have_right('manage_content')) { ?>
	var selectState = parseInt('<?php echo $user['state']; ?>', 10);
	var $selectCity;

	$(function () {
        /**
         * When the user changes the account source, show the target field
         *
         * @param {JQuery<HTMLElement>} block - The block that the user interacted with.
         * @param {String} fieldName - the name of the info field
         */
        function onChangeSource(block, fieldName) {
            var option = this.find(":selected");
            var target = option.data("target") || null;
            if (target) {
                var otherField = block.find(target);

                this.removeAttr("name");
                otherField.prop("name", fieldName).show();
            } else {
                this.attr("name", fieldName);
                this.children()
                    .toArray()
                    .forEach(function(e) {
                        var listedTarget = e.dataset.target || null;
                        if (listedTarget) {
                            block.find(listedTarget).removeAttr("name").removeClass("validengine-border").hide();
                        }
                    });
            }
        }

        /**
         * When the user changes the type of the account source block, we need to hide all the sources and show the correct one.
         *
         * @param {JQuery<HTMLElement>} block - The block that contains the sources.
         * @param {JQuery<HTMLSelectElement|HTMLInputElement>} sources - The jQuery object of the source elements.
         * @param {String} fieldName - the name of the info field
         */
        function onChangeType(block, sources, fieldName) {
            var option = this.find(":selected");
            var target = option.data("target") || null;
            var placeholder = option.data("placeholder");

            // Here we will hide all elements in the sources wrapper
            sources.children().hide().removeClass("validengine-border").removeAttr("name").off("change");
            // If the option has the DOM target value, then we need to do
            // additional processing
            if (target) {
                var sourceType = block.find(target);
                sourceType
                    .attr("placeholder", placeholder || "")
                    .attr("name", fieldName)
                    .show();

                if (sourceType.prop("tagName") === "SELECT") {
                    sourceType.on("change", onChangeSource.bind(sourceType, block, fieldName));
                }
            }
        }

        var list = $("#js-account-source-list");
        var sourceBlock = $("#js-account-source-wrapper");
        list.on("change", onChangeType.bind(list, sourceBlock, sourceBlock.find(sourceBlock.data("sources")), "find_info"));
        globalThis.addEventListener("user-profile:saved", function () {
            if (sourceBlock) {
                sourceBlock.remove();
                sourceBlock = null;
            }
        });

        userPhoneMask.init({
            selectedFax: <?php echo ($selected_fax_code && (int)$selected_fax_code->getId())?$selected_fax_code->getId():0;?>,
            selectedPhone: <?php echo ($selected_phone_code && (int)$selected_phone_code->getId())?$selected_phone_code->getId():0;?>,
            selectorPhoneCod: 'select#js-preferences-phone-code',
            selectorFaxCod: 'select#js-preferences-fax-code',
            selectorPhoneNumber: '#js-preferences-phone-number',
            selectorFaxNumber: '#js-preferences-fax-number',
            textErorCountryCode: '<?php echo translate('register_error_country_code'); ?>',
            textErorPhoneMask: '<?php echo translate('register_error_phone_mask'); ?>',
        });

		$selectCity = $(".select-city");

		initSelectCity($selectCity);

        var $legalNameEl = $('#js-add-legal-name-checkbox');

        $legalNameEl.on('change', function(event) {
            if(event.target.checked){
                $('#js-legal-name-block').show();
            }else{
                $('#js-legal-name-block').hide();
            }
        });

		$('body').on('change', "select#country_states", function() {
			selectState = this.value;
			$selectCity.empty().trigger("change").prop("disabled", false);

			if (selectState != '' || selectState != 0) {
				var select_text = translate_js({
					plug: 'general_i18n',
					text: 'form_placeholder_select2_city'
				});
			} else {
				var select_text = translate_js({
					plug: 'general_i18n',
					text: 'form_placeholder_select2_state_first'
				});
				$selectCity.prop("disabled", true);
			}
			$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
		});

		$("#country").change(function () {
			selectCountry($(this), 'select#country_states');
			selectState = 0;
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

        $('.js-related-account-checkbox').on('click', function() {
            var label = $(this);
            var checkbox = label.find('.js-pseudo-checkbox');
            var account = label.data('value');

            if (checkbox.hasClass('checked')) {
                label.find('input[type=hidden]').remove();
            } else {
                label.append('<input type="hidden" name="sync_with_accounts[]" value="' + account + '">');
            }

            checkbox.toggleClass('checked');
        });

        <?php if(
                !empty($user['state'])
                && (int)$user['state'] != 0
                && empty($city_selected)
            ){ ?>
            $selectCity.empty().trigger("change").prop("disabled", false);
        <?php }; ?>
	});
<?php } ?>
</script>

<div class="container-center dashboard-container inputs-40">
    <div class="dashboard-info">
        <div class="dashboard-line">
            <h1 class="dashboard-line__ttl">Personal info</h1>
        </div>

        <div class="info-alert-b dashboard-info__alert mb-20">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span><?php echo translate('user_preferences_description'); ?></span>
        </div>

        <?php if (!empty($personalInformationSourceAccounts)) {?>
            <div class="info-alert-b dashboard-info__alert">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <span class="txt-bold"><?php echo translate('user_preferences_use_existing_information_title');?></span><br>
                <span><?php echo translate('user_preferences_use_existing_information_content');?></span>
                <div class="dropdown">
                    <a class="btn btn-primary dropdown-toggle mt-5 mnw-200" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <span class="pl-5 pr-5">Choose account</span>
                        <i class="ep-icon ep-icon_arrow-down fs-10"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-bottom mnw-200 shadow-none">
                        <?php foreach ($personalInformationSourceAccounts as $accountId => $accountGroupType) {?>
                            <a
                                class="dropdown-item call-function"
                                data-message="<?php echo translate('copying_existing_information_confirm_dialog', null, true);?>"
                                data-callback="openConfirmPopup"
                                data-title="Warning!"
                                data-account="<?php echo $accountId;?>"
                                title="<?php echo "Copy existing information from " . $accountGroupType;?>"
                            >
                                <span class="txt"><?php echo $accountGroupType;?></span>
                            </a>
                        <?php }?>
                    </div>
                </div>
            </div>
        <?php }?>

        <div class="dashboard-info__form">
            <form method="post" class="validengine inputs-40 relative-b" data-callback="saveUserInfo">
                <div>
                    <label class="input-label input-label--required">First Name</label>
                    <input
                        <?php echo addQaUniqueIdentifier("preferences__first-name-input")?>
                        type="text"
                        class="validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                        name="fname"
                        value="<?php echo cleanOutput($user['fname']); ?>"
                        placeholder="First name">
                </div>

                <div>
                    <label class="input-label input-label--required">Last Name</label>
                    <input
                        <?php echo addQaUniqueIdentifier("preferences__last-name-input")?>
                        type="text"
                        class="validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                        name="lname"
                        value="<?php echo cleanOutput($user['lname']); ?>"
                        placeholder="Last name">
                </div>

                <?php if (!have_right('manage_content')) { ?>
                    <div>
                        <label class="custom-checkbox" <?php echo addQaUniqueIdentifier("preferences__legal-name-checkbox")?> >
                            <input id="js-add-legal-name-checkbox" name="checkbox_legal_name" type="checkbox" <?php if (!empty($user['legal_name'])) echo "checked" ?>>
                            <span class="custom-checkbox__text">My First and Last name are different on my legal documents</span>
                        </label>
                    </div>

                    <div class="<?php echo !(empty($user['legal_name'])) ?: 'display-n'; ?>" id="js-legal-name-block">
                        <label class="input-label input-label--required">Legal Name</label>
                        <input
                            <?php echo addQaUniqueIdentifier("preferences__legal-name-input")?>
                            type="text"
                            class="validate[required,minSize[2],maxSize[100]]"
                            name="legal_name"
                            value="<?php echo cleanOutput($user['legal_name']); ?>"
                            placeholder="Legal Name">
                    </div>

                    <div>
                        <label class="input-label input-label--required">Country</label>
                        <select id="country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier("preferences__country-select")?> >
                            <?php echo getCountrySelectOptions($port_country, empty($user['country']) ? 0 : $user['country']); ?>
                        </select>
                    </div>

                    <div>
                        <div id="state_td">
                            <label class="input-label input-label--required">State</label>
                            <div class="notranslate">
                                <select name="states" class="validate[required]" id="country_states" <?php echo addQaUniqueIdentifier("preferences__state-select")?> >
                                    <option value="">Select state or province</option>
                                    <?php if (isset($states) && !empty($states)) { ?>
                                        <?php foreach ($states as $state) { ?>
                                            <option value="<?php echo cleanOutput($state['id']);?>"
                                                <?php if(!empty($user['state'])) echo selected($user['state'], $state['id']); ?>>
                                                <?php echo cleanOutput($state['state']); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div id="city_td" class="wr-select2-h50">
                            <label class="input-label input-label--required">City</label>
                            <div>
                                <select name="port_city" class="validate[required] select-city" id="port_city" <?php echo addQaUniqueIdentifier("preferences__city-select")?> >
                                    <option value="">Select country first</option>
                                    <?php if (isset($city_selected) && !empty($city_selected)) { ?>
                                        <option value="<?php echo cleanOutput($city_selected['id']); ?>" selected>
                                            <?php echo cleanOutput($city_selected['city']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="input-label input-label--required">Address</label>
                        <input
                            <?php echo addQaUniqueIdentifier("preferences__address-input")?>
                            type="text"
                            name="address"
                            class="validate[required,minSize[3],maxSize[255]]"
                            value="<?php echo cleanOutput($user['address']); ?>"
                            placeholder="address">
                    </div>

                    <div>
                        <label class="input-label input-label--required">ZIP</label>
                        <input
                            <?php echo addQaUniqueIdentifier("preferences__zip-input")?>
                            type="text"
                            name="zip"
                            class="validate[required,custom[zip_code],maxSize[20]]"
                            value="<?php echo cleanOutput($user['zip']); ?>"
                            maxlength="20"
                            placeholder="zip">
                    </div>

					<label class="input-label input-label--required">Phone</label>
					<div class="input-group">
						<div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("preferences__phone-code-input")?>>
							<div class="notranslate">
								<select
									id="js-preferences-phone-code"
									class="validate[required]"
									name="phone_code"
								>
									<option value=""></option>
									<?php foreach ($phone_codes as $phone_code) { ?>
										<?php /** @var \App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface $phone_code */ ?>
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
						</div>

                        <input
                            <?php echo addQaUniqueIdentifier("preferences__phone-input")?>
                            id="js-preferences-phone-number"
							class="form-control validate[required,funcCall[checkPhoneMask]]"
							type="text"
							name="phone"
							maxlength="25"
							value="<?php echo cleanOutput($user['phone']); ?>"
							placeholder="phone">
					</div>

					<label class="input-label">Fax</label>

					<div class="input-group">
						<div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("preferences__fax-code-select")?>>
							<div class="notranslate">
								<select
									id="js-preferences-fax-code"
									class="validate[]"
									name="fax_code"
								>
									<option value=""></option>
									<?php foreach ($fax_codes as $fax_code) { ?>
										<?php /** @var \App\Entities\Phones\CountryCode $fax_code */ ?>
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
						</div>
                        <input
                            <?php echo addQaUniqueIdentifier("preferences__fax-input")?>
                            id="js-preferences-fax-number"
							class="form-control validate[funcCall[checkFaxMask]]"
							type="text"
							name="fax"
							value="<?php echo cleanOutput($user['fax']); ?>"
							maxlength="25"
							placeholder="fax">
					</div>

                    <div>
                        <label class="input-label">Description</label>
                        <textarea
                            <?php echo addQaUniqueIdentifier("preferences__description-textarea")?>
                            name="description"
                            class="validate[maxSize[1000]] textcounter"
                            data-max="1000"
                            ><?php echo cleanOutput($user['description']); ?></textarea>
                    </div>

                    <?php if ($find_about_us_block) { ?>
                        <?php views()->display('new/user/find_about_us_view', [
                            'wrapperId' => "js-account-source-wrapper",
                            'fieldId'   => "js-account-source-list",
                        ]); ?>
                    <?php } ?>

                    <?php if (!empty($relatedAccounts)) {?>
                        <div>
                            <label class="input-label">
                                <?php echo translate('user_preferences_apply_changes_to_other_accounts_label');?>
                                <a
                                    class="info-dialog ep-icon ep-icon_info ml-5"
                                    data-message="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_message', null, true);?>"
                                    data-title="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_title', null, true);?>"
                                    title="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_hover_title', null, true);?>"
                                    href="#"
                                ></a>
                            </label>
                            <?php foreach ($relatedAccounts as $accountId => $accountType) {?>
                                <div>
                                    <label class="checkbox-group checkbox-group--inline js-related-account-checkbox" data-value="<?php echo $accountId;?>">
                                        <div class="pseudo-checkbox js-pseudo-checkbox <?php echo isset($syncPersonalInfo[$accountId]) ? 'checked' : '';?>"></div>
                                        <div class="pl-14 lh-23 txt-black-light"><?php echo $accountType;?></div>
                                        <?php if (isset($syncPersonalInfo[$accountId])) {?>
                                            <input type="hidden" name="sync_with_accounts[]" value="<?php echo $accountId;?>">
                                        <?php }?>
                                    </label>
                                </div>
                            <?php }?>
                        </div>
                    <?php }?>
                <?php } ?>

                <div class="flex-display flex-jc--fe pt-15">
                    <button class="btn btn-primary w-150" type="submit" <?php echo addQaUniqueIdentifier("preferences__form-submit")?>>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

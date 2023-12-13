<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>
<?php views()->display('new/file_upload_scripts'); ?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/inputmask-5.x/jquery.inputmask.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/phone-mask/phone-mask-init.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/tinymce-4-3-10/tinymce.min.js');?>"></script>

<script>
	var $selectCity;
	var selectState;

	(function() {
		"use strict";

		window.shipperEdit = ({
			init: function (params) {
				shipperEdit.self = this;
				// shipperEdit.$selectCcodePhone;
				// shipperEdit.$selectCcodeFax;
				shipperEdit.countryName = $('select#country').find("option:selected").text().trim();
				$selectCity = $(".select-city");
				shipperEdit.cityName = $selectCity.next('.select2-container').find('#select2-port_city-container').text().trim();
				shipperEdit.addressName = $('input[name="address"]').val().trim();
				shipperEdit.filesAmount = parseInt('<?php echo $fileupload['limits']['amount']['total']; ?>', 10);
				shipperEdit.filesAllowed = parseInt('<?php echo $fileupload['limits']['amount']['total'] - arrayGet($fileupload, 'limits.amount.current', 0); ?>', 10);
				shipperEdit.fileTypes = new RegExp('(<?php echo $fileupload['limits']['mimetypes']; ?>)', 'i');
				shipperEdit.fileFormats = new RegExp('(\.|\/)(<?php echo $fileupload['limits']['formats']; ?>)', 'i');
				shipperEdit.fileUploadMaxSize = "<?php echo $fileupload['rules']['size']; ?>";
				shipperEdit.fileUploadUrl = "<?php echo $fileupload['url']['upload']; ?>";
				shipperEdit.fileRemoveUrl = "<?php echo $fileupload['url']['delete']; ?>";
				shipperEdit.counter = $('#total-uploaded');
				shipperEdit.uploader = $('#user-photo--formfield--uploader');
				shipperEdit.uploadButton = $('.fileinput-loader-btn');
				shipperEdit.imageWrapper = $('#user-photo--formfield--image-wrapper');
				shipperEdit.uploaderOptions = {
					url: shipperEdit.fileUploadUrl,
					dataType: 'json',
					maxNumberOfFiles: shipperEdit.filesAmount,
					maxFileSize: shipperEdit.fileUploadMaxSize,
					acceptFileTypes: shipperEdit.fileFormats,
					loadImageFileTypes: shipperEdit.fileTypes,
					processalways: shipperEdit.self.onUploadFinished,
					beforeSend: shipperEdit.self.onUploadStart,
					done: shipperEdit.self.onUploadDone,
				};

				shipperEdit.self.initPlugs();
				shipperEdit.self.initListiners();
			},
			initPlugs: function(){
				initSelectCity($selectCity);

				$('.link-info').popover({
					container: 'body',
					html: true,
					trigger: 'hover'
				});

				tinymce.remove('#company_description');
				tinymce.init({
					selector: '#company_description',
					menubar: false,
					statusbar: true,
					height: 300,
					plugins: ["autolink lists link image charactercount"],
					style_formats: [
						{ title: 'H3', block: 'h3' },
						{ title: 'H4', block: 'h4' },
						{ title: 'H5', block: 'h5' },
						{ title: 'H6', block: 'h6' },
					],
					toolbar: "styleselect | bold italic underline | numlist bullist ",
					resize: false
				});

				shipperEdit.uploader.fileupload(shipperEdit.uploaderOptions);
				shipperEdit.uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
			},
			initListiners: function(){
				mix(window, {
					fileploadRemoveUserPhoto: shipperEdit.self.onFileRemove,
					edit_company: shipperEdit.self.onEditCompany,
				});

				$('input[name="address"]').on('change', function() {
					shipperEdit.addressName = $(this).val().trim();
				});

				$selectCity.on('select2:select', function (e) {
					var data = e.params.data;
					shipperEdit.cityName = data.name.trim();
					shipperEdit.addressName = '';
				});

				$selectCity.data('select2').$container.attr('id', 'select-сity--formfield--tags-container')
					.addClass('validate[required]')
					.setValHookType('selectselectCityShipper');

				$.valHooks.selectselectCityShipper = {
					get: function (el) {
						return $selectCity.val() || [];
					}
				};

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

				$('body').on('change', "#country", function() {
					selectCountry($(this), 'select#country_states');
					selectState = 0;
					shipperEdit.countryName = $(this).find("option:selected").text().trim();
					shipperEdit.cityName = '';
					shipperEdit.addressName = '';
					$selectCity.empty().trigger("change").prop("disabled", true);
				});

                <?php if(
                        !empty($selected_region)
                        && (int)$selected_region != 0
                        && empty($selected_city)
                    ){ ?>
                    $selectCity.empty().trigger("change").prop("disabled", false);
                <?php }; ?>
			},
			onEditCompany: function(form) {
				var url = __group_site_url + 'company/ajax_company_operation/edit_shipper';
				var wrapper = $('#company-edit-wr');
                var saveText = function (editor) {
					return new Promise(function (resolve) {
						var handler = function (event) {
							editor.off('SaveContent', handler);
							resolve(true);
						};

						editor.on('SaveContent', handler);
						tinyMCE.triggerSave();
					});
				};
				var onRequestStart = function () {
					$('html, body').animate({ scrollTop: $("body").offset().top }, 500);
					showLoader(wrapper);
				};
				var onRequestEnd = function () {
					hideLoader(wrapper);
				};
				var onRequestSuccess = function (response) {
					if ('success' === response.mess_type) {
                        var legalNameField = $('#edit-company-form--formfield--legal-name');
                        if (legalNameField.length) {
                            legalNameField.siblings('label.input-label').removeClass('input-label--required');
                            legalNameField.replaceWith(
                                $('<p>').addClass('lh-40').text(legalNameField.val())
                            );
                        }

                        var displayNameField = $('#edit-company-form--formfield--display-name');
                        if (displayNameField.length) {
                            displayNameField
                                .siblings('label.input-label').removeClass('input-label--required').removeClass('input-label--info')
                                .find('a.info-dialog').remove()
                            ;

                            displayNameField.replaceWith(
                                $('<p>').addClass('lh-40').text(displayNameField.val())
                            );
                        }

						var params = {
							title: "Success!",
							subTitle: response.message,
							additional_button: {
								text: "js_bootstrap_dialog_view_info",
								class: "btn-primary",
								location: response.url || null
							}
						}

						companyNotificationModal(params);
					} else {
						systemMessages(response.message, response.mess_type);
					}
				};

                onRequestStart();

                return saveText(tinyMCE.activeEditor)
                    .then(function () { return postRequest(url, form.serializeArray()) })
                    .then(onRequestSuccess)
                    .catch(onRequestError)
                    .then(onRequestEnd);
			},
			onUploadStart: function(event, files, index, xhr, handler, callBack) {
				if (files.files && files.files.length > shipperEdit.filesAllowed) {
					if (shipperEdit.filesAllowed > 0) {
						systemMessages(translate_js({
							plug: 'fileUploader',
							text: 'error_exceeded_limit_text'
						}).replace('[AMOUNT]', shipperEdit.filesAmount), 'warning');
					} else {
						systemMessages(translate_js({
							plug: 'fileUploader',
							text: 'error_no_more_files'
						}), 'warning');
					}
					shipperEdit.uploadButton.fadeOut();
					event.abort();

					return;
				}

				shipperEdit.uploadButton.fadeIn();
			},
			onUploadFinished: function(e, data) {
				if (data.files.error) {
					systemMessages(data.files[0].error, 'error');
				}
			},
			onUploadDone: function(e, data) {
				if (data.result.mess_type == 'success') {
					if (data.result.files && Array.isArray(data.result.files)) {
						data.result.files.forEach(shipperEdit.self.addImage);
					} else {
						shipperEdit.self.addImage(data.result.files, 0);
					}

					shipperEdit.self.updateCounter();
				} else {
					systemMessages(data.result.message, data.result.mess_type);
				}

				shipperEdit.uploadButton.fadeOut();
			},
			onFileRemove: function(button) {
				try {
					fileuploadRemove(button).then(function(response) {
						if ('success' === response.mess_type) {
							shipperEdit.filesAllowed++;
							shipperEdit.self.updateCounter();
						}
					});
				} catch (error) {
					if (__debug_mode) {
						console.error(error);
					}
				}
			},
			addImage: function(file, index) {
				shipperEdit.filesAllowed--;

				var pictureId = file.id_picture;
				var url = file.path;

				var image = $('<img>').attr({
					src: file.thumb
				});
				var imageInput = $('<input>').attr({
					class: 'display-n',
					name: 'main_photo',
					type: 'radio',
					value: file.id_picture
				});
				var closeButton = $('<a>').text(translate_js({
					plug: 'general_i18n',
					text: 'form_button_delete_file_text'
				})).attr({
					title: translate_js({
						plug: 'general_i18n',
						text: 'form_button_delete_file_title'
					}),
					class: 'btn btn-dark confirm-dialog',
					'data-file': file.id_picture,
					'data-action': shipperEdit.fileRemoveUrl,
					'data-message': translate_js({
						plug: 'general_i18n',
						text: 'form_button_delete_file_message'
					}),
					'data-callback': 'fileploadRemoveUserPhoto',
				});
				var imageContent = $(templateFileUploadNew({
					type: 'img',
					index: pictureId,
					image: image.prop('outerHTML'),
					image_link: url,
					className: 'fileupload-image',
				}));

				imageContent.find('.fileupload-item__actions').append([closeButton]);
				shipperEdit.imageWrapper.append(imageContent);

				if (shipperEdit.imageWrapper.find('.fileupload-item').length === 1) {
					shipperEdit.imageWrapper.find('.fileupload-item').first().trigger('click');
				}
			},
			updateCounter: function() {
				shipperEdit.counter.text(shipperEdit.filesAmount - shipperEdit.filesAllowed);
			},
		});
	}());

	$(function() {
		selectState = intval('<?php echo (int) arrayGet($shipper, 'id_state', 0); ?>');
        shipperEdit.init();

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
    });
</script>

<div id="company-edit-wr" class="container-center dashboard-container inputs-40">
	<div class="dashboard-line">
		<h1 class="dashboard-line__ttl">
			Edit Company
		</h1>
	</div>

	<form class="validengine relative-b" id="edit_company_form" data-callback="edit_company">
		<?php if (empty(arrayGet($industries, 'selected'))) { ?>
			<div class="row">
				<div class="col-12">
					<div class="warning-alert-b mb-10">
						<i class="ep-icon ep-icon_warning-circle-stroke"></i>
						<span>Please select the company industries an categories.</span>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="row">
			<div class="col-12 col-md-6">
                <?php if (empty($shipper['legal_co_name'])) { ?>
                    <label class="input-label input-label--required">Company Name</label>
                    <input
                        <?php echo addQaUniqueIdentifier('ff-company-edit__legal-name-input');?>
                        id="edit-company-form--formfield--legal-name"
                        type="text"
                        name="original_name"
                        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($shipper['legal_co_name']); ?>">
                <?php } else { ?>
                    <label class="input-label">Company Name</label>
                    <p class="lh-40"><?php echo cleanOutput($shipper['legal_co_name']); ?></p>
                <?php } ?>
			</div>

			<div class="col-12 col-md-6">
                <?php if (empty($shipper['co_name'])) { ?>
                    <label class="input-label input-label--info input-label--required">
                        <span class="input-label__text">Display company Name</span><a href="#"
                            class="info-dialog ep-icon ep-icon_info"
                            data-content="#info-dialog__display-name"
                            data-title="Display company Name"
                            title="View information"></a>
                        <div class="display-n" id="info-dialog__display-name">
                            <?php echo translate('pre_registration_page_register_form_business_label_company_name_info'); ?>
                        </div>
                    </label>
                    <input
                        <?php echo addQaUniqueIdentifier('ff-company-edit__displayed-name-input');?>
                        id="edit-company-form--formfield--display-name"
                        type="text"
                        name="co_name"
                        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($shipper['co_name']); ?>">
                <?php } else { ?>
                    <label class="input-label">
                        Display company Name
                    </label>
                    <p class="lh-40"><?php echo cleanOutput($shipper['co_name']); ?></p>
                <?php } ?>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<label class="input-label input-label--required">Logo:</label>

				<?php views()->display('new/user/photo_cropper_view'); ?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12 form-group">
                <label class="input-label input-label--required">Number of Office Locations</label>
                <input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__number-of-office-locations-input');?>
                    name="company_offices_number"
                    class="validate[required, custom[natural], max[999999]]"
                    value="<?php echo cleanOutput(arrayGet($shipper, 'offices_number', '')); ?>">
            </div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label--required">Industries of the items you can deliver</label>
                <?php widgetIndustriesMultiselect([
                    'industries'                => arrayGet($industries, 'all', []),
                    'selected_industries'       => arrayGet($industries, 'selected', []),
                    'show_only_industries'      => true,
                    'enable_select_all'         => true,
                    'dispatchDynamicFragment'   => true,
                ]); ?>
			</div>
		</div>

		<div class="row">
			<div class="col-12 col-md-6">
				<label class="input-label input-label--info input-label--required">
					<span class="input-label__text">Annual full container load volume (TEU's)</span><a href="#"
						class="info-dialog ep-icon ep-icon_info"
						data-content="#info-dialog__teus-info"
						data-title="Annual full container load volume (TEU's)"
						title="View information"></a>
					<div class="display-n" id="info-dialog__teus-info">
						<?php echo translate('pre_registration_page_register_form_business_label_teus_info'); ?>
					</div>
				</label>
				<input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__teu-input');?>
					name="company_teu"
					class="validate[required, custom[natural], max[9999999999]]"
					value="<?php echo cleanOutput(arrayGet($shipper, 'co_teu', '')); ?>">
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label input-label">Government tax ID number</label>
				<input
                    class="validate[maxSize[50]]"
                    type="text"
					name="company_tax_id"
					value="<?php echo cleanOutput(arrayGet($shipper, 'tax_id', '')); ?>"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__government-tax-id-input');?>
                >
			</div>
		</div>

		<div class="row">
			<div class="col-12 col-md-6">
				<label class="input-label input-label">
					DUNS number
					<a href="#"
						class="info-dialog"
						data-content="#info-dialog__duns-info"
						data-title="DUNS number"
						title="View information">
						<i class="ep-icon ep-icon_info fs-16"></i>
					</a>
					<div class="display-n" id="info-dialog__duns-info">
						<?php echo translate('pre_registration_page_register_form_business_label_duns_info'); ?>
					</div>
				</label>
				<input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__duns-number-input');?>
					name="company_duns"
					class="validate[custom[possible_duns]]"
					value="<?php echo cleanOutput(arrayGet($shipper, 'co_duns', '')); ?>">
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label">Website</label>
				<input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__website-input');?>
					name="website"
					class="validate[custom[url]]"
					value="<?php echo cleanOutput(arrayGet($shipper, 'co_website', '')); ?>">
			</div>
		</div>

		<div class="row pt-10">
			<div class="col-12 col-md-6">
				<label class="input-label input-label--required">Country</label>
				<select id="country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier('ff-company-edit__country-select');?>>
					<?php echo getCountrySelectOptions($countries, (int) arrayGet($shipper, 'id_country', 0)); ?>
				</select>
			</div>

			<div class="col-12 col-md-6">
				<div id="state_td" class="clearfix">
					<label class="input-label input-label--required">State</label>
					<select id="country_states"  name="states" class="validate[required]" id="id_state" <?php echo addQaUniqueIdentifier('ff-company-edit__state-select');?>>
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
		</div>

		<div class="row pt-10">
			<div class="col-12 col-md-6">
				<div id="city_td" class="clearfix pb-10 wr-select2-h50" <?php echo addQaUniqueIdentifier('ff-company-edit__city-select-div');?>>
					<label class="input-label input-label--required">City</label>
					<select name="port_city" class="validate[required] select-city" id="port_city" <?php echo addQaUniqueIdentifier('ff-company-edit__city-select');?>>
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
				<label class="input-label input-label--required">Postal code</label>
				<input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__zip-input');?>
					name="zip"
					class="validate[required,custom[zip_code],maxSize[20]]"
					value="<?php echo cleanOutput(arrayGet($shipper, 'zip', '')); ?>"
					maxlength="20">
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<label class="input-label input-label--required">Address</label>
				<input type="text"
                    <?php echo addQaUniqueIdentifier('ff-company-edit__address-input');?>
					name="address"
					class="validate[required,minSize[3],maxSize[255]]"
					value="<?php echo cleanOutput(arrayGet($shipper, 'address', '')); ?>">
			</div>
		</div>

		<div class="row">
			<div class="col-12 col-md-6">
				<label class="input-label input-label--required">Phone</label>
				<div class="input-group">
					<div class="input-group__left input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier('ff-company-edit__phone-code-div');?>>
						<select
                            <?php echo addQaUniqueIdentifier('ff-company-edit__phone-code-select');?>
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
                        <?php echo addQaUniqueIdentifier('ff-company-edit__phone-input');?>
                        id="js-company-edit-phone-number"
						class="form-control validate[required,funcCall[checkPhoneMask]]"
						type="text"
						name="phone"
						value="<?php echo cleanOutput(arrayGet($shipper, 'phone', '')); ?>"
						maxlength="25"
						placeholder="phone">
				</div>
			</div>

			<div class="col-12 col-md-6">
				<label class="input-label">Fax</label>
				<div class="input-group">
					<div class="input-group__left input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier('ff-company-edit__fax-code-div');?>>
						<select
                            <?php echo addQaUniqueIdentifier('ff-company-edit__fax-code-select');?>
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
                        <?php echo addQaUniqueIdentifier('ff-company-edit__fax-input');?>
                        id="js-company-edit-fax-number"
						class="form-control validate[funcCall[checkFaxMask]]"
						type="text"
						name="fax"
						value="<?php echo cleanOutput(arrayGet($shipper, 'fax', '')); ?>"
						maxlength="25"
						placeholder="fax">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12 mb-15" <?php echo addQaUniqueIdentifier('ff-company-edit__tinymce_editor');?>>
				<label class="input-label input-label--required">Description</label>
				<textarea
                    <?php echo addQaUniqueIdentifier('ff-company-edit__description-textarea');?>
                    id="company_description"
					name="description"
					class="validate[required,maxSize[20000]] mb-0"
					data-max="20000"
				><?php echo arrayGet($shipper, 'description', ''); ?></textarea>
			</div>

			<div class="col-12">
				<label class="input-label">Video (youtube or vimeo link)</label>
				<input
                    <?php echo addQaUniqueIdentifier('ff-company-edit__video-input');?>
                    type="text"
					name="video"
					class="validate[maxSize[200], custom[url]]"
					placeholder="URL of your video"
					value="<?php echo cleanOutput(arrayGet($shipper, 'video', '')); ?>">
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<label class="input-label">Photos</label>
				<div class="juploader-b">
					<div class="row">
						<div class="col-12">
							<span class="btn btn-dark mnw-125 fileinput-button">
								<span>Select file...</span>
								<input id="user-photo--formfield--uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload, 'limits.accept'); ?>" <?php echo addQaUniqueIdentifier('ff-company-edit__photos-select-file-btn');?>>
							</span>
							<span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
								<img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> Uploading...
							</span>
							<span class="total-b">Uploaded <span id="total-uploaded"><?php echo arrayGet($fileupload, 'limits.amount.current'); ?></span> from <?php echo arrayGet($fileupload, 'limits.amount.total'); ?></span>
							<div class="info-alert-b mt-10">
								<i class="ep-icon ep-icon_info-stroke"></i>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload, 'rules.size_placeholder'))); ?></div>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload, 'rules.min_height'))); ?></div>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => arrayGet($fileupload, 'limits.amount.total'))); ?></div>
								<div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', arrayGet($fileupload, 'limits.formats')))); ?></div>
							</div>
						</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload mt-30" id="user-photo--formfield--image-wrapper">
						<?php if (!empty($photos)) { ?>
							<?php foreach ($photos as $key => $photo) { ?>
								<div class="fileupload-item">
									<div class="fileupload-item__image">
										<a class="link fancyboxGallery" rel="fancybox-thumb" href="<?php echo cleanOutput($photo['url']); ?>" <?php echo addQaUniqueIdentifier('ff-company-edit__open-image-fancybox');?>>
											<img src="<?php echo cleanOutput($photo['thumb']); ?>"
												class="image <?php echo cleanOutput(viewPictureType($photo['type_photo'], $photo['url'])); ?>">
										</a>
									</div>
									<div class="fileupload-item__actions">
										<a class="btn btn-dark confirm-dialog"
                                            <?php echo addQaUniqueIdentifier('ff-company-edit__photos-remove-file-btn');?>
											data-callback="fileploadRemoveUserPhoto"
											data-action="<?php echo cleanOutput(arrayGet($fileupload, 'url.delete')); ?>"
											data-file="<?php echo cleanOutput($photo['id_picture']); ?>"
											data-message="<?php echo translate('systmess_company_info_delete_image_message', null, true); ?>"
											title="Delete">
											Delete
										</a>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<button class="btn btn-primary w-150 mt-15 pull-right" type="submit" <?php echo addQaUniqueIdentifier('ff-company-edit__submit-form-btn');?>>Save</button>
			</div>
		</div>
	</form>
</div>

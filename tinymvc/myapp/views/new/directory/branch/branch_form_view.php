<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="js-form-branch"
        class="modal-flex__form validateModal inputs-40"
        data-callback="branchFormCallBack"
        autocomplete="off"
    >
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Branch name</label>
						<input class="validate[required,maxSize[100]]" type="text" name="name" value="<?php if(isset($branch['name_company'])){ echo $branch['name_company'];}?>" placeholder="Branch name"/>
					</div>
					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Type</label>
						<select class="validate[required]" name="type">
							<?php if(isset($branch['id_type'])){?>
								<?php foreach ($types as $type){ ?>
									<option value="<?php echo $type['id_type']?>" <?php echo selected($type['id_type'],$branch['id_type']);?>><?php echo $type['name_type']?></option>
								<?php } ?>
							<?php }else{ ?>
								<?php foreach ($types as $type){ ?>
									<option value="<?php echo $type['id_type']?>"><?php echo $type['name_type']?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="input-label input-label--required">Branch logo</label>

						<?php views()->display('new/user/photo_cropper_view'); ?>
					</div>

					<div class="col-12">
						<label class="input-label input-label--required">Industries/categories</label>

						<div id="js-industry-select-branch">
                            <?php widgetIndustriesMultiselect([
                                'industries' => arrayGet($multipleselect_industries, 'industries', array()),
                                'selected_industries' => arrayGet($multipleselect_industries, 'industries_selected', array()),
                                'categories' => arrayGet($multipleselect_industries, 'categories', array()),
                                'selected_categories' => arrayGet($multipleselect_industries, 'categories_selected_by_id', array()),
                                'max_selected_industries' => arrayGet($multipleselect_industries, 'max_industries', 0),
                                'industries_top' => arrayGet($multipleselect_industries, 'industries_top', array())
                            ]);?>
						</div>
					</div>

					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Country</label>
						<select id="country" class="validate[required]" name="country">
							<?php echo getCountrySelectOptions($port_country, empty($branch['id_country']) ? 0 : $branch['id_country']);?>
						</select>
					</div>
					<div class="col-s-12 col-6">
						<div id="state_td">
							<label class="input-label input-label--required">State</label>
							<select class="validate[required]" id="country_states" name="states">
								<option value="">Select state or province</option>
								<?php if(!empty($states)){?>
									<?php foreach($states as $state){?>
										<option value="<?php echo $state['id'];?>" <?php echo selected($branch['id_state'], $state['id']);?>>
											<?php echo $state['state'];?>
										</option>
									<?php } ?>
								<?php }?>
							</select>
						</div>
					</div>
					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">City</label>
						<div id="city_td" class="wr-select2-h35">
							<select class="validate[required] select-city" id="port_city" name="port_city">
								<option value="">Select country first</option>
								<?php if(!empty($city_selected)){ ?>
									<option value="<?php echo $city_selected['id'];?>" selected>
										<?php echo $city_selected['city'];?>
									</option>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Address</label>
						<input class="validate[required]" type="text" name="address" value="<?php if(isset($branch['address_company'])){ echo $branch['address_company'];}?>" placeholder="Address"/>
					</div>
					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Zip</label>
						<input class="validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php if(isset($branch['zip_company'])){ echo $branch['zip_company'];}?>" placeholder="Zip"/>
					</div>

					<div class="col-12">
						<div class="description-b pt-10 mb-10">
							<h3>Map coordinates</h3>
							<p class="map-coord">Please note: If you'd like to change the office location on the map, use the marker to change position.</p>
							<input class="validate[required]" type="hidden" name="lat" id="lat" value="<?php if(isset($branch['latitude'])){ echo $branch['latitude'];}?>"/>
							<input class="validate[required]" type="hidden" name="long" id="long" value="<?php if(isset($branch['longitude'])){ echo $branch['longitude'];}?>"/>
						</div>
					</div>

					<div class="col-12">
						<div class="h-300" id="google-map-b"></div>
					</div>

					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Phone</label>
						<div class="row">
							<div class="col-12 col-lg-5 wr-select2-h35 pb-15-lg">
								<select class="validate[required]" name="phone_code_company" id="phone_code_company">
									<option value=""></option>
									<?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface> $phone_codes */ ?>
									<?php foreach($phone_codes as $phone_code) { ?>
										<option
											value="<?php echo cleanOutput($phone_code->getId()); ?>"
											data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
											data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
											data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
											<?php if ($selected_phone_code && $selected_phone_code->getId() === $phone_code->getId()) { ?>selected<?php } ?>>
											<?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="col-12 col-lg-7">
								<input class="validate[required,custom[phoneNumber]]" maxlength="25" type="text" name="phone" value="<?php if(isset($branch['phone_company'])){ echo $branch['phone_company']; }?>">
							</div>
						</div>
					</div>
					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Fax</label>
						<div class="row">
							<div class="col-12 col-lg-5 wr-select2-h35 pb-15-lg">
								<select class="validate[required]" name="fax_code_company" id="fax_code_company">
									<option value=""></option>
									<?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface> $fax_codes */ ?>
									<?php foreach($fax_codes as $fax_code) { ?>
										<option
											value="<?php echo cleanOutput($fax_code->getId()); ?>"
											data-country-flag="<?php echo cleanOutput(getCountryFlag($fax_code_country = $fax_code->getCountry()->getName())); ?>"
											data-country-name="<?php echo cleanOutput($fax_code_country); ?>"
											data-country="<?php echo cleanOutput($fax_code->getCountry()->getId()); ?>"
											<?php if ($selected_fax_code && $selected_fax_code->getId() === $fax_code->getId()) { ?>selected<?php } ?>>
											<?php echo cleanOutput(trim("{$fax_code->getName()} {$fax_code_country}")); ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<div class="col-12 col-lg-7">
								<input class="validate[custom[phoneNumber]]" maxlength="25" type="text" name="fax" value="<?php if(isset($branch['fax_company'])){ echo $branch['fax_company'];}?>">
							</div>
						</div>
					</div>

					<div class="col-s-12 col-6">
						<label class="input-label input-label--required">Email</label>
						<input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" type="text" name="email" value="<?php if(isset($branch['email_company'])){ echo $branch['email_company'];}?>" placeholder="Email"/>
					</div>
					<div class="col-s-12 col-6">
						<label class="input-label">Number of employees</label>
						<input
                            class="validate[maxSize[5],custom[positive_integer]]"
                            type="text"
                            name="employees"
                            value="<?php if(isset($branch['employees_company'])){ echo $branch['employees_company'];}?>"
                        />
					</div>

					<div class="col-s-12 col-6">
						<label class="input-label">Annual Revenue, in USD:</label>
						<input
							class="validate[custom[positive_number]]"
							type="text"
							name="revenue"
							value="<?php if(isset($branch['revenue_company'])){ echo $branch['revenue_company'];}?>"
						/>
					</div>

					<div class="col-12">
						<label class="input-label">Pictures</label>
						<div class="juploader-b js-file-upload2">
							<span class="btn btn-dark mnw-125 fileinput-button">
								<span>Select file...</span>
								<!-- The file input field used as target for the file upload widget -->
								<input id="user-photo--formfield--uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload_photos, 'limits.accept'); ?>">
							</span>

							<span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
								<img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> Uploading...
							</span>
							<span class="total-b">Uploaded <span id="total-uploaded"><?php echo arrayGet($fileupload_photos, 'limits.amount.current'); ?></span> from <?php echo arrayGet($fileupload_photos, 'limits.amount.total'); ?></span>
							<div class="info-alert-b mt-10">
								<i class="ep-icon ep-icon_info-stroke"></i>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload_photos, 'rules.size_placeholder'))); ?></div>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload_photos, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_photos, 'rules.min_height'))); ?></div>
								<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => arrayGet($fileupload_photos, 'limits.amount.total'))); ?></div>
								<div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', arrayGet($fileupload_photos, 'limits.formats')))); ?></div>
							</div>

							<!-- The container for the uploaded files -->
							<div class="fileupload mt-30" id="user-photo--formfield--image-wrapper">
								<?php if(!empty($branch['pictures'])){?>
									<?php foreach ($branch['pictures'] as $key => $photo) { ?>
										<?php $link_img = getDisplayImageLink(array('{ID}' => $branch['id_company'], '{FILE_NAME}' => $photo['photo_name']), 'company_branches.photos');?>
										<div class="fileupload-item js-fileupload-item">
											<div class="fileupload-item__image">
												<span class="link">
													<img
														class="image <?php echo viewPictureType($photo['type_photo'], $link_img);?>"
														src="<?php echo getDisplayImageLink(array('{ID}' => $branch['id_company'], '{FILE_NAME}' => $photo['photo_name']), 'company_branches.photos', array('thumb_size' => 2)); ?>" />
												</span>
											</div>
											<div class="fileupload-item__actions">
												<a
													class="btn btn-dark confirm-dialog"
													data-callback="fileploadRemoveUserPhoto"
													data-action="<?php echo arrayGet($fileupload_photos, 'url.delete'); ?>"
													data-file="<?php echo $photo['id_photo']; ?>"
                                                    data-name="<?php echo $photo['photo_name']; ?>"
													data-message="Are you sure you want to delete this image?"
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

					<div class="col-12">
						<label class="input-label input-label--required">Description</label>
						<textarea name="description" class="validate[required]" data-max="20000" id="company_description" placeholder="Description"><?php if(isset($branch['description_company'])){ echo $branch['description_company'];}?></textarea>
					</div>

					<div class="col-12">
						<label class="input-label">Video (youtube or vimeo link):</label>
						<input class="validate[maxSize[200], custom[url]]" type="text" name="video" placeholder="URL of your video" value="<?php if(isset($branch['video_company'])){ echo $branch['video_company'];}?>"/>
					</div>
				</div>
			</div>
            <?php if(isset($branch['id_company'])){?>
            <input type="hidden" name="branch" value="<?php echo $branch['id_company'];?>"/>
            <?php }?>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>
</div>

<script>
	var $selectCity;
	var selectState;

	(function() {
		"use strict";

		window.branchModal = ({
			init: function (params) {
				branchModal.self = this;
				branchModal.$mainForm = $('#js-form-branch');

				branchModal.$selectCcodePhone;
				branchModal.$selectCcodeFax;

				$selectCity = $(".select-city");
				branchModal.industrySelectNameBranch = '#js-industry-select-branch';

				branchModal.geocoder;
				branchModal.map;
				branchModal.marker;
				branchModal.latitude = 0;
				branchModal.longitude = 0;
				branchModal.cityName = '';
				branchModal.countryName = '';
				branchModal.addressName = '';
				branchModal.zoom = 6;

				branchModal.filesAmount = parseInt('<?php echo $fileupload_photos['limits']['amount']['total']; ?>', 10);
				branchModal.filesAllowed = parseInt('<?php echo $fileupload_photos['limits']['amount']['total'] - arrayGet($fileupload_photos, 'limits.amount.current', 0); ?>', 10);
				branchModal.fileTypes = new RegExp('(<?php echo $fileupload_photos['limits']['mimetypes']; ?>)', 'i');
				branchModal.fileFormats = new RegExp('(\.|\/)(<?php echo $fileupload_photos['limits']['formats']; ?>)', 'i');
				branchModal.fileUploadMaxSize = "<?php echo $fileupload_photos['rules']['size']; ?>";
				branchModal.fileUploadUrl = "<?php echo $fileupload_photos['url']['upload']; ?>";
				branchModal.fileRemoveUrl = "<?php echo $fileupload_photos['url']['delete']; ?>";
				branchModal.counter = $('#total-uploaded');
				branchModal.uploader = $('#user-photo--formfield--uploader');
				branchModal.uploadButton = $('.fileinput-loader-btn');
				branchModal.imageWrapper = $('#user-photo--formfield--image-wrapper');
				branchModal.uploaderOptions = {
					url: branchModal.fileUploadUrl,
					dataType: 'json',
					maxNumberOfFiles: branchModal.filesAmount,
					maxFileSize: branchModal.fileUploadMaxSize,
					acceptFileTypes: branchModal.fileFormats,
					loadImageFileTypes: branchModal.fileTypes,
					processalways: branchModal.self.onUploadFinished,
					beforeSend: branchModal.self.onUploadStart,
					done: branchModal.self.onUploadDone,
				};

				branchModal.self.initPlug();
				branchModal.self.initListiners();
				branchModal.self.initGoogleMaps();
			},
			initPlug: function(){

				tinymce.remove('#company_description');
				tinymce.init({
					selector:'#company_description',
					menubar: false,
					statusbar : true,
					height : 300,
					plugins: ["autolink lists link image charactercount"],
					style_formats: [
						{title: 'H3', block: 'h3'},
						{title: 'H4', block: 'h4'},
						{title: 'H5', block: 'h5'},
						{title: 'H6', block: 'h6'},
					],
					toolbar: "styleselect | bold italic underline | link | numlist bullist ",
					resize: false
				});

				branchModal.$selectCcodePhone = $('select#phone_code_company').select2({
					theme: "default ep-select2-h30",
					templateResult: formatCcode,
					placeholder: "Select country code",
					width: '100%',
					dropdownAutoWidth : true
				});

				branchModal.$selectCcodeFax = $('select#fax_code_company').select2({
					theme: "default ep-select2-h30",
					templateResult: formatCcode,
					placeholder: "Select country code",
					width: '100%',
					dropdownAutoWidth : true
				});

				initSelectCity($selectCity);

				branchModal.uploader.fileupload(branchModal.uploaderOptions);
				branchModal.uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
			},
			initListiners: function(){
				mix(
					window,
					{
						fileploadRemoveUserPhoto: branchModal.self.onFileRemove,
						branchFormCallBack: branchModal.self.onModalFormCallBack
					},
					false
				);

				$('input[name="address"]').on('change', function(){
					branchModal.addressName = $(this).val().trim();
					branchModal.self.showGoogleMapsAddress();
				});

				$selectCity.on('select2:select', function (e) {
					var data = e.params.data;
					branchModal.cityName = data.name.trim();

					branchModal.addressName = '';
					branchModal.self.showGoogleMapsAddress();
				});

				$selectCity.data('select2').$container.attr('id', 'select-сity--formfield--tags-container')
					.addClass('validate[required]')
					.setValHookType('selectselectCityBranch');

				$.valHooks.selectselectCityBranch = {
					get: function (el) {
						return $selectCity.val() || [];
					}
				};

				branchModal.$mainForm.on('change', '#lat, #long', function(){
					setInfoMap();
				});

				branchModal.$mainForm.on('change', "select#country_states", function(){
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

				branchModal.$mainForm.on('change', "#country", function(){
					selectCountry($(this), 'select#country_states');
					selectState = 0;
					$selectCity.empty().trigger("change").prop("disabled", true);
					branchModal.countryName = $(this).find("option:selected").text().trim();
					branchModal.cityName = '';
					branchModal.addressName = '';
					branchModal.self.showGoogleMapsAddress();
				});

				<?php //if(empty($branch['logo_company'])){?>
					// logoFileupload.addClass('validate[required]')
					// 	.setValHookType('images');
				<?php //}?>

				// $.valHooks.images = {
				// 	get: function (el) {
				// 		return $logoFileupload.val() || [];
				// 	},
				// 	set: function (el, val) {
				// 		$logoFileupload.val(val);
				// 	}
				// };
			},
			initGoogleMaps: function(){
				branchModal.geocoder = new google.maps.Geocoder();

				var latLng = new google.maps.LatLng(branchModal.latitude,branchModal.longitude);
				var mapOptions = {
					center: latLng,
					zoom: 1,
					mapTypeControl: false,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};

				branchModal.map = new google.maps.Map(document.getElementById('google-map-b'), mapOptions);
				branchModal.marker = new google.maps.Marker({ map: branchModal.map });

				google.maps.event.addListener(branchModal.map, 'click', function(event){
					// UPDATE LAT, LNG VALUES
					branchModal.latitude = event.latLng.lat();
					branchModal.longitude = event.latLng.lng();
					$('#lat').val(branchModal.latitude);
					$('#long').val(branchModal.longitude);

					var map_latlng = new google.maps.LatLng(branchModal.latitude, branchModal.longitude, false);

					// CLEAR MAP MARKERS
					branchModal.marker.setMap(null);

					// PUT MARKER ON THE MAP
					branchModal.marker = new google.maps.Marker({
						animation: google.maps.Animation.DROP,
						position: map_latlng,
						map: branchModal.map,
						draggable: true,
						title: '<?php echo $company_map_config['marker_title'];?>'
					});
					branchModal.self.putListenerToMarker(branchModal.marker, branchModal.map);
				});
			},
			showGoogleMapsAddress: function(){
				var formatted_address = [];
				if(branchModal.addressName != ''){
					formatted_address.push(branchModal.addressName);
					branchModal.zoom = 16;
				}
				if(branchModal.cityName != '' && branchModal.cityName != translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'})){
					formatted_address.push(branchModal.cityName);
					branchModal.zoom = 13;
				}
				if(branchModal.countryName != ''){
					formatted_address.push(branchModal.countryName);
				}

				if(formatted_address.length > 0){
					branchModal.geocoder.geocode( { 'address': formatted_address.join(', ')}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							// CLEAR MAP MARKERS
							branchModal.marker.setMap(null);

							// UPDATE LAT, LNG VALUES
							branchModal.latitude = results[0].geometry.location.lat();
							branchModal.longitude = results[0].geometry.location.lng();
							$("#lat").val(branchModal.latitude);
							$("#long").val(branchModal.longitude);

							// PUT MARKER ON THE MAP
							branchModal.marker = new google.maps.Marker({
								animation: google.maps.Animation.DROP,
								position: results[0].geometry.location,
								map: branchModal.map,
								draggable: true,
								title: '<?php echo $company_map_config['marker_title'];?>'
							});
							branchModal.map.setCenter(results[0].geometry.location);
						}
					});
				}
				branchModal.map.setZoom(branchModal.zoom);
			},
			putListenerToMarker: function(marker, map){
				google.maps.event.addListener(marker, 'drag', function(event){
					$("#lat").val(marker.position.lat());
					$("#long").val(marker.position.lng());
				});

				google.maps.event.addListener(marker, 'dragend', function(event){;
					$('#lat').val(marker.position.lat());
					$('#long').val(marker.position.lng());
				});
			},
			onModalFormCallBack: function(form){
				var $form = $(form);
				var $wrform = $form.closest('.js-modal-flex');
				var fdata = $form.serialize();

				<?php if(isset($branch)){?>
					var url = 'company_branches/ajax_branch_operation/edit';
				<?php }else{?>
					var url = 'company_branches/ajax_branch_operation/add';
				<?php }?>

				$.ajax({
					type: 'POST',
					url: url,
					data: fdata,
					dataType: 'JSON',
					beforeSend: function(){
						showLoader($wrform);
						$form.find('button[type=submit]').addClass('disabled');
					},
					success: function(resp){
						hideLoader($wrform);
						systemMessages( resp.message, resp.mess_type );

						if(resp.mess_type == 'success'){
							closeFancyBox();
							dtDirectoriesList.fnDraw();
						}else{
							$form.find('button[type=submit]').removeClass('disabled');
						}
					}
				});
			},
			onUploadStart: function(event, files, index, xhr, handler, callBack) {
				if (files.files && files.files.length > branchModal.filesAllowed) {
					if (branchModal.filesAllowed > 0) {
						systemMessages(translate_js({
							plug: 'fileUploader',
							text: 'error_exceeded_limit_text'
						}).replace('[AMOUNT]', branchModal.filesAmount), 'warning');
					} else {
						systemMessages(translate_js({
							plug: 'fileUploader',
							text: 'error_no_more_files'
						}), 'warning');
					}
					branchModal.uploadButton.fadeOut();
					event.abort();

					return;
				}

				branchModal.uploadButton.fadeIn();
			},
			onUploadFinished: function(e, data) {
				if (data.files.error) {
					systemMessages(data.files[0].error, 'error');
				}
			},
			onUploadDone: function(e, data) {
				if (data.result.mess_type == 'success') {
					if (data.result.files && Array.isArray(data.result.files)) {
						data.result.files.forEach(branchModal.self.addImage);
					} else {
						branchModal.self.addImage(data.result.files, 0);
					}

					branchModal.self.updateCounter();
				} else {
					systemMessages(data.result.message, data.result.mess_type);
				}

				branchModal.uploadButton.fadeOut();
			},
            onFileRemove: function(button) {
                try {
                    fileuploadRemoveNew2(button).then(function(response) {
                        branchModal.filesAllowed++;
                        branchModal.self.updateCounter();
                    });
                } catch (error) {
                    if(__debug_mode) {
                        console.error(error);
                    }
                }
                // var file = button.data('file');
                // button.closest('.juploader-b').append('<input type="hidden" name="images_remove[]" value="'+file+'">');
                // button.closest('.fileupload-item').remove();
                // branchModal.filesAllowed++;
                // console.log(branchModal.filesAllowed);
                // branchModal.self.updateCounter();
			},
			addImage: function(file, index) {
				branchModal.filesAllowed--;

				var pictureId = file.id_picture;
				var url = file.fullPath;

				var image = $('<img>').attr({
					src: file.fullPath
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
					'data-file': file.name,
                    'data-name': file.name,
					'data-action': branchModal.fileRemoveUrl,
					'data-message': translate_js({
						plug: 'general_i18n',
						text: 'form_button_delete_file_message'
					}),
					'data-callback': 'fileploadRemoveUserPhoto',
				});
				var imageContent = $(templateFileUploadNew({
					type: 'imgnolink',
					index: pictureId,
					image: image.prop('outerHTML'),
					image_link: url,
					className: 'js-fileupload-item',
				}));

                imageContent.find('.fileupload-item__image').append('<input type="hidden" name="images_pictures[]" value="'+file.path+'">');

				imageContent.find('.fileupload-item__actions').append([closeButton]);
				branchModal.imageWrapper.append(imageContent);
			},
			updateCounter: function() {
				branchModal.counter.text(branchModal.filesAmount - branchModal.filesAllowed);
			},
		});

	}());

	$(function() {
		branchModal.init();
    });
</script>

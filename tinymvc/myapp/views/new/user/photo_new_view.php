<div class="container-center dashboard-container inputs-40">
	<div class="dashboard-line">
        <h2 class="dashboard-line__ttl">Profile picture</h2>
	</div>

	<div class="mw-1024">
		<script>
		function callbackReplaceCropImages(resp){
			var d = new Date();
			if($('.js-replace-file-avatar').length){
				$('.js-replace-file-avatar').attr('src', resp.thumb);
			}

			if($('.js-replace-file-avatar').css('background-image') != ''){
				$('.js-replace-file-avatar').css('background-image', 'url(' + resp.thumb + ')');
			}
		}
		</script>
		<?php views()->display('new/user/photo_cropper_view'); ?>
	</div>
	<?php if (have_right('manage_personal_pictures')) {?>
		<div class="dashboard-line mt-50">
			<h2 class="dashboard-line__ttl">Pictures</h2>
		</div>

		<div class="juploader-b">
			<div class="mw-1024">
				<span class="btn btn-dark mnw-125 fileinput-button">
					<span>Select file...</span>
					<!-- The file input field used as target for the file upload widget -->
					<input id="user-photo--formfield--uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload, 'limits.accept'); ?>">
				</span>
				<span class="js-fileinput-loader-btn fileinput-loader-img lh-40" style="display:none;">
					<img class="h-40 image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> Uploading...
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

			<!-- The container for the uploaded files -->
			<div class="fileupload mt-30" id="user-photo--formfield--image-wrapper">
				<?php if(!empty($photos)) { ?>
					<?php foreach($photos as $key => $photo) { ?>
						<?php $link_img = getDisplayImageLink(array('{ID}' => $user['idu'], '{FILE_NAME}' => $photo['name_photo']), 'users.photos');?>
						<div class="fileupload-item">
							<div class="fileupload-item__image">
								<a class="link fancyboxGallery" rel="fancybox-thumb" href="<?php echo $link_img; ?>">
									<img
										class="image <?php echo viewPictureType($photo['type_photo'], $link_img);?>"
										src="<?php echo getDisplayImageLink(array('{ID}' => $user['idu'], '{FILE_NAME}' => $photo['name_photo']), 'users.photos', array('thumb_size' => 2)); ?>"
									/>
								</a>
							</div>
							<div class="fileupload-item__actions">
								<a class="btn btn-dark confirm-dialog"
									data-callback="fileploadRemoveUserPhoto"
									data-action="<?php echo arrayGet($fileupload, 'url.delete'); ?>"
									data-file="<?php echo $photo['id_photo']; ?>"
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
	<?php }?>
</div>

<?php if (have_right('manage_personal_pictures')) {?>
	<?php views()->display('new/file_upload_scripts'); ?>

	<script>
		(function() {
			"use strict";

			window.userImg = ({
				init: function (params) {
					userImg.self = this;

					userImg.fileTypes = new RegExp('(<?php echo $fileupload['limits']['mimetypes']; ?>)', 'i');
					userImg.fileFormats = new RegExp('(\.|\/)(<?php echo $fileupload['limits']['formats']; ?>)', 'i');
					userImg.fileUploadMaxSize = "<?php echo $fileupload['rules']['size']; ?>";
					userImg.fileUploadTimestamp = "<?php echo $fileupload['directory']; ?>";

					userImg.filesAmount = parseInt('<?php echo $fileupload['limits']['amount']['total']; ?>', 10);
					userImg.filesAllowed = parseInt('<?php echo $fileupload['limits']['amount']['total'] - arrayGet($fileupload, 'limits.amount.current', 0); ?>', 10);
					userImg.counter = $('#total-uploaded');
					userImg.uploader = $('#user-photo--formfield--uploader');
					userImg.uploadButton = $('.js-fileinput-loader-btn');
					userImg.imageWrapper = $('#user-photo--formfield--image-wrapper');
					userImg.fileUploadUrl = "<?php echo $fileupload['url']['upload']; ?>";
					userImg.fileRemoveUrl = "<?php echo $fileupload['url']['delete']; ?>";
					userImg.uploaderOptions = {
						url: userImg.fileUploadUrl,
						dataType: 'json',
						maxNumberOfFiles: userImg.filesAmount,
						maxFileSize: userImg.fileUploadMaxSize,
						acceptFileTypes: userImg.fileFormats,
						loadImageFileTypes: userImg.fileTypes,
						processalways: userImg.onUploadFinished,
						beforeSend: userImg.onUploadStart,
						done: userImg.onUploadDone,
					};

					userImg.self.initUploader();
					userImg.self.initListiners();
				},
				initListiners: function(){
					mix(window, {
						fileploadRemoveUserPhoto: userImg.onFileRemove
					});
				},
				initUploader: function(){
					userImg.uploader.fileupload(userImg.uploaderOptions);
					userImg.uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
				},
				onFileRemove: function (button) {
					try {
						fileuploadRemove(button).then(function(response) {
							if ('success' === response.mess_type) {
								userImg.filesAllowed++;
								userImg.updateCounter();
							}
						});
					} catch (error) {
						if(__debug_mode) {
							console.error(error);
						}
					}
				},
				onUploadStart: function (event, files, index, xhr, handler, callBack) {
					if(files.files && files.files.length > userImg.filesAllowed){
						if(userImg.filesAllowed > 0) {
							systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', userImg.filesAmount), 'warning');
						} else {
							systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
						}
						userImg.uploadButton.fadeOut();
						event.abort();

						return;
					}

					userImg.uploadButton.fadeIn();
				},
				onUploadFinished: function (e, data){
					if (data.files.error){
                        var error_msg = data.files[0].error;

                        if ('File type not allowed' === error_msg) {
                            error_msg = 'Invalid file format. List of supported formats (<?php echo $fileupload['rules']['format']; ?>).';
                        }

						systemMessages(error_msg, 'error');
					}
				},
				onUploadDone: function (e, data) {
					if(data.result.mess_type == 'success'){
						if(data.result.files && Array.isArray(data.result.files)) {
							data.result.files.forEach(userImg.addImage);
						} else {
							userImg.addImage(data.result.files, 0);
						}

						userImg.updateCounter();
					} else {
						systemMessages(data.result.message, data.result.mess_type);
					}

                    if (typeof data.result.upload_result !== 'undefined') {
                        systemMessages(data.result.upload_result, 'success');
                    }

					userImg.uploadButton.fadeOut();
				},
				addImage: function (file, index) {
					userImg.filesAllowed--;

					var pictureId = file.id_photo;
					var photoClassDirection = ((file.type_photo == 'landscape')?'image--landscape':'image--portrait');
					var image = $('<img class="' + photoClassDirection + '">').attr({ src: file.thumb });

					var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
						title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
						class: 'btn btn-dark confirm-dialog',
						'data-file': file.id_photo,
						'data-action': userImg.fileRemoveUrl,
						'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
						'data-callback': 'fileploadRemoveUserPhoto',
					});
					var imageContent = $(templateFileUploadNew({
						type: 'img',
						index: pictureId,
						image: image.prop('outerHTML'),
						image_link: file.path,
						className: 'fileupload-image',
					}));

					imageContent.find('.fileupload-item__actions').append([closeButton]);
					userImg.imageWrapper.append(imageContent);

					if (userImg.imageWrapper.find('.fileupload-item').length === 1) {
						userImg.imageWrapper.find('.fileupload-item').first().trigger('click');
					}
				},
				updateCounter: function () {
					userImg.counter.text(userImg.filesAmount - userImg.filesAllowed);
				}
			});

		}());

		$(function() {
			userImg.init();
		});
	</script>
<?php }?>

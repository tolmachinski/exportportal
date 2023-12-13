<div class="col-12">
    <label class="input-label">Photos</label>
    <span class="btn btn-dark mnw-125 fileinput-button">
        <span>Select files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="add-edit-dispute--formfield--uploader" type="file" name="files[]" accept="<?php echo arrayGet($fileupload, 'limits.type.accept'); ?>">
    </span>
    <span class="fileinput-loader-btn" id="add-edit-dispute--formaction--upload-button" style="display:none;">
        <img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> Uploading...
    </span>

    <div class="info-alert-b mt-10">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div>
            <?php echo translate(
                'general_dashboard_modal_field_image_help_text_line_1',
                array(
                    '[[SIZE]]' => arrayGet($fileupload, 'limits.filesize.placeholder', '0B')
                ),
                true
            ); ?>
        </div>
        <div>
            <?php echo translate(
                'general_dashboard_modal_field_image_help_text_line_2',
                array(
                    '[[WIDTH]]'  => arrayGet($fileupload, 'limits.image.width', 0),
                    '[[HEIGHT]]' => arrayGet($fileupload, 'limits.image.height', 0),
                ),
                true
            ); ?>
        </div>
        <div>
            <?php echo translate(
                'general_dashboard_modal_field_image_help_text_line_3_alternate',
                array(
                    '[[TOTAL]]'   => arrayGet($fileupload, 'limits.amount.total'),
                    '[[ALLOWED]]' => arrayGet($fileupload, 'limits.amount.allowed'),
                ),
                true
            ); ?>
        </div>
        <div>
            • <?php echo translate(
                'general_dashboard_modal_field_image_help_text_line_4',
                array(
                    '[[FORMATS]]' => implode(', ', arrayGet($fileupload, 'limits.type.extensions'))
                ),
                true
            ); ?>
        </div>
    </div>

    <!-- The container for the uploaded files -->
    <div class="fileupload mt-10" id="add-edit-dispute--formfield--image-wrapper"></div>
</div>

<script>
	$(function() {
        var onUploadStart = function (event, files, index, xhr, handler, callBack) {
            if(files.files && files.files.length > filesAllowed){
                if(filesAllowed > 0) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', filesAmount), 'warning');
                } else {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                }
                uploadButton.fadeOut();
                event.abort();

                return;
            }

            uploadButton.fadeIn();
        };
        var onUploadFinished = function (e, data){
            if (data.files.error){
                systemMessages(data.files[0].error, 'error');
            }
        };
        var onUploadDone = function (e, data) {
            if(data.result.mess_type == 'success'){
                if(data.result.files && Array.isArray(data.result.files)) {
                    data.result.files.forEach(addImage);
                } else {
                    addImage(data.result.files, 0);
				}
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			uploadButton.fadeOut();
        };
		var onFileRemove = function (button) {
			try {
				fileuploadRemove(button).then(function(response) {
					if ('success' === response.mess_type) {
						filesAllowed++;
					}
				});
			} catch (error) {
				if(__debug_mode) {
					console.error(error);
				}
			}
		};
        var addImage = function (file, index) {
            filesAllowed--;

            var pictureId = index + '-' + new Date().getTime();
            var url = __img_url + file.fullPath;
			var image = $('<img>').attr({ src: file.fullPath, class: 'image' });
            var imageInput = $('<input>').attr({
                name: 'images[]',
                type: 'hidden',
                value: file.path
            });
			var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': fileRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': 'fileploadRemoveDisputeImage',
            });
            var imageContent = $(templateFileUploadNew({
                type: 'imgnolink',
                index: pictureId,
                image: image.prop('outerHTML'),
				image_link: url,
                className: 'fileupload-image',
            }));

            imageContent.find('.fileupload-item__actions').append([imageInput, closeButton]);
            imageWrapper.append(imageContent);
        };

        var canUpload = Boolean(~~parseInt('<?php echo (int) $can_upload_photos; ?>', 10));
        var filesAmount = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.total', 0); ?>', 10);
        var filesAllowed = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.allowed', 0); ?>', 10);
        var fileTypes = new RegExp('(<?php echo implode('|', arrayGet($fileupload, 'limits.type.mimetypes')); ?>)', 'i');
        var fileFormats = new RegExp('(\.|\/)(<?php echo implode('|', arrayGet($fileupload, 'limits.type.extensions')); ?>)', 'i');
        var fileUploadMaxSize = "<?php echo arrayGet($fileupload, 'limits.filesize.size', 0); ?>";
        var fileUploadTimestamp = "<?php echo arrayGet($fileupload, 'directory'); ?>";
        var fileUploadUrl = "<?php echo arrayGet($fileupload, 'urls.upload'); ?>";
        var fileRemoveUrl = "<?php echo arrayGet($fileupload, 'urls.delete'); ?>";
        var uploader = $('#add-edit-dispute--formfield--uploader');
        var uploadButton = $('#add-edit-dispute--formaction--upload-button');
        var imageWrapper = $('#add-edit-dispute--formfield--image-wrapper');
        var uploaderOptions = {
            url: fileUploadUrl,
            dataType: 'json',
            maxNumberOfFiles: filesAmount,
            maxFileSize: fileUploadMaxSize,
            acceptFileTypes: fileFormats,
            loadImageFileTypes: fileTypes,
            processalways: onUploadFinished,
            beforeSend: onUploadStart,
            done: onUploadDone,
        };

        if (canUpload) {
            uploader.fileupload(uploaderOptions);
            uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
        } else {
            uploader.prop('disabled', true).parent().addClass('disabled');
        }

		window.fileploadRemoveDisputeImage = onFileRemove;
    });
</script>

<div class="form-group" id="<?php echo $group; ?>--formfield--upload-container">
    <?php if (!empty($label)) { ?>
        <label class="input-label <?php echo $is_required ? 'input-label--required' : ''; ?>">
            <?php echo $label; ?>
        </label>
    <?php } ?>
    <div id="<?php echo $group; ?>--formaction--upload-button-container">
        <span class="btn btn-dark mnw-125 fileinput-button" id="<?php echo $group; ?>--formaction--upload-button">
            <span>
                <?php echo translate(arrayGet($translations, 'button_text', "general_dashboard_modal_field_image_upload_button_text"), null, true); ?>
            </span>
            <input <?php echo addQaUniqueIdentifier("global_upload_btn")?> id="<?php echo $group; ?>--formfield--uploader" type="file" name="files[]" accept="<?php echo arrayGet($fileupload, 'limits.type.accept'); ?>">
        </span>
        <span class="fileinput-loader-btn" id="<?php echo $group; ?>--formaction--upload-label" style="display:none;">
            <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader">
            <?php echo translate(arrayGet($translations, 'upload_text', "general_dashboard_modal_field_document_upload_placeholder"), null, true); ?>
        </span>
    </div>

    <div class="info-alert-b mt-10">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div>
            <?php echo translate(
                arrayGet($translations, 'size_text', 'general_dashboard_modal_field_document_help_text_line_1'),
                array('[[SIZE]]' => arrayGet($fileupload, 'limits.filesize.placeholder', '0B')),
                true
            ); ?>
        </div>
        <?php if('image' === $type) { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'image_resolution', 'general_dashboard_modal_field_image_help_image_resolution'),
                    array(
                        '[[WIDTH]]'  => arrayGet($fileupload, 'limits.image.width', 1920),
                        '[[HEIGHT]]' => arrayGet($fileupload, 'limits.image.height', 1080),
                    ),
                    true
                ); ?>
            </div>
        <?php } ?>
        <?php if ($is_limited) { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'limited_amount_text', 'general_dashboard_modal_field_document_help_text_line_2_limited'),
                    array(
                        '[[TOTAL]]'   => arrayGet($fileupload, 'limits.amount.total'),
                        '[[ALLOWED]]' => arrayGet($fileupload, 'limits.amount.allowed'),
                    ),
                    true
                ); ?>
            </div>
        <?php } else { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'amount_text', 'general_dashboard_modal_field_document_help_text_line_2'),
                    array('[[AMOUNT]]' => arrayGet($fileupload, 'limits.amount.total')),
                    true
                ); ?>
            </div>
        <?php } ?>
        <div>
            <?php echo translate(
                arrayGet($translations, 'format_text', 'general_dashboard_modal_field_document_help_text_line_3'),
                array('[[FORMATS]]' => implode(', ', arrayGet($fileupload, 'limits.type.extensions'))),
                true
            ); ?>
        </div>
        <!-- <?php if (!empty($maxItems = arrayGet($fileupload, 'limits.maxRowsItems'))) {?>
            <div>
                <?php echo translate('general_dashboard_modal_field_document_max_rows_items', ['{{COUNT_OF_LINES}}' => $maxItems]);?>
            </div>
        <?php }?> -->
    </div>
    <div class="fileupload mt-10" id="<?php echo $group; ?>--formfield--upload-wrapper"></div>
</div>

<script>
	$(function() {
        var onUploadStart = function (event, data, index, xhr, handler, callBack) {
            if(data.files && data.files.length > filesAllowed){
                if(filesAllowed > 0) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', filesAmount), 'warning');
                } else {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                }
                uploadLabel.fadeOut();
                event.abort();

                return;
            }

            uploadLabel.fadeIn();
            triggerEvent('upload:process-start', { uploadEvent: event, files: data.files || [], data: data, index: index });
        };
        var onUploadFailure = function (event, data) {
            triggerEvent('upload:process-failure', { uploadEvent: event, files: data.files || [], data: data });
            if (data.jqXHR && (data.jqXHR.status && "canceled" !== data.jqXHR.statusText)) {
                onRequestError(data.jqXHR);
            }

            uploadLabel.fadeOut();
        };
        var onUploadFinished = function (event, data){
            triggerEvent('upload:process-finish', { uploadEvent: event, files: data.files || [], data: data });
            if (data.files.error){
                triggerEvent('upload:process-failure', { uploadEvent: event, files: data.files || [], data: data });
                systemMessages(data.files[0].error, 'error');
            }
        };
        var onUploadDone = function (event, data) {
            triggerEvent('upload:process-done', { uploadEvent: event, files: data.files || [], data: data });
            if(data.result.mess_type == 'success'){
                triggerEvent('upload:process-success', { uploadEvent: event, files: data.files || [], data: data });
                if(data.result.files && Array.isArray(data.result.files)) {
                    if (usePreview) {
                        data.result.files.forEach(addUploadPreview.bind(undefined, data.files, true));
                    }
                } else if (usePreview) {
                    addUploadPreview(data.files, true, data.result.files, 0);
				}
            } else {
                triggerEvent('upload:process-failure', { uploadEvent: event, files: data.files || [], data: data });
                systemMessages(data.result.message, data.result.mess_type);
            }

			uploadLabel.fadeOut();
        };

		var onFileRemove = function (button) {
			try {
                fileuploadRemove(button, hasRemoteDeletion).then(function(response) {
                    if ($.isPlainObject(response)) {
                        if ('success' === response.mess_type) {
                            filesAllowed++;
                        }
                    } else {
                        if (response && !hasRemoteDeletion) {
                            filesAllowed++;
                        }
                    }

                    triggerEvent('upload:remove-preview-success', { data: response });
                });
			} catch (error) {
				if(__debug_mode) {
					console.error(error);
				}

                triggerEvent('upload:remove-preview-failure', { error: error });
			}
		};
        var blobToDataUrl = function(blob) {
            return new Promise(resolve => {
                var reader = new FileReader();
                reader.onloadend = () => resolve(reader.result.toString());
                reader.readAsDataURL(blob);
            });
        };
        var addUploadPreview = function (fileList, isUploaded, file, index) {
            filesAllowed--;

            var fileBlob = fileList[index] || null;
            var previewId = index + '-' + new Date().getTime();
            var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': fileRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': removeHandlerName,
            });
            var uploadInput = $('<input>').attr({
                value: file.name,
                name: hiddenInputName,
                type: 'hidden'
            });

            var finalizePreview = function (preview) {
                preview.find('.fileupload-item__actions').append([uploadInput, closeButton]);
                previewWrapper.append(preview);
                triggerEvent('upload:add-preview-success', { file: file, index: index, isUploaded: isUploaded });
            };

            if (isImageOnly) {
                blobToDataUrl(fileBlob)
                    .then(function(imageData) {
                        var image = $('<img>').attr({ src: imageData, class: 'image' });

                        return $(templateFileUploadNew({
                            type: 'imgnolink',
                            index: previewId,
                            image: image.prop('outerHTML'),
                            className: 'fileupload-image',
                        }));
                    })
                    .then(finalizePreview)
            } else {
                finalizePreview(
                    $(templateFileUploadNew({
                        type: 'files',
                        image: '',
                        index: previewId,
                        iconClassName: file.type  + " icon",
                        className: 'fileupload-image',
                    }))
                );
            }
        };
        var getUploader = function () {
            return $('#<?php echo $group; ?>--formfield--uploader');
        };
        var getContainer = function () {
            return $('#<?php echo $group; ?>--formfield--upload-container');
        }
        var triggerEvent = function (eventName, data) {
            getContainer().trigger(eventName, data);
        };

        var uploadedFiles = <?php echo json_encode($uploadedFiles ?? []); ?>;
        var hasRemoteDeletion = Boolean(~~parseInt('<?php echo (int) $has_remote_deletion; ?>', 10));
        var canUpload = Boolean(~~parseInt('<?php echo (int) $is_enabled; ?>', 10));
        var usePreview = Boolean(~~parseInt('<?php echo (int) $use_preview; ?>', 10));
        var isImageOnly = Boolean(~~parseInt('<?php echo (int) ('image' === $type); ?>', 10));
        var filesAmount = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.total', 0); ?>', 10);
        var filesAllowed = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.allowed', 0); ?>', 10);
        var fileTypes = new RegExp('(<?php echo implode('|', arrayGet($fileupload, 'limits.type.mimetypes')); ?>)', 'i');
        var fileFormats = new RegExp('(\.|\/)(<?php echo implode('|', arrayGet($fileupload, 'limits.type.extensions')); ?>)', 'i');
        var fileUploadMaxSize = "<?php echo arrayGet($fileupload, 'limits.filesize.size', 0); ?>";
        var fileUploadTimestamp = "<?php echo arrayGet($fileupload, 'directory'); ?>";
        var fileUploadUrl = "<?php echo arrayGet($fileupload, 'urls.upload'); ?>";
        var fileRemoveUrl = "<?php echo arrayGet($fileupload, 'urls.delete'); ?>";
        var uploader = $('#<?php echo $group; ?>--formfield--uploader');
        var uploadLabel = $('#<?php echo $group; ?>--formaction--upload-label');
        var previewWrapper = $('#<?php echo $group; ?>--formfield--upload-wrapper');
        var removeHandlerName = 'fileploadRemoveUplaodedFile<?php echo $hash; ?>';
        var hiddenInputName = '<?php echo $name; ?>';
        var uploaderOptions = {
            url: fileUploadUrl,
            dataType: 'json',
            maxNumberOfFiles: filesAmount,
            maxFileSize: fileUploadMaxSize,
            acceptFileTypes: fileFormats,
            loadImageFileTypes: fileTypes,
            processalways: onUploadFinished,
            beforeSend: onUploadStart,
            fail: onUploadFailure,
            done: onUploadDone,
        };

        if (canUpload) {
            uploader.fileupload(uploaderOptions);
            uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
        } else {
            uploader.prop('disabled', true).parent().addClass('disabled');
        }

        Promise.all(
            uploadedFiles.map(function (file) {
                return new Promise(function (resolve) {
                    var image = document.createElement('img');
                    var canvas = document.createElement("canvas");
                    var context = canvas.getContext("2d");


                    image.src = file.url;
                    image.onload = function () {
                        canvas.width = this.naturalWidth;
                        canvas.height = this.naturalHeight;
                        context.drawImage(this, 0, 0);
                        canvas.toBlob(function(blob) { resolve(blob); }, file.type);
                };
                });
            })
        ).then(function (fileList) {
            uploadedFiles.forEach(addUploadPreview.bind(undefined, fileList, false));
        });

        mix(globalThis, { [removeHandlerName]: onFileRemove }, false);
    });
</script>

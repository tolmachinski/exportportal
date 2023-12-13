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
                    arrayGet($translations, 'image_limit_text', 'general_dashboard_modal_field_image_help_text_line_2'),
                    array(
                        '[[WIDTH]]'  => arrayGet($fileupload, 'limits.image.width', 0),
                        '[[HEIGHT]]' => arrayGet($fileupload, 'limits.image.height', 0),
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
        <?php if (!empty($maxItems = arrayGet($fileupload, 'limits.maxRowsItems'))) {?>
            <div>
                <?php echo translate('general_dashboard_modal_field_document_max_rows_items', ['{{COUNT_OF_LINES}}' => $maxItems]);?>
            </div>
        <?php }?>
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
                        data.result.files.forEach(addUploadPreview);
                    }
                } else if (usePreview) {
                    addUploadPreview(data.result.files, 0);
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
        var addUploadPreview = function (file, index) {
            filesAllowed--;

            var previewId = index + '-' + new Date().getTime();
            var url = __img_url + file.path;
            var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': fileRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': removeHandlerName,
            });
            var uploadInput = $('<input>').attr({
                value: file.path,
                name: hiddenInputName,
                type: 'hidden'
            });

            if (isImageOnly) {
                var image = $('<img>').attr({ src: __img_url + file.path, class: 'image' });
                var previewContent = $(templateFileUploadNew({
                    type: 'imgnolink',
                    index: previewId,
                    image: image.prop('outerHTML'),
                    image_link: url,
                    className: 'fileupload-image',
                }));
            } else {
                var previewContent = $(templateFileUploadNew({
                    type: 'files',
                    image: '',
                    index: previewId,
                    iconClassName: file.type  + " icon",
                    className: 'fileupload-image',
                }));
            }

            previewContent.find('.fileupload-item__actions').append([uploadInput, closeButton]);
            previewWrapper.append(previewContent);
            triggerEvent('upload:add-preview-success', { file: file, index: index });
        };
        var getUploader = function () {
            return $('#<?php echo $group; ?>--formfield--uploader');
        };
        var triggerEvent = function (eventName, data) {
            getUploader().trigger(eventName, data);
        };

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

		window[removeHandlerName] = onFileRemove;
    });
</script>

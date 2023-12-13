<div id="<?php echo $group; ?>--formfield--uploader">
    <?php if (!empty($label)) { ?>
        <label class="input-label <?php echo $is_required ? 'input-label--required' : ''; ?>"><?php echo $label; ?></label>
    <?php } ?>
    <div class="info-alert-b mb-10">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div>
            <?php echo translate(
                arrayGet($translations, 'format_text', 'general_dashboard_modal_field_document_help_text_line_3_alternate'),
                array(
                    '[[FORMATS]]' => implode(', ', arrayGet($fileupload, 'limits.type.extensions', array()))
                ),
                true
            ); ?>
        </div>
        <div>
            <?php echo translate(
                arrayGet($translations, 'size_text', 'general_dashboard_modal_field_document_help_text_line_1_alternate'),
                array(
                    '[[SIZE]]' => arrayGet($fileupload, 'limits.filesize.placeholder', '0B')
                ),
                true
            ); ?>
        </div>
        <?php if ($is_limited) { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'limited_amount_text', 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate'),
                    array(
                        '[[TOTAL]]'   => arrayGet($fileupload, 'limits.amount.total', 0),
                        '[[ALLOWED]]' => arrayGet($fileupload, 'limits.amount.allowed', 0),
                    ),
                    true
                ); ?>
            </div>
        <?php } else { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'amount_text', 'general_dashboard_modal_field_document_help_text_line_2_alternate'),
                    array(
                        '[[AMOUNT]]' => arrayGet($fileupload, 'limits.amount.total', 0),
                    ),
                    true
                ); ?>
            </div>
        <?php } ?>
    </div>
    <div id="<?php echo $group; ?>--formfield--image" <?php echo addQaUniqueIdentifier("ep-docs__upload-document--iframe-wrapper");?> class="h-60"></div>
    <span class="fileinput-loader-btn" id="<?php echo $group; ?>--formfield--loader" style="display:none;">
        <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> Uploading...
    </span>
    <div class="row mt-10" id="<?php echo $group; ?>--formfield--image-container">
        <?php if (!empty($files)) { ?>
            <?php foreach ($files as $index => $file) { ?>
                <div id="fileupload-item-<?php echo $index; ?>" class="col-sm-2 col-md-2 item-wrapper">
                    <div class="uploadify-queue-item fileupload-item fileupload-image w-100pr icon">
                        <div class="fileupload-item__image img-b icon-files-<?php echo cleanOutput($file['extension']); ?>"></div>
                        <div class="fileupload-item__actions">
                            <input type="hidden" name="<?php echo cleanOutput($file['input']); ?>" value="<?php echo cleanOutput($file['id']); ?>">
                            <div class="cancel">
                                <a class="confirm-dialog"
                                    title="<?php echo translate("general_modal_field_document_button_delete_title"); ?>"
                                    data-message="<?php echo translate("general_modal_field_document_button_delete_message"); ?>"
                                    data-callback="removeUploadedDocument<?php echo $hash; ?>">
                                    <i class="ep-icon ep-icon_remove"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<script type="text/template" id="<?php echo $group; ?>--templates--upload-file--preview">
    <div id="fileupload-item-{{index}}" class="col-xs-6 col-sm-2 item-wrapper">
        <div class="uploadify-queue-item fileupload-item {{className}} icon">
            <div class="fileupload-item__image img-b {{icon.className}}"></div>
            <div class="fileupload-item__actions">
                {{> hiddenInput}}
                {{> deleteButton}}
            </div>
        </div>
    </div>
</script>
<script type="text/template" id="<?php echo $group; ?>--templates--upload-file--hidden-input">
    <input type="hidden" name="{{name}}" value="{{value}}">
</script>
<script type="text/template" id="<?php echo $group; ?>--templates--upload-file--delete-button">
    <div class="cancel">
        <a title="{{title}}" class="{{className}}" data-message="{{message}}" data-callback="{{callback}}">
            <i class="ep-icon ep-icon_remove"></i>
        </a>
    </div>
</script>
<script>
    $(function() {
        var renderFilePreview = function(context, embedded) {
            context = context || {};
            embedded = embedded || {};

            var partials = {};
            for (var key in embedded) {
                if (!Object.prototype.hasOwnProperty.call(embedded, key) || !Object.prototype.hasOwnProperty.call(templates.preview.children, key)) {
                    continue;
                }

                partials[key] = { template: templates.preview.children[key], context: embedded[key] };
            }

            return renderTemplate(templates.preview.main, context, partials);
        };
        var addDocumentPreview = function (container, file) {
            var fileId = new Date().getTime();
            var imageContent = renderFilePreview(
                {
                    index: fileId,
                    className: ['fileupload-image', 'w-100pr', additionalPreviewClasses].filter(function (f) { return f }).join(' '),
                    icon: {
                        className: "icon-files-" + file.extension.toLowerCase(),
                    }
                },
                {
                    hiddenInput: {
                        name: hiddenInputName,
                        type: 'hidden',
                        value: btoa(file.id)
                    },
                    deleteButton: {
                        text: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'}),
                        title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                        message: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                        callback: removeHandlerName,
                        className: 'confirm-dialog',
                    }
                }
            );

            container.append(imageContent);
            currentAmount++;

            return {
                image: imageContent,
                file: file,
                id: fileId,
            }
        };
        var removeDocumentPreview = function (button) {
            button.closest('.item-wrapper').remove();
            currentAmount--;
        };
        var handleUploadError = function (error) {
            switch (error.type) {
                case 'validation_error':
                    var list = error.data || {};
                    for (var key in list) {
                        if (list.hasOwnProperty(key)) {
                            systemMessages(list[key], 'error');
                        }
                    }

                    break;
                case 'propagation_error':
                    if(filesAmount > 0) {
                        systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', filesAmount), 'warning');
                    } else {
                        systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                    }

                    break;
                case "malware_error":
                    systemMessages(translate_js({ plug: "fileUploader", text: "error_malware_text" }), "error");
                    if (this.debug) {
                        console.error(error);
                    }

                    break;
                case 'domain_error':
                    if (__debug_mode) {
                        console.warn(error);
                    }

                    break;

                default:
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_default'}), 'error');
                    if (__debug_mode) {
                        console.error(error);
                    }

                    break;
            };
        };
        var showUploadLoader = function () {
            loaderWrapper.show();
        };
        var hideUploadLoader = function () {
            loaderWrapper.hide();
        };
        var showUploadButton = function () {
            uploadButtonWrapper.show();
        };
        var hideUploadButton = function () {
            uploadButtonWrapper.hide();
        };
        var onUpload = function (file) {
            var context = addDocumentPreview(container, file);
            dispatchEvent('upload', context.image, [context.id, context.file, container.find('.item-wrapper').toArray()]);

            hideUploadLoader();
            if (onUploadValidate()) {
                showUploadButton();
            }
            if (isModal) {
                $.fancybox.update();
            }
        };
        var onUploadStart = function () {
            dispatchEvent('start');

            showUploadLoader();
            hideUploadButton();
        };
        var onUploadError = function (error) {
            handleUploadError(error);
            dispatchEvent('error', null, [error, container.find('.item-wrapper').toArray()]);

            hideUploadLoader();
            showUploadButton();
        };
        var onUploadValidate = function () {
            dispatchEvent('validate', null, [currentAmount < filesAllowed, currentAmount, filesAllowed]);

            return currentAmount < filesAllowed;
        };
        var onDeleteDocument = function (button) {
            removeDocumentPreview(button);
            dispatchEvent('delete', null, [button, button.closest('.item-wrapper'), container.find('.item-wrapper').toArray()]);

            if (onUploadValidate()) {
                showUploadButton();
            }
            if (isModal) {
                $.fancybox.update();
            }
        };
        var dispatchEvent = function (event, element, args) {
            $(element || container).trigger('epd-uploader:' + event, args || []);
        };
        var attachUploader = function () {
            EPDocs[type](uploadButtonWrapperId, {
                start: onUploadStart,
                error: onUploadError,
                upload: onUpload,
                validate: onUploadValidate,
                maxFileSize: fileUploadMaxSize,
            });
        };

        var src = "<?php echo sprintf('%s/js/upload/index.js?integration-date=%s', config('env.EP_DOCS_CDN'), config('env.EP_DOCS_INTEGRATION_DATE', '2021-10-01')); ?>"
        var type = '<?php echo ucfirst($type); ?>';
        var isModal = Boolean(~~parseInt('<?php echo (int) $is_modal; ?>', 10));
        var canUpload = Boolean(~~parseInt('<?php echo (int) $is_enabled; ?>', 10));
        var filesAmount = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.total', 0); ?>', 10);
        var filesAllowed = parseInt('<?php echo arrayGet($fileupload, 'limits.amount.allowed', 0); ?>', 10);
        var currentAmount = 0;
        var fileUploadMaxSize = "<?php echo arrayGet($fileupload, 'limits.filesize.size', 0); ?>";
        var uploadButtonWrapperId = '#<?php echo $group; ?>--formfield--image';
        var additionalPreviewClasses = '<?php echo $preview_classes; ?>';
        var uploadButtonWrapper = $(uploadButtonWrapperId);
        var removeHandlerName = 'removeUploadedDocument<?php echo $hash; ?>';
        var hiddenInputName = '<?php echo $name; ?>';
        var loaderWrapper = $("#<?php echo $group; ?>--formfield--loader");
        var container = $('#<?php echo $group; ?>--formfield--image-container');
        var templates = {
            preview: {
                main: $("#<?php echo $group; ?>--templates--upload-file--preview").text() || '',
                children: {
                    hiddenInput: $("#<?php echo $group; ?>--templates--upload-file--hidden-input").text() || '',
                    deleteButton: $("#<?php echo $group; ?>--templates--upload-file--delete-button").text() || '',
                }
            }
        };

        if (canUpload) {
            if (typeof EPDocs !== 'undefined' && EPDocs) {
                attachUploader();
            } else {
                showLoader(container, '');
                getScript(src, true)
                    .then(function () { attachUploader(); })
                    .finally(function () { hideLoader(container); })
            }
        }

        if (currentAmount >= filesAllowed) {
            hideUploadButton();
        }

        window[removeHandlerName] = onDeleteDocument;
    });
</script>

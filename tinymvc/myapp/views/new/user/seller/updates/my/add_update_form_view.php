<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerUpdatesAddUpdateFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <div class="form-group" <?php echo addQaUniqueIdentifier('seller-updates-my__add-update_text-group_popup'); ?>>
                <label class="input-label input-label--required"><?php echo translate('seller_updates_dashboard_modal_field_description_label_text'); ?></label>
                <textarea name="text"
                    data-max="250"
                    id="js-add-document-text"
                    class="validate[required,maxSize[250]]"
                    placeholder="<?php echo translate('seller_updates_dashboard_modal_field_description_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('seller-updates-my__add-update_description-textarea_popup'); ?>></textarea>
            </div>

            <div class="form-group">
                <label class="input-label"><?php echo translate('general_dashboard_modal_field_image_label_text'); ?></label>

                <div>
                    <span class="btn btn-dark mnw-125 fileinput-button">
                        <span><?php echo translate('general_dashboard_modal_field_image_upload_button_text'); ?></span>
                        <input
                            id="js-add-document-uploader"
                            type="file"
                            name='file'
                            accept="<?php echo $fileupload_limits['accept']; ?>"
                            <?php echo addQaUniqueIdentifier('seller-updates-my__add-update_select-files-btn_popup'); ?>
                        >
                    </span>
                    <span class="fileinput-loader-btn" style="display:none;">
                        <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> <?php echo translate('general_dashboard_modal_field_image_upload_placeholder'); ?>
                    </span>

                    <div class="info-alert-b mt-10">
                        <i class="ep-icon ep-icon_info-stroke"></i>
                        <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => $fileupload_limits['image_size_readable'])); ?></div>
                        <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => $fileupload_limits['image_width'], '[[HEIGHT]]' => $fileupload_limits['image_height'])); ?></div>
                        <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => $fileupload_limits['amount'])); ?></div>
                        <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', $fileupload_limits['formats']))); ?></div>
                    </div>
                </div>

                <div class="container-fluid-modal pt-15">
                    <div class="row">
                        <div class="col-12 col-md-3 col-lg-3" id="js-add-document-image-wrapper">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="custom-checkbox" <?php echo addQaUniqueIdentifier('seller-updates-my__add-update_post-wall-checkbox_popup'); ?>>
                            <input name="post_wall" type="checkbox">
                            <span class="custom-checkbox__text"><?php echo translate('general_dashboard_modal_field_wall_flag_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('seller-updates-my__add-update_submit-btn_popup'); ?>
                >
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
    $(function() {
        $.fn.setValHookType = function (type) {
            this.each(function () {
                this.type = type;
            });

            return this;
        };

        var beforeUpload = function (event, files, index, xhr, handler, callBack) {
            if(files.files.length > uploadFileLimit){
                if(uploadFileLimit > 0) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', uploadFileLimit), 'warning');
                } else {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                }
                uploadButton.fadeOut();
                event.abort();

                return;
            }

            uploadButton.fadeIn();
        };
        var onUploadFinished = function(e,data){
            if (data.files.error){
                systemMessages(data.files[0].error, 'error');
            }
        };
        var onUploadDone = function (e, data) {
            if(data.result.mess_type == 'success'){
                addImage(0, data.result.files);
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			$('.fileinput-loader-btn').fadeOut();
        };
        var onSaveContent = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.js-wr-modal');
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray();
            var url = form.attr('action');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var beforeSend = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction('callbackAddUpdate', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var addImage = function(index, file) {
            uploadFileLimit--;

            var pictureId = index + '-' + new Date().getTime();
            var url = file.fullPath;
            var imageInput = $('<input>').attr({
                name: 'image',
                type: 'hidden',
                value: file.path
            });
            var imageContent = $(templateFileUploadNew({
                type: 'imgnolink',
                image: $('<img>').attr({ src: url }).prop('outerHTML'),
                index: pictureId,
                className: 'fileupload-image w-100pr',
                iconClassName: file.type,
            }));
            var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': imageRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': 'fileuploadRemove',
                'data-additional-callback': 'updateFileStats',
            });

            imageContent.find('.fileupload-item__actions').append([imageInput, closeButton]);
            imageWrapper.append(imageContent);
        };
        var onStatsUpdate = function() {
            uploadFileLimit++;
        };
        var initializeEditor = function(editor) {
            initNewTinymce(editor, {validate: 'validate[required,maxSize[250]]', valHook: 'editor'});
        };

        var uploadFileLimit = 1;
        var imageTypes = new RegExp('(<?php echo $fileupload_limits['mimetypes']; ?>)', 'i');
        var imageFormats = new RegExp('(.|\/)(<?php echo $fileupload_limits['formats']; ?>)', 'i');
        var imageUploadMaxSize = "<?php echo $fileupload_limits['image_size']; ?>";
        var imageUploadTimestamp = "<?php echo $upload_folder;?>";
        var imageUploadUrl = __site_url + 'seller_updates/ajax_seller_updates_upload_photo/' + imageUploadTimestamp;
        var imageRemoveUrl = __site_url + 'seller_updates/ajax_seller_updates_delete_photo/' + imageUploadTimestamp;
        var imageWrapper = $('#js-add-document-image-wrapper');
        var uploader = $('#js-add-document-uploader');
        var uploadButton = $('.fileinput-loader-btn');
        var descriptionField = $('#js-add-document-text');
        var uploaderOptions = {
            url: imageUploadUrl,
            dataType: 'json',
            maxNumberOfFiles: 1,
            maxFileSize: imageUploadMaxSize,
            acceptFileTypes: imageFormats,
            loadImageFileTypes: imageTypes,
            processalways: onUploadFinished,
            beforeSend: beforeUpload,
            done: onUploadDone,
        };
        var editorOptions = {
			target: descriptionField.get(0),
			height : 140,
			resize: false,
			menubar: false,
			statusbar : true,
			plugins: ["lists charactercount contextmenu paste"],
			toolbar: "undo redo | bold italic underline | numlist bullist |",
            contextmenu: "undo redo | bold italic underline | numlist bullist",
			dialog_type : "modal",
            paste_filter_drop: true,
            valid_elements: 'p,span,strong,em,b,i,u,ol,ul,li,br',
            paste_word_valid_elements: 'p,span,strong,em,b,i,u,ol,ul,li,br',
            paste_enable_default_filters: true,
            paste_webkit_styles: 'none',
            paste_webkit_styles: 'text-decoration',
            paste_data_images: false,
            paste_retain_style_properties: 'text-decoration',
            init_instance_callback: initializeEditor,
		};
        if(uploader.length) {
            uploader
                .fileupload(uploaderOptions)
                    .prop('disabled', !$.support.fileInput)
                    .parent()
                        .addClass($.support.fileInput ? undefined : 'disabled');
        }
        if(descriptionField.length) {
            tinymce.remove("#" + descriptionField.attr('id'));
            tinymce.init(editorOptions);
        }

        $.valHooks.editor = {
            get: function (el) {
                return tinymce.get(descriptionField.attr('id')).getContent({format : 'text'}) || "";
            }
        };

        window.sellerUpdatesAddUpdateFormCallBack = onSaveContent;
        window.updateFileStats = onStatsUpdate;
    });
</script>

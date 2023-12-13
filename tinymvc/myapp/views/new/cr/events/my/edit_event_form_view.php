<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>" id="edit-event--form">
        <input type="hidden" name="upload_folder" value="<?php echo $upload_folder; ?>"/>
        <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>"/>
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_name_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="240"
                            name="name"
                            id="edit-event--formfield--name"
                            class="validate[required,maxSize[240]]"
                            value="<?php echo cleanOutput(arrayGet($event, 'event_name')); ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_name_placeholder', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_type_label_text'); ?>
                        </label>
                        <select name="type" class="form-control validate[required]" id="edit-event--formfield--type">
                            <option value="" selected><?php echo translate('cr_events_dasboard_modal_event_field_type_placeholder'); ?></option>
                            <?php if (!empty($types)) { ?>
                                <?php foreach ($types as $type) { ?>
                                    <option value="<?php echo $type['id']; ?>" <?php echo selected($type['id'], $event['event_id_type']); ?>>
                                        <?php echo cleanOutput($type['event_type_name']); ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_image_label_text'); ?>
                        </label>
                        <span class="btn btn-dark mnw-125 fileinput-button">
                            <span><?php echo translate('cr_events_dasboard_modal_event_field_image_upload_button_text'); ?></span>
                            <input id="edit-event--formfield--uploader" type="file" name="files" accept="<?php echo $fileupload_limits['accept']; ?>">
                        </span>
                        <span class="fileinput-loader-btn" style="display:none;">
                            <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> <?php echo translate('cr_events_dasboard_modal_event_field_image_upload_placeholder'); ?>
                        </span>

                        <div class="info-alert-b mt-10">
                            <i class="ep-icon ep-icon_info-stroke"></i>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => $fileupload_limits['image_size_readable'])); ?></div>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => $fileupload_limits['image_width'], '[[HEIGHT]]' => $fileupload_limits['image_height'])); ?></div>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => $fileupload_limits['amount'])); ?></div>
                            <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', $fileupload_limits['formats']))); ?></div>
                        </div>

                        <div class="container-fluid-modal pt-15 pr-15" id="edit-event--formfield--image-container">
                            <div class="row">
                                <div class="col-12 col-md-3 col-lg-3" id="edit-event--formfield--image-wrapper">
                                    <?php if(!empty($event['event_image'])) { ?>
                                        <div id="fileupload-item-0" class="fileupload-item fileupload-image w-100pr">
                                            <div class="fileupload-item__image">
                                                <span class="link">
                                                    <img class="image" src="<?php echo $event['event_image_url']; ?>" />
                                                </span>
                                            </div>
                                            <div class="fileupload-item__actions">
                                                <a class="btn btn-dark confirm-dialog"
                                                    data-file="<?php echo $event['id_event']; ?>"
                                                    data-action="<?php echo __SITE_URL . "cr_events/ajax_event_delete_files"; ?>"
                                                    data-callback="fileuploadRemove"
                                                    data-additional-callback="updateFileStats"
                                                    data-message="<?php echo translate("general_modal_field_image_button_delete_message"); ?>"
                                                    title="<?php echo translate("general_modal_field_image_button_delete_title"); ?>">
                                                    <?php echo translate("general_modal_field_image_button_delete_text"); ?>
                                                </a>
                                                <input type="hidden" name="main_image" value="<?php echo $event['event_image_path']; ?>">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_start_date_label_text'); ?>
                        </label>
                        <input type="text"
                            autocomplete="off"
                            name="date_start"
                            id="edit-event--formfield--start-date"
                            class="validate[required] date-picked"
                            value="<?php echo !empty($event['event_date_start']) ? formatDate($event['event_date_start'], 'm/d/Y') : null; ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_start_date_placeholder', null, true); ?>">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_end_date_label_text'); ?>
                        </label>
                        <input type="text"
                            autocomplete="off"
                            name="date_end"
                            id="edit-event--formfield--end-date"
                            class="validate[required] date-picked"
                            value="<?php echo !empty($event['event_date_end']) ? formatDate($event['event_date_end'], 'm/d/Y') : null; ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_end_date_placeholder', null, true); ?>">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_country_label_text'); ?>
                        </label>
                        <input type="text"
                            autocomplete="off"
                            id="edit-event--formfield--country-static"
                            value="<?php echo cleanOutput($event['country']); ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_country_placeholder_alternate_text', null, true); ?>"
                            readonly>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_state_label_text'); ?>
                        </label>
                        <select name="state"
                            id="edit-event--formfield--state"
                            class="form-control validate[required] w-100pr">
                            <option value=""><?php echo translate("cr_events_dasboard_modal_event_field_state_placeholder_text"); ?></option>
                            <?php if(!empty($states)) { ?>
                                <?php foreach($states as $state) { ?>
                                    <option value="<?php echo $state['id']; ?>" <?php echo selected($state['id'], $event['event_id_state']); ?>>
                                        <?php echo cleanOutput($state['state']); ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 wr-select2-h50 relative-b">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_city_label_text'); ?>
                        </label>
                        <select name="city"
                            id="edit-event--formfield--city"
                            class="form-control validate[required] w-100pr event-select-city"
                            data-placeholder="<?php echo translate("cr_events_dasboard_modal_event_field_state_city_text", null, true); ?>">
                            <option></option>
                            <?php if(!empty($city)) { ?>
                                <option value="<?php echo $city['id']; ?>" selected>
                                    <?php echo cleanOutput($city['city']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_postal_code_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="20"
                            name="zip"
                            id="edit-event--formfield--postal-code"
                            class="validate[required,maxSize[20]]"
                            value="<?php echo cleanOutput(arrayGet($event, 'event_zip')); ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_postal_code_placeholder', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_address_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="255"
                            name="address"
                            id="edit-event--formfield--address"
                            class="validate[required,maxSize[255]]"
                            value="<?php echo cleanOutput(arrayGet($event, 'event_address')); ?>"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_address_placeholder', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_short_description_label_text'); ?>
                        </label>
                        <textarea name="short_description"
                            id="edit-event--formfield--short-description"
                            class="validate[required,maxSize[500]] h-80 textcounter"
                            data-max="500"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_short_description_placeholder', null, true); ?>"
                        ><?php echo cleanOutput(arrayGet($event, 'event_short_description')); ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dasboard_modal_event_field_full_description_label_text'); ?>
                        </label>
                        <textarea name="description"
                            id="edit-event--formfield--full-description"
                            class="validate[required] h-80 event-text-block"
                            data-max="60000"
                            placeholder="<?php echo translate('cr_events_dasboard_modal_event_field_full_description_placeholder', null, true); ?>"
                        ><?php echo cleanOutput(arrayGet($event, 'event_description')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="edit-event--formaction--submit">
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
                addImage(0, data.result);
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			$('.fileinput-loader-btn').fadeOut();
        };
        var onSaveContent = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.js-modal-flex');
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
                    callFunction('callbackEditEvent', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var addImage = function(index, file) {
            uploadFileLimit--;

            var pictureId = index + '-' + new Date().getTime();
            var url = __img_url + '/' + file.path;
            var imageInput = $('<input>').attr({
                name: 'main_image',
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
            updateCitiesList();
        };
        var onStatsUpdate = function() {
            uploadFileLimit++;
            dataT.fnDraw(false);
            updateCitiesList();
        };
        var onDatepickerShow = function (input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var initializeEditor = function(editor) {
            var container = $(editor.editorContainer);
            var containerId = container.attr('id');
            var showPrompt = function (e) {
                var selector = "." + containerId + 'formError';
                var errorBox = container.siblings(selector);
                if(errorBox.length) {
                    errorBox.show();
                    errorBox.css('opacity', 1);
                }
            };
            var hidePrompt =  function (e) {
                var selector = "." + containerId + 'formError';
                var errorBox = container.siblings(selector);
                if(errorBox.length) {
                    errorBox.hide();
                    errorBox.css('opacity', 0);
                }
            };
            var reValidate = function () {
                container.validationEngine('validate');
            };

            container.addClass('validate[required,maxSize[60000]]').setValHookType('editor').on('blur', hidePrompt);
            editor.on('blur', hidePrompt);
            // editor.on('dirty', reValidate);
            editor.on('click change', function() {
                var htmlLength = this.getContent({format : 'html'}).length;
                var textLength = this.getContent({format : 'text'}).length;
                var isEmptyHtml = htmlLength === 0;
                var isEmptyText = textLength === 0;
                var hasErrorBox = container.siblings("." + containerId + 'formError').length !== 0;
                if((isEmptyHtml || isEmptyText)) {
                    if(hasErrorBox) {
                        reValidate();
                        showPrompt();
                    }
                } else {
                    if(textLength > 60000) {
                        if(hasErrorBox) {
                            reValidate();
                            showPrompt();
                        }
                    } else {
                        reValidate();
                        hidePrompt();
                    }
                }
            });
        };
        var onChangeState = function(event) {
            var self = $(this);
            var region = self.val() || null;
            if(null !== region) {
                citiesList.prop('disabled', false);
                bindValidationToCities(citiesList)
            }
        };
        var onCitiesSearchRequest = function (params) {
            return {
                page: params.page,
                search: params.term,
                state: statesList.val() || null,
            };
        };
        var onCitiesSearchResponse = function (data, params) {
            params.page = params.page || 1;
            data.items.forEach(function(item) {
                item.text = item.name;
            });

            return {
                results: data.items,
                pagination: {
                    more: (params.page * data.per_p) < data.total_count
                }
            };
        };
        var onCitiesResultShow = function (e) {
            this.dropdown._positionDropdown();
        };
        var bindValidationToCities = function (node) {
            var type;
            var selectInstance = node.data('select2') || null;
            if(null === selectInstance) {
                return;
            }

            type = selectInstance.$container.prop('type') || null;
            if(null === type){
                selectInstance.$container.attr('id', 'edit-event--formfield--city-container')
                    .addClass('validate[required]')
                    .setValHookType('citiesList');
            }
        };
        var normalizeText = function (text) {
            if(text.length === 1 && text.charCodeAt() === 10) {
                return "";
            }

            var decodedStripped = text;
            decodedStripped = decodedStripped.replace(/\xA0/g,' ');
            decodedStripped = decodedStripped.replace(/\r/g, ' ');
            decodedStripped = decodedStripped.replace(/\n/g, ' ');
            decodedStripped = decodedStripped.replace(/\t/g, ' ');

            return text;
        };
        var updateCitiesList = function () {
            if(citiesList.length) {
                citiesList.select2('destroy');
                citiesList.select2(citiesOptions);
            }
        };

        var uploadFileLimit = parseInt('<?php echo (int) empty($event['event_image']); ?>', 10);
        var imageTypes = new RegExp('(<?php echo $fileupload_limits['mimetypes']; ?>)', 'i');
        var imageFormats = new RegExp('(.|\/)(<?php echo $fileupload_limits['formats']; ?>)', 'i');
        var imageUploadMaxSize = "<?php echo $fileupload_limits['image_size']; ?>";
        var imageUploadTimestamp = "<?php echo $upload_folder;?>";
        var imageUploadUrl = __site_url + 'cr_events/ajax_upload_image/' + imageUploadTimestamp;
        var imageRemoveUrl = __site_url + 'cr_events/ajax_event_delete_temp_files/' + imageUploadTimestamp;
        var uploader = $('#edit-event--formfield--uploader');
        var uploadButton = $('.fileinput-loader-btn');
        var imageWrapper = $('#edit-event--formfield--image-wrapper');
        var shortDescription = $('#edit-event--formfield--short-description');
        var fullDescription = $('#edit-event--formfield--full-description');
        var datepickers = $('.date-picked');
        var statesList = $('#edit-event--formfield--state');
        var citiesList = $('#edit-event--formfield--city');

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
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };
        var datepickerOptions = {
            beforeShow: onDatepickerShow
        };
        var editorOptions = {
            theme: 'modern',
            height : 350,
            resize: false,
			menubar: false,
			statusbar: true,
			language: __site_lang,
            plugins: ["media autolink lists link image contextmenu charactercount paste fullscreen"],
            toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | numlist bullist | link | media | fullscreen',
            contextmenu: "undo redo | bold italic underline | numlist bullist | link | media",
            timestamp: imageUploadTimestamp,
            relative_urls: false,
            convert_urls: false,
            remove_script_host: false,
            media_poster: false,
			media_alt_source: false,
            paste_filter_drop: true,
            paste_word_valid_elements: 'a,p,span,strong,em,b,i,u,ol,ul,li,br,h3,h4,h5,h6',
            paste_enable_default_filters: true,
            paste_webkit_styles: 'none',
            paste_webkit_styles: 'text-decoration',
            paste_data_images: false,
            paste_retain_style_properties: 'text-decoration',
            dialog_type: 'modal',
            style_formats: [
                { title: 'H3', block: 'h3' },
                { title: 'H4', block: 'h4' },
                { title: 'H5', block: 'h5' },
                { title: 'H6', block: 'h6' },
            ],
            init_instance_callback: initializeEditor,
        };
        var citiesOptions = {
            ajax: {
                delay: 250,
                type: 'POST',
                dataType: 'json',
                url: __site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest,
                processResults: onCitiesSearchResponse,
            },
            width: '100%',
            minimumInputLength: 2,
            language: __site_lang,
            theme: "default ep-select2-h30",
        };

        if(uploader.length) {
            uploader
                .fileupload(uploaderOptions)
                    .prop('disabled', !$.support.fileInput)
                    .parent()
                        .addClass($.support.fileInput ? undefined : 'disabled');
        }
        if(shortDescription.length) {
            shortDescription.textcounter(counterOptions);
        }
        if(fullDescription.length) {
            tinymce.remove('#' + fullDescription.attr('id'));
            tinymce.init(Object.assign({ target: fullDescription.get(0) }, editorOptions));
            $.valHooks.editor = {
                get: function (el) {
                    var editor = tinymce.get(fullDescription.attr('id'));

                    return normalizeText(editor.getContent({format : 'text'}) || "");
                }
            };
        }
        if(datepickers.length) {
            datepickers.datepicker(datepickerOptions);
        }
        if(statesList.length) {
            statesList.on('change', onChangeState);
        }
        if(citiesList.length) {
            citiesList.select2(citiesOptions).on("results:message", onCitiesResultShow);
            bindValidationToCities(citiesList);
            $.valHooks.citiesList = {
                get: function (el) {
                    return citiesList.val() || '';
                },
                set: function (el, val) {
                    citiesList.val(val);
                }
            };
        }

        window.modalFormCallBack = onSaveContent;
        window.updateFileStats = onStatsUpdate;
    });
</script>

<div class="wr-modal-flex inputs-40" id="bulk-items-upload--formwrapper">
    <form
        id="bulk-items-upload--form"
        class="h-100pr validateModal"
        data-callback="itemsDraftUploadFormCallBack"
    >
        <div id="bulk-items-upload--formfield--descriptions" class="modal-flex__form">
            <div class="modal-flex__content pb-0">
                <div class="info-alert-b mb-10"><i class="ep-icon ep-icon_info-stroke"></i>
                    <div class="txt-medium"><?php echo translate('bulk_item_upload_form_info'); ?></div>
                    <div><?php echo translate('bulk_item_upload_form_info_line_two', [
                        '{{START_TAG}}'   => '<a href="#" class="bulk-upload__btn call-function" data-callback="downloadGuide" data-guide-name="item_bulk_upload" data-lang="en" data-group="all">',
                        '{{END_TAG}}'     => '</a>',
                        '{{START_VIDEO}}' => '<a class="bulk-upload__btn call-function" data-callback="openVideoModal" href="#" data-title="' . translate('popup_bulk_item_upload_ttl', null, true) . '" data-href="' .  config("my_items_bulk_upload_video_url") . '" data-autoplay="true" title="' .  translate('popup_bulk_item_upload_ttl', null, true) . '" data-mw="1920" data-w="80%" data-h="88%">',
                        '{{END_VIDEO}}'   => '</a>'
                    ]); ?>
                    </div>
                </div>
                <?php widgetFileUploader(
                    $fileupload,
                    null,
                    'bulk-items-upload',
                    'file',
                    true,
                    false,
                    false,
                    false,
                    true,
                    'mixed',
                    array(
                        'button_text'   => 'items_drafts_dashboard_modal_field_uploader_button_text',
                        'amount_text'   => 'general_dashboard_modal_field_document_help_text_line_2_file'
                    )
                ); ?>

                <div class="custom-checkbox-wrap">
                    <label class="custom-checkbox mt-15">
                        <input class="checkbox-xls-row js-processing" type="checkbox" name="first_row" value="1">
                        <span class="custom-checkbox__text">Use first row as column(s) name</span>
                    </label>
                </div>
                <?php if (!empty($upload_config)) { ?>
                    <label class="custom-checkbox">
                        <input class="checkbox-xls-row" type="checkbox" name="user_config" value="1">
                        <span class="custom-checkbox__text">Use my saved configuration</span>
                    </label>
                <?php } ?>

                <div class="mt-15 mb-10">Here is the list of available fields. The marked <strong class="txt-red fs-18">*</strong> options are required.</div>

                <div class="mb-15">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="txt-medium">Title <span class="txt-red fs-18">*</span></div>
                            <div class="fs-14">Indicate the title of your product(s).</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Price</div>
                            <div class="fs-14">Indicate the product’s price in USD.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Discount Price</div>
                            <div class="fs-14">Price in USD with a discount, if set it will represent the intended price for sale.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Quantity</div>
                            <div class="fs-14">Indicate the total quantity of products available for purchase.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Min sale quantity</div>
                            <div class="fs-14">This option allows you to set a minimal quantity of products for purchase.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Max sale quantity</div>
                            <div class="fs-14">This option allows you to set a maximal quantity of products for purchase.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Weight</div>
                            <div class="fs-14">This cell allows the sellers to enter the net weight of the product(s) in kg.</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Sizes</div>
                            <div class="fs-14">Specify the product's size, (cm) LxWxH, e.g. 10x5x20</div>
                        </li>
                        <li class="list-group-item">
                            <div class="txt-medium">Video</div>
                            <div class="fs-14">The option allows you to insert the video link (youtube or vimeo) for your products’/ product’s advertisement.</div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="modal-flex__btns js-buttons-container" id="bulk-items-upload--formfield--descriptions-buttons-container">
                <div class="modal-flex__btns-right">
                    <button id="bulk-items-upload--formfield--descriptions-buttons-next" class="btn btn-primary call-function" data-callback="goToConfigurations" type="button" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div id="bulk-items-upload--formfield--configurations" class="modal-flex__form" style="display:none;">
            <div class="modal-flex__content pb-0">
                <div class="container-fluid-modal">
                    <div id="bulk-items-upload--formfield--configuration-information"></div>
                </div>
            </div>
            <div class="modal-flex__btns js-buttons-container" id="bulk-items-upload--formfield--configurations-buttons-container">
                <div class="modal-flex__btns-left">
                    <div class="dropdown">
                        <a class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>

                        <div class="dropdown-menu">
                            <a class="dropdown-item call-function" href="#" data-callback="saveUploadConfiguration">
                                <i class="ep-icon ep-icon_save"></i> Remember this configuration
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-flex__btns-right">
                    <button class="btn btn-dark call-function" type="button" data-callback="backToUpload">
                        Back
                    </button>
                    <button class="btn btn-success" type="submit">
                        Import
                    </button>
                </div>
            </div>
        </div>

        <div id="bulk-items-upload--formfield--upload-results-wrapper" class="modal-flex__form" style="display:none;">
            <div class="modal-flex__content pb-0">
                <div id="bulk-items-upload--formfield--upload-results-contents"></div>
            </div>
            <div class="modal-flex__btns js-buttons-container" id="bulk-items-upload--formfield--result-buttons-container">
                <div class="modal-flex__btns-left">
                    <button class="btn btn-dark call-function" type="button" data-callback="backToConfiguration">
                        Back to configuration
                    </button>
                </div>
                <div class="modal-flex__btns-right">
                    <button id="bulk-items-upload--formfield--result-buttons-close" style="display:none;" class="btn btn-primary call-function" data-callback="closeFancyBox" type="button">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    $(function() {
        var form = $('#bulk-items-upload--form');
        var fileInput = $('#bulk-items-upload--formfield--upload-input');
        var fileButton = $('#bulk-items-upload--formfield--upload-button');
        var closeButton = $('#bulk-items-upload--formfield--result-buttons-close')
        var formWrapper = $('#bulk-items-upload--formwrapper');
        var resultWrapper = $('#bulk-items-upload--formfield--upload-results-wrapper');
        var resultContents = $('#bulk-items-upload--formfield--upload-results-contents');
        var nextStepButton = $('#bulk-items-upload--formfield--descriptions-buttons-next');
        var uploadContainer = $('#bulk-items-upload--formfield--upload-container');
        var fieldsDescription = $('#bulk-items-upload--formfield--descriptions');
        var fieldsConfigurations = $('#bulk-items-upload--formfield--configurations');
        var uploadButtonContainer = $('#bulk-items-upload--formaction--upload-button-container');
        var configurationInformation = $('#bulk-items-upload--formfield--configuration-information');
        var buttonsContainer = form.find('.js-buttons-container');
        var currentFile = null;
        var saveDraftUrl = new URL('<?php echo $urls['draft']['href'] ?? null; ?>');
        var saveConfigstUrl = new URL('<?php echo $urls['save_configs']['href'] ?? null; ?>');
        var showConfigstUrl = new URL('<?php echo $urls['show_configs']['href'] ?? null; ?>');

        var onRequestEnd = function() {
            hideLoader(formWrapper);
        };
        var onRequestStart = function() {
            showLoader(formWrapper);
        };
        var onSaveContent = function(formElement) {
            var url = saveDraftUrl;
            var form = $(formElement);
            var formData = form.serialize();
            var submitButton = form.find('button[type=submit]');
            var onRequestEnd = function() {
                hideLoader(formWrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestStart = function() {
                showLoader(formWrapper);
                submitButton.addClass('disabled');
            };
            var onRequestSuccess = function(response) {
                if ('success' === response.mess_type) {
                    callFunction('bulkImportCallback', true);
                    open_result_modal({
                        title: 'Success',
                        content: response.message,
                        type: 'success',
                        closable: true,
                        buttons: [{
                            label: translate_js({
                                plug: "BootstrapDialog",
                                text: "close"
                            }),
                            cssClass: "btn btn-light",
                            action: function(dialog) {
                                dialog.close();
                            },
                        }]
                    });

                    $.fancybox.close();

                    updateDataTables(false);
                } else {
                    systemMessages(response.message, response.mess_type);
                }

            };
            var onRequestFailure = function(error) {
                if (error.xhr) {
                    var response = error.xhr.responseJSON || null;
                    if (null !== response && response.has_record_errors) {
                        fieldsConfigurations.hide();
                        resultContents.html(response.upload_results);
                        resultWrapper.show();
                        closeButton.show();

                        return;
                    }
                }

                onRequestError(error)
            }
            onRequestStart();

            return postRequest(url, formData)
                .then(onRequestSuccess)
                .catch(onRequestFailure)
                .then(onRequestEnd);
        };
        var onSaveConfig = function(form, wrapper, button) {
            var url = saveConfigstUrl;
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
            };
            onRequestStart();

            return postRequest(url, form.serializeArray())
                .then(onRequestSuccess)
                .catch(onRequestError)
                .then(onRequestEnd);
        };
        var onProceeed = function(button) {
            return getConfigurations(currentFile);
        };
        var onReturnToUpload = function(button) {
            fieldsConfigurations.hide();
            configurationInformation.empty();
            fieldsDescription.show();
        };
        var onReturnToConfigurations = function(button) {
            resultWrapper.hide();
            resultContents.empty();
            fieldsConfigurations.show();
        };
        var onUploadFile = function(event, parameters) {
            var url = showConfigstUrl;
            var data = form.serializeArray();
            var response = parameters.data && typeof parameters.data.result !== 'undefined' ? parameters.data.result : null;
            var files = response.files || [];
            var file = files[0] || null;
            if (!response || !file) {
                return;
            }

            currentFile = file;
        };
        var onAddPreview = function() {
            nextStepButton.prop('disabled', false);
            uploadButtonContainer.hide();
        };
        var onRemoveFile = function() {
            currentFile = null;
            nextStepButton.prop('disabled', true);
            uploadButtonContainer.show();
        };
        var getConfigurations = function(file) {
            var url = showConfigstUrl;
            var data = form.serializeArray();
            var filterResponse = function(response) {
                if (['success', 'warning', 'error'].indexOf(response.mess_type) === -1) {
                    throw new Error("The response status is not supported.");
                }
                if ('warning' === response.mess_type || 'error' === response.mess_type) {
                    var error = new Error(response.message)
                    error.isGeneric = true;
                    error.mess_type = response.mess_type;

                    throw error;
                }

                return {
                    form: response.config_form || '',
                    columnConfigs: response.xls_columns_config || {}
                };
            };
            var onRequestSuccess = function(response) {
                var form = response.form;
                var configurations = response.columnConfigs;

                window.selectState = response.state;
                configurationInformation.html(form);
                fieldsConfigurations.show();
                fieldsDescription.hide();
                buttonsContainer.show();
            };

            onRequestStart();
            for (var key in file) {
                if (file.hasOwnProperty(key)) {
                    var value = file[key];
                    data.push({
                        name: 'file[' + key + ']',
                        value: value
                    });
                }
            }

            return postRequest(url, data)
                .then(filterResponse)
                .then(onRequestSuccess)
                .catch(onRequestError)
                .then(onRequestEnd);
        };

        uploadContainer.on('upload:process-success', onUploadFile);
        uploadContainer.on('upload:add-preview-success', onAddPreview);
        uploadContainer.on('upload:remove-preview-success', onRemoveFile);

        mix(window, {
            backToUpload: onReturnToUpload,
            itemsDraftUploadFormCallBack: onSaveContent,
            goToConfigurations: onProceeed,
            backToConfiguration: onReturnToConfigurations,
            saveUploadConfiguration: onSaveConfig.bind(null, form, formWrapper),
        }, false);
    });
</script>

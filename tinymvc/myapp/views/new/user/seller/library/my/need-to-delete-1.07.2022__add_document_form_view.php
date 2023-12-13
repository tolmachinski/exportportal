<div class="js-wr-modal wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
        <input type="hidden" name="id" value="<?php echo $document["id_file"];?>"/>
        <div class="modal-flex__content">
            <div class="form-group">

                <?php views("new/user/seller/categories_field_view", array("categories" => $library_categories)); ?>

                <!-- <label class="input-label input-label--required"><?php echo translate('seller_library_dashboard_modal_field_category_label_text'); ?></label>

                <div id="js-add-video-category" class="input-group"></div> -->
<!--
                <?php if(!empty($library_categories)){?>
                    <div class="input-group">
                        <select name="library_category" class="form-control validate[required]" id="js-add-document-category">
                            <option value=""><?php echo translate('seller_library_dashboard_modal_field_category_placeholder'); ?></option>
                            <?php foreach($library_categories as $category){ ?>
                                <option value="<?php echo $category['id_category']; ?>" <?php echo selected($document['id_category'], $category['id_category']); ?>>
                                    <?php echo $category['category_title']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="input-group-btn">
                            <button href="<?php echo $category_url; ?>"
                                class="btn btn-dark js-validate-modal"
                                data-href="<?php echo $category_url; ?>"
                                data-validate="1"
                                data-close-click="none"
                                data-title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>"
                                title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>">
                                <span class="d-none d-sm-inline">
                                    <?php echo translate('seller_library_categories_dashboard_add_category_button_title'); ?>
                                </span>
                                <i class="ep-icon ep-icon_plus d-inline d-sm-none"></i>
                            </button>
                        </div>
                    </div>
                <?php } else{?>
                    <p class="pb-20"><?php echo translate('seller_library_no_categories_pleas_add_message'); ?></p>
                    <button
                        class="btn btn-dark btn-block js-validate-modal"
                        data-href="<?php echo $category_url; ?>"
                        data-validate="1"
                        data-close-click="none"
                        data-title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>"
                        title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>">
                        <span class="d-none d-sm-inline">
                            <?php echo translate('seller_library_categories_dashboard_add_category_button_title'); ?>
                        </span>
                        <i class="ep-icon ep-icon_plus d-inline d-sm-none"></i>
                    </a>
                <?php }?> -->

            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_title_label_text'); ?>
                </label>
                <input
                    type="text"
                    name="title"
                    class="validate[required,maxSize[50]]"
                    placeholder="<?php echo translate('seller_library_dashboard_modal_field_document_title_placeholder_text', null, true); ?>"
                    value="<?php if(isset($document)) echo $document['title_file'];?>"/>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_access_type_label_text'); ?>
                </label>

                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item pb-0">
                        <label class="list-form-checked-info__label">
                            <input class="js-add-document-access-type" type="radio" name="file_type" value="private" checked <?php echo checked($document['type_file'], 'private'); ?>/>
                            <span class="list-form-checked-info__check-text">
                                <?php echo translate('seller_library_dashboard_modal_field_document_access_type_private_label_text'); ?>
                            </span>
                            <span class="txt-gray ml-5">(<?php echo translate('seller_library_dashboard_modal_field_document_access_type_private_help_text'); ?>)</span>
                        </label>
                    </li>
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label">
                            <input class="js-add-document-access-type" type="radio" name="file_type" value="public" <?php echo checked($document['type_file'], 'public'); ?>/>
                            <span class="list-form-checked-info__check-text">
                                <?php echo translate('seller_library_dashboard_modal_field_document_access_type_public_label_text'); ?>
                            </span>
                            <span class="txt-gray ml-5">(<?php echo translate('seller_library_dashboard_modal_field_document_access_type_public_help_text'); ?>)</span>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_description_label_text'); ?>
                </label>
                <textarea name="text"
                    id="js-add-document-document-description"
                    class="validate[required,maxSize[250]]"
                    data-max="250"
                    placeholder="<?php echo translate('seller_library_dashboard_modal_field_document_description_placeholder_text', null, true); ?>"><?php if(isset($document)) echo $document['description_file'];?></textarea>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_library_dashboard_modal_field_document_label_text'); ?></label>
                <span class="btn btn-dark mnw-125 fileinput-button">
                    <span><?php echo translate('seller_library_dashboard_modal_field_document_upload_button_text'); ?></span>
                    <input id="js-add-document-uploader" type="file" name="files[]">
                </span>
                <span class="fileinput-loader-btn" style="display:none;">
                    <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> <?php echo translate('seller_library_dashboard_modal_field_document_upload_placeholder'); ?>
                </span>

                <div class="info-alert-b mt-10">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div><?php echo translate('general_dashboard_modal_field_document_help_text_line_1', array('[[SIZE]]' => $fileupload_limits['filesize_readable'])); ?></div>
                    <div><?php echo translate('general_dashboard_modal_field_document_help_text_line_2', array('[[AMOUNT]]' => $fileupload_limits['amount'])); ?></div>
                    <div><?php echo translate('general_dashboard_modal_field_document_help_text_line_3', array('[[FORMATS]]' => str_replace('|', ',', $fileupload_limits['formats']))); ?></div>
                </div>

                <div class="container-fluid-modal fileupload file" id="js-add-document-uploader-file-container"></div>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label mb-0">
                            <input id="js-add-document-wall-post" name="post_wall" type="checkbox">
                            <span class="list-form-checked-info__check-text"><?php echo translate('seller_library_dashboard_modal_field_wall_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- <script type="text/template" id="js-video-categories-select">

    <select name="library_category" class="form-control validate[required]" id="js-add-document-category">
        <option value=""><?php echo translate('seller_library_dashboard_modal_field_category_placeholder'); ?></option>
        <?php foreach($library_categories as $category){ ?>
            <option value="<?php echo $category['id_category']; ?>" <?php echo selected($document['id_category'], $category['id_category']); ?>>
                <?php echo $category['category_title']; ?>
            </option>
        <?php } ?>
    </select>

    <div class="input-group-btn">
        <a
            class="btn btn-dark call-function"
            data-callback="addNewCategory"
            data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
            title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
            href="#"
        >
            <i class="ep-icon ep-icon_plus-circle "></i>
        </a>
    </div>

</script>

<script type="text/template" id="js-video-category-input">
    <input
        class="form-control validate[required,maxSize[50]]"
        type="text"
        name="new_category"
        placeholder="<?php echo translate("seller_pictures_write_new_category_text", null, true); ?>">

    <div class="input-group-btn">
        <a class="btn btn-dark call-function" data-callback="showSelectCategories" href="#">
            <i class="ep-icon ep-icon_remove-circle "></i>
        </a>
    </div>
</script> -->

<script type="application/javascript">

    // var addNewCategory = function() {
    //     $("#js-add-video-category").html($('#js-video-category-input').html());
    // }

    // var showSelectCategories = function(){
    //     $("#js-add-video-category").html($('#js-video-categories-select').html());
    // };

    $(function(){

        // showSelectCategories();

        var beforeUpload = function (event, data, index, xhr, handler, callBack) {
            if(data.files.length > uploadFileLimit){
                if(uploadFileLimit > 0) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', uploadFileLimit), 'warning');
                } else {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                }
                uploadButton.fadeOut();
                event.abort();

                return;
            }

            var category = categoriesList.val() || null;
            // if(null === category) {
            //     systemMessages(translate_js({ plug: 'fileUploader', text: 'error_category_required'}), 'warning');
            //     uploadButton.fadeOut();
            //     event.abort();

            //     return;
            // }

            for (var index = 0; index < data.files.length; index++) {
                var file = data.files[index];
                if('' === file.type) {
                    continue;
                }

                if(false === fileTypes.test(file.type)) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_format_not_allowed'}), 'error');
                    uploadButton.fadeOut();
                    event.abort();

                    return;
                }
            }

            uploadButton.fadeIn();
        };
        var onUploadFinished = function(e, data){
            if (data.files.error){
                systemMessages(data.files[0].error, 'error');
            }
        };
        var onUploadDone = function (e, data) {
            if(data.result.mess_type == 'success'){
                $.each(data.result.files || [], addFile);
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			$('.fileinput-loader-btn').fadeOut();
        };
        var addFile = function(index, file) {
            uploadFileLimit--;

            var documentId = index + '-' + new Date().getTime();
            var url = __img_url + '/' + file.path;
            var fileInput = $('<input>').attr({
                name: 'document',
                type: 'hidden',
                value: file.path
            });
            var fileContent = $(templateFileUploadNew({
                type: 'files',
                image: '',
                index: documentId,
                className: 'fileupload-document',
                iconClassName: file.type,
            }));
            var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': fileRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': 'fileuploadRemove',
                'data-additional-callback': 'updateFileStats',
            });

            fileContent.find('.fileupload-item__actions').append([fileInput, closeButton]);
            fileContainer.append(fileContent);
        };
        var onStatsUpdate = function() {
            uploadFileLimit++;
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
                    callFunction('callbackEditLibraryDocument', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var changeWallPostAcessibility = function(event) {
            var self = $(this);
            var value = self.val() || null;
            if(null !== value) {
                if('public' === value) {
                    wallPostFlag.icheck('enable');
                } else {
                    wallPostFlag.icheck('disable');
                }
            }
        };

        var uploadFileLimit = 1;
        var fileUploadMaxSize = "<?php echo $fileupload_limits['filesize']; ?>";
        var fileTypes = new RegExp('(<?php echo $fileupload_limits['mimetypes']; ?>)', 'i');
        var fileFormats = new RegExp('(.|\/)(<?php echo $fileupload_limits['formats']; ?>)', 'i');

        var fileUploadTimestamp = "<?php echo $upload_folder; ?>";
        var fileUploadUrl = __site_url + 'seller_library/ajax_seller_upload_file/' + fileUploadTimestamp;
        var fileRemoveUrl = __site_url + 'seller_library/ajax_seller_delete_files/' + fileUploadTimestamp;
        var fileContainer = $('#js-add-document-uploader-file-container');
        var uploader = $('#js-add-document-uploader');
        var uploadButton = $('.fileinput-loader-btn');
        var categoriesList = $('#js-add-document-category');
        var wallPostFlag = $('#js-add-document-wall-post')
        var accessTypeRadio = $('.js-add-document-access-type');
        var documentDescription = $('#js-add-document-document-description');
        var accessTypeRadioOptions = {
            radioClass: 'iradiobox iradiobox--20 iradiobox--blue',
            increaseArea: '20%',
                callbacks: {
                ifCreated: true
            }
        };
        var wallFlagOptions = {
            checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue',
            increaseArea: '20%'
        };
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };
        var uploaderOptions = {
            url: fileUploadUrl,
            dataType: 'json',
            maxNumberOfFiles: 1,
            maxFileSize: fileUploadMaxSize,
            processalways: onUploadFinished,
            acceptFileTypes: fileFormats,
            beforeSend: beforeUpload,
            done: onUploadDone,
        };

        if(accessTypeRadio.length) {
            accessTypeRadio.on('ifCreated', function(event) {
                var $this = $(this);
                $( '<span class="icheck-validate"></span>' ).insertAfter($this);
            });
            accessTypeRadio
                .icheck(accessTypeRadioOptions)
                .on('ifChecked', changeWallPostAcessibility)
        }
        if(wallPostFlag.length) {
            wallPostFlag
                .icheck(wallFlagOptions)
                .icheck('disable');
        }
        if(documentDescription.length) {
            documentDescription.textcounter(counterOptions);
        }
        if(uploader.length) {
            uploader
                .fileupload(uploaderOptions)
                    .prop('disabled', !$.support.fileInput)
                    .parent()
                        .addClass($.support.fileInput ? undefined : 'disabled');
        }

        window.modalFormCallBack = onSaveContent;
        window.updateFileStats = onStatsUpdate;
    });
</script>

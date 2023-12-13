<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="picturesFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">

            <!-- Categories -->

            <?php views("new/user/seller/categories_field_view", array("categories" => $pictures_categories, "main" => $picture)); ?>

            <!-- Title -->

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_pictures_dashboard_modal_field_image_title_label_text'); ?>
                </label>
                <input type="text"
                    <?php echo addQaUniqueIdentifier('popup__seller-pictures-my__form_title-input'); ?>
                       name="title"
                       class="validate[required,maxSize[200]]"
                       placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_title_placeholder_public', null, true); ?>"
                       value="<?php if(isset($picture)) echo $picture['title_photo'];?>"/>
            </div>

            <!-- Description -->

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_pictures_dashboard_modal_field_image_description_label_text'); ?>
                </label>
                <textarea name="description"
                    <?php echo addQaUniqueIdentifier('popup__seller-pictures-my__form_description-texarea'); ?>
                    id="js-edit-picture-image-description"
                    class="validate[required,maxSize[2000]] textcounter"
                    data-max="2000"
                    placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_description_placeholder_public', null, true); ?>"><?php if(isset($picture)) echo $picture['description_photo'];?></textarea>
            </div>

            <!-- Picture -->

            <?php if (empty($picture)) { ?>
                <div class="form-group">
                    <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_label_text'); ?></label>
                    <div class="juploader-b">
                        <span class="btn btn-dark mnw-125 fileinput-button">
                            <span><?php echo translate('seller_pictures_dashboard_modal_field_image_upload_button_text'); ?></span>
                            <input id="js-add-picture-uploader" <?php echo addQaUniqueIdentifier('popup__seller-pictures-my__form_select-files-btn'); ?> type="file" name="files" accept="<?php echo arrayGet($fileupload, 'limits.accept'); ?>">
                        </span>

                        <span class="fileinput-loader-btn" style="display:none;">
                            <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> <?php echo translate('seller_pictures_dashboard_modal_field_image_upload_placeholder'); ?>
                        </span>
                        <div class="info-alert-b mt-10">
                            <i class="ep-icon ep-icon_info-stroke"></i>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload, 'rules.size_placeholder'))); ?></div>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload, 'rules.min_height'))); ?></div>
                            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => arrayGet($fileupload, 'limits.amount.total'))); ?></div>
                            <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', arrayGet($fileupload, 'limits.formats')))); ?></div>
                        </div>

                        <div class="fileupload mt-20" id="js-add-picture-image-wrapper"></div>
                    </div>
                </div>
            <?php } ?>

            <!-- Post on wall -->

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="custom-checkbox">
                            <input name="post_wall" type="checkbox">
                            <span class="custom-checkbox__text"><?php echo translate('seller_pictures_dashboard_modal_field_visibility_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>

        <div class="modal-flex__btns">
            <?php if (!empty($picture)) { ?>
                <input type="hidden" name="photo" value="<?php echo !empty($picture) ? $picture['id_photo'] : null; ?>"/>
            <?php } ?>

            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__seller-pictures-my__form_save-btn'); ?> type="submit"><?php echo translate('general_modal_button_save_text'); ?></button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
     (function() {
        "use strict";

        <?php if (empty($picture)) { ?>
            window.addPicturesModal = ({
                init: function (params) {
                    addPicturesModal.self = this;

                    addPicturesModal.imageDescription = $('#js-add-picture-image-description');

                    addPicturesModal.$addPictureSelectCategory = $('#js-category');
                    addPicturesModal.$addPictureNewCategory = $('#js-new-category');

                    addPicturesModal.filesAmount = parseInt('<?php echo $fileupload['limits']['amount']['total']; ?>', 10);
                    addPicturesModal.filesAllowed = parseInt('<?php echo $fileupload['limits']['amount']['total'] - arrayGet($fileupload, 'limits.amount.current', 0); ?>', 10);
                    addPicturesModal.fileTypes = new RegExp('(<?php echo $fileupload['limits']['mimetypes']; ?>)', 'i');
                    addPicturesModal.fileFormats = new RegExp('(\.|\/)(<?php echo $fileupload['limits']['formats']; ?>)', 'i');
                    addPicturesModal.fileUploadMaxSize = "<?php echo $fileupload['rules']['size']; ?>";
                    addPicturesModal.fileUploadUrl = "<?php echo $fileupload['url']['upload']; ?>";
                    addPicturesModal.fileRemoveUrl = "<?php echo $fileupload['url']['delete']; ?>";

                    addPicturesModal.uploader = $('#js-add-picture-uploader');
                    addPicturesModal.uploadButton = $('.fileinput-loader-btn');
                    addPicturesModal.imageWrapper = $('#js-add-picture-image-wrapper');
                    addPicturesModal.uploaderOptions = {
                        url: addPicturesModal.fileUploadUrl,
                        dataType: 'json',
                        maxNumberOfFiles: addPicturesModal.filesAmount,
                        maxFileSize: addPicturesModal.fileUploadMaxSize,
                        acceptFileTypes: addPicturesModal.fileFormats,
                        loadImageFileTypes: addPicturesModal.fileTypes,
                        processalways: addPicturesModal.self.onUploadFinished,
                        beforeSend: addPicturesModal.self.onUploadStart,
                        done: addPicturesModal.self.onUploadDone,
                    };

                    addPicturesModal.self.initPlug();
                    addPicturesModal.self.initListiners();
                },
                initPlug: function(){
                    if(addPicturesModal.uploader.length) {
                        addPicturesModal.uploader.fileupload(addPicturesModal.uploaderOptions);
                        addPicturesModal.uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
                    }

                    if(addPicturesModal.imageDescription.length) {
                        addPicturesModal.imageDescription.textcounter({
                            countDown: true,
                            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
                            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
                        });
                    }
                },
                initListiners: function(){
                    mix(
                        window,
                        {
                            fileploadRemoveUserPhoto: addPicturesModal.self.onFileRemove,
                            picturesFormCallBack: addPicturesModal.self.onSaveContent,
                            showPictureNewCategory: addPicturesModal.self.onShowPictureNewCategory,
                            showPictureSelectCategory: addPicturesModal.self.onShowPictureSelectCategory,
                        },
                        false
                    );
                },
                onShowPictureNewCategory: function($this){
                    addPicturesModal.$addPictureSelectCategory.hide();
                    addPicturesModal.$addPictureNewCategory.css({'display': 'flex'});
                },
                onShowPictureSelectCategory: function($this){
                    addPicturesModal.$addPictureSelectCategory.show();
                    addPicturesModal.$addPictureNewCategory.hide();
                    addPicturesModal.$addPictureNewCategory.find('input[name="new_category"]').val("");
                },
                onSaveContent: function(formElement){
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
                            callFunction('callbackAddSellerPictures', data);
                        }
                    };

                    beforeSend();
                    sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
                },
                onUploadStart: function(event, files, index, xhr, handler, callBack){

                    if (files.files && files.files.length > addPicturesModal.filesAllowed) {
                        if (addPicturesModal.filesAllowed > 0) {
                            systemMessages(translate_js({
                                plug: 'fileUploader',
                                text: 'error_exceeded_limit_text'
                            }).replace('[AMOUNT]', addPicturesModal.filesAmount), 'warning');
                        } else {
                            systemMessages(translate_js({
                                plug: 'fileUploader',
                                text: 'error_no_more_files'
                            }), 'warning');
                        }
                        addPicturesModal.uploadButton.fadeOut();
                        event.abort();

                        return;
                    }

                    addPicturesModal.uploadButton.fadeIn();
                },
                onUploadFinished: function(e,data){
                    if (data.files.error){
                        systemMessages(data.files[0].error, 'error');
                    }
                },
                onUploadDone: function(e, data) {
                    if (data.result.mess_type == 'success') {
                        addPicturesModal.self.addImage(data.result.files)
                    } else {
                        systemMessages(data.result.message, data.result.mess_type);
                    }

                    addPicturesModal.uploadButton.fadeOut();
                },
                onFileRemove: function(button) {
                    try {
                        fileuploadRemove(button).then(function(response) {
                            if ('success' === response.mess_type) {
                                addPicturesModal.filesAllowed++;
                            }
                        });
                    } catch (error) {
                        if (__debug_mode) {
                            console.error(error);
                        }
                    }
                },
                addImage: function(file, index){
                    addPicturesModal.filesAllowed--;
                    console.log(file);

                    var pictureId = file.id_picture;
                    var url = file.fullPath;

                    var image = $('<img>').attr({
                        src: file.fullPath
                    });

                    var imageInput = $('<input>').attr({
                        name: 'photo',
                        type: 'hidden',
                        value: file.path
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
                        'data-action': addPicturesModal.fileRemoveUrl,
                        'data-message': translate_js({
                            plug: 'general_i18n',
                            text: 'form_button_delete_file_message'
                        }),
                        'data-callback': 'fileploadRemoveUserPhoto'
                    });

                    var imageContent = $(templateFileUploadNew({
                        type: 'imgnolink',
                        index: pictureId,
                        image: image.prop('outerHTML'),
                        image_link: url,
                        className: 'fileupload-image'
                    }));

                    imageContent.find('.fileupload-item__actions').append([imageInput, closeButton]);
                    addPicturesModal.imageWrapper.append(imageContent);
                }
            });
        <?php } else { ?>
            window.editPicturesModal = ({
                init: function (params) {
                    editPicturesModal.self = this;

                    editPicturesModal.$addPictureSelectCategory = $('#js-add-picture-select-category');
                    editPicturesModal.$addPictureNewCategory = $('#js-add-picture-new-category');
                    editPicturesModal.imageDescription = $('#js-edit-picture-image-description');

                    editPicturesModal.self.initPlug();
                    editPicturesModal.self.initListiners();
                },
                initPlug: function(){
                    if(editPicturesModal.imageDescription.length) {
                        editPicturesModal.imageDescription.textcounter({
                            countDown: true,
                            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
                            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
                        });
                    }
                },
                initListiners: function(){
                    mix(
                        window,
                        {
                            picturesFormCallBack: editPicturesModal.self.onSaveContent,
                            showPictureNewCategory: editPicturesModal.self.onShowPictureNewCategory,
                            showPictureSelectCategory: editPicturesModal.self.onShowPictureSelectCategory,
                        },
                        false
                    );
                },
                onShowPictureNewCategory: function($this){
                    editPicturesModal.$addPictureSelectCategory.hide();
                    editPicturesModal.$addPictureNewCategory.css({'display': 'flex'});
                },
                onShowPictureSelectCategory: function($this){
                    editPicturesModal.$addPictureSelectCategory.show();
                    editPicturesModal.$addPictureNewCategory.hide();
                    editPicturesModal.$addPictureNewCategory.find('input[name="new_category"]').val("");
                },
                onSaveContent: function(formElement){
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
                            callFunction('callbackEditSellerPictures', data);
                        }
                    };

                    beforeSend();
                    sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
                }
            });
        <?php } ?>
    }());

    $(function() {
        <?php if (empty($picture)) { ?>
            addPicturesModal.init();
        <?php } else { ?>
            editPicturesModal.init();
        <?php } ?>
    });
</script>

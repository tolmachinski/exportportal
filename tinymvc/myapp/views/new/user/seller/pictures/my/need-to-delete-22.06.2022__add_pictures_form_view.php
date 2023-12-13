<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
        <div class="modal-flex__content">

            <div class="form-group">
                <?php views("new/user/seller/categories_field_view", array("categories" => $pictures_categories)); ?>

                <!-- <div id="js-add-picture-select-category" class="input-group">
                    <select class="form-control validate[required]" name="category">
                        <option value="" selected><?php echo translate('seller_pictures_dashboard_modal_field_category_placeholder'); ?></option>
                        <?php if (!empty($pictures_categories)) { ?>
                            <?php foreach ($pictures_categories as $pictures_category) { ?>
                                <option value="<?php echo $pictures_category['id_category']; ?>">
                                    <?php echo cleanOutput($pictures_category['category_title']); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>

                    <div class="input-group-btn">
                        <a
                            class="btn btn-dark call-function"
                            data-callback="showPictureNewCategory"
                            data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            href="#"
                        >
                            <i class="ep-icon ep-icon_plus-circle "></i>
                        </a>
                    </div>
                </div> -->
            </div>

            <!-- <div id="js-add-picture-new-category" class="form-group display-n">
                <input class="form-control validate[required,maxSize[50]]" type="text" name="new_category" placeholder="<?php echo translate("seller_pictures_write_new_category_text", null, true); ?>">
                <div class="input-group-btn">
                    <a class="btn btn-dark call-function" data-callback="showPictureSelectCategory" href="#">
                        <i class="ep-icon ep-icon_remove-circle "></i>
                    </a>
                </div>
            </div> -->

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_title_label_text'); ?></label>
                <input type="text"
                    maxlength="200"
                    name="image_title"
                    class="validate[required,maxSize[200]]"
                    placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_title_placeholder', null, true); ?>">
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_description_label_text'); ?></label>
                <textarea name="image_description"
                    id="js-add-picture-image-description"
                    class="validate[required,maxSize[2000]] textcounter"
                    data-max="2000"
                    placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_description_placeholder', null, true); ?>"></textarea>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_label_text'); ?></label>
                <div class="juploader-b">
                    <span class="btn btn-dark mnw-125 fileinput-button">
                        <span><?php echo translate('seller_pictures_dashboard_modal_field_image_upload_button_text'); ?></span>
                        <input id="js-add-picture-uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload, 'limits.accept'); ?>">
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

            <div class="">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label mb-0">
                            <input id="js-add-picture-image-visibility" name="post_wall" type="checkbox">
                            <span class="list-form-checked-info__check-text"><?php echo translate('seller_pictures_dashboard_modal_field_visibility_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>

        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('general_modal_button_save_text'); ?></button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
     (function() {
        "use strict";

        window.addPicturesModal = ({
            init: function (params) {
                addPicturesModal.self = this;

                addPicturesModal.visibilityFlag = $('#js-add-picture-image-visibility');
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
                if(addPicturesModal.visibilityFlag.length) {
                    addPicturesModal.visibilityFlag.icheck({
                        checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue',
                        increaseArea: '20%'
                    });
                }

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
                        modalFormCallBack: addPicturesModal.self.onSaveContent,
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
					if (data.result.files && Array.isArray(data.result.files)) {
						data.result.files.forEach(addPicturesModal.self.addImage);
					} else {
						addPicturesModal.self.addImage(data.result.files, 0);
					}
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

                var pictureId = file.id_picture;
                var url = file.path;

                var image = $('<img>').attr({
					src: file.thumb
				});

                var imageInput = $('<input>').attr({
                    name: 'image_url',
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

    }());

    $(function() {
        addPicturesModal.init();
    });
</script>

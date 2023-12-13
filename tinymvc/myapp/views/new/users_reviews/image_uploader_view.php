<div class="form-group">
    <label class="input-label">
        <?php echo translate('add_review_form_add_product_image_title'); ?>
    </label>

    <div class="juploader-b">
        <div class="info-alert-b info-alert-b--lh22">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div><?php echo translate('product_reviews_field_image_help_text_line_1', ['{{COUNT_IMAGES}}' => $fileUploadConfigs['limit']]); ?></div>
            <div><?php echo translate('product_reviews_field_image_help_text_line_2', ['{{MIN_WIDTH}}' => $fileUploadConfigs['rules']['min_width'], '{{MIN_HEIGHT}}' => $fileUploadConfigs['rules']['min_height']]); ?></div>
            <div><?php echo translate('product_reviews_field_image_help_text_line_3', ['{{MAX_SIZE}}' => $fileUploadConfigs['rules']['size_placeholder']]); ?></div>
            <div><?php echo translate('product_reviews_field_image_help_text_line_4', ['{{ALLOWED_FORMATS}}' => $fileUploadConfigs['rules']['format']]); ?></div>
        </div>

        <!-- The container for the uploaded files -->
        <div class="container-fluid-modal">
            <div class="fileupload2 js-file-upload2" id="js-add-image--formfield--image-wrapper">
                <?php foreach ($review['images'] ?: [] as $index => $image) { ?>
                    <div class="fileupload2__item image-card3 js-fileupload-item">
                        <span class="link js-fileupload-image">
                            <img class="image" src="<?php echo getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $image['name']], 'product_reviews.main'); ?>" alt="<?php echo "Review image {$index}"; ?>" />
                        </span>

                        <div class="js-fileupload-actions fileupload2__actions">
                            <a <?php echo addQaUniqueIdentifier('global-image-uploader__remove-image'); ?> class="btn btn-light pl-10 pr-10 w-40 call-function" data-callback="fileploadRemoveItemImage" data-file="<?php echo $image['id']; ?>" data-name="<?php echo $image['name']; ?>" data-message="Are you sure you want to delete this image?" title="Delete">
                                <i class="ep-icon ep-icon_trash-stroke fs-17"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <span class="js-fileinput-button btn btn-dark mnw-130 fileinput-button">
            <span>
                <?php echo translate('add_review_form_select_filest_btn'); ?>
            </span>
            <!-- The file input field used as target for the file upload widget -->
            <input <?php echo addQaUniqueIdentifier('global-image-uploader__add-image'); ?> id="js-add-image--formfield--uploader" type="file" name="files" accept="<?php echo $fileUploadConfigs['accept']; ?>">
            <input type="hidden" name="folder" value="<?php echo $fileUploadConfigs['encryptedFolderName']; ?>" />
        </span>

        <span class="fileinput-loader-btn" id="js-add-image--formaction--upload-button" style="display:none;">
            <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.gif" alt="loader"> Uploading...
        </span>
        <span class="total-b display-n">
            <?php echo translate('add_review_form_uploaded_text'); ?>
            <span id="js-add-image-total-uploaded">
                <?php echo $fileUploadConfigs['countUploadedImages']; ?>
            </span>
            <?php echo translate('add_review_form_from_text'); ?>
            <?php echo $fileUploadConfigs['limit']; ?>
        </span>
    </div>
</div>

<?php views('new/file_upload_scripts'); ?>

<script>
    (function() {
        "use strict";

        window.reviewImages = ({
            init: function(params) {
                reviewImages.self = this;
                reviewImages.filesAmount = parseInt('<?php echo $fileUploadConfigs['limit']; ?>', 10);
                reviewImages.filesAllowed = parseInt('<?php echo $fileUploadConfigs['limit'] - $fileUploadConfigs['countUploadedImages']; ?>', 10);
                reviewImages.fileTypes = new RegExp('(<?php echo $fileUploadConfigs['mimetypes']; ?>)', 'i');
                reviewImages.fileFormats = new RegExp('(\.|\/)(<?php echo $fileUploadConfigs['formats']; ?>)', 'i');
                reviewImages.fileUploadMaxSize = "<?php echo $fileUploadConfigs['rules']['size']; ?>";
                reviewImages.fileUploadUrl = "<?php echo $fileUploadConfigs['uploadFileUrl']; ?>";
                reviewImages.fileRemoveUrl = "<?php echo $fileUploadConfigs['removeFileUrl']; ?>";
                reviewImages.counter = $('#js-add-image-total-uploaded');
                reviewImages.uploader = $('#js-add-image--formfield--uploader');
                reviewImages.uploadButton = reviewImages.uploader.closest('.js-fileinput-button');
                reviewImages.uploadLoader = $('#js-add-image--formaction--upload-button');
                reviewImages.imageWrapper = $('#js-add-image--formfield--image-wrapper');
                reviewImages.uploaderOptions = {
                    url: reviewImages.fileUploadUrl,
                    dataType: 'json',
                    maxNumberOfFiles: reviewImages.filesAmount,
                    maxFileSize: reviewImages.fileUploadMaxSize,
                    acceptFileTypes: reviewImages.fileFormats,
                    loadImageFileTypes: reviewImages.fileTypes,
                    processalways: reviewImages.self.onUploadFinished,
                    beforeSend: reviewImages.self.onUploadStart,
                    done: reviewImages.self.onUploadDone,
                    fail: reviewImages.self.onUploadFail,
                };

                reviewImages.self.initPlug();
                reviewImages.self.initListiners();
            },
            initPlug: function() {

                reviewImages.uploader
                    .fileupload(reviewImages.uploaderOptions);

                reviewImages.uploader
                    .prop('disabled', !$.support.fileInput)
                    .parent()
                    .addClass($.support.fileInput ? undefined : 'disabled');

                reviewImages.uploadButton
                    .setValHookType('itemImages');

                $.valHooks.itemImages = {
                    get: function(el) {
                        return reviewImages.imageWrapper.find('.js-fileupload-item').length ? 1 : '';
                    }
                };
            },
            initListiners: function() {
                mix(
                    window, {
                        fileploadRemoveItemImage: reviewImages.self.onFileploadRemoveItemImage
                    },
                    false
                );
            },
            onUploadStart: function(event, files, index, xhr, handler, callBack) {
                if (files.files && files.files.length > reviewImages.filesAllowed) {
                    if (reviewImages.filesAllowed > 0) {
                        systemMessages(translate_js({
                            plug: 'fileUploader',
                            text: 'error_exceeded_limit_text'
                        }).replace('[AMOUNT]', reviewImages.filesAmount), 'warning');
                    } else {
                        systemMessages(translate_js({
                            plug: 'fileUploader',
                            text: 'error_no_more_files'
                        }), 'warning');
                    }
                    reviewImages.uploadLoader.fadeOut();
                    event.abort();

                    return;
                }

                reviewImages.uploadLoader.fadeIn();
            },
            onUploadFinished: function(e, data) {
                if (data.files.error) {
                    systemMessages(data.files[0].error, 'error');
                }
            },
            onUploadFail: function(e, data) {
                reviewImages.uploadLoader.fadeOut();
                if (reviewImages.filesAllowed !== 0) {
                    systemMessages(translate_js({
                        plug: "general_i18n",
                        text: "system_message_server_error_text"
                    }), "error");
                }
            },
            onUploadDone: function(e, data) {
                if (data.result.mess_type == 'success') {
                    if (data.result.files && Array.isArray(data.result.files)) {
                        data.result.files.forEach(reviewImages.self.addImage);
                    } else {
                        reviewImages.self.addImage(data.result.files, 0);
                    }

                    reviewImages.self.updateCounter();
                    reviewImages.self.confirmCloseDialog();
                } else {
                    systemMessages(data.result.message, data.result.mess_type);
                }

                reviewImages.uploadLoader.fadeOut();
            },
            confirmRemoveItemImage: function(message, buttons) {
                BootstrapDialog.show({
                    message: message,
                    closable: false,
                    draggable: true,
                    animate: false,
                    buttons: buttons
                });
            },
            onFileploadRemoveItemImage: function($this) {
                reviewImages.self.confirmRemoveItemImage(
                    'Are you sure you want to delete this image?',
                    [{
                            label: translate_js({
                                plug: 'BootstrapDialog',
                                text: 'ok'
                            }),
                            cssClass: 'btn-success mnw-80',
                            action: function(dialogRef) {
                                reviewImages.self.onFileRemove($this);
                                dialogRef.close();
                            }
                        },
                        {
                            label: translate_js({
                                plug: 'BootstrapDialog',
                                text: 'cancel'
                            }),
                            cssClass: 'mnw-80',
                            action: function(dialogRef) {
                                dialogRef.close();
                            }
                        }
                    ]
                );
            },
            onFileRemove: function(button) {
                try {
                    fileuploadRemoveNew2(button).then(function(response) {
                        reviewImages.filesAllowed++;
                        reviewImages.self.updateCounter();
                    });
                    reviewImages.self.confirmCloseDialog();
                } catch (error) {
                    if (__debug_mode) {
                        console.error(error);
                    }
                }
            },
            addImage: function(file, index) {
                reviewImages.filesAllowed--;

                var image = $('<img class="image">').attr({
                    src: file.path
                });

                var closeButton = $('<a>')
                    .html('<i class="ep-icon ep-icon_trash-stroke fs-17"></i>')
                    .attr({
                        title: translate_js({
                            plug: 'general_i18n',
                            text: 'form_button_delete_file_title'
                        }),
                        class: 'btn btn-light pl-10 pr-10 w-40 call-function',
                        'data-file': file.id_picture || file.name,
                        'data-action': reviewImages.fileRemoveUrl,
                        'data-name': file.name,
                        'data-message': translate_js({
                            plug: 'general_i18n',
                            text: 'form_button_delete_file_message'
                        }),
                        'data-callback': 'fileploadRemoveItemImage',
                    });

                var imageContent = $(templateFileUploadNew2({
                    type: 'imgnolink',
                    index: file.id_picture || file.name,
                    image: image.prop('outerHTML'),
                    image_link: file.path,
                    className: 'fileupload-image',
                }));

                imageContent
                    .find('.js-fileupload-image')
                    .append('<input type="hidden" name="images[]" data-name="' + file.name + '" value="' + file.name + '">');

                imageContent
                    .find('.js-fileupload-actions')
                    .append([closeButton]);

                reviewImages.imageWrapper.append(imageContent);
                reviewImages.uploadButton.removeClass('validengine-border').prev('.formError').remove();
            },
            updateCounter: function() {
                reviewImages.counter.text(reviewImages.filesAmount - reviewImages.filesAllowed);
            },
            confirmCloseDialog: function() {
                $(".fancybox-title").find('a[data-callback="closeFancyBox"]').removeClass("call-function").addClass("confirm-dialog");
            }
        });
    }());

    $(function() {
        reviewImages.init();
    });
</script>

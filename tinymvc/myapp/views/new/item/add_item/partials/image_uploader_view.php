<div class="form-group">
    <label class="input-label <?php echo form_validation_label($validation, 'images', 'required'); ?>">Other images</label>

    <div class="juploader-b">
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_5', array('[[AMOUNT]]' => arrayGet($fileupload_photos, 'limits.amount.total'))); ?></div>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_6'); ?></div>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload_photos, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_photos, 'rules.min_height'))); ?></div>
            <div>
                <?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload_photos, 'rules.size_placeholder'))); ?>
                <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', arrayGet($fileupload_photos, 'limits.formats')))); ?>
            </div>
        </div>

        <!-- The container for the uploaded files -->
        <div class="container-fluid-modal">
            <div class="fileupload2 js-file-upload2" id="js-add-edit-item--formfield--image-wrapper">
                <?php if(!empty($photos)){?>
                    <?php foreach($photos as $photo){ ?>
                        <?php //$link_img = getDisplayImageLink(array('{ID}' => $item["id"], '{FILE_NAME}' => $photo['photo_name']), 'images.photos');?>

                        <div class="fileupload2__item image-card3 js-fileupload-item">
                            <span class="link js-fileupload-image">
                                <img
                                    class="image"
                                    src="<?php echo $photo['photo_url']; ?>"
                                    alt="<?php echo cleanOutput($item['title']); ?>" />
                            </span>

                            <div class="js-fileupload-actions fileupload2__actions">
                                <a
                                    <?php echo addQaUniqueIdentifier("global-image-uploader__remove-image")?>
                                    class="btn btn-light pl-10 pr-10 w-40 call-function"
                                    data-callback="fileploadRemoveItemImage"
                                    data-file="<?php echo $photo['id']; ?>"
                                    data-name="<?php echo $photo['photo_name']; ?>"
                                    data-message="Are you sure you want to delete this image?"
                                    title="Delete">
                                    <i class="ep-icon ep-icon_trash-stroke fs-17"></i>
                                </a>

                                <input type="hidden" name="images_validate[]" value="<?php echo $photo['photo_name']; ?>">
                                <?php if (isset($photo['is_temp']) && $photo['is_temp']) { ?>
                                    <input name="images[<?php echo $photo['id']; ?>]" type="hidden" value="<?php echo $photo['photo_path']; ?>">
                                <?php } ?>
                            </div>
                        </div>
                    <?php }?>
                <?php }?>
            </div>
        </div>

        <span class="js-fileinput-button btn btn-dark mnw-130 fileinput-button">
            <span>Select files</span>
            <!-- The file input field used as target for the file upload widget -->
            <input <?php echo addQaUniqueIdentifier("global-image-uploader__add-image")?> id="js-add-item--formfield--uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload_photos, 'limits.accept'); ?>">
        </span>

        <span class="fileinput-loader-btn" id="js-add-edit-item--formaction--upload-button" style="display:none;">
            <img class="image" src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...
        </span>
        <span class="total-b display-n">Uploaded <span id="js-add-item-total-uploaded"><?php echo arrayGet($fileupload_photos, 'limits.amount.current'); ?></span> from <?php echo arrayGet($fileupload_photos, 'limits.amount.total'); ?></span>
    </div>
</div>

<script>
    (function() {
		"use strict";

		window.productImages = ({
            init: function (params) {
                productImages.self = this;
                productImages.filesAmount = parseInt('<?php echo $fileupload_photos['limits']['amount']['total']; ?>', 10);
                productImages.filesAllowed = parseInt('<?php echo $fileupload_photos['limits']['amount']['total'] - arrayGet($fileupload_photos, 'limits.amount.current', 0); ?>', 10);
                productImages.fileTypes = new RegExp('(<?php echo $fileupload_photos['limits']['mimetypes']; ?>)', 'i');
                productImages.fileFormats = new RegExp('(\.|\/)(<?php echo $fileupload_photos['limits']['formats']; ?>)', 'i');
                productImages.fileUploadMaxSize = "<?php echo $fileupload_photos['rules']['size']; ?>";
                productImages.fileUploadUrl = "<?php echo $fileupload_photos['url']['upload']; ?>";
                productImages.fileRemoveUrl = "<?php echo $fileupload_photos['url']['delete']; ?>";
                productImages.counter = $('#js-add-item-total-uploaded');
                productImages.uploader = $('#js-add-item--formfield--uploader');
                productImages.uploadButton = productImages.uploader.closest('.js-fileinput-button');
                productImages.uploadLoader = $('#js-add-edit-item--formaction--upload-button');
                productImages.imageWrapper = $('#js-add-edit-item--formfield--image-wrapper');
                productImages.typeFailFile = "fail";
                productImages.uploaderOptions = {
                    url: productImages.fileUploadUrl,
                    dataType: 'json',
                    maxNumberOfFiles: productImages.filesAmount,
                    maxFileSize: productImages.fileUploadMaxSize,
                    acceptFileTypes: productImages.fileFormats,
                    loadImageFileTypes: productImages.fileTypes,
                    processalways: productImages.self.onUploadFinished,
                    beforeSend: productImages.self.onUploadStart,
                    done: productImages.self.onUploadDone,
                    fail: productImages.self.onUploadFail,
                };

                productImages.self.initPlug();
                productImages.self.initListiners();
            },
            initPlug: function(){

                productImages.uploader
                    .fileupload(productImages.uploaderOptions);

                productImages.uploader
                    .prop('disabled', !$.support.fileInput)
                    .parent()
                    .addClass($.support.fileInput ? undefined : 'disabled');

                productImages.uploadButton
                    .addClass('validate[<?php echo form_validation_rules($validation, 'images', 'required'); ?>]')
                    .setValHookType('itemImages');

                $.valHooks.itemImages = {
                    get: function (el) {
                        return productImages.imageWrapper.find('.js-fileupload-item').length ? 1 : '';
                    }
                };
            },
            initListiners: function(){
                mix(
                    window,
                    {
                        fileploadRemoveItemImage: productImages.self.onFileploadRemoveItemImage
                    },
					false
                );
            },
            onUploadStart: function(event, files, index, xhr, handler, callBack){
                if(files.files && files.files.length > productImages.filesAllowed){
                    if(productImages.filesAllowed > 0) {
                        systemMessages(translate_js({
                            plug: 'fileUploader',
                            text: 'error_exceeded_limit_text'
                        }).replace('[AMOUNT]', productImages.filesAmount), 'warning');
                    } else {
                        systemMessages(translate_js({
                            plug: 'fileUploader',
                            text: 'error_no_more_files'
                        }), 'warning');
                    }
                    productImages.uploadLoader.fadeOut();
                    productImages.typeFailFile = "abort";
                    event.abort();

                    return;
                }

                productImages.uploadLoader.fadeIn();
            },
			onUploadFinished: function(e, data) {
				if (data.files.error){
                    systemMessages(data.files[0].error, 'error');
                }
			},
            onUploadFail: function(e, data) {

                if (productImages.typeFailFile === "fail") {
                    productImages.uploadLoader.fadeOut();
                    systemMessages(translate_js({ plug: "general_i18n", text: "system_message_server_error_text" }), "error");
                } else {
                    productImages.typeFailFile = "fail";
                }

            },
			onUploadDone: function(e, data) {
				if(data.result.mess_type == 'success'){
                    if(data.result.files && Array.isArray(data.result.files)) {
                        data.result.files.forEach(productImages.self.addImage);
                    } else {
                        productImages.self.addImage(data.result.files, 0);
                    }

                    productImages.self.updateCounter();
                } else {
                    systemMessages(data.result.message, data.result.mess_type);
                }

                productImages.uploadLoader.fadeOut();
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
                if($("#js-add-item-variants-wr input[value='" + $this.data('name') + "']").length){
                    productImages.self.confirmRemoveItemImage(
                        'Do you want remove image in combination?',
                        [{
                            label: translate_js({plug:'BootstrapDialog', text: 'ok'}),
                            cssClass: 'btn-success mnw-80',
                            action: function(dialog){
                                productImages.self.removeImagesVariations($this);
                                dialog.close();
                            }
                        }, {
                            label: translate_js({plug:'BootstrapDialog', text: 'cancel'}),
                            cssClass: 'mnw-80',
                            action: function(dialog){
                                dialog.close();
                            }
                        }],
                        false,
                        '',
                        false
                    );
                    return true;
                }else{
                    productImages.self.confirmRemoveItemImage(
                        'Are you sure you want to delete this image?',
                        [{
                            label: translate_js({plug:'BootstrapDialog', text: 'ok'}),
                            cssClass: 'btn-success mnw-80',
                            action: function(dialogRef){
                                productImages.self.onFileRemove($this);
                                dialogRef.close();
                            }
                        },
                        {
                            label: translate_js({plug:'BootstrapDialog', text: 'cancel'}),
                            cssClass: 'mnw-80',
                            action: function(dialogRef){
                                dialogRef.close();
                            }
                        }]
                    );
                }
            },
            onFileRemove: function(button) {
                try {
                    fileuploadRemoveNew2(button).then(function(response) {
                        productImages.filesAllowed++;
                        productImages.self.updateCounter();

                        dispatchCustomEvent("select-variant-images:remove-option", globalThis, { detail: { name: button.data('name') }});
                    });
                } catch (error) {
                    if(__debug_mode) {
                        console.error(error);
                    }
                }
            },
            removeImagesVariations: function($this) {
                var image = $this.data('name');

                if ($(".js-item-add-variant").length){
                    var elementsVariantImage = $("#js-add-item-variants-wr input[value='" + image + "']");
                    if (elementsVariantImage.length){
                        elementsVariantImage.val("main");
                    }

                    var hash = (+new Date).toString(36);
                    var main = $('.js-select-variant-images .js-add-item-change-main-photo').attr('src');
                    $('#js-add-item-variants-wr .js-item-add-variant[data-img="' + image + '"]')
                        .data('img', 'main')
                        .find('.image')
                        .addClass('js-add-item-change-main-photo')
                        .attr('src', main + '?=' + hash);
                }

                productImages.self.onFileRemove($this);
            },
            addImage: function(file, index) {
                productImages.filesAllowed--;

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
                        'data-action': productImages.fileRemoveUrl,
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
                    .append('<input type="hidden" name="images[]" data-name="' + file.name + '" value="'+file.path+'">');

                imageContent
                    .find('.js-fileupload-actions')
                    .append([closeButton]);

                productImages.imageWrapper.append(imageContent);
                productImages.uploadButton.removeClass('validengine-border').prev('.formError').remove();

                dispatchCustomEvent("select-variant-images:uppend-option", globalThis, { detail: { path: file.path, name: file.photo_name }});
            },
            updateCounter: function() {
				productImages.counter.text(productImages.filesAmount - productImages.filesAllowed);
            }
        });
    }());

    $(function() {
		productImages.init();
    });
</script>

<div class="form-group">
    <label class="input-label <?php echo form_validation_label($validation, 'images', 'required'); ?>">Images</label>

    <div class="juploader-b">
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_5', ['[[AMOUNT]]' => arrayGet($fileupload_photos, 'limits.amount.total')]); ?></div>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_6'); ?></div>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', ['[[WIDTH]]' => arrayGet($fileupload_photos, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_photos, 'rules.min_height')]); ?></div>
            <div>
                <?php echo translate('general_dashboard_modal_field_image_help_text_line_1', ['[[SIZE]]' => arrayGet($fileupload_photos, 'rules.size_placeholder')]); ?>
                <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', ['[[FORMATS]]' => str_replace('|', ',', arrayGet($fileupload_photos, 'limits.formats'))]); ?>
            </div>
        </div>

        <!-- The container for the uploaded files -->
        <div class="container-fluid-modal">
            <div class="fileupload2 fileupload2--large js-file-upload2" id="js-add-edit-item--formfield--image-wrapper">
                <?php if (!empty($photos)) {?>
                    <?php $issetParent = 0;?>
                    <?php foreach ($photos as $photo) { ?>
                        <?php if ($photo['main_parent']) {$issetParent = 1;}?>
                        <div class="fileupload2__item image-card3 js-fileupload-item <?php echo empty($photo['main_parent']) ? '' : 'fileupload2__item--main'; ?>" data-name="<?php echo $photo['photo_name']; ?>" data-main="<?php echo empty($photo['main_parent']) ? 'false' : 'true'; ?>">
                            <span class="link js-fileupload-image">
                                <img
                                    class="image"
                                    src="<?php echo $photo['photo_url']; ?>"
                                    alt="<?php echo cleanOutput($item['title']); ?>"
                                    data-src="<?php echo $photo['orig_url']; ?>" />
                            </span>
                                <button
                                <?php echo addQaUniqueIdentifier('global__image-uploader__main-image-btn'); ?>
                                    class="fileupload2__item-btn fileupload2__item-btn--main call-function"
                                    data-callback="openModal"
                                    data-name="<?php echo $photo['photo_name']; ?>"
                                    type="button"
                                >
                                    Main
                                </button>
                                <button
                                <?php echo addQaUniqueIdentifier('global__image-uploader__set-as-main-btn'); ?>
                                    class="fileupload2__item-btn js-set-as-main-btn call-function"
                                    data-callback="openModal"
                                    data-name="<?php echo $photo['photo_name']; ?>"
                                    type="button"
                                >
                                    Set as main
                                </button>

                            <div class="js-fileupload-actions fileupload2__actions">
                                <a
                                    <?php echo addQaUniqueIdentifier('global__image-uploader__remove-image-btn'); ?>
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
            <input <?php echo addQaUniqueIdentifier('global__image-uploader__add-image-btn'); ?> id="js-add-item--formfield--uploader" type="file" name="files" accept="<?php echo arrayGet($fileupload_photos, 'limits.accept'); ?>">
        </span>

        <span class="fileinput-loader-btn" id="js-add-edit-item--formaction--upload-button" style="display:none;">
            <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.gif" alt="loader"> Uploading...
        </span>
        <span class="total-b display-n">Uploaded <span id="js-add-item-total-uploaded"><?php echo arrayGet($fileupload_photos, 'limits.amount.current'); ?></span> from <?php echo arrayGet($fileupload_photos, 'limits.amount.total'); ?></span>
    </div>
</div>

<div id="js-popup-croppper-wr" class="display-n">
	<div id="js-popup-croppper" class="popup-cropperjs-container">
		<img id="js-my-img-crop" class="image" src="" style="visibility: hidden; opacity: 0;">
	</div>
</div>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/canvas-toblob/canvas-toblob.js'); ?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/cropperjs/cropper.js'); ?>"></script>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/plug/cropperjs/cropper.css'); ?>" />

<script>
    (function() {
		"use strict";
        // TODO: при переносе на webpack нужно учитывать что этот имитатор класса также используется в "image_uploader_view"
		window.productImages = ({
            init: function () {
                productImages.self = this;
                productImages.filesAmount = parseInt('<?php echo $fileupload_photos['limits']['amount']['total']; ?>', 10);
                productImages.filesAllowed = parseInt('<?php echo $fileupload_photos['limits']['amount']['total'] - arrayGet($fileupload_photos, 'limits.amount.current', 0); ?>', 10);
                productImages.uploader = $('#js-add-item--formfield--uploader');
                productImages.uploadButton = productImages.uploader.closest('.js-fileinput-button');
                productImages.uploadLoader = $('#js-add-edit-item--formaction--upload-button');
                productImages.imageWrapper = $('#js-add-edit-item--formfield--image-wrapper');
                productImages.typeFailFile = "fail";
                productImages.crpWinImgWidth = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_width'); ?>);
				productImages.crpWinImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_height'); ?>);
				productImages.crpImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'crop_img_height', 400); ?>);
				productImages.cropImg = $('#js-my-img-crop');
				productImages.popupCropperShow = '#js-popup-croppper';
				productImages.$popupCropperShow = $('#js-popup-croppper');
				productImages.uploadImgCrop;
                productImages.uploaderOptions = {
                    url: "<?php echo $fileupload_photos['url']['upload']; ?>",
                    dataType: 'json',
                    maxNumberOfFiles: productImages.filesAmount,
                    maxFileSize: "<?php echo $fileupload_photos['rules']['size']; ?>",
                    acceptFileTypes: new RegExp('(\.|\/)(<?php echo $fileupload_photos['limits']['formats']; ?>)', 'i'),
                    loadImageFileTypes: new RegExp('(<?php echo $fileupload_photos['limits']['mimetypes']; ?>)', 'i'),
                    processalways: productImages.self.onUploadFinished,
                    beforeSend: productImages.self.onUploadStart,
                    done: productImages.self.onUploadDone,
                    fail: productImages.self.onUploadFail,
                };

                productImages.self.initPlug();
                productImages.self.initListiners();
            },
            initPlug: function(){
                productImages.self.existRealImage();
                productImages.self.checkMainImage(productImages.imageWrapper);

                $.valHooks.mainImages = {
                    get: function (el) {
                        return productImages.imageWrapper.find('input[name="images_main_validate"]').length ? 1 : '';
                    }
                };

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
                        fileploadRemoveItemImage: productImages.self.onFileploadRemoveItemImage,
                        cropImage: productImages.onCropImage,
                        openModal: productImages.openModal,
                    },
					false
                );

                productImages.cropImg.on('click', '.js-cropper-rotate', function(e) {
					e.preventDefault();
					productImages.$uploadImgCrop.croppie('rotate', -90);
				});
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
            confirmRemoveItemImage: function($this, message, variations) {
                variations = variations || false;
                open_result_modal({
                    title: "Info",
                    type: "info",
                    subTitle: message,
                    buttons: [{
                        label: translate_js({
                            plug: 'BootstrapDialog',
                            text: 'ok'
                        }),
                        cssClass: 'btn-success mnw-100',
                        action: function(dialog) {
                            if(variations) {
                                productImages.self.removeImagesVariations($this);
                            } else {
                                productImages.self.onFileRemove($this);
                            }
                            dialog.close();
                        }
                    }, {
                        label: translate_js({
                            plug: 'BootstrapDialog',
                            text: 'cancel'
                        }),
                        cssClass: 'btn-light mnw-100',
                        action: function(dialog) {
                            dialog.close();
                        }
                    }],
                });
            },
            onFileploadRemoveItemImage: function($this) {
                if ($($this).closest(".js-fileupload-item").data("main")) {
                    systemMessages(translate_js({
                        plug: "general_i18n",
                        text: "system_message_server_error_text"
                    }), "error");
                    return true;
                }
                if ($("#js-add-item-variants-wr input[value='" + $this.data('name') + "']").length) {
                    productImages.self.confirmRemoveItemImage($this, 'Do you want remove image in combination?', true);
                    // return true;
                } else {
                    productImages.self.confirmRemoveItemImage($this, 'Are you sure you want to delete this image?');
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
                productImages.filesAllowed--;;

                var closeButton = $('<a>')
                    .html('<i class="ep-icon ep-icon_trash-stroke fs-17"></i>')
                    .attr({
                        title: translate_js({
                            plug: 'general_i18n',
                            text: 'form_button_delete_file_title'
                        }),
                        class: 'btn btn-light pl-10 pr-10 w-40 call-function',
                        'data-file': file.id_picture || file.name,
                        'data-action': "<?php echo $fileupload_photos['url']['delete']; ?>",
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
                    image: '<img class="image" src="'+file.path+'" data-src="'+file.orig_path+'">',
                    image_link: file.path,
					className: 'fileupload-image',
                    upload: true,
                }));

                imageContent
                    .find('.js-fileupload-image')
                    .append('<input type="hidden" name="images[]" data-name="' + file.name + '" value="'+file.path+'">');

                imageContent
                    .find('.js-fileupload-actions')
                    .append([closeButton]);

                productImages.checkMainImage(imageContent , true)

                productImages.imageWrapper.append(imageContent);
                productImages.uploadButton.removeClass('validengine-border').prev('.formError').remove();

                dispatchCustomEvent("select-variant-images:uppend-option", globalThis, { detail: { path: file.path, name: file.photo_name }});
            },
            updateCounter: function() {
				$('#js-add-item-total-uploaded').text(productImages.filesAmount - productImages.filesAllowed);
            },
			croppperCalcHeight: function(){
				var windHeight = $(window).height();

				if(windHeight < 540){
					productImages.crpImgHeight = productImages.crpWinImgHeight + 30;
				}else if(windHeight < 450){
					productImages.crpImgHeight = productImages.crpWinImgHeight + 10;
				}

				return productImages.crpImgHeight;
			},
            initCropper: function(){
                var image = document.getElementById('js-my-img-crop');
                var cropW = 0;
                var cropH = 0;
                var cropAspectRatio = productImages.crpWinImgWidth / productImages.crpWinImgHeight;

                productImages.uploadImgCrop = new Cropper(image, {
                    viewMode: 0,
                    zoomable: true,
                    aspectRatio: cropAspectRatio,
                    autoCropArea: 1,
                    wheelZoomRatio: 0.06,
                    dragMode: 'move',
                    scalable: false,
                    toggleDragModeOnDblclick: false,
                    cropBoxResizable: false,
                    cropBoxMovable: false,
                    getCroppedCanvas:{fillcolor: "#fff"},
                    ready: function() {
						hideLoader(productImages.popupCropperShow);
                    },
                    crop: function(event) {
                        cropW = event.detail.width;
                        cropH = event.detail.height;
                    },
                    zoom: function(event) {
                        var imageData = this.cropper.getImageData();
                        var canvasData = this.cropper.getCanvasData();
                        var cropBoxData = this.cropper.getCropBoxData();
                        var data = this.cropper.getData();

                        if(event.detail.oldRatio < event.detail.ratio) {

                            if(
                                cropW < productImages.crpWinImgWidth
                                || cropH < productImages.crpWinImgHeight
                            ){
                                this.cropper.zoomTo(event.detail.oldRatio);
                                event.preventDefault();
                            }
                        }else{
                            var maxP = Math.max(imageData.width, imageData.height);
                            var minPCropprt = Math.min(cropBoxData.width, cropBoxData.height);

                            if(
                                ((maxP == imageData.width) && (imageData.width != imageData.height) && (maxP <= cropBoxData.width) && (imageData.height <= cropBoxData.height))
                                || ((maxP == imageData.height) && (imageData.width != imageData.height) && (maxP <= cropBoxData.height) && (imageData.width <= cropBoxData.width))
                                || ((imageData.width == imageData.height) && (maxP <= minPCropprt))
                            ){
                                if(event.detail.oldRatio != event.detail.ratio){
                                    this.cropper.zoomTo(event.detail.oldRatio);
                                }
                                event.preventDefault();
                            }
                        }
                    }
                });
            },
            existRealImage: function(){
                var issetParent = parseInt('<?php echo $issetParent; ?>', 10);
                if(issetParent){
                    productImages.imageWrapper.append('<input type="hidden" name="images_main_validate" value="<?php echo arrayGet($fileupload_crop, 'link_thumb_main_image'); ?>">');
                }
            },

            openModal: function(button) {
                var name = button.data("name")
                var image = $(button).parent().find(".js-fileupload-image").find("img");
                var btn = $('<button class="btn btn-primary mnw-150 pull-right call-function" <?php echo addQaUniqueIdentifier('photo-cropper__crop-popup__crop-image-button'); ?> data-callback="cropImage" type="button"><?php echo arrayGet($fileupload_crop, 'btn_text_save_picture'); ?></button>')
                btn.attr("data-name", name);
                if(image.attr("src").match(/noimage/g)){
                    systemMessages(translate_js({ plug: "general_i18n", text: "system_message_error_upload_main_image_text" }), "error");
                    return true;
                }
				BootstrapDialog.show({
					cssClass: 'info-bootstrap-dialog inputs-40',
					title: '<?php echo arrayGet($fileupload_crop, 'title_text_popup'); ?>',
					type: 'type-light',
					size: 'size-wide',
					closable: true,
					closeByBackdrop: false,
					closeByKeyboard: false,
					draggable: false,
					animate: true,
					nl2br: false,
					onshow: function(dialog) {
						showLoader(productImages.popupCropperShow);
						var modal_dialog = dialog.getModalDialog();
						modal_dialog.addClass('modal-dialog-centered');
						dialog.getModalBody().append(productImages.$popupCropperShow);
						dialog.getModalFooter().append(btn).show();
					},
					onshown: function(dialogRef){
						productImages.cropImg.addClass('ready');
						$('#js-my-img-crop').attr('src', image.data('src'));
						productImages.self.initCropper();
					},
					onhidden: function(dialogRef){
						productImages.uploadImgCrop.destroy();
						$('#js-popup-croppper-wr').append(productImages.$popupCropperShow);
					}
				});

			},
            onCropImage: function(btn) {
                var parent = productImages.imageWrapper.find(".js-fileupload-item[data-name='"+btn.data("name")+"']");
				btn.prop('disabled', true);

				productImages.uploadImgCrop.getCroppedCanvas({width: productImages.crpWinImgWidth, height: productImages.crpWinImgHeight, fillColor:'#fff'}).toBlob(function (blob) {
					var formData = new FormData();
					formData.append('files', blob, 'cropp.jpeg');

					$.ajax({
						url: "<?php echo $fileupload_crop['url']['upload']; ?>",
						type: 'POST',
						dataType: "JSON",
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function(){
							showLoader(productImages.popupCropperShow);
						},
						success: function(resp){
							if (resp.mess_type == 'success') {
                                productImages.imageWrapper.find(".js-fileupload-item").each(function() {
                                    var item = $(this);
                                    var data = item.data("main");
                                    if(data) {
                                        var formerMainSrc = item.find(".image").data("src");
                                        var formerMainName = item.data("name");
                                        dispatchCustomEvent("select-variant-images:uppend-option", globalThis, { detail: { path: formerMainSrc, name: formerMainName }});
                                    }

                                    item.removeClass('fileupload2__item--main');
                                    item.data("main", false);
                                });

                                parent.addClass('fileupload2__item--main');
                                parent.data("main", true);

								if(resp.tmp_url != undefined){
									var hiddenInputs = productImages.imageWrapper.find('input[type="hidden"]');

                                    hiddenInputs.each(function() {
                                        var input = $(this);
                                        var inputName = input.attr("name");
                                        if(inputName !== "images_remove[]" && inputName !== "images[]" && inputName !== "images_validate[]") {
                                            input.remove();
                                        }
                                    });

                                    productImages.imageWrapper.find(".js-set-as-main-btn").removeClass('validate[required] validengine-border').prev('.formError').remove();
									productImages.imageWrapper.append('<input type="hidden" name="images_main" value="'+resp.tmp_url+'">');
									productImages.imageWrapper.append('<input type="hidden" name="images_main_nonce" value="'+resp.nonce+'">');
									productImages.imageWrapper.append('<input type="hidden" name="parent" value="'+parent.data("name")+'">');

                                    dispatchCustomEvent("select-variant-images:remove-option", globalThis, { detail: { name: btn.data('name') }});
								}

								if(typeof callbackReplaceCropImages != "undefined"){
									callbackReplaceCropImages(resp);
								}

                                BootstrapDialog.closeAll();

							} else {
								systemMessages( resp.message, resp.mess_type );
							}
						},
                        complete: function() {
                            hideLoader(productImages.popupCropperShow);
                        }
					});

				}, "image/jpeg", 0.75);
			},
            setValHook: function(wrapper) {
                wrapper.find('.js-set-as-main-btn').addClass('validate[required]').setValHookType('mainImages');
            },
            checkMainImage: function(wrapper, addImage) {
                addImage = addImage || false;
                var mainImageValide = productImages.imageWrapper.find('input[name="images_main_validate"]').length;
                var mainImage = productImages.imageWrapper.find('input[name="images_main"]').length;

                if(addImage) {
                    if(!mainImageValide && !mainImage) {
                        productImages.self.setValHook(wrapper);
                    }
                } else if(!mainImageValide) {
                    productImages.self.setValHook(wrapper);
                }
            }
        });
    }());

    $(function() {
		productImages.init();
    });
</script>

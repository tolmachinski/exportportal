<div class="flex-card show-column-md">
	<div class="flex-card__fixed order-md--1 mb-10">
		<?php $classPreview = arrayGet($fileupload_crop, 'class_preview');?>
 		<div class="cropper-preview image-card3<?php if(!empty($classPreview)){ echo ' '.$classPreview; }?><?php if(arrayGet($fileupload_crop, 'image_circle_preview')){?> bd-radius-50pr<?php }?>">
            <?php $preview_fanacy = arrayGet($fileupload_crop, 'preview_fanacy') ?? true;?>
            <?php if($preview_fanacy){?>
                <span
                    id="js-view-main-photo"
                    class="link"
                    data-title="<?php echo arrayGet($fileupload_crop, 'title_text_popup'); ?>"
                >
            <?php }else{?>
                <span
                    id="js-view-main-photo"
                    class="link"
                >
            <?php }?>
                <img class="image js-fs-image" data-fsw="120" data-fsh="120" src="<?php echo arrayGet($fileupload_crop, 'link_thumb_main_image'); ?>"/>
            <?php if($preview_fanacy){?>
                </span>
            <?php }else{?>
                </span>
            <?php }?>
		</div>
	</div>

	<div class="flex-card__float">
		<div class="info-alert-b mb-10">
			<i class="ep-icon ep-icon_info-stroke"></i>
			<div><?php echo translate('general_dashboard_modal_field_document_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload_crop, 'rules.size_placeholder')));?></div>
			<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload_crop, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_crop, 'rules.min_height'))); ?></div>
			<div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => arrayGet($fileupload_crop, 'rules.format'))); ?></div>
		</div>
	</div>
</div>

<a class="js-fileinput-button btn btn-dark mnw-130 btn-file">
	<span>Select file</span>
	<input id="js-upload-file-crop" type="file" value="Choose a file" accept="<?php echo arrayGet($fileupload_crop, 'accept'); ?>" <?php echo addQaUniqueIdentifier("global-cropper__add-image")?>>
</a>

<div id="js-popup-croppper-wr" class="display-n">
	<div id="js-popup-croppper" class="popup-cropperjs-container">
		<img id="js-my-img-crop" class="image" src="">
	</div>
</div>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/canvas-toblob/canvas-toblob.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/cropperjs/cropper.js');?>"></script>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/plug/cropperjs/cropper.css');?>" />

<script>
	(function() {
		"use strict";

		window.cropperImg = ({
			init: function(params) {
				cropperImg.self = this;
				cropperImg.extensions = ["<?php echo implode('","', explode(',', arrayGet($fileupload_crop, 'accept')));?>"];
				cropperImg.winImgWidth = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_width');?>);
				cropperImg.winImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_height');?>);
                cropperImg.aspectRatio = cropperImg.winImgWidth / cropperImg.winImgHeight;
				cropperImg.crpImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'crop_img_height', 400);?>);
				cropperImg.$cropImg = $('#js-my-img-crop');
				cropperImg.$mainImg = $('#js-view-main-photo');
				cropperImg.$ploadFile = $('#js-upload-file-crop');
				cropperImg.$ploadFileBtn = cropperImg.$ploadFile.closest('.js-fileinput-button');
				cropperImg.$popupCropperShowWr = $('#js-popup-croppper-wr');
				cropperImg.popupCropperShow = '#js-popup-croppper';
				cropperImg.$popupCropperShow = $('#js-popup-croppper');
				cropperImg.$uploadImgCrop;
				cropperImg.uploadButton = $('.js-img-crop-fileinput-loader-btn');
				cropperImg.fileUploadUrl = "<?php echo $fileupload_crop['url']['upload']; ?>";
				cropperImg.imgOriginalName;

				cropperImg.self.initListiners();
				cropperImg.self.initPlug();
			},
			croppperCalcHeight: function(){
				var windHeight = $(window).height();

				if(windHeight < 540){
					cropperImg.crpImgHeight = cropperImg.winImgHeight + 30;
				}else if(windHeight < 450){
					cropperImg.crpImgHeight = cropperImg.winImgHeight + 10;
				}

				return cropperImg.crpImgHeight;
			},
			initListiners: function(){
				mix(
					window,
					{
						cropImage: cropperImg.onCropImage
					},
					false
				);

				cropperImg.$ploadFile.on('change', function () {
					cropperImg.self.readFileCrop(this);
				});

				cropperImg.$cropImg.on('click', '.js-cropper-rotate', function(e) {
					e.preventDefault();
					cropperImg.$uploadImgCrop.croppie('rotate', -90);
				});
			},
			initPlug: function(){
                cropperImg.self.existRealImage();
                cropperImg.$ploadFileBtn
                    .addClass('validate[required]')
                    .setValHookType('mainImages');

                $.valHooks.mainImages = {
                    get: function (el) {
                        return cropperImg.$mainImg.find('input[type="hidden"]').length ? 1 : '';
                    }
                };
            },
			initCropper: function(){

				var image = document.getElementById('js-my-img-crop');
				var cropW = 0;
				var cropH = 0;

				cropperImg.$uploadImgCrop = new Cropper(image, {
					viewMode: 0,
					zoomable: true,
					aspectRatio: cropperImg.aspectRatio,
					autoCropArea: 1,
					wheelZoomRatio: 0.06,
					dragMode: 'move',
					scalable: false,
					toggleDragModeOnDblclick: false,
					cropBoxResizable: false,
					cropBoxMovable: false,
					getCroppedCanvas:{fillcolor: "#fff"},
					crop: function(event) {
						cropW = event.detail.width;
						cropH = event.detail.height;
						// console.log(event.detail);
					},
					zoom: function(event) {
						var imageData = this.cropper.getImageData();
						var canvasData = this.cropper.getCanvasData();
						var cropBoxData = this.cropper.getCropBoxData();
						var data = this.cropper.getData();
						// console.log(canvasData);

						if(event.detail.oldRatio < event.detail.ratio) {
							// console.log('plus zoom');

							if(
								cropW < cropperImg.winImgWidth
								|| cropH < cropperImg.winImgHeight
							){
								this.cropper.zoomTo(event.detail.oldRatio);
								event.preventDefault();
							}
						}else{
							// console.log('min zoom');
							var maxP = Math.max(imageData.width, imageData.height);
							var minPCropprt = Math.min(cropBoxData.width, cropBoxData.height);
							// console.log(maxP);
							// console.log(imageData.width);
                            // console.log(imageData.height);
                            // console.log(imageData.width == imageData.height);

                            if(
                                ((maxP == imageData.width) && (imageData.width != imageData.height) && (maxP <= cropBoxData.width) && (imageData.height <= cropBoxData.height))
                                || ((maxP == imageData.height) && (imageData.width != imageData.height) && (maxP <= cropBoxData.height) && (imageData.width <= cropBoxData.width))
                                || ((imageData.width == imageData.height) && (maxP <= minPCropprt))
							){
								// console.log('mmin zoom');
								if(event.detail.oldRatio != event.detail.ratio){
									// console.log('min set');
									this.cropper.zoomTo(event.detail.oldRatio);
								}
								event.preventDefault();
							}
						}
					}
				});

			},
            existRealImage: function(){
                var image = cropperImg.$mainImg.find('.image').attr('src');

                if(image.match(/noimage/g) === null && image.match(/main-image.svg/g) === null){
                    cropperImg.$mainImg.append('<input type="hidden" name="images_main_validate" value="' + image + '">');
                }
            },
			readFileCrop: function(input) {

				if (input.files && input.files[0]) {
					var reader = new FileReader();
					cropperImg.imgOriginalName = input.files[0].name || 'avatar.png';

					if (!cropperImg.extensions.includes(input.files[0].type)) {
						// systemMessages('Available file formats (<?php echo arrayGet($fileupload_crop, 'rules.format');?>).', 'error');
						systemMessages('File type not allowed', 'error');
						return;
					}

                    if (input.files[0].size > parseInt(<?php echo $fileupload_crop['rules']['size'];?>, 10)) {
                        systemMessages('The maximum file size was exceeded.', 'error');
						return;
                    }

					reader.onload = function (e) {
						cropperImg.getFileSize(e);
					}

					reader.readAsDataURL(input.files[0]);
					input.value = '';
				}
			},
			getFileSize: function(img) {
				var image = new Image();
				var rezult = {h: 0, w: 0};
				image.src = img.target.result;

				image.onload = function () {
					if(
						cropperImg.winImgWidth <= parseInt(this.width)
						&& cropperImg.winImgHeight <= parseInt(this.height)
					){
						cropperImg.openModal(img);
					}else{
						systemMessages('<?php echo translate('general_dashboard_error_image_sizes_text', array('{width}' => arrayGet($fileupload_crop, 'rules.min_width'), '{height}' => arrayGet($fileupload_crop, 'rules.min_height'))); ?>', 'error');
					}
				};
			},
			openModal: function(img) {
				var btnFooter = '<button class="btn btn-primary mnw-150 pull-right call-function" <?php echo addQaUniqueIdentifier("photo-cropper__crop-popup__crop-image-button")?> data-callback="cropImage" type="button"><?php echo arrayGet($fileupload_crop, 'btn_text_save_picture')?></button>';
				BootstrapDialog.show({
					cssClass: 'info-bootstrap-dialog inputs-40',
					title: '<?php echo arrayGet($fileupload_crop, 'title_text_popup')?>',
					type: 'type-light',
					size: 'size-wide',
					closable: true,
					closeByBackdrop: false,
					closeByKeyboard: false,
					draggable: false,
					animate: true,
					nl2br: false,
					onshow: function(dialog) {
						showLoader(cropperImg.popupCropperShow);
						var $modal_dialog = dialog.getModalDialog();
						$modal_dialog.addClass('modal-dialog-centered');
						dialog.getModalBody().append(cropperImg.$popupCropperShow);
						dialog.getModalFooter().html(btnFooter).show();
					},
					onshown: function(dialogRef){
						cropperImg.$cropImg.addClass('ready');
						$('#js-my-img-crop').attr('src', img.target.result);
						hideLoader(cropperImg.popupCropperShow);
						cropperImg.self.initCropper();
					},
					onhidden: function(dialogRef){
						cropperImg.$uploadImgCrop.destroy();
						cropperImg.$popupCropperShowWr.append(cropperImg.$popupCropperShow);
					}
				});

			},
			onCropImage: function($this) {
				$this.prop('disabled', true);
				// console.log(cropperImg.winImgWidth);
				// console.log(cropperImg.winImgHeight);

				cropperImg.$uploadImgCrop.getCroppedCanvas({width: cropperImg.winImgWidth, height: cropperImg.winImgHeight, fillColor:'#fff'}).toBlob(function (blob) {
					var formData = new FormData();
					formData.append('files', blob, 'cropp.jpeg');

					$.ajax({
						url: cropperImg.fileUploadUrl,
						type: 'POST',
						dataType: "JSON",
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function(){
							showLoader(cropperImg.popupCropperShow);
						},
						success: function(resp){
							hideLoader(cropperImg.popupCropperShow);

							if (resp.mess_type == 'success') {

								var d = new Date();

								cropperImg.$mainImg.attr('href', resp.path);
								cropperImg.$mainImg.find('.image').attr('src', resp.thumb);

								if(cropperImg.$mainImg.find('.image').css('background-image') != ''){
									cropperImg.$mainImg.find('.image').css('background-image', 'url(' + resp.thumb + ')');
								}

								if(resp.tmp_url != undefined){
									var hiddenInput = cropperImg.$mainImg.find('input[type="hidden"]');
									if(hiddenInput.length){
										hiddenInput.remove();
                                    }

                                    cropperImg.$ploadFileBtn.removeClass('validengine-border').prev('.formError').remove();
									cropperImg.$mainImg.append('<input type="hidden" name="images_main" value="'+resp.tmp_url+'">');
								}

								if(typeof callbackReplaceCropImages != "undefined"){
									callbackReplaceCropImages(resp);
								}

								// closeFancyBox();
                                BootstrapDialog.closeAll();

							} else {
								systemMessages( resp.message, resp.mess_type );
							}
						}
					});

				}, "image/jpeg", 0.75);
			}
		});

	}());

	$(function() {
		cropperImg.init();
    });
</script>

<div class="">
	<div class="info-alert-b mb-10">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<div><?php echo translate('general_dashboard_modal_field_document_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload_crop_multiple, 'rules.size_placeholder')));?></div>
		<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload_crop_multiple, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_crop_multiple, 'rules.min_height'))); ?></div>
		<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => arrayGet($fileupload_crop_multiple, 'limit'))); ?></div>
		<div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => arrayGet($fileupload_crop_multiple, 'rules.format'))); ?></div>
	</div>

	<div class="mb-10">
		<a class="js-fileinput-button-multiple btn btn-dark mnw-130 btn-file">
			<span>Select file</span>
			<input id="js-upload-file-crop-multiple" type="file" value="Choose a file" accept="<?php echo arrayGet($fileupload_crop_multiple, 'accept'); ?>" <?php echo addQaUniqueIdentifier("global-cropper__add-image")?>>
		</a>
	</div>
</div>

<div id="js-cropper-preview-list" class="cropper-preview-list">
	<?php
		$classPreview = arrayGet($fileupload_crop_multiple, 'class_preview');
		$images = arrayGet($fileupload_crop_multiple, 'images_links');
	?>

	<?php foreach($images as $imagesItem){ ?>
		<div class="js-cropper-preview-item cropper-preview-list__item">
			<div class="cropper-preview image-card3<?php if(!empty($classPreview)){ echo ' '.$classPreview; }?><?php if(arrayGet($fileupload_crop_multiple, 'image_circle_preview')){?> bd-radius-50pr<?php }?>">
				<?php $preview_fanacy = arrayGet($fileupload_crop_multiple, 'preview_fanacy') ?? true;?>
				<?php if($preview_fanacy){?>
					<span
						class="link"
						data-title="<?php echo arrayGet($fileupload_crop_multiple, 'title_text_popup'); ?>"
					>
				<?php }else{?>
					<span
						class="link"
					>
				<?php }?>
					<img class="image js-fs-image" src="<?php echo $imagesItem['link']; ?>"/>
				<?php if($preview_fanacy){?>
					</span>
				<?php }else{?>
					</span>
				<?php }?>
				<div class="cropper-preview__remove">
					<a
						class="link confirm-dialog"
						data-callback="removeMultipleImage"
						data-message="Do you want to remove this image?"
						data-name="<?php echo $imagesItem['name']; ?>"
						href="#"
					><i class="ep-icon ep-icon_remove"></i></a>
				</div>
			</div>
		</div>
	<?php }?>
</div>

<div id="js-popup-croppper-wr-multiple" class="display-n">
	<div id="js-popup-croppper-multiple" class="popup-cropperjs-container">
		<img id="js-my-img-crop-multiple" class="image" src="">
	</div>
</div>

<script type="text/template" id="js-template-selected-image-multiple">
	<div class="js-cropper-preview-item cropper-preview-list__item">
		<div class="cropper-preview image-card3">
			<span
				class="link"
			>
				<img class="image" src="{{SRC}}"/>
				<input type="hidden" name="images_multiple[]" value="{{INPUT}}">
			</span>

			<div class="cropper-preview__remove">
				<a
					class="link confirm-dialog"
					data-callback="removeMultipleImageTmp"
					data-message="Do you want to remove this image?"
					data-name="{{NAME}}"
					href="#"
				><i class="ep-icon ep-icon_remove"></i></a>
			</div>
		</div>
	</div>
</script>

<?php if((bool)$fileupload_crop_multiple['only_multiple_plug']){ ?>
	<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/canvas-toblob/canvas-toblob.js');?>"></script>
	<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/cropperjs/cropper.js');?>"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/plug/cropperjs/cropper.css');?>" />
<?php } ?>

<script>
	(function() {
		"use strict";

		window.cropperImgMultiple = ({
			init: function(params) {
				cropperImgMultiple.self = this;
				cropperImgMultiple.extensions = ["<?php echo implode('","', explode(',', arrayGet($fileupload_crop_multiple, 'accept')));?>"];
				cropperImgMultiple.winImgWidth = parseInt(<?php echo arrayGet($fileupload_crop_multiple, 'rules.min_width');?>);
				cropperImgMultiple.winImgHeight = parseInt(<?php echo arrayGet($fileupload_crop_multiple, 'rules.min_height');?>);
                cropperImgMultiple.aspectRatio = cropperImgMultiple.winImgWidth / cropperImgMultiple.winImgHeight;
				cropperImgMultiple.crpImgHeight = parseInt(<?php echo arrayGet($fileupload_crop_multiple, 'crop_img_height', 400);?>);
				cropperImgMultiple.$cropImg = $('#js-my-img-crop-multiple');
				cropperImgMultiple.$mainImg = $('#js-view-multiple-photo');
				cropperImgMultiple.$ploadFile = $('#js-upload-file-crop-multiple');
				cropperImgMultiple.$ploadFileBtn = cropperImgMultiple.$ploadFile.closest('.js-fileinput-button-multiple');
				cropperImgMultiple.$popupCropperShowWr = $('#js-popup-croppper-wr-multiple');
				cropperImgMultiple.popupCropperShow = '#js-popup-croppper-multiple';
				cropperImgMultiple.$popupCropperShow = $('#js-popup-croppper-multiple');
				cropperImgMultiple.$uploadImgCrop;
				cropperImgMultiple.uploadButton = $('.js-img-crop-fileinput-loader-btn-multiple');
				cropperImgMultiple.fileUploadUrl = "<?php echo $fileupload_crop_multiple['url']['upload']; ?>";
				cropperImgMultiple.fileRemoveTmpUrl = "<?php echo $fileupload_crop_multiple['url']['removeTmp']; ?>";
				cropperImgMultiple.imgOriginalName;
				cropperImgMultiple.previewListWr = $('#js-cropper-preview-list');
				cropperImgMultiple.imgSelectedTemplate = $('#js-template-selected-image-multiple');

				cropperImgMultiple.self.initListiners();
				cropperImgMultiple.self.initPlug();
			},
			croppperCalcHeight: function(){
				var windHeight = $(window).height();

				if(windHeight < 540){
					cropperImgMultiple.crpImgHeight = cropperImgMultiple.winImgHeight + 30;
				}else if(windHeight < 450){
					cropperImgMultiple.crpImgHeight = cropperImgMultiple.winImgHeight + 10;
				}

				return cropperImgMultiple.crpImgHeight;
			},
			initListiners: function(){
				mix(
					window,
					{
						cropImageMultiple: cropperImgMultiple.onCropImage,
						removeMultipleImageTmp: cropperImgMultiple.onRemoveMultipleImageTmp,
						removeMultipleImage: cropperImgMultiple.onRemoveMultipleImage,
					},
					false
				);

				cropperImgMultiple.$ploadFile.on('change', function () {
					cropperImgMultiple.self.readFileCrop(this);
				});

				cropperImgMultiple.$cropImg.on('click', '.js-cropper-rotate-multiple', function(e) {
					e.preventDefault();
					cropperImgMultiple.$uploadImgCrop.croppie('rotate', -90);
				});
			},
			initPlug: function(){
                cropperImgMultiple.$ploadFileBtn
                    .addClass('validate[required]')
                    .setValHookType('galleryImages');

                $.valHooks.galleryImages = {
                    get: function (el) {
                        return cropperImgMultiple.previewListWr.find('.js-cropper-preview-item').length ? 1 : '';
                    }
                };
            },
			initCropper: function(){

				var image = document.getElementById('js-my-img-crop-multiple');
				var cropW = 0;
				var cropH = 0;

				cropperImgMultiple.$uploadImgCrop = new Cropper(image, {
					viewMode: 0,
					zoomable: true,
					aspectRatio: cropperImgMultiple.aspectRatio,
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
								cropW < cropperImgMultiple.winImgWidth
								|| cropH < cropperImgMultiple.winImgHeight
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
			readFileCrop: function(input) {

				if (input.files && input.files[0]) {
					var reader = new FileReader();
					cropperImgMultiple.imgOriginalName = input.files[0].name || 'avatar.png';

					if (!cropperImgMultiple.extensions.includes(input.files[0].type)) {
						// systemMessages('Available file formats (<?php echo arrayGet($fileupload_crop_multiple, 'rules.format');?>).', 'error');
						systemMessages('File type not allowed', 'error');
						return;
					}

                    if (input.files[0].size > parseInt(<?php echo $fileupload_crop_multiple['rules']['size'];?>, 10)) {
                        systemMessages('The maximum file size was exceeded.', 'error');
						return;
                    }

					reader.onload = function (e) {
						cropperImgMultiple.getFileSize(e);
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
						cropperImgMultiple.winImgWidth <= parseInt(this.width)
						&& cropperImgMultiple.winImgHeight <= parseInt(this.height)
					){
						cropperImgMultiple.openModal(img);
					}else{
						systemMessages('<?php echo translate('general_dashboard_error_image_sizes_text', array('{width}' => arrayGet($fileupload_crop_multiple, 'rules.min_width'), '{height}' => arrayGet($fileupload_crop_multiple, 'rules.min_height'))); ?>', 'error');
					}
				};
			},
			openModal: function(img) {
				var btnFooter = '<button class="btn btn-primary mnw-150 pull-right call-function" data-callback="cropImageMultiple" <?php echo addQaUniqueIdentifier("photo-cropper__crop-popup__crop-image-button")?> type="button"><?php echo arrayGet($fileupload_crop_multiple, 'btn_text_save_picture')?></button>';
				BootstrapDialog.show({
					cssClass: 'info-bootstrap-dialog inputs-40',
					title: '<?php echo arrayGet($fileupload_crop_multiple, 'title_text_popup')?>',
					type: 'type-light',
					size: 'size-wide',
					closable: true,
					closeByBackdrop: false,
					closeByKeyboard: false,
					draggable: false,
					animate: true,
					nl2br: false,
					onshow: function(dialog) {
						showLoader(cropperImgMultiple.popupCropperShow);
						var $modal_dialog = dialog.getModalDialog();
						$modal_dialog.addClass('modal-dialog-centered');
						dialog.getModalBody().append(cropperImgMultiple.$popupCropperShow);
						dialog.getModalFooter().html(btnFooter).show();
					},
					onshown: function(dialogRef){
						cropperImgMultiple.$cropImg.addClass('ready');
						$('#js-my-img-crop-multiple').attr('src', img.target.result);
						hideLoader(cropperImgMultiple.popupCropperShow);
						cropperImgMultiple.self.initCropper();
					},
					onhidden: function(dialogRef){
						cropperImgMultiple.$uploadImgCrop.destroy();
						cropperImgMultiple.$popupCropperShowWr.append(cropperImgMultiple.$popupCropperShow);
					}
				});

			},
			onCropImage: function($this) {
				$this.prop('disabled', true);

				cropperImgMultiple.$uploadImgCrop.getCroppedCanvas({width: cropperImgMultiple.winImgWidth, height: cropperImgMultiple.winImgHeight, fillColor:'#fff'}).toBlob(function (blob) {
					var formData = new FormData();
					formData.append('files', blob, 'cropp.jpeg');

					$.ajax({
						url: cropperImgMultiple.fileUploadUrl,
						type: 'POST',
						dataType: "JSON",
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function(){
							showLoader(cropperImgMultiple.popupCropperShow);
						},
						success: function(resp){
							hideLoader(cropperImgMultiple.popupCropperShow);

							if (resp.mess_type == 'success') {

								var d = new Date();

								var img = cropperImgMultiple.imgSelectedTemplate.text()
									.replace('{{SRC}}', resp.thumb)
									.replace('{{INPUT}}', resp.tmp_url)
									.replace('{{NAME}}', resp.name);

								cropperImgMultiple.previewListWr.append(img);

                                BootstrapDialog.closeAll();

							} else {
								systemMessages( resp.message, resp.mess_type );
							}
						}
					});

				}, "image/jpeg", 0.75);
			},
			onRemoveMultipleImageTmp: function($this){
				var onRequestSuccess = function (data) {
					if(data.mess_type == 'success'){
						$this.closest('.js-cropper-preview-item').remove();
					} else {
						systemMessages( data.message, data.mess_type);
					}
				};

				postRequest(cropperImgMultiple.fileRemoveTmpUrl, { file: $this.data('name') })
					.then(onRequestSuccess)
					.catch(onRequestError);
			},
			onRemoveMultipleImage: function($this){
				var imageName = $this.data('name');

				if (!imageName) {
					systemMessages('The image name is undefined.', 'message-error');
					return;
				}

				$this.closest('.js-cropper-preview-item').remove();
				cropperImgMultiple.previewListWr.append('<input type="hidden" name="images_multiple_removed[]" value="' + imageName + '">');
			}
		});

	}());

	$(function() {
		cropperImgMultiple.init();
    });
</script>

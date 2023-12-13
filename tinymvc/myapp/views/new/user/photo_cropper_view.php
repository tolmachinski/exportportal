<a class="btn btn-dark mnw-125 btn-file">
	<span>Select file...</span>
	<input <?php echo addQaUniqueIdentifier("photo-cropper__upload-input")?> id="js-upload-file-crop" type="file" value="Choose a file" accept="<?php echo arrayGet($fileupload_crop, 'accept'); ?>">
</a>

<div class="flex-card show-767 mt-20">
	<div class="flex-card__fixed mb-5">
		<div id="js-view-main-photo" class="w-125 h-125 <?php if(arrayGet($fileupload_crop, 'image_circle_preview')){?>bd-radius-50pr<?php }?> image-card2">
			<img class="image" src="<?php echo arrayGet($fileupload_crop, 'link_thumb_main_image'); ?>"/>
		</div>
	</div>

    <?php if ($isEditProfilePicture) { ?>
    <div class="flex-card__float">
        <div class="info-alert-b mnh-95 pt-10 pb-10">
    <?php } else { ?>
    <div class="flex-card__float mw-580">
		<div class="info-alert-b mnh-125">
    <?php } ?>
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => arrayGet($fileupload_crop, 'rules.size_placeholder'))); ?></div>
			<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => arrayGet($fileupload_crop, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($fileupload_crop, 'rules.min_height'))); ?></div>
            <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => arrayGet($fileupload_crop, 'rules.format'))); ?></div>
		</div>

        <?php if ($isEditProfilePicture && is_certified()) { ?>
            <label class="input-label input-label--info mt-10 mb-0 dn-md_i custom-checkbox">
                <input class="js-certified-checkbox" type="checkbox" <?php echo checked(1, session()->__get('user_photo_with_badge'));?>>
                <span class="custom-checkbox__text"><?php echo translate('user_photo_use_certified_image_label'); ?></span>
                <a
                    class="info-dialog ep-icon ep-icon_info ml-5"
                    data-message="<?php echo translate('user_photo_info_dialog_message'); ?>"
                    data-title="<?php echo translate('user_photo_info_dialog_title'); ?>"
                    href="#">
                </a>
            </label>
        <?php } ?>
    </div>
</div>

<?php if ($isEditProfilePicture && is_certified()) { ?>
    <div class="display-n show-767">
        <label class="input-label input-label--info mt-10 mb-0 custom-checkbox">
            <input class="js-certified-checkbox" type="checkbox" <?php echo checked(1, session()->__get('user_photo_with_badge'));?>>
            <span class="custom-checkbox__text"><?php echo translate('user_photo_use_certified_image_label'); ?></span>
            <a
                class="info-dialog ep-icon ep-icon_info ml-5"
                data-message="<?php echo translate('user_photo_info_dialog_message'); ?>"
                data-title="<?php echo translate('user_photo_info_dialog_title'); ?>"
                href="#">
            </a>
        </label>
    </div>
<?php } ?>

<div id="js-popup-croppper-wr" class="display-n">
	<div id="js-popup-croppper" class="popup-croppie-container">
		<div id="js-my-img-crop" <?php if(arrayGet($fileupload_crop, 'image_circle_preview')){?>class="croppie-circle"<?php }?>></div>
	</div>
</div>

<script type="text/javascript" src="<?php echo 'public/plug/croppie-master/croppie.js';?>"></script>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/croppie.css');?>" />

<script>
	(function() {
		"use strict";

		window.cropperImg = ({
			init: function (params) {
				cropperImg.self = this;
				cropperImg.extensions = ["<?php echo implode('","', explode(',', arrayGet($fileupload_crop, 'accept')));?>"];
				cropperImg.winImgWidth = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_width');?>);
				cropperImg.winImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'rules.min_height');?>);
				cropperImg.crpImgHeight = parseInt(<?php echo arrayGet($fileupload_crop, 'crop_img_height', 400);?>);
				cropperImg.$cropImg = $('#js-my-img-crop');
				cropperImg.$mainImg = $('#js-view-main-photo');
				cropperImg.$ploadFile = $('#js-upload-file-crop');
				cropperImg.$popupCropperShowWr = $('#js-popup-croppper-wr');
				cropperImg.popupCropperShow = '#js-popup-croppper';
				cropperImg.$popupCropperShow = $('#js-popup-croppper');
				cropperImg.$uploadImgCrop;
				cropperImg.uploadButton = $('.js-img-crop-fileinput-loader-btn');
				cropperImg.fileUploadUrl = "<?php echo $fileupload_crop['url']['upload']; ?>";
				cropperImg.imgOriginalName;

				cropperImg.croppperParams = {
					boundary: {
						width: '100%',
						height: cropperImg.self.croppperCalcHeight()
					},
					viewport: {
						width: cropperImg.winImgWidth,
						height: cropperImg.winImgHeight,
						type: 'square'
					},
					enableExif: true,
					maxZoom: 1,
					enableOrientation: true,
				};

				<?php if(isset($fileupload_crop['croppper_limit_by_min']) && $fileupload_crop['croppper_limit_by_min']){?>
					cropperImg.croppperParams.enforceBoundary = false;
					cropperImg.croppperParams.enforceBoundaryMin = true;
				<?php }?>

				cropperImg.self.initListiners();
				cropperImg.self.initCropper();
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
			initCropper: function(){
				cropperImg.$uploadImgCrop = cropperImg.$cropImg.croppie(cropperImg.croppperParams);

				setTimeout(function(){
					cropperImg.$cropImg.find('.cr-slider-wrap').append('<a class="btn btn-dark js-cropper-rotate ml-15" <?php echo addQaUniqueIdentifier("photo-cropper__crop-popup__rotate-button")?> href="#"><i class="ep-icon ep-icon_updates"></i></a>');
				}, 1000)
			},
			readFileCrop: function (input) {

				if (input.files && input.files[0]) {
					var reader = new FileReader();
                    cropperImg.imgOriginalName = input.files[0].name || 'avatar.png';

                    if (input.files[0].size > parseInt(<?php echo $fileupload_crop['rules']['size'];?>, 10)) {
                        systemMessages('The maximum file size was exceeded.', 'error');
						return;
                    }

					if (!cropperImg.extensions.includes(input.files[0].type)) {
						systemMessages('Invalid file format. List of supported formats (<?php echo arrayGet($fileupload_crop, 'rules.format');?>).', 'error');
						return;
                    }

					reader.onload = function (e) {
						cropperImg.getFileSize(e);
					}

					reader.readAsDataURL(input.files[0]);
					input.value = '';
				}
				// else{
				// 	systemMessages(input.files[0].error, 'error');
				// }
			},
			getFileSize: function (img) {
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
			openModal: function (img) {
				// var bodyContent = $(cropperImg.popupCropperShow).replaceAll();
				var btnFooter = '<button class="btn btn-primary mnw-150 pull-right call-function" data-callback="cropImage" <?php echo addQaUniqueIdentifier("photo-cropper__crop-popup__crop-image-button")?> type="button"><?php echo arrayGet($fileupload_crop, 'btn_text_save_picture')?></button>';
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
						// dialog.getModalBody().html($(cropperImg.popupCropperShow));
						// cropperImg.$popupCropperShow.appendTo(dialog.getModalBody());
						dialog.getModalBody().append(cropperImg.$popupCropperShow);
						dialog.getModalFooter().html(btnFooter).show();
					},
					onshown: function(dialogRef){
						cropperImg.$cropImg.addClass('ready');
						cropperImg.$uploadImgCrop.croppie('bind', {
							url: img.target.result
						}).then(function(){
							hideLoader(cropperImg.popupCropperShow);
						});
					},
					onhidden: function(dialogRef){
						// cropperImg.$popupCropperShow.appendTo(cropperImg.$popupCropperShowWr);
						cropperImg.$popupCropperShowWr.append(cropperImg.$popupCropperShow);
					}
				});

				// $.fancybox.open({
				// 		'title': '<?php //echo arrayGet($fileupload_crop, 'title_text_popup')?>',
				// 		'href': cropperImg.popupCropperShow
				// 	},{
				// 		width		: fancyW,
				// 		height		: 'auto',
				// 		maxWidth	: 500,
				// 		autoSize	: false,
				// 		loop : false,
				// 		helpers : {
				// 			title: {
				// 				type: 'inside',
				// 				position: 'top'
				// 			},
				// 			overlay: {
				// 				locked: false
				// 			}
				// 		},
				// 		lang : __site_lang,
				// 		i18n : translate_js_one({plug:'fancybox'}),
				// 		modal: true,
				// 		padding: fancyP,
				// 		closeBtn : true,
				// 		closeBtnWrapper: '.fancybox-skin .fancybox-title',
				// 		beforeShow : function() {
				// 			showLoader(cropperImg.popupCropperShow);
				// 		},
				// 		afterShow : function() {
				// 			cropperImg.$cropImg.addClass('ready');

				// 			cropperImg.$uploadImgCrop.croppie('bind', {
				// 				url: img.target.result
				// 			}).then(function(){
				// 				hideLoader(cropperImg.popupCropperShow);
				// 			});
				// 		}
				// 	}
				// );

			},
			onCropImage: function () {

				var crop_params = {
					type: 'blob',
					size: 'viewport'
				};

				if (navigator.userAgent.indexOf('Edge') >= 0){
					crop_params.size = {width: cropperImg.winImgWidth + 1, height: cropperImg.winImgHeight + 1};
				}

				cropperImg.$uploadImgCrop.croppie('result',
					crop_params
				).then(function (resp) {
					var extension = cropperImg.imgOriginalName.split('.').pop()
					var formData = new FormData();
					formData.append('files', resp, cropperImg.imgOriginalName.replace(new RegExp("^(.+)\." + extension + "$"), '$1.png'));

					$.ajax({
						url: cropperImg.fileUploadUrl,
						type: 'POST',
						dataType: "JSON",
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function(){
                            var $checkbox = $('.js-certified-checkbox');

							showLoader(cropperImg.popupCropperShow);

                            if ($checkbox.length && $checkbox.prop( "checked")) {
                                $checkbox.prop( "checked", false );
                            }
						},
						success: function(resp){
							hideLoader(cropperImg.popupCropperShow);

							if (resp.mess_type == 'success') {

								var d = new Date();
								cropperImg.$mainImg.attr('href', resp.path);
								cropperImg.$mainImg.find('.image').attr('src', resp.thumb);
                                console.log(cropperImg.$mainImg.find('.image'));


								if(cropperImg.$mainImg.find('.image').css('background-image') != ''){
									cropperImg.$mainImg.find('.image').css('background-image', 'url(' + resp.thumb + ')');
								}

								if(resp.tmp_url != undefined){
									var hiddenInput = cropperImg.$mainImg.find('input[type="hidden"]');
									if(hiddenInput.length){
										hiddenInput.remove();
									}
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
				});
			}
		});

	}());

	$(function() {
		cropperImg.init();

        let checkbox = $('.js-certified-checkbox');

        checkbox.on('click', function () {
            if (checkbox.prop( "checked")) {
                setCertifiedImage(true);
            } else {
                setCertifiedImage(false);
            }
        })
    });

</script>
<?php if(is_certified()){?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/user_page/photo-badge.js'); ?>"></script>
<?php } ?>

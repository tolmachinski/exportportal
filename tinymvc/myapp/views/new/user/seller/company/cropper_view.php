<a class="btn btn-dark mnw-125 btn-file">
	<span>Select file...</span>
	<input
        <?php echo addQaUniqueIdentifier('photo-cropper__upload-input'); ?>
        id="js-upload-file-crop"
        type="file"
        value="Choose a file"
        accept="<?php echo arrayGet($parameters, 'accept'); ?>"
    >
</a>

<div class="flex-card show-767 mt-10">
	<div class="flex-card__fixed mr-5">
		<div id="js-view-main-photo" class="w-125 h-125 <?php if (arrayGet($parameters, 'image_circle_preview')) {?>bd-radius-50pr<?php }?> image-card2">
			<img class="image js-fs-image" src="<?php echo arrayGet($parameters, 'link_thumb_main_image'); ?>"/>
		</div>
	</div>

    <div class="flex-card__float">
		<div class="info-alert-b mnh-125">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', ['[[SIZE]]' => arrayGet($parameters, 'rules.size_placeholder')]); ?></div>
			<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', ['[[WIDTH]]' => arrayGet($parameters, 'rules.min_width'), '[[HEIGHT]]' => arrayGet($parameters, 'rules.min_height')]); ?></div>
            <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', ['[[FORMATS]]' => arrayGet($parameters, 'rules.format')]); ?></div>
		</div>
    </div>
</div>

<div id="js-popup-croppper-wr" class="display-n">
	<div id="js-popup-croppper" class="popup-croppie-container">
		<div id="js-my-img-crop" <?php if (arrayGet($parameters, 'image_circle_preview')) {?>class="croppie-circle"<?php }?>></div>
	</div>
</div>

<?php echo dispatchDynamicFragment(
    'cropper:boot',
    [[
        'fileUploadUrl'         => $parameters['url']['upload'],
        'extensions'            => implode('","', explode(',', arrayGet($parameters, 'accept'))),
        'rulesSize'             => $parameters['rules']['size'],
        'rulesFormat'           => arrayGet($parameters, 'rules.format'),
        'modalTitle'            => arrayGet($parameters, 'title_text_popup'),
        'modalBtnText'          => arrayGet($parameters, 'btn_text_save_picture'),
        'cropperImageHeight'          => arrayGet($parameters, 'crop_img_height', 400),
        'imgValidationWidth'    => arrayGet($parameters, 'rules.min_width'),
        'imgValidationHeight'   => arrayGet($parameters, 'rules.min_height'),
        'imageValidationError'  => translate('general_dashboard_error_image_sizes_text', [
            '{width}'  => arrayGet($parameters, 'rules.min_width'),
            '{height}' => arrayGet($parameters, 'rules.min_height'),
        ]),
    ]],
    true
); ?>

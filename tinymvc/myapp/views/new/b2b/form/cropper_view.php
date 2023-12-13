<div class="b2b-request-form__main-image-wrapper">
    <div class="b2b-request-form__main-image">
        <div class="cropper-preview cropper-preview--b2b image-card3">
            <div id="js-view-main-photo" class="link" data-title="<?php echo arrayGet($parameters, 'title_text_popup'); ?>">
                <img class="image" src="<?php echo arrayGet($parameters, 'link_thumb_main_image'); ?>" alt="<?php echo arrayGet($parameters, 'title_text_popup'); ?>"/>
            </div>
        </div>
    </div>

    <div class="b2b-request-form__main-image-rules">
        <div class="info-alert-b info-alert-b--fs16">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div>
                <?php
                    echo translate('general_dashboard_modal_field_document_help_text_line_1', [
                        '[[SIZE]]' => arrayGet($parameters, 'rules.size_placeholder'),
                    ]);
                ?>
            </div>
            <div>
                <?php
                    echo translate('general_dashboard_modal_field_image_help_text_line_2', [
                        '[[WIDTH]]'  => arrayGet($parameters, 'rules.min_width'),
                        '[[HEIGHT]]' => arrayGet($parameters, 'rules.min_height'),
                    ]);
                ?>
            </div>
            <div>
                • <?php
                    echo translate('general_dashboard_modal_field_image_help_text_line_4', [
                        '[[FORMATS]]' => arrayGet($parameters, 'rules.format'),
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>

<div id="js-popup-croppper-wr" class="display-n">
    <div id="js-popup-croppper" class="popup-croppie-container">
        <div id="js-my-img-crop" <?php if (arrayGet($parameters, 'image_circle_preview')) {?>class="croppie-circle"<?php }?>></div>
    </div>
</div>

<a class="b2b-request-form__select-files-btn js-fileinput-button btn btn-dark btn-new16 btn-file">
    <span><?php echo translate('b2b_form_select_files_btn'); ?></span>
    <input
        id="js-upload-file-crop"
        type="file"
        value="<?php echo translate('b2b_form_select_files_placeholder', null, true); ?>"
        accept="<?php echo arrayGet($parameters, 'accept'); ?>"
        <?php echo addQaUniqueIdentifier('global__photo-cropper__upload-input'); ?>
    >
</a>

<?php echo dispatchDynamicFragment(
    'cropper:boot',
    [[
        'fileUploadUrl'         => $parameters['url']['upload'],
        'extensions'            => implode('","', explode(',', arrayGet($parameters, 'accept'))),
        'rulesSize'             => $parameters['rules']['size'],
        'rulesFormat'           => arrayGet($parameters, 'rules.format'),
        'modalTitle'            => arrayGet($parameters, 'title_text_popup'),
        'modalBtnText'          => arrayGet($parameters, 'btn_text_save_picture'),
        'inputName'             => 'main',
        'cropperImageHeight'    => arrayGet($parameters, 'crop_img_height', 400),
        'imgValidationWidth'    => arrayGet($parameters, 'rules.min_width'),
        'imgValidationHeight'   => arrayGet($parameters, 'rules.min_height'),
        'imageValidationError'  => translate('general_dashboard_error_image_sizes_text', [
            '{width}'  => arrayGet($parameters, 'rules.min_width'),
            '{height}' => arrayGet($parameters, 'rules.min_height'),
        ]),
    ]],
    true
); ?>

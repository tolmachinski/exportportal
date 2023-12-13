<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label class="ep-label"><?php echo translate('b2b_form_additional_pictures_label'); ?></label>
            <div class="juploader-b">
                <div class="info-alert-b info-alert-b--fs16 mt-10 mb-10">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div>
                        <?php
                            echo translate('b2b_form_additional_pictures_rules_line_1', [
                                '{{COUNT}}' => arrayGet($uploaderParameters, 'limits.amount.total'),
                            ]);
                        ?>
                    </div>
                    <div>
                        <?php
                            echo translate('b2b_form_additional_pictures_rules_line_2', [
                                '{{WIDTH}}'  => arrayGet($uploaderParameters, 'rules.min_width'),
                                '{{HEIGHT}}' => arrayGet($uploaderParameters, 'rules.min_height'),
                            ]);
                        ?>
                    </div>
                    <div>
                        <?php
                            echo translate('b2b_form_additional_pictures_rules_line_3', [
                                '{{SIZE}}' => arrayGet($uploaderParameters, 'rules.size_placeholder'),
                                '{{FORMATS}}' => str_replace('|', ', ', arrayGet($uploaderParameters, 'limits.formats')),
                            ]);
                        ?>
                    </div>
                </div>

                <div class="container-fluid-modal">
                    <div id="js-add-picture-image-wrapper" class="fileupload2 js-fileupload-wrapper">
                        <?php if(!empty($photos)){?>
                            <?php foreach($photos as $photo){ ?>
                                <div class="fileupload2__item image-card3 js-fileupload-item">
                                    <span class="link js-fileupload-image">
                                        <img
                                            class="image"
                                            src="<?php echo $photo['url']; ?>"
                                            alt="<?php echo cleanOutput($title ?? ''); ?>"
                                        >
                                    </span>

                                    <div class="js-fileupload-actions fileupload2__actions inputs-40">
                                        <a
                                            class="btn btn-light pl-10 pr-10 w-40 call-action"
                                            data-js-action="fileupload:remove-item-image"
                                            data-file="<?php echo $photo['id']; ?>"
                                            data-name="<?php echo $photo['photo']; ?>"
                                            data-message="<?php echo translate('b2b_form_additional_pictures_delete_confirm_message', null, true); ?>"
                                            title="<?php echo translate('b2b_form_additional_pictures_delete_confirm_title', null, true); ?>"
                                            href="#"
                                            <?php echo addQaUniqueIdentifier('global__image-uploader__remove-image-btn'); ?>
                                        >
                                            <i class="ep-icon ep-icon_trash-stroke fs-17"></i>
                                        </a>

                                        <input type="hidden" name="images_validate[]" value="<?php echo $photo['photo']; ?>">
                                    </div>
                                </div>
                            <?php }?>
                        <?php }?>
                    </div>
                </div>

                <button id="js-fileinput-uploader-button" class="b2b-request-form__select-files-btn btn btn-dark btn-new16 fileinput-button mt-5" type="button">
                    <span><?php echo translate('seller_pictures_dashboard_modal_field_image_upload_button_text'); ?></span>
                    <input
                        id="js-add-picture-uploader"
                        type="file"
                        name="files"
                        accept="<?php echo arrayGet($uploaderParameters, 'limits.accept'); ?>">
                </button>

                <span id="js-upload-loader" class="fileinput-loader-btn" style="display: none;">
                    <img
                        class="image"
                        src="<?php echo __IMG_URL; ?>public/img/loader.svg"
                        alt="loader"
                    >
                    <?php echo translate('seller_pictures_dashboard_modal_field_image_upload_placeholder'); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        'lazy-loading:fileupload',
        [
            [
                'filesAmount'            => arrayGet($uploaderParameters, 'limits.amount.total'),
                'filesAllowed'           => arrayGet($uploaderParameters, 'limits.amount.total') - arrayGet($uploaderParameters, 'limits.amount.current', 0),
                'fileTypes'              => arrayGet($uploaderParameters, 'limits.mimetypes'),
                'fileFormats'            => arrayGet($uploaderParameters, 'limits.formats'),
                'fileUploadMaxSize'      => arrayGet($uploaderParameters, 'rules.size'),
                'fileUploadUrl'          => arrayGet($uploaderParameters, 'url.upload'),
                'fileRemoveUrl'          => arrayGet($uploaderParameters, 'url.delete'),
                'uploaderSelector'       => '#js-add-picture-uploader',
                'uploadBtnSelector'      => '#js-fileinput-uploader-button',
                'uploadLoaderSelector'   => '#js-upload-loader',
                'imageWrapperSelector'   => '#js-add-picture-image-wrapper',
            ]
        ],
        true,
    );
?>

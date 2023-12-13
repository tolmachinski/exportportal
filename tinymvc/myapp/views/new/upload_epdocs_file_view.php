<div id="<?php echo $group; ?>--formfield--uploader">
    <?php if (!empty($label)) { ?>
        <label class="input-label <?php echo $is_required ? 'input-label--required' : ''; ?>"><?php echo $label; ?></label>
    <?php } ?>
    <div class="info-alert-b mb-10">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div>
            <?php echo translate(
                arrayGet($translations, 'format_text', 'general_dashboard_modal_field_document_help_text_line_3_alternate'),
                array(
                    '[[FORMATS]]' => implode(', ', arrayGet($fileupload, 'limits.type.extensions', array()))
                ),
                true
            ); ?>
        </div>
        <div>
            <?php echo translate(
                arrayGet($translations, 'size_text', 'general_dashboard_modal_field_document_help_text_line_1_alternate'),
                array(
                    '[[SIZE]]' => arrayGet($fileupload, 'limits.filesize.placeholder', '0B')
                ),
                true
            ); ?>
        </div>
        <?php if ($is_limited) { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'limited_amount_text', 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate'),
                    array(
                        '[[TOTAL]]'   => arrayGet($fileupload, 'limits.amount.total', 0),
                        '[[ALLOWED]]' => arrayGet($fileupload, 'limits.amount.allowed', 0),
                    ),
                    true
                ); ?>
            </div>
        <?php } else { ?>
            <div>
                <?php echo translate(
                    arrayGet($translations, 'amount_text', 'general_dashboard_modal_field_document_help_text_line_2_alternate'),
                    array(
                        '[[AMOUNT]]' => arrayGet($fileupload, 'limits.amount.total', 0),
                    ),
                    true
                ); ?>
            </div>
        <?php } ?>
    </div>
    <div id="<?php echo $group; ?>--formfield--image" <?php echo addQaUniqueIdentifier("ep-docs__upload-document--iframe-wrapper");?> class="h-60"></div>
    <span class="fileinput-loader-btn" id="<?php echo $group; ?>--formfield--loader" style="display:none;">
        <img class="image" src="<?php echo __IMG_URL; ?>public/img/loader.svg" alt="loader"> Uploading...
    </span>
    <div class="row mt-10" id="<?php echo $group; ?>--formfield--image-container">
        <?php if (!empty($files)) { ?>
            <?php foreach ($files as $index => $file) { ?>
                <div id="fileupload-item-<?php echo $index; ?>" class="col-6 col-md-3 item-wrapper">
                    <div class="fileupload-item fileupload-image icon">
                        <div class="fileupload-item__image icon-files-<?php echo cleanOutput($file['extension']); ?>"></div>
                        <div class="fileupload-item__actions">
                            <input type="hidden" name="<?php echo cleanOutput($file['input']); ?>" value="<?php echo cleanOutput($file['id']); ?>">
                            <a class="btn btn-dark js-confirm-dialog confirm-dialog"
                                title="<?php echo translate("general_modal_field_document_button_delete_title"); ?>"
                                data-js-action="files:ep-docs-upload:remove-file"
                                data-group="<?php echo cleanOutput($group); ?>"
                                data-message="<?php echo translate("general_modal_field_document_button_delete_message"); ?>"
                                data-callback="removeUploadedDocument<?php echo $hash; ?>"
                                data-keep-modal="1">
                                <?php echo translate("general_modal_field_file_button_delete_text"); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<script type="text/template" id="templates--upload-file--preview">
    <div id="fileupload-item-{{index}}" class="col-6 col-md-3 item-wrapper">
        <div class="fileupload-item {{className}} icon">
            <div class="fileupload-item__image {{icon.className}}"></div>
            <div class="fileupload-item__actions">
                {{> hiddenInput}}
                {{> deleteButton}}
            </div>
        </div>
    </div>
</script>
<script type="text/template" id="templates--upload-file--hidden-input">
    <input type="hidden" name="{{name}}" value="{{value}}">
</script>
<script type="text/template" id="templates--upload-file--delete-button">
    <a title="{{title}}" class="{{className}}" data-js-action="files:ep-docs-upload:remove-file" data-group="{{group}}" data-message="{{message}}" data-callback="{{callback}}" data-keep-modal="1">
        {{text}}
    </a>
</script>

<?php echo dispatchDynamicFragmentInCompatMode(
    "files:ep-docs-upload",
    asset("public/plug/js/ep_docs/upload-files.js", "legacy"),
    sprintf(
        <<<SCRIPT
        function () {
            if (!('UploadFilesModule' in window)) {
                if (__debug_mode) {
                    console.error(new SyntaxError("'UploadFilesModule' must be defined"))
                }

                return;
            }

            UploadFilesModule.default(%s);
        }
        SCRIPT,
        json_encode($params = [
            "type"                      => ucfirst($type),
            "group"                     => $group,
            "isModal"                   => $is_modal,
            "canUpload"                 => $is_enabled,
            'scriptUrl'                 => sprintf('%s/js/upload/index.js?integration-date=%s', config('env.EP_DOCS_CDN'), config('env.EP_DOCS_INTEGRATION_DATE', '2021-10-01')),
            "filesAmount"               => arrayGet($fileupload, 'limits.amount.total', 0),
            "filesAllowed"              => arrayGet($fileupload, 'limits.amount.allowed', 0),
            "fileUploadMaxSize"         => arrayGet($fileupload, 'limits.filesize.size', 0),
            "hiddenInputName"           => $name,
            "removeHandlerName"         => "removeUploadedDocument{$hash}",
            "additionalPreviewClasses"  => $preview_classes,
            "templatesData"             => [
                "mainId"          => "#templates--upload-file--preview",
                "hiddenInputId"   => "#templates--upload-file--hidden-input",
                "deleteButtonId"  => "#templates--upload-file--delete-button",
            ]
        ])
    ),
    [$params],
    true,
); ?>


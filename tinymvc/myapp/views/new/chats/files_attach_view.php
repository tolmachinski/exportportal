<?php if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {?>
    <div class="wr-modal-flex inputs-40">
        <form id="js-modal-message-attach-inner" class="modal-flex__form validateModal">
            <div class="modal-flex__content">
                <?php widgetEpdocsFileUploader('upload-message-attachment', 'attachments[]', 'attachment', $fileupload, array(), array(), null, true, true, true, true, true); ?>
            </div>
            <div id="js-modal-message-attach-btns" class="modal-flex__btns"></div>
        </form>
    </div>
<?php } else { ?>
    <form id="js-modal-message-attach-inner" class="mnh-50">
        <div class="container-fluid-modal">
            <?php widgetEpdocsFileUploader('upload-message-attachment', 'attachments[]', 'attachment', $fileupload, array(), array(), null, true, true, true, true, true); ?>
        </div>
    </form>
<?php } ?>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "chat_app:attach-files",
        asset("public/plug/js/ep_docs/attach-files.js", "legacy"),
        sprintf(
            <<<SCRIPT
            function () {
                if (!('AttachFilesModule' in window)) {
                    if (__debug_mode) {
                        throw new SyntaxError("'AttachFilesModule' must be defined");
                    }

                    return;
                }

                AttachFilesModule.default(%s);
            }
            SCRIPT,
            json_encode([
                'saveUrl'           => $saveUrl = getUrlForGroup("/matrix_chat/ajax_operation/move-attachments"),
                'formSelector'      => $containerId = "#js-modal-message-attach-inner",
                'uploaderSelector'  => $uploaderId = "#upload-message-attachment--formfield--uploader",
            ]),
        ),
        [$containerId, $uploaderId, $saveUrl],
        true,
    );
?>

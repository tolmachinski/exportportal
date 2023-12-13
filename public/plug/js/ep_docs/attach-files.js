/* eslint-disable vars-on-top, prefer-destructuring, prefer-template, no-var */
var AttachFilesModule = (function () {
    function onAttachFilesToMessage(form, url, button) {
        var modalContent = $(".modal-content");
        var roomId = button.data("room");
        var data = form.serializeArray().map(function (file) {
            return Object.assign({ mxid: button.data("user") }, file);
        });

        showLoader(modalContent);
        postRequest(url, data)
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type);
                }
                if (response.mess_type === "success") {
                    BootstrapDialog.closeAll();

                    dispatchCustomEvent("chat-app:external:attach-files", globalThis, {
                        detail: {
                            files: response.files || [],
                            roomId: roomId,
                        },
                    });
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                hideLoader(modalContent);
            });
    }

    return {
        default(params) {
            var saveUrl = params.saveUrl || null;
            if (null === saveUrl) {
                throw new ReferenceError('The "saveUrl" must be defined.');
            }

            var uploadContainer = $(params.uploaderSelector);
            var form = $(params.formSelector);
            var modalBtn = $("#js-chat-app-attach-files-modal-dialog");
            if (!modalBtn.length) {
                throw new ReferenceError('The modal "Attach files" must be opened.');
            }

            uploadContainer.on("epd-uploader:start", function () {
                modalBtn.prop("disabled", true);
            });
            uploadContainer.on("epd-uploader:delete", function (event, button, file, files) {
                if (files.length <= 0) {
                    modalBtn.prop("disabled", true);
                }
            });
            uploadContainer.on("epd-uploader:upload", function (event, id, file, files) {
                if (files.length > 0) {
                    modalBtn.prop("disabled", false);
                }
            });
            uploadContainer.on("epd-uploader:error", function (event, error, files) {
                if (files.length > 0) {
                    modalBtn.prop("disabled", false);
                }
            });

            mix(window, { attachFilesToMessage: onAttachFilesToMessage.bind(null, form, saveUrl) }, false);
        },
    };
})();

import $ from "jquery";

import EventHub from "@src/event-hub";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SHIPPER_PAGE } from "@src/common/constants";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

async function onAttachFilesToMessage(form, url, button) {
    const modalContent = SHIPPER_PAGE ? $(".fancybox-content") : $(".modal-content");
    const roomId = button.data("room");
    const data = form.serializeArray().map(file => ({ ...file, mxid: button.data("user") }));

    try {
        showLoader(modalContent);
        const { mess_type: messageType, message, files } = await postRequest(url, data);
        if (message) {
            systemMessages(message, messageType);
        }
        if (messageType === "success") {
            EventHub.trigger("room-message:upload-files", { files, roomId });

            if (SHIPPER_PAGE) {
                closeFancyboxPopup();
            } else {
                closeAllDialogs();
            }
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(modalContent);
    }
}

export default (formSelector, uploaderSelector, saveUrl) => {
    $(() => {
        setTimeout(() => {
            const modalBtn = $("#js-chat-app-attach-files-modal-dialog");

            if (modalBtn.length) {
                const uploadContainer = $(uploaderSelector);
                const form = $(formSelector);

                if (!modalBtn.length) {
                    throw new ReferenceError('The modal "Attach files" must be opened.');
                }

                ["epd-uploader:start", "epd-uploader:delete", "epd-uploader:upload", "epd-uploader:error"].forEach(event => uploadContainer.off(event));
                uploadContainer.on("epd-uploader:start", () => {
                    modalBtn.prop("disabled", true);
                });
                uploadContainer.on("epd-uploader:delete", (event, button, file, files) => {
                    if (files.length <= 0) {
                        modalBtn.prop("disabled", true);
                    }
                });
                uploadContainer.on("epd-uploader:upload", (event, id, file, files) => {
                    if (files.length > 0) {
                        modalBtn.prop("disabled", false);
                    }
                });
                uploadContainer.on("epd-uploader:error", (event, error, files) => {
                    if (files.length > 0) {
                        modalBtn.prop("disabled", false);
                    }
                });

                EventHub.off("chat:room-attach-files");
                EventHub.on("chat:room-attach-files", (e, button) => onAttachFilesToMessage(form, saveUrl, button));
            }
        }, 2000);
    });
};

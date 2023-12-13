import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { DEBUG, SHIPPER_PAGE } from "@src/common/constants";
import { updateFancyboxPopup3 } from "@src/plugins/fancybox/v3/util";
import getScript from "@src/util/http/get-script";
import EventHub from "@src/event-hub";

import UploadHandler from "@src/common/upload/UploadHandler";

/**
 * Validation handler.
 *
 * @param {UploadHandler} handler
 *
 * @returns {boolean}
 */
function onUploadValidate(handler) {
    handler.getContainerEmitter().emit("validate", [handler.currentAmount < handler.filesAllowed, handler.currentAmount, handler.filesAllowed]);

    return handler.currentAmount < handler.filesAllowed;
}

/**
 * File upload listener.
 *
 * @param {UploadHandler} handler
 * @param {any} file
 */
function onUpload(handler, file) {
    const context = handler.addDocumentPreview(file);
    context.emitter.emit("upload", [context.id, context.file, handler.container.find(".item-wrapper").toArray()]);
    handler.hideUploadLoader();
    if (onUploadValidate(handler)) {
        handler.showUploadButton();
    }
    if (handler.isModal) {
        if (SHIPPER_PAGE) {
            updateFancyboxPopup3();
        } else {
            updateFancyboxPopup();
        }
    }
}

/**
 * Plugin start handler
 *
 * @param {UploadHandler} handler
 */
function onUploadStart(handler) {
    handler.getContainerEmitter().emit("start");
    handler.showUploadLoader();
    handler.hideUploadButton();
}

/**
 * Error handler
 *
 * @param {UploadHandler} handler
 * @param {any} error
 */
function onUploadError(handler, error) {
    handler.handleUploadError(error);
    handler.getContainerEmitter().emit("error", [error, handler.container.find(".item-wrapper").toArray()]);
    handler.hideUploadLoader();
    handler.showUploadButton();
}

/**
 * Document delete handler.
 *
 * @param {UploadHandler} handler
 * @param {JQuery} button
 */
function onDeleteDocument(handler, button) {
    if ((button.data("group") ?? null) !== handler.group) {
        return;
    }

    handler.removeDocumentPreview(button);
    handler.getContainerEmitter().emit("delete", [button, button.closest(".item-wrapper"), handler.container.find(".item-wrapper").toArray()]);
    if (onUploadValidate(handler)) {
        handler.showUploadButton();
    }
    if (handler.isModal) {
        if (SHIPPER_PAGE) {
            updateFancyboxPopup3();
        } else {
            updateFancyboxPopup();
        }
    }
}

/**
 * @param {string} type
 * @param {string} uploadButtonWrapperId
 * @param {UploadHandler} handler
 */
const attachUploader = function (type, uploadButtonWrapperId, handler) {
    globalThis.EPDocs[type](uploadButtonWrapperId, {
        start: onUploadStart.bind(null, handler),
        error: onUploadError.bind(null, handler),
        upload: onUpload.bind(null, handler),
        validate: onUploadValidate.bind(null, handler),
        maxFileSize: handler.fileUploadMaxSize,
    });
};

export default async params => {
    const { type, group, scriptUrl, canUpload, templatesData = { mainId: null, hiddenInputId: null, deleteButtonId: null } } = params;
    const uploadButtonWrapperId = `#${group}--formfield--image`;
    const uploadButtonWrapper = $(uploadButtonWrapperId);
    const loaderWrapper = $(`#${group}--formfield--loader`);
    const container = $(`#${group}--formfield--image-container`);
    const templates = {
        preview: {
            main: $(templatesData.mainId).text() || "",
            children: {
                hiddenInput: $(templatesData.hiddenInputId).text() || "",
                deleteButton: $(templatesData.deleteButtonId).text() || "",
            },
        },
    };
    const handler = new UploadHandler(container, uploadButtonWrapper, loaderWrapper, templates, params, DEBUG);

    if (canUpload) {
        // TODO: reamke to the package
        if (typeof globalThis.EPDocs !== "undefined" && globalThis.EPDocs) {
            attachUploader(type, uploadButtonWrapperId, handler);
        } else {
            showLoader(container, "");
            try {
                await getScript(scriptUrl, true);
                attachUploader(type, uploadButtonWrapperId, handler);
            } finally {
                hideLoader(container);
            }
        }
    }

    if (handler.currentAmount >= handler.filesAllowed) {
        handler.hideUploadButton();
    }

    EventHub.on("files:ep-docs-upload:remove-file", (e, button) => onDeleteDocument(handler, button));
};

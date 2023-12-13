import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import handleRequestError from "@src/util/http/handle-request-error";
import RequestError from "@src/util/http/RequestError";
import postRequest from "@src/util/http/post-request";
import loadDialog from "@src/plugins/bootstrap-dialog/index";
import getRequest from "@src/util/http/get-request";
import promisify from "@src/util/async/promisify";
import isObject from "@src/util/common/is-object";

/**
 * Formats the response into proper format.
 *
 * @param {any} response
 * @returns {any|string}
 */
const formatResponse = response => {
    try {
        return JSON.parse(response);
    } catch (e) {
        return response;
    }
};

/**
 * Filters the HTTP response.
 *
 * @param {any} response
 * @throws {RequestError} when request contains error
 * @returns {any}
 */
const filterResponse = response => {
    if (response && isObject(response)) {
        if (response.mess_type && response.mess_type !== "success") {
            throw new RequestError(response.message || null, response.mess_type, true);
        }
    }

    return response;
};

/**
 * Load content from the server.
 *
 * @param {string} url
 * @param {any} [data]
 * @returns {Promise<any|void>}
 */
const loadContent = async (url, data = null) => {
    if (data === null) {
        return getRequest(url, "text");
    }

    return postRequest(url, data, "text");
};

/**
 * Dialog shown handler.
 *
 * @param {any} dialog
 * @param {string} url
 * @param {any} data
 * @param {Function} [callback]
 *
 * @returns {Promise<{dialog: any, response?: any, error?: any, shown: Boolean }>}
 */
const onShowDialog = async (dialog, url, data, callback = null) => {
    const modal = dialog.getModal();
    const content = dialog.getModalContent();
    const messageContent = dialog.getMessage();
    modal.trigger("loading.bs.custom.modal", [dialog]);
    dialog.getModalDialog().addClass("modal-dialog-centered");
    dialog.getModalHeader().find(".close, .bootstrap-dialog-title");
    dialog.getModalBody().addClass("mnh-100");

    try {
        showLoader(content, "Loading...");
        const response = filterResponse(formatResponse(await loadContent(url, data)));
        modal.trigger("load.bs.custom.modal", [dialog]);
        messageContent.append(response.html ? response.html : response);
        if (messageContent.find("form.validateModal").length) {
            enableFormValidation(messageContent.find("form.validateModal"));
        }
        let doShow = true;
        if (callback) {
            doShow = await promisify(callback).call(null, dialog);
            if (typeof doShow === "undefined") {
                doShow = true;
            }
        }
        modal.trigger("loaded.bs.custom.modal", [dialog]);

        return { dialog, response, shown: doShow };
    } catch (error) {
        modal.trigger("error.bs.custom.modal", [dialog]);
        dialog.close();
        handleRequestError(error);

        return { dialog, error, shown: false };
    } finally {
        hideLoader(content);
    }
};

/**
 * Opens a popup using bootstrap dialog.
 *
 * @param {string} url
 * @param {any} [options={}]
 * @param {string} [title=null]
 * @param {any} [data={}]
 *
 * @returns {Promise<any>}
 */
export default async function remoteDialog(url, options = {}, title = null, data = null) {
    const BootstrapDialog = await loadDialog();
    const baseOptions = {
        title,
        tabindex: 0,
        message: $("<div></div>"),
        closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
        cssClass: "dialog-type-popup",
        closable: true,
        closeByBackdrop: false,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
    };

    return BootstrapDialog.show({
        ...baseOptions,
        ...(options ?? {}),
        onshow: async dialog => {
            const result = await onShowDialog(dialog, url, data, options?.onshow);
            const { shown: openIt } = result;
            if (openIt === false) {
                dialog.setOpened(false);
            }
        },
    });
}

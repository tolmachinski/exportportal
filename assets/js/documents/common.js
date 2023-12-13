import $ from "jquery";
import { saveAs } from "file-saver";

/* eslint-disable import/prefer-default-export */
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
/**
 * Sends envelope to the recipients.
 *
 * @param {number} envelopeId
 * @param {string|URL} url
 * @param {DataTables.JQueryDataTables} datagrid
 */
const sendPlainRequest = async (envelopeId, url, datagrid) => {
    if (envelopeId === null) {
        throw new TypeError("The argument 0 must be a number.");
    }

    try {
        const response = await postRequest(url, { envelope: envelopeId }, "json");
        const { message = null, mess_type: messageType = null } = response;
        if (message) {
            systemMessages(message, messageType);
        }
        if (messageType === "success") {
            datagrid.api().draw(true);
        }

        return response;
    } catch (e) {
        handleRequestError(e);
    }

    return null;
};

/**
 * Sends envelope to the recipients.
 *
 * @param {number} envelopeId
 * @param {number} documentId
 * @param {string|URL} url
 */
const downloadDocuments = async (envelopeId, documentId, url) => {
    try {
        /** @type {{file: ?any, mess_type: ?string, message: ?string }} */
        const response = await postRequest(url, { envelope: envelopeId, document: documentId }, "json");
        const { file = null, message = null, mess_type: messageType = null } = response;
        if (message) {
            systemMessages(message, messageType);
        }
        if (!file) {
            return;
        }

        saveAs(file.url, file.name);
    } catch (e) {
        handleRequestError(e);
    }
};

/**
 * Sends envelope to the recipients.
 *
 * @param {number} envelopeId
 * @param {number} documentId
 * @param {string|URL} url
 * @param {DataTables.JQueryDataTables} datagrid
 */
const viewDocuments = async (envelopeId, documentId, url, datagrid) => {
    try {
        /** @type {{ mess_type: ?string, message: ?string, preview: { url: string, targets: Array<string> } }} */
        const response = await postRequest(url, { envelope: envelopeId, document: documentId }, "json");
        const { message = null, mess_type: messageType = null, preview = { url: null, targets: [] } } = response;
        const { targets = [] } = preview;
        if (message) {
            systemMessages(message, messageType);
        }
        if (messageType === "success") {
            datagrid.api().draw(true);
        }
        if (!targets.length) {
            return;
        }

        targets
            .map(target => globalThis.open(target, "_blank"))
            .pop()
            .focus();
    } catch (e) {
        handleRequestError(e);
    }
};

export { viewDocuments };
export { sendPlainRequest };
export { downloadDocuments };

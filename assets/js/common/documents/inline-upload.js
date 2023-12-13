import { addCounter } from "@src/plugins/textcounter/index";
import { systemMessages } from "@src/util/system-messages/index";
import EventHub, { removeListeners } from "@src/event-hub";
import loadDialog from "@src/plugins/bootstrap-dialog/index";
import getElement from "@src/util/dom/get-element";

/**
 * Handle the uploaf file event.
 *
 * @param {JQuery.NameValuePair[]} data the list that contains form data
 * @param {any} modal                   the dialog instance
 * @param {string} fieldName            the document field name
 */
const onUploadFile = async (data, modal, fieldName) => {
    const hasDocument = data.filter(({ name }) => name === fieldName).length !== 0;
    if (!hasDocument) {
        systemMessages("The document must be uploaded first.");

        return;
    }

    // After that we need to transform the key-value pairs list to key-value data object.
    const extractedData = {};
    data.forEach(({ name, value }) => {
        extractedData[name] = value;
    });

    // After we extracted data object from key-value pairs we need to notify subscribers about uploaded files.
    // We trigger event for modal, so later we can listen them using the same modal object.
    modal.getModal().trigger("documents:inline-upload-dialog.attach", [extractedData]);
    // And now we need to close current dialog
    if (modal !== null) {
        modal.close();
    }
};

/**
 * @param {string} formSelector
 * @param {string} documentFieldName
 */
export default async (formSelector, documentFieldName = "document") => {
    const form = getElement(formSelector);
    const BootstrapDialog = await loadDialog();

    addCounter(form.find(".textcounter-document_comment"));
    removeListeners("documents:inline-upload-dialog.upload");

    EventHub.on("documents:inline-upload-dialog.upload", () =>
        onUploadFile(form.serializeArray(), BootstrapDialog.getDialog(form.closest(".bootstrap-dialog").attr("id") ?? null), documentFieldName)
    );
};

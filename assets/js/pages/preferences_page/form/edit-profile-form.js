import $ from "jquery";

import { translate } from "@src/i18n";
import { addCounter } from "@src/plugins/textcounter/index";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import EventHub, { removeListeners } from "@src/event-hub";
import { updateFancyboxPopup, closeFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import showNotificationDialog from "@src/components/dialog/notification-dialog";
import showEditSuccessDialog from "@src/components/dialog/success-dialog";
import makePhoneCodesBlock from "@src/components/phone/phone-codes-block";
import handleRequestError from "@src/util/http/handle-request-error";
import makeLocationBlock from "@src/components/location/location-block";
import showUploadDialog from "@src/components/dialog/upload-document-dialog";
import postRequest from "@src/util/http/post-request";

/**
 * Add documents to the form data.
 *
 * @param {Map<number,any>} documents
 * @param {Array<{name: string, value: any}>} data
 */
function addDocumentsToFomrData(documents, data) {
    if (documents.size === 0) {
        return;
    }

    Array.from(documents.values()).forEach((value, index) => {
        if (!value) {
            return;
        }

        Object.keys(value).forEach(key => {
            data.push({
                name: `documents[${index}][${key}]`,
                value: value[key],
            });
        });
    });
}

function onToggleLegalName() {
    const element = $("#js-user-preferences-legal-name-checkbox");
    const target = $(element.data("target") ?? null);
    if (!target.length) {
        return;
    }

    target.toggle();
    updateFancyboxPopup();
}

/**
 * Handles upload document button click event.
 *
 * @param {JQuery} button
 * @param {Map<number,any>} documents
 */
async function onUploadDocument(button, documents) {
    const { document } = button.data() ?? {};
    if (!document) {
        throw new ReferenceError("The document ID is required. Please add 'data-document' attribute to the button.");
    }

    // Open upload dialog
    const dialog = await showUploadDialog(button);
    // And listen for upload event
    dialog.getModal().on("documents:inline-upload-dialog.attach", (e, data) => {
        // Add data from upload dialog to the documents pull
        documents.set(document, { id: document, ...(data ?? {}) });
        // Hide button
        button.hide();
        // Show button siblings (label and remove button)
        button.siblings().show();
        // Add a bookmark to the remove button to remove the right one later
        button.siblings(".js-button-remove").data("document", document);
        // Remove validation border (if any)
        button.parent().removeClass("validengine-border");
    });
}

/**
 * Handles remove document button click event.
 *
 * @param {JQuery} button
 * @param {Map<number,any>} documents
 */
function onRemoveDocument(button, documents) {
    const documentId = button.data("document") ?? null;
    // If entry value empty or it is not present in the documents list then we leave
    if (documentId === null || !documents.has(documentId)) {
        return;
    }

    // Delete document from list
    documents.set(documentId, null);
    // Hide button
    button.hide();
    // Hide all siblings
    button.siblings().hide();
    // Show upload button
    button.siblings(".js-button-upload").show();
}

/**
 * Save handler.
 *
 * @param {JQuery} form
 * @param {string} url
 * @param {Map<number,any>} documents
 */
async function onSave(form, url, documents) {
    const data = form.serializeArray();
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);

    try {
        addDocumentsToFomrData(documents, data);
        const { mess_type: messageType, message, url: accountUrl, isEdit = false } = await postRequest(url, data);
        if (messageType !== "success") {
            systemMessages(message, messageType);

            return;
        }

        closeFancyboxPopup();
        if (isEdit) {
            const dialog = await showNotificationDialog({
                title: translate({ plug: "general_i18n", text: "popup_success_type_default_title" }),
                subTitle: message,
                additionalButton: accountUrl === null ? null : { text: "js_bootstrap_dialog_view_info", class: "btn-primary", location: accountUrl },
            });

            // Reload page when the dialog is hidden
            dialog.getModal().on("hidden.bs.modal", () => {
                globalThis.location.reload();
            });
        } else {
            showEditSuccessDialog({ text: message, title: translate({ plug: "general_i18n", text: "popup_success_type_default_title" }) });
        }
        EventHub.trigger("user:profile-form.added-document");
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
        submitButton.removeClass("disabled");
    }
}

/**
 * @param {JQuery} form
 */
export default function editProfileForm(form, url) {
    const documents = new Map();
    // Collect all buttons
    const uploadButtons = form.find(".js-button-upload");
    if (uploadButtons.length) {
        uploadButtons.toArray().forEach(node => {
            const button = $(node);
            const { document: documentId } = button.data() ?? {};
            const valHook = `uploadDocumentButton${documentId}`;

            documents.set(documentId, null);
            // @ts-ignore
            button.parent().setValHookType(valHook).addClass("validate[required]");
            $.valHooks[valHook] = {
                get() {
                    return documents.get(documentId);
                },
            };
        });
    }

    $("#js-user-preferences-legal-name-checkbox").on("change", onToggleLegalName);
    addCounter(form.find(".js-reason-counter"));
    makeLocationBlock(form.find(".js-location-block"));
    makePhoneCodesBlock(form.find(".js-select-phone-codes-lazy-block"));
    removeListeners("user:profile-form.remove-document", "user:profile-form.add-document", "user:profile-form.submit");

    EventHub.on("user:profile-form.remove-document", (e, button) => onRemoveDocument(button, documents));
    EventHub.on("user:profile-form.add-document", (e, button) => onUploadDocument(button, documents));
    EventHub.on("user:profile-form.submit", () => onSave(form, url, documents));
}

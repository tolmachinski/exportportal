import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";
import showNotificationDialog from "@src/components/dialog/notification-dialog";
import showConfirmationDialog from "@src/components/dialog/confirmation-dialog";
import createEditor from "@src/components/editor/tinymvc-editor";
import EventHub, { removeListeners } from "@src/event-hub";
import getElement from "@src/util/dom/get-element";
import initTinymceValidator from "@src/components/editor/tinymce-validator";

// Constants
import { SUBDOMAIN_URL } from "@src/common/constants";

// Styles
import "@scss/user_pages/company_page/index.scss";

/**
 * Send a request to use existing information to fill profile.
 *
 * @param {JQuery} button
 */
const useExistingInformation = async button => {
    const body = $("body");
    const accountId = button.data("account");

    showLoader(body);
    closeAllDialogs();

    try {
        const { message, mess_type: messageType } = await postRequest(`${SUBDOMAIN_URL}company/ajax_company_operation/use_existing_info`, {
            account: accountId,
        });
        if (messageType === "success") {
            globalThis.location.reload();
        } else {
            hideLoader(body);
            systemMessages(message, messageType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(body);
    }
};

/**
 * Open confirnation dialog.
 *
 * @param {JQuery} button
 */
const importAccountInformation = async button => {
    const { isConfirmed } = await showConfirmationDialog(button);
    if (isConfirmed) {
        useExistingInformation(button);
    }
};

/**
 * Saves the text from editor.
 *
 * @param {any} editor
 * @returns {Promise<void>}
 */
const saveEditorText = async editor => {
    return new Promise(resolve => {
        const handler = () => {
            editor.off("SaveContent", handler);
            resolve();
        };

        editor.on("SaveContent", handler);
        editor.save();
    });
};

/**
 * Save company partial information.
 *
 * @param {JQuery} form
 * @param {any} editor
 */
const savePartialInformation = async (form, editor) => {
    showLoader(form, "default", "fixed");

    try {
        await saveEditorText(editor);
        const { url = null, message, mess_type: messageType } = await postRequest(
            `${SUBDOMAIN_URL}company/ajax_company_operation/save-additional`,
            form.serializeArray()
        );
        if (messageType === "success") {
            showNotificationDialog({
                title: "Success!",
                subTitle: message,
                additionalButton: url === null ? null : { text: "js_bootstrap_dialog_view_company", class: "btn-primary", location: url },
            });

            const companyIndexNameInput = $(".js-company-index-name-input");
            const companyIndexNameInputVal = companyIndexNameInput.val();
            if (companyIndexNameInputVal) {
                const companyBaseUrl = $(".js-company-link-base-url");
                companyBaseUrl.text(`${companyBaseUrl.text().trim()}${companyIndexNameInputVal}`);
                companyIndexNameInput.remove();
            }
        } else {
            systemMessages(message, messageType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
    }
};

/**
 * Handles successfull save action.
 *
 * @param {JQuery} wrapper
 */
const onSuccessfullSave = wrapper => {
    const buttons = wrapper.find(".js-edit-request-button");

    // Hide all buttons
    buttons.hide();
    // Show notification button
    buttons.filter(".js-notify").show();
};

$(async () => {
    const wrapper = $("#company-edit--page--wrapper");
    const form = getElement(wrapper.data("form"));
    const [editor] = await createEditor(form.data("editor"), {
        // eslint-disable-next-line camelcase
        init_instance_callback: initTinymceValidator.bind(this, { valHook: "editorCompanyDescription" }),
    });

    $.valHooks.editorCompanyDescription = {
        get() {
            return editor.getContent({ format: "text" }) || "";
        },
    };

    removeListeners("company:addendum-form.submit", "company:existing-accounts.choose", "company:edit-form.added-document");
    enableFormValidation(form);

    EventHub.on("company:addendum-form.submit", () => savePartialInformation(form, editor));
    EventHub.on("company:edit-form.added-document", () => onSuccessfullSave(wrapper));
    EventHub.on("company:existing-accounts.choose", (e, button) => importAccountInformation(button));
});

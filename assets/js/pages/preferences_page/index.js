import $ from "jquery";

import { addCounter } from "@src/plugins/textcounter/index";
import { systemMessages } from "@src/util/system-messages/index";
import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import accountSource from "@src/components/pages/profile/account-source";
import handleRequestError from "@src/util/http/handle-request-error";
import showNotificationDialog from "@src/components/dialog/notification-dialog";
import showConfirmationDialog from "@src/components/dialog/confirmation-dialog";
import EventHub, { removeListeners } from "@src/event-hub";
import getElement from "@src/util/dom/get-element";

// Constants
import { SUBDOMAIN_URL } from "@src/common/constants";

// Styles
import "@scss/user_pages/preferences_page/index.scss";

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
        const { message, mess_type: messageType } = await postRequest(`${SUBDOMAIN_URL}profile/ajax_operations/use-existing`, {
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
 * Save user partial information.
 *
 * @param {JQuery} form
 *
 * @returns {Promise<boolean>}
 */
const savePartialInformation = async form => {
    showLoader(form, "default", "fixed");

    try {
        const { url = null, message, mess_type: messageType } = await postRequest(
            `${SUBDOMAIN_URL}profile/ajax_operations/save-additional`,
            form.serializeArray()
        );
        if (messageType === "success") {
            showNotificationDialog({
                title: "Success!",
                subTitle: message,
                additionalButton: url === null ? null : { text: "js_bootstrap_dialog_view_info", class: "btn-primary", location: url },
            });

            return true;
        }

        systemMessages(message, messageType);
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
    }

    return false;
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

$(() => {
    const wrapper = $("#user-preferences--page--wrapper");
    const form = getElement(wrapper.data("form"));
    // The block with account source type CAN be on the page, but only
    // in special conditions.
    let sourceInfo = null;
    try {
        sourceInfo = getElement(wrapper.data("sourceInfo"));
    } catch (error) {
        // Skip - the element is not found on the page.
    }

    addCounter(".textcounter");
    removeListeners("user:profile-addendum-form.submit", "user:existing-accounts.choose", "user:profile-form.added-document");
    // If element with source type exists, then we need
    // to process this information
    if (sourceInfo) {
        accountSource(sourceInfo);
    }

    EventHub.on("user:existing-accounts.choose", (e, button) => importAccountInformation(button));
    EventHub.on("user:profile-form.added-document", () => onSuccessfullSave(wrapper));
    EventHub.on("user:profile-addendum-form.submit", async () => {
        // If the information was successfully saved, then we need to remove the
        // account source info element.
        if ((await savePartialInformation(form)) && sourceInfo) {
            // Remove element from DOM
            sourceInfo.remove();
            // Void the element pointer
            sourceInfo = null;
        }
    });
});

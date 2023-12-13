import $ from "jquery";

import loadBootstrapDialog, { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { showSuccessSubscribtionPopup, showConfirmSubscribtionPopup } from "@src/components/popups/subscribe/index";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import EventHub from "@src/event-hub";

const subscribeUser = form => {
    const $form = $(form);
    const $btnSubmit = $form.find("button[type=submit]");

    $btnSubmit.addClass("disabled");
    showLoader($form);

    postRequest(`${SUBDOMAIN_URL}subscribe/ajax_subscribe_operation/subscribe`, $form.serialize())
        .then(async response => {
            if (response.mess_type === "success") {
                sentPopupViewed("subscribe");
                await loadBootstrapDialog();
                closeAllDialogs();
                showSuccessSubscribtionPopup(response.message);
                globalThis.closeDialog = "subscribe";
            } else if (response.mess_type === "info") {
                await loadBootstrapDialog();
                closeAllDialogs();
                showConfirmSubscribtionPopup(response);
                globalThis.closeDialog = "subscribe";
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader($form);
            $btnSubmit.removeClass("disabled");
        });
};

export default () => {
    enableFormValidation($("#js-subscribe-benefits-form"));
    EventHub.off("popup:subscribe-form-submit");
    EventHub.on("popup:subscribe-form-submit", (e, form) => subscribeUser(form));
};

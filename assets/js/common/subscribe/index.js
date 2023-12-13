import $ from "jquery";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import loadBootstrapDialog, { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { showSuccessSubscribtionPopup, showConfirmSubscribtionPopup } from "@src/components/popups/subscribe/index";
import { SUBDOMAIN_URL } from "@src/common/constants";

// eslint-disable-next-line import/prefer-default-export
export const subscribeFormCallBack = function (e, form) {
    const $form = $(form);
    // eslint-disable-next-line no-underscore-dangle
    const url = `${SUBDOMAIN_URL}subscribe/ajax_subscribe_operation/subscribe`;
    const fdata = $form.serialize();

    $form.find("button[type=submit]").addClass("disabled");
    showLoader($form);

    postRequest(url, fdata)
        .then(async response => {
            const { mess_type: messType, message } = response;
            if (messType === "error") {
                systemMessages(message, messType);

                return;
            }

            await loadBootstrapDialog();
            closeAllDialogs();

            setTimeout(() => {
                $form[0].reset();
            }, 100);

            if (messType === "success") {
                showSuccessSubscribtionPopup(message);
            } else if (messType === "info") {
                showConfirmSubscribtionPopup(response);
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader($form);
            $form.find("button[type=submit]").removeClass("disabled");
        });
};

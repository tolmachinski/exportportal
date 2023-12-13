import $ from "jquery";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
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
        .then(async ({ mess_type: messType, message }) => {
            if (messType === "error") {
                systemMessages(message, messType);

                return;
            }

            const { openDialogModal } = await import("@src/epl/common/popups/types/modal-dialog");
            $form[0].reset();
            openDialogModal({
                category: messType,
                subTitle: message,
                closable: true,
                buttons: [
                    {
                        label: "Ok",
                        cssClass: "btn btn-outline-primary",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader($form);
            $form.find("button[type=submit]").removeClass("disabled");
        });
};

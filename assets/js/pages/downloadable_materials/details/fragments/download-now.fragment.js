import $ from "jquery";

import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { googleRecaptchaValidation, googleRecaptchaLoading } from "@src/common/recaptcha/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { SITE_URL } from "@src/common/constants";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const userRegisterSubmit = async (e, form) => {
    const element = $(form);
    const container = element.closest(".js-modal-flex");

    element.find("button[type=submit]").addClass("disabled");
    showLoader(container);
    await googleRecaptchaLoading();
    await googleRecaptchaValidation(form);

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}downloadable_materials/ajaxFormAdministration/create`, element.serialize());

        if (messageType === "success") {
            await loadBootstrapDialog();
            openResultModal({
                title: "Success!",
                subTitle: message,
                type: "success",
                closable: true,
                classes: "tac",
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light mnw-130",
                        action: dialog => dialog.close(),
                    },
                ],
            });

            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        element.find("button[type=submit]").removeClass("disabled");
        hideLoader(container);
    }
};

export default () => {
    EventHub.off("user_register:submit");
    EventHub.on("user_register:submit", (e, form) => userRegisterSubmit(e, form));
};

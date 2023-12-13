import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { addCounter } from "@src/plugins/textcounter/index";
import { SITE_URL } from "@src/common/constants";

import EventHub from "@src/event-hub";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const friendInviteFormSubmit = async (e, form) => {
    const formWrapper = form.closest(".js-modal-flex");

    try {
        showLoader(formWrapper, "Sending email...");
        form.find("button[type=submit]").addClass("disabled");
        const { message, mess_type: messType } = await postRequest(`${SITE_URL}company/ajax_send_email/invite_external_customers`, form.serialize(), "JSON");

        systemMessages(message, messType);

        if (messType === "success") {
            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(formWrapper);
        form.find("button[type=submit]").removeClass("disabled");
    }
};

export default () => {
    addCounter($(".js-textcounter-email-message"));

    EventHub.off("friend-invite-popup:submit");
    EventHub.on("friend-invite-popup:submit", friendInviteFormSubmit);
};

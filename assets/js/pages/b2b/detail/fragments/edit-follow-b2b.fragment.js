import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { addCounter } from "@src/plugins/textcounter/index";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

/**
 * Send handler.
 *
 * @param {JQuery} form
 */
const onSend = async form => {
    const submitButton = form.find("button[type=submit]");

    submitButton.addClass("disabled");
    showLoader(form);

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}follow/ajax_operation/edit_follow_b2b_request`, form.serializeArray());

        systemMessages(message, messageType);

        if (messageType === "success") {
            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
        submitButton.removeClass("disabled");
    }
};

export default () => {
    addCounter($(".js-textcounter-message"));

    EventHub.off("b2b:edit-follow-form.submit");
    EventHub.on("b2b:edit-follow-form.submit", (_e, form) => onSend(form));
};

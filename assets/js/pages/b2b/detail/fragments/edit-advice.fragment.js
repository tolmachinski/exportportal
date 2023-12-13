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
    const wrModal = $(".js-modal-flex");

    submitButton.addClass("disabled");
    showLoader(wrModal);

    try {
        const { mess_type: messageType, message, text, id_advice: idAdvice } = await postRequest(
            `${SITE_URL}b2b/ajax_b2b_operation/edit_advice`,
            form.serialize()
        );

        systemMessages(message, messageType);

        if (messageType === "success") {
            $(`#js-advice-${idAdvice}`).find(".js-advice-text").html(text);
            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(wrModal);
        submitButton.removeClass("disabled");
    }
};

export default () => {
    addCounter($(".js-textcounter-message"));

    EventHub.off("b2b:edit-advice-form.submit");
    EventHub.on("b2b:edit-advice-form.submit", (_e, form) => onSend(form));
};

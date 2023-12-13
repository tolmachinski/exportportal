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
 * @param {number} idRequest
 */
const onSend = async (form, idRequest) => {
    const submitButton = form.find("button[type=submit]");
    const wrModal = $(".js-wr-modal");

    submitButton.addClass("disabled");
    showLoader(wrModal);

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}follow/ajax_operation/follow_b2b`, form.serializeArray());

        systemMessages(message, messageType);

        if (messageType === "success") {
            closeFancyBox();

            $(`#js-follow-b2b-${idRequest}`)
                .removeClass("fancybox.ajax fancyboxValidateModal")
                .addClass("call-action")
                .attr({ "data-request": idRequest, "data-js-action": "b2b-requests:unfollow" })
                .data("title", "Unfollow this")
                .html('<i class="ep-icon ep-icon_unfollow"></i> Unfollow B2B request');
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(wrModal);
        submitButton.removeClass("disabled");
    }
};

export default idRequest => {
    addCounter($(".js-textcounter-message"));

    EventHub.off("b2b:follow-form.submit");
    EventHub.on("b2b:follow-form.submit", (_e, form) => onSend(form, idRequest));
};

import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { addCounter } from "@src/plugins/textcounter/index";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";
import lazyLoadingInstance from "@src/plugins/lazy/index";

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
        const { mess_type: messageType, message, advice } = await postRequest(`${SITE_URL}b2b/ajax_b2b_operation/add_advice`, form.serializeArray());

        systemMessages(message, messageType);

        if (messageType === "success") {
            const advicesList = $("#js-b2b-request-advices-wrapper");
            const emptyBlock = $("#js-b2b-advices-empty-block");
            const advicesCounter = $("#js-b2b-advices-counter");

            if (emptyBlock.length) {
                emptyBlock.remove();
            }

            advicesList.append(advice);
            $("#js-btn-add-advice").remove();
            advicesCounter.text(parseInt(advicesCounter.text(), 10) + 1);

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

    EventHub.off("b2b:add-advice-form.submit");
    EventHub.on("b2b:add-advice-form.submit", async (_e, form) => {
        await onSend(form);
        lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
    });
};

import $ from "jquery";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const onSaveContent = async (formElement, sendUrl) => {
    const form = $(formElement);
    const wrapper = form.closest(".js-modal-flex");
    const submitButton = form.find("button[type=submit]");
    const formData = form.serializeArray();

    try {
        submitButton.addClass("disabled");
        showLoader(wrapper);
        const { message = "", mess_type: messageType = "error" } = await postRequest(sendUrl, formData);

        systemMessages(message, messageType);
        if (messageType === "success") {
            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitButton.removeClass("disabled");
        hideLoader(wrapper);
    }
};

export default sendUrl => {
    EventHub.off("registration:resend-confirm-email");
    EventHub.on("registration:resend-confirm-email", (e, form) => onSaveContent(form, sendUrl));
};

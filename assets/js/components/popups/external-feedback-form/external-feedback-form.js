import $ from "jquery";

import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { addCounter } from "@src/plugins/textcounter/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const onSave = async (url, e, form) => {
    const formWrapper = form.closest(".js-modal-flex");

    try {
        showLoader(formWrapper);
        form.find("button[type=submit]").addClass("disabled");
        const { message, mess_type: messType } = await postRequest(url, form.serialize());

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

export default saveUrl => {
    addCounter($(".textcounter_email-message"));
    EventHub.off("navbar:external-feedback");
    EventHub.on("navbar:external-feedback", onSave.bind(null, saveUrl));
};

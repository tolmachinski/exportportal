import $ from "jquery";
import { addCounter } from "@src/plugins/textcounter/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import { closeBootstrapDialog } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

/**
 * Add comment handler
 * @param {JQuery} form - the form element
 * @param {boolean} isDialog - true if the form is in a dialog, false if it's in a fancybox
 */
const onSubmitReportForm = async (form, isDialog) => {
    const wrform = form.closest(".js-modal-flex");
    const submitBtn = form.find("button[type=submit]");
    const url = form.attr("action");

    showLoader(wrform);
    submitBtn.addClass("disabled");

    try {
        const { mess_type: messageType, message } = await postRequest(url, form.serializeArray());
        systemMessages(message, messageType);

        if (messageType === "success") {
            if (isDialog) {
                closeBootstrapDialog(form);
            } else {
                closeFancyBox();
            }

            systemMessages(translate({ plug: "general_i18n", text: "system_message_changes_will_come_soon" }), "info");
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitBtn.removeClass("disabled");
        hideLoader(wrform);
    }
};

export default isDialog => {
    addCounter($(".js-limited-comment"));

    EventHub.off("items:add-comment-form.submit");
    EventHub.on("items:add-comment-form.submit", (_e, form) => {
        onSubmitReportForm(form, isDialog);
    });
};

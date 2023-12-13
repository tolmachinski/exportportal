import $ from "jquery";

import { SUBDOMAIN_URL } from "@src/common/constants";
import { addCounter } from "@src/plugins/textcounter/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

/**
 * Add report submit handler
 * @param {JQuery} form - the form element
 * @param {string} loaderMessage - the message that will be displayed in the loader.
 */
const onSubmitReportForm = async (form, loaderMessage) => {
    const wrform = form.closest(".js-modal-flex");
    const submitBtn = form.find("button[type=submit]");

    showLoader(wrform, loaderMessage);
    submitBtn.addClass("disabled");

    try {
        const { mess_type: messageType, message } = await postRequest(`${SUBDOMAIN_URL}complains/ajax_complains_operations/add_complain`, form.serialize());
        systemMessages(message, messageType);

        if (messageType === "success") {
            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitBtn.removeClass("disabled");
        hideLoader(wrform);
    }
};

export default loaderMessage => {
    const otherText = $("#js-div-text-theme");
    addCounter($(".js-textcounter-message"));

    $("#js-select-complain-theme").on("change", function onChange() {
        if (Number($(this).val()) !== 0) {
            otherText.hide();
        } else {
            otherText.show();
            // @ts-ignore
            $.fancybox.update();
        }
    });

    EventHub.off("complains:report-form.submit");
    EventHub.on("complains:report-form.submit", (_e, form) => {
        onSubmitReportForm(form, loaderMessage);
    });
};

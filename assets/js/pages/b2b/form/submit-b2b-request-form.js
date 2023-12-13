import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import { systemMessages } from "@src/util/system-messages/index";

const submitB2bRequestForm = async form => {
    const data = form.serializeArray();
    const requestType = form.data("requestType");
    let url = SITE_URL;
    const submitButton = form.find("button[type=submit]");
    const wrForm = $("#js-b2b-request-form-wr");

    if (requestType === "edit" || requestType === "register") {
        url = `${SITE_URL}b2b/ajax_b2b_operation/${requestType}`;
    } else {
        throw new TypeError("Invalid request type");
    }

    submitButton.addClass("disabled");
    showLoader(wrForm);

    try {
        const { mess_type: messageType, message } = await postRequest(url, data);

        systemMessages(message, messageType);

        if (messageType === "success") {
            $("html, body").animate(
                {
                    scrollTop: $("body").offset().top,
                },
                1000,
                function removeFormFromDom() {
                    form.remove();
                }
            );

            $("#js-dashboard-heading").after(
                `<div class="success-alert-b mt-15 mb-15"><i class="ep-icon ep-icon_ok-circle"></i> ${message} <a class="ml-5 txt-blue2" href="${SITE_URL}b2b/all">View B2B</a></div>`
            );
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        setTimeout(() => {
            hideLoader(wrForm);
        }, 1000);
        submitButton.removeClass("disabled");
    }
};

export default submitB2bRequestForm;

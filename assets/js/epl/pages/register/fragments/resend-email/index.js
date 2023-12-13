import $ from "jquery";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { EMAIL } from "@src/plugins/jquery-validation/rules";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import initJqueryValidation from "@src/plugins/jquery-validation/lazy";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const resendEmail = async formSelector => {
    const form = $(formSelector);
    const submitButton = form.find("button[type=submit]");

    try {
        submitButton.prop("disabled", true);
        showLoader(form);
        const { message = "", mess_type: messageType = "error" } = await postRequest(
            `${SUBDOMAIN_URL}register/ajax_operations/resend_confirmation_email`,
            form.serializeArray()
        );

        systemMessages(message, messageType);
        if (messageType === "success") {
            closeFancyboxPopup();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitButton.prop("disabled", false);
        hideLoader(form);
    }
};

const initFormValidation = () => {
    const formSelector = "#js-resend-email-form";
    const validationOptions = {
        rules: {
            // eslint-disable-next-line camelcase
            email: EMAIL,
        },
    };

    initJqueryValidation(formSelector, resendEmail.bind(resendEmail, formSelector), validationOptions);
};

export default () => {
    initFormValidation();
};

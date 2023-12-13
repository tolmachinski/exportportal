import $ from "jquery";
import initJqueryValidation from "@src/plugins/jquery-validation/lazy";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { googleRecaptchaValidation, googleRecaptchaLoading } from "@src/common/recaptcha/index";
import { EMAIL } from "@src/plugins/jquery-validation/rules";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const formSubmit = async form => {
    showLoader(form);

    postRequest(`${SUBDOMAIN_URL}authenticate/ajax_forgot`, form.serialize())
        .then(async resp => {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                form.trigger("reset");
                $(".js-first-step-block").hide();
                $(".js-second-step-block").show();
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader(form);
            form.find("button[type=submit]").prop("disabled", false);
        });

    return false;
};

const restorePassword = async formSelector => {
    const form = $(formSelector);
    const submitButton = form.find("button[type=submit]");

    submitButton.prop("disabled", true);
    await googleRecaptchaLoading();
    googleRecaptchaValidation(formSelector)
        .then(() => {
            formSubmit(form);
        })
        .catch(() => {
            submitButton.prop("disabled", false);
        });
};

const validateForm = () => {
    const validationOptions = {
        rules: {
            // eslint-disable-next-line camelcase
            user_email: EMAIL,
        },
    };

    const formSelector = "#js-epl-forgot-form";
    initJqueryValidation(formSelector, restorePassword.bind(null, formSelector), validationOptions);
};

export default () => {
    validateForm();

    EventHub.on("epl-forgot:return-to-forgot-form", () => {
        $(".js-second-step-block").hide();
        $(".js-first-step-block").show();
    });
};

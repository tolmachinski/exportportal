import $ from "jquery";
import initJqueryValidation from "@src/plugins/jquery-validation/lazy";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { PASSWORD, PASSWORD_CONFIRM } from "@src/plugins/jquery-validation/rules";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const resetPassword = () => {
    const form = $("#js-epl-reset-form");
    const submitButton = form.find("button[type=submit]");

    showLoader(form);
    submitButton.prop("disabled", true);

    postRequest(`${SUBDOMAIN_URL}authenticate/reset_ajax`, form.serialize())
        .then(async resp => {
            if (resp.mess_type === "success") {
                $("html, body").animate(
                    {
                        scrollTop: $("body").offset().top,
                    },
                    1000
                );

                $(".js-epl-forgot-content").html($("#js-epl-password-changed-content").html());
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader(form);
            submitButton.prop("disabled", false);
        });

    return false;
};

const validationOptions = {
    rules: {
        pwd: PASSWORD,
        // eslint-disable-next-line camelcase
        pwd_confirm: PASSWORD_CONFIRM,
    },
};

export default () => {
    initJqueryValidation("#js-epl-reset-form", resetPassword, validationOptions);
};

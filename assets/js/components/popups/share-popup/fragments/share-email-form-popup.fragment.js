import $ from "jquery";

import { SUBDOMAIN_URL } from "@src/common/constants";
import { addCounter } from "@src/plugins/textcounter/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const onSave = form => {
    const $form = $(form);
    const $wrform = $form.closest(".js-wr-modal");
    const action = $form.data("action");
    const fdata = $form.serialize();

    showLoader($wrform, "Sending email...");
    $form.find("button[type=submit]").addClass("disabled");

    return postRequest(`${SUBDOMAIN_URL}${action}`, fdata)
        .then(async response => {
            hideLoader($wrform);
            systemMessages(response.message, response.mess_type);

            if (response.mess_type === "success") {
                closeFancyBox();
            } else {
                $form.find("button[type=submit]").removeClass("disabled");
            }
        })
        .catch(handleRequestError);
};

export default () => {
    addCounter($(".js-textcounter-message"));

    EventHub.off("global:share-email-form-popup-submit");
    EventHub.on("global:share-email-form-popup-submit", (e, form) => {
        onSave(form);
    });
};

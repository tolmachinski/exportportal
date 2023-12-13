import $ from "jquery";

import { addCounter } from "@src/plugins/textcounter/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import loadBootstrapDialog, { openEmailSuccessDialog } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const onSave = (url, form) => {
    const $form = $(form);
    const $wrform = $form.closest(".js-modal-flex");
    const fdata = $form.serialize();

    showLoader($wrform, "Sending email...");
    $form.find("button[type=submit]").addClass("disabled");

    return postRequest(url, fdata)
        .then(async response => {
            systemMessages(response.message, response.mess_type);

            if (response.mess_type === "success") {
                let template = $("#js-template-email-invite-success").text();
                template = template.replace(new RegExp("{{email}}", "g"), response.email);

                await loadBootstrapDialog();
                openEmailSuccessDialog("Friend invite", template, [
                    {
                        label: translate({ plug: "general_i18n", text: "form_button_done_text" }),
                        cssClass: "btn btn-dark mnw-130",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ]);
                closeFancyBox();
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            $form.find("button[type=submit]").removeClass("disabled");
            hideLoader($wrform);
        });
};

export default saveUrl => {
    addCounter($(".js-textcounter-message"));

    EventHub.off("navbar:friend-invited");
    EventHub.on("navbar:friend-invited", (e, form) => {
        onSave(saveUrl, form);
    });
};

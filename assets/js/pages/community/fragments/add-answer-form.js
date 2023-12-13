import $ from "jquery";

import { addCounter } from "@src/plugins/textcounter/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const onSave = (url, e, form) => {
    e.preventDefault();
    e.stopImmediatePropagation();
    const $form = $(form);
    const $wrform = $form.closest(".js-modal-flex");
    const fdata = $form.serialize();

    $form.find("button[type=submit]").addClass("disabled");
    showLoader($wrform);

    return postRequest(url, fdata)
        .then(async response => {
            if (response.mess_type === "success") {
                closeFancyBox();

                await loadBootstrapDialog();
                openResultModal({
                    title: "Success!",
                    subTitle: response.message,
                    content: response.responseContent,
                    type: "success",
                    closable: true,
                    closeByBg: true,
                    buttons: [
                        {
                            label: translate({ plug: "BootstrapDialog", text: "close" }),
                            cssClass: "btn btn-light",
                            action(dialog) {
                                dialog.close();
                            },
                        },
                    ],
                });
            } else {
                hideLoader($wrform);
                $form.find("button[type=submit]").removeClass("disabled");
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError);
};

export default saveUrl => {
    addCounter($(".js-text-counter"));
    EventHub.on("community:add_answer", onSave.bind(null, saveUrl));
};

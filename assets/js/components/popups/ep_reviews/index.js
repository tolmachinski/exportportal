import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { addCounter } from "@src/plugins/textcounter/index";
import { translate } from "@src/i18n";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const writeReviewSubmit = async (e, form) => {
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);

    try {
        const { mess_type: messType, message } = await postRequest(`${SITE_URL}ep_reviews/ajax_operations/add_review`, form.serialize(), "JSON");
        if (messType === "success") {
            await loadBootstrapDialog();
            closeFancyBox();
            openResultModal({
                title: "Success!",
                subTitle: message,
                type: "success",
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        } else {
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitButton.removeClass("disabled");
        hideLoader(form);
    }
};

export default () => {
    EventHub.off("write-review:submit");
    EventHub.on("write-review:submit", writeReviewSubmit);

    const reviewTextarea = $(".js-write-review-textarea");
    addCounter(reviewTextarea);
    reviewTextarea.on("keyup", function onKeyupTextarea() {
        const btn = $(this).closest("form").find("button[type=submit]");
        if ($(this).val().toString().length) {
            btn.removeAttr("disabled");
        } else {
            btn.attr("disabled", "disabled");
        }
    });
};

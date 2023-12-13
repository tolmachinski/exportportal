import $ from "jquery";
import loadBootstrapDialog, { closeAllDialogs, openHeaderImageModal, openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";
import callGAEvent from "@src/common/google-analytics/index";
import EventHub from "@src/event-hub";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

import "@scss/user_pages/schedule_demo_popup/index.scss";

let isSubmitedForm = false;

const webinarRequestFormCallBack = function (e, formSelector) {
    const form = $(formSelector);

    form.find("button[type=submit]").addClass("disabled");
    showLoader(form);

    postRequest(`${SUBDOMAIN_URL}webinar_requests/ajax_operations/requesting_a_demo`, form.serialize())
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();

                form.trigger("reset");
                isSubmitedForm = true;
                closeAllDialogs();

                openResultModal({
                    subTitle: response.message,
                    type: "success",
                    closable: true,
                    closeByBg: true,
                    buttons: [
                        {
                            label: translate({
                                plug: "BootstrapDialog",
                                text: "ok",
                            }),
                            cssClass: "btn btn-light",
                            action(dialog) {
                                dialog.close();
                            },
                        },
                    ],
                });
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader(form);
            form.find("button[type=submit]").removeClass("disabled");
        });
};

const openScheduleDemoPopup = btn => {
    let url = `${SUBDOMAIN_URL}${btn.data("href")}`;

    if (btn.data("hash")) {
        url = `${SUBDOMAIN_URL}${btn.data("href")}?request=${btn.data("hash")}`;
    }

    openHeaderImageModal({
        title: translate({ plug: "general_i18n", text: "js_schedule_a_demo_popup_title" }),
        titleUppercase: true,
        subTitle: translate({ plug: "general_i18n", text: "js_schedule_a_demo_popup_subtitle" }),
        titleImage: btn.data("popupBg"),
        isAjax: true,
        content: url,
        classes: "schedule-demo-popup",
        validate: true,
        closeCallBack: () => {
            if (!isSubmitedForm) {
                callGAEvent("schedule_demo_popup_close_not_submit", "webinar-requests");
            }
        },
    });

    callGAEvent("schedule_demo_popup_open", "webinar-requests");

    EventHub.off("webinar-request:form-submit");
    EventHub.on("webinar-request:form-submit", (e, form) => webinarRequestFormCallBack(e, form));
};

export default openScheduleDemoPopup;

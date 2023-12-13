import $ from "jquery";
import { SUBDOMAIN_URL } from "@src/common/constants";

import { enableFormValidation } from "@src/plugins/validation-engine/index";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages/index";
import { addCounter } from "@src/plugins/textcounter/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { translate } from "@src/i18n";
import { removePopupBanner, resizePopupBanner } from "@src/components/popups_system/popup_util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import EventHub from "@src/event-hub";

const removeBanner = $this => {
    removePopupBanner($this);
};

const closeBanner = btn => {
    removeBanner(btn);

    if (btn.data("type") === "feedback_certification") {
        sentPopupViewed("feedback_certification", "cancel");
    }
};

const onSubmit = async form => {
    showLoader(form);
    const popupHash = form.data("type");

    try {
        const { mess_type: messType, message } = await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/save/${popupHash}`, form.serialize());
        if (messType === "success") {
            if (popupHash === "feedback_certification") {
                sentPopupViewed("feedback_certification");
            }
            removeBanner(form);
            await loadBootstrapDialog();
            openResultModal({
                subTitle: translate({ plug: "general_i18n", text: "popup_feedback_subtitle" }),
                type: "success",
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light",
                        action: dialog => dialog.close(),
                    },
                ],
            });
        } else {
            systemMessages(message, messType);
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader(form);
    }
};

const onLeaveFeedback = btn => {
    btn.hide().closest(".js-widget-feedback").removeClass("pseudo-modal--hide-content").find(".js-widget-hidden").removeClass("pseudo-modal__hidden");

    enableFormValidation($(".validateModalLeaveFeedback"), {
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        focusFirstField: false,
        scroll: false,
        showArrow: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete(form, status) {
            if (status) {
                onSubmit(form);
            } else {
                systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });
};

let counterState = false;
const onClickFeedbackRate = async function () {
    const $this = $(this);
    const rating = Number($this.val());
    const feedbackDescription = $(".js-widget-leave-feedback-description");

    if (rating === 3) {
        if (!counterState) {
            await addCounter(feedbackDescription.find("textarea"));
            counterState = true;
        }

        feedbackDescription.removeClass("display-n");
    } else {
        feedbackDescription.addClass("display-n");
    }

    resizePopupBanner();
};

export default () => {
    $(".js-widget-leave-feedback-rate").on("click", onClickFeedbackRate);
    EventHub.off("popup-feedback:leave-feedback");
    EventHub.on("popup-feedback:leave-feedback", (e, btn) => onLeaveFeedback(btn));
    EventHub.off("popup-feedback:close");
    EventHub.on("popup-feedback:close", (e, btn) => closeBanner(btn));
};

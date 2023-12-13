import $ from "jquery";
import EventHub from "@src/event-hub";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import { closeFancyBox, closeFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { open } from "@src/plugins/fancybox/v2/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { addCounter } from "@src/plugins/textcounter/index";
import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { removePopupBanner } from "@src/components/popups_system/popup_util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const removeOpinionPopup = $this => {
    removePopupBanner($this);
};

const closeAboutPopup = $this => {
    sentPopupViewed("hash_about", "cancel");
    removeOpinionPopup($this);
};

const fancyboxAboutOpen = $this => {
    removeOpinionPopup($this);

    open(
        { content: $($this.data("href")).html() },
        {
            width: "70%",
            height: "auto",
            maxWidth: $this.data("mw"),
            beforeShow: () => {
                addCounter(".js-textcounter");
                enableFormValidation($(".js-popup-about-review-form"));
            },
            afterClose() {
                $(".fancybox-lock").removeClass("fancybox-lock");
                $(".fancybox-margin").removeClass("fancybox-margin");
            },
        }
    );
};

const submitReviewForm = async $form => {
    const $submitButton = $form.find("button[type=submit]");

    showLoader($form);
    $submitButton.prop("disabled", true);

    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/save/hash_about`, $form.serialize())
        .then(response => {
            if (response.message && (!response.mess_type || response.mess_type !== "success")) {
                systemMessages(response.message, response.mess_type || null);
            } else {
                closeFancyBox();
                sentPopupViewed("hash_about");
                systemMessages(translate({ plug: "general_i18n", text: "js_about_feedback_success_message" }), "success");
            }

            hideLoader($form);
        })
        .catch(handleRequestError)
        .finally(() => {
            hideLoader($form);
            $submitButton.prop("disabled", false);
        });
};

export default () => {
    EventHub.off("popups:close-hash-about-popup");
    EventHub.on("popups:close-hash-about-popup", (e, button) => closeAboutPopup(button));
    EventHub.off("popups:fancybox-hash-about-open");
    EventHub.on("popups:fancybox-hash-about-open", (e, button) => fancyboxAboutOpen(button));
    EventHub.off("popups:submit-review-form");
    EventHub.on("popups:submit-review-form", (e, form) => submitReviewForm(form));
    EventHub.off("fancy-box:close");
    EventHub.on("fancy-box:close", () => closeFancyboxPopup());
};

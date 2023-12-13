import $ from "jquery";
import loadBootstrapDialog, { closeAllDialogs, openHeaderImageModal, openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { addCounter } from "@src/plugins/textcounter/index";
import EventHub from "@src/event-hub";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";
import makePhoneCodesBlock from "@src/components/phone/phone-codes-block";
import { enableFormValidation } from "@src/plugins/validation-engine/index";

const sendClickToCallPopup = async (e, formSelector) => {
    const form = $(formSelector);
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);
    try {
        const { mess_type: messType, message } = await postRequest(`${SUBDOMAIN_URL}click_to_call/ajax_save_call_request`, form.serialize(), "JSON");
        if (messType === "success") {
            await loadBootstrapDialog();
            form.trigger("reset");
            closeAllDialogs();

            openResultModal({
                subTitle: message,
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
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
        submitButton.removeClass("disabled");
    }
};

const getTimeZone = () => {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
};

const setTimeZone = selector => {
    const element = document.querySelector(selector);
    if (!element) {
        return;
    }
    const timeZone = getTimeZone();
    const options = Array.from(element.options);
    options.forEach(option => {
        if (option.text.includes(timeZone)) {
            element.value = option.value;
        }
    });
};

const openClickToCallPopup = async btn => {
    await import("@scss/user_pages/click_to_call_popup/index.scss");
    await loadBootstrapDialog();
    openHeaderImageModal({
        title: translate({ plug: "general_i18n", text: "js_click_to_call_popup_title" }),
        titleUppercase: true,
        subTitle: translate({ plug: "general_i18n", text: "js_click_to_call_popup_subtitle" }),
        titleImage: btn.data("popupBg"),
        isAjax: true,
        content: `${SUBDOMAIN_URL}${btn.data("href")}`,
        classes: "click-to-call-popup js-select2-dropdown-wrapper",
        validate: true,
        openCallBack: () => {
            const form = $("#click-to-call-popup");
            const { phoneBlock, timeZone, counterTextarea } = form.data();
            enableFormValidation($(form), {}, btn);
            setTimeZone(timeZone);
            makePhoneCodesBlock(phoneBlock);
            addCounter($(counterTextarea));
        },
    });
    EventHub.off("click-to-call-popup:form-submit");
    EventHub.on("click-to-call-popup:form-submit", (e, form) => sendClickToCallPopup(e, form));
};

export default openClickToCallPopup;

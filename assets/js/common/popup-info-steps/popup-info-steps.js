import $ from "jquery";

import { translate } from "@src/i18n";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";

const onNextTabStep = tabsNav => {
    updateFancyboxPopup();
    $(tabsNav)
        .find(".link.active")
        .closest(".tabs-circle__item")
        .addClass("complete")
        .nextAll(".tabs-circle__item:visible")
        .first()
        .find(".link")
        .trigger("click")
        .next(".delimeter")
        .addClass("progress");
    setTimeout(() => {
        hideLoader(tabsNav.next());
    }, 200);
};

const onPrevTabStep = tabsNav => {
    updateFancyboxPopup();
    $(tabsNav)
        .find(".link.active")
        .next(".delimeter")
        .removeClass("progress")
        .closest(".tabs-circle__item")
        .prevAll(".tabs-circle__item:visible")
        .first()
        .removeClass("complete")
        .find(".link")
        .trigger("click");
};

const validateTabInit = (formSelector, button) => {
    return import("@src/plugins/validation-engine/index").then(async ({ disableFormValidation, enableFormValidation }) => {
        await disableFormValidation(formSelector);
        await enableFormValidation(formSelector, {}, button);
    });
};

const onValidateTabSubmit = (formSelector, button) => {
    validateTabInit(formSelector, button).then(() => {
        $(formSelector).trigger("submit");
    });
};

async function validateTab(formSelector, tabsNav, button, step) {
    await validateTabInit(formSelector, button);

    $(formSelector).validationEngine("validate", {
        updatePromptsPosition: true,
        promptPosition: "topLeft:0",
        autoPositionUpdate: true,
        focusFirstField: false,
        scroll: false,
        showArrow: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete(form, status) {
            if (status) {
                showLoader(formSelector);
                onNextTabStep($(tabsNav));
            } else {
                systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });
}

const onNextTabSteps = function (formSelector, tabsNav, button) {
    validateTab(formSelector, tabsNav, button, button.data("step"));
};

export { onPrevTabStep, onNextTabSteps, onValidateTabSubmit };

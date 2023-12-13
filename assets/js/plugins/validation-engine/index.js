import $ from "jquery";

import { isWebpackEnabled } from "@src/util/platform";
import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
import callFunction from "@src/util/common/call-function";
import EventHub from "@src/event-hub";

let isInitialized = false;
let ValidationEngine;
const defaultOptions = {
    scroll: false,
    showArrow: false,
    promptPosition: "topLeft:0",
    focusFirstField: false,
    autoPositionUpdate: true,
    addFailureCssClassToField: "validengine-border",
};

const clearValidationOnFocusOut = () => {
    const hideBorder = function () {
        const target = $(this);
        const borderClass = "validengine-border";
        if (target.hasClass(borderClass)) {
            target.trigger("focusout");
        } else if (target.data("select2Id") && target.find("option").length) {
            target.parent().find(`.${borderClass}`).removeClass(borderClass);
        }
    };

    $(document).on("change", "select", hideBorder);
};

/**
 * Initialize validator.
 */
const initialize = async () => {
    if (!isInitialized) {
        await import(/*  webpackChunkName: "validation-engine" */ "@scss/plug/validation-engine/index.scss");
        const validator = await import(/*  webpackChunkName: "validation-engine-plugin" */ "@src/plugins/validation-engine/validation-engine");
        const factory = validator.default;

        await factory();
        ValidationEngine = $.fn.validationEngine || null;
        isInitialized = true;
        clearValidationOnFocusOut();
    }

    return ValidationEngine;
};

/**
 * Disables the form validation.
 *
 * @param {HTMLElement|JQuery} selector
 */
const disableFormValidation = async selector => {
    await initialize();

    const elements = $(selector);
    if (typeof $.fn.validationEngine === "undefined") {
        return elements;
    }

    return elements.validationEngine("detach");
};

/**
 *
 * @param {JQuery} form
 * @param {boolean} status
 * @param {JQuery} [button]
 *
 * @returns {boolean}
 */
const onValidate = (form, status, button = null) => {
    if (!status) {
        systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");

        return;
    }

    const formElement = $(form);
    const action = formElement.data("jsAction") || null;

    if (isWebpackEnabled() && action !== null) {
        EventHub.trigger(action, [form || null, status, button || null]);

        return;
    }

    const callback = formElement.data("callback") || null;
    if (callback !== null) {
        // eslint-disable-next-line consistent-return
        return callFunction(callback, form || null, button || null);
    }

    if (!isWebpackEnabled()) {
        // TODO: should be removed if possible
        if ("modalFormCallBack" in globalThis && globalThis.modalFormCallBack) {
            // eslint-disable-next-line consistent-return
            return callFunction("modalFormCallBack", form || null, button || null);
        }

        if (action !== null) {
            EventHub.trigger(action, [form || null, status, button || null]);

            return;
        }
    }

    formElement.removeClass("validengine");
    setTimeout(() => {
        disableFormValidation(form);
        formElement.trigger("submit");
    }, 500);

    // eslint-disable-next-line consistent-return
    return status;
};

/**
 * Enables provided form validation.
 *
 * @param {HTMLElement|JQuery} selector
 * @param {any} [options]
 * @param {JQuery} [button]
 */
const enableFormValidation = async (selector, options, button) => {
    await initialize();

    const elements = $(selector);
    if (typeof $.fn.validationEngine === "undefined" || elements.data("jqv")) {
        return elements;
    }

    return elements.validationEngine(
        "attach",
        $.extend(
            {
                onValidationComplete(form, status) {
                    return onValidate.call(this, form, status, button);
                },
            },
            defaultOptions,
            options || {}
        )
    );
};

/**
 * Validates the element.
 *
 * @param {HTMLElement|JQuery|JQuery[]} selector
 * @param {any} [options]
 *
 * @returns {Promise<boolean>}
 */
const validateElement = async (selector, options) => {
    await initialize();

    if (typeof $.fn.validationEngine === "undefined") {
        return false;
    }

    const result = $(selector).validationEngine(
        "validate",
        $.extend(
            {
                onValidationComplete(form, status) {
                    return onValidate.call(this, form, status);
                },
            },
            defaultOptions,
            options || {}
        )
    );

    if (typeof result !== "boolean") {
        return false;
    }

    return result;
};

export { initialize };
export { validateElement };
export { enableFormValidation };
export { disableFormValidation };

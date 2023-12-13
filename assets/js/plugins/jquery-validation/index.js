import $ from "jquery";
import addCustomMethods from "@src/plugins/jquery-validation/methods";
import { translate } from "@src/i18n";
import { systemMessages } from "@src/util/system-messages/index";
import { LANG } from "@src/common/constants";

let Validation = null;

/**
 * Initialize validator.
 */
const initialize = async () => {
    if (!Validation) {
        // @ts-ignore
        await import(/*  webpackChunkName: "jquery-validation" */ "@scss/plug/jquery-validation/index.scss");
        await import(/*  webpackChunkName: "jquery-validation-i18n" */ `@plug/jquery-validation/lang/${LANG}.js`);
        // @ts-ignore
        // eslint-disable-next-line no-unused-vars
        const validator = await import(/*  webpackChunkName: "jquery-validation-plugin" */ "jquery-validation");
        // @ts-ignore
        Validation = $.fn.validate || null;

        addCustomMethods();
    }

    return Validation;
};

/**
 * Enables provided form validation.
 *
 * @param {String} selector
 * @param {any} [options]
 */
const enableFormValidation = async (selector, submitCallBack, options) => {
    await initialize();

    const form = $(selector);

    // @ts-ignore
    if (typeof $.fn.validate === "undefined") {
        return form;
    }

    // @ts-ignore
    form.validate(
        $.extend({
            wrapper: "div",
            errorElement: "div",
            errorClass: "invalid-field",
            onsubmit: true,
            focusInvalid: false,
            ignore: ".ignore, :not(:visible)",
            onfocusin(fieldSelector) {
                const field = $(fieldSelector);

                if (!field.data("hideError")) {
                    const errorWr = field.data("group") ? field.prev(".js-validation-error") : field.parent().find(".js-validation-error");

                    this.settings.setErrorPosition(field, errorWr);

                    // @ts-ignore
                    if (errorWr.length && !field.valid()) {
                        errorWr.addClass("show");
                    }
                }
            },
            onfocusout(fieldSelector) {
                const field = $(fieldSelector);
                // @ts-ignore
                const valid = field.valid();

                if (!field.data("hideError")) {
                    const errorWr = field.data("group") ? field.prev(".js-validation-error") : field.parent().find(".js-validation-error");

                    if (!valid && errorWr.hasClass("show")) {
                        errorWr.removeClass("show");
                    }
                }
            },
            setErrorPosition(fieldSelector, errorWr) {
                if (fieldSelector.data("errorPosition") === "bottom") {
                    errorWr.addClass("validation-error--bottom");
                }

                if ($(window).width() < 768) {
                    if (fieldSelector.data("errorPositionMobile") === "top") {
                        errorWr.removeClass("validation-error--bottom");
                    }
                }

                if ($(fieldSelector).hasClass("select2-hidden-accessible")) {
                    errorWr.find(".js-validation-error-content").css({ width: "100%" });
                } else {
                    errorWr.find(".js-validation-error-content").css({ width: fieldSelector[0].offsetWidth });
                }
            },
            errorPlacement(errorContent, field) {
                if (!field.data("hideError")) {
                    const errorWr = $(`<div class="validation-error validation-error-for-${field.attr("name")} js-validation-error"></div>`);

                    if (field.hasClass("select2-hidden-accessible")) {
                        errorContent.css({ bottom: 0 });
                    } else {
                        errorContent.css({ bottom: field.outerHeight(true) });
                    }

                    if (field.data("group")) {
                        errorWr.insertBefore(field);
                    } else {
                        errorWr.insertAfter(field);
                    }

                    errorContent.addClass("validation-error-content js-validation-error-content");
                    errorWr.append(errorContent);
                }
            },
            highlight(elementSelector, errorClass) {
                const field = $(elementSelector);

                if (!field.data("hideError")) {
                    const errorWr = field.data("group") ? field.prev(".js-validation-error") : field.parent().find(".js-validation-error");

                    this.settings.setErrorPosition(field, errorWr);
                }

                if (!field.hasClass(errorClass) && !field.parent().hasClass(errorClass)) {
                    if (!field.data("hideError")) {
                        if (field.hasClass("select2-hidden-accessible")) {
                            $(`#select2-${field.attr("id")}-container`)
                                .parent()
                                .addClass(errorClass);
                        } else {
                            field.addClass(errorClass);
                        }
                    }
                }
            },
            unhighlight(elementSelector, errorClass) {
                const field = $(elementSelector);

                if (!field.data("hideError")) {
                    const errorWr = field.data("group") ? field.prev(".js-validation-error") : field.parent().find(".js-validation-error");

                    if (errorWr.hasClass("show")) {
                        errorWr.removeClass("show");
                    }
                }

                if (field.hasClass("select2-hidden-accessible")) {
                    $(`#select2-${field.attr("id")}-container`)
                        .parent()
                        .removeClass(errorClass);
                } else {
                    field.removeClass(errorClass);
                }
            },
            onkeyup(elementSelector) {
                const field = $(elementSelector);
                // @ts-ignore
                const valid = field.valid();
                const errorWr = field.data("group") ? field.prev(".js-validation-error") : field.parent().find(".js-validation-error");

                if (!errorWr.hasClass("show") && !valid) {
                    errorWr.addClass("show");
                }

                this.settings.setErrorPosition(field, errorWr);
            },
            submitHandler() {
                $(".js-validation-error").removeClass("show");
                submitCallBack();
            },
            invalidHandler() {
                systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");
            },
            ...options,
        })
    );

    // eslint-disable-next-line func-names
    $("body").on("select2:close", "select", function () {
        // @ts-ignore
        $(this).valid();
        $(this).trigger("focusout");
    });

    // eslint-disable-next-line func-names
    $("body").on("select2:opening", "select", function () {
        $(this).trigger("focusin");
    });
};

/**
 * Disables the form validation.
 *
 * @param {String} selector
 */
const disableFormValidation = async selector => {
    await initialize();

    const form = $(selector);

    // @ts-ignore
    if (typeof $.fn.validate === "undefined") {
        return form;
    }

    // @ts-ignore
    const validator = form.validate();

    if (validator) {
        return validator.destroy();
    }
};

export { initialize };
export { enableFormValidation, disableFormValidation };

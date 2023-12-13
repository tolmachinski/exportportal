import $ from "jquery";
import inputmask from "inputmask";
import { translate } from "@src/i18n";
import { LANG, SITE_URL } from "@src/common/constants";
import mix from "@src/util/common/mix";

let selectCcode = () => {};
let selectCcodeFax = () => {};
let maskIsSelectedFax = false;
let maskIsCompleteFax = false;
let maskIsSelected = false;
let maskIsComplete = false;
let maskIsInit = false;
let mask = null;

const initCountryCodeSelect = (phoneNumberSelector, phoneNumber, selectCountryCode) => {
    const selectCountryChange = function () {
        const selected = $(this).find("option:selected");
        // const regex = selected.data("regex") || "";
        const phoneMask = selected.data("phoneMask") || null;

        if (selected.length) {
            maskIsSelected = true;
        } else {
            maskIsSelected = false;
        }

        if (phoneNumber.val() === "") {
            maskIsComplete = false;
        }

        if (phoneNumber.hasClass("validengine-border")) {
            phoneNumber.removeClass("validengine-border").prev(".formError").remove();
        }

        if (phoneMask !== null) {
            mask = inputmask({
                // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                mask: phoneMask.replace(/_/g, "9").replace(/\*/g, "a"),
                keepStatic: true,
                oncomplete() {
                    maskIsComplete = true;
                },
                onincomplete() {
                    maskIsComplete = false;
                },
            }).mask(phoneNumberSelector);

            if (!maskIsInit) {
                phoneNumber.on("paste", () => {
                    setTimeout(() => {
                        maskIsComplete = mask.isComplete();
                    }, 0);
                });
                maskIsInit = true;
            }
        }

        const country = $(this).find("option:selected").data("country");
        setTimeout(() => {
            $(globalThis).trigger("locations:inline:override-coutry", { country });
        }, 500);
    };
    selectCountryCode.change(selectCountryChange);
    selectCountryCode.trigger("change");
};

const initFaxCodeMask = (faxNumberSelector, faxNumber, selectFaxCode) => {
    const selectFaxChange = function () {
        const selected = $(this).find("option:selected");
        const phoneMask = selected.data("phoneMask") || null;

        if (selected.length) {
            maskIsSelectedFax = true;
        } else {
            maskIsSelectedFax = false;
        }

        if (faxNumber.val() === "") {
            maskIsCompleteFax = false;
        }

        if (faxNumber.hasClass("validengine-border")) {
            faxNumber.removeClass("validengine-border").prev(".formError").remove();
        }

        if (phoneMask !== null) {
            mask = inputmask({
                // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                mask: phoneMask.replace(/_/g, "9").replace(/\*/g, "a"),
                keepStatic: true,
                oncomplete() {
                    maskIsCompleteFax = true;
                },
                onincomplete() {
                    maskIsCompleteFax = false;
                },
            }).mask(faxNumberSelector);

            if (!maskIsInit) {
                faxNumber.on("paste", () => {
                    setTimeout(() => {
                        maskIsCompleteFax = mask.isComplete();
                    }, 0);
                });
                maskIsInit = true;
            }
        }

        const country = $(this).find("option:selected").data("country");
        setTimeout(() => {
            $(globalThis).trigger("locations:inline:override-coutry", { country });
        }, 500);
    };
    selectFaxCode.change(selectFaxChange);
    selectFaxCode.trigger("change");
};

const initSelect2 = (selectCountryCode, selectFaxCode) => {
    function formatCcodeRegSelection(cCode) {
        if (!cCode.id) {
            return cCode.text;
        }

        const data = cCode.element.dataset || {};

        return $(
            `<img class="select-country-flag" width="32" height="32" src="${data.countryFlag || null}" alt="${data.countryName || ""}"/><span>${
                data.code || ""
            }</span>`
        );
    }

    function formatCcodeReg(cCode) {
        if (cCode.loading) {
            return cCode.text;
        }

        const { element } = cCode;
        const data = element.dataset || {};

        return $(`<span class="flex-display flex-ai--c notranslate">
                        <img class="w-16 h-16 mr-10" src="${data.countryFlag || null}" alt="${data.countryName}"/>
                        <span>${element.innerText || element.textContent || data.countryName}</span>
                    </span>`);
    }

    selectCcode = selectCountryCode.select2({
        placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
        allowClear: false,
        // eslint-disable-next-line no-underscore-dangle
        language: LANG,
        templateResult: formatCcodeReg,
        templateSelection: formatCcodeRegSelection,
        width: "auto",
        dropdownAutoWidth: true,
    });

    selectCcode.data("select2").$container.attr("id", "country-code--formfield--code-container").addClass("validate[required]").setValHookType("selectCcode");

    $.valHooks.selectCcode = {
        get() {
            return selectCcode.val() || [];
        },
        set(el, val) {
            selectCcode.val(val);
        },
    };
    if (selectFaxCode) {
        selectCcodeFax = selectFaxCode.select2({
            placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
            allowClear: false,
            language: LANG,
            templateResult: formatCcodeReg,
            templateSelection: formatCcodeRegSelection,
            width: "auto",
            dropdownAutoWidth: true,
        });

        selectCcodeFax
            .data("select2")
            .$container.attr("id", "country-code-fax--formfield--code-container")
            .addClass("validate[required]")
            .setValHookType("selectCcodeFax");

        $.valHooks.selectCcodeFax = {
            get() {
                return selectCcodeFax.val() || [];
            },
            set(el, val) {
                selectCcodeFax.val(val);
            },
        };
    }
};

const onCheckPhoneMask = () => {
    if (maskIsSelected === false) {
        return translate({ plug: "general_i18n", text: "js_error_country_code" });
    }

    if (maskIsComplete === false) {
        return translate({ plug: "general_i18n", text: "js_error_phone_mask" });
    }

    return null;
};
const onCheckFaxMask = () => {
    if (maskIsSelectedFax === false) {
        return translate({ plug: "general_i18n", text: "js_error_country_code" });
    }

    if (maskIsCompleteFax === false) {
        return translate({ plug: "general_i18n", text: "js_error_phone_mask" });
    }

    return null;
};

mix(
    globalThis,
    {
        checkPhoneMask: onCheckPhoneMask,
        checkFaxMask: onCheckFaxMask,
    },
    false
);

export { initCountryCodeSelect, initSelect2, initFaxCodeMask };

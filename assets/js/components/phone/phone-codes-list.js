import $ from "jquery";

import { translate } from "@src/i18n";
import { LANG } from "@src/common/constants";

const formatSelection = code => {
    if (!code.id) {
        return code.text;
    }
    const { countryFlag = null, countryName = "", code: codeTitle = "" } = code?.element?.dataset || {};

    return $(`<img class="select-country-flag" width="32" height="32" src="${countryFlag}" alt="${countryName}"/><span>${codeTitle}</span>`);
};

const formatResult = code => {
    if (code.loading) {
        return code.text;
    }
    const { textContent = null, innerText = null } = code?.element ?? {};
    const { countryFlag = null, countryName = "" } = code?.element?.dataset || {};

    return $(
        `<span class="flex-display flex-ai--c notranslate">
            <img class="w-16 h-16 mr-10" src="${countryFlag}" alt="${countryName}"/>
            <span>${innerText || textContent || countryName}</span>
        </span>`
    );
};

const enableValidation = element => {
    const containerId = element.data("validationContainer") ?? `phone-code-container-${(Math.random() + 1).toString(36).substring(2)}`;
    const validationType = element.data("validationType") ?? `phoneCodeType${(Math.random() + 1).toString(36).substring(2)}`;
    const validationRules = element.data("validationRules") ?? "validate[required]";

    element.data("select2").$container.attr("id", containerId).addClass(validationRules).setValHookType(validationType);
    $.valHooks[validationType] = {
        get() {
            return element.val() || [];
        },
        set(el, val) {
            element.val(val);
        },
    };
};

export default function makePhoneCodesList(selector, dropdownParent = null, placeholder = null) {
    const element = $(selector).select2({
        placeholder: placeholder ?? translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
        allowClear: false,
        language: LANG,
        templateResult: formatResult,
        templateSelection: formatSelection,
        width: "auto",
        dropdownAutoWidth: true,
        dropdownParent,
    });
    enableValidation(element);

    return element;
}

import $ from "jquery";
import Inputmask from "inputmask";
// eslint-disable-next-line no-unused-vars
import "select2";

import { renderTemplate } from "@src/util/templates";
import htmlEscape from "@src/util/common/html-escape";
import mix from "@src/util/common/mix";

const selectorPhoneCode = "#js-phone-code";
const selectorPhoneNumber = "#js-phone-number";

let maskIsComplete = false;
let mask;

const initCountryCodeMask = () => {
    const phoneMask = $(selectorPhoneCode).find("option:selected").data("phoneMask");
    mask = Inputmask({
        mask: phoneMask.replace(/_/g, "9").replace(/\*/g, "a"),
        keepStatic: true,
        oncomplete() {
            maskIsComplete = true;
        },
        onincomplete() {
            maskIsComplete = false;
        },
    }).mask(selectorPhoneNumber);

    $(selectorPhoneNumber).on("paste", () => {
        setTimeout(() => {
            maskIsComplete = mask.isComplete();
        }, 0);
    });
};

export default phoneMaskError => {
    initCountryCodeMask();

    const phoneCountryCodes = $("#js-phone-code");

    const template = (isResult = false) => {
        return countryCode => {
            if (countryCode.loading) {
                return countryCode.text;
            }
            const element = $(countryCode.element);

            return $(
                renderTemplate(
                    '<span class="flex-display flex-ai--c notranslate">' +
                        '    <img width="32" height="32" class="mr-10" src="{{url}}" alt="{{alt}}"/>' +
                        "    <span>{{name}}</span>" +
                        "</span>",
                    {
                        url: element.data("countryFlag") || null,
                        alt: element.data("countryName") || "",
                        name: htmlEscape(`${element.text()} ${isResult ? element.data("countryName") : ""}`),
                    }
                )
            );
        };
    };

    const templateResult = template(true);
    const templateSelection = template();

    const selectOptions = {
        width: "100%",
        theme: "default ep-select2-h30",
        dropdownAutoWidth: true,
        templateResult,
        templateSelection,
    };

    if (phoneCountryCodes.length) {
        phoneCountryCodes.select2(selectOptions).data("select2");

        phoneCountryCodes.on("select2:selecting", () => {
            setTimeout(() => {
                mask.option({
                    mask: $(selectorPhoneCode).find("option:selected").data("phoneMask").replace(/_/g, "9").replace(/\*/g, "a"),
                });
            }, 0);
        });
    }

    const checkPhoneMask = function () {
        if (maskIsComplete === false) {
            return `- ${phoneMaskError}`;
        }

        return null;
    };

    if (typeof globalThis.checkPhoneMask === "undefined") {
        mix(globalThis, { checkPhoneMask });
    }
};

import $ from "jquery";
import { googleRecaptchaValidation, googleRecaptchaLoading } from "@src/common/recaptcha/index";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { addCounter } from "@src/plugins/textcounter/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { translate } from "@src/i18n";
import { LANG } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";
import mix from "@src/util/common/mix";

import "@scss/user_pages/contact_us_popup/index.scss";
import onResizeCallback from "@src/util/dom/on-resize-callback";

let $selectCcode;
const phoneNumberSelector = "#js-contact-phone-number";
const selectCountryCodeSelector = "#js-contact-country-code";
let maskIsSelected = false;
let maskIsComplete = false;
let maskIsInit = false;
let mask = {};
let translationRegisterErrorphoneMask = "";
let translationRegisterErrorCountryCode = "";

const formSubmit = async function (formElement, showLoaderTranslate, loggedIn) {
    const form = $(formElement);
    const data = form.serialize();
    const url = loggedIn ? "contact/ajax_contact_operations/send_admin_message" : "contact/ajax_contact_operations/email_contact_admin";

    showLoader(form, showLoaderTranslate);
    try {
        const { message, mess_type: messType } = await postRequest(url, data, "JSON");
        systemMessages(message, messType);
        if (messType === "success") {
            form[0].reset();
            closeFancyBox();
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader($(form));
        setTimeout(() => {
            form.find("button[type=submit]").removeClass("disabled");
        }, 350);
    }
};

const contactAdminFn = async function (form, showLoaderTranslate, loggedIn) {
    $(form).find("button[type=submit]").addClass("disabled");
    if (loggedIn) {
        formSubmit(form, showLoaderTranslate, loggedIn);
    } else {
        await googleRecaptchaLoading();
        googleRecaptchaValidation(form)
            .then(() => {
                formSubmit(form, showLoaderTranslate, loggedIn);
            })
            .catch(() => {
                $(form).find("button[type=submit]").removeClass("disabled");
            });
    }
};

const initSelect2 = () => {
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

    const dropdownWrapper = $(".js-contact-us-dropdown-wrapper");

    $selectCcode = $(selectCountryCodeSelector).select2({
        placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
        allowClear: false,
        language: LANG,
        templateResult: formatCcodeReg,
        templateSelection: formatCcodeRegSelection,
        width: "auto",
        dropdownAutoWidth: true,
        dropdownParent: dropdownWrapper.length ? dropdownWrapper : $("body"),
    });

    $selectCcode.data("select2").$container.attr("id", "country-code--formfield--code-container").addClass("validate[required]").setValHookType("selectCcode");

    $.valHooks.selectCcode = {
        get() {
            return $selectCcode.val() || [];
        },
        set(el, val) {
            $selectCcode.val(val);
        },
    };
};

const initCountryCodeSelect = () => {
    const selectCountryChange = async function () {
        const $selected = $(this).find("option:selected");
        // const regex = $selected.data("regex") || "";
        const phoneMask = $selected.data("phoneMask") || null;

        if ($selected.length) {
            maskIsSelected = true;
        } else {
            maskIsSelected = false;
        }

        if ($(phoneNumberSelector).val() === "") {
            maskIsComplete = false;
        }

        if ($(phoneNumberSelector).hasClass("validengine-border")) {
            $(phoneNumberSelector).removeClass("validengine-border").prev(".formError").remove();
        }

        if (phoneMask !== null) {
            const { default: Inputmask } = await import("inputmask");
            mask = Inputmask({
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
                $(phoneNumberSelector).on("paste", () => {
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
    $(selectCountryCodeSelector).change(selectCountryChange);
    $(selectCountryCodeSelector).trigger("change");
};

const onCheckPhoneMask = () => {
    if (maskIsSelected === false) {
        return `- ${translationRegisterErrorCountryCode}`;
    }

    if (maskIsComplete === false) {
        return `- ${translationRegisterErrorphoneMask}`;
    }

    return null;
};

export default (showLoaderTranslate, loggedIn, url, translationRegisterErrorphoneMaskParam, translationRegisterErrorCountryCodeParam) => {
    translationRegisterErrorphoneMask = translationRegisterErrorphoneMaskParam;
    translationRegisterErrorCountryCode = translationRegisterErrorCountryCodeParam;
    addCounter($(".textcounter_contact-message"));

    EventHub.off("contact-us:form-submit");
    EventHub.on("contact-us:form-submit", (e, form) => contactAdminFn(form, showLoaderTranslate, loggedIn));

    if (!loggedIn) {
        // Lazy loading Select 2
        const lazyLoadingSelect2Fn = async (e, button) => {
            await import("select2").then(() => {
                EventHub.off("lazy-loading:select2", lazyLoadingSelect2Fn);
                initSelect2();
                button.remove();
            });
            $(selectCountryCodeSelector).select2("open");
        };
        EventHub.on("lazy-loading:select2", lazyLoadingSelect2Fn);

        // Lazy loading Input Mask
        const loadLoadingInputMask = async () => {
            $("#js-contact-phone-number").off("click focus", loadLoadingInputMask);
            initCountryCodeSelect();
        };
        $("#js-contact-phone-number").on("click focus", loadLoadingInputMask);

        mix(
            globalThis,
            {
                checkPhoneMask: onCheckPhoneMask,
            },
            false
        );
    }

    $(() => {
        setTimeout(() => {
            $(selectCountryCodeSelector).find("option[data-country-flag]").first().prop("selected", true);
            $(selectCountryCodeSelector).trigger("change");
        }, 100);

        onResizeCallback(() => {
            const openedCountryCode = $(".select-country-code-group .select2-container--open");
            if (openedCountryCode.length) {
                openedCountryCode.prev("select").select2("close").select2("open");
            }
        });
    });
};

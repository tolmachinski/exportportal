import $ from "jquery";
import { translate } from "@src/i18n";
import { LANG } from "@src/common/constants";

class PhoneMask {
    constructor(params = {}) {
        this.countryCodeSelector = params.countryCodeSelector;
        this.selectCountryCode = $(this.countryCodeSelector);
        this.dropdownParentSelector = params.dropdownParentSelector || null;
        this.phoneNumberSelector = params.phoneNumberSelector;
        this.phoneNumber = $(this.phoneNumberSelector);
        this.selectedFax = params.selectedFax || 0;
        this.selectedPhone = params.selectedPhone || 0;
        this.textErorCountryCode = params.textErorCountryCode || "";
        this.textErorPhoneMask = params.textErorPhoneMask || "";
        this.lazyLoaderBtnSelector = params.lazyLoaderBtnSelector || "";
        this.selectCcode = null;
        this.maskIsSelected = false;
        this.maskIsComplete = false;
        this.maskIsInit = false;
        this.mask = {};
    }

    static formatCcodeRegSelection(cCode) {
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

    static formatCcodeReg(cCode) {
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

    initSelect2() {
        const that = this;

        that.selectCcode = that.selectCountryCode.select2({
            placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
            allowClear: false,
            language: LANG,
            templateResult: PhoneMask.formatCcodeReg,
            templateSelection: PhoneMask.formatCcodeRegSelection,
            width: "auto",
            dropdownAutoWidth: true,
            dropdownParent: that.dropdownParentSelector ? $(that.dropdownParentSelector) : $("body"),
            // @ts-ignore
            selectedPhone: that.selectedPhone,
            selectedFax: that.selectedFax,
        });

        that.selectCcode
            // @ts-ignore
            .data("select2")
            .$container.attr("id", "js-country-code-formfield-code-container")
            .addClass("validate[required]")
            .setValHookType("selectCcode");

        $.valHooks.selectCcode = {
            get() {
                // @ts-ignore
                return that.selectCcode.val() || [];
            },
            set(el, val) {
                // @ts-ignore
                that.selectCcode.val(val);
            },
        };
    }

    async selectCountryChange() {
        const that = this;
        const selected = this.selectCountryCode.find("option:selected");
        const phoneMask = selected.data("phoneMask") || null;

        if (selected.length) {
            that.maskIsSelected = true;
        } else {
            that.maskIsSelected = false;
        }

        if (that.phoneNumber.val() === "") {
            that.maskIsComplete = false;
        }

        if (that.phoneNumber.hasClass("validengine-border")) {
            that.phoneNumber.removeClass("validengine-border").prev(".formError").remove();
        }

        if (phoneMask !== null) {
            const { default: Inputmask } = await import("inputmask");
            that.mask = Inputmask({
                // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                mask: phoneMask.replace(/_/g, "9").replace(/\*/g, "a"),
                keepStatic: true,
                oncomplete() {
                    that.maskIsComplete = true;
                },
                onincomplete() {
                    that.maskIsComplete = false;
                },
            }).mask(that.phoneNumberSelector);

            if (!that.maskIsInit) {
                that.phoneNumber.on("paste", () => {
                    setTimeout(() => {
                        that.maskIsComplete = that.mask.isComplete();
                    }, 0);
                });
                that.maskIsInit = true;
            }
        }

        const country = this.selectCountryCode.find("option:selected").data("country");
        setTimeout(() => {
            $(globalThis).trigger("locations:inline:override-coutry", { country });
        }, 500);
    }

    initCountryCodeSelect() {
        const that = this;

        this.selectCountryCode.on("change", that.selectCountryChange.bind(that));
        this.selectCountryCode.trigger("change");
    }

    onCheckPhoneMask() {
        if (this.maskIsSelected === false) {
            return `- ${this.textErorCountryCode}`;
        }

        if (this.maskIsComplete === false) {
            return `- ${this.textErorPhoneMask}`;
        }

        return null;
    }
}

export default PhoneMask;

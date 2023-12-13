import $ from "jquery";
import { LANG, LOGGED_IN, SUBDOMAIN_URL } from "@src/common/constants";
import { translate } from "@src/i18n";
import { googleRecaptchaValidation, googleRecaptchaLoading } from "@src/common/recaptcha/index";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import { showLoader, hideLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import { systemMessages } from "@src/util/system-messages/index";
import { EMAIL, EMAIL_CONTENT, EMAIL_SUBJECT, PHONE, USER_NAME } from "@src/plugins/jquery-validation/rules";
import initJqueryValidation from "@src/plugins/jquery-validation/lazy";

import "@scss/epl/contact-us.scss";

class Contact {
    constructor(params) {
        this.self = this;
        this.formSelector = ".js-contact-us-form";
        this.mask = null;
        this.maskIsInit = false;
        this.maskIsSelected = false;
        this.maskIsComplete = false;
        this.phoneNumberSelector = "#js-epl-register-phone-number";
        this.phoneNumber = $(this.phoneNumberSelector);
        this.selectCcode = null;
        this.selectCountryCode = $("#js-country-code");
        this.recaptcha = null;
        this.loaderTxt = params.laoderTxt;

        this.initFormValidation();
    }

    initCountryCodeSelect() {
        const that = this;
        const selectCountryChange = async function () {
            const selected = $(this).find("option:selected");
            const phoneMask = selected.data("phoneMask") || null;

            if (selected.length) {
                that.maskIsSelected = true;
                that.phoneNumber.val("");
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

                that.phoneNumber.prop("readonly", false);
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
                            this.maskIsComplete = that.mask.isComplete();
                        }, 0);
                    });
                    that.maskIsInit = true;
                }
            }

            const country = $(this).find("option:selected").data("country");
            setTimeout(() => {
                $(globalThis).trigger("locations:inline:override-coutry", { country });
            }, 500);
        };
        this.selectCountryCode.on("change", selectCountryChange);
        this.selectCountryCode.trigger("change");
    }

    initSelect2() {
        const that = this;
        function formatCcodeRegSelection(cCode) {
            if (!cCode.id) {
                return cCode.text;
            }

            const data = cCode.element.dataset || {};

            return $(
                `<img class="select-country-flag" width="32" height="32" src="${data.countryFlag || null}" alt="${
                    data.countryName || ""
                }"/><span class="select-country-code-number">${data.code || ""}</span>`
            );
        }

        function formatCcodeReg(cCode) {
            if (cCode.loading) {
                return cCode.text;
            }

            const { element } = cCode;
            const data = element.dataset || {};

            return $(`<span class="select2-results__option-country notranslate">
                        <img src="${data.countryFlag || null}" alt="${data.countryName}"/>
                        <span>${element.innerText || element.textContent || data.countryName}</span>
                    </span>`);
        }

        this.self.selectCcode = this.selectCountryCode.select2({
            placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
            allowClear: false,
            language: LANG,
            templateResult: formatCcodeReg,
            templateSelection: formatCcodeRegSelection,
            width: "auto",
            dropdownAutoWidth: true,
            dropdownParent: $(".js-select2-dropdown-wrapper"),
        });

        this.self.selectCcode
            .data("select2")
            .$container.attr("id", "country-code--formfield--code-container")
            .addClass("validate[required]")
            .setValHookType("selectCcode");

        $.valHooks.selectCcode = {
            get() {
                return that.selectCcode.val() || [];
            },
            set(el, val) {
                that.selectCcode.val(val);
            },
        };
    }

    checkIsSelectedPhoneMask() {
        return this.maskIsSelected;
    }

    checkIsCompletedPhoneMask() {
        return this.maskIsComplete;
    }

    formSubmit(form) {
        showLoader(form, this.loaderTxt);

        return postRequest(
            `${SUBDOMAIN_URL}${LOGGED_IN ? "contact/ajax_contact_operations/send_admin_message" : "contact/ajax_contact_operations/email_contact_admin"}`,
            form.serialize(),
            "json"
        )
            .then(data => {
                systemMessages(data.message, data.mess_type);
                if (data.mess_type === "success") {
                    form.trigger("reset");
                    closeFancyboxPopup();
                }
            })
            .catch(handleRequestError)
            .finally(() => {
                form.find("button[type=submit]").prop("disabled", false);
                hideLoader(form);
            });
    }

    async contactAdminFn() {
        const that = this;
        const form = $(that.formSelector);
        const submitButton = form.find("button[type=submit]");

        submitButton.prop("disabled", true);

        if (LOGGED_IN) {
            that.formSubmit(form);
        } else {
            await googleRecaptchaLoading();
            googleRecaptchaValidation(that.formSelector)
                .then(() => {
                    that.formSubmit(form);
                })
                .catch(() => {
                    submitButton.prop("disabled", false);
                });
        }
    }

    initFormValidation() {
        const validationOptions = {
            rules: {
                fname: USER_NAME,
                lname: USER_NAME,
                phone: PHONE,
                from: EMAIL,
                subject: EMAIL_SUBJECT,
                content: EMAIL_CONTENT,
            },
        };

        initJqueryValidation(this.formSelector, this.contactAdminFn.bind(this), validationOptions);
    }
}

export default Contact;

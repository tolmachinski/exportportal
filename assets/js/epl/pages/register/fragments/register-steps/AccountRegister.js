/* eslint-disable camelcase */
import $ from "jquery";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import { LANG, SUBDOMAIN_URL } from "@src/common/constants";
import { translate } from "@src/i18n";
import {
    USER_NAME,
    PHONE,
    EMAIL,
    PASSWORD,
    PASSWORD_CONFIRM,
    COUNTRY_CODE,
    COMPANY_NAME,
    COMPANY_OFFICES_NUMBER,
    COMPANY_TEU,
    COUNTRY,
    LOCATION,
    ADDRESS,
    ZIP_CODE,
    STATE,
    CITY,
    CHECKBOX,
} from "@src/plugins/jquery-validation/rules";
import scrollToElement from "@src/util/common/scroll-to-element";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

class AccountRegister {
    constructor() {
        this.self = this;
        this.anotherInit = false;
        this.steps = {};
        this.accountFormSelector = "#js-epl-account-form";
        this.wrRegisterForm = $("#js-epl-wr-register-form");
        this.registerFormSelector = "#js-epl-register-form";
        this.registerForm = $(this.registerFormSelector);
        this.stepList = $("#js-epl-register-steps");
        this.registerNavHasAdditional = $("#js-epl-register-nav-has-additional");
        this.registerNavAdditional = $("#js-epl-register-nav-additional");
        this.registerType = $("#js-register-type-input").val();
        this.mask = null;
        this.maskIsInit = false;
        this.maskIsSelected = false;
        this.maskIsComplete = false;
        this.phoneNumberSelector = "#js-epl-register-phone-number";
        this.phoneNumber = $(this.phoneNumberSelector);
        this.selectCcode = null;
        this.selectCountryCode = $("#js-country-code");
        this.recaptcha = null;

        this.initPlug();
    }

    initPlug() {
        this.initSteps();

        onResizeCallback(() => {
            this.self.initSteps();
        });
    }

    initSteps() {
        let indexPrev;
        const { steps } = this;
        const itemVisibleEach = function () {
            let progress = "";
            // eslint-disable-next-line consistent-this
            const that = $(this);
            const link = that.find(".js-epl-register-steps-item-inner");
            const point = that.find(".js-form-steps-point");
            const element = {
                index: that.index(),
                width: that.outerWidth(),
                left: that.position().left,
                link: {
                    width: point.outerWidth(),
                    left: point.position().left,
                },
            };

            element.leftTotal = element.left + element.width;

            if (that.hasClass("complete") || link.hasClass("active") || that.hasClass("additional")) {
                progress = "progress";
            }

            if (steps[indexPrev] !== undefined) {
                const prevElement = steps[indexPrev];
                const delimeter = {
                    plusElementWidth: (element.width - element.link.width) / 2,
                };
                delimeter.plusAllWidth = delimeter.plusElementWidth + (prevElement.width - prevElement.link.width) / 2;
                delimeter.width = element.left + delimeter.plusAllWidth - prevElement.leftTotal;
                delimeter.minusPosition = delimeter.width - delimeter.plusElementWidth;

                that.append(
                    `<div class="delimeter js-epl-register-steps-delimiter ${progress}" style="width: ${delimeter.width}px; left: -${delimeter.minusPosition}px;"></div>`
                );
            }

            indexPrev = element.index;
            steps[indexPrev] = element;
        };

        this.stepList.find(".js-epl-register-steps-delimiter").remove();
        this.stepList.find(".js-epl-register-steps-item:visible").each(itemVisibleEach);
    }

    onNextRegisterSteps(btn) {
        const step = btn.data("step");
        this.validateStep(step);
    }

    nextRegisterStepsSuccess() {
        scrollToElement(this.accountFormSelector, 20, 300, this.onNextRegisterStep.bind(this));
    }

    onNextRegisterStep() {
        const currentStep = this.stepList.find(".js-epl-register-steps-item.active");

        currentStep
            .removeClass("active")
            .addClass("complete")
            .next(".js-epl-register-steps-item")
            .addClass("active")
            .find(".js-epl-register-steps-item-inner")
            .next(".js-epl-register-steps-delimiter")
            .addClass("progress");

        $(currentStep.data("content")).hide().removeClass("active").next().fadeIn().addClass("active");
    }

    onPrevRegisterSteps() {
        scrollToElement(this.accountFormSelector, 20, 300, this.onPrevRegisterStep.bind(this));
    }

    onPrevRegisterStep() {
        const currentStep = this.stepList.find(".js-epl-register-steps-item.active");

        currentStep
            .removeClass("active")
            .find(".js-epl-register-steps-delimiter")
            .removeClass("progress")
            .end()
            .prev(".js-epl-register-steps-item")
            .removeClass("complete")
            .addClass("active");

        $(currentStep.data("content")).hide().removeClass("active").prev().fadeIn().addClass("active");
    }

    validateStepInit(submitForm = false) {
        const validationOptions = {
            rules: {
                fname: USER_NAME,
                lname: USER_NAME,
                country_code: COUNTRY_CODE,
                phone: {
                    ...PHONE,
                    selectPhoneMask: this.checkIsSelectedPhoneMask.bind(this),
                    completePhoneMask: this.checkIsCompletedPhoneMask.bind(this),
                },
                email: { ...EMAIL },
                password: PASSWORD,
                confirm_password: PASSWORD_CONFIRM,
                company_legal_name: COMPANY_NAME,
                company_name: COMPANY_NAME,
                company_offices_number: COMPANY_OFFICES_NUMBER,
                company_teu: COMPANY_TEU,
                country: COUNTRY,
                states: STATE,
                port_city: CITY,
                location: LOCATION,
                address: ADDRESS,
                zip: ZIP_CODE,
                terms_cond: CHECKBOX,
            },
        };

        return import("@src/plugins/jquery-validation/index").then(async ({ disableFormValidation, enableFormValidation }) => {
            await disableFormValidation(this.registerFormSelector);
            await enableFormValidation(this.registerFormSelector, submitForm ? this.onFormSubmit.bind(this) : null, validationOptions);
        });
    }

    async validateStep(step) {
        await this.validateStepInit();
        this.step = step;

        // @ts-ignore
        if (this.registerForm.valid()) {
            this.serverRequest(this.registerForm, step);
        }
    }

    onValidateStepSubmit() {
        this.validateStepInit(true).then(() => {
            this.registerForm.trigger("submit");
        });
    }

    async onFormSubmit() {
        await this.validateStepInit();

        showLoader(this.registerForm);

        this.recaptcha(this.registerForm)
            .then(() => this.sendRequest(this.registerForm))
            .catch(e => {
                handleRequestError(e);
            })
            .finally(() => {
                hideLoader(this.registerForm);
            });
    }

    serverRequest(form, step) {
        showLoader(form);

        return postRequest(`${SUBDOMAIN_URL}register/ajax_operations/validate_step/${step}`, form.serialize(), "json")
            .then(resp => {
                if (resp.mess_type === "success") {
                    const currentStep = this.stepList.find(".js-epl-register-steps-item.active");
                    const nextStepContent = $(currentStep.next(".js-epl-register-steps-item").data("content"));

                    if (nextStepContent.length) {
                        this.self.nextRegisterStepsSuccess();
                    } else if (resp.content) {
                        $(resp.content).insertAfter($(".js-account-registration-step.active"));
                        this.self.nextRegisterStepsSuccess();
                    }
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }
            })
            .catch(handleRequestError)
            .finally(() => {
                setTimeout(() => {
                    hideLoader(this.registerForm);
                }, 200);
            });
    }

    sendRequest(form) {
        const that = this;

        showLoader(form);

        return postRequest(`${SUBDOMAIN_URL}register/ajax_operations/${that.registerType}`, form.serialize(), "json")
            .then(async resp => {
                await import("@src/util/tracking").then(({ runFormTracking }) => {
                    runFormTracking(form, resp.mess_type === "success");
                });

                if (resp.mess_type === "success") {
                    that.wrRegisterForm.replaceWith(resp.message);
                    scrollToElement(that.accountFormSelector, 60);
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }
            })
            .catch(handleRequestError)
            .finally(() => {
                hideLoader(form);
            });
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
                            that.maskIsComplete = that.mask.isComplete();
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
            dropdownParent: $("#js-country-code-wr"),
        });

        this.self.selectCcode.data("select2").$container.attr("id", "js-country-code-formfield-container").setValHookType("selectCcode");

        $.valHooks.selectCcode = {
            get() {
                return that.selectCcode.val() || [];
            },
            set(el, val) {
                that.selectCcode.val(val);
            },
        };

        $(document).on("select2:open", () => {
            document.querySelector(".select2-search__field").focus();
        });
    }

    checkIsSelectedPhoneMask() {
        return this.maskIsSelected;
    }

    checkIsCompletedPhoneMask() {
        return this.maskIsComplete;
    }
}

export default AccountRegister;

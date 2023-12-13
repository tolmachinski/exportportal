import $ from "jquery";

import { translate } from "@src/i18n";
import { showLoader, hideLoader } from "@src/util/common/loader";
import scrollToElement from "@src/util/common/scroll-to-element";
import mix from "@src/util/common/mix";

import { systemMessages } from "@src/util/system-messages";
import { BACKSTOP_TEST_MODE, IS_RECAPTCHA_ENABLE, LANG, SITE_URL } from "@src/common/constants";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import EventHub from "@src/event-hub";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const accountRegister = {
    init(params) {
        accountRegister.self = this;
        accountRegister.tabsElements = {};
        accountRegister.maskIsSelected = false;
        accountRegister.maskIsComplete = false;
        accountRegister.$selectCcode = () => {};
        accountRegister.anotherInit = false;
        accountRegister.accountForm = "#js-account-form";
        accountRegister.$wrRegisterForm = $("#js-wr-register-form");
        accountRegister.$formTabs = $("#js-register-form");
        accountRegister.$tabsNav = $("#js-register-nav-tabs");
        accountRegister.phoneNumberSelector = "#js-register-phone-number";
        accountRegister.$phoneNumber = $(accountRegister.phoneNumberSelector);
        accountRegister.$registerNavHasAdditional = $("#js-register-nav-has-additional");
        accountRegister.$registerNavAdditional = $("#js-register-nav-additional");
        accountRegister.$anotherAccountHidden = $("#js-another-account-hidden");
        accountRegister.$registerAnotherAccount = $(".js-register-another-account");
        accountRegister.$anotherAccountCheckbox = $("#js-another-account-checkbox");
        accountRegister.$selectCountryCode = $("#js-country-code");
        accountRegister.translationRegisterErrorCountryCode = params.translationRegisterErrorCountryCode;
        accountRegister.translationRegisterErrorphoneMask = params.translationRegisterErrorphoneMask;
        accountRegister.translationRegisterValidateEmailMessage = `- ${params.translationRegisterValidateEmailMessage}`;
        accountRegister.registerType = accountRegister.$formTabs.find('input[name="register_type"]').val();

        accountRegister.self.initPlug();
        accountRegister.self.initListiners();
    },
    initPlug() {
        accountRegister.self.initTabs();
        accountRegister.self.initAccountCheckbox();

        setTimeout(() => {
            accountRegister.$selectCountryCode.find("option[data-country-flag]").first().prop("selected", true);
            accountRegister.$selectCountryCode.trigger("change");
        }, 100);

        onResizeCallback(() => {
            accountRegister.self.initTabs();
        });
    },
    initListiners() {
        mix(
            globalThis,
            {
                checkPhoneMask: accountRegister.self.onCheckPhoneMask,
                checkEmail: accountRegister.self.onCheckEmail,
                nextRegisterStep: accountRegister.self.onNextRegisterStep,
                prevRegisterStep: accountRegister.self.onPrevRegisterStep,
            },
            false
        );
    },
    initTabs() {
        let indexPrev;
        const itemMinEach = function () {
            const item = {
                that: $(this),
            };
            const next = {
                item: item.that.next(),
            };
            const prev = {
                item: item.that.prev(),
            };
            item.width = item.that.width();
            item.wrWidth = accountRegister.$tabsNav.width();
            next.left = next.item.position().left;
            next.rightPosition = item.wrWidth - next.left;
            prev.left = prev.item.position().left;
            prev.width = prev.item.width();
            const innerWidth = (next.left - (prev.left + prev.width)) / 2;

            item.that.css({ right: innerWidth + next.rightPosition - item.width / 2 });
        };
        const itemVisibleEach = function () {
            let progress = "";
            // eslint-disable-next-line consistent-this
            const that = $(this);
            const link = that.find(".link");
            const point = that.find(".tabs-circle__point");
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

            if (accountRegister.tabsElements[indexPrev] !== undefined) {
                const prevElement = accountRegister.tabsElements[indexPrev];
                const delimeter = {
                    plusElementWidth: (element.width - element.link.width) / 2,
                };
                delimeter.plusAllWidth = delimeter.plusElementWidth + (prevElement.width - prevElement.link.width) / 2;
                delimeter.width = element.left + delimeter.plusAllWidth - prevElement.leftTotal;
                delimeter.minusPosition = delimeter.width - delimeter.plusElementWidth;

                that.append(`<div class="delimeter ${progress}" style="width: ${delimeter.width}px; left: -${delimeter.minusPosition}px;"></div>`);
            }

            indexPrev = element.index;
            accountRegister.tabsElements[indexPrev] = element;
        };

        accountRegister.$tabsNav.find(".delimeter").remove();
        accountRegister.$tabsNav.find(".tabs-circle__item--min").each(itemMinEach);
        accountRegister.$tabsNav.find(".tabs-circle__item:visible").each(itemVisibleEach);
    },
    initCountryCodeSelect() {
        const selectCountryChange = async function () {
            const $selected = $(this).find("option:selected");
            // const regex = $selected.data("regex") || "";
            const phoneMask = $selected.data("phoneMask") || null;

            if ($selected.length) {
                accountRegister.maskIsSelected = true;
            } else {
                accountRegister.maskIsSelected = false;
            }

            if (accountRegister.$phoneNumber.val() === "") {
                accountRegister.maskIsComplete = false;
            }

            if (accountRegister.$phoneNumber.hasClass("validengine-border")) {
                accountRegister.$phoneNumber.removeClass("validengine-border").prev(".formError").remove();
            }

            if (phoneMask !== null) {
                const { default: Inputmask } = await import("inputmask");
                accountRegister.mask = Inputmask({
                    // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                    mask: phoneMask.replace(/_/g, "9").replace(/\*/g, "a"),
                    keepStatic: true,
                    oncomplete() {
                        accountRegister.maskIsComplete = true;
                    },
                    onincomplete() {
                        accountRegister.maskIsComplete = false;
                    },
                }).mask(accountRegister.phoneNumberSelector);

                if (!accountRegister.maskIsInit) {
                    accountRegister.$phoneNumber.on("paste", () => {
                        setTimeout(() => {
                            accountRegister.maskIsComplete = accountRegister.mask.isComplete();
                        }, 0);
                    });
                    accountRegister.maskIsInit = true;
                }
            }

            const country = $(this).find("option:selected").data("country");
            setTimeout(() => {
                $(globalThis).trigger("locations:inline:override-coutry", { country });
            }, 500);
        };
        accountRegister.$selectCountryCode.change(selectCountryChange);
        accountRegister.$selectCountryCode.trigger("change");
    },
    initSelect2() {
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

        accountRegister.$selectCcode = accountRegister.$selectCountryCode.select2({
            placeholder: translate({ plug: "general_i18n", text: "register_label_country_code_placeholder" }),
            allowClear: false,
            // eslint-disable-next-line no-underscore-dangle
            language: LANG,
            templateResult: formatCcodeReg,
            templateSelection: formatCcodeRegSelection,
            width: "auto",
            dropdownAutoWidth: true,
            dropdownParent: $("#js-country-code-wr"),
        });

        accountRegister.$selectCcode
            .data("select2")
            .$container.attr("id", "country-code--formfield--code-container")
            .addClass("validate[required]")
            .setValHookType("selectCcode");

        $.valHooks.selectCcode = {
            get() {
                return accountRegister.$selectCcode.val() || [];
            },
            set(el, val) {
                accountRegister.$selectCcode.val(val);
            },
        };
    },
    initAccountCheckbox() {
        const wrRegisterFormFn = function () {
            const checkbox = $(this);
            const val = checkbox.val();
            const allCheckbox = $("input[name=type_another_account]");
            const allCheckboxChecked = $("input[name=type_another_account]:checked").length;
            const checkboxBoth = $(".js-register-another-account").find("input[value=all]");

            if (val !== "all") {
                if (checkbox.prop("checked")) {
                    accountRegister.$anotherAccountHidden.find(`.js-account-registration-another-${val}`).show();

                    if (allCheckbox.length - 1 === allCheckboxChecked) {
                        checkboxBoth.prop("checked", true);
                    }
                } else {
                    accountRegister.$anotherAccountHidden.find(`.js-account-registration-another-${val}`).hide();
                    checkboxBoth.prop("checked", false);
                }
            } else {
                const checkboxChecked = checkbox.prop("checked");

                if (checkboxChecked) {
                    allCheckbox.prop("checked", true);
                    $('div[class*="js-account-registration-another-"]').show();
                    accountRegister.$registerAnotherAccount.find("input[type=checkbox]").prop("checked", true);
                } else {
                    allCheckbox.prop("checked", false);
                    $('div[class*="js-account-registration-another-"]').hide();
                    accountRegister.$registerAnotherAccount.find("input[type=checkbox]").prop("checked", false);
                }
            }
        };
        accountRegister.$registerAnotherAccount.find("input[type=checkbox]").on("change", wrRegisterFormFn);
    },
    onNextRegisterSteps(btn) {
        const step = btn.data("step");
        accountRegister.self.validateTab(step);
    },
    nextRegisterStepsSuccess() {
        scrollToElement(accountRegister.accountForm, 0, 300, "nextRegisterStep");
    },
    onNextRegisterStep() {
        accountRegister.$tabsNav
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
            hideLoader(accountRegister.$formTabs);
        }, 200);
    },
    onPrevRegisterSteps() {
        scrollToElement(accountRegister.accountForm, 0, 300, "prevRegisterStep");
    },
    onPrevRegisterStep() {
        accountRegister.$tabsNav.find(".additional").removeClass("additional");

        accountRegister.$tabsNav
            .find(".link.active")
            .next(".delimeter")
            .removeClass("progress")
            .closest(".tabs-circle__item")
            .prevAll(".tabs-circle__item:visible")
            .first()
            .removeClass("complete")
            .find(".link")
            .trigger("click");
    },
    onSelectAnotherAccount(node) {
        scrollToElement(accountRegister.accountForm, 0, 300);
        const step = node.data("step");
        accountRegister.self.validateTab(step, "additional");
    },
    selectAnotherAccountSuccess() {
        if (accountRegister.anotherInit === false) {
            accountRegister.$registerNavAdditional.removeClass("display-n");
            accountRegister.self.initTabs();
        }

        accountRegister.$registerNavHasAdditional.addClass("additional");

        accountRegister.$registerNavAdditional.find(".link").trigger("click").next(".delimeter").addClass("progress");

        hideLoader(accountRegister.$formTabs);
    },
    onPrevAdditonalRegisterSteps() {
        scrollToElement(accountRegister.accountForm, 0, 300);

        if (accountRegister.anotherInit === false) {
            accountRegister.$registerNavAdditional.addClass("display-n");
            accountRegister.self.initTabs();
        } else {
            accountRegister.$registerNavAdditional.find(".delimeter").removeClass("progress");
        }

        accountRegister.$registerNavHasAdditional.removeClass("additional complete").find(".link").trigger("click");
    },
    onNextAdditonalRegisterSteps(node) {
        scrollToElement(accountRegister.accountForm, 0, 300);
        const step = node.data("step");

        if (accountRegister.anotherInit === true) {
            accountRegister.self.validateTab(step, "additional-next");
        } else {
            accountRegister.anotherInit = false;
            accountRegister.$registerNavAdditional.addClass("display-n");
            accountRegister.self.initTabs();

            accountRegister.$registerNavHasAdditional.addClass("complete");

            accountRegister.$tabsNav
                .find(".link.active")
                .closest(".tabs-circle__item")
                .next(".tabs-circle__item")
                .find(".link")
                .trigger("click")
                .next(".delimeter")
                .addClass("progress");
        }
    },
    selectAnotherAccountNextSuccess() {
        accountRegister.$registerNavHasAdditional.addClass("complete");

        accountRegister.$tabsNav
            .find(".link.active")
            .closest(".tabs-circle__item")
            .addClass("complete")
            .next(".tabs-circle__item")
            .find(".link")
            .trigger("click")
            .next(".delimeter")
            .addClass("progress");

        setTimeout(() => {
            hideLoader(accountRegister.$formTabs);
        }, 200);
    },
    async validateTab(step, type) {
        await accountRegister.validateTabInit();
        const typeValid = type || "main";
        accountRegister.$formTabs.validationEngine("validate", {
            updatePromptsPosition: true,
            promptPosition: "topLeft:0",
            autoPositionUpdate: true,
            focusFirstField: false,
            scroll: false,
            showArrow: false,
            addFailureCssClassToField: "validengine-border",
            onValidationComplete(form, status) {
                if (status) {
                    accountRegister.step = step;
                    accountRegister.self.serverRequest(accountRegister.$formTabs, step, typeValid);
                } else {
                    systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");
                }
            },
        });
    },
    serverRequest($form, step, type) {
        const typeValid = type || "main";
        const fdata = $form.serialize();

        $.ajax({
            type: "POST",
            // eslint-disable-next-line no-underscore-dangle
            url: `${SITE_URL}register/ajax_operations/validate_step/${step}`,
            data: fdata,
            beforeSend() {
                showLoader($form);
            },
            dataType: "json",
            success(resp) {
                if (resp.mess_type === "success") {
                    if (typeValid === "additional") {
                        accountRegister.self.selectAnotherAccountSuccess();
                    } else if (typeValid === "additional-next") {
                        accountRegister.self.selectAnotherAccountNextSuccess();
                    } else {
                        accountRegister.self.nextRegisterStepsSuccess();
                    }
                } else {
                    hideLoader($form);
                    systemMessages(resp.message, resp.mess_type);
                }
            },
        });
        return false;
    },
    onValidateTabSubmit(btn) {
        accountRegister.validateTabInit(btn).then(() => {
            accountRegister.$formTabs.submit();
        });
    },
    validateTabInit(btn) {
        return import("@src/plugins/validation-engine/index").then(async ({ disableFormValidation, enableFormValidation }) => {
            await disableFormValidation(accountRegister.$formTabs);
            await enableFormValidation(accountRegister.$formTabs, {}, btn);
        });
    },
    onRegisterForm(form) {
        showLoader(form);

        accountRegister
            .recaptcha(form)
            .then(() => accountRegister.sendRequest(form))
            .catch(e => {
                handleRequestError(e);
                hideLoader(form);
            });
    },
    sendRequest(form) {
        showLoader(form);

        return postRequest(`${SITE_URL}register/ajax_operations/${accountRegister.registerType}`, form.serialize(), "json")
            .then(async resp => {
                await import("@src/util/tracking").then(({ runFormTracking }) => {
                    runFormTracking(form, resp.mess_type === "success");
                });

                if (resp.mess_type === "success") {
                    // NEW POPUP CALL feedback_registration
                    EventHub.trigger("register-forms:call-trigger-feedback-registration");

                    $("#js-wr-register-form").replaceWith(resp.message);
                    if (!BACKSTOP_TEST_MODE) {
                        scrollToElement(accountRegister.accountForm, 60);
                    }
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }
            })
            .catch(handleRequestError)
            .finally(() => {
                hideLoader(form);
            });
    },
    onCheckPhoneMask() {
        if (accountRegister.maskIsSelected === false) {
            return `- ${accountRegister.translationRegisterErrorCountryCode}`;
        }

        if (accountRegister.maskIsComplete === false) {
            return `- ${accountRegister.translationRegisterErrorphoneMask}`;
        }

        return null;
    },
    onCheckEmail(field) {
        let isValidEmail = true;

        $.ajax({
            type: "POST",
            url: "validate_ajax_call/ajax_check_email_new",
            data: { email: field[0].value },
            beforeSend() {},
            dataType: "json",
            async: false,
            success(resp) {
                isValidEmail = true;

                if (resp.mess_type === "error") {
                    isValidEmail = false;
                }
            },
        });

        return !isValidEmail ? accountRegister.translationRegisterValidateEmailMessage : null;
    },
};

const optionsPass = {
    rules: {
        scores: {
            wordLowercase: 5,
            wordUppercase: 8,
            wordOneNumber: 5,
            wordThreeNumbers: 7,
            wordOneSpecialChar: 10,
            wordTwoSpecialChar: 15,
        },
        activated: {
            wordLowercase: true,
            wordUppercase: true,
            wordOneNumber: true,
            wordMinLength: true,
            wordMaxLength: true,
            wordRepetitions: true,
            wordSequences: false,
        },
    },
    ui: {
        showErrors: true,
        popoverPlacement: "right",
        showProgressBar: true,
        container: ".pass-strength-popup",
        viewports: {
            progress: ".pass-strength-popup__progress",
            errors: ".pass-strength-popup__errors",
            verdict: ".pass-strength-popup__verdict",
        },
    },
    common: {
        minChar: 6,
        maxChar: 30,
    },
};
// eslint-disable-next-line no-underscore-dangle
if (LANG !== "en") {
    optionsPass.i18n = {
        t(key) {
            return translate({ plug: "pwstrength", text: key });
        },
    };
}

const defaultFn = (translationRegisterErrorCountryCode, translationRegisterErrorphoneMask, translationRegisterValidateEmailMessage) => {
    accountRegister.init({
        translationRegisterErrorCountryCode,
        translationRegisterErrorphoneMask,
        translationRegisterValidateEmailMessage,
    });

    $(document).on("select2:open", () => {
        document.querySelector(".select2-search__field").focus();
    });

    EventHub.on("register-forms:next-register-steps", async (e, button) => {
        await accountRegister.validateTabInit();
        accountRegister.onNextRegisterSteps(button);
    });

    EventHub.on("register-forms:prev-register-steps", () => {
        accountRegister.onPrevRegisterSteps();
    });

    EventHub.on("register-forms:validate-tab-submit", (e, button) => {
        accountRegister.onValidateTabSubmit(button);
    });

    EventHub.on("register-forms:select-another-account", (e, button) => {
        accountRegister.onSelectAnotherAccount(button);
    });

    EventHub.on("register-forms:prev-additional-register-steps", () => {
        accountRegister.onPrevAdditonalRegisterSteps();
    });

    EventHub.on("register-forms:next-additional-register-steps", (e, button) => {
        accountRegister.onNextAdditonalRegisterSteps(button);
    });

    EventHub.on("register-forms:submit", (e, form) => {
        accountRegister.onRegisterForm(form);
    });

    // Lazy loading password strength
    let pswdStrengthInit = false;
    const lazyLoadingPswdStrength = function () {
        const target = $(this);
        import("@src/plugins/pwstrength/pwstrength-bootstrap").then(() => {
            if (pswdStrengthInit) return;
            pswdStrengthInit = true;
            target.off("click focus", lazyLoadingPswdStrength);
            const showPopover = function () {
                target.siblings(".popover-password").fadeIn();
            };
            const hidePopover = function () {
                target.siblings(".popover-password").fadeOut();
            };
            // @ts-ignore
            target.pwstrength(optionsPass);
            target.on("focus", showPopover).on("blur", hidePopover);
            showPopover();
        });
    };
    $("#js-register-password").on("click focus", lazyLoadingPswdStrength);

    // Lazy loading Select 2
    const lazyLoadingSelect2Fn = async (e, button) => {
        await import("select2").then(() => {
            EventHub.off("lazy-loading:select2", lazyLoadingSelect2Fn);
            accountRegister.initSelect2();
            button.remove();
        });
        $("#js-country-code").select2("open");
    };
    EventHub.on("lazy-loading:select2", lazyLoadingSelect2Fn);

    // Lazy loading Input Mask
    const loadLoadingInputMask = async () => {
        $("#js-register-phone-number").off("click focus", loadLoadingInputMask);
        accountRegister.initCountryCodeSelect();
    };
    $("#js-register-phone-number").on("click focus", loadLoadingInputMask);

    // Lazy loading validation, recaptcha
    const loadingModules = () => {
        // Validation
        accountRegister.validateTabInit();
        // Recaptcha
        if (IS_RECAPTCHA_ENABLE) {
            import("@src/common/recaptcha/index").then(({ googleRecaptchaLoading, googleRecaptchaValidation }) => {
                googleRecaptchaLoading();
                accountRegister.recaptcha = googleRecaptchaValidation;
            });
        } else {
            accountRegister.recaptcha = () => Promise.resolve();
        }
    };

    accountRegister.$formTabs.one("click, focus", "input", loadingModules);
};

export default defaultFn;

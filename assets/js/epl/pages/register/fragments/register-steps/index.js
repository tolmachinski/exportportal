import $ from "jquery";
import AccountRegister from "@src/epl/pages/register/fragments/register-steps/AccountRegister";
import EventHub from "@src/event-hub";
import { IS_RECAPTCHA_ENABLE } from "@src/common/constants";

let validationIsInit = false;

// Lazy loading Select 2 and Input Mask
const lazyLoadingSelect2Fn = async (e, button, obj) => {
    await import("select2").then(() => {
        EventHub.off("lazy-loading:select2", lazyLoadingSelect2Fn);
        obj.initCountryCodeSelect();
        obj.initSelect2();
        button.remove();
    });

    $("#js-country-code").select2("open");

    obj.initCountryCodeSelect();
};

// Lazy loading validation, recaptcha
const loadingModules = async obj => {
    const accountRegister = obj;
    // Validation
    if (!validationIsInit) {
        await accountRegister.validateStepInit();
    }
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

export default () => {
    const accountRegister = new AccountRegister();

    EventHub.on("register-forms:next-register-steps", async (e, button) => {
        await accountRegister.validateStepInit();
        validationIsInit = true;
        accountRegister.onNextRegisterSteps(button);
    });

    EventHub.on("register-forms:prev-register-steps", () => {
        accountRegister.onPrevRegisterSteps();
    });

    EventHub.on("lazy-loading:select2", (e, button) => {
        lazyLoadingSelect2Fn(e, button, accountRegister);
    });

    EventHub.on("register-forms:validate-step-submit", () => {
        accountRegister.onValidateStepSubmit();
    });

    accountRegister.registerForm.one("click, focus", "input", loadingModules.bind(null, accountRegister));
};

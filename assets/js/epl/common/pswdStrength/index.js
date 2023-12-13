import $ from "jquery";
import { LANG } from "@src/common/constants";
import { translate } from "@src/i18n";

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
        container: ".js-pass-strength-popup",
        viewports: {
            progress: ".js-pass-strength-popup__progress",
            errors: ".js-pass-strength-popup__errors",
            verdict: ".js-pass-strength-popup__verdict",
        },
    },
    common: {
        minChar: 6,
        maxChar: 30,
    },
};

if (LANG !== "en") {
    optionsPass.i18n = {
        t(key) {
            return translate({ plug: "pwstrength", text: key });
        },
    };
}

let pswdStrengthInit = false;

const lazyLoadingPswdStrength = function () {
    const target = $(this);

    import("@src/plugins/pwstrength/pwstrength-bootstrap").then(() => {
        if (pswdStrengthInit) return;
        pswdStrengthInit = true;
        target.off("click focus", lazyLoadingPswdStrength);

        const showPopover = function () {
            target.siblings(".js-popover-password").fadeIn();
        };

        const hidePopover = function () {
            target.siblings(".js-popover-password").fadeOut();
        };
        // @ts-ignore
        target.pwstrength(optionsPass);
        target.on("focus", showPopover).on("blur", hidePopover);
        showPopover();
    });
};

export default lazyLoadingPswdStrength;

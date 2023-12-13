import $ from "jquery";
import { IS_RECAPTCHA_ENABLE, RECAPTCHA_TOKEN } from "@src/common/constants";

const googleRecaptchaLoading = () => {
    return new Promise(resolve => {
        if (!IS_RECAPTCHA_ENABLE) {
            resolve();
        }

        if (document.getElementById("js-recaptcha-script")) {
            resolve();
        } else {
            const s = document.createElement("script");
            s.src = `https://www.recaptcha.net/recaptcha/api.js?render=${RECAPTCHA_TOKEN}`;
            s.async = true;
            s.id = "js-recaptcha-script";
            s.onload = () => resolve();
            document.body.appendChild(s);
        }
    });
};

const googleRecaptchaValidation = async (node, action = {}) => {
    return new Promise(resolve => {
        if (!IS_RECAPTCHA_ENABLE) {
            resolve($(node));
        }

        const captcha = globalThis.grecaptcha;
        captcha.ready(() => {
            captcha.execute(RECAPTCHA_TOKEN, action).then(token => {
                resolve($(node).append(`<input type="hidden" name="token" value="${token}" />`));
            });
        });
    });
};

export default googleRecaptchaValidation;
export { googleRecaptchaValidation, googleRecaptchaLoading };

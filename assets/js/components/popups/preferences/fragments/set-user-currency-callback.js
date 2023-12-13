import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { hideLoader, showLoader } from "@src/util/common/loader";
import getCookie from "@src/util/cookies/get-cookie";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import { systemMessages } from "@src/util/system-messages/index";
import { changeLanguage, translateWithGoogle } from "@src/components/languages/fragments/choose-language/methods";

const callChangeLangCallback = (e, form, lang, options) => {
    const selectedOption = $(form).find(`select[name="language"] option[value="${lang}"]`);
    let googleTranslate = false;

    if (selectedOption.data("translate") === "google") {
        googleTranslate = true;
    }

    if (!googleTranslate) {
        changeLanguage(e, options.domainLangs, lang, selectedOption.data("redirect"));
    } else {
        translateWithGoogle(e, options.urlLang, lang, selectedOption.data("redirect"));
    }
};

const setUserCurrencyCallBack = async (e, form, options) => {
    const formdata = $(form).serializeArray();
    const data = {};
    const currentCurency = getCookie("currency_key");

    $(formdata).each((_index, obj) => {
        // @ts-ignore
        data[obj.name] = obj.value;
    });

    showLoader(form);

    if (currentCurency === data.currency) {
        callChangeLangCallback(e, form, data.language, options);
    } else {
        try {
            const { mess_type: messType, message } = await postRequest(`${SITE_URL}exchange_rate/ajax_operations/set_user_currency`, {
                curr_code: data.currency,
            });

            if (messType !== "success") {
                hideLoader(form);
                systemMessages(message, messType);
            } else {
                callChangeLangCallback(e, form, data.language, options);
            }
        } catch (error) {
            hideLoader(form);
            handleRequestError(error);
        }
    }
};

export default setUserCurrencyCallBack;

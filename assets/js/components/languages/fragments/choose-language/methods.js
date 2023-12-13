import $ from "jquery";
import Cookies from "js-cookie";

import setCookie from "@src/util/cookies/set-cookie";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { openModalPopup } from "@src/plugins/bootstrap-dialog/index";

const getQuery = () => {
    const url = location.search;
    const qs = url.substring(url.indexOf("?") + 1).split("&");
    let result = {};

    for (let i = 0, result = {}; i < qs.length; i++) {
        let parts = qs[i].split("=");

        result[parts[0]] = decodeURIComponent(parts[1]);
    }

    return result;
};

const clearTranslateCookie = () => {
    Cookies.set("googtrans", null);
    Cookies.set("googtrans", null, { domain: globalThis.__js_cookie_domain, sameSite: 'Strict' });
};

const setTranslateCookie = urlLang => {
    const lang = urlLang === "cn" ? "zh-CN" : urlLang === "zh_tw" ? "zh-TW" : urlLang;

    Cookies.set("googtrans", `/en/${lang}`);
    Cookies.set("googtrans", `/en/${lang}`, { domain: globalThis.__js_cookie_domain, sameSite: 'Strict' });
};

const googleTranslate = urlLang => {
    clearTranslateCookie();
    setTranslateCookie(urlLang);

    if (!("google" in globalThis)) {
        return;
    }

    new globalThis.google.translate.TranslateElement(
        {
            pageLanguage: "en",
            includedLanguages: urlLang,
            layout: globalThis.google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: true,
        },
        "google_translate_element"
    );
};

/**
 * Translate with Google Traslation.
 *
 * @param {JQuery.Event} e
 * @param {string} urlLang
 * @param {string} ulang
 * @param {string} translateUrl
 */
const translateWithGoogle = (e, urlLang, ulang, translateUrl) => {
    e.preventDefault();

    if (urlLang === ulang) {
        closeFancyBox();
        location.reload(false);

        return false;
    }

    setCookie("_ulang", ulang, { expires: 31 });
    globalThis.location.href = translateUrl;
};

/**
 * Change language.
 *
 * @param {JQuery.Event} e
 * @param {Array<string>} domainLangs
 * @param {string} ulang
 * @param {string} redirectLink
 */
const changeLanguage = function (e, domainLangs, ulang, redirectLink) {
    e.preventDefault();

    if ($.inArray(ulang, domainLangs) > -1) {
        setCookie("_ulang", ulang, { expires: 31 });
    }
    globalThis.location.href = redirectLink;

    return false;
};

const callSocialModal = button => {
    openModalPopup({
        btn: button,
        title: $(button).attr("title"),
        content: $("#share-social").html(),
        classes: "modal-share",
    });
};

export { getQuery };
export { changeLanguage };
export { googleTranslate };
export { setTranslateCookie };
export { clearTranslateCookie };
export { translateWithGoogle };
export { callSocialModal };

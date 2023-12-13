import $ from "jquery";

import { getQuery, googleTranslate } from "@src/components/languages/fragments/choose-language/methods";

import EventHub from "@src/event-hub";
import getCookie from "@src/util/cookies/get-cookie";
import setUserCurrencyCallBack from "@src/components/popups/preferences/fragments/set-user-currency-callback";

const GOOGLE_TRANSLATIONS = "//translate.google.com/translate_a/element.js";
const setPreferences = (options = {}, e, $form) => {
    setUserCurrencyCallBack(e, $form, options);
};

/**
 * @param {boolean} connectGoogleTranslations
 * @param {Array<string>} domainLangs
 * @param {Array<string>} googleLangs
 * @param {Array<string>} availableLangs
 */
export default (connectGoogleTranslations, domainLangs, googleLangs, availableLangs, loadTranslationScript = false) => {
    const body = $("body");
    const query = getQuery();
    const cookieLang = getCookie("_ulang");
    let urlLang = query.lang !== undefined && $.inArray(query.lang, availableLangs) > -1 ? query.lang : "en";

    if (typeof query.lang === "undefined" && cookieLang !== "en") {
        urlLang = cookieLang;
    }

    body.prepend('<div id="js-google-translate-last" style="font-size: 0; height:0">Translated</div>');

    if (connectGoogleTranslations && new URL(globalThis.location.href).searchParams.get("lang")) {
        if (loadTranslationScript) {
            const script = document.createElement("script");
            script.src = GOOGLE_TRANSLATIONS;
            script.onload = () => {
                setTimeout(() => googleTranslate(googleLangs[urlLang]), 1000);
            };

            document.getElementsByTagName("body")[0].appendChild(script);
        } else {
            googleTranslate(urlLang);
        }
    }

    EventHub.on("toolbar:set-user-preferences", setPreferences.bind(null, { domainLangs, urlLang }));

    body.addClass("t-0");
};

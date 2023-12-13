(function () {
    "use strict";

    var isInitialized = false;

    function clearTranslateCookie() {
        Cookies.set("googtrans", null, { sameSite: 'Strict' });
        Cookies.set("googtrans", null, { domain: __js_cookie_domain, sameSite: 'Strict' });
    }

    function setTranslateCookie(urlLang) {
        urlLang = urlLang === "cn" ? "zh-CN" : urlLang;
        urlLang = urlLang === "zh_tw" ? "zh-TW" : urlLang;

        Cookies.set("googtrans", "/en/" + urlLang, { sameSite: 'Strict' });
        Cookies.set("googtrans", "/en/" + urlLang, { domain: __js_cookie_domain, sameSite: 'Strict' });
    }

    function googleTranslate(urlLang) {
        clearTranslateCookie();
        setTranslateCookie(urlLang);
        new google.translate.TranslateElement(
            {
                pageLanguage: "en",
                includedLanguages: urlLang,
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: true,
            },
            "google_translate_element"
        );
    }

    var translateWithGoogle = function (urlLang, ulang, translateUrl) {
        if (urlLang === ulang) {
            closeFancyBox();
            location.reload(false);

            return false;
        }

        setCookie("_ulang", ulang, 31);
        globalThis.location.href = translateUrl;
    };

    var changeLanguage = function (domainLangs, ulang, redirectLink) {
        if ($.inArray(ulang, domainLangs) > -1) {
            setCookie("_ulang", ulang, 31);
        }

        globalThis.location.href = redirectLink;
        return false;
    };

    function getQuery() {
        var url = location.search;
        var qs = url.substring(url.indexOf("?") + 1).split("&");

        for (var i = 0, result = {}; i < qs.length; i++) {
            qs[i] = qs[i].split("=");
            result[qs[i][0]] = decodeURIComponent(qs[i][1]);
        }

        return result;
    }

    function callUserPreferences(connectGoogleTranslations, domainLangs, googleLangs, availableLangs, loadTranslationScript) {
        loadTranslationScript = loadTranslationScript || false;

        if (isInitialized) {
            return;
        }

        var query = getQuery();
        var cookieLang = getCookie("_ulang");
        var urlLang = query.lang != undefined && $.inArray(query.lang, availableLangs) > -1 ? query.lang : "en";

        if (typeof query.lang === "undefined" && cookieLang !== "en") {
            urlLang = cookieLang;
        }

        isInitialized = true;
        $(function () {
            $("body").prepend('<div id="js-google-translate-last" style="font-size: 0; height:0">Translated</div>');

            if (connectGoogleTranslations && new URL(globalThis.location.href).searchParams.get("lang")) {
                if (loadTranslationScript) {
                    var script = document.createElement("script");
                    script.type = "text/javascript";
                    script.src = "//translate.google.com/translate_a/element.js";

                    script.onload = function () {
                        setTimeout(function () {
                            googleTranslate(googleLangs[urlLang]);
                        }, 1000);
                    };

                    document.getElementsByTagName("body")[0].appendChild(script);
                }
            }

            $("body").addClass("t-0");
        });
        mix(globalThis, {
            googleTranslateElementInit: translateWithGoogle.bind(null, urlLang),
            changeUserPreferences: setPreferences.bind(null, {
                domainLangs: domainLangs,
                urlLang: urlLang,
            }),
        });
    }

    var setUserCurrencyCallBack = function (e, $form, options) {
        var formdata = $($form).serializeArray();
        var data = {};
        var currentCurency = getCookie("currency_key");
        $(formdata).each(function (index, obj) {
            data[obj.name] = obj.value;
        });
        showLoader($form);

        if (currentCurency === data.currency) {
            callChangeLangCallback($form, data.language, options);
        } else {
            // eslint-disable-next-line no-underscore-dangle
            postRequest(__site_url + "exchange_rate/ajax_operations/set_user_currency", { curr_code: data.currency })
                .then(function (response) {
                    if (response.mess_type !== "success") {
                        hideLoader($form);
                        systemMessages(response.message, response.mess_type);
                    } else {
                        callChangeLangCallback($form, data.language, options);
                    }
                })
                .catch(onRequestError);
        }
    };

    function setPreferences(options, form, e) {
        options = options || {};
        setUserCurrencyCallBack(e, $(form), options);
    }

    function callChangeLangCallback($form, lang, options) {
        var selectedOption = $($form).find('select[name="language"] option[value="' + lang + '"]');
        var googleTranslate = false;

        if ("google" === selectedOption.data("translate")) {
            googleTranslate = true;
        }

        if (!googleTranslate) {
            changeLanguage(options.domainLangs, lang, selectedOption.data("redirect"));
        } else {
            translateWithGoogle(options.urlLang, lang, selectedOption.data("redirect"));
        }
    }

    mix(globalThis, {
        callUserPreferences: callUserPreferences,
    });
})();

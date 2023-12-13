import $ from "jquery";
import { i18nDomain } from "@src/i18n";
import { BASE_OPTIONS } from "@src/epl/common/popups/options";
import { LANG } from "@src/common/constants";

let bootHandle = null;
const doBoot = async () => {
    await Promise.all([
        // @ts-ignore
        import(/* webpackChunkName: "fancybox-core" */ "@fancyapps/fancybox"),
        // @ts-ignore
        import(/* webpackChunkName: "fancybox-styles-v3" */ "@scss/plug/fancybox-3-5-7/jquery.fancybox3.scss"),
        // @ts-ignore
        import(/* webpackChunkName: "popup-styles" */ "@scss/epl/components/popups/index.scss"),
        import(/* webpackChunkName: "fancybox-i18n" */ `@plug/fancybox-3-5-7/lang/${LANG}.js`),
    ]);
};

/**
 * Boots the plugin only one time.
 */
const boot = async () => {
    if (bootHandle === null) {
        bootHandle = doBoot();
    }

    return bootHandle;
};

const getAdvancedOptions = () => {
    return {
        lang: LANG,
        i18n: i18nDomain({ plug: "fancybox3" }),
    };
};

/**
 * Initializes the fancybox for given selector.
 *
 * @param {string} selector
 * @param {any} options
 */
const initialize = async (selector, options = {}) => {
    await boot();

    const elements = $(document.querySelectorAll(selector));
    const { i18n, lang } = getAdvancedOptions();

    return new Promise(resolve => {
        setTimeout(() => {
            // @ts-ignore
            elements.selector = selector;
            elements.fancybox({
                ...options,
                i18n: {
                    [lang]: i18n,
                },
            });

            resolve({ self: elements });
        }, 0);
    });
};

const openFancyboxPopup = async (target, options = {}) => {
    await boot();
    const { i18n } = getAdvancedOptions();

    // @ts-ignore
    return $.fancybox.open(target, {
        ...BASE_OPTIONS,
        ...options,
        ...i18n,
    });
};

export { boot };
export { initialize };
export { getAdvancedOptions };
export { openFancyboxPopup };
export default async () => {
    await boot();

    return $.fancybox;
};

import $ from "jquery";

import { i18nDomain } from "@src/i18n";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import { BASE_OPTIONS } from "@src/common/popups/options";
import polyfill from "@src/plugins/fancybox/v2/polyfill";
import { LANG } from "@src/common/constants";

let bootHandle = null;

const connectStyleForFancybox = async () => {
    // eslint-disable-next-line no-underscore-dangle
    if (!globalThis.__shipper_page) {
        return true;
    }

    const elementModalId = "js-verify-fancybox-styles";
    let elementModal = document.getElementById(elementModalId);

    if (!elementModal) {
        const divCreated = document.createElement("div");
        divCreated.className = "fancybox-title";
        divCreated.id = elementModalId;
        divCreated.style.display = "none";
        document.body.appendChild(divCreated);
        elementModal = document.getElementById(elementModalId);
    }

    if (getComputedStyle(elementModal).position !== "relative") {
        await import("@scss/epl/_old-fancybox.scss");
    }

    return true;
};

const doBoot = async () => {
    const [{ default: factory }] = await Promise.all([
        // @ts-ignore
        import(/* webpackChunkName: "fancybox-core" */ "fancybox"),
        // @ts-ignore
        import(/* webpackChunkName: "fancybox-styles" */ "@scss/plug/fancybox-2-1-7/jquery.fancybox.scss"),
        // eslint-disable-next-line no-underscore-dangle
        import(/* webpackChunkName: "fancybox-i18n" */ `@plug/jquery-fancybox-2-1-7/lang/${LANG}.js`),
    ]);

    await connectStyleForFancybox();

    factory($);
    polyfill($.fancybox);
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
    const adjustments = calculateModalBoxSizes();
    const i18n = {
        // eslint-disable-next-line no-underscore-dangle
        lang: LANG,
        i18n: i18nDomain({ plug: "fancybox" }),
    };

    return { adjustments, i18n };
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
    const { adjustments, i18n } = getAdvancedOptions();

    return new Promise(resolve => {
        setTimeout(() => {
            // @ts-ignore
            elements.selector = selector;
            elements.fancybox({
                ...options,
                ...adjustments,
                ...i18n,
            });

            resolve({ self: elements });
        }, 0);
    });
};

const open = async (target, options = {}) => {
    await boot();
    const { adjustments, i18n } = getAdvancedOptions();

    return $.fancybox.open(target, {
        ...BASE_OPTIONS,
        ...i18n,
        ...adjustments,
        ...options,
    });
};

export { boot };
export { open };
export { initialize };
export default async () => {
    await boot();

    return $.fancybox;
};

import $ from "jquery";

import { i18nDomain } from "@src/i18n";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import { BASE_OPTIONS } from "@src/common/popups/options";

import Fancybox from "@src/plugins/fancybox/v2/index";
import { LANG } from "@src/common/constants";

/**
 * Opens the popu with content.
 *
 * @param {string} title
 * @param {string} content
 * @param {any} options
 */
export default async (title, content, options = {}) => {
    const instance = await Fancybox();
    const i18n = {
        // eslint-disable-next-line no-underscore-dangle
        lang: LANG,
        i18n: i18nDomain({ plug: "fancybox" }),
    };

    // @ts-ignore
    instance.open([{ title, content }], $.extend({}, BASE_OPTIONS, calculateModalBoxSizes(), i18n, options, true));
};

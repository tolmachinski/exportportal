import $ from "jquery";

import promisify from "@src/util/async/promisify";

/**
 * Initializes the select2 plugin in lazy mode.
 *
 * @param {string|HTMLElement|JQuery<any>} selector
 * @param {Function} initFn select2 initialization fucntion
 *
 * @returns {Promise<void>}
 */
export default async function lazyLoad(selector, initFn) {
    const rootElement = $(selector);

    return new Promise(resolve => {
        /**
         * @param {JQuery.Event} e
         * @param {JQuery} button
         * @param {JQuery} element
         *
         * @returns {Promise<void>}
         */
        async function doLoad(e, button, element) {
            await import("select2");
            const node = (await promisify(initFn).call(null, element, button)) ?? element;
            button.remove();
            node.select2("open");
        }

        rootElement.on("click", function handler(e) {
            e.preventDefault();
            const button = $(this);
            const target = button.data("lazyTarget") ?? null;
            if (target !== null) {
                doLoad(e, button, $(target));
            }

            button.off("click", handler);
        });

        resolve();
    });
}

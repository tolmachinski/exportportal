import $ from "jquery";

let bootHandle = null;
const defaultOptions = {
    container: ".dtfilter-list",
    txtResetBtn: "Reset filters",
    onActive(array) {
        if (array.length) {
            $(".btn-filter").addClass("btn-filter--active");
        } else {
            $(".btn-filter").removeClass("btn-filter--active");
        }
    },
};

const doBoot = async () => {
    const { default: factory } = await import(/* webpackChunkName: "dt-filters-core" */ "@src/plugins/dt-filters/dt.filters");

    factory();

    // @ts-ignore
    return $.fn.dtFilters;
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

/**
 * Initializes the fancybox for given selector.
 *
 * @param {string} selector
 * @param {any} options
 * @param {boolean} debug
 */
const initialize = async (selector, options = {}, debug = false) => {
    await boot();

    return new Promise(resolve => {
        setTimeout(() => {
            const elements = $(document.querySelectorAll(selector));

            // @ts-ignore
            elements.selector = selector;
            // @ts-ignore

            elements.dtFilters({
                ...defaultOptions,
                ...options,
                selector,
                debug,
                onInit: handler => resolve(handler),
            });
        }, 0);
    });
};

export { boot };
export { initialize };
export default async () => boot();

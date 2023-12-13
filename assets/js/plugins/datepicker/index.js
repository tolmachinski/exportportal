import $ from "jquery";

let bootHandle = null;
const defaultOptions = {
    beforeShow: (input, instance) => {
        instance.dpDiv.addClass("dtfilter-ui-datepicker");
    },
};

const doBoot = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "jquery-ui-datepicker-core" */ "jquery-ui/ui/widgets/datepicker");

    // @ts-ignore
    return $.fn.datepicker;
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
 */
const initialize = async (selector, options = {}) => {
    await boot();

    return new Promise(resolve => {
        setTimeout(() => {
            const elements = $(document.querySelectorAll(selector));

            resolve(
                // @ts-ignore
                elements.datepicker({
                    ...defaultOptions,
                    ...options,
                })
            );
        }, 0);
    });
};

export { boot };
export { initialize };
export default async () => boot();

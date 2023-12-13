import $ from "jquery";

let bootHandle = null;
const defaultOptions = {
    beforeShow: (input, instance) => {
        instance.dpDiv.addClass("dtfilter-ui-datepicker");
    },
};

const doBoot = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "blueimp-file-upload-ui-widget" */ "blueimp-file-upload/js/vendor/jquery.ui.widget");
    await import(/* webpackChunkName: "blueimp-file-upload-transport" */ "blueimp-file-upload/js/jquery.iframe-transport.js");
    await import(/* webpackChunkName: "blueimp-file-upload-core" */ "blueimp-file-upload");
    await import(/* webpackChunkName: "blueimp-file-upload-process" */ "blueimp-file-upload/js/jquery.fileupload-process");
    await import(/* webpackChunkName: "blueimp-file-upload-validate" */ "blueimp-file-upload/js/jquery.fileupload-validate");

    // @ts-ignore
    return $.fn.fileupload;
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
 * Initializes the fileupload for given selector.
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
                elements.fileupload({
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

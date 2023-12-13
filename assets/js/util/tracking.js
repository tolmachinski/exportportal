import $ from "jquery";

/**
 * Runs submit tracking signal for provided form.
 *
 * @param {HTMLElement|JQuery} form
 * @param {boolean} isSuccessfull
 */
const runFormTracking = function (form, isSuccessfull) {
    if (!form) {
        return;
    }

    // eslint-disable-next-line no-underscore-dangle
    if (typeof globalThis.__analytics !== "undefined") {
        // eslint-disable-next-line no-underscore-dangle
        globalThis.__analytics.trackSubmit(
            // eslint-disable-next-line no-underscore-dangle
            $(form).filter(globalThis.__tracking_selector).toArray(),
            {
                isManual: true,
                isSuccessfull: Boolean(~~isSuccessfull),
            },
            {
                immediate: true,
                propagate: false,
            }
        );
    }
};

export { runFormTracking };

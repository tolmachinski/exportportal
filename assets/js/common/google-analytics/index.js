/* eslint-disable camelcase */
// Global function of google analytics events
/**
 * @function callGAEvent Send data to Google Analytics
 * @param {string} eventName Event name, ex: Add-item-success-submit
 * @param {string} eventCategory Event category, ex: Click, Submit, Ajax success, Validation error, e.t.c
 * @param {string} eventLabel Event Label, ex: Some data, may be some string like object {send:true, date:date}
 */
const callGAEvent = function (eventName, eventCategory, eventLabel = "") {
    let tracker = globalThis.gtag || null;
    if (tracker === null) {
        if (typeof globalThis.dataLayer === "undefined" || globalThis.dataLayer.push === "function") {
            return;
        }

        tracker = globalThis.dataLayer.push;
    }

    try {
        tracker("event", eventName, {
            event_category: eventCategory,
            event_label: eventLabel,
            transport_type: "beacon",
        });
    } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
    }
};

export default callGAEvent;

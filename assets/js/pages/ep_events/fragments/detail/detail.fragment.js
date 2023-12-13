import $ from "jquery";
import "jquery-countdown";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";

// #region Variables
/**
 * @typedef {Object} JQueryElements
 * @property {JQuery} eventCountDown
 */

/**
 * @typedef {Object} Selectors
 * @property {String} eventCountDown
 * @property {String} daysLeft
 * @property {String} hoursLeft
 * @property {String} minutesLeft
 * @property {String} secondsLeft
 */

/**
 * @type {JQueryElements}
 */
const defaultElements = {
    eventCountDown: null,
};

/**
 * @type {Selectors}
 */
const defaultSelectors = {
    eventCountDown: null,
    daysLeft: null,
    hoursLeft: null,
    minutesLeft: null,
    secondsLeft: null,
};

// #endregion Variables

// #region Handlers
/**
 * Dispatches the listneres on the page.
 *
 * @param {Selectors} selectors
 */
function startCountDown(elements, selectors, startDate) {
    $(elements.eventCountDown)
        .countdown(startDate, event => {
            const daysLeft = event.strftime("%D");
            const hoursLeft = event.strftime("%H");
            const minutesLeft = event.strftime("%M");
            const secondsLeft = event.strftime("%S");

            if (daysLeft === "01") {
                $(selectors.daysTxt).text("Day");
            }

            $(selectors.daysLeft).html(daysLeft);
            $(selectors.hoursLeft).html(hoursLeft);
            $(selectors.minutesLeft).html(minutesLeft);
            $(selectors.secondsLeft).html(secondsLeft);
        })
        .on("finish.countdown", () => {
            globalThis.location.reload();
        });
}
// #endregion Handlers

export default params => {
    const selectors = { ...defaultSelectors, ...(params.selectors || {}) };
    const elements = { ...defaultElements, ...findElementsFromSelectors(selectors, Object.keys(defaultElements)) };
    const startDate = new Date(params.startDate);

    if (startDate != null) {
        startCountDown(elements, selectors, startDate);
    }
};

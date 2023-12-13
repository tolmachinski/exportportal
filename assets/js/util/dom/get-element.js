import $ from "jquery";

/**
 * Get element for selector.
 *
 * @param {string} selector
 * @returns {JQuery}
 */
export default function getElement(selector) {
    const element = $(selector);
    if (!element.length) {
        throw new ReferenceError(`The element with selector "${selector}"`);
    }

    return element;
}

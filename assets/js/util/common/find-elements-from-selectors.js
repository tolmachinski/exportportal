import $ from "jquery";
/**
 * Returns the elements for provided selectors and subset.
 *
 * @param {{[x: string]: HTMLElement}} selectors
 * @param {Array<string>} keySubset
 *
 * @deprecated No subtitution provided
 *
 * @returns {any}
 */
const findElementsFromSelectors = function (selectors, keySubset) {
    const elements = {};
    const allowedKeys = keySubset || [];
    const selectorKeys = Object.keys(selectors);
    const filterElements = typeof keySubset !== "undefined";

    for (let i = 0; i < selectorKeys.length; i += 1) {
        const key = selectorKeys[i];
        if (filterElements && allowedKeys.indexOf(key) !== -1) {
            if (Object.prototype.hasOwnProperty.call(selectors, key) && selectors[key]) {
                const element = $(selectors[key]);
                if (element.length) {
                    elements[key] = element;
                }
            }
        }
    }

    return elements;
};

export default findElementsFromSelectors;

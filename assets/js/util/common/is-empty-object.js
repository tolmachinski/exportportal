/**
 * Checks if value is object and not is NULL
 *
 * @param {any} value
 *
 * @returns {boolean}
 */
const isEmptyObject = function (value) {
    return typeof value !== "object" || value.constructor !== Object || Object.keys(value).length === 0;
};

export default isEmptyObject;

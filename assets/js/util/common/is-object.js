/**
 * Checks if value is object and not is NULL
 *
 * @param {any} value
 *
 * @returns {boolean}
 */
const isObject = function (value) {
    return value != null && (typeof value === "object" || typeof value === "function");
};

export default isObject;

/**
 * Finds value in the object using dot notation.
 *
 * @param {any} object
 * @param {string|string[]} path
 * @param {any} [value]
 *
 * @returns {any}
 */
const dotIndex = function (object, path, value) {
    if (typeof path === "string") {
        return dotIndex(object, path.split("."), value);
    }

    if (path.length === 1 && typeof value !== "undefined") {
        // eslint-disable-next-line no-param-reassign
        object[path[0]] = value;

        return value;
    }

    if (path.length === 0) {
        return object;
    }

    return dotIndex(object[path[0]], path.slice(1), value);
};

export default dotIndex;

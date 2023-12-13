/**
 * Escapes the regex string
 *
 * @param {string} string
 *
 * @returns {string}
 */

const escapeRegExp = function (string) {
    return string.replace(/[.*+\-?^${}()|[\]\\]/g, "\\$&"); // $& means the whole matched string
};

export default escapeRegExp;

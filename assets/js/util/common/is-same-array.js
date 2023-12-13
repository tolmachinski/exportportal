/**
 * Check if it the same array.
 *
 * @param {Array} a
 * @param {Array} b
 */
const isSameArray = function (a, b) {
    return a.length === b.length && a.every((val, i) => val === b[i]);
};

export default isSameArray;

/**
 * Transforms the DOM value to the boolean value.
 *
 * @param {any} value
 * @returns {boolean}
 */
export default function normalizeDomBoolean(value) {
    if (typeof value === "boolean") {
        return value;
    }

    return Boolean(!Number.isNaN(value) ? value + 0 : ~~value);
}

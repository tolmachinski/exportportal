/**
 * Escapes HTML entities in text.
 *
 * @param {string} text
 *
 * @returns {string}
 */
const htmlEscape = function (text = "") {
    let txt = text.toString();
    if (txt.length === 0) {
        return txt;
    }

    const replaceFrom = ["&", "<", ">", '"', "'", "`"];
    const replaceTo = ["&amp;", "&lt;", "&gt;", "&quot;", "&#x27;", "&#x60;"];
    if (new RegExp(`(?:${replaceFrom.join("|")})`).test(txt)) {
        for (let i = 0; i < replaceFrom.length; i += 1) {
            txt = txt.replace(new RegExp(replaceFrom[i], "g"), replaceTo[i]);
        }
    }

    return txt;
};

export default htmlEscape;

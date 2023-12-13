/**
 * Calls provided function if it is defined
 *
 * @param {string|Function} fn
 * @param {...any} args
 */
const callFunction = (fn, ...args) => {
    if (typeof fn === "string" && fn in globalThis && globalThis[fn]) {
        return globalThis[fn].apply(globalThis[fn], args);
    }

    if (typeof fn === "function") {
        return fn.apply(fn, args);
    }

    return null;
};

export default callFunction;

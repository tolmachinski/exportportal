const cache = new Map();

// Null pattern
cache.set("empty:fragment", () => {});

/**
 * Takes the entrypoint by name
 *
 * @param {string} name
 */
const take = name => cache.get(name) ?? null;

/**
 * @param {string} moduleName
 * @param {function} importCallback
 */
const registerFragment = (moduleName, importCallback) => {
    // eslint-disable-next-line consistent-return
    cache.set(moduleName, async (...args) => {
        try {
            return (await importCallback()).default(...args);
        } catch (error) {
            // eslint-disable-next-line no-console
            console.log(error);
        }
    });
};

/**
 * Dispatches the entrypoint
 *
 * @param {string} name
 * @param {any[]} args
 */
const dispatch = (name, ...args) => {
    const entrypoint = take(name);
    if (typeof entrypoint !== "function") {
        throw new ReferenceError(`The entrypoint '${name}' is not found`);
    }

    return entrypoint(...args);
};

Object.defineProperty(globalThis, "dispatchFragment", { writable: false, value: dispatch });

export { registerFragment };
export { dispatch };
export { take };
export default {
    cache,
    take,
    dispatch,
    registerFragment,
};

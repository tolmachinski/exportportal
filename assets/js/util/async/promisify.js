/**
 * Promisfies the function
 *
 * @param {Function} fn
 *
 * @returns {Function}
 */
const promisify = fn => {
    /**
     * @returns {Promise<any>}
     */
    return function promisified(...args) {
        const self = this;

        return new Promise((resolve, reject) => {
            try {
                resolve(fn.apply(self, args));
            } catch (error) {
                reject(error);
            }
        });
    };
};

export default promisify;

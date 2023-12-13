/**
 * Mexes properties into the object.
 *
 * @param {any} object
 * @param {any} properties
 * @param {boolean} [immutable]
 */
const mix = function (object, properties, immutable = true) {
    Object.keys(properties).forEach(key => {
        if (Object.prototype.hasOwnProperty.call(properties, key)) {
            Object.defineProperty(object, key, {
                writable: !immutable,
                configurable: !immutable,
                value: properties[key],
            });
        }
    });

    return object;
};

export default mix;

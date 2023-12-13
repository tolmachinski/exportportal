import $ from "jquery";

import isObject from "@src/util/common/is-object";

/**
 * The Operation object.
 *
 * @param {string} name
 * @param {Function} handler
 * @param {any} taget
 * @param {any} value
 */
class Operation {
    constructor(name, handler, source, taget, value) {
        this.name = name;
        this.value = value || null;
        this.taget = taget || null;
        this.source = source || null;
        this.handler = typeof handler === "function" ? handler : null;
    }

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    handle(filter) {
        if (this.handler === null) {
            return;
        }

        this.handler.call(
            filter,
            typeof this.source !== "undefined" && this.source ? $(this.source) : null,
            typeof this.taget !== "undefined" && this.taget ? $(this.taget) : null,
            this.value
        );
    }

    /**
     * Checks if operation is valid.
     *
     * @param {any} operation
     */
    static isValid(operation) {
        return operation instanceof Operation || (isObject(operation) && Object.prototype.hasOwnProperty.call(operation, "name"));
    }
}

export default Operation;

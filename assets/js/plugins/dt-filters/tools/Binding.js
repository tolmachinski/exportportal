import isObject from "@src/util/common/is-object";
import Operation from "@src/plugins/dt-filters/tools/Operation";

/**
 * The Binding object.
 *
 * @param {string} name
 * @param {any} source
 * @param {Array.<{name: any, target?: any, value?: any}>} operations
 * @param {{[x: string]: Function}} handlers
 */
class Binding {
    constructor(name, source, operations, handlers) {
        const self = this;

        this.name = name;
        this.operations = [];

        (operations || []).forEach(operation => {
            if (!Operation.isValid(operation) || !Object.prototype.hasOwnProperty.call(handlers, operation.name)) {
                return;
            }

            self.addOperation(new Operation(operation.name, handlers[operation.name], source || null, operation.target || null, operation.value || null));
        });
    }

    /**
     * Adds one operation
     *
     * @param {Operation} operation
     */
    addOperation(operation) {
        if (!(operation instanceof Operation)) {
            return;
        }

        this.operations.push(operation);
    }

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    handle(filter) {
        this.operations.forEach(operation => {
            operation.handle(filter);
        });
    }

    /**
     * Checks if binding is valid.
     *
     * @param {any} binding
     */
    static isValid(binding) {
        return (
            binding instanceof Binding ||
            (isObject(binding) &&
                Object.prototype.hasOwnProperty.call(binding, "name") &&
                Object.prototype.hasOwnProperty.call(binding, "op") &&
                Array.isArray(binding.op))
        );
    }
}

export default Binding;

import Binding from "@src/plugins/dt-filters/tools/Binding";

/**
 * The bindings list object.
 *
 * @param {Array.<{name: string, source?: any, op: Array.<{name: any, target?: any, value?: any}>}>} bindings
 * @param {{[x: string]: Function}} handlers
 */
class Bindings {
    constructor(bindings, handlers) {
        const self = this;

        this.list = [];

        (bindings || []).forEach(binding => {
            if (!Binding.isValid(binding)) {
                return;
            }

            self.addBinding(new Binding(binding.name, binding.source || null, binding.op, handlers || {}));
        });
    }

    /**
     * Adds one binding
     *
     * @param {Binding} binding
     */
    addBinding(binding) {
        if (!(binding instanceof Binding)) {
            return;
        }

        this.list.push(binding);
    }

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    handle(filter) {
        this.list
            .filter(binding => {
                return filter.name && binding.name === filter.name;
            })
            .forEach(binding => {
                binding.handle(filter);
            });
    }
}

export default Bindings;

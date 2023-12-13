class Filters {
    constructor(defaultFilters, activeFilters) {
        const that = this;
        that.currentState = {};
        that.previousState = {};
        that.activeFilters = new Map();

        const raw = { ...(activeFilters || {}) };

        Object.keys(defaultFilters).forEach(filterKey => {
            if (Object.prototype.hasOwnProperty.call(defaultFilters, filterKey)) {
                let filterValue = defaultFilters[filterKey];
                if (Object.prototype.hasOwnProperty.call(activeFilters, filterKey)) {
                    filterValue = raw[filterKey];
                }
                that.activeFilters.set(filterKey, { name: filterKey, value: filterValue });
                that.currentState[filterKey] = filterValue;
                that.previousState[filterKey] = filterValue;
            }
        });
    }

    update(newState) {
        const that = this;

        Object.keys(newState).forEach(name => {
            if (Object.prototype.hasOwnProperty.call(newState, name)) {
                that.updateFilter(name, newState[name]);
            }
        });
    }

    updateFilter(name, value) {
        if (this.activeFilters.has(name)) {
            const oldValue = this.activeFilters.get(name).value;

            this.activeFilters.get(name).value = value;
            this.previousState[name] = oldValue;
            this.currentState[name] = value;
        } else {
            this.activeFilters.set(name, { name, value });
            this.currentState[name] = value;
        }
    }

    hasFilter(name) {
        return this.activeFilters.has(name) && this.filters.get(name).value !== null;
    }

    getFilter(name) {
        if (!this.activeFilters.has(name)) {
            return null;
        }

        return this.activeFilters.get(name);
    }

    getFilterValue(name) {
        if (!this.activeFilters.has(name)) {
            return null;
        }

        return this.activeFilters.get(name).value || null;
    }

    dropFilter(name) {
        if (!this.activeFilters.has(name)) {
            return;
        }

        this.activeFilters.delete(name);
        this.previousState[name] = null;
        this.currentState[name] = null;

        delete this.previousState[name];
        delete this.currentState[name];
    }

    hasChanges() {
        const that = this;

        if (Object.keys(that.currentState).length !== Object.keys(that.previousState).length) {
            return true;
        }

        return Object.keys(that.currentState).some(
            key =>
                Object.prototype.hasOwnProperty.call(that.currentState, key) &&
                (!Object.prototype.hasOwnProperty.call(that.previousState, key) || that.currentState[key] !== that.previousState[key])
        );
    }

    toArray() {
        return Array.from(this.activeFilters.values());
    }

    clear() {
        const that = this;
        that.previousState = { ...that.currentState };
        that.currentState = {};
        Array.from(that.activeFilters.keys()).forEach(key => {
            that.activeFilters.set(key, { name: key, value: null });
            that.currentState[key] = null;
        });
    }
}

export default Filters;

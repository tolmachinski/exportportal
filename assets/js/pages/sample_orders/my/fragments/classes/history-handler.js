class HistoryHandler {
    constructor(urlBuilder, ordersFilters, detailsFilters) {
        this.urlBuilder = urlBuilder;
        this.ordersFilters = ordersFilters;
        this.detailsFilters = detailsFilters;
        this.hasPushStateSupport = "history" in globalThis && typeof globalThis.history.pushState !== "undefined";

        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        if (!this.hasPushStateSupport) {
            // eslint-disable-next-line no-console
            console.warn("This browser does not support pushState");
        }
    }

    save() {
        if (!this.hasPushStateSupport) {
            return; // Nothing to save - no feature support;
        }
        const savedState = { orders: {}, details: {} };
        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        this.ordersFilters.toArray().forEach(entry => {
            savedState.orders[entry.name] = entry.value;
        });
        this.detailsFilters.toArray().forEach(entry => {
            savedState.details[entry.name] = entry.value;
        });

        globalThis.history.pushState(savedState, globalThis.document.title || "", this.urlBuilder.buildUrl());
    }

    update() {
        if (!this.hasPushStateSupport) {
            return; // Nothing to save - no feature support;
        }
        const savedState = { orders: {}, details: {} };
        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        this.ordersFilters.toArray().forEach(entry => {
            savedState.orders[entry.name] = entry.value;
        });
        this.detailsFilters.toArray().forEach(entry => {
            savedState.details[entry.name] = entry.value;
        });

        globalThis.history.replaceState(savedState, globalThis.document.title || "", this.urlBuilder.buildUrl());
    }

    restore(savedState) {
        const savedStateThis = { orders: {}, details: {}, ...(savedState || {}) };

        [
            [savedStateThis.orders || {}, this.ordersFilters],
            [savedStateThis.details || {}, this.detailsFilters],
        ].forEach(entry => {
            const state = entry[0];
            const filters = entry[1];

            // Restore state of the all page content except details
            if (Object.keys(state).length) {
                // If filters exists - we apply them
                filters.update(state);
            } else {
                // Else we clear the filters
                filters.clear();
            }
        });
    }
}

export default HistoryHandler;

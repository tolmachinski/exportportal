class PageContent {
    constructor(statuses, orders, details, filtersPopup, ordersFilters, detailsFilters, historyHandler) {
        this.orders = orders;
        this.details = details;
        this.statuses = statuses;
        this.filtersPopup = filtersPopup;
        this.ordersFilters = ordersFilters;
        this.detailsFilters = detailsFilters;
        this.historyHandler = historyHandler;
    }

    /**
     * Updates page content
     *
     * @param {Boolean} force
     *
     * @returns {Promise<any[]>}
     */
    update(force) {
        const forceThis = typeof force !== "undefined" ? Boolean(~~force) : true;
        const self = this;

        // Collect promises in stack
        const requestStack = [self.statuses.updateCounters()];
        if (forceThis) {
            requestStack.push(self.updateOrders());
            requestStack.push(self.updateDetails());
        } else {
            if (self.ordersFilters.hasChanges()) {
                requestStack.push(self.updateOrders());
            }
            if (self.detailsFilters.hasChanges()) {
                requestStack.push(self.updateDetails());
            }
        }

        return Promise.all(requestStack).finally(() => {
            const orderFilter = self.detailsFilters.getFilterValue("order");
            if (orderFilter !== null) {
                self.orders.activateOrder(orderFilter);
            }
        });
    }

    /**
     * Shows specified content
     *
     * @returns {Promise<any[]>}
     */
    showContent(order, status) {
        const that = this;
        const orderThis = order || null;
        const statusThis = status || null;

        if (orderThis !== null || statusThis !== null) {
            if (orderThis !== null) {
                if (typeof orderThis !== "number") {
                    throw new TypeError("The order must be a number");
                }

                that.detailsFilters.updateFilter("order", orderThis);
            }

            if (statusThis !== null) {
                if (typeof statusThis !== "string") {
                    throw new TypeError("The status must be a string");
                }

                that.ordersFilters.updateFilter("page", 1);
                that.ordersFilters.updateFilter("status", statusThis);
                that.detailsFilters.updateFilter("status", statusThis); // Actually, requires the same filters except for page for content consistency
            }

            that.historyHandler.save(); // Save to history
            that.filtersPopup.updateFields(); // Update form fields
        }

        return Promise.all([that.updateOrders(), that.updateDetails(), that.statuses.updateCounters()]).finally(() => {
            if (orderThis !== null) {
                that.orders.activateOrder(orderThis);
            }
        });
    }

    /**
     * Shows sample order details using provided filters
     *
     * @return {Promise<boolean|void>}
     */
    updateDetails() {
        const that = this;
        const orderFilter = that.detailsFilters.getFilterValue("order");

        if (orderFilter === null) {
            that.details.cleanDetails();
            that.orders.deactivateAllOrders();

            return Promise.resolve(false);
        }
        that.orders.activateOrder(orderFilter);

        return that.details.showDetails(that.detailsFilters);
    }

    /**
     * Shows sample orders for list of filters.
     *
     * @return {Promise<void>}
     */
    updateOrders() {
        const that = this;
        const statusFilter = that.ordersFilters.getFilterValue("status");

        if (statusFilter === null) {
            that.statuses.deactiveStatus();
        } else {
            that.statuses.activeStatus(statusFilter);
        }

        return that.orders.showOrders(that.ordersFilters);
    }

    /**
     * Shows sample order for ID.
     *
     * @param {Number} orderId
     *
     * @return {Promise<[void,boolean]>}
     */
    showDetailsForOrder(orderId) {
        const that = this;
        if (typeof orderId === "undefined" || Number.isNaN(Number(orderId))) {
            throw new TypeError("The order ID must be a number");
        }
        that.detailsFilters.updateFilter("order", orderId);
        that.historyHandler.update(); // Update state in history
        that.filtersPopup.updateFields(); // Update form fields

        return Promise.all([that.statuses.updateCounters(), that.updateDetails()]);
    }

    /**
     * Shows sample orders for status.
     *
     * @param {(null|String)} status
     *
     * @return {Promise<[void,void]>}
     */
    showOrdersForStatus(status) {
        const that = this;
        if (typeof status === "undefined") {
            throw new TypeError("The status must be defined");
        }

        // Clear orders filters
        that.ordersFilters.clear();
        // Clear details
        that.detailsFilters.clear();
        that.details.cleanDetails();

        that.ordersFilters.updateFilter("page", 1);
        that.ordersFilters.updateFilter("status", status);
        that.detailsFilters.updateFilter("status", status); // Actually, requires the same filters except for page for content consistency
        that.historyHandler.save(); // Save to history
        that.filtersPopup.updateFields(); // Update fields
        that.orders.deactivateAllOrders();

        return Promise.all([
            that.statuses.updateCounters(),
            that.updateOrders().then(() => {
                const currentOrder = that.detailsFilters.getFilterValue("order");
                if (currentOrder !== null) {
                    that.orders.activateOrder(currentOrder);
                } else {
                    that.orders.deactivateAllOrders();
                }
            }),
        ]);
    }

    /**
     * Shows sample orders for page.
     *
     * @param {Number} page
     *
     * @return {Promise<[void,void]>}
     */
    showOrdersForPage(page) {
        const that = this;
        if (typeof page === "undefined" || Number.isNaN(Number(page))) {
            throw new TypeError("The page must be defined");
        }
        that.ordersFilters.updateFilter("page", page);
        that.historyHandler.save(); // Save to history
        that.filtersPopup.updateFields(); // Update foem fields

        return Promise.all([
            that.statuses.updateCounters(),
            that.updateOrders().then(() => {
                const currentOrder = that.detailsFilters.getFilterValue("order");
                if (currentOrder !== null) {
                    that.orders.activateOrder(currentOrder);
                } else {
                    that.orders.deactivateAllOrders();
                }
            }),
        ]);
    }

    getFiltersPopup() {
        return this.filtersPopup;
    }

    getHistoryHandler() {
        return this.historyHandler;
    }

    getOrdersFilters() {
        return this.ordersFilters;
    }

    getDetailsFilters() {
        return this.detailsFilters;
    }
}

export default PageContent;

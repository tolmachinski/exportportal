import { toPadedNumber } from "@src/util/number";

class FiltersPopup {
    /**
     * @typedef {Object} Selectors
     * @typedef {Object} JQueryElements
     */
    /**
     * Controls the filters popup
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    constructor(elements, selectors, ordersFilters, detailsFilters) {
        this.form = elements.searchForm;
        this.selectors = selectors;
        this.resetButton = elements.searchForm ? elements.searchForm.find(selectors.resetFilters) : null;
        this.filterButton = elements.filterButton;
        this.ordersFilters = ordersFilters;
        this.detailsFilters = detailsFilters;

        this.updateFields();
    }

    showMarker() {
        if (this.filterButton !== null) {
            this.filterButton.addClass("btn-filter--active");
        }
    }

    hideMarker() {
        if (this.filterButton !== null) {
            this.filterButton.removeClass("btn-filter--active");
        }
    }

    showResetButton() {
        if (this.resetButton !== null) {
            this.resetButton.css({ display: "inline-block" });
        }
    }

    hideResetButton() {
        if (this.resetButton !== null) {
            this.resetButton.hide();
        }
    }

    updateFields() {
        const assignedFilter = this.ordersFilters.getFilter("assigned");
        const keywordsFilter = this.ordersFilters.getFilter("keywords");
        const statusFilter = this.ordersFilters.getFilter("status");
        const orderFilter = this.ordersFilters.getFilter("order");
        const typeSelect = this.form.find(this.selectors.typesList);
        const keywordsField = this.form.find(this.selectors.keywords);
        const assignedStatusSelect = this.form.find(this.selectors.assignedStatus);
        const activeFormFilters = this.ordersFilters
            .toArray()
            .filter(f => {
                return f.name !== "page";
            })
            .filter(f => {
                return f.value !== null;
            });

        if (orderFilter !== null && orderFilter.value !== null) {
            // If we have sample order then we fill it with order ID
            typeSelect.val(orderFilter.name);
            keywordsField.val(toPadedNumber(orderFilter.value));
        } else {
            // Else if we need to process other filters
            typeSelect.val(statusFilter.value || null);
            keywordsField.val(keywordsFilter.value);
            assignedStatusSelect.val(assignedFilter.value);
        }

        if (activeFormFilters.length) {
            this.showMarker();
            this.showResetButton();
        } else {
            this.hideMarker();
            this.hideResetButton();
        }
    }

    applyChanges() {
        const typeSelect = this.form.find(this.selectors.typesList);
        const keywordsField = this.form.find(this.selectors.keywords);
        const assignedStatusSelect = this.form.find(this.selectors.assignedStatus);
        const type = typeSelect.val() || null;
        const keywords = keywordsField.val() || null;
        const assignedStatus = assignedStatusSelect.val() || null;
        const newOrdersFilters = { status: null, keywords: null, assigned: null, order: null };

        if (type !== null || keywords !== null) {
            if (type === "order") {
                if (keywords === null) {
                    return;
                }

                newOrdersFilters.order = parseInt(keywords.replace("#", ""), 10);
                if (Number.isNaN(Number(newOrdersFilters.order))) {
                    return;
                }
            } else {
                newOrdersFilters.status = type;
                newOrdersFilters.keywords = keywords;
            }
        }
        if (assignedStatus !== null) {
            newOrdersFilters.assigned = parseInt(assignedStatus, 10);
        } else {
            newOrdersFilters.assigned = null;
        }

        const hasOrderFilters = Object.values(newOrdersFilters).filter(f => f !== null).length > 0;
        this.ordersFilters.update(newOrdersFilters);
        this.detailsFilters.update(newOrdersFilters); // To preserve content consistency
        if (hasOrderFilters) {
            this.showMarker();
            this.showResetButton();
        } else {
            this.hideMarker();
            this.hideResetButton();
        }
    }
}

export default FiltersPopup;

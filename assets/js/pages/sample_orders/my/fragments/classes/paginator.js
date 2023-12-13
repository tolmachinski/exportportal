import { renderTemplate } from "@src/util/templates";
import $ from "jquery";

class Paginator {
    /**
     * @typedef {Object} Selectors
     * @typedef {Object} JQueryElements
     */
    /**
     * Paginator
     *
     * @param {any} state
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {String} template
     */

    constructor(state, elements, selectors, template) {
        this.list = elements.paginationWrapper.find(selectors.pagesList);
        this.nextButton = elements.paginationWrapper.find(selectors.nextButton);
        this.prevButton = elements.paginationWrapper.find(selectors.previousButton);
        this.amountLabel = elements.paginationWrapper.find(selectors.pageLabel);
        this.totalLabel = elements.paginationWrapper.find(selectors.totalPagesLabel);
        this.optionTemplate = template || "";
        this.state = { total: 0, perPage: 10, lastPage: 1, currentPage: 1, hasMorePages: false, hasPages: false, ...(state || {}) };

        this.updateState();
    }

    hasRecords() {
        return this.state.total > 0;
    }

    hasPages() {
        return this.state.hasPages;
    }

    getPage() {
        return this.state.currentPage;
    }

    goToPage(page) {
        this.state.currentPage = page;
        if (this.state.currentPage >= this.state.lastPage) {
            this.state.currentPage = this.state.lastPage;
            this.nextButton.prop("disabled", true);
            this.prevButton.prop("disabled", false);
        } else if (this.state.currentPage <= 1) {
            this.state.currentPage = 1;
            this.nextButton.prop("disabled", false);
            this.prevButton.prop("disabled", true);
        } else {
            this.nextButton.prop("disabled", false);
            this.prevButton.prop("disabled", false);
        }
    }

    nextPage() {
        if (this.state.currentPage < this.state.lastPage) {
            this.state.currentPage += 1;
        }
        if (this.state.currentPage >= this.state.lastPage) {
            this.state.currentPage = this.state.lastPage;
            this.nextButton.prop("disabled", true);
        }
        if (this.state.currentPage > 1) {
            this.prevButton.prop("disabled", false);
        }

        this.list.find(`option[value="${this.state.currentPage}"]`).prop("selected", true);
    }

    prevPage() {
        if (this.state.currentPage > 1) {
            this.state.currentPage -= 1;
        }
        if (this.state.currentPage <= 1) {
            this.state.currentPage = 1;
            this.prevButton.prop("disabled", true);
        }
        if (this.state.currentPage < this.state.lastPage) {
            this.nextButton.prop("disabled", false);
        }

        this.list.find(`option[value="${this.state.currentPage}"]`).prop("selected", true);
    }

    updateState(state) {
        const newState = state || {};
        if (Object.keys(newState).length) {
            Object.assign(this.state, newState);
        }

        if (this.state.lastPage === 1) {
            // If last page then disable pagination
            this.prevButton.prop("disabled", true);
            this.nextButton.prop("disabled", true);
        }
        if (this.state.currentPage > 1) {
            this.prevButton.prop("disabled", false);
        }
        if (this.state.currentPage < this.state.lastPage) {
            this.nextButton.prop("disabled", false);
        }

        const options = [];
        if (this.state.lastPage >= 2) {
            // Render the options for pages.
            for (let i = 1; i <= this.state.lastPage; i += 1) {
                const option = renderTemplate(this.optionTemplate, {
                    text: i,
                    value: i,
                    selected: i === this.state.currentPage ? "selected" : "",
                });

                options.push($(option));
            }
        }

        this.amountLabel.text(this.state.total); // Set total amount of records
        this.totalLabel.text(this.state.lastPage); // Set total amount of pages

        if (options.length) {
            this.list.html(options);
        } else {
            const option = renderTemplate(this.optionTemplate, {
                text: 1,
                value: 1,
                selected: "selected",
            });
            this.list.html($(option));
        }

        this.list.prop("disabled", this.list.children().length === 1);
    }
}

export default Paginator;

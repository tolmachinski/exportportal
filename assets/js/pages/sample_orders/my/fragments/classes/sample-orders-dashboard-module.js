import $ from "jquery";
import debounce from "lodash/debounce";
import { preventDefault } from "@src/util/events";
import { toPadedNumber } from "@src/util/number";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";

import Filters from "@src/pages/sample_orders/my/fragments/classes/filters";
import PageContent from "@src/pages/sample_orders/my/fragments/classes/page-content";
import WidthMonitor from "@src/pages/sample_orders/my/fragments/classes/width-monitor";
import Paginator from "@src/pages/sample_orders/my/fragments/classes/paginator";
import StatusesList from "@src/pages/sample_orders/my/fragments/classes/statuses-list";
import OrdersList from "@src/pages/sample_orders/my/fragments/classes/orders-list";
import OrderDetails from "@src/pages/sample_orders/my/fragments/classes/order-details";
import FiltersPopup from "@src/pages/sample_orders/my/fragments/classes/filters-popup";
import HistoryHandler from "@src/pages/sample_orders/my/fragments/classes/history-handler";
import UrlBuilder from "@src/pages/sample_orders/my/fragments/classes/url-builder";

class SampleOrdersDashboardModule {
    /**
     * @typedef {Object} JQueryElements
     * @property {jQuery} statusesList
     * @property {jQuery} searchForm
     * @property {jQuery} rightBlock
     * @property {jQuery} centerBlock
     * @property {jQuery} samplesList
     * @property {jQuery} filterButton
     * @property {jQuery} samplesLabel
     * @property {jQuery} paginationWrapper
     */

    /**
     * @typedef {Object} Selectors
     * @property {String} searchForm
     * @property {String} rightBlock
     * @property {String} centerBlock
     * @property {String} samplesList
     * @property {String} filterButton
     * @property {String} samplesLabel
     * @property {String} statusCounters
     * @property {String} paginationWrapper
     * @property {String} activeStatusItem
     * @property {String} previousButton
     * @property {String} assignedStatus
     * @property {String} statusesList
     * @property {String} nextButton
     * @property {String} statusItem
     * @property {String} sampleItem
     * @property {String} typesList
     * @property {String} keywords
     * @property {String} pagesList
     * @property {String} resetFilters
     * @property {String} statusTitle
     * @property {String} pageLabel
     * @property {String} popovers
     * @property {String} listAlert
     * @property {String} listElement
     * @property {String} detailsAlert
     * @property {String} detailsContent
     * @property {String} totalPagesLabel
     */

    /**
     * @typedef {Object} Fragment
     * @property {String} key
     * @property {String} name
     * @property {String} type
     * @property {Number} position
     * @property {any} value
     */

    constructor(params) {
        this.global = globalThis;

        /**
         * @type {JQueryElements}
         */

        this.defaultElements = {
            statusesList: null,
            searchForm: null,
            rightBlock: null,
            centerBlock: null,
            samplesList: null,
            filterButton: null,
            samplesLabel: null,
            samplesDetails: null,
            paginationWrapper: null,
        };

        /**
         * @type {Selectors}
         */
        this.defaultSelectors = {
            searchForm: null,
            rightBlock: null,
            centerBlock: null,
            samplesList: null,
            filterButton: null,
            samplesLabel: null,
            statusesList: null,
            samplesDetails: null,
            statusCounters: null,
            paginationWrapper: null,
            activeStatusItem: null,
            assignedStatus: null,
            previousButton: null,
            statusItem: null,
            sampleItem: null,
            typesList: null,
            keywords: null,
            pagesList: null,
            nextButton: null,
            resetFilters: null,
            statusTitle: null,
            pageLabel: null,
            popovers: null,
            listAlert: null,
            listElement: null,
            detailsAlert: null,
            detailsContent: null,
            totalPagesLabel: null,
        };

        this.statusVisibilityBreakpoint = 660;
        this.defaultCotentFilters = { status: null, keywords: null, assigned: null, order: null, page: null };
        this.defaultDetailsFilters = { status: null, keywords: null, assigned: null, order: null };

        this.sampleOrdersDashboardModule(params);
    }

    /**
     * Dispatches the listneres on the page.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     * @param {WidthMonitor} widthMonitor
     */
    dispatchListeners(elements, selectors, paginator, pageContent, widthMonitor) {
        const that = this;

        if (elements.statusesList !== null) {
            elements.statusesList.on(
                "click",
                selectors.statusItem,
                debounce(
                    preventDefault(function onClick() {
                        that.onClickStatus(this, pageContent);
                    }),
                    250
                )
            );
        }

        if (elements.samplesList !== null) {
            elements.samplesList.on(
                "click",
                selectors.sampleItem,
                debounce(
                    preventDefault(function onClick() {
                        that.onClickOrder(this, pageContent);
                    }),
                    250
                )
            );
        }

        if (elements.paginationWrapper !== null) {
            elements.paginationWrapper.on(
                "click",
                selectors.previousButton,
                preventDefault(() => {
                    that.onNavigatePreviousPage(paginator, pageContent);
                })
            );
            elements.paginationWrapper.on(
                "click",
                selectors.nextButton,
                preventDefault(() => {
                    that.onNavigateNextPage(paginator, pageContent);
                })
            );
            elements.paginationWrapper.on(
                "change",
                selectors.pagesList,
                preventDefault(function onNavigate() {
                    that.onNavigateToPage(this, paginator, pageContent);
                })
            );
        }

        if (elements.searchForm !== null) {
            elements.searchForm.on(
                "submit",
                preventDefault(() => {
                    that.onApplyFormFilters(pageContent);
                })
            );
            elements.searchForm.on(
                "click",
                selectors.resetFilters,
                preventDefault(() => {
                    that.onClearFormFilters(pageContent);
                })
            );
            elements.searchForm.on(
                "change blur",
                selectors.keywords,
                preventDefault(function onChangeNum() {
                    that.onChangeNumber(this, elements.searchForm.find(selectors.typesList));
                })
            );
        }

        $(this.global).on(
            "resize",
            debounce(function ajust() {
                widthMonitor.adjustTo($(this).width());
            }, 100)
        );
        $(this.global).on(
            "popstate",
            debounce(() => {
                that.onRestoreState(pageContent, globalThis.history.state || {});
            }, 250)
        );

        globalThis.addEventListener("sample-orders:confirm-delivery", e => {
            pageContent.showContent((e?.detail || {}).order || null, "order-completed");
        }); // On delivery completion

        globalThis.addEventListener("sample-orders:confirm-po", e => {
            pageContent.showContent((e?.detail || {}).order || null, "payment-processing");
        }); // On PO confirmation

        globalThis.addEventListener("sample-orders:request", e => {
            pageContent.showContent((e?.detail || {}).order || null, "new-order");
        }); // On request order

        globalThis.addEventListener("sample-orders:create", e => {
            pageContent.showContent((e?.detail || {}).order || null, "new-order");
        }); // On create order

        globalThis.addEventListener("sample-orders:assign", e => {
            pageContent.showContent((e?.detail || {}).order || null, "new-order");
        }); // On assign order

        globalThis.addEventListener("sample-orders:edit-po", () => {
            pageContent.update();
        }); // On PO edit

        globalThis.addEventListener("sample-orders:pay-bill", () => {
            pageContent.updateDetails();
        }); // On bills payment

        globalThis.addEventListener("sample-orders:edit-address", () => {
            pageContent.updateDetails();
        }); // On edit delivery address

        globalThis.addEventListener("sample-orders:edit-tracking-info", () => {
            pageContent.updateDetails();
        }); // On edit tracking info
    }

    /**
     * The sample orders page module
     * @param {any} params
     */
    sampleOrdersDashboardModule(params) {
        /** @type {Selectors} */
        const selectors = { ...this.defaultSelectors, ...(params.selectors || {}) };
        /** @type {JQueryElements} */
        const elements = { ...this.defaultElements, ...findElementsFromSelectors(selectors, Object.keys(this.defaultElements)) };
        const baseUrl = params.baseUrl || globalThis.location.href;
        const ordersUrl = params.ordersUrl || null;
        const detailsUrl = params.detailsUrl || null;
        const countersUrl = params.countersUrl || null;
        const urlMetadata = params.urlMetadata || {};
        const pageTemplate = params.pageTemplate || "";
        const paginatorState = { ...(params.paginator || {}) };
        const pageFilters = { page: 1, ...(params.filters || {}) };

        const detailsFilters = new Filters(this.defaultDetailsFilters, pageFilters);
        const ordersFilters = new Filters(this.defaultCotentFilters, pageFilters);
        const widthMonitor = new WidthMonitor(elements.statusesList, $(globalThis).width());
        const paginator = new Paginator(paginatorState, elements, selectors, pageTemplate);
        const pageContent = new PageContent(
            new StatusesList(elements.statusesList, countersUrl, selectors),
            new OrdersList(ordersUrl, elements, selectors, paginator),
            new OrderDetails(detailsUrl, elements.rightBlock, elements.samplesDetails, selectors),
            new FiltersPopup(elements, selectors, ordersFilters, detailsFilters),
            ordersFilters,
            detailsFilters,
            new HistoryHandler(
                new UrlBuilder(baseUrl, globalThis.location.href, urlMetadata.path || {}, urlMetadata.query || {}),
                ordersFilters,
                detailsFilters
            )
        );

        // Update curretn state to save assigned filters
        pageContent.getHistoryHandler().update();
        // Update content if paginator has pages
        if (paginator.hasRecords()) {
            pageContent.update();
        }

        this.elements = elements;
        this.selectors = selectors;
        this.pageContent = pageContent;

        this.dispatchListeners(elements, selectors, paginator, pageContent, widthMonitor);
    }

    update(order, status) {
        return this.pageContent.update(order || null, status || null);
    }

    switchTo(order, status) {
        return this.pageContent.showContent(order || null, status || null);
    }

    updateDetails() {
        return this.pageContent.updateDetails();
    }

    changeStatus(status) {
        return this.pageContent.showOrdersForStatus(status);
    }

    showOrder(orderId) {
        return this.pageContent.showDetailsForOrder(orderId);
    }

    toPage(page) {
        return this.pageContent.showOrdersForPage(page);
    }

    /**
     * Handles number change event.
     *
     * @param {thisBtn} thisBtn
     * @param {jQuery} types
     */
    // eslint-disable-next-line class-methods-use-this
    onChangeNumber(thisBtn, types) {
        if (types === null || types.length === 0) {
            return;
        }
        const btn = $(thisBtn);
        const type = types.val() || null;
        const number = btn.val() || null;
        if (type === "order" && number !== null) {
            const formatted = toPadedNumber(number);
            if (formatted !== false) {
                btn.val(formatted);
            }
        }
    }

    /**
     * Handles the click on sample order status
     *
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onClickStatus(thisBtn, pageContent) {
        const btn = $(thisBtn);
        let status = btn.data("status") || null;
        if (btn.data("statusIgnore")) {
            status = null;
        }
        pageContent.showOrdersForStatus(status);
    }

    /**
     * Handles the click on sample order
     *
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onClickOrder(thisBtn, pageContent) {
        const btn = $(thisBtn);
        const orderId = btn.data("order") || null;
        if (orderId === null) {
            return;
        }

        pageContent.showDetailsForOrder(orderId);
    }

    /**
     * Handles the submmit of the flters form
     *
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onApplyFormFilters(pageContent) {
        pageContent.getFiltersPopup().applyChanges(); // Apply form changes
        if (pageContent.ordersFilters.hasChanges()) {
            pageContent.getHistoryHandler().save(); // Save to history if orders filters changed
        } else {
            pageContent.getHistoryHandler().update(); // Just update it
        }
        pageContent.update(); // Full content update
    }

    /**
     * Handles the form clear filters event
     *
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onClearFormFilters(pageContent) {
        pageContent.getOrdersFilters().clear(); // Clear orders filters
        pageContent.getDetailsFilters().clear(); // Clear details fitlers
        pageContent.getHistoryHandler().save(); // Save to history
        pageContent.getFiltersPopup().updateFields(); // Update form fields
        pageContent.update();
    }

    /**
     * Handles the navigation to the previous page
     *
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onNavigatePreviousPage(paginator, pageContent) {
        paginator.prevPage();
        pageContent.showOrdersForPage(paginator.getPage());
    }

    /**
     * Handles the navigation to the next page
     *
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onNavigateNextPage(paginator, pageContent) {
        paginator.nextPage();
        pageContent.showOrdersForPage(paginator.getPage());
    }

    /**
     * Handles the navigation to specific page
     *
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     */
    // eslint-disable-next-line class-methods-use-this
    onNavigateToPage(thisBtn, paginator, pageContent) {
        const btn = $(thisBtn);
        const page = btn.val() || null;
        if (page === null) {
            return;
        }

        paginator.goToPage(page);
        pageContent.showOrdersForPage(paginator.getPage());
    }

    /**
     * Handles the history previous state restoration
     *
     * @param {PageContent} pageContent
     * @param {any} state
     */
    // eslint-disable-next-line class-methods-use-this
    onRestoreState(pageContent, state) {
        pageContent.getHistoryHandler().restore(state); // Restore state from history
        pageContent.getFiltersPopup().updateFields(); // Update form fields
        pageContent.update(false); // Full content update
    }
}

export default SampleOrdersDashboardModule;

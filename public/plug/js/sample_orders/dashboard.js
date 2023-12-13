var SampleOrdersDashboardModule = (function (global) {
    "use strict";

    //#region Variables
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

    /**
     * @type {JQueryElements}
     */
    var defaultElements = {
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
    var defaultSelectors = {
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

    var statusVisibilityBreakpoint = 660;
    var detailsVisibilityBreakpoint = 992;
    var defaultCotentFilters = { status: null, keywords: null, assigned: null, order: null, page: null };
    var defaultDetailsFilters = { status: null, keywords: null, assigned: null, order: null };
    //#endregion Variables

    //#region Utility functions
    /**
     * Dispatches the listneres on the page.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     * @param {WidthMonitor} widthMonitor
     */
    function dispatchListeners(elements, selectors, paginator, pageContent, widthMonitor) {
        if (null !== elements.statusesList) {
            elements.statusesList.on("click", selectors.statusItem, _.debounce(
                preventDefault(function () { onClickStatus.call(this, pageContent); }),
                250
            ));
        }

        if (null !== elements.samplesList) {
            elements.samplesList.on("click", selectors.sampleItem, _.debounce(
                preventDefault(function () { onClickOrder.call(this, pageContent); }),
                250
            ));
        }

        if (null !== elements.paginationWrapper) {
            elements.paginationWrapper.on("click", selectors.previousButton, preventDefault(function () {
                onNavigatePreviousPage.call(this, paginator, pageContent);
            }));
            elements.paginationWrapper.on("click", selectors.nextButton, preventDefault(function () {
                onNavigateNextPage.call(this, paginator, pageContent);
            }));
            elements.paginationWrapper.on("change", selectors.pagesList, preventDefault(function () {
                onNavigateToPage.call(this, paginator, pageContent);
            }));
        }

        if (null !== elements.searchForm) {
            elements.searchForm.on("submit", preventDefault(function () {
                onApplyFormFilters.call(this, pageContent);
            }));
            elements.searchForm.on("click", selectors.resetFilters, preventDefault(function () {
                onClearFormFilters.call(this, pageContent);
            }));
            elements.searchForm.on("change blur", selectors.keywords, preventDefault(function () {
                onChangeNumber.call(this, elements.searchForm.find(selectors.typesList));
            }));
        }

        $(global).on("resize", _.debounce(function (e) { widthMonitor.adjustTo($(this).width()); }, 100));
        $(global).on("popstate", _.debounce(function (e) { onRestoreState.call(this, pageContent, history.state || {}) }, 250));

        //Using jQuery as EventHub to handle communication between other components
        $(global).on('sample-orders:confirm-delivery', function (e, data) { pageContent.showContent((data || {}).order || null, 'order-completed'); }); // On delivery completion
        $(global).on('sample-orders:confirm-po', function (e, data) { pageContent.showContent((data || {}).order || null, 'payment-processing'); }); // On PO confirmation
        $(global).on('sample-orders:request', function (e, data) { pageContent.showContent((data || {}).order || null, 'new-order'); }); // On request order
        $(global).on('sample-orders:create', function (e, data) { pageContent.showContent((data || {}).order || null, 'new-order'); }); // On create order
        $(global).on('sample-orders:assign', function (e, data) { pageContent.showContent((data || {}).order || null, 'new-order'); }); // On assign order
        $(global).on('sample-orders:edit-po', function () { pageContent.update(); }); // On PO edit
        $(global).on('sample-orders:pay-bill', function () { pageContent.updateDetails(); }); // On bills payment
        $(global).on('sample-orders:edit-address', function () { pageContent.updateDetails(); }); // On edit delivery address
        $(global).on('sample-orders:edit-tracking-info', function () { pageContent.updateDetails(); }); // On edit tracking info
    }
    //#endregion Utility functions

    //#region Handlers
    /**
     * Handles number change event.
     *
     * @param {jQuery} types
     */
    function onChangeNumber(types) {
        if (null === types || 0 === types.length) {
            return;
        }

        var self = $(this);
        var type = types.val() || null;
        var number = self.val() || null;
        if ("order" === type && null !== number && "toOrderNumber" in global) {
            var formatted = toOrderNumber(number);
            if (false !== formatted) {
                self.val(formatted);
            }
        }
    }
    /**
     * Handles the click on sample order status
     *
     * @param {PageContent} pageContent
     */
    function onClickStatus(pageContent) {
        var self = $(this);
        var status = self.data("status") || null;
        if (self.data("statusIgnore")) {
            status = null;
        }

        pageContent.showOrdersForStatus(status);
    }

    /**
     * Handles the click on sample order
     *
     * @param {PageContent} pageContent
     */
    function onClickOrder(pageContent) {
        var self = $(this);
        var orderId = self.data("order") || null;
        if (null === orderId) {
            return;
        }

        pageContent.showDetailsForOrder(orderId);
    }

    /**
     * Handles the submmit of the flters form
     *
     * @param {PageContent} pageContent
     */
    function onApplyFormFilters(pageContent) {
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
    function onClearFormFilters(pageContent) {
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
    function onNavigatePreviousPage(paginator, pageContent) {
        paginator.prevPage();
        pageContent.showOrdersForPage(paginator.getPage());
    }

    /**
     * Handles the navigation to the next page
     *
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     */
    function onNavigateNextPage(paginator, pageContent) {
        paginator.nextPage();
        pageContent.showOrdersForPage(paginator.getPage());
    }

    /**
     * Handles the navigation to specific page
     *
     * @param {Paginator} paginator
     * @param {PageContent} pageContent
     */
    function onNavigateToPage(paginator, pageContent) {
        var self = $(this);
        var page = self.val() || null;
        if (null === page) {
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
    function onRestoreState(pageContent, state) {
        pageContent.getHistoryHandler().restore(state); // Restore state from history
        pageContent.getFiltersPopup().updateFields(); // Update form fields
        pageContent.update(false); // Full content update
    }
    //#endregion Handlers

    //#region Filters
    function Filters(defaultFilters, activeFilters) {
        this.currentState = {};
        this.previousState = {};
        this.activeFilters = new Map();

        var raw = Object.assign({}, activeFilters || {});
        for (var filterKey in defaultFilters) {
            if (defaultFilters.hasOwnProperty(filterKey)) {
                var filterValue = defaultFilters[filterKey];
                if (activeFilters.hasOwnProperty(filterKey)) {
                    filterValue = raw[filterKey];
                }

                this.activeFilters.set(filterKey, { name: filterKey, value: filterValue });
                this.currentState[filterKey] = filterValue;
                this.previousState[filterKey] = filterValue;
            }
        }
    }
    Filters.prototype.update = function (newState) {
        for (var name in newState) {
            if (newState.hasOwnProperty(name)) {
                this.updateFilter(name, newState[name]);
            }
        }
    };
    Filters.prototype.updateFilter = function (name, value) {
        if (this.activeFilters.has(name)) {
            var oldValue = this.activeFilters.get(name).value;

            this.activeFilters.get(name).value = value;
            this.previousState[name] = oldValue;
            this.currentState[name] = value;
        } else {
            this.activeFilters.set(name, { name: name, value: value });
            this.currentState[name] = value;
        }
    };
    Filters.prototype.hasFilter = function (name) {
        return this.activeFilters.has(name) && this.filters.get(name).value !== null;
    };
    Filters.prototype.getFilter = function (name) {
        if (!this.activeFilters.has(name)) {
            return null;
        }

        return this.activeFilters.get(name);
    };
    Filters.prototype.getFilterValue = function (name) {
        if (!this.activeFilters.has(name)) {
            return null;
        }

        return this.activeFilters.get(name).value || null;
    };
    Filters.prototype.dropFilter = function (name) {
        if (!this.activeFilters.has(name)) {
            return;
        }

        this.activeFilters.delete(name);
        this.previousState[name] = null;
        this.currentState[name] = null;

        delete this.previousState[name];
        delete this.currentState[name];
    };
    Filters.prototype.hasChanges = function () {
        if (Object.keys(this.currentState).length !== Object.keys(this.previousState).length) {
            return true;
        }

        for (var key in this.currentState) {
            if (this.currentState.hasOwnProperty(key)) {
                if (!this.previousState.hasOwnProperty(key) || this.currentState[key] !== this.previousState[key]) {
                    return true;
                }
            }
        }

        return false;
    };
    Filters.prototype.toArray = function () {
        return Array.from(this.activeFilters.values());
    };
    Filters.prototype.clear = function () {
        var self = this;
        this.previousState = Object.assign({}, this.currentState);
        this.currentState = {};
        Array.from(this.activeFilters.keys()).forEach(function (key) {
            self.activeFilters.set(key, { name: key, value: null });
            self.currentState[key] = null;
        });
    };
    //#endregion Filters

    //#region Paginator
    /**
     * Paginator
     *
     * @param {any} state
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {String} template
     */
    function Paginator(state, elements, selectors, template) {
        this.list = elements.paginationWrapper.find(selectors.pagesList);
        this.nextButton = elements.paginationWrapper.find(selectors.nextButton);
        this.prevButton = elements.paginationWrapper.find(selectors.previousButton);
        this.amountLabel = elements.paginationWrapper.find(selectors.pageLabel);
        this.totalLabel = elements.paginationWrapper.find(selectors.totalPagesLabel);
        this.optionTemplate = template || "";
        this.state = Object.assign({}, { total: 0, perPage: 10, lastPage: 1, currentPage: 1, hasMorePages: false, hasPages: false }, state || {});

        this.updateState();
    }
    Paginator.prototype.hasRecords = function () {
        return this.state.total > 0;
    };
    Paginator.prototype.hasPages = function () {
        return this.state.hasPages;
    };
    Paginator.prototype.getPage = function () {
        return this.state.currentPage;
    };
    Paginator.prototype.goToPage = function (page) {
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
    };
    Paginator.prototype.nextPage = function () {
        if (this.state.currentPage < this.state.lastPage) {
            this.state.currentPage++;
        }
        if (this.state.currentPage >= this.state.lastPage) {
            this.state.currentPage = this.state.lastPage;
            this.nextButton.prop("disabled", true);
        }
        if (this.state.currentPage > 1) {
            this.prevButton.prop("disabled", false);
        }

        this.list.find('option[value="' + this.state.currentPage + '"]').prop("selected", true);
    };
    Paginator.prototype.prevPage = function () {
        if (this.state.currentPage > 1) {
            this.state.currentPage--;
        }
        if (this.state.currentPage <= 1) {
            this.state.currentPage = 1;
            this.prevButton.prop("disabled", true);
        }
        if (this.state.currentPage < this.state.lastPage) {
            this.nextButton.prop("disabled", false);
        }

        this.list.find('option[value="' + this.state.currentPage + '"]').prop("selected", true);
    };
    Paginator.prototype.updateState = function (state) {
        var newState = state || {};
        if (Object.keys(newState).length) {
            Object.assign(this.state, newState);
        }

        if (1 === this.state.lastPage) {
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

        var options = [];
        if (this.state.lastPage >= 2) {
            // Render the options for pages.
            for (var i = 1; i <= this.state.lastPage; i++) {
                var option = renderTemplate(this.optionTemplate, {
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
        }

        this.list.prop("disabled", 1 === this.list.children().length);
    };
    //#endregion Paginator

    //#region Statuses List
    /**
     *
     * @param {Selectors} selectors
     */
    function StatusesList(list, listUrl, selectors) {
        if (null === list) {
            throw new TypeError("The statuses list must be defined");
        }
        if (null === listUrl) {
            throw new TypeError("The list URL must be defined");
        }

        this.list = list;
        this.listUrl = listUrl;
        this.selectors = selectors;
    }
    StatusesList.prototype.activeStatus = function (status) {
        if (null === status) {
            return;
        }

        var status = this.list.find('li[data-status="' + status + '"]');
        status.siblings().filter(".active").removeClass("active");
        status.addClass("active");
    };
    StatusesList.prototype.deactiveStatus = function () {
        this.list.find("li[data-status]").removeClass("active");
        this.list.find("li[data-status='all']").addClass("active");
    };
    /**
     * Updates the statuses of the counters
     *
     * @return {Promise<void>}
     */
    StatusesList.prototype.updateCounters = function () {
        var self = this;

        return getRequest(this.listUrl)
            .then(function (data) {
                var counters = data.counters || {};
                for (var key in counters) {
                    if (counters.hasOwnProperty(key)) {
                        var counter = self.list.find('li[data-status="' + key + '"]').find(self.selectors.statusCounters);
                        counter.text(counters[key] || 0);
                    }
                }
            })
            .catch(function (error) {
                if (__debug_mode) {
                    console.error(error);
                }
            });
    };
    //#endregion Statuses List

    //#region Orders List
    /**
     * The sample orders list handler
     * @param {String} listUrl
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Paginator} paginator
     */
    function OrdersList(listUrl, elements, selectors, paginator) {
        this.list = elements.samplesList;
        this.label = elements.samplesLabel;
        this.paginator = paginator;
        this.listWrapper = elements.centerBlock;
        this.listUrl = listUrl || null;
        this.selectors = selectors;
        this.listAlert = this.list.find(selectors.listAlert);
    }
    /**
     * Shows loader in the sample orders list
     */
    OrdersList.prototype.showListLoader = function () {
        if (null !== this.listWrapper) {
            showLoader(this.listWrapper);
        }
    };
    /**
     * Hides loader in the sample orders list
     */
    OrdersList.prototype.hideListLoader = function () {
        if (null !== this.listWrapper) {
            hideLoader(this.listWrapper);
        }
    };
    /**
     * Cleans the list of sample orders.
     *
     * @param {String|null} alert
     */
    OrdersList.prototype.cleanOrderList = function (alert) {
        if (null === this.list) {
            return;
        }

        alert = alert || null;
        var alertChild = this.listAlert;
        var otherChildren = this.list.find(this.selectors.listElement).not(alertChild);

        otherChildren.remove();
        alertChild.show();
        if (null !== alert) {
            alertChild.find("strong").text(alert);
        }
    };
    /**
     * Adds sample orders to the list.
     *
     * @param {Array<String>|null} orders
     */
    OrdersList.prototype.addOrdersToList = function (orders) {
        if (null === this.list) {
            return;
        }

        var newElements = orders || [];
        if (!newElements.length) {
            return;
        }

        this.listAlert.before(
            newElements.map(function (e) {
                return $(e);
            })
        );
        this.listAlert.hide();
    };
    /**
     * Makes the sample order element in list active
     *
     * @param {Number} sample
     */
    OrdersList.prototype.activateOrder = function (orderId) {
        if (null === orderId || null === this.list) {
            return;
        }

        var currentOrder = this.list.find('li[data-order="' + orderId + '"]');
        if (currentOrder.length) {
            currentOrder.siblings().filter(".active").removeClass("active");
            currentOrder.addClass("active");
        }
    };
    /**
     * Makes the sample order element in list inactive
     *
     * @param {Number} orderId
     */
    OrdersList.prototype.deactivateOrder = function (orderId) {
        if (null === orderId || null === this.list) {
            return;
        }

        var currentOrder = this.list.find('li[data-order="' + orderId + '"]');
        if (currentOrder.length) {
            currentOrder.removeClass("active");
        }
    };
    /**
     * Deactivates all orders in the list.
     */
    OrdersList.prototype.deactivateAllOrders = function () {
        if (null === this.list) {
            return;
        }

        var orders = this.list.find("li[data-order]");
        if (orders.length) {
            orders.removeClass("active");
        }
    };
    /**
     * Shows the sample orders in the list
     *
     * @param {Filters} filters
     *
     * @return {Promise<void|boolean>}
     */
    OrdersList.prototype.fetchOrders = function (filters) {
        var self = this;
        if (null === this.listUrl) {
            return Promise.resolve(false);
        }
        this.showListLoader();

        return postRequest(this.listUrl, filters.toArray().filter(function (f) { return null !== f.value; }))
            .then(function (response) {
                self.cleanOrderList();
                self.addOrdersToList(response.data || []);
                self.paginator.updateState(response.paginator || {});

                return true;
            })
            .catch(function (error) {
                onRequestError(error);

                self.cleanOrderList();
                self.label.text(self.label.data("textNotFound"));
            })
            .finally(function () {
                self.hideListLoader();
            });
    };
    /**
     * Finds sample orders with provided fi;lters
     *
     * @param {Filters} filters
     *
     * @return {Promise<void>}
     */
    OrdersList.prototype.showOrders = function (filters) {
        var self = this;
        var hasLabel = null !== this.label;
        var labelText = hasLabel ? this.label.data("textAll") : null;
        var currentStatus = filters.getFilterValue("status") || null;
        if (currentStatus && null !== currentStatus.value) {
            labelText = this.list
                .find('li[data-status="' + currentStatus.value + '"]')
                .find(this.selectors.statusTitle)
                .text();
        }

        return this.fetchOrders(filters).then(function (isSuccessfull) {
            if (isSuccessfull && hasLabel) {
                self.label.text(labelText);

                var activeOrder = filters.getFilterValue("order") || null;
                if (null !== activeOrder) {
                    self.activateOrder(activeOrder);
                }
            }
        });
    };
    //#endregion Orders List

    //#region Order Details
    /**
     *
     * @param {String} detailsUrl
     * @param {jQuery} wrapper
     * @param {jQuery} details
     * @param {Selectors} selectors
     */
    function OrderDetails(detailsUrl, wrapper, details, selectors) {
        if (null === details) {
            throw new TypeError("The details element must be defined");
        }
        if (null === detailsUrl) {
            throw new TypeError("The details URL must be defined");
        }

        this.url = detailsUrl;
        this.selectors = selectors;
        this.wrapper = wrapper;
        this.details = details;
        this.content = details.find(selectors.detailsContent);
        this.alert = details.find(selectors.detailsAlert);
        this.activateRequest = null;
    }

    /**
     * Shows loader in the sample order details
     */
    OrderDetails.prototype.showDetailsLoader = function () {
        if (null !== this.wrapper) {
            showLoader(this.wrapper);
        }
    };
    /**
     * Hides loader in the sample order details
     */
    OrderDetails.prototype.hideDetailsLoader = function () {
        if (null !== this.wrapper) {
            hideLoader(this.wrapper);
        }
    };
    /**
     * Cleans sample order details.
     *
     * @param {(null|String)} alert
     */
    OrderDetails.prototype.cleanDetails = function (alert) {
        alert = alert || null;

        this.content.empty();
        this.alert.show();
        if (null !== alert) {
            this.alert.find("strong").text(alert);
        }
    };
    /**
     * Updates popovers in details
     */
    OrderDetails.prototype.updatePopovers = function () {
        if (null === this.wrapper) {
            return;
        }

        var popovers = this.wrapper.find(this.selectors.popovers);
        if (popovers.length) {
            popovers.popover({ container: "body", trigger: "hover" });
        }
    };
    /**
     * Adds sample orders to the details.
     *
     * @param {String} order
     *
     * @returns {Promise<void>}
     */
    OrderDetails.prototype.addOrderToDetails = function (order) {
        var self = this;

        return new Promise(function (resolve) {
            if (null === order) {
                return;
            }

            var newElements = [order || null].filter(function (f) {
                return f;
            });
            if (!newElements.length) {
                return;
            }

            self.content.append(newElements.map(function (e) { return $(e); }));
            self.content.show();
            self.alert.hide();

            resolve();
        });
    };
    /**
     * Shows content in modal.
     *
     * @param {String|null} html
     * @param {String|null} title
     *
     * @returns {Promise<void>}
     */
    OrderDetails.prototype.showDetailsModal = function (html, title) {
        return new Promise(function (resolve) {
            if (typeof $.fancybox === "undefined") {
                throw new TypeError("The Fancybox must be defined in the current context.");
            }

            var width = fancyW || "auto";
            var padding = fancyP || 0;

            $.fancybox.open(
                { title: title || "", content: html || "" },
                {
                    lang: __site_lang || "en",
                    i18n: translate_js_one({ plug: "fancybox" }),
                    loop: false,
                    width: width,
                    height: "auto",
                    maxWidth: 700,
                    autoSize: false,
                    helpers: {
                        title: { type: "inside", position: "top" },
                        overlay: { locked: true },
                    },
                    modal: true,
                    closeBtn: true,
                    padding: padding,
                    closeBtnWrapper: ".fancybox-skin .fancybox-title",
                    beforeShow: function () { resolve(this); },
                    beforeLoad: function () {
                        this.width = width;
                        this.padding = [padding, padding, padding, padding];
                    },
                }
            );
        });
    };
    /**
     * Shows the sample order details.
     *
     * @param {Filters} filters
     *
     * @return {Promise<boolean|void>}
     */
    OrderDetails.prototype.showDetails = function (filters) {
        var self = this;
        if (null === filters.getFilterValue("order")) {
            return Promise.resolve(false);
        }
        if (null !== this.activateRequest) {
            this.activateRequest.xhrHandler.abort();
        }

        this.showDetailsLoader();
        this.activateRequest = postRequest(this.url, filters.toArray().filter(function (f) { return null !== f.value; }));

        return this.activateRequest
            .then(function (response) {
                var onShowDetails = function () {
                    self.updatePopovers();

                    return true;
                };

                self.activateRequest = null;
                self.cleanDetails();
                self.hideDetailsLoader();
                if (widthLessThan(detailsVisibilityBreakpoint)) {
                    return self.showDetailsModal(response.data || null, response.title || null)
                        .then(function (modal) { modal.wrap.find('.order-detail__scroll').removeClass('order-detail__scroll'); })
                        .then(onShowDetails);
                } else {
                    return self.addOrderToDetails(response.data || null).then(onShowDetails);
                }
            })
            .catch(function (error) {
                if (error && error.status && 'abort' === error.status) {
                    return;
                }
                onRequestError(error);

                self.activateRequest = null;
                self.cleanDetails();
                self.hideDetailsLoader();
            });
    };
    //#endregion Order Details

    //#region FiltersPopup
    /**
     * Controls the filters popup
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Filters} ordersFilters
     * @param {Filters} detailsFilters
     */
    function FiltersPopup(elements, selectors, ordersFilters, detailsFilters) {
        this.form = elements.searchForm;
        this.selectors = selectors;
        this.resetButton = elements.searchForm ? elements.searchForm.find(selectors.resetFilters) : null;
        this.filterButton = elements.filterButton;
        this.ordersFilters = ordersFilters;
        this.detailsFilters = detailsFilters;

        this.updateFields();
    }
    FiltersPopup.prototype.showMarker = function () {
        if (null !== this.filterButton) {
            this.filterButton.addClass("btn-filter--active");
        }
    };
    FiltersPopup.prototype.hideMarker = function () {
        if (null !== this.filterButton) {
            this.filterButton.removeClass("btn-filter--active");
        }
    };
    FiltersPopup.prototype.showResetButton = function () {
        if (null !== this.resetButton) {
            this.resetButton.css({ display: "inline-block" });
        }
    };
    FiltersPopup.prototype.hideResetButton = function () {
        if (null !== this.resetButton) {
            this.resetButton.hide();
        }
    };
    FiltersPopup.prototype.updateFields = function () {
        var assignedFilter = this.ordersFilters.getFilter("assigned");
        var keywordsFilter = this.ordersFilters.getFilter("keywords");
        var statusFilter = this.ordersFilters.getFilter("status");
        var orderFilter = this.ordersFilters.getFilter("order");
        var typeSelect = this.form.find(this.selectors.typesList);
        var keywordsField = this.form.find(this.selectors.keywords);
        var assignedStatusSelect = this.form.find(this.selectors.assignedStatus);
        var activeFormFilters = this.ordersFilters
            .toArray()
            .filter(function (f) { return "page" !== f.name; })
            .filter(function (f) { return null !== f.value; });

        if (null !== orderFilter && null !== orderFilter.value) {
            // If we have sample order then we fill it with order ID
            typeSelect.val(orderFilter.name);
            keywordsField.val(toOrderNumber(orderFilter.value));
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
    };
    FiltersPopup.prototype.applyChanges = function () {
        var typeSelect = this.form.find(this.selectors.typesList);
        var keywordsField = this.form.find(this.selectors.keywords);
        var assignedStatusSelect = this.form.find(this.selectors.assignedStatus);
        var type = typeSelect.val() || null;
        var keywords = keywordsField.val() || null;
        var assignedStatus = assignedStatusSelect.val() || null;
        var newOrdersFilters = { status: null, keywords: null, assigned: null, order: null };

        if (null !== type || null !== keywords) {
            if ("order" === type) {
                if (null === keywords) {
                    return;
                }

                newOrdersFilters.order = parseInt(keywords.replace("#", ""), 10);
                if (isNaN(newOrdersFilters.order)) {
                    return;
                }
            } else {
                newOrdersFilters.status = type;
                newOrdersFilters.keywords = keywords;
            }
        }
        if (null !== assignedStatus) {
            newOrdersFilters.assigned = parseInt(assignedStatus, 10);
        } else {
            newOrdersFilters.assigned = null;
        }

        var hasOrderFilters = Object.values(newOrdersFilters).filter(function (f) { return null !== f; }).length > 0;
        this.ordersFilters.update(newOrdersFilters);
        this.detailsFilters.update(newOrdersFilters); // To preserve content consistency
        if (hasOrderFilters) {
            this.showMarker();
            this.showResetButton();
        } else {
            this.hideMarker();
            this.hideResetButton();
        }
    };
    //#endregion FiltersPopup

    //#region Width Handling
    function WidthMonitor(statuses, initialWidth) {
        this.statuses = statuses || null;
        this.currentWidth = initialWidth || 0;
    }
    WidthMonitor.prototype.adjustTo = function (width) {
        width = width || 0;
        if (typeof width !== "number") {
            throw new TypeError("The width must be a number");
        }
        if (width === this.currentWidth) {
            return;
        }

        // Resize
        this.currentWidth = width;
        // Show statuses if need
        if (null !== this.statuses && width > statusVisibilityBreakpoint) {
            this.statuses.show();
        }
    };
    //#endregion Width Handling

    //#region Page Content
    /**
     * Page content handler.
     *
     * @param {StatusesList} statuses
     * @param {OrdersList} orders
     * @param {OrderDetails} details
     * @param {FiltersPopup} filtersPopup
     * @param {Filters} ordersFilters
     * @param {Filters} detailsFilters
     * @param {HistoryHandler} historyHandler
     */
    function PageContent(statuses, orders, details, filtersPopup, ordersFilters, detailsFilters, historyHandler) {
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
    PageContent.prototype.update = function (force) {
        force = typeof force !== 'undefined' ? Boolean(~~force) : true;
        var self = this;

        // Collect promises in stack
        var requestStack = [this.statuses.updateCounters()];
        if (force) {
            requestStack.push(this.updateOrders());
            requestStack.push(this.updateDetails());
        } else {
            if (this.ordersFilters.hasChanges()) {
                requestStack.push(this.updateOrders());
            }
            if (this.detailsFilters.hasChanges()) {
                requestStack.push(this.updateDetails());
            }
        }

        return Promise.all(requestStack).finally(function () {
            var orderFilter = self.detailsFilters.getFilterValue("order");
            if (null !== orderFilter) {
                self.orders.activateOrder(orderFilter);
            }
        });
    };
    /**
     * Shows specified content
     *
     * @returns {Promise<any[]>}
     */
    PageContent.prototype.showContent = function (order, status) {
        var self = this;
        order = order || null;
        status = status || null;
        if (null !== order || null !== status) {
            if (null !== order) {
                if (typeof order !== "number") {
                    throw new TypeError("The order must be a number");
                }

                this.detailsFilters.updateFilter("order", order);
            }

            if (null !== status) {
                if (typeof status !== "string") {
                    throw new TypeError("The status must be a string");
                }

                this.ordersFilters.updateFilter("page", 1);
                this.ordersFilters.updateFilter("status", status);
                this.detailsFilters.updateFilter("status", status); // Actually, requires the same filters except for page for content consistency
            }

            this.historyHandler.save(); // Save to history
            this.filtersPopup.updateFields(); // Update form fields
        }

        return Promise.all([this.updateOrders(), this.updateDetails(), this.statuses.updateCounters()]).finally(function () {
            if (null !== order) {
                self.orders.activateOrder(order);
            }
        });
    };
    /**
     * Shows sample order details using provided filters
     *
     * @return {Promise<boolean|void>}
     */
    PageContent.prototype.updateDetails = function () {
        var orderFilter = this.detailsFilters.getFilterValue("order");
        if (null === orderFilter) {
            this.details.cleanDetails();
            this.orders.deactivateAllOrders();

            return Promise.resolve(false);
        }
        this.orders.activateOrder(orderFilter);

        return this.details.showDetails(this.detailsFilters);
    };
    /**
     * Shows sample orders for list of filters.
     *
     * @return {Promise<void>}
     */
    PageContent.prototype.updateOrders = function () {
        var statusFilter = this.ordersFilters.getFilterValue("status");
        if (null === statusFilter) {
            this.statuses.deactiveStatus();
        } else {
            this.statuses.activeStatus(statusFilter);
        }

        return this.orders.showOrders(this.ordersFilters);
    };
    /**
     * Shows sample order for ID.
     *
     * @param {Number} orderId
     *
     * @return {Promise<[void,boolean]>}
     */
    PageContent.prototype.showDetailsForOrder = function (orderId) {
        if (typeof orderId === "undefined" || isNaN(orderId)) {
            throw new TypeError("The order ID must be a number");
        }
        this.detailsFilters.updateFilter("order", orderId);
        this.historyHandler.update(); // Update state in history
        this.filtersPopup.updateFields(); // Update form fields

        return Promise.all([this.statuses.updateCounters(), this.updateDetails()]);
    };
    /**
     * Shows sample orders for status.
     *
     * @param {(null|String)} status
     *
     * @return {Promise<[void,void]>}
     */
    PageContent.prototype.showOrdersForStatus = function (status) {
        var self = this;
        if (typeof status === "undefined") {
            throw new TypeError("The status must be defined");
        }

        // Clear orders filters
        this.ordersFilters.clear();
        // Clear details
        this.detailsFilters.clear();
        this.details.cleanDetails();

        //Update status and page filters
        this.ordersFilters.updateFilter("page", 1);
        this.ordersFilters.updateFilter("status", status);
        this.detailsFilters.updateFilter("status", status); // Actually, requires the same filters except for page for content consistency
        this.historyHandler.save(); // Save to history
        this.filtersPopup.updateFields(); // Update fields
        this.orders.deactivateAllOrders();

        return Promise.all([
            this.statuses.updateCounters(),
            this.updateOrders().then(function () {
                var currentOrder = self.detailsFilters.getFilterValue("order");
                if (null !== currentOrder) {
                    self.orders.activateOrder(currentOrder);
                } else {
                    self.orders.deactivateAllOrders();
                }
            }),
        ]);
    };
    /**
     * Shows sample orders for page.
     *
     * @param {Number} page
     *
     * @return {Promise<[void,void]>}
     */
    PageContent.prototype.showOrdersForPage = function (page) {
        var self = this;
        if (typeof page === "undefined" || isNaN(page)) {
            throw new TypeError("The page must be defined");
        }
        this.ordersFilters.updateFilter("page", page);
        this.historyHandler.save(); // Save to history
        this.filtersPopup.updateFields(); // Update foem fields

        return Promise.all([
            this.statuses.updateCounters(),
            this.updateOrders().then(function () {
                var currentOrder = self.detailsFilters.getFilterValue("order");
                if (null !== currentOrder) {
                    self.orders.activateOrder(currentOrder);
                } else {
                    self.orders.deactivateAllOrders();
                }
            }),
        ]);
    };
    /**
     * Returns the filters popup.
     *
     * @returns {FiltersPopup}
     */
    PageContent.prototype.getFiltersPopup = function () {
        return this.filtersPopup;
    };
    /**
     * Returns the history handler.
     *
     * @returns {HistoryHandler}
     */
    PageContent.prototype.getHistoryHandler = function () {
        return this.historyHandler;
    };
    /**
     * Returns the orders filters.
     *
     * @returns {Filters}
     */
    PageContent.prototype.getOrdersFilters = function () {
        return this.ordersFilters;
    };
    /**
     * Returns the details filters.
     *
     * @returns {Filters}
     */
    PageContent.prototype.getDetailsFilters = function () {
        return this.detailsFilters;
    };
    //#endregion Page Content

    //#region History handler
    /**
     * Handles the history on the page.
     *
     * @param {UrlBuilder} urlBuilder
     * @param {Filters} ordersFilters
     * @param {filters} detailsFilters
     */
    function HistoryHandler(urlBuilder, ordersFilters, detailsFilters) {
        this.urlBuilder = urlBuilder;
        this.ordersFilters = ordersFilters;
        this.detailsFilters = detailsFilters;
        this.hasPushStateSupport = "history" in global && typeof global.history.pushState !== "undefined";

        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        if (!this.hasPushStateSupport) {
            console.warn("This browser does not support pushState");
        }
    }
    HistoryHandler.prototype.save = function () {
        if (!this.hasPushStateSupport) {
            return; // Nothing to save - no feature support;
        }

        var savedState = { orders: {}, details: {} };
        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        this.ordersFilters.toArray().forEach(function (entry) { savedState.orders[entry.name] = entry.value; });
        this.detailsFilters.toArray().forEach(function (entry) { savedState.details[entry.name] = entry.value; });

        global.history.pushState(savedState, global.document.title || "", this.urlBuilder.buildUrl());
    };
    HistoryHandler.prototype.update = function () {
        if (!this.hasPushStateSupport) {
            return; // Nothing to save - no feature support;
        }

        var savedState = { orders: {}, details: {} };
        this.urlBuilder.updateMetaFromFilters(this.ordersFilters);
        this.ordersFilters.toArray().forEach(function (entry) { savedState.orders[entry.name] = entry.value; });
        this.detailsFilters.toArray().forEach(function (entry) { savedState.details[entry.name] = entry.value; });

        global.history.replaceState(savedState, global.document.title || "", this.urlBuilder.buildUrl());
    };
    HistoryHandler.prototype.restore = function (savedState) {
        savedState = Object.assign({ orders: {}, details: {} }, savedState || {});

        [
            [savedState.orders || {}, this.ordersFilters],
            [savedState.details || {}, this.detailsFilters]
        ].forEach(function (entry) {
            var state = entry[0];
            var filters = entry[1];

            // Restore state of the all page content except details
            if (Object.keys(state).length) {
                // If filters exists - we apply them
                filters.update(state);
            } else {
                // Else we clear the filters
                filters.clear();
            }
        });
    };
    //#endregion History handler

    //#region Url BUilder
    /**
     * The URL builder
     *
     * @param {(String|URL)} baseUrl
     * @param {(String|URL)} currentUrl
     * @param {Array<Fragment>} pathMetadata
     * @param {Array<Fragment>} queryMetadada
     */
    function UrlBuilder(baseUrl, currentUrl, pathMetadata, queryMetadada) {
        pathMetadata = pathMetadata || [];
        queryMetadada = queryMetadada || [];

        this.url = currentUrl instanceof URL ? currentUrl : new URL(currentUrl);
        this.baseUrl = baseUrl instanceof URL ? baseUrl : new URL(baseUrl);
        this.pathMetadata = new Map();
        this.queryMetadada = new Map();

        for (var index = 0; index < pathMetadata.length; index++) {
            var fragment = pathMetadata[index];
            this.pathMetadata.set(fragment.key, fragment);
        }
        for (var index = 0; index < queryMetadada.length; index++) {
            var fragment = queryMetadada[index];
            this.queryMetadada.set(fragment.key, fragment);
        }
    }
    /**
     * Updates URL from fitlers
     *
     * @param {Filters} filters
     */
    UrlBuilder.prototype.updateMetaFromFilters = function (filters) {
        var self = this;

        filters.toArray().forEach(function (entry) {
            self.updateMetaFragment(entry.name, entry.value);
        });
    };
    UrlBuilder.prototype.updateMetaFragment = function (name, value) {
        if (typeof name !== "string") {
            throw new TypeError("The name must be a string");
        }

        var inPath = this.pathMetadata.has(name);
        var inQuery = this.queryMetadada.has(name);
        if (!inPath && !inQuery) {
            return;
        }
        if (inPath) {
            this.pathMetadata.get(name).value = value;
        }
        if (inQuery) {
            this.queryMetadada.get(name).value = value;
        }
    };
    /**
     * Builds url.
     *
     * @returns {URL}
     */
    UrlBuilder.prototype.buildUrl = function () {
        var url = new URL(this.baseUrl.href);
        var fragments = [];
        var formatters = {
            raw: function (value) { return value; },
            entity: function (value) { return parseInt(value, 10); },
            "scalar:any": function (value) { return value; },
            "scalar:number": function (value) { return parseInt(value, 10); },
            "option:boolean": function (value) { return value ? "yes" : "no"; },
            "scalar:number:page": function (value) {
                var page = parseInt(value, 10);

                return page > 1 ? page : null;
            },
        };

        // Update URL path
        Array.from(this.pathMetadata.values()).forEach(function (meta) {
            if (null === meta.value) {
                fragments[meta.position] = null;
            } else {
                var value = typeof meta.value !== "undefined" ? meta.value : null;
                var formatter = meta.type && formatters.hasOwnProperty(meta.type) ? formatters[meta.type] : formatters.raw;
                var formattedValue = null === value ? null : formatter(meta.value);

                fragments[meta.position] = null === formattedValue ? null : [meta.name, formattedValue].join("/");
            }
        });
        fragments = fragments.filter(function (f) { return f; });
        if (fragments.length) {
            url.pathname = [url.pathname].concat(fragments).join("/");
        }

        // Clean URL query from all
        url.search = '';
        // Update URL query
        Array.from(this.queryMetadada.values()).forEach(function (meta) {
            var value = typeof meta.value !== "undefined" ? meta.value : null;
            if (null === value) {
                url.searchParams.delete(meta.name);
            } else {
                var formatter = meta.type && formatters.hasOwnProperty(meta.type) ? formatters[meta.type] : formatters.raw;
                url.searchParams.set(meta.name, formatter(value));
            }
        });

        return url;
    };
    //#endregion Url BUilder

    //#region Module
    /**
     * The sample orders page module
     * @param {any} param
     */
    function SampleOrdersDashboardModule(params) {
        /** @type {Selectors} */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type {JQueryElements} */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));
        var baseUrl = params.baseUrl || location.href;
        var ordersUrl = params.ordersUrl || null;
        var detailsUrl = params.detailsUrl || null;
        var countersUrl = params.countersUrl || null;
        var urlMetadata = params.urlMetadata || {};
        var pageTemplate = params.pageTemplate || "";
        var paginatorState = Object.assign({}, params.paginator || {});
        var pageFilters = Object.assign({}, { page: 1 }, params.filters || {});

        var detailsFilters = new Filters(defaultDetailsFilters, pageFilters);
        var ordersFilters = new Filters(defaultCotentFilters, pageFilters);
        var widthMonitor = new WidthMonitor(elements.statusesList, $(global).width());
        var paginator = new Paginator(paginatorState, elements, selectors, pageTemplate);
        var pageContent = new PageContent(
            new StatusesList(elements.statusesList, countersUrl, selectors),
            new OrdersList(ordersUrl, elements, selectors, paginator),
            new OrderDetails(detailsUrl, elements.rightBlock, elements.samplesDetails, selectors),
            new FiltersPopup(elements, selectors, ordersFilters, detailsFilters),
            ordersFilters,
            detailsFilters,
            new HistoryHandler(
                new UrlBuilder(baseUrl, location.href, urlMetadata.path || {}, urlMetadata.query || {}),
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

        //#region Dispatch listeners
        dispatchListeners(elements, selectors, paginator, pageContent, widthMonitor);
        //#endregion Dispatch listeners
    }
    SampleOrdersDashboardModule.prototype.update = function (order, status) {
        return this.pageContent.update(order || null, status || null);
    };
    SampleOrdersDashboardModule.prototype.switchTo = function (order, status) {
        return this.pageContent.showContent(order || null, status || null);
    };
    SampleOrdersDashboardModule.prototype.updateDetails = function () {
        return this.pageContent.updateDetails();
    };
    SampleOrdersDashboardModule.prototype.changeStatus = function (status) {
        return this.pageContent.showOrdersForStatus(status);
    };
    SampleOrdersDashboardModule.prototype.showOrder = function (orderId) {
        return this.pageContent.showDetailsForOrder(orderId);
    };
    SampleOrdersDashboardModule.prototype.toPage = function (page) {
        return this.pageContent.showOrdersForPage(page);
    };
    //#endregion Module

    return SampleOrdersDashboardModule;
})(globalThis);

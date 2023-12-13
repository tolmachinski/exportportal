var CreateSampleOrderPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {Object} Debounce
     * @property {Function} cancel
     * @property {Function} flush
     */

    /**
     * @typedef {Object} JQueryElements
     * @property {JQuery} form
     * @property {JQuery} searchField
     * @property {JQuery} productsList
     * @property {JQuery} selectedProducts
     * @property {JQuery} sampleDescription
     */

    /**
     * @typedef {Object} Selectors
     * @property {String} form
     * @property {String} searchField
     * @property {String} productsList
     * @property {String} selectedProducts
     * @property {String} sampleDescription
     * @property {String} productRow
     * @property {String} noProductsRow
     * @property {String} deleteProduct
     */

    /**
     * @typedef {{ ongoingSearchRequest: ?Boolean }} States
     * @typedef {{ inputHandler: ?Debounce }} Handlers
     */

    /**
     * @type {States}
     */
    var defaultState = { ongoingSearchRequest: false };

    /**
     * @type {Handlers}
     */
    var defaultHandlers = { inputHandler: null };

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, searchField: null, productsList: null, selectedProducts: null, sampleDescription: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = {
        form: null,
        searchField: null,
        productsList: null,
        selectedProducts: null,
        sampleDescription: null,
        productRow: null,
        noProductsRow: null,
        deleteProduct: null,
    };

    var descriptionLimitsOptions = {
        countDown: true,
        countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
        countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before " }),
    };

    var PRODUCTS_LIST_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
    var PRODUCTS_LIST_LAYOUT_TRESHOLD = 768;
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Handlers} handlers
     * @param {States} states
     * @param {String} productTemplate
     * @param {String} searchUrl
     */
    function dispatchListeners(elements, selectors, handlers, states, productTemplate, searchUrl) {
        var hasLodash = typeof _ !== "undefined";
        var resizeEvent = hasLodash ? "resize" : "resizetop";

        if (null !== elements.searchField) {
            handlers.inputHandler = _.debounce(
                preventDefault(function () {
                    onTextSearch.call(this, elements.productsList, searchUrl, handlers, states);
                }),
                300
            );
            elements.searchField.on("input", handlers.inputHandler);
        }

        if (null !== elements.productsList) {
            elements.productsList.on(
                "click",
                selectors.productRow,
                preventDefault(function () {
                    onChooseProduct.call(this, elements, selectors, handlers, states, productTemplate);
                })
            );
        }

        if (null !== elements.selectedProducts) {
            elements.selectedProducts.on(
                "click",
                selectors.deleteProduct,
                preventDefault(function () {
                    onRemoveProduct.call(this, elements, selectors);
                })
            );
        }

        $(global).on(resizeEvent, onContentResize(resizeEvent, hasLodash, elements));
    }

    /**
     * Adds mobile support to the content
     *
     * @param {JQueryElements} elements
     */
    function addMobileSupport(elements) {
        addMobileSupportForPorducts(elements.selectedProducts);
    }

    /**
     * Updates products table layout.
     *
     * @param {JQuery} selectedProducts
     */
    function updateProductsTableLayout(selectedProducts) {
        if (!selectedProducts || !selectedProducts.length) {
            return;
        }

        if (widthLessThan(PRODUCTS_LIST_LAYOUT_TRESHOLD)) {
            selectedProducts.addClass(PRODUCTS_LIST_LAYOUT_CLASSES);
        } else {
            selectedProducts.removeClass(PRODUCTS_LIST_LAYOUT_CLASSES);
        }
    }
    //#endregion Utility

    //#region Handlers
    /**
     *
     * @param {JQuery} productsList
     * @param {Handlers} handlers
     * @param {States} states
     */
    function onTextSearch(productsList, searchUrl, handlers, states) {
        if (states.ongoingSearchRequest && handlers.inputHandler) {
            handlers.inputHandler.cancel();
        }

        var self = $(this);
        var searchText = self.val() || null;
        if (null === searchText) {
            clearFoundProducts(productsList);

            return;
        }

        states.ongoingSearchRequest = true;
        findProducts(searchText, searchUrl, productsList).then(function () {
            states.ongoingSearchRequest = false;
        });
    }

    /**
     * Handles the click on the found products
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {String} productTemplate
     * @param {Handlers} handlers
     * @param {States} states
     */
    function onChooseProduct(elements, selectors, handlers, states, productTemplate) {
        clearSearchField(elements.searchField);
        clearFoundProducts(elements.productsList);

        var self = $(this);
        var product = self.data("product") || null;
        if (null === product || null === elements.selectedProducts || null === productTemplate) {
            return;
        }

        // In the case when we have selected products, leave
        if (elements.selectedProducts.find(selectors.productRow).length) {
            return;
        }

        elements.selectedProducts.find("tbody").append(renderTemplate(productTemplate, product));
        elements.selectedProducts.find(selectors.noProductsRow).hide();
        elements.selectedProducts.find(selectors.productRow).show();
        disableSearch(elements.searchField);
        enablesSaveButton(elements.form);
        addMobileSupportForPorducts(elements.selectedProducts);
        if (states.ongoingSearchRequest && handlers.inputHandler) {
            handlers.inputHandler.cancel();
        }
    }

    /**
     * Handles the product removal button click
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function onRemoveProduct(elements, selectors) {
        var self = $(this);

        askConfirmation(self.data("message")).then(function (result) {
            if (result.confirm) {
                self.closest(selectors.productRow).remove();
                if (null !== elements.selectedProducts && !elements.selectedProducts.find(selectors.productRow).length) {
                    elements.selectedProducts.find(selectors.noProductsRow).show();
                }

                enableSearch(elements.searchField);
                disableSaveButton(elements.form);
            }

            result.dialog.close();
        });
    }

    /**
     * Handles content resize
     *
     * @param {string} resizeEvent
     * @param {boolean} hasLodash
     * @param {JQueryElements} elements
     */
    function onContentResize(resizeEvent, hasLodash, elements) {
        var onResize = function () {
            if (!$.contains(global.document, elements.form.get(0))) {
                $(global).off(resizeEvent, onResize);

                return;
            }

            updateProductsTableLayout(elements.productsList);
        };
        if (hasLodash) {
            onResize = _.debounce(onResize, 250);
        }

        return onResize;
    }
    //#endregion Handlers

    //#region Products functions
    /**
     * Disables the search field
     *
     * @param {JQuery} searchField
     */
    function disableSearch(searchField) {
        if (null === searchField) {
            return;
        }

        searchField.prop("disabled", true);
    }

    /**
     * Enables the search field
     *
     * @param {JQuery} searchField
     */
    function enableSearch(searchField) {
        if (null === searchField) {
            return;
        }

        searchField.prop("disabled", false);
    }

    /**
     * Adds mobile support to the products table.
     *
     * @param {JQuery} selectedProducts
     */
    function addMobileSupportForPorducts(selectedProducts) {
        [selectedProducts]
            .filter(function (f) { return f; })
            .forEach(function (table) {
                if (table.length) {
                    updateProductsTableLayout(table);
                    mobileDataTable(table);
                }
            });
    }

    /**
     * Clears the list with products
     *
     * @param {JQuery} productsList
     */
    function clearFoundProducts(productsList) {
        if (null !== productsList) {
            productsList.empty();
        }
    }

    /**
     * Clears the search field
     *
     * @param {JQuery} searchField
     */
    function clearSearchField(searchField) {
        if (null !== searchField) {
            searchField.val(null);
        }
    }

    /**
     * Adds products to the list
     *
     * @param {JQuery} productsList
     * @param {Array<any>} products
     */
    function addFoundProducts(productsList, products) {
        if (null === productsList) {
            return;
        }

        productsList.append(
            (products || []).map(function (e) {
                return $(e);
            })
        );
    }

    /**
     * Finds products.
     *
     * @param {String} searchText
     * @param {String} url
     * @param {JQuery} productsList
     *
     * @return {Promise<any>}
     */
    function findProducts(searchText, url, productsList) {
        return postRequest(url, { search: searchText })
            .then(function (response) {
                if (response.message && (!response.mess_type || "success" !== response.mess_type)) {
                    systemMessages(response.message, response.mess_type || null);
                }

                clearFoundProducts(productsList);
                addFoundProducts(productsList, response.data || []);
            })
            .catch(function (error) {
                if (__debug_mode) {
                    console.error(error);
                }

                clearFoundProducts(productsList);
            });
    }
    //#endregion Products functions

    //#region Actions
    /**
     * Disables the save button
     *
     * @param {JQuery} form
     */
    function disableSaveButton(form) {
        if (null !== form) {
            form.find('button[type="submit"]').prop("disabled", true);
        }
    }

    /**
     * Enables save button
     *
     * @param {JQuery} form
     */
    function enablesSaveButton(form) {
        if (null !== form) {
            form.find('button[type="submit"]').prop("disabled", false);
        }
    }

    /**
     * Sends request thta saves the sample order.
     *
     * @param {JQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     *
     * @return {Promise<void|boolean>}
     */
    function createOrder(form, url, isDialogPopup) {
        showLoader(form);
        disableSaveButton(form);

        return postRequest(url, form.serializeArray())
            .then(function (response) {
                if (response.message) {
                    if ('success' === (response.mess_type || null) && response.urls && Array.isArray(response.urls) && response.urls.length > 0) {
                        var buttons = [
                            {
                                label: translate_js({ plug: 'BootstrapDialog', text: 'continue' }),
                                cssClass: 'btn btn-primary mnw-80',
                                action: function (dialog) { dialog.close(); }
                            }
                        ]
                        response.urls.map(function (url) {
                            buttons.push({
                                label: translate_js({ plug: 'BootstrapDialog', text: 'view_sample_order' }),
                                cssClass: 'btn btn-primary mnw-80',
                                action: function (dialog) {
                                    if ('redirect' === url.type) {
                                        location.href = url.href;
                                    }

                                    dialog.close();
                                }
                            });
                        });

                        open_result_modal({
                            content: response.message,
                            type: 'success',
                            closable: true,
                            buttons: buttons.reverse()
                        });
                    } else {
                        systemMessages(response.message, response.mess_type || null);
                    }
                }

                if (response.mess_type && "success" === response.mess_type) {
                    if (isDialogPopup) {
                        if ("BootstrapDialog" in global) {
                            closeBootstrapDialog(form);
                        }
                    } else {
                        closeFancyboxPopup();
                        dispatchCustomEvent('sample-order:order-created', globalThis);
                    }
                }

                return response.data || {};
            })
            .catch(onRequestError)
            .finally(function () {
                hideLoader(form);
                enablesSaveButton(form);
            });
    }
    //#endregion Actions

    //#region Module
    function CreateSampleOrderPopupModule(params) {
        this.productTemplate = params.productTemplate || null;
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.handlers = Object.assign({}, defaultHandlers);
        this.state = Object.assign({}, defaultState);
        this.saveUrl = params.saveUrl || null;
        this.searchUrl = params.searchUrl || null;

        if (null === this.saveUrl) {
            throw TypeError("The 'saveUrl' must be defined.");
        }

        dispatchListeners(this.elements, this.selectors, this.handlers, this.state, this.productTemplate, this.searchUrl);
        addMobileSupport(this.elements);

        if (null !== this.elements.sampleDescription) {
            this.elements.sampleDescription.textcounter(descriptionLimitsOptions);
        }
        if (null !== this.elements.selectedProducts) {
            disableSaveButton(this.elements.form);
        }
    }

    CreateSampleOrderPopupModule.prototype.search = function (text) {
        if (typeof text !== "string") {
            throw new TypeError("The text must be string");
        }

        if (!text.length) {
            clearFoundProducts(this.elements.productsList);

            return Promise.resolve();
        }

        return findProducts(text, this.searchUrl, this.elements.productsList);
    };
    CreateSampleOrderPopupModule.prototype.save = function () {
        var self = this;
        if (null !== this.elements.selectedProducts) {
            if (!this.elements.selectedProducts.find(this.selectors.productRow).length) {
                disableSaveButton(this.elements.form);

                return Promise.reject(new Error("At least one item must be selected"));
            }
        }

        return createOrder(this.elements.form, this.saveUrl, this.isDialogPopup).then(function (data) {
            if (data) {
                var themeId = null !== self.elements.form ? (self.elements.form.find('input[name="theme"]').val() || null): null;

                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:create", global, {detail: { theme: themeId, order: data.order || null }});
            }
        });
    }
    //#endregion Module

    return CreateSampleOrderPopupModule;
} (globalThis));

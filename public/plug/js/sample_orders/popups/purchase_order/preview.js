var PreviewPurchaseOrderPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, detailsWrapper: ?string, itemsTable: ?string, confirmButton: ?string }} Selectors
     * @typedef {{ form: ?JQuery, detailsWrapper: ?JQuery, itemsTable: ?JQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, detailsWrapper: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, detailsWrapper: null, itemsTable: null, confirmButton: null };

    var ITEMS_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
    var ITEMS_LAYOUT_TRESHOLD = 768;
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners(elements, selectors) {
        var hasLodash = typeof _ !== "undefined";
        var resizeEvent = hasLodash ? "resize" : "resizetop";

        $(global).on(resizeEvent, onContentResize(resizeEvent, hasLodash, elements));
    }

    /**
     * Adds mobile support.
     *
     * @param {JQuery} [detailsWrapper]
     * @param {string} [itemsTable]
     */
    function addMobileSupport(detailsWrapper, itemsTable) {
        [detailsWrapper.find(itemsTable)].forEach(function (table) {
            if (table.length) {
                updateItemsTableLayout(table);
                mobileDataTable(table);
            }
        });
    }

    /**
     * Updated items table.
     *
     * @param {JQuery} [itemsTable]
     */
    function updateItemsTableLayout(itemsTable) {
        if (!itemsTable || !itemsTable.length) {
            return;
        }

        if (widthLessThan(ITEMS_LAYOUT_TRESHOLD)) {
            itemsTable.addClass(ITEMS_LAYOUT_CLASSES);
        } else {
            itemsTable.removeClass(ITEMS_LAYOUT_CLASSES);
        }
    }
    //#endregion Utility

    //#region Handlers
    /**
     * Handles the resize event
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

            updateItemsTableLayout(elements.itemsTable);
        };
        if (hasLodash) {
            onResize = _.debounce(onResize, 250);
        }

        return onResize;
    }
    //#endregion Handlers

    //#region Confirm
    /**
     * Disables the save button
     *
     * @param {JQuery} confirmButton
     */
    function disableConfirmButton(confirmButton) {
        if (null !== confirmButton) {
            confirmButton.prop("disabled", true);
        }
    }

    /**
     * Enables save button
     *
     * @param {JQuery} confirmButton
     */
    function enableConfirmButton(confirmButton) {
        if (null !== confirmButton) {
            confirmButton.prop("disabled", false);
        }
    }

    /**
     *
     * @param {JQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     *
     * @return {Promise<void|boolean>}
     */
    function confirmPurchaseOrder(form, url, confirmButtonSelector, isDialogPopup) {
        if (null === form || null === url || null === confirmButtonSelector) {
            return Promise.reject();
        }

        var confirmButton = form.find(confirmButtonSelector);
        var order = confirmButton.data("order") || null;
        if (null === order) {
            return Promise.reject();
        }

        showLoader(form);
        disableConfirmButton(confirmButton);

        return postRequest(url, { order: order })
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type || null);
                }

                if (response.mess_type && "success" === response.mess_type) {
                    if (isDialogPopup) {
                        if ("BootstrapDialog" in global) {
                            closeBootstrapDialog(form);
                        }
                    } else {
                        closeFancyboxPopup();
                    }
                }

                return response.data || {};
            })
            .catch(onRequestError)
            .finally(function () {
                hideLoader(form);
                enableConfirmButton(confirmButton);
            });
    }
    //#endregion Confirm

    //#region Module
    function PreviewPurchaseOrderPopupModule(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.confirmUrl = params.confirmUrl || null;

        dispatchListeners(this.elements, this.selectors);
        addMobileSupport(this.elements.detailsWrapper, this.selectors.itemsTable);
    }
    PreviewPurchaseOrderPopupModule.prototype.confirmPo = function () {
        return confirmPurchaseOrder(this.elements.form, this.confirmUrl, this.selectors.confirmButton, this.isDialogPopup).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:confirm-po", global, {detail: { order: data.order || null }});
            }
        });
    }
    //#endregion Module

    return PreviewPurchaseOrderPopupModule;
} (globalThis));

var EditPurchaseOrderPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, dueDate: ?string, orderNotes: ?string, itemsList: ?string, totalAmount: ?string, priceField: ?string, activeItems: ?string }} Selectors
     * @typedef {{ form: ?JQuery, dueDate: ?JQuery, orderNotes: ?JQuery, itemsList: ?JQuery, totalAmount: ?JQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, dueDate: null, orderNotes: null, itemsList: null, totalAmount: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, dueDate: null, orderNotes: null, itemsList: null, totalAmount: null, priceField: null, activeItems: null };

    var notesLimitOptions = {
        countDown: true,
        countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
        countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before" }),
    };

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

        if (elements.itemsList && selectors.activeItems) {
            elements.itemsList.on("change", selectors.activeItems, function () {
                onTotalAmountUpdate(selectors.priceField ? elements.itemsList.find(selectors.priceField) : null, elements.totalAmount || null);
            });
        }

        $(global).on(resizeEvent, onContentResize(resizeEvent, hasLodash, elements));
    }

    /**
     * Adds mobile support.
     *
     * @param {JQuery} detailsWrapper
     * @param {JQuery} itemsTable
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
     * @param {JQuery} itemsTable
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

            updateItemsTableLayout(elements.itemsList);
        };
        if (hasLodash) {
            onResize = _.debounce(onResize, 250);
        }

        return onResize;
    }
    /**
     * Recalculates total amount.
     *
     * @param {JQuery} table
     * @param {JQuery} field
     * @param {JQuery} [total]
     */
    function onTotalAmountUpdate(priceFields, total) {
        if (null === priceFields || null === total) {
            return;
        }

        var amount = 0;
        for (var index = 0; index < priceFields.length; index++) {
            amount += parseFloat($(priceFields[index]).val() || 0, 10);
        }

        total.text(get_price(amount));
    }
    //#endregion Handlers

    //#region Save
    /**
     * Disables the save button
     */
    function disableSaveButton(form) {
        if (null !== form) {
            form.find('button[type="submit"]').prop("disabled", true);
        }
    }

    /**
     * Enables save button
     */
    function enablesSaveButton(form) {
        if (null !== form) {
            form.find('button[type="submit"]').prop("disabled", false);
        }
    }

    /**
     *
     * @param {JQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     *
     * @returns {Promise<boolean|void>}
     */
    function editPurchaseOrder(form, url, isDialogPopup) {
        if (null === form || null === url) {
            return Promise.reject();
        }
        showLoader(form);
        disableSaveButton(form);

        return postRequest(url, form.serializeArray())
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
                enablesSaveButton(form);
            });
    }
    //#endregion Save

    //#region Module
    function EditPurchaseOrderPopupModule(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.saveUrl = params.saveUrl || null;

        dispatchListeners(this.elements, this.selectors);
        addMobileSupport(this.elements.form, this.elements.itemsList);

        if (null !== this.elements.orderNotes && $.fn.textcounter) {
            this.elements.orderNotes.textcounter(notesLimitOptions);
        }
        if (null !== this.elements.dueDate && $.fn.datepicker) {
            var minimalDate = new Date();
            minimalDate.setDate(minimalDate.getDate() + 1);

            this.elements.dueDate.datepicker({
                minDate: minimalDate,
                beforeShow: function (input, instance) {
                    if (instance.dpDiv && instance.dpDiv.length) {
                        instance.dpDiv.addClass("dtfilter-ui-datepicker");
                    }
                },
            });
        }
    }

    EditPurchaseOrderPopupModule.prototype.save = function () {
        return editPurchaseOrder(this.elements.form, this.saveUrl, this.isDialogPopup).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:edit-po", global, {detail: { order: data.order || null }});
            }
        });
    };
    //#endregion Module

    return EditPurchaseOrderPopupModule;
} (globalThis));

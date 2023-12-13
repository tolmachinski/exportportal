var SampleOrderBillsPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, detailsToggle: ?string }} Selectors
     * @typedef {{ form: ?jQuery, detailsToggle: ?jQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, detailsToggle: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, detailsToggle: null };
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners (elements, selectors) {
        if (null !== elements.detailsToggle) {
            elements.detailsToggle.on("click", preventDefault(onToggleClick));
        }
    }
    //#endregion Utility

    //#region Handlers
    function onToggleClick () {
        var self = $(this);
        var toggleSelector = self.data("toggle");
        var toggleElement = $(toggleSelector);
        if (toggleElement.length) {
            toggleElement.toggle();
        }

        if (typeof $.fancybox !== "undefined") {
            $.fancybox.update();
        }
    }
    //#endregion Handlers

    //#region Module
    function SampleOrderBillsPopupModule (params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));

        dispatchListeners(this.elements, this.selectors);
    }

    SampleOrderBillsPopupModule.prototype.save = function (data) {
        var self = this;

        return Promise.resolve(data).then(function (data) {
            if (data) {
                var billId = data.bill || null;
                var orderId = null !== self.elements.form ? (self.elements.form.find('input[name="order"]').val() || null): null;

                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:pay-bill", global, {detail: { bill: billId, order: orderId }});
            }
        });
    }
    //#endregion Module

    return SampleOrderBillsPopupModule;
} (globalThis));

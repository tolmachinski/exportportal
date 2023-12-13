var AssignSampleOrderPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, numberInput: ?string }} Selectors
     * @typedef {{ form: ?jQuery, numberInput: ?jQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, numberInput: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, numberInput: null };
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners(elements, selectors) {
        if (null !== elements.numberInput) {
            elements.numberInput.on("change blur paste", preventDefault(function (e) {
                var pasteText = null;
                if (e.type === "paste") {
                    pasteText = e.originalEvent.clipboardData.getData("text");
                }
                onChangeNumber(e, $(e.currentTarget || e.target), elements.form, pasteText);
            }));
        }
    }
    //#endregion Utility

    //#region Handlers
    /**
     * Handles number change event.
     */
    function onChangeNumber(e, self, form, pasteText) {
        var number = (pasteText || self.val()).trim() || null;
        if (number !== null) {
            var formatted = toOrderNumber(number);
            if (false !== formatted) {
                self.val(formatted);
            }

            form.find('button[type="submit"]').removeClass('disabled');
        }
    }
    //#endregion Handlers

    //#region Actions
    /**
     * Assigns the sample order to the buyer and theme.
     *
     * @param {jQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     */
    function assignOrder(form, url, isDialogPopup) {
        if (null === form || null === url) {
            return Promise.reject();
        }
        showLoader(form);

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
                        dispatchCustomEvent('sample-order:order-assgined', globalThis);
                    }
                }

                return response.data || {};
            })
            .catch(onRequestError)
            .finally(function () {
                hideLoader(form);
            });
    }
    //#endregion Actions

    //#region Module
    function AssignSampleOrderPopupModule(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.assignUrl = params.assignUrl || null;

        dispatchListeners(this.elements, this.selectors);
    }

    AssignSampleOrderPopupModule.prototype.save = function () {
        var self = this;

        return assignOrder(this.elements.form, this.assignUrl, this.isDialogPopup).then(function (data) {
            if (data) {
                var themeId = null !== self.elements.form ? (self.elements.form.find('input[name="theme"]').val() || null): null;

                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:assign", global, {detail: { theme: themeId, order: data.order || null }});
            }
        });
    }
    //#endregion Module

    return AssignSampleOrderPopupModule;
} (globalThis));

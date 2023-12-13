var EditDeliveryAddressPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string }} Selectors
     * @typedef {{ form: ?jQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null };
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {any} currentLocation
     */
    function dispatchListeners(elements, selectors, currentLocation) {
        $(global).on("locations:override-location", function (e, data) {
            currentLocation.serialized = data.serialized || [];
        });
    }
    //#endregion Utility

    //#region Actions
    /**
     * Edits the address.
     *
     * @param {jQuery} form
     * @param {String} url
     * @param {any} currentLocation
     *
     * @returns {Promise<void|boolean>}
     */
    function editAddress(form, url, currentLocation) {
        if (null === form || null === url) {
            return Promise.reject();
        }
        showLoader(form);

        var formData = form
            .serializeArray()
            .concat(currentLocation.serialized || [])
            .filter(function (f) {
                return f;
            });

        return postRequest(url, formData)
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type || null);
                }

                if (response.mess_type && "success" === response.mess_type) {
                    closeFancyboxPopup();
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
    function EditDeliveryAddressPopup(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.saveUrl = params.saveUrl || null;
        this.currentLocation = params.location || {};
        this.currentLocation.serialized = Object.keys(this.currentLocation).map(function (key) {
            if (!/^.*_show$/i.test(key)) {
                return { name: key, value: this[key] ? this[key].value || null : null };
            }
        }, this.currentLocation).filter(function (f) { return f; });


        dispatchListeners(this.elements, this.selectors, this.currentLocation);
    }

    EditDeliveryAddressPopup.prototype.save = function () {
        return editAddress(this.elements.form, this.saveUrl, this.currentLocation).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:edit-address", global, {detail: { order: data.order || null }});
            }
        });
    };
    //#endregion Module

    return EditDeliveryAddressPopup;
} (globalThis));

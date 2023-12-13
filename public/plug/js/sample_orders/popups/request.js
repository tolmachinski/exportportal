var RequestSampleOrderPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, sampleDescription: ?string }} Selectors
     * @typedef {{ form: ?jQuery, sampleDescription: ?jQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, sampleDescription: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, sampleDescription: null };

    var descriptionLimitsOptions = {
        countDown: true,
        countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
        countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before " }),
    };
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
     * Sends sample order request.
     *
     * @param {jQuery} form
     * @param {String} url
     * @param {any} currentLocation
     *
     * @returns {Promise<void|boolean>}
     */
    function sendRequest(form, url, currentLocation) {
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
                    if ("success" === (response.mess_type || null)) {
                        var buttons = [];

                        if (response.urls && Array.isArray(response.urls)) {
                            response.urls.map(function (url) {
                                buttons.push({
                                    label: translate_js({ plug: "BootstrapDialog", text: "view_sample_order" }),
                                    cssClass: "btn btn-primary",
                                    action: function (dialog) {
                                        if ("redirect" === url.type) {
                                            location.href = url.href;
                                        }

                                        dialog.close();
                                    },
                                });
                            });
                        }

                        buttons.push({
                            label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                            cssClass: "btn btn-light",
                            action: function (dialog) {
                                dialog.close();
                            }
                        });

                        open_result_modal({
                            title: "Success!",
                            subTitle: response.message,
                            type: "success",
                            closable: true,
                            buttons: buttons
                        });
                    } else {
                        systemMessages(response.message, response.mess_type || null);
                    }
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
    function RequestSampleOrderPopupModule(params) {
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

        if (null === this.saveUrl) {
            throw TypeError("The 'saveUrl' must be defined.");
        }

        dispatchListeners(this.elements, this.selectors, this.currentLocation);

        if (null !== this.elements.sampleDescription) {
            this.elements.sampleDescription.textcounter(descriptionLimitsOptions);
        }
    }
    RequestSampleOrderPopupModule.prototype.save = function () {
        return sendRequest(this.elements.form, this.saveUrl, this.currentLocation).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:request", global, {detail: { order: data.order || null }});
            }
        });
    };
    //#endregion Module

    return RequestSampleOrderPopupModule;
} (globalThis));

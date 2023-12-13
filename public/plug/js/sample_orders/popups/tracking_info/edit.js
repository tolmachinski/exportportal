var EditTrackingInfoPopupModule = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{ form: ?string, deliveryDate: ?string, trackingInfo: ?string }} Selectors
     * @typedef {{ form: ?jQuery, deliveryDate: ?jQuery, trackingInfo: ?jQuery }} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, deliveryDate: null, trackingInfo: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, deliveryDate: null, trackingInfo: null };

    var trackingTextLimitOptions = {
        countDown: true,
        countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
        countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before" }),
    };
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners(elements, selectors) {
        // Nothing here traveller. Your code is in another castle
    }
    //#endregion Utility

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
     * @param {jQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     *
     * @returns {Promise<boolean|void>}
     */
    function editTrackingInfo(form, url, isDialogPopup) {
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
    function EditTrackingInfoPopup(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        this.elements = Object.assign({}, defaultElements, findElementsFromSelectors(this.selectors, Object.keys(defaultElements)));
        this.saveUrl = params.saveUrl || null;

        if (null !== this.elements.trackingInfo && $.fn.textcounter) {
            this.elements.trackingInfo.textcounter(trackingTextLimitOptions);
        }
        if (null !== this.elements.deliveryDate && $.fn.datepicker) {
            var minimalDate = new Date();
            minimalDate.setDate(minimalDate.getDate() + 1);

            this.elements.deliveryDate.datepicker({
                minDate: minimalDate,
                beforeShow: function (input, instance) {
                    if (instance.dpDiv && instance.dpDiv.length) {
                        instance.dpDiv.addClass("dtfilter-ui-datepicker");
                    }
                },
            });
        }

        dispatchListeners(this.elements, this.selectors);
    }

    EditTrackingInfoPopup.prototype.save = function () {
        return editTrackingInfo(this.elements.form, this.saveUrl, this.isDialogPopup).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:edit-tracking-info", global, {detail: { order: data.order || null }});
            }
        });
    };
    //#endregion Module

    return EditTrackingInfoPopup;
} (globalThis));

import $ from "jquery";
import { closeBootstrapDialog } from "@src/plugins/bootstrap-dialog/index";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { hideLoader, showLoader } from "@src/util/common/loader";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";

import { dispatchEvent, preventDefault } from "@src/util/events";
import { toPadedNumber } from "@src/util/number";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

class AssignSampleOrderPopupModule {
    /**
     * @typedef {{ form: ?string, numberInput: ?string }} Selectors
     * @typedef {{ form: ?JQuery, numberInput: ?JQuery }} JQueryElements
     */

    constructor(params) {
        this.global = globalThis;

        /**
         * @type {JQueryElements}
         */
        this.defaultElements = { form: null, numberInput: null };

        /**
         * @type {Selectors}
         */
        this.defaultSelectors = { form: null, numberInput: null };

        this.AssignSampleOrderPopupModule(params);
    }

    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     */
    dispatchListeners(elements) {
        const that = this;

        if (elements.numberInput !== null) {
            elements.numberInput.on(
                // @ts-ignore
                "change blur paste",
                preventDefault(e => {
                    let pasteText = null;

                    if (e.type === "paste") {
                        pasteText = e.originalEvent.clipboardData.getData("text");
                    }
                    that.onChangeNumber(e, $(e.currentTarget || e.target), elements.form, pasteText);
                })
            );
        }
    }

    /**
     * Handles number change event.
     */
    // eslint-disable-next-line class-methods-use-this
    onChangeNumber(e, target, form, pasteText) {
        const number = (pasteText || target.val()).trim();

        if (number) {
            const formatted = toPadedNumber(number);
            target.val(formatted);

            form.find(`button[type="submit"]`).removeClass("disabled");
        }
    }

    /**
     * Assigns the sample order to the buyer and theme.
     *
     * @param {JQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     */
    // eslint-disable-next-line class-methods-use-this
    assignOrder(form, url, isDialogPopup) {
        if (form === null || url === null) {
            return Promise.reject();
        }
        showLoader(form);

        return postRequest(url, form.serializeArray())
            .then(response => {
                if (response.message) {
                    systemMessages(response.message, response.mess_type || null);
                }

                if (response.mess_type && response.mess_type === "success") {
                    if (isDialogPopup) {
                        if ("BootstrapDialog" in global) {
                            closeBootstrapDialog(form);
                        }
                    } else {
                        closeFancyboxPopup();
                        dispatchEvent("sample-order:order-assgined", globalThis);
                    }
                }

                return response.data || {};
            })
            .catch(handleRequestError)
            .finally(() => hideLoader(form));
    }

    AssignSampleOrderPopupModule(params) {
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = { ...this.defaultSelectors, ...(params.selectors || {}) };
        this.elements = { ...this.defaultElements, ...findElementsFromSelectors(this.selectors, Object.keys(this.defaultElements)) };
        this.assignUrl = params.assignUrl || null;

        this.dispatchListeners(this.elements);
    }

    async save() {
        const that = this;

        const data = await that.assignOrder(this.elements.form, this.assignUrl, this.isDialogPopup);
        if (data) {
            const themeId = that.elements.form !== null ? that.elements.form.find('input[name="theme"]').val() || null : null;

            // Using jQuery as Event Hub to communicate with other components
            dispatchEvent("sample-orders:assign", that.global, { detail: { theme: themeId, order: data.order || null } });
        }
    }
}

/**
 * @param {any} params
 */
export default params => {
    const create = new AssignSampleOrderPopupModule(params);

    EventHub.off("sample-order:asign");
    EventHub.on("sample-order:asign", () => create.save());
};

import $ from "jquery";

import { openInfoDialog100 } from "@src/plugins/bootstrap-dialog/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";
import mobileDataTable from "@src/util/common/mobile-data-table";
import mix from "@src/util/common/mix";

import existCookie from "@src/util/cookies/exist-cookie";
import setCookie from "@src/util/cookies/set-cookie";
import { dispatchEvent, preventDefault } from "@src/util/events";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import { systemMessages } from "@src/util/system-messages/index";
import debounce from "lodash/debounce";

class SampleOrderDetailsModule {
    /**
     * @typedef {Object} JQueryElements
     * @property {JQuery} statusActions
     * @property {JQuery} detailsWrapper
     * @property {JQuery} productsTable
     */

    /**
     * @typedef {Object} Selectors
     * @property {String} statusActions
     * @property {String} detailsWrapper
     * @property {String} productsTable
     * @property {String} dialogClose
     * @property {String} statusModal
     * @property {String} checkboxes
     * @property {String} infoDialog
     * @property {String} statusText
     */

    constructor(params) {
        this.global = globalThis;

        /**
         * @type {JQueryElements}
         */
        this.defaultElements = { statusActions: null, detailsWrapper: null, productsTable: null };

        /**
         * @type {Selectors}
         */
        this.defaultSelectors = {
            statusActions: null,
            detailsWrapper: null,
            productsTable: null,
            statusText: null,
            infoDialog: null,
            statusModal: null,
            dialogClose: null,
            checkboxes: null,
        };

        this.PRODUCTS_LIST_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
        this.PRODUCTS_LIST_LAYOUT_TRESHOLD = 425;

        this.SampleOrderDetailsModule(params);
    }

    /**
     * Dispatches the listneres on the page.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    dispatchListeners(elements, selectors) {
        const that = this;
        const resizeEvent = "resize";

        if (elements.detailsWrapper !== null) {
            elements.detailsWrapper.on(
                "click",
                selectors.infoDialog,
                preventDefault(function onOpenSteps() {
                    that.onOpenStepDescription.call(this, elements, selectors, that);
                })
            );
        }

        $(that.global).on(resizeEvent, that.onContentResize(resizeEvent, elements));
    }

    /**
     * Updates products table layout.
     *
     * @param {JQuery} [detailsWrapper]
     * @param {JQuery} [productsTable]
     */
    updateProductsTableLayout(detailsWrapper, productsTable) {
        if (!detailsWrapper || !detailsWrapper.length || !productsTable || !productsTable.length) {
            return;
        }

        if (detailsWrapper.width() < this.PRODUCTS_LIST_LAYOUT_TRESHOLD) {
            productsTable.addClass(this.PRODUCTS_LIST_LAYOUT_CLASSES);
        } else {
            productsTable.removeClass(this.PRODUCTS_LIST_LAYOUT_CLASSES);
        }
    }

    /**
     * Checks if popup with step description must be shown
     *
     * @param {JQuery} detailsWrapper
     * @param {String} infoDialog
     */
    // eslint-disable-next-line class-methods-use-this
    checkIfShowStepDescription(detailsWrapper, infoDialog) {
        if (detailsWrapper === null) {
            return;
        }

        const desriptionButtons = detailsWrapper.find(infoDialog);

        if (!existCookie("_ep_view_order_sample_status")) {
            if (desriptionButtons.length) {
                desriptionButtons.first().trigger("click");
            }
        }
    }

    /**
     * Adds mobile support to the content
     *
     * @param {JQuery} detailsWrapper
     * @param {JQuery} productsTable
     */
    addMobileSupport(detailsWrapper, productsTable) {
        if (!detailsWrapper || !detailsWrapper.length) {
            return;
        }

        const that = this;

        [detailsWrapper.find(productsTable)].forEach(table => {
            if (table.length) {
                that.updateProductsTableLayout(detailsWrapper, table);
                mobileDataTable(table);
            }
        });
    }

    /**
     * Handles opening of the step description
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    onOpenStepDescription(elements, selectors, that) {
        const self = $(this);
        const storedMessage = self.data("message") || null;
        const storedContent = self.data("content") || null;
        const storedActions = self.data("actions") || null;
        let message = "";
        let actions = "";

        if (storedMessage !== null) {
            message = storedMessage;
        } else if (storedContent !== null) {
            message = ($(storedContent).html() || "").trim();
        }

        if (storedActions !== null) {
            actions = ($(storedActions).html() || "").trim();
        }

        openInfoDialog100({
            title: self.data("title"),
            message,
            actions,
            function(dialog) {
                that.onOpenDialog(dialog, elements, selectors);
            },
        });
    }

    /**
     * Handles opening of the Boostrap Dialog
     *
     * @param {Dialog} dialog
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    onOpenDialog(dialog, elements, selectors) {
        const that = this;
        const adapter = new that.DialogAdapter(dialog, elements.statusActions, {
            statusModal: selectors.statusModal,
            checkboxes: selectors.checkboxes,
            dialogClose: selectors.dialogClose,
        });

        adapter.attachListners();
    }

    /**
     * Handles content resize
     *
     * @param {string} resizeEvent
     * @param {JQueryElements} elements
     */
    onContentResize(resizeEvent, elements) {
        const that = this;
        let onResize = function () {
            if (!$.contains(that.global.document, elements.detailsWrapper.get(0))) {
                $(that.global).off(resizeEvent, onResize);

                return;
            }

            that.updateProductsTableLayout(elements.detailsWrapper, elements.productsTable);
        };

        onResize = debounce(onResize, 250);

        return onResize;
    }

    /**
     * Confirms order delivery.
     *
     * @param {Number} [orderId]
     * @param {String} [url]
     * @param {JQuery} [detailsWrapper]
     *
     * @return {Promise<void>}
     */
    // eslint-disable-next-line class-methods-use-this
    confirmOrderDelivery(orderId, url, detailsWrapper) {
        if (orderId === null || url === null || detailsWrapper === null) {
            return Promise.reject();
        }

        showLoader(detailsWrapper);

        return postRequest(url, { order: orderId })
            .then(response => {
                if (response.message) {
                    systemMessages(response.message, response.mess_type || null);

                    if (response.data.openSurveyPopup) {
                        $("body").append(
                            '<a id="js-sample-order-survey" class="display-n_i fancybox fancybox.iframe" href="https://survey.zohopublic.com/zs/a4CszR" data-title="Complete the survey" data-w="100%" data-h="95%"></a>'
                        );
                        setTimeout(() => {
                            const popupSelectors = ".fancybox-overlay, .bootstrap-dialog";

                            if ($(popupSelectors).length === 0) {
                                $("#js-sample-order-survey").trigger("click");
                            } else {
                                const checkFancybox = setInterval(() => {
                                    if ($(popupSelectors).length === 0) {
                                        clearInterval(checkFancybox);
                                        $("#js-sample-order-survey").trigger("click");
                                    }
                                }, 1000);
                            }
                        }, 500);
                    }
                }

                return response.data || {};
            })
            .catch(handleRequestError)
            .finally(() => {
                hideLoader(detailsWrapper);
            });
    }

    /**
     * Boostrap Dialog adapter.
     *
     * @param {Dialog} dialog
     * @param {JQuery} actions
     * @param {{statusModal: ?string}} selectors
     */
    DialogAdapter(dialog, actions, selectors) {
        this.dialog = dialog;
        this.wrapper = dialog.getModal();
        this.actions = actions || null;
        this.hideModal = false;
        this.selectors = selectors;
        this.consentWrapper = dialog.getModal().find(this.selectors.statusModal);

        this.wrapper.addClass("bs-modal-dialog--sample-orders--details");
        this.wrapper
            .data("bs.modal")
            .getGlobalOpenedDialogs()
            .forEach(d => {
                if (d === dialog || !d.getModal().hasClass("bs-modal-dialog--sample-orders--details")) {
                    return;
                }
                d.close();
            });
    }

    attachListners() {
        const self = this;

        this.consentWrapper.on("ifChecked", () => {
            self.hideModal = true;
        });
        this.consentWrapper.on("ifUnchecked", () => {
            self.hideModal = false;
        });
        this.consentWrapper.on(
            "click",
            this.selectors.dialogClose,
            preventDefault(() => {
                self.close();
            })
        );
    }

    close() {
        if (this.hideModal && !existCookie("_ep_view_order_sample_status")) {
            if (this.actions !== null) {
                this.actions.empty();
            }

            setCookie("_ep_view_order_sample_status", 1, { expires: 7 });
        }

        this.dialog.close();
    }

    /**
     * The samples details module
     * @param {any} params
     */
    SampleOrderDetailsModule(params) {
        const that = this;

        /** @type {Selectors} */
        const selectors = { ...this.defaultSelectors, ...(params.selectors || {}) };
        /** @type {JQueryElements} */
        const elements = { ...this.defaultElements, ...findElementsFromSelectors(selectors, Object.keys(this.defaultElements)) };
        const confirmUrl = params.confirmUrl || null;
        if (confirmUrl === null) {
            throw TypeError("The parameter 'confirmUrl' must be defined.");
        }

        that.elements = elements;
        that.selectors = selectors;
        that.confirmUrl = confirmUrl;

        that.dispatchListeners(elements, selectors);
        that.checkIfShowStepDescription(elements.detailsWrapper, selectors.infoDialog);
        setTimeout(() => {
            that.addMobileSupport(elements.detailsWrapper, elements.productsTable);
        }, 0);
    }

    showDialog(dialog) {
        const that = this;

        that.onOpenDialog(dialog, that.elements, that.selectors);
    }

    confirmDelivery(orderId) {
        const that = this;

        return that.confirmOrderDelivery(orderId, this.confirmUrl, this.elements.detailsWrapper).then(data => {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchEvent("sample-orders:confirm-delivery", that.global, { detail: { order: data.order || null } });
            }
        });
    }
}

export default params => {
    const sampleDetails = new SampleOrderDetailsModule(params);

    mix(
        globalThis,
        {
            confirmDelivery: button => {
                sampleDetails.confirmDelivery(button.data("order") || null);
            },
        },
        false
    );
};

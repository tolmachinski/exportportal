var SampleOrderDetailsModule = (function (global) {
    "use strict";

    //#region Variables
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

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { statusActions: null, detailsWrapper: null, productsTable: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = {
        statusActions: null,
        detailsWrapper: null,
        productsTable: null,
        statusText: null,
        infoDialog: null,
        statusModal: null,
        dialogClose: null,
        checkboxes: null,
    };

    var PRODUCTS_LIST_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
    var PRODUCTS_LIST_LAYOUT_TRESHOLD = 425;
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listneres on the page.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners(elements, selectors) {
        var hasLodash = typeof _ !== "undefined";
        var resizeEvent = hasLodash ? "resize" : "resizetop";

        if (null !== elements.detailsWrapper) {
            elements.detailsWrapper.on("click", selectors.infoDialog, preventDefault(function () {
                onOpenStepDescription.call(this, elements, selectors);
            }));
        }

        $(global).on(resizeEvent, onContentResize(resizeEvent, hasLodash, elements));
    }

    /**
     * Updates products table layout.
     *
     * @param {JQuery} [detailsWrapper]
     * @param {JQuery} [productsTable]
     */
    function updateProductsTableLayout(detailsWrapper, productsTable) {
        if (!detailsWrapper || !detailsWrapper.length || !productsTable || !productsTable.length) {
            return;
        }

        if (detailsWrapper.width() < PRODUCTS_LIST_LAYOUT_TRESHOLD) {
            productsTable.addClass(PRODUCTS_LIST_LAYOUT_CLASSES);
        } else {
            productsTable.removeClass(PRODUCTS_LIST_LAYOUT_CLASSES);
        }
    }

    /**
     * Checks if popup with step description must be shown
     *
     * @param {JQuery} detailsWrapper
     * @param {String} infoDialog
     */
    function checkIfShowStepDescription(detailsWrapper, infoDialog) {
        if (null === detailsWrapper) {
            return;
        }

        var desriptionButtons = detailsWrapper.find(infoDialog);
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
    function addMobileSupport(detailsWrapper, productsTable) {
        if (!detailsWrapper || !detailsWrapper.length) {
            return;
        }

        [detailsWrapper.find(productsTable)].forEach(function (table) {
            if (table.length) {
                updateProductsTableLayout(detailsWrapper, table);
                mobileDataTable(table);
            }
        });
    }
    //#endregion Utility

    //#region Handlers
    /**
     * Handles opening of the step description
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function onOpenStepDescription(elements, selectors) {
        if (!("open_info_dialog_100" in global)) {
            throw new SyntaxError("The function 'open_info_dialog_100' is not defined.");
        }

        var self = $(this);
        var storedMessage = self.data("message") || null;
        var storedContent = self.data("content") || null;
        var storedActions = self.data("actions") || null;
        var message = "";
        var actions = "";

        if (null !== storedMessage) {
            message = storedMessage;
        } else if (null !== storedContent) {
            message = ($(storedContent).html() || "").trim();
        }

        if (null !== storedActions) {
            actions = ($(storedActions).html() || "").trim();
        }

        open_info_dialog_100(self.data("title"), message, actions, function (dialog) {
            onOpenDialog(dialog, elements, selectors);
        });
    }

    /**
     * Handles opening of the Boostrap Dialog
     *
     * @param {Dialog} dialog
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function onOpenDialog(dialog, elements, selectors) {
        var adapter = new DialogAdapter(dialog, elements.statusActions, {
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
     * @param {boolean} hasLodash
     * @param {JQueryElements} elements
     */
    function onContentResize(resizeEvent, hasLodash, elements) {
        var onResize = function () {
            if (!$.contains(global.document, elements.detailsWrapper.get(0))) {
                $(global).off(resizeEvent, onResize);

                return;
            }

            updateProductsTableLayout(elements.detailsWrapper, elements.productsTable);
        };
        if (hasLodash) {
            onResize = _.debounce(onResize, 250);
        }

        return onResize;
    }
    //#endregion Handlers

    //#region Actions
    /**
     * Confirms order delivery.
     *
     * @param {Number} [orderId]
     * @param {String} [url]
     * @param {JQuery} [detailsWrapper]
     *
     * @return {Promise<void>}
     */
    function confirmOrderDelivery(orderId, url, detailsWrapper) {
        if (null === orderId || null === url || null === detailsWrapper) {
            return Promise.reject();
        }
        showLoader(detailsWrapper);

        return postRequest(url, { order: orderId })
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type || null);

                    if(response.data.openSurveyPopup && !__disable_popup_system){
                        // NEW POPUP CALL order_sample_survey
                        dispatchCustomEvent("popup:call-popup", globalThis, {detail: { name: "order_sample_survey" }});
                    }
                }

                return response.data || {};
            })
            .catch(onRequestError)
            .finally(function () {
                hideLoader(detailsWrapper);
            });
    }
    //#endregion Actions

    //#region Module
    //#region Adapter
    /**
     * Boostrap Dialog adapter.
     *
     * @param {Dialog} dialog
     * @param {JQuery} actions
     * @param {{statusModal: ?string}} selectors
     */
    function DialogAdapter(dialog, actions, selectors) {
        this.dialog = dialog;
        this.wrapper = dialog.getModal();
        this.actions = actions || null;
        this.hideModal = false;
        this.selectors = selectors;
        this.consentWrapper = dialog.getModal().find(this.selectors.statusModal);

        this.wrapper.addClass('bs-modal-dialog--sample-orders--details');
        this.wrapper.data('bs.modal').getGlobalOpenedDialogs().forEach(function (d) {
            if (d === dialog || !d.getModal().hasClass('bs-modal-dialog--sample-orders--details')) {
                return;
            }

            d.close();
        });
    }

    Object.assign(DialogAdapter.prototype, {
        attachListners: function () {
            var self = this;

            this.consentWrapper.on(
                "click",
                this.selectors.dialogClose,
                preventDefault(function () {
                    self.hideModal = $('.modal .js-dont-show-more').prop('checked');
                    self.close();
                })
            );
        },
        close: function () {
            if (this.hideModal && !existCookie("_ep_view_order_sample_status")) {
                if (null !== this.actions) {
                    this.actions.empty();
                }

                setCookie("_ep_view_order_sample_status", 1, 7);
            }

            this.dialog.close();
        },
    });
    //#endregion Adapter

    /**
     * The samples details module

     * @param {any} param
     */
    function SampleOrderDetailsModule(params) {
        /** @type {Selectors} */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type {JQueryElements} */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));
        var confirmUrl = params.confirmUrl || null;
        if (null === confirmUrl) {
            throw TypeError("The parameter 'confirmUrl' must be defined.");
        }

        this.elements = elements
        this.selectors = selectors;
        this.confirmUrl = confirmUrl;

        dispatchListeners(elements, selectors);
        checkIfShowStepDescription(elements.detailsWrapper, selectors.infoDialog);
        setTimeout(function () {
            addMobileSupport(elements.detailsWrapper, elements.productsTable);
        }, 0);
    }

    SampleOrderDetailsModule.prototype.showDialog = function (dialog) {
        onOpenDialog(dialog, this.elements, this.selectors);
    };
    SampleOrderDetailsModule.prototype.confirmDelivery = function (orderId) {
        return confirmOrderDelivery(orderId, this.confirmUrl, this.elements.detailsWrapper).then(function (data) {
            if (data) {
                // Using jQuery as Event Hub to communicate with other components
                dispatchCustomEvent("sample-orders:confirm-delivery", global, {detail: { order: data.order || null }});
            }
        });
    };
    //#endregion Module

    return SampleOrderDetailsModule;
})(globalThis);

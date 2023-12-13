import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";
import mobileDataTable from "@src/util/common/mobile-data-table";

import { askConfirmation, closeBootstrapDialog, openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { dispatchEvent, preventDefault } from "@src/util/events";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import { renderTemplate } from "@src/util/templates";
import { addCounter } from "@src/plugins/textcounter/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import debounce from "lodash/debounce";
import EventHub from "@src/event-hub";

import "@scss/components/popups/create_sample_order/index.scss";

class CreateSampleOrderPopupModule {
    /**
     * @typedef {Object} Debounce
     * @property {Function} cancel
     * @property {Function} flush
     */

    /**
     * @typedef {Object} JQueryElements
     * @property {JQuery} form
     * @property {JQuery} searchField
     * @property {JQuery} productsList
     * @property {JQuery} selectedProducts
     * @property {JQuery} sampleDescription
     */

    /**
     * @typedef {Object} Selectors
     * @property {String} form
     * @property {String} searchField
     * @property {String} productsList
     * @property {String} selectedProducts
     * @property {String} sampleDescription
     * @property {String} productRow
     * @property {String} noProductsRow
     * @property {String} deleteProduct
     */

    /**
     * @typedef {{ ongoingSearchRequest: ?Boolean }} States
     * @typedef {{ inputHandler: ?Debounce }} Handlers
     */

    constructor(params) {
        this.global = globalThis;

        /**
         * @type {States}
         */
        this.defaultState = { ongoingSearchRequest: false };

        /**
         * @type {Handlers}
         */
        this.defaultHandlers = { inputHandler: null };

        /**
         * @type {JQueryElements}
         */
        this.defaultElements = { form: null, searchField: null, productsList: null, selectedProducts: null, sampleDescription: null };

        /**
         * @type {Selectors}
         */
        this.defaultSelectors = {
            form: null,
            searchField: null,
            productsList: null,
            selectedProducts: null,
            sampleDescription: null,
            productRow: null,
            noProductsRow: null,
            deleteProduct: null,
        };

        this.descriptionLimitsOptions = {
            countDown: true,
            countDownTextAfter: translate({ plug: "textcounter", text: "count_down_text_after" }),
            countDownTextBefore: translate({ plug: "textcounter", text: "count_down_text_before " }),
        };

        this.PRODUCTS_LIST_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
        this.PRODUCTS_LIST_LAYOUT_TRESHOLD = 768;

        this.CreateSampleOrderPopupModule(params);
    }

    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {Handlers} handlers
     * @param {States} states
     * @param {String} productTemplate
     * @param {String} searchUrl
     */
    dispatchListeners(elements, selectors, handlers, states, productTemplate, searchUrl) {
        const that = this;
        const resizeEvent = "resize";

        if (elements.searchField !== null) {
            handlers.inputHandler = debounce(
                preventDefault(function () {
                    that.onTextSearch.call(this, elements.productsList, searchUrl, handlers, states, that);
                }),
                300
            );
            elements.searchField.on("input", handlers.inputHandler);
        }

        if (elements.productsList !== null) {
            elements.productsList.on(
                "click",
                selectors.productRow,
                preventDefault(function () {
                    that.onChooseProduct.call(this, elements, selectors, handlers, states, productTemplate, that);
                })
            );
        }

        if (elements.selectedProducts !== null) {
            elements.selectedProducts.on(
                "click",
                selectors.deleteProduct,
                preventDefault(function () {
                    that.onRemoveProduct.call(this, elements, selectors, that);
                })
            );
        }

        $(that.global).on(resizeEvent, that.onContentResize(resizeEvent, elements));
    }

    /**
     * Adds mobile support to the content
     *
     * @param {JQueryElements} elements
     */
    addMobileSupport(elements) {
        this.addMobileSupportForPorducts(elements.selectedProducts);
    }

    /**
     * Updates products table layout.
     *
     * @param {JQuery} selectedProducts
     */
    updateProductsTableLayout(selectedProducts) {
        const that = this;

        if (!selectedProducts || !selectedProducts.length) {
            return;
        }

        if (globalThis.innerWidth < that.PRODUCTS_LIST_LAYOUT_TRESHOLD) {
            selectedProducts.addClass(that.PRODUCTS_LIST_LAYOUT_CLASSES);
        } else {
            selectedProducts.removeClass(that.PRODUCTS_LIST_LAYOUT_CLASSES);
        }
    }

    /**
     *
     * @param {JQuery} productsList
     * @param {Handlers} handlers
     * @param {States} states
     */
    onTextSearch(productsList, searchUrl, handlers, states, that) {
        const self = $(this);
        const searchText = self.val() || null;

        if (states.ongoingSearchRequest && handlers.inputHandler) {
            handlers.inputHandler.cancel();
        }

        if (searchText === null) {
            that.clearFoundProducts(productsList);

            return;
        }

        states.ongoingSearchRequest = true;
        that.findProducts(searchText, searchUrl, productsList);
    }

    /**
     * Handles the click on the found products
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     * @param {String} productTemplate
     * @param {Handlers} handlers
     * @param {States} states
     */
    onChooseProduct(elements, selectors, handlers, states, productTemplate, that) {
        that.clearSearchField(elements.searchField);
        that.clearFoundProducts(elements.productsList);

        const self = $(this);
        const product = self.data("product") || null;
        if (product === null || elements.selectedProducts === null || productTemplate === null) {
            return;
        }

        // In the case when we have selected products, leave
        if (elements.selectedProducts.find(selectors.productRow).length) {
            return;
        }

        elements.selectedProducts.find("tbody").append(renderTemplate(productTemplate, product));
        elements.selectedProducts.find(selectors.noProductsRow).hide();
        elements.selectedProducts.find(selectors.productRow).show();
        that.disableSearch(elements.searchField);
        that.enablesSaveButton(elements.form);
        that.addMobileSupportForPorducts(elements.selectedProducts);
        if (states.ongoingSearchRequest && handlers.inputHandler) {
            handlers.inputHandler.cancel();
        }
    }

    /**
     * Handles the product removal button click
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    onRemoveProduct(elements, selectors, that) {
        const self = $(this);

        askConfirmation(self.data("message")).then(function (result) {
            if (result.confirm) {
                self.closest(selectors.productRow).remove();
                if (elements.selectedProducts !== null && !elements.selectedProducts.find(selectors.productRow).length) {
                    elements.selectedProducts.find(selectors.noProductsRow).show();
                }

                that.enableSearch(elements.searchField);
                that.disableSaveButton(elements.form);
            }

            result.dialog.close();
        });
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
            if (!$.contains(that.global.document, elements.form.get(0))) {
                $(that.global).off(resizeEvent, onResize);

                return;
            }

            that.updateProductsTableLayout(elements.productsList);
        };

        onResize = debounce(onResize, 250);

        return onResize;
    }

    /**
     * Disables the search field
     *
     * @param {JQuery} searchField
     */
    disableSearch(searchField) {
        const that = this;

        if (searchField === null) {
            return;
        }

        searchField.prop("disabled", true);
    }

    /**
     * Enables the search field
     *
     * @param {JQuery} searchField
     */
    enableSearch(searchField) {
        const that = this;

        if (searchField === null) {
            return;
        }

        searchField.prop("disabled", false);
    }

    /**
     * Adds mobile support to the products table.
     *
     * @param {JQuery} selectedProducts
     */
    addMobileSupportForPorducts(selectedProducts) {
        const that = this;

        [selectedProducts]
            .filter(function (f) {
                return f;
            })
            .forEach(function (table) {
                if (table.length) {
                    that.updateProductsTableLayout(table);
                    mobileDataTable(table);
                }
            });
    }

    /**
     * Clears the list with products
     *
     * @param {JQuery} productsList
     */
    clearFoundProducts(productsList) {
        const that = this;

        if (productsList !== null) {
            productsList.empty();
        }
    }

    /**
     * Clears the search field
     *
     * @param {JQuery} searchField
     */
    clearSearchField(searchField) {
        const that = this;

        if (searchField !== null) {
            searchField.val(null);
        }
    }

    /**
     * Adds products to the list
     *
     * @param {JQuery} productsList
     * @param {Array<any>} products
     */
    addFoundProducts(productsList, products) {
        const that = this;

        if (productsList === null) {
            return;
        }

        productsList.append(
            (products || []).map(function (e) {
                return $(e);
            })
        );
    }

    /**
     * Finds products.
     *
     * @param {String} searchText
     * @param {String} url
     * @param {JQuery} productsList
     *
     * @return {Promise<any>}
     */
    async findProducts(searchText, url, productsList) {
        const that = this;

        return postRequest(url, { search: searchText })
            .then(function (response) {
                if (response.message && (!response.mess_type || response.mess_type !== "success")) {
                    systemMessages(response.message, response.mess_type || null);
                }

                that.clearFoundProducts(productsList);
                that.addFoundProducts(productsList, response.data || []);
            })
            .catch(error => {
                handleRequestError(error);
                that.clearFoundProducts(productsList);
            });
    }

    /**
     * Disables the save button
     *
     * @param {JQuery} form
     */
    disableSaveButton(form) {
        const that = this;

        if (form !== null) {
            form.find('button[type="submit"]').prop("disabled", true);
        }
    }

    /**
     * Enables save button
     *
     * @param {JQuery} form
     */
    enablesSaveButton(form) {
        const that = this;

        if (form !== null) {
            form.find('button[type="submit"]').prop("disabled", false);
        }
    }

    /**
     * Sends request thta saves the sample order.
     *
     * @param {JQuery} form
     * @param {String} url
     * @param {Boolean} isDialogPopup
     *
     * @return {Promise<void|boolean>}
     */
    createOrder(form, url, isDialogPopup) {
        const that = this;

        showLoader(form);
        this.disableSaveButton(form);

        return postRequest(url, form.serializeArray())
            .then(function (response) {
                if (response.message) {
                    if ((response.mess_type || null) === "success" && response.urls && Array.isArray(response.urls) && response.urls.length > 0) {
                        const buttons = [
                            {
                                label: translate({ plug: "BootstrapDialog", text: "continue" }),
                                cssClass: "btn btn-primary mnw-80",
                                action(dialog) {
                                    dialog.close();
                                },
                            },
                        ];
                        // eslint-disable-next-line func-names
                        response.urls.map(function (url) {
                            buttons.push({
                                label: translate({ plug: "BootstrapDialog", text: "view_sample_order" }),
                                cssClass: "btn btn-primary mnw-80",
                                action(dialog) {
                                    if (url.type === "redirect") {
                                        globalThis.location.href = url.href;
                                    }

                                    dialog.close();
                                },
                            });

                            return true;
                        });

                        openResultModal({
                            content: response.message,
                            type: "success",
                            closable: true,
                            buttons: buttons.reverse(),
                        });
                    } else {
                        systemMessages(response.message, response.mess_type || null);
                    }
                }

                if (response.mess_type && response.mess_type === "success") {
                    if (isDialogPopup) {
                        if ("BootstrapDialog" in that.global) {
                            closeBootstrapDialog(form);
                        }
                    } else {
                        closeFancyboxPopup();
                        dispatchEvent("sample-order:order-created", that.global);
                    }
                }

                return response.data || {};
            })
            .catch(handleRequestError)
            .finally(function () {
                hideLoader(form);
                that.enablesSaveButton(form);
            });
    }

    CreateSampleOrderPopupModule(params) {
        this.productTemplate = document.getElementById("create-sample--form-template--product").innerText || null;
        this.isDialogPopup = typeof params.isDialogPopup !== "undefined" ? Boolean(~~params.isDialogPopup) : false;
        this.selectors = { ...this.defaultSelectors, ...(params.selectors || {}) };
        this.elements = { ...this.defaultElements, ...findElementsFromSelectors(this.selectors, Object.keys(this.defaultElements)) };
        this.handlers = { ...this.defaultHandlers };
        this.state = { ...this.defaultState };
        this.saveUrl = params.saveUrl || null;
        this.searchUrl = params.searchUrl || null;

        if (this.saveUrl === null) {
            throw TypeError("The 'saveUrl' must be defined.");
        }

        this.dispatchListeners(this.elements, this.selectors, this.handlers, this.state, this.productTemplate, this.searchUrl);
        this.addMobileSupport(this.elements);

        if (this.elements.sampleDescription !== null) {
            addCounter(this.elements.sampleDescription);
        }
        if (this.elements.selectedProducts !== null) {
            this.disableSaveButton(this.elements.form);
        }
    }

    search(text) {
        if (typeof text !== "string") {
            throw new TypeError("The text must be string");
        }

        if (!text.length) {
            this.clearFoundProducts(this.elements.productsList);

            return Promise.resolve();
        }

        return this.findProducts(text, this.searchUrl, this.elements.productsList);
    }

    save() {
        const that = this;

        if (this.elements.selectedProducts !== null) {
            if (!this.elements.selectedProducts.find(this.selectors.productRow).length) {
                this.disableSaveButton(this.elements.form);

                return Promise.reject(new Error("At least one item must be selected"));
            }
        }

        return this.createOrder(this.elements.form, this.saveUrl, this.isDialogPopup).then(function (data) {
            if (data) {
                const themeId = that.elements.form !== null ? that.elements.form.find('input[name="theme"]').val() || null : null;

                // Using jQuery as Event Hub to communicate with other components
                dispatchEvent("sample-orders:create", that.global, { detail: { theme: themeId, order: data.order || null } });
            }
        });
    }
}

export default params => {
    const create = new CreateSampleOrderPopupModule(params);

    EventHub.off("sample-order:create");
    EventHub.on("sample-order:create", () => create.save());
};

import $ from "jquery";

import debounce from "lodash/debounce";
import { preventDefault } from "@src/util/events";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { askConfirmation, closeAllDialogs, openResultModal, closeBootstrapDialog } from "@src/plugins/bootstrap-dialog/index";
import { renderTemplate } from "@src/util/templates";
import { translate } from "@src/i18n";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

class CreateFeatureItemsPopupModule {
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
     */

    /**
     * @typedef {Object} Selectors
     * @property {String} form
     * @property {String} searchField
     * @property {String} productsList
     * @property {String} selectedProducts
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
        this.defaultElements = {
            form: null,
            searchField: null,
            productsList: null,
            selectedProducts: null,
            selectedProductsWrapper: null,
            btnCancel: null,
            btnSubmit: null,
        };

        /**
         * @type {Selectors}
         */
        this.defaultSelectors = {
            form: null,
            searchField: null,
            productsList: null,
            selectedProducts: null,
            selectedProductsWrapper: null,
            productRow: null,
            noProductsRow: null,
            deleteProduct: null,
            btnCancel: null,
            btnSubmit: null,
        };

        this.excludeItems = [];

        this.saveUrl = "";
        this.maxItems = 0;

        this.CreateFeatureItemsPopupModule(params);
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

        $("body").on(
            "click",
            selectors.btnCancel,
            preventDefault(function () {
                closeAllDialogs();
            })
        );

        $("body").on(
            "click",
            selectors.btnSubmit,
            preventDefault(function () {
                that.onSubmitModal.call(this, elements, selectors, that);
            })
        );

        $("body").on(
            "submit",
            selectors.form,
            preventDefault(function () {})
        );
    }

    // eslint-disable-next-line class-methods-use-this
    onSubmitModal(elements, selectors, that) {
        if (elements.selectedProducts !== null && !elements.selectedProducts.find(selectors.productRow).length) {
            that.disableSaveButton(elements.btnSubmit);
            systemMessages("At least one item must be selected", "error");
        } else {
            that.saveFeatureItems(elements.form, that.saveUrl, elements.btnSubmit);
        }
    }

    /**
     *
     * @param {JQuery} productsList
     * @param {Handlers} handlers
     * @param {States} states
     */
    onTextSearch(productsList, searchUrl, handlers, states, that) {
        if (states.ongoingSearchRequest && handlers.inputHandler) {
            handlers.inputHandler.cancel();
        }

        const self = $(this);
        const searchText = self.val() || null;
        if (searchText === null) {
            that.clearFoundProducts(productsList);
            return;
        }

        states.ongoingSearchRequest = true;
        that.findProducts(searchText, searchUrl, productsList).then(function () {
            states.ongoingSearchRequest = false;
        });
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
        const id = self.data("id") || null;
        const idproduct = self.data("idproduct") || null;

        if (product === null || elements.selectedProducts === null || productTemplate === null) {
            return;
        }

        // In the case when we have selected products, leave
        if (elements.selectedProducts.find(selectors.productRow).length === that.maxItems) {
            return;
        }

        if (elements.selectedProducts.find(`${selectors.productRow}[data-item="${id}"]`).length) {
            return;
        }

        elements.selectedProducts.append(renderTemplate(productTemplate, product));
        elements.selectedProducts.find(selectors.noProductsRow).hide();
        elements.selectedProducts.find(selectors.productRow).show();
        elements.selectedProductsWrapper.removeClass("visible-h");

        if (elements.selectedProducts.find(selectors.productRow).length === that.maxItems) {
            that.disableSearch(elements.searchField);
        }

        that.excludeItems.push(idproduct);
        that.enablesSaveButton(elements.btnSubmit);
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
        const idproduct = self.closest(".js-product").data("idproduct") || null;

        askConfirmation(self.data("message")).then(function (result) {
            if (result.confirm) {
                self.closest(selectors.productRow).remove();
                if (elements.selectedProducts !== null && !elements.selectedProducts.find(selectors.productRow).length) {
                    elements.selectedProducts.find(selectors.noProductsRow).show();
                }

                if (!elements.selectedProducts.find(selectors.productRow).length) {
                    that.disableSaveButton(elements.btnSubmit);
                    elements.selectedProductsWrapper.addClass("visible-h");
                }

                const indexProduct = that.excludeItems.indexOf(idproduct);
                if (indexProduct > -1) {
                    that.excludeItems.splice(indexProduct, 1);
                }
                that.enableSearch(elements.searchField);
            }

            result.dialog.close();
        });
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

        return postRequest(url, { search: searchText, excludeItems: that.excludeItems })
            .then(function (response) {
                if (response.message && (!response.mess_type || response.mess_type !== "success")) {
                    systemMessages(response.message, response.mess_type || null);
                }

                that.clearFoundProducts(productsList);
                that.addFoundProducts(productsList, response.data || []);
            })
            .catch(function (error) {
                handleRequestError(error);
                that.clearFoundProducts(productsList);
            });
    }

    /**
     * Disables the save button
     *
     * @param {JQuery} form
     */
    disableSaveButton(btnSubmit) {
        const that = this;

        if (btnSubmit !== null) {
            btnSubmit.prop("disabled", true);
        }
    }

    /**
     * Enables save button
     *
     * @param {JQuery} form
     */
    enablesSaveButton(btnSubmit) {
        const that = this;

        if (btnSubmit !== null) {
            btnSubmit.prop("disabled", false);
        }
    }

    /**
     * Sends request thta saves the feature items.
     *
     * @param {JQuery} form
     * @param {String} url
     *
     * @return {Promise<void|boolean>}
     */
    saveFeatureItems(form, url, btnSubmit) {
        const that = this;

        showLoader(form);
        that.disableSaveButton(btnSubmit);

        postRequest(url, form.serializeArray())
            .then(function (response) {
                if (response.message) {
                    if (response.mess_type === "success") {
                        openResultModal({
                            content: response.message,
                            type: "success",
                            closable: true,
                            buttons: [
                                {
                                    label: translate({ plug: "BootstrapDialog", text: "ok" }),
                                    cssClass: "btn btn-light",
                                    action(dialog) {
                                        dialog.close();
                                    },
                                },
                            ],
                        });
                    } else {
                        systemMessages(response.message, response.mess_type || null);
                    }
                }

                if (response.mess_type && response.mess_type === "success") {
                    if ("BootstrapDialog" in global) {
                        closeBootstrapDialog(form);
                    }
                }

                return response.data || {};
            })
            .catch(handleRequestError)
            .finally(function () {
                hideLoader(form);
                that.enablesSaveButton(btnSubmit);
            });
    }

    CreateFeatureItemsPopupModule(params) {
        this.productTemplate = document.getElementById("js-modal-select-featured-items-product").innerText || null;
        this.selectors = { ...this.defaultSelectors, ...(params.selectors || {}) };
        this.elements = { ...this.defaultElements, ...findElementsFromSelectors(this.selectors, Object.keys(this.defaultElements)) };
        this.handlers = { ...this.defaultHandlers };
        this.state = { ...this.defaultState };
        this.saveUrl = params.saveUrl || null;
        this.maxItems = parseInt(params.maxItems, 10) || 3;
        this.searchUrl = params.searchUrl || null;

        if (this.saveUrl === null) {
            throw TypeError("The 'saveUrl' must be defined.");
        }

        this.dispatchListeners(this.elements, this.selectors, this.handlers, this.state, this.productTemplate, this.searchUrl);

        if (this.elements.selectedProducts !== null) {
            this.disableSaveButton(this.elements.btnSubmit);
        }
    }
}

export default params => {
    // eslint-disable-next-line no-new
    new CreateFeatureItemsPopupModule(params);
};

import $ from "jquery";
import { open } from "@src/plugins/fancybox/v2/index";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

class OrderDetails {
    /**
     * @typedef {Object} Selectors
     */
    /**
     *
     * @param {String} detailsUrl
     * @param {jQuery} wrapper
     * @param {jQuery} details
     * @param {Selectors} selectors
     */
    constructor(detailsUrl, wrapper, details, selectors) {
        if (details === null) {
            throw new TypeError("The details element must be defined");
        }
        if (detailsUrl === null) {
            throw new TypeError("The details URL must be defined");
        }

        this.url = detailsUrl;
        this.selectors = selectors;
        this.wrapper = wrapper;
        this.details = details;
        this.content = details.find(selectors.detailsContent);
        this.alert = details.find(selectors.detailsAlert);
        this.activateRequest = null;
        this.detailsVisibilityBreakpoint = 992;
    }

    /**
     * Shows loader in the sample order details
     */
    showDetailsLoader() {
        if (this.wrapper !== null) {
            showLoader(this.wrapper);
        }
    }

    /**
     * Hides loader in the sample order details
     */
    hideDetailsLoader() {
        if (this.wrapper !== null) {
            hideLoader(this.wrapper);
        }
    }

    /**
     * Cleans sample order details.
     *
     * @param {(null|String)} alert
     */
    cleanDetails(alert) {
        const alertB = alert || null;

        this.content.empty();
        this.alert.show();
        if (alertB !== null) {
            this.alert.find("strong").text(alert);
        }
    }

    /**
     * Updates popovers in details
     */
    updatePopovers() {
        if (this.wrapper === null) {
            return;
        }

        const popovers = this.wrapper.find(this.selectors.popovers);
        if (popovers.length) {
            popovers.popover({ container: "body", trigger: "hover" });
        }
    }

    /**
     * Adds sample orders to the details.
     *
     * @param {String} order
     *
     * @returns {Promise<void>}
     */
    addOrderToDetails(order) {
        const self = this;

        return new Promise(resolve => {
            if (order === null) {
                return;
            }

            const newElements = [order || null].filter(f => f);
            if (!newElements.length) {
                return;
            }

            self.content.append(newElements.map(e => $(e)));
            self.content.show();
            self.alert.hide();

            resolve();
        });
    }

    /**
     * Shows content in modal.
     *
     * @param {String|null} html
     * @param {String|null} title
     *
     * @returns {Promise<void>}
     */
    // eslint-disable-next-line class-methods-use-this
    showDetailsModal(html, title) {
        return new Promise(resolve => {
            const adjustments = calculateModalBoxSizes();

            const width = adjustments.width || "auto";
            const padding = adjustments.gutter || 0;

            open(
                {
                    title: title || "",
                    content: html || "",
                    closeBtn: true,
                },
                {
                    width,
                    height: "auto",
                    padding,
                    beforeShow() {
                        resolve(this);
                    },
                }
            );
        });
    }

    /**
     * Shows the sample order details.
     *
     * @param {Filters} filters
     *
     * @return {Promise<boolean|void>}
     */
    showDetails(filters) {
        const self = this;
        if (filters.getFilterValue("order") === null) {
            return Promise.resolve(false);
        }
        if (this.activateRequest !== null) {
            this.activateRequest.xhrHandler.abort();
        }

        this.showDetailsLoader();
        this.activateRequest = postRequest(
            this.url,
            filters.toArray().filter(f => f.value !== null)
        );

        return this.activateRequest
            .then(response => {
                const onShowDetails = function () {
                    self.updatePopovers();

                    return true;
                };

                self.activateRequest = null;
                self.cleanDetails();
                self.hideDetailsLoader();
                if (globalThis.innerWidth < self.detailsVisibilityBreakpoint) {
                    return self
                        .showDetailsModal(response.data || null, response.title || null)
                        .then(modal => {
                            modal.wrap.find(".order-detail__scroll").removeClass("order-detail__scroll");
                        })
                        .then(onShowDetails);
                }

                return self.addOrderToDetails(response.data || null).then(onShowDetails);
            })
            .catch(error => {
                if (error && error.status && error.status === "abort") {
                    return;
                }
                handleRequestError(error);

                self.activateRequest = null;
                self.cleanDetails();
                self.hideDetailsLoader();
            });
    }
}

export default OrderDetails;

import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

class OrdersList {
    /**
     * @typedef {Object} Selectors
     * @typedef {Object} JQueryElements
     */
    /**
     * The sample orders list handler
     * @param {String} listUrl
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    constructor(listUrl, elements, selectors, paginator) {
        this.list = elements.samplesList;
        this.label = elements.samplesLabel;
        this.paginator = paginator;
        this.listWrapper = elements.centerBlock;
        this.listUrl = listUrl || null;
        this.selectors = selectors;
        this.listAlert = this.list.find(selectors.listAlert);
    }

    /**
     * Shows loader in the sample orders list
     */
    showListLoader() {
        if (this.listWrapper !== null) {
            showLoader(this.listWrapper);
        }
    }

    /**
     * Hides loader in the sample orders list
     */
    hideListLoader() {
        if (this.listWrapper !== null) {
            hideLoader(this.listWrapper);
        }
    }

    cleanOrderList() {
        if (this.list === null) {
            return;
        }

        const alertThis = null;
        const alertChild = this.listAlert;
        const otherChildren = this.list.find(this.selectors.listElement).not(alertChild);

        otherChildren.remove();
        alertChild.show();
        if (alertThis !== null) {
            alertChild.find("strong").text(alertThis);
        }
    }

    /**
     * Adds sample orders to the list.
     *
     * @param {Array<String>|null} orders
     */
    addOrdersToList(orders) {
        if (this.list === null) {
            return;
        }

        const newElements = orders || [];
        if (!newElements.length) {
            return;
        }

        this.listAlert.before(newElements.map(e => $(e)));
        this.listAlert.hide();
    }

    /**
     * Makes the sample order element in list active
     *
     * @param {Number} orderId
     */
    activateOrder(orderId) {
        if (orderId === null || this.list === null) {
            return;
        }

        const currentOrder = this.list.find(`li[data-order="${orderId}"]`);
        if (currentOrder.length) {
            currentOrder.siblings().filter(".active").removeClass("active");
            currentOrder.addClass("active");
        }
    }

    /**
     * Makes the sample order element in list inactive
     *
     * @param {Number} orderId
     */
    deactivateOrder(orderId) {
        if (orderId === null || this.list === null) {
            return;
        }

        const currentOrder = this.list.find(`li[data-order="${orderId}"]`);
        if (currentOrder.length) {
            currentOrder.removeClass("active");
        }
    }

    /**
     * Deactivates all orders in the list.
     */
    deactivateAllOrders() {
        if (this.list === null) {
            return;
        }

        const orders = this.list.find("li[data-order]");
        if (orders.length) {
            orders.removeClass("active");
        }
    }

    fetchOrders(filters) {
        const that = this;
        if (that.listUrl === null) {
            return Promise.resolve(false);
        }
        that.showListLoader();

        return postRequest(
            that.listUrl,
            filters.toArray().filter(f => f.value !== null)
        )
            .then(response => {
                that.cleanOrderList();
                that.addOrdersToList(response.data || []);
                that.paginator.updateState(response.paginator || {});

                return true;
            })
            .catch(error => {
                handleRequestError(error);

                that.cleanOrderList();
                that.label.text(that.label.data("textNotFound"));
            })
            .finally(() => {
                that.hideListLoader();
            });
    }

    showOrders(filters) {
        const self = this;
        const hasLabel = this.label !== null;
        let labelText = hasLabel ? this.label.data("textAll") : null;
        const currentStatus = filters.getFilterValue("status") || null;
        if (currentStatus && currentStatus.value !== null) {
            labelText = this.list.find(`li[data-status="${currentStatus.value}"]`).find(this.selectors.statusTitle).text();
        }

        return this.fetchOrders(filters).then(isSuccessfull => {
            if (isSuccessfull && hasLabel) {
                self.label.text(labelText);

                const activeOrder = filters.getFilterValue("order") || null;
                if (activeOrder !== null) {
                    self.activateOrder(activeOrder);
                }
            }
        });
    }
}

export default OrdersList;

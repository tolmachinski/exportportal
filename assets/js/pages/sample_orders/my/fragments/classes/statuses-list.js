import { DEBUG } from "@src/common/constants";
import getRequest from "@src/util/http/get-request";

class StatusesList {
    constructor(list, listUrl, selectors) {
        if (list === null) {
            throw new TypeError("The statuses list must be defined");
        }
        if (listUrl === null) {
            throw new TypeError("The list URL must be defined");
        }

        this.list = list;
        this.listUrl = listUrl;
        this.selectors = selectors;
    }

    activeStatus(status) {
        if (status === null) {
            return;
        }

        const statusThis = this.list.find(`li[data-status="${status}"]`);
        statusThis.siblings().filter(".active").removeClass("active");
        statusThis.addClass("active");
    }

    deactiveStatus() {
        this.list.find("li[data-status]").removeClass("active");
        this.list.find("li[data-status='all']").addClass("active");
    }

    /**
     * Updates the statuses of the counters
     *
     * @return {Promise<void>}
     */
    updateCounters() {
        const self = this;

        return getRequest(this.listUrl)
            .then(data => {
                const counters = data.counters || {};
                Object.keys(counters).forEach(key => {
                    if (Object.prototype.hasOwnProperty.call(counters, key)) {
                        const counter = self.list.find(`li[data-status="${key}"]`).find(self.selectors.statusCounters);
                        counter.text(counters[key] || 0);
                    }
                });
            })
            .catch(error => {
                if (DEBUG) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }
            });
    }
}

export default StatusesList;

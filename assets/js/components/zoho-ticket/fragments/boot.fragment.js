import $ from "jquery";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import EventHub from "@src/event-hub";

export default () => {
    globalThis.ZohoHCAsapReady = o => {
        // eslint-disable-next-line camelcase, no-cond-assign
        if (((globalThis.ZohoHCAsap__asyncalls = globalThis.ZohoHCAsap__asyncalls || []), globalThis.ZohoHCAsapReadyStatus)) {
            if (o) globalThis.ZohoHCAsap__asyncalls.push(o);
            for (let a = globalThis.ZohoHCAsap__asyncalls, s = 0; s < a.length; s += 1) {
                if (a[s]) a[s]();
            }

            // eslint-disable-next-line camelcase
            globalThis.ZohoHCAsap__asyncalls = null;
        } else if (o) globalThis.ZohoHCAsap__asyncalls.push(o);
    };

    const zohoTicketLoader = (button, show = false) => {
        const target = $(button).find("i");
        const classes = "ep-icon_updates rotate-circle";
        if (show) {
            target.addClass(classes);
        } else {
            target.removeClass(classes);
        }
    };

    const loadingZohoTicket = button => {
        return new Promise(resolve => {
            if (globalThis.ZohoHCAsap) {
                resolve();
            } else {
                globalThis.ZohoHCAsap = (a, b) => {
                    globalThis.ZohoHCAsap[a] = b;
                };
                const s = document.createElement("script");
                s.defer = true;
                s.src = "https://desk.zoho.com/portal/api/web/inapp/421682000005314003?orgId=694613358";
                document.body.appendChild(s);
                zohoTicketLoader(button, true);
                globalThis.ZohoHCAsapReady(() => {
                    globalThis.ZohoHCAsap.Action("hideLauncher");
                    resolve();
                });
            }
        });
    };

    EventHub.on("zoho-ticket:open", (e, button) => {
        e.preventDefault();
        loadingZohoTicket(button).then(() => {
            zohoTicketLoader(button);
            globalThis.ZohoHCAsap.Action("open");
        });
    });
    if (!BACKSTOP_TEST_MODE) {
        setTimeout(() => {
            loadingZohoTicket();
        }, 10000);
    }
};

import $ from "jquery";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";

import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import "jquery-countdown";

const showMaintenanceBanner = function () {
    const url = "maintenance/show_maintenance_banner";

    return postRequest(url)
        .then(resp => {
            if (resp.html) {
                $("#js-ep-header-fixed-top").prepend(
                    `<div id="js-maintenance-banner-container" class="maintenance-banner-container animate">
                        ${resp.html}
                    </div>`
                );
                $(".ep-header").addClass("ep-header--maintenance");
                $("html").addClass("html--maintenance");
            }
        })
        .catch(handleRequestError);
};

const checkMaintenanceMode = () => {
    if (globalThis.Worker) {
        // eslint-disable-next-line no-underscore-dangle
        const mmWorker = new Worker(`${SUBDOMAIN_URL}public/plug/js/maintenance-mode/maintenance_worker.js`);

        mmWorker.postMessage({ url: SITE_URL, messtype: "init" });
        mmWorker.onmessage = e => {
            if (e.data.mode === "on") {
                if (e.data.is_started === false) {
                    if ($("#js-maintenance-banner").length === 0) {
                        showMaintenanceBanner();
                    }
                    mmWorker.postMessage({ messtype: "close" });
                } else if (e.data.is_started === true && e.data.reload === true) {
                    globalThis.location.reload();
                }
            }
        };
    }
};

export default checkMaintenanceMode;

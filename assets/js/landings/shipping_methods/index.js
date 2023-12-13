import $ from "jquery";
import readMoreText from "@src/plugins/read-more-text/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import EventHub from "@src/event-hub";
import offResizeCallback from "@src/util/dom/off-resize-callback";

import "@scss/landings/shipping_methods/index.scss";

const addListenersOnResizePage = selector => {
    offResizeCallback();
    onResizeCallback(() => {
        readMoreText(selector);
    });
};

$(() => {
    const selector = ".js-read-more";
    readMoreText(selector);
    addListenersOnResizePage(selector);

    EventHub.off("shipping-method:scroll-to");
    EventHub.on("shipping-method:scroll-to", async (e, button) => {
        const { default: clickToScroll } = await import("@src/landings/shipping_methods/click-to-scroll");
        clickToScroll(button);
    });

    // Download PDF guide
    EventHub.on("best-practices:download-pdf", async (e, button) => {
        const { default: downloadBestPracticesGuide } = await import("@src/pages/home/fragments/download-best-practices-guide");
        downloadBestPracticesGuide(button);
    });
});

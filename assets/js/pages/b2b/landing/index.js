import "@scss/user_pages/b2b/landing/index.scss";
import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import openAddRequestPopup from "@src/pages/b2b/landing/fragments/add-request-popup";
import EventHub from "@src/event-hub";

const lazyLoadingsSections = async sliderNodeList => {
    const loadAllSections = async () => {
        (await import("@src/pages/b2b/landing/fragments/search-by-country")).default();
        return true;
    };

    if ("IntersectionObserver" in window && !BACKSTOP_TEST_MODE) {
        // Or if we see section
        const lazyLoadingSlider = new IntersectionObserver(
            changes => {
                changes.forEach(async change => {
                    if (change.isIntersecting && !$(change.target).hasClass("slick-initialized")) {
                        // @ts-ignore
                        (await import("@src/pages/b2b/landing/fragments/search-by-country")).default();
                    }
                });
            },
            // preload pixel if desktop 50% of height or 300px if is tablet or mobile device
            { rootMargin: window.matchMedia("(min-width: 1199px)").matches ? "50%" : "300px" }
        );
        document.querySelectorAll(sliderNodeList).forEach(slider => lazyLoadingSlider.observe(slider));
    } else {
        await loadAllSections();
    }
};

$(() => {
    EventHub.off("b2b:add-request");
    EventHub.on("b2b:add-request", (e, btn) => openAddRequestPopup(btn));
    lazyLoadingsSections(".js-search-by-country");
});

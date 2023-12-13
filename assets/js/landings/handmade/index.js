import $ from "jquery";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import "@scss/user_pages/category/handmade_landing/index.scss";

const lazyLoadingsSections = async sliderNodeList => {
    const loadAllSections = async () => {
        (await import("@src/landings/handmade/fragments/latest-slider")).default();
        (await import("@src/landings/handmade/fragments/most-popular-slider")).default();

        return true;
    };

    if ("IntersectionObserver" in window && !BACKSTOP_TEST_MODE) {
        // Or if we see section
        const lazyLoadingSlider = new IntersectionObserver(
            changes => {
                changes.forEach(async change => {
                    if (change.isIntersecting && !$(change.target).hasClass("slick-initialized")) {
                        // @ts-ignore
                        switch (change.target?.dataset.lazyName) {
                            case "latest-items":
                                (await import("@src/landings/handmade/fragments/latest-slider")).default();
                                (await import("@src/landings/handmade/fragments/most-popular-slider")).default();
                                break;
                            default:
                                break;
                        }
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
    lazyLoadingsSections(".js-latest-items, .js-most-popular");
});

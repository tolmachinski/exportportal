import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import "@scss/user_pages/learn_more/index.scss";

const lazyLoadingsSections = async sliderNodeList => {

    const loadAllSections = async () => {
        (await import("@src/pages/learn_more/fragments/check-slider")).default();
        (await import("@src/pages/learn_more/fragments/tour-slider")).default();

        return true;
    };

    if ("IntersectionObserver" in window) {
        // Or if we see section
        const lazyLoadingSlider = new IntersectionObserver(
            changes => {
                changes.forEach(async change => {
                    if (change.isIntersecting && !$(change.target).hasClass("slick-initialized")) {
                        // @ts-ignore
                        switch (change.target?.dataset.lazyName) {
                            case "learnmore-check":
                                (await import("@src/pages/learn_more/fragments/check-slider")).default();
                                break;
                            case "learnmore-slider-tour":
                                (await import("@src/pages/learn_more/fragments/tour-slider")).default();
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
    lazyLoadingsSections(
        ".js-learnmore-check, .js-learnmore-slider-tour"
    );

    $(".js-subscribe-block-form-btn").on("click", () => {
        setTimeout(() => {
            $(".js-subscribe-block-form-input").trigger("focus");
        }, 100);
    });
});

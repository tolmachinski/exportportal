import $ from "jquery";

import showSuccessSubscribtionPopupIfNeeded from "@src/components/popups/subscribe/index";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import firstContentPaintBanner from "@src/pages/home/fragments/first-content-paint-banner";

import "@scss/user_pages/index_page/index_page.scss";

showSuccessSubscribtionPopupIfNeeded();

const lazyLoadingsSections = async sliderNodeList => {
    const loadAllSections = async () => {
        (await import("@src/pages/home/fragments/updates-from-ep-slider")).default();
        (await import("@src/pages/home/fragments/blog-slider")).default();
        (await import("@src/pages/home/fragments/featured-slider")).default();
        (await import("@src/pages/home/fragments/latest-slider")).default();
        (await import("@src/pages/home/fragments/top-products-slider")).default();
        (await import("@src/pages/home/fragments/exclusive-deals")).default();
        (await import("@src/pages/home/fragments/picks-of-month")).default();

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
                            case "updates-from-ep":
                                (await import("@src/pages/home/fragments/updates-from-ep-slider")).default();
                                break;
                            case "blogs":
                                (await import("@src/pages/home/fragments/blog-slider")).default();
                                break;
                            case "featured-items":
                                (await import("@src/pages/home/fragments/featured-slider")).default();
                                break;
                            case "latest-items":
                                (await import("@src/pages/home/fragments/latest-slider")).default();
                                break;
                            case "top-products":
                                (await import("@src/pages/home/fragments/top-products-slider")).default();
                                break;
                            case "exclusive-deals":
                                (await import("@src/pages/home/fragments/exclusive-deals")).default();
                                break;
                            case "picks-of-month":
                                (await import("@src/pages/home/fragments/picks-of-month")).default();
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
    firstContentPaintBanner();
    lazyLoadingsSections(
        ".js-updates-from-ep, .js-featured-items, #js-blogs-slider, .js-latest-items, .js-top-products, .js-exclusive-deals, .js-picks-of-month"
    );

    const url = new URL(globalThis.location.href);
    // Check if exist hash for order call requests
    if (url.searchParams.get("order_call")) {
        setTimeout(() => {
            $(".js-order-call-btn").trigger("click");
        }, 500);
    }
});

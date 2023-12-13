import $ from "jquery";
import EventHub from "@src/event-hub";

import showSuccessSubscribtionPopupIfNeeded from "@src/components/popups/subscribe/index";
import firstContentPaintBanner from "@src/pages/home/fragments/first-content-paint-banner";

import "@scss/user_pages/index_page/logged_index_page.scss";

showSuccessSubscribtionPopupIfNeeded();

const lazyLoadingsSections = async sliderNodeList => {
    const loadAllSections = async () => {
        (await import("@src/pages/home/fragments/featured-slider")).default();
        (await import("@src/pages/home/fragments/just-for-you-slider")).default();
        (await import("@src/pages/home/fragments/latest-slider-logged")).default();
        (await import("@src/pages/home/fragments/latest-slider")).default();
        (await import("@src/pages/home/fragments/updates-from-ep-slider")).default();
        (await import("@src/pages/home/fragments/picks-of-month")).default();
        (await import("@src/pages/home/fragments/blog-slider")).default();
        (await import("@src/pages/home/fragments/top-products-slider")).default();
        (await import("@src/pages/home/fragments/exclusive-deals")).default();
        (await import("@src/pages/home/fragments/reviews-slider")).default();
        (await import("@src/pages/home/fragments/freight-forwarders-magazine-slider")).default();

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
                            case "featured-items":
                                (await import("@src/pages/home/fragments/featured-slider")).default();
                                break;
                            case "just-for-you":
                                (await import("@src/pages/home/fragments/just-for-you-slider")).default();
                                break;
                            case "latest-items-logged":
                                (await import("@src/pages/home/fragments/latest-slider-logged")).default();
                                break;
                            case "latest-items":
                                (await import("@src/pages/home/fragments/latest-slider")).default();
                                break;
                            case "updates-from-ep":
                                (await import("@src/pages/home/fragments/updates-from-ep-slider")).default();
                                break;
                            case "picks-of-month":
                                (await import("@src/pages/home/fragments/picks-of-month")).default();
                                break;
                            case "blogs":
                                (await import("@src/pages/home/fragments/blog-slider")).default();
                                break;
                            case "customer-reviews":
                                (await import("@src/pages/home/fragments/reviews-slider")).default();
                                break;
                            case "top-products":
                                (await import("@src/pages/home/fragments/top-products-slider")).default();
                                break;
                            case "exclusive-deals":
                                (await import("@src/pages/home/fragments/exclusive-deals")).default();
                                break;
                            case "freight-forwarders-magazine":
                                (await import("@src/pages/home/fragments/freight-forwarders-magazine-slider")).default();
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
        ".js-featured-items, .js-just-for-you, .js-logged-latest-items, .js-latest-items, .js-updates-from-ep, .js-picks-of-month, #js-blogs-slider, .js-top-products, .js-exclusive-deals, #js-customer-reviews-slider, .js-freight-forwarders-magazine"
    );

    // Download PDF guide
    EventHub.on("best-practices:download-pdf", async (e, button) => {
        const { default: downloadBestPracticesGuide } = await import("@src/pages/home/fragments/download-best-practices-guide");
        downloadBestPracticesGuide(button);
    });

    const url = new URL(globalThis.location.href);
    // Check if exist hash for order call requests
    if (url.searchParams.get("order_call")) {
        setTimeout(() => {
            $(".js-order-call-btn").trigger("click");
        }, 500);
    }
});

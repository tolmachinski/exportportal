import $ from "jquery";
import EventHub from "@src/event-hub";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";

import "@scss/user_pages/item_detail_page/index.scss";

const lazyLoadingSections = async sectionNodeList => {
    const loadAllSections = async () => {
        (await import("@src/pages/item_detail/fragments/similar-items-slider")).default();
        (await import("@src/components/items/promo_products_sliders/promo-items-slider")).default();
        (await import("@src/components/items/promo_products_sliders/featured-items-slider")).default();
        (await import("@src/components/items/promo_products_sliders/popular-items-slider")).default();
        (await import("@src/components/items/promo_products_sliders/latest-items-slider")).default();

        return true;
    };

    if ("IntersectionObserver" in window && !BACKSTOP_TEST_MODE) {
        // Or if we see section
        const lazyLoadingSection = new IntersectionObserver(
            changes => {
                changes.forEach(async change => {
                    if (change.isIntersecting && !$(change.target).hasClass("slick-initialized")) {
                        // @ts-ignore
                        switch (change.target?.dataset.lazyName) {
                            case "similar-items":
                                (await import("@src/pages/item_detail/fragments/similar-items-slider")).default();
                                break;
                            case "promo-items":
                                (await import("@src/components/items/promo_products_sliders/promo-items-slider")).default();
                                break;
                            case "featured-items":
                                (await import("@src/components/items/promo_products_sliders/featured-items-slider")).default();
                                break;
                            case "popular-items":
                                (await import("@src/components/items/promo_products_sliders/popular-items-slider")).default();
                                break;
                            case "latest-items":
                                (await import("@src/components/items/promo_products_sliders/latest-items-slider")).default();
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
        document.querySelectorAll(sectionNodeList).forEach(slider => lazyLoadingSection.observe(slider));
    } else {
        await loadAllSections();
    }
};

$(() => {
    lazyLoadingSections(
        "#js-similar-products-slider, .js-promo-items-slider-wr, .js-featured-items-slider-wr, .js-popular-items-slider-wr, .js-latest-items-slider-wr"
    );

    // listeners
    EventHub.on("item-detail:product-detail-toggle", async (_e, btn) => {
        const { default: productDetailToggle } = await import("@src/pages/item_detail/fragments/product-detail-toggle");
        productDetailToggle(btn);
    });

    EventHub.on("certified-modal:open", async (_e, btn) => {
        const { default: openCertifiedModal } = await import("@src/components/popups/certified-modal/open-certified-modal");
        openCertifiedModal(btn);
    });

    EventHub.on("item-comments:show-more-replies", async (_e, btn) => {
        const { default: showMoreReply } = await import("@src/pages/item_detail/fragments/show-more-comments-replies");
        showMoreReply(btn);
    });
    lazyLoadingScriptOnScroll(
        $(".js-show-all-replies-btn"),
        async () => {
            const { toggleBtnShowMoreReply } = await import("@src/pages/item_detail/fragments/show-more-comments-replies");
            toggleBtnShowMoreReply();
        },
        "50%"
    );

    EventHub.off("did-help:click");
    EventHub.on("did-help:click", async (e, btn) => {
        e.preventDefault();

        const { default: didHelp } = await import("@src/components/did-help/index");
        didHelp(btn);
    });
});

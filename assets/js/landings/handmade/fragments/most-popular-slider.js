import $ from "jquery";

import { SITE_URL } from "@src/common/constants";
import fixGetNavigableIndexesSlick from "@src/util/slick/get-navigable-indexes";
import customSlickAutoplay from "@src/util/slick/custom-autoplay";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import removeAjaxLoader from "@src/util/dom/remove-ajax-loader";
import getItemsForSlider from "@src/util/items/get-items-for-slider";

let wasCalled = false;
const topProductsSlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    let slick = null;
    const sliderNode = ".js-most-popular";
    const slider = $(sliderNode);

    if (!slider.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    const sliderParent = slider.closest("section");
    const itemsCount = await getItemsForSlider(slider, sliderParent, `${SITE_URL}handmade/ajax_get_popular`);

    slider.on("init", (_e, slickSlider) => {
        slick = slickSlider;
        fixGetNavigableIndexesSlick(slick);
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    });

    slider.slick({
        swipeToSlide: true,
        arrows: false,
        infinite: true,
        dots: false,
        variableWidth: true,
        rows: itemsCount > 12 ? 2 : 1,
        responsive: [
            {
                breakpoint: 576,
                settings: {
                    rows: 2,
                },
            },
        ],
    });

    customSlickAutoplay(slider, slick, 5000, {
        breakpoints: {
            1920: 4,
            1360: 3,
            1092: 2,
            575: 3,
        },
    });

    removeAjaxLoader(sliderParent, () => slider.removeClass("loading"));
};

export default topProductsSlider;

import $ from "jquery";

import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import initSlickSlider from "@src/components/items/items-slider";
import onInitSlickSlider from "@src/util/slick/on-init";

const initItemsSlider = slider => {
    if ($(globalThis).width() > 1200) {
        return;
    }

    initSlickSlider({
        slider,
        itemsCount: slider.data("itemsCount") || 8,
        options: {
            dots: true,
        },
    });
};

const initPromoItemsSlider = slider => {
    if ($(globalThis).width() <= 1200) {
        return;
    }

    initSlickSlider({
        slider,
        itemsCount: slider.data("itemsCount") || 8,
        options: {
            slidesToShow: 1,
            autoplay: !BACKSTOP_TEST_MODE,
            breakpoints: [],
        },
    });
};

const lazyInitSlider = (slider, callback) => {
    lazyLoadingScriptOnScroll(slider, () => import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => callback(slider)), "50px");
};

const initializePromoProductsSliders = () => {
    const promoItemsSliderSelector = ".js-promo-items-slider-wr";
    const featuredItemsSliderSelector = ".js-featured-items-slider-wr";
    const popularItemsSliderSelector = ".js-popular-items-slider-wr";
    const latestItemsSliderSelector = ".js-latest-items-slider-wr";
    const promoItemsSlider = $(promoItemsSliderSelector);
    const featuredItemsSlider = $(featuredItemsSliderSelector);
    const popularItemsSlider = $(popularItemsSliderSelector);
    const latestItemsSlider = $(latestItemsSliderSelector);
    featuredItemsSlider.on("init", () => lazyLoadingInstance(`${featuredItemsSliderSelector} .js-lazy`));
    latestItemsSlider.on("init", () => lazyLoadingInstance(`${latestItemsSliderSelector} .js-lazy`));
    popularItemsSlider.on("init", () => lazyLoadingInstance(`${popularItemsSliderSelector} .js-lazy`));

    onInitSlickSlider({
        sliderNode: promoItemsSliderSelector,
        sliderName: "promo-items",
        buttonsBreakpoints: {
            1920: 1,
        },
    });

    lazyInitSlider(promoItemsSlider, initPromoItemsSlider);
    lazyInitSlider(featuredItemsSlider, initItemsSlider);
    lazyInitSlider(latestItemsSlider, initItemsSlider);
    lazyInitSlider(popularItemsSlider, initItemsSlider);

    onResizeCallback(() => {
        lazyInitSlider(promoItemsSlider, initPromoItemsSlider);
        lazyInitSlider(featuredItemsSlider, initItemsSlider);
        lazyInitSlider(latestItemsSlider, initItemsSlider);
        lazyInitSlider(popularItemsSlider, initItemsSlider);
    }, globalThis);
};

export default initializePromoProductsSliders;

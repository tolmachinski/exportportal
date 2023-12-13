import $ from "jquery";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import initDefaultItemsSlider from "@src/pages/categories/fragments/init-default-items-slider";
import initPopularItemsSlider from "@src/pages/categories/fragments/init-popular-items-slider";

const initializeSliders = () => {
    const featuredItemsSliderNode = ".js-featured-items-categories-slider";
    const latestItemsSliderNode = ".js-latest-items-categories-slider";
    const popularItemsSliderNode = ".js-categories-popular-items-slider";

    lazyLoadingScriptOnScroll(
        $(featuredItemsSliderNode),
        () => import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => initDefaultItemsSlider(featuredItemsSliderNode)),
        "50px"
    );
    lazyLoadingScriptOnScroll(
        $(latestItemsSliderNode),
        () => import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => initDefaultItemsSlider(latestItemsSliderNode)),
        "50px"
    );
    lazyLoadingScriptOnScroll(
        $(popularItemsSliderNode),
        () => import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => initPopularItemsSlider(popularItemsSliderNode)),
        "50px"
    );
};

export default initializeSliders;

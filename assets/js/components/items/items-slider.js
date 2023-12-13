import getProductSlidersOptions from "@src/util/slick/product-sliders-base-options";

/**
 * It imports the slick-carousel library, then initializes the slick slider on the given element
 */
const itemsSlider = async ({ slider, itemsCount = 12, options = {} }) => {
    if (slider.hasClass("slick-initialized")) {
        return;
    }

    const additionalOptions = options;
    const sliderOptions = getProductSlidersOptions({ itemsCount, breakpoints: additionalOptions.breakpoints || [] });

    additionalOptions.breakpoints = {};

    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
        slider.not(".slick-initialized").slick({ ...sliderOptions, ...additionalOptions });
    });
};

export default itemsSlider;

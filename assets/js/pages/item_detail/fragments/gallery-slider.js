import $ from "jquery";
import lazyLoadingInstance from "@src/plugins/lazy/index";

/**
 * It initializes a slick slider for gallery
 */
const initGallerySlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    const sliderNode = "#js-product-gallery-additional";
    const slider = $(sliderNode);

    slider.on("init", () => {
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    });

    slider.not(".slick-initialized").slick({
        arrows: true,
        infinite: false,
        slidesToShow: 2,
        slidesToScroll: 1,
        verticalSwiping: false,
        vertical: false,
        centerMode: false,
        variableWidth: true,
        focusOnSelect: false,
        mobileFirst: true,
        nextArrow: '<i class="ep-icon ep-icon_arrow-right"></i>',
        prevArrow: '<i class="ep-icon ep-icon_arrow-left"></i>',
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    vertical: false,
                    verticalSwiping: false,
                    variableWidth: true,
                    slidesToShow: 2,
                },
            },
            {
                breakpoint: 574,
                settings: {
                    vertical: true,
                    verticalSwiping: true,
                    variableWidth: false,
                    slidesToShow: 4,
                },
            },
        ],
    });
};

export default initGallerySlider;

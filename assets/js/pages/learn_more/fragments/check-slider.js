import lazyLoadingInstance from "@src/plugins/lazy/index";
import "slick-carousel";

const initLearnMoreCheckSlider = function () {
    const selector = "#js-learnmore-check-slider";
    const slider = $(selector);
    slider.on("init", function () {
        lazyLoadingInstance(selector + " .js-lazy");
    });
    slider.slick({
        rows: 0,
        slidesToShow: 3,
        slidesToScroll: 1,
        dots: false,
        infinite: false,
        arrows: false,
        nextArrow: `<button class="slick-next" aria-label="Previous" type="button"><i class="ep-icon ep-icon_arrow-right"></i></button>`,
        prevArrow: `<button class="slick-prev" aria-label="Next" type="button"><i class="ep-icon ep-icon_arrow-left"></i></button>`,
        responsive: [
            {
                breakpoint: 1366,
                settings: {
                    arrows: false,
                },
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true,
                },
            },
            {
                breakpoint: 575,
                settings: {
                    slidesToShow: 1,
                    arrows: false,
                    dots: true,
                },
            },
        ],
    })
};

export default initLearnMoreCheckSlider;

import lazyLoadingInstance from "@src/plugins/lazy/index";
import "slick-carousel";

let initLearnMoreTourSlider = function () {
    const selector = ".js-learnmore-slider-tour";
    let slider = $(selector);
    slider.on("init", function () {
        lazyLoadingInstance(selector + " .js-lazy");
    });
    slider.slick({
        rows: 0,
        slidesToShow: 2,
        slidesToScroll: 1,
        arrows: false,
        dots: true,
        infinite: false,
        responsive: [
            {
                breakpoint: 575,
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    })
};

export default initLearnMoreTourSlider;

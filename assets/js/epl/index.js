import $ from "jquery";

import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import mix from "@src/util/common/mix";

// @ts-ignore
import "@scss/epl/pages/home/index.scss";

mix(globalThis, { ENCORE_MODE: true });

const initTestimonialsSlider = () => {
    const sliderNode = "#js-epl-testimonials-slider";
    const slider = $(sliderNode);
    slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
    slider.slick({
        dots: true,
        arrows: false,
        infinite: true,
        slidesToShow: 2,
        slidesToScroll: 2,
        autoplay: false,
        variableWidth: false,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    variableWidth: false,
                },
            },
        ],
    });
};

$(() => {
    const testimonialsSliderNodeWr = $("#js-epl-testimonials-b");

    lazyLoadingScriptOnScroll(
        testimonialsSliderNodeWr,
        () => {
            // @ts-ignore
            import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
                initTestimonialsSlider();
            });
        },
        "50px"
    );
});

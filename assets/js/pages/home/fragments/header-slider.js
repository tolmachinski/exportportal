import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const headerHomeSlider = async sliderNode => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    const slider = $(sliderNode);
    slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
    slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        nextArrow: `<button class="slick-arrow-custom slick-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>`,
        prevArrow: `<button class="slick-arrow-custom slick-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>`,
        infinite: true,
        autoplay: !BACKSTOP_TEST_MODE,
        autoplaySpeed: 5000,
        dots: true,
        responsive: [
            {
                breakpoint: 991,
                settings: {
                    dots: false,
                },
            },
        ],
    });
};

export default headerHomeSlider;

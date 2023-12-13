import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import lazyLoadingInstance from "@src/plugins/lazy/index";

let wasCalled = false;
const updateFromEpSlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    const sliderNode = ".js-updates-from-ep";
    const slider = $(sliderNode);

    if (wasCalled) return;
    wasCalled = true;

    slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
    slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        nextArrow: `<span class="js-promo-banner-arrows-next promo-banner__arrows-next ep-icon ep-icon_arrow-right"></span>`,
        prevArrow: `<span class="js-promo-banner-arrows-prev promo-banner__arrows-prev ep-icon ep-icon_arrow-left"></span>`,
        infinite: true,
        autoplay: !BACKSTOP_TEST_MODE,
        autoplaySpeed: 5000,
        dots: false,
        responsive: [
            {
                breakpoint: 991,
                settings: {
                    autoplay: false,
                },
            },
        ],
    });
};

export default updateFromEpSlider;

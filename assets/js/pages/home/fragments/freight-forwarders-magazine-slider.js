import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import fixGetNavigableIndexesSlick from "@src/util/slick/get-navigable-indexes";
import lazyLoadingInstance from "@src/plugins/lazy/index";

let wasCalled = false;
const freightForwardersMagazineSlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    let slick = null;
    const sliderNode = ".js-freight-forwarders-magazine";
    const slider = $(sliderNode);
    const loader = slider.closest("section").find(".ajax-loader");

    if (!slider.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    loader.fadeOut(200, () => {
        loader.remove();
        slider.removeClass("loading");
    });

    slider.on("init", (e, slickSlider) => {
        slick = slickSlider;
        slick.options.swipeToSlide = true;
        fixGetNavigableIndexesSlick(slick);
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    });
    slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        infinite: true,
        autoplay: !BACKSTOP_TEST_MODE,
        autoplaySpeed: 5000,
        dots: false,
        variableWidth: true,
    });
};

export default freightForwardersMagazineSlider;

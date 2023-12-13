import $ from "jquery";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const initAdvisorBenefitsSlider = selector => {
    const slider = $(selector);
    slider.on("init", () => lazyLoadingInstance(`${selector} .js-lazy`));
    slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        arrows: false,
        focusOnSelect: true,
        autoplay: false,
        autoplaySpeed: 4000,
        pauseOnHover: true,
    });
};

const advisorBenefitsSlider = selector => {
    const advisorBenefitsSliderNode = $(selector);
    // Blog slider
    lazyLoadingScriptOnScroll(
        advisorBenefitsSliderNode,
        () => {
            import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
                initAdvisorBenefitsSlider(selector);
            });
        },
        "200px"
    );
};

export default advisorBenefitsSlider;

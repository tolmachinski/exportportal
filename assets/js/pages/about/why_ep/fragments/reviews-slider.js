import $ from "jquery";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

export default () => {
    const sliderNode = "#js-reviews-slider";
    const slider = $(sliderNode);
    const initReviewsHomeSlider = () => {
        slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
        slider.slick({
            dots: true,
            arrows: true,
            slidesToShow: 3,
            slidesToScroll: 3,
            nextArrow: `<button class="slick-arrow-custom slick-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>`,
            prevArrow: `<button class="slick-arrow-custom slick-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>`,
            autoplay: !BACKSTOP_TEST_MODE,
            autoplaySpeed: 5000,
            responsive: [
                {
                    breakpoint: 1360,
                    settings: {
                        arrows: false,
                    },
                },
                {
                    breakpoint: 1024,
                    settings: {
                        arrows: false,
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        pauseOnFocus: false,
                        pauseOnDotsHover: false,
                    },
                },
                {
                    breakpoint: 620,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        adaptiveHeight: true,
                        pauseOnFocus: false,
                        pauseOnDotsHover: false,
                    },
                },
            ],
        });
    };

    // Blog slider
    lazyLoadingScriptOnScroll(
        slider,
        () => {
            import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
                initReviewsHomeSlider();

                slider.on("touchcancel touchmove", function () {
                    $(this).slick("slickPlay");
                });
            });
        },
        "50%"
    );
};

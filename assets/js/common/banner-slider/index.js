import $ from "jquery";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const initBannerSlider = () => {
    $(".js-promo-banner-wr").each(function () {
        const sliderNode = ".js-promo-banner";
        const slider = $(this).find(sliderNode);

        slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
        slider.slick({
            infinite: true,
            slidesToShow: 1,
            nextArrow: `<span class="js-promo-banner-arrows-next promo-banner__arrows-next ep-icon ep-icon_arrow-right"></span>`,
            prevArrow: `<span class="js-promo-banner-arrows-prev promo-banner__arrows-prev ep-icon ep-icon_arrow-left"></span>`,
            autoplay: !BACKSTOP_TEST_MODE,
            autoplaySpeed: 3000,
            variableWidth: true,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        variableWidth: false,
                    },
                },
            ],
        });
    });
};

$(() => {
    lazyLoadingScriptOnScroll(
        $(".js-promo-banner"),
        () => {
            import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
                initBannerSlider();

                $(".js-promo-banner").on("touchcancel touchmove", function () {
                    $(this).slick("slickPlay");
                });
            });
        },
        "1px"
    );
});

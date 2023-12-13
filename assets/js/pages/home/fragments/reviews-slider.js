import $ from "jquery";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import postRequest from "@src/util/http/post-request";
import delay from "@src/util/async/delay";
import { SITE_URL } from "@src/common/constants";

let wasCalled = false;
const customerReviewsSlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    const sliderNode = "#js-customer-reviews-slider";
    const slider = $(sliderNode);
    const loader = slider.closest("section").find(".ajax-loader");

    if (!slider.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    try {
        const { reviews } = await postRequest(`${SITE_URL}default/ajax_get_customer_reviews`);

        if (reviews && !Array.isArray(reviews)) {
            slider.prepend(reviews);
        } else {
            slider.closest("section").remove();

            return;
        }
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error(error);
        slider.closest("section").remove();

        return;
    }

    loader.fadeOut(200);
    slider.on("init", () => {
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    });

    slider.slick({
        dots: true,
        arrows: true,
        slidesToShow: 3,
        slidesToScroll: 3,
        nextArrow: `<button class="slick-arrow-custom slick-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>`,
        prevArrow: `<button class="slick-arrow-custom slick-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>`,
        autoplay: false,
        responsive: [
            {
                breakpoint: 1025,
                settings: {
                    arrows: false,
                    slidesToShow: 2,
                    slidesToScroll: 2,
                },
            },
            {
                breakpoint: 620,
                settings: {
                    arrows: false,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    adaptiveHeight: true,
                },
            },
        ],
    });

    await delay(200);
    loader.remove();
    slider.removeClass("loading");
};

export default customerReviewsSlider;

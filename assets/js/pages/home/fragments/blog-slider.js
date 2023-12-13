import $ from "jquery";

import { SITE_URL } from "@src/common/constants";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import postRequest from "@src/util/http/post-request";
import delay from "@src/util/async/delay";

let wasCalled = false;
const blogSlider = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel");

    let blogSliderIsInit = false;
    const sliderNode = "#js-blogs-slider";
    const slider = $(sliderNode);
    const loader = slider.closest("section").find(".ajax-loader");

    if (!slider.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    try {
        const { blogs } = await postRequest(`${SITE_URL}blogs/ajax_get_blogs`);

        if (blogs && !Array.isArray(blogs)) {
            slider.prepend(blogs);
        } else {
            slider.closest("section").remove();

            return;
        }
    } catch (error) {
        console.error(error);
        slider.closest("section").remove();

        return;
    }

    loader.fadeOut(200);

    const initBlogsHomeSlider = () => {
        slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
        slider.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            centerMode: true,
            prevArrow: `<span class="blogs__arrows blogs__arrows-prev ep-icon ep-icon_arrow-line-left"></span>`,
            nextArrow: `<span class="blogs__arrows blogs__arrows-next ep-icon ep-icon_arrow-line-right"></span>`,
            variableWidth: true,
            focusOnSelect: true,
            infinite: true,
        });
        blogSliderIsInit = true;
    };

    if (window.matchMedia("(min-width:992px)").matches) {
        initBlogsHomeSlider();
    } else {
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    }

    onResizeCallback(() => {
        if (window.matchMedia("(max-width:991px)").matches) {
            if (blogSliderIsInit) {
                // @ts-ignore
                slider.slick("unslick");
                blogSliderIsInit = false;
            }
        } else if (!blogSliderIsInit) {
            initBlogsHomeSlider();
        }
    });

    await delay(200);
    loader.remove();
    slider.removeClass("loading");
};

export default blogSlider;

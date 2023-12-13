import $ from "jquery";
// @ts-ignore
import Swiper, { Navigation, Pagination, EffectCoverflow, Autoplay } from "swiper";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

const changeWrapSize = () => {
    const windowWidth = $(window).outerWidth();
    let sliderHeight = $(".swiper-slide-active .js-about-videos-item").height();

    if (windowWidth > 1200) {
        sliderHeight += 110;
    } else if (windowWidth <= 1200 && windowWidth > 991) {
        sliderHeight += 100;
    } else if (windowWidth <= 991 && windowWidth > 767) {
        sliderHeight += 60;
    }

    $("#js-about-videos-slider").height(sliderHeight);
};

const aboutVideosSlider = () => {
    const sliderId = "#js-about-videos-slider";
    const slider = $(sliderId);

    Swiper.use([Navigation, Pagination, EffectCoverflow, Autoplay]);

    const swiper = new Swiper(sliderId, {
        init: false,
        centeredSlides: true,
        autoHeight: true,
        slidesPerView: 3,
        loop: true,
        speed: 600,
        observer: true,
        grabCursor: true,
        effect: "coverflow",
        coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 200,
            modifier: 0,
            slideShadows: false,
        },
        autoplay: BACKSTOP_TEST_MODE
            ? false
            : {
                  delay: 5000,
                  disableOnInteraction: false,
              },
        pagination: {
            el: ".swiper-pagination",
            type: "bullets",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            320: {
                spaceBetween: 10,
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 15,
            },
            992: {
                slidesPerView: 3,
                spaceBetween: 20,
            },
        },
        on: {
            init: () => {
                lazyLoadingInstance(`${sliderId} .js-lazy`);
                $(".js-about-videos-bg").addClass("animated");
                changeWrapSize();
            },
            slideChangeTransitionStart: () => {
                changeWrapSize();
            },
            sliderMove: () => {
                changeWrapSize();
            },
        },
    });

    if (!BACKSTOP_TEST_MODE) {
        slider.on({
            mouseenter: () => {
                swiper.autoplay.stop();
            },
            mouseleave: () => {
                swiper.autoplay.start();
            },
        });
    }

    onResizeCallback(() => {
        swiper.update();
        changeWrapSize();
    });

    slider.removeClass("about-videos--not-init");
    swiper.init();
};

export default aboutVideosSlider;

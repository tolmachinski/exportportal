import $ from "jquery";
import onInitSlickSlider from "@src/util/slick/on-init";
import initSlickSlider from "@src/components/items/items-slider";

const sliderNode = ".js-latest-products-slider";
const initTestimonialsSlider = async () => {
    const slider = $(sliderNode);
    const { sliderName, itemsCount = 8 } = slider.data();

    onInitSlickSlider({
        sliderNode,
        sliderName,
    });

    initSlickSlider({
        slider,
        itemsCount,
        options: {
            slidesToShow: 4,
            breakpoints: [
                {
                    breakpoint: 1380,
                    settings: {
                        slidesToShow: 3,
                    },
                },
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 4,
                    },
                },
                {
                    breakpoint: 1033,
                    settings: {
                        slidesToShow: 3,
                    },
                },
            ],
        },
    });
};

export default initTestimonialsSlider;

import $ from "jquery";
import onInitSlickSlider from "@src/util/slick/on-init";
import initSlickSlider from "@src/components/items/items-slider";

const initPopularItemsSlider = sliderNode => {
    const slider = $(sliderNode);
    const { sliderName, itemsCount = 8 } = slider.data();

    onInitSlickSlider({
        sliderNode,
        sliderName,
        buttonsBreakpoints: {
            1920: 4,
            1119: 1,
        },
    });

    initSlickSlider({
        slider,
        itemsCount,
        options: {
            slidesToShow: 4,
            breakpoints: [
                {
                    breakpoint: 1098,
                    settings: {
                        slidesToShow: 3,
                    },
                },
            ],
        },
    });
};

export default initPopularItemsSlider;

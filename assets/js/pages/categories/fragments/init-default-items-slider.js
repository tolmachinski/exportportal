import $ from "jquery";
import onInitSlickSlider from "@src/util/slick/on-init";
import initSlickSlider from "@src/components/items/items-slider";

const initDefaultItemsSlider = sliderNode => {
    const slider = $(sliderNode);
    const { sliderName, itemsCount = 8 } = slider.data();

    onInitSlickSlider({
        sliderNode,
        sliderName,
        buttonsBreakpoints: {
            1920: 2,
            1119: 1,
        },
    });

    initSlickSlider({
        slider,
        itemsCount,
        options: {
            slidesToShow: 2,
            breakpoints: [
                {
                    breakpoint: 1119,
                    settings: {
                        slidesToShow: 1,
                    },
                },
            ],
        },
    });
};

export default initDefaultItemsSlider;

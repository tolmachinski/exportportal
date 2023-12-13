import $ from "jquery";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import onInitSlickSlider from "@src/util/slick/on-init";
import initSlickSlider from "@src/components/items/items-slider";

const productsSliderSelector = "#js-b2b-request-products-slider";
const productsSlider = $(productsSliderSelector);
const itemsCount = productsSlider.data("countItems");

const slickDependencyFromResolution = () => {
    const windowWidth = $(globalThis).width();

    if (
        itemsCount > 5 ||
        (windowWidth < 476 && itemsCount > 4) ||
        (windowWidth > 475 && windowWidth < 1100 && itemsCount > 3) ||
        (windowWidth > 1100 && windowWidth < 1370 && itemsCount > 4)
    ) {
        return true;
    }

    if (productsSlider.hasClass("slick-initialized")) {
        productsSlider.slick("unslick");
    }

    return false;
};

const initProductsSlider = async () => {
    if (!slickDependencyFromResolution()) {
        return;
    }

    onInitSlickSlider({
        sliderNode: productsSliderSelector,
        sliderName: "user-items",
        buttonsBreakpoints: {
            1920: 5,
            1380: 4,
            1107: 3,
        },
    });

    initSlickSlider({
        slider: productsSlider,
        itemsCount,
        options: {
            itemsCount,
            breakpoints: [
                {
                    breakpoint: 1380,
                    settings: {
                        arrows: false,
                        slidesToShow: 4,
                    },
                },
                {
                    breakpoint: 1107,
                    settings: {
                        arrows: false,
                        slidesToShow: 3,
                    },
                },
            ],
        },
    });
};

export default () => {
    initProductsSlider();
    onResizeCallback(() => initProductsSlider(), globalThis);
};

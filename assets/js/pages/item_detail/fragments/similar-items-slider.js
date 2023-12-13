import $ from "jquery";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import getItemsForSlider from "@src/util/items/get-items-for-slider";
import removeAjaxLoader from "@src/util/dom/remove-ajax-loader";
import onInitSlickSlider from "@src/util/slick/on-init";
import initSlickSlider from "@src/components/items/items-slider";
import { SITE_URL } from "@src/common/constants";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const sliderNode = "#js-similar-products-slider";
const slider = $(sliderNode);
let wasCalled = false;

/**
 * Initialize the slider depending on the resolution and count items
 * @param {number} itemsCount
 */
const slickDependencyFromResolution = itemsCount => {
    const windowWidth = $(globalThis).width();

    if (itemsCount > 4 || (itemsCount > 3 && windowWidth <= 1109)) {
        return true;
    }

    if (slider.hasClass("slick-initialized")) {
        slider.slick("unslick");
    }

    lazyLoadingInstance(`${sliderNode} .js-lazy`);

    return false;
};

/**
 * It initializes the slider
 * @param {number} itemsCount
 */
const initSlider = itemsCount => {
    onInitSlickSlider({
        sliderNode,
        sliderName: "similar-items",
        buttonsBreakpoints: {
            1920: 4,
            1106: 3,
        },
    });

    initSlickSlider({
        slider,
        itemsCount,
        options: {
            slidesToShow: 4,
            breakpoints: [
                {
                    breakpoint: 1106,
                    settings: {
                        slidesToShow: 3,
                        dots: false,
                    },
                },
            ],
        },
    });
};

/**
 * It gets the number of items for the slider, and if the number of items is greater than the number of
 * items that should be displayed in the slider, it initializes the slider
 */
const initSimilarProductsSlider = async () => {
    if (!slider.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    const sliderParent = slider.closest("section");
    const itemsCount = await getItemsForSlider(slider, sliderParent, `${SITE_URL}items/ajax_get_similar_items`, {
        item: slider.data("item"),
        category: slider.data("category"),
    });

    if (slickDependencyFromResolution(itemsCount)) {
        initSlider(itemsCount);
    }

    onResizeCallback(() => {
        if (slickDependencyFromResolution(itemsCount)) {
            initSlider(itemsCount);
        }
    }, globalThis);

    removeAjaxLoader(sliderParent, () => slider.removeClass("loading"));
};

export default initSimilarProductsSlider;

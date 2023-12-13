import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with products that are popular
 */
const initPopularItemsSlider = async () => {
    if (wasCalled || $(globalThis).width() > 1200) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-popular-items-slider-wr",
        sliderName: "popular-items",
        options: {
            dots: true,
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_popular_items`,
        postData: {
            promoItems: true,
        },
    });

    wasCalled = true;
};

export default () => {
    initPopularItemsSlider();

    onResizeCallback(() => initPopularItemsSlider(), globalThis);
};

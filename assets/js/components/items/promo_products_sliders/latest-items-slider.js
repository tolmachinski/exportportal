import $ from "jquery";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import { SITE_URL } from "@src/common/constants";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with the latest items
 */
const initLatestItemsSlider = async () => {
    if (wasCalled || $(globalThis).width() > 1200) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-latest-items-slider-wr",
        sliderName: "latest-items",
        options: {
            dots: true,
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_latest_items`,
        postData: {
            promoItems: true,
        },
    });

    wasCalled = true;
};

export default () => {
    initLatestItemsSlider();

    onResizeCallback(() => initLatestItemsSlider(), globalThis);
};

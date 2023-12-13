import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with the latest items
 */
const loggedLatestItemsSlider = async () => {
    if (wasCalled) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-logged-latest-items",
        sliderName: "latest-items",
        buttonsBreakpoints: {
            1920: 4,
            1370: 3,
            1098: 2,
        },
        options: {
            appendDots: $(".js-latest-items-wrapper"),
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_latest_items`,
        disableBreakpoint: 575,
    });

    wasCalled = true;
};

export default loggedLatestItemsSlider;

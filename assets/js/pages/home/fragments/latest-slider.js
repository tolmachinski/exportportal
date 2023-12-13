import { SITE_URL } from "@src/common/constants";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with the latest items
 */
const initLatestItemsSlider = async () => {
    if (wasCalled) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-latest-items",
        sliderName: "latest-items",
        buttonsBreakpoints: {
            1920: 5,
            1377: 4,
            1105: 3,
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_latest_items`,
        disableBreakpoint: 575,
    });

    wasCalled = true;
};

export default initLatestItemsSlider;

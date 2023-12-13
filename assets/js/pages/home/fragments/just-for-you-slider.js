import { SITE_URL } from "@src/common/constants";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with products that are fetched from the server
 */
const justForYouSlider = async () => {
    if (wasCalled) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-just-for-you",
        sliderName: "just-for-you-items",
        buttonsBreakpoints: {
            1920: 3,
            1298: 2,
        },
        options: {
            slidesToShow: 3,
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_items_for_you`,
    });

    wasCalled = true;
};

export default justForYouSlider;

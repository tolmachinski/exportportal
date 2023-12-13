import { SITE_URL } from "@src/common/constants";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slider with the class `.js-featured-items` and the name `featured-items` and it
 * fetches the products from the URL `items/ajax_get_featured_items`
 */
const featuredItemsSlider = async () => {
    if (wasCalled) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-featured-items",
        sliderName: "featured-items",
        buttonsBreakpoints: {
            1920: 5,
            1440: 4,
            1200: 3,
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_featured_items`,
    });

    wasCalled = true;
};

export default featuredItemsSlider;

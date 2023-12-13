import $ from "jquery";
import { BACKSTOP_TEST_MODE, SITE_URL } from "@src/common/constants";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import initAjaxProductsSlider from "@src/components/items/ajax-items-slider";

let wasCalled = false;
/**
 * It initializes a slick slider
 */
const initPromoItemsSlider = async () => {
    if (wasCalled || $(globalThis).width() <= 1200) {
        return;
    }

    initAjaxProductsSlider({
        sliderNode: ".js-promo-items-slider-wr",
        sliderName: "promo-items",
        buttonsBreakpoints: {
            1920: 1,
        },
        options: {
            slidesToShow: 1,
            autoplay: !BACKSTOP_TEST_MODE,
            breakpoints: [],
        },
        ajaxUrl: `${SITE_URL}items/ajax_get_all_promo_items`,
        postData: {
            promoItems: true,
        },
    });

    wasCalled = true;
};

export default () => {
    initPromoItemsSlider();
    onResizeCallback(() => initPromoItemsSlider(), globalThis);
};

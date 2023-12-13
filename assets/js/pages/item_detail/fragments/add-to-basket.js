import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

/**
 * It sends a request to the server to add a product to the basket
 * @param {JQuery} btn - the button that was clicked
 * @param {any} params - {}
 * @returns a boolean value.
 */
const addToBasket = async (btn, params) => {
    const form = btn.closest("form");

    if ($(".js-product-variant-selected").length && !$(".js-product-variant-selected-item").length) {
        systemMessages(params.systmessFillOptions, "warning");

        return false;
    }

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}basket/ajax_add_to_basket`, form.serialize());
        systemMessages(message, messageType);

        if (messageType === "success") {
            const basketLink = $(".js-header-basket-link");
            if (!basketLink.find(".js-epuser-line-circle-sign").length) {
                basketLink.append('<span class="epuser-line__circle-sign bg-orange js-epuser-line-circle-sign"></span>');
            }
        }
    } catch (error) {
        handleRequestError(error);
    }

    return true;
};

export default addToBasket;

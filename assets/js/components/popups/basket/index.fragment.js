import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import notifyOutOfStock from "@src/util/out-of-stock/notify-out-of-stock";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

export default () => {
    notifyOutOfStock();

    EventHub.on("basket:remove-item", async (e, btn) => {
        const basketElement = $("#header-toggle-basket");

        try {
            showLoader(basketElement);
            const { message, mess_type: messType, basket, items_total: itemsTotal } = await postRequest(`${SITE_URL}basket/ajax_basket_operation/delete_one`, {
                id: btn.data("item"),
            });

            systemMessages(message, messType);
            if (messType === "success") {
                basketElement.html(basket);

                if (!Number(itemsTotal)) {
                    const circleSign = $(".epuser-line__icons-item--basket .epuser-line__circle-sign");
                    if (circleSign.length) {
                        circleSign.remove();
                    }
                }
            }
        } catch (error) {
            handleRequestError(error);
        } finally {
            hideLoader(basketElement);
        }
    });
};

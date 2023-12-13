import $ from "jquery";

import { translate_js } from "@src/i18n";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";

import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const saveProduct = async button => {
    try {
        const { mess_type: messType, message } = await postRequest(`${SUBDOMAIN_URL}items/ajax_saveproduct_operations/add_product_saved`, {
            product: button.data("item"),
        });

        if (messType === "success") {
            const item = $(`.js-products-favorites-btn[data-item="${button.data("item")}"]`);

            item.data("jsAction", "favorites:remove-product").attr(
                "title",
                translate_js({ plug: "general_i18n", text: "item_card_remove_from_favorites_tag_title" })
            );

            const svgIcon = item.find(".ep-icon-svg use");
            const svgSplit = svgIcon.attr("href").split("#");
            svgIcon.attr("href", `${svgSplit[0]}#ep-icon-favorite`);

            const itemText = item.find("span");
            if (itemText.length) {
                itemText.text(translate_js({ plug: "general_i18n", text: "item_card_label_favorite" }));
            }
        } else {
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    }
};

export default saveProduct;

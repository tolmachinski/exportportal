import $ from "jquery";

import { translate_js } from "@src/i18n";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";

const removeSavedProduct = function (button) {
    $.ajax({
        url: `${SUBDOMAIN_URL}items/ajax_saveproduct_operations/remove_product_saved`,
        type: "POST",
        dataType: "JSON",
        data: { product: button.data("item") },
        success: resp => {
            if (resp.mess_type === "success") {
                const item = $(`.js-products-favorites-btn[data-item="${button.data("item")}"]`);

                item.data("jsAction", "favorites:save-product").attr(
                    "title",
                    translate_js({ plug: "general_i18n", text: "item_card_add_to_favorites_tag_title" })
                );

                const svgIcon = item.find(".ep-icon-svg use");
                const svgSplit = svgIcon.attr("href").split("#");
                svgIcon.attr("href", `${svgSplit[0]}#ep-icon-favorite-empty`);

                const itemText = item.find("span");
                if (itemText.length) {
                    itemText.text(translate_js({ plug: "general_i18n", text: "item_card_label_favorited" }));
                }
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        },
    });
};

export default removeSavedProduct;

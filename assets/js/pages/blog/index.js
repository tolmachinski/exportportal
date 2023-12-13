import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";

import hideMaxList from "@src/plugins/hide-max-list/index";

import "@scss/user_pages/blog/index.scss";

$(() => {
    hideMaxList(".js-hide-max-list");

    $(".js-on-validate-before-submit").on("submit", function searchBeforeSubmit() {
        if (String($(this).find(".js-on-validate-before-submit__input").val()).trim() === "") {
            systemMessages(translate({ plug: "general_i18n", text: "blog_search_empty" }), "info");
            return false;
        }

        return true;
    });
});

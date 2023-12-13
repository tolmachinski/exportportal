import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

const onClickDropdownItem = item => {
    const searchType = item.data("type");
    const form = $(".js-search-autocomplete-form");

    item.addClass("active").closest(".js-select-search-by").find("button.active").removeClass("active");
    $(".js-search-autocomplete-reset-btn").removeClass("active");
    form.data("type", searchType).find(".js-select-search-text").text(item.text());

    form.data("suggestionsUrl", `${SITE_URL}autocomplete/ajax_get_${searchType}_suggestions`)
        .data("type", searchType)
        .find(".js-select-search-text")
        .text(item.text());
};

export default onClickDropdownItem;

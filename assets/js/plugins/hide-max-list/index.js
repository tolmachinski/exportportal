import $ from "jquery";

import { hideMaxList } from "@src/plugins/hide-max-list/core";
import { translate } from "@src/i18n";

$.fn.extend({ hideMaxListItems: hideMaxList });

export default (selector, options = {}) => {
    const elements = $(selector);
    // @ts-ignore
    elements.hideMaxListItems.call(elements, {
        max: 5,
        moreText: translate({ plug: "general_i18n", text: "show_more" }),
        lessText: translate({ plug: "general_i18n", text: "show_less" }),
        moreHTML: '<div class="maxlist-more js-maxlist-more"><button class="maxlist-more__btn" type="button"></button></div>',
        lessHTML: '<div class="maxlist-more js-maxlist-more"><button class="maxlist-more__btn maxlist-more__btn--less" type="button"></button></div>',
        ...(options || {}),
    });

    return elements;
};

import $ from "jquery";

import { readMoreText } from "@src/plugins/read-more-text/core";

$.fn.extend({ hideMaxText: readMoreText });

export default (selector, options = {}) => {
    const elements = $(selector);
    // @ts-ignore
    elements.hideMaxText.call(elements, {
        selector,
        ...(options || {}),
    });

    return elements;
};

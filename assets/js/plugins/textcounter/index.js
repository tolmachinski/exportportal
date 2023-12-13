import $ from "jquery";
import "jquery-text-counter/textcounter.js";

import { translate } from "@src/i18n";
import { LANG } from "@src/common/constants";

const addCounter = async (selector, options = {}) => {
    await import(/* webpackChunkName: "textcounter-i18n" */ `@plug/textcounter-0-3-6/lang/${LANG}.js`);

    const elements = $(selector);
    const textPrefix = translate({ plug: "textcounter", text: "count_down_text_before" });
    const textPostfix = translate({ plug: "textcounter", text: "count_down_text_after" });
    const countDownText = `${textPrefix} %d ${textPostfix}`;

    elements.toArray().forEach(e => {
        const node = $(e);

        node.textcounter({
            max: Number(node.data("max") || 200),
            min: Number(node.data("min") || 0),
            countContainerClass: "textcounter-wrapper",
            textCountClass: "textcounter",
            countDown: true,
            countSpaces: true,
            countDownText,
            ...(options || {}),
        });
    });

    return elements;
};

export { addCounter };
export default addCounter;

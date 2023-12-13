import $ from "jquery";
import { translate } from "@src/i18n";

import "@scss/plug/read-more-text/index.scss";

const readMoreText = function (options) {
    /** @type {JQuery} self */
    const self = this;
    const defaults = {
        moreText: translate({
            plug: "general_i18n",
            text: "read_more",
            replaces: { "{{AMOUNT}}": `<i class="ep-icon ep-icon_arrow-down fs-12">` },
        }),
        lessText: translate({
            plug: "general_i18n",
            text: "read_less",
            replaces: { "{{AMOUNT}}": `<i class="ep-icon ep-icon_arrow-up fs-12">` },
        }),
        button: `<button class="read-more-btn txt-blue2 bg-n"></button>`,
    };
    const optionsInner = $.extend(defaults, options);

    return self.each(function () {
        const wrapper = $(this);
        const op = optionsInner;
        const atas = wrapper.attr("atas");
        const wrapperHeight = wrapper.height();
        const wrapperScrollHeight = wrapper[0].scrollHeight;

        if (wrapperScrollHeight > 0 && wrapperScrollHeight > wrapperHeight) {
            const button = $(op.button).data("type", "more");
            button.html(op.moreText);

            if (!wrapper.next(button).length) {
                wrapper.after(button);
            }

            if (typeof atas !== typeof undefined && atas !== false) {
                button.attr("atas", "global__text__read-more-btn");
            }

            button.on("click", function (e) {
                e.preventDefault();
                const btn = $(this);
                if (btn.data("type") === "more") {
                    btn.data("type", "less");
                    btn.html(op.lessText);
                    wrapper.css({ "max-height": "100%", display: "block" });
                } else {
                    btn.data("type", "more");
                    btn.html(op.moreText);
                    wrapper.removeAttr("style");
                }
            });
        }
    });
};

export default readMoreText;
export { readMoreText };

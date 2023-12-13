import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import { BASE_OPTIONS } from "@src/common/popups/options";
import inputAutocompliteOff from "@src/util/dom/input-auto-complite-off";

const beforeShow = function () {
    const elem = this.element;
    if (elem.data("dashboard-class") !== undefined) {
        $(".fancybox-inner").addClass(elem.data("dashboard-class"));
    }
};

const afterShow = function () {
    const fancyboxContent = $(".fancybox-inner .modal-flex__content");
    const hasScrollBar = function (node) {
        return node.get(0).scrollHeight > node.get(0).clientHeight;
    };
    // TODO: Перепроверить если этот функционал вообще нужен
    setTimeout(() => {
        if (fancyboxContent.length && hasScrollBar(fancyboxContent)) {
            fancyboxContent.addClass("pr-15");
        }
    }, 100);
};

const onLoadComplete = () => {
    inputAutocompliteOff();
};

export default async selector => {
    const options = {
        ajax: { complete: onLoadComplete },
        beforeShow,
        afterShow,
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, options, true));
};

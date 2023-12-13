import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import inputAutocompliteOff from "@src/util/dom/input-auto-complite-off";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { BASE_OPTIONS } from "@src/common/popups/options";

const onBeforeShow = function () {
    /** @type {JQuery} element */
    const { element } = this;

    inputAutocompliteOff();
    enableFormValidation(this.wrap.find(".validateModal"), {}, element);
    if (element.data("dashboard-class")) {
        $(".fancybox-inner").addClass(element.data("dashboard-class"));
    }
};

const onAfterShow = function () {
    const wrapClass = this.element.data("wrapClass");
    if (wrapClass) {
        this.wrap.addClass(wrapClass);
    }
};

export default async selector => {
    const options = {
        beforeShow: onBeforeShow,
        afterShow: onAfterShow,
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, options, true));
};

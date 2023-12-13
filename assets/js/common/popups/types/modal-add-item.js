import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { BASE_OPTIONS, ITEM_MODAL_OPTIONS } from "@src/common/popups/options";
import inputAutocompliteOff from "@src/util/dom/input-auto-complite-off";

const onAfterShow = () => {
    $("body").css({ position: "fixed" });
};

const onAfterClose = () => {
    $("body").css({ position: "" });
};

const onLoadComplete = function () {
    inputAutocompliteOff();
    enableFormValidation($(".validateModal"), {}, this.caller_btn);
};

export default async selector => {
    const options = {
        ajax: { complete: onLoadComplete },
        afterShow: onAfterShow,
        afterClose: onAfterClose,
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, ITEM_MODAL_OPTIONS, options));
};

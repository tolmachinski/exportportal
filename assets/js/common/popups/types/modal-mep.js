import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import inputAutocompliteOff from "@src/util/dom/input-auto-complite-off";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { BASE_OPTIONS, MEP_MODAL_OPTIONS } from "@src/common/popups/options";

const onLoadComplete = function () {
    inputAutocompliteOff();
    enableFormValidation($(".validateModal"), {}, this.caller_btn);
};

export default async (selector) => {
    const options = {
        ajax: { complete: onLoadComplete },
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, MEP_MODAL_OPTIONS, options, true));
};


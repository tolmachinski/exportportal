import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import inputAutocompliteOff from "@src/util/dom/input-auto-complite-off";
import { BASE_OPTIONS, SELECT_LANG_MODAL_OPTIONS } from "@src/common/popups/options";

const onLoadComplete = function () {
    inputAutocompliteOff();
};

export default async (selector) => {
    const options = {
        ajax: { complete: onLoadComplete },
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, SELECT_LANG_MODAL_OPTIONS, options, true));
};

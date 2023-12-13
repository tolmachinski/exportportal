import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import { BASE_OPTIONS, IFRAME_MODAL_OPTIONS } from "@src/common/popups/options";

export default selector => {
    const options = {};

    return initialize(selector, $.extend({}, BASE_OPTIONS, IFRAME_MODAL_OPTIONS, options, true));
};

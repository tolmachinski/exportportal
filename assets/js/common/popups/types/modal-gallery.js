import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import { BASE_OPTIONS, GALLERY_MODAL_OPTIONS } from "@src/common/popups/options";

export default async (selector) => {
    return initialize(selector, $.extend({}, BASE_OPTIONS, GALLERY_MODAL_OPTIONS, {}, false));
};

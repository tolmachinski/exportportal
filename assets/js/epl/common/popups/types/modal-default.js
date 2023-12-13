import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v3/index";
import { BASE_OPTIONS } from "@src/epl/common/popups/options";

export default async selector => {
    const options = {};

    return initialize(selector, $.extend({}, BASE_OPTIONS, options, true));
};

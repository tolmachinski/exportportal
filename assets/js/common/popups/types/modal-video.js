import $ from "jquery";
import { initialize } from "@src/plugins/fancybox/v2/index";
import { BASE_OPTIONS, VIDEO_MODAL_OPTIONS } from "@src/common/popups/options";

const beforeShow = function () {
    $(".fancybox-inner").addClass("fancybox-video");
};

export default async selector => {
    const options = {
        beforeShow,
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, VIDEO_MODAL_OPTIONS, options, true));
};

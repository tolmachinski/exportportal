import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v3/index";
import { BASE_OPTIONS } from "@src/epl/common/popups/options";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

export default async selector => {
    const options = {
        isVideoModal: true,
        wrClass: "fancybox-video-content",
        media: {
            youtube: {
                params: {
                    autoplay: BACKSTOP_TEST_MODE ? 0 : 1,
                },
            },
        },
    };

    return initialize(selector, $.extend({}, BASE_OPTIONS, options, true));
};

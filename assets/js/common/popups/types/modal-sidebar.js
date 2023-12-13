import $ from "jquery";

import { initialize } from "@src/plugins/fancybox/v2/index";
import { BASE_OPTIONS, SIDEBAR_MODAL_OPTIONS } from "@src/common/popups/options";

export default async selector => initialize(selector, $.extend({}, BASE_OPTIONS, SIDEBAR_MODAL_OPTIONS, true));

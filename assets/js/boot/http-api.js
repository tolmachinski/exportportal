import $ from "jquery";

import { LANG } from "@src/common/constants";

// Add some specific configurations to ajax
$.ajaxSetup({
    xhrFields: { withCredentials: true },
    headers: {
        // eslint-disable-next-line no-underscore-dangle
        "X-User-Language": LANG || "en",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        "X-Script-Mode": "webpack",
    },
});

import $ from "jquery";
import scrollToElement from "@src/util/common/scroll-to-element";

import "@scss/user_pages/terms/index.scss";

export default cookieModal => {
    $(".js-scroll-terms .link").on("click", function onClick(e) {
        e.preventDefault();
        const $this = $(this);

        if (cookieModal !== undefined) {
            scrollToElement($this.attr("href"), 0, 500, "", ".fancybox-inner", "position");
        } else {
            const callback = $(window).width() < 768 ? "closeFancyBox" : "";
            scrollToElement($this.attr("href"), 70, 500, callback);
        }
    });
};

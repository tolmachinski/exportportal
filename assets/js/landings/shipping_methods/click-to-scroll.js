import $ from "jquery";
import scrollToElement from "@src/util/common/scroll-to-element";

const clickToScroll = button => {
    const element = button.data("anchor");
    const minusHeight = $("#js-ep-header-bottom").height();
    scrollToElement(`#${element}`, minusHeight, 500);
};

export default clickToScroll;

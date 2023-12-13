import $ from "jquery";
import scrollToElement from "@src/util/common/scroll-to-element";

const navScrollTo = button => {
    const introHeight = $("#js-giveaway-intro-section").height() - 100;
    const el = button.data("anchor");
    scrollToElement(`#${el}`, introHeight, 500);
};

export default navScrollTo;

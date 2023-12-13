import $ from "jquery";
import scrollToElement from "@src/util/common/scroll-to-element";
import toggleMobileMenu from "@src/epl/components/navigation/fragments/toggle-dashboard-mobile-menu";

const navScrollTo = button => {
    const eplHeader = $("#js-epl-header");
    const headerLine = $("#js-epl-header-line");

    const heightNav = eplHeader.height();
    const el = button.data("anchor");
    scrollToElement(`#${el}`, heightNav, 500);

    if (headerLine.hasClass("active")) {
        toggleMobileMenu();
    }
};

export default navScrollTo;

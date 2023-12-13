import $ from "jquery";

import { scrollupState } from "@src/common/scroll-up";
import { lockBody } from "@src/plugins/lock-body/index";
import toggleShadowHeaderTop from "@src/components/navigation/fragments/shadow-header-toggle";

const showDashboardMenu = function () {
    const scrollPosition = $(globalThis).scrollTop();
    lockBody();
    $(globalThis).scrollTop(scrollPosition);
    $("#js-epuser-subline").slideDown();
    toggleShadowHeaderTop("show");

    $("*:not(object)")
        .filter(function filterFixedElement() {
            return $(this).css("position") === "fixed" && !$(this).hasClass("fancybox-overlay") && !$(this).hasClass("fancybox-wrap");
        })
        .addClass("fancybox-margin2");
    $("html").addClass("fancybox-margin2 fancybox-lock2");
    const btnUserDashboard = $(".epuser-line .epuser-line__user .ep-icon");
    if (btnUserDashboard.hasClass("ep-icon_arrow-up")) {
        btnUserDashboard.toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
    }

    scrollupState.active = false;

    if ($(".js-community-search-header:visible").length) $(".js-community-search-header").slideToggle();
};

export default showDashboardMenu;

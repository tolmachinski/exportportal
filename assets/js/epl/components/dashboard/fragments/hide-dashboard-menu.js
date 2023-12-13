import $ from "jquery";

import { scrollupState } from "@src/common/scroll-up";
import toggleHeaderLineBackground from "@src/epl/components/navigation/fragments/toggle-header-line-background";

const hideDashboardMenu = () => {
    $("#js-epuser-subline").removeClass("active").slideUp();
    $(".js-epuser-line *, .js-navbar-toggle-btn").removeClass("active");
    toggleHeaderLineBackground();
    $("body").removeClass("locked");

    if ($(window).scrollTop() > 500) {
        $("#js-btn-scrollup").show();
    }

    const btnUserDashboard = $(".js-btn-user-dashboard");

    if (btnUserDashboard.hasClass("ep-icon_arrow-up")) {
        btnUserDashboard.toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
    }

    scrollupState.active = true;
};

export default hideDashboardMenu;

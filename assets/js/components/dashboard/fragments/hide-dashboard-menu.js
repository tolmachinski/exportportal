import $ from "jquery";

import { unlockBody } from "@src/plugins/lock-body/index";
import { scrollupState } from "@src/common/scroll-up";
import toggleShadowHeaderTop from "@src/components/navigation/fragments/shadow-header-toggle";
import definePositionEpuserSubline from "@src/components/navigation/fragments/define-position-epuser-subline";

const hideDashboardMenu = function () {
    if ($("body").css("overflow") === "hidden") {
        unlockBody();
    }

    $(".js-epuser-line").find(".active").removeClass("active").end().find(".ep-icon_arrow-up").toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
    $("#js-epuser-subline").slideUp();
    definePositionEpuserSubline(1);
    toggleShadowHeaderTop();
    $(".fancybox-margin2").removeClass("fancybox-margin2");
    $("html").removeClass("fancybox-lock2");
    scrollupState.active = true;
};

export default hideDashboardMenu;

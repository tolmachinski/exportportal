import $ from "jquery";

import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import hideDashboardMenu from "@src/components/dashboard/fragments/hide-dashboard-menu";
import toggleShadowHeaderTop from "@src/components/navigation/fragments/shadow-header-toggle";
import definePositionEpuserSubline from "@src/components/navigation/fragments/define-position-epuser-subline";
import showDashboardMenu from "@src/components/dashboard/fragments/show-dashboard-menu";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

let dashboardBlockStatus = true;
const epuserSublineHeight = 469;

const toggleDashboardMenu = async button => {
    // @ts-ignore
    await import("@scss/user_pages/general/dashboard_menu.scss");

    if (!dashboardBlockStatus) {
        return true;
    }

    dashboardBlockStatus = false;
    const btnCheck = button.closest(".js-block-wrapper-navbar-toggle");

    if (btnCheck.hasClass("active")) {
        hideDashboardMenu();
        definePositionEpuserSubline(1);
        dashboardBlockStatus = true;
    } else {
        $(".epuser-line__item.active").removeClass("active");
        const epuserSubline = $("#js-epuser-subline");
        const epuserDashboard = $("#js-epuser-dashboard");

        epuserSubline.slideUp(async () => {
            epuserDashboard.hide();
            definePositionEpuserSubline();
            epuserSubline.height(epuserSublineHeight).slideDown();
            toggleShadowHeaderTop("show");

            try {
                showLoader(epuserSubline);

                const { mess_type: messType, menu_content: menuContent, message } = await postRequest(
                    `${SUBDOMAIN_URL}dashboard/ajax_view_dashboard_new/webpackData`
                );

                dashboardBlockStatus = true;

                if (messType === "success") {
                    epuserDashboard.html(menuContent).show();
                    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
                    showDashboardMenu();
                    btnCheck.addClass("active").find('[class*="ep-icon_arrow"]').toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
                } else {
                    epuserSubline.slideUp(() => toggleShadowHeaderTop());
                    systemMessages(message, messType);
                }
            } catch (error) {
                handleRequestError(error);
            } finally {
                hideLoader(epuserSubline);
            }
        });
    }

    return true;
};

export default toggleDashboardMenu;

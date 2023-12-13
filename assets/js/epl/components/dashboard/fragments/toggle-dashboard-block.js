import $ from "jquery";

import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { scrollupState } from "@src/common/scroll-up";

import handleRequestError from "@src/util/http/handle-request-error";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";
import hideEpuserSubline from "@src/epl/components/dashboard/fragments/hide-dashboard-menu";
import toggleHeaderLineBackground from "@src/epl/components/navigation/fragments/toggle-header-line-background";

let dashboardBlockStatus = true;

const showDashboardMenu = () => {
    const btnUserDashboard = $(".js-btn-user-dashboard");

    $("#js-epl-header-line-background").data("jsAction", "navbar:toggle-dashboard-menu");
    $("#js-epuser-subline").addClass("active");

    if (btnUserDashboard.hasClass("ep-icon_arrow-up")) {
        btnUserDashboard.toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
    }

    scrollupState.active = false;
};

const setDashboardHeight = () => {
    const windowWidth = $(window).width();
    let height = 382;
    let contentHeight = "285px";
    const heightLineTopBottom = 110;
    const windowHeight = $(window).height();

    if (windowWidth <= 991 && windowWidth >= 768) {
        height = 455;
    } else if (windowWidth < 768) {
        height = 410;
    }

    if (windowHeight < height + heightLineTopBottom) {
        height = windowHeight - heightLineTopBottom;
        contentHeight = "230px";
    }

    $("#js-epuser-subline-content").css("max-height", contentHeight);

    return height;
};

const toggleDashboardMenu = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "epl_styles_user_menu" */ "@scss/epl/components/user_menu/index.scss");

    if (!dashboardBlockStatus) {
        return true;
    }

    if ($(window).width() < 768 && $(".js-open-chat.chat-active").length) {
        EventHub.trigger("chat:close-chat-popup");
    }

    dashboardBlockStatus = false;
    const toggleBtn = $(".js-navbar-toggle-btn");

    if (toggleBtn.hasClass("active")) {
        hideEpuserSubline();
        dashboardBlockStatus = true;
    } else {
        const epuserSubline = $("#js-epuser-subline");
        toggleBtn.removeClass("active");

        epuserSubline.slideUp(() => {
            let timeout = 0;
            const messages = $("#js-epuser-dashboard");

            if ($("#js-epl-header-line").hasClass("active")) {
                EventHub.trigger("navbar:toggle-dashboard-mobile-menu");
                timeout = 300;
            }

            $("body").addClass("locked");

            if ($(window).scrollTop() > 500) {
                $("#js-btn-scrollup").hide();
            }

            messages.hide();
            epuserSubline.height(setDashboardHeight()).delay(timeout).slideDown();
            setTimeout(() => {
                toggleHeaderLineBackground();
            }, timeout);
            showLoader(epuserSubline);
            onResizeCallback(() => {
                epuserSubline.height(setDashboardHeight());
            });

            postRequest(`${SUBDOMAIN_URL}dashboard/ajax_view_dashboard_new`)
                .then(resp => {
                    dashboardBlockStatus = true;
                    if (resp.mess_type === "success") {
                        messages.html(resp.menu_content).show();
                        // Lazy loading images
                        lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
                        showDashboardMenu();
                        toggleBtn.addClass("active").find(".ep-icon").toggleClass("ep-icon_arrow-down ep-icon_arrow-up");
                    } else {
                        epuserSubline.slideUp(() => {
                            $("#js-epl-header-line-background").removeClass("active");
                        });
                        systemMessages(resp.message, resp.mess_type);
                    }
                })
                .catch(handleRequestError)
                .finally(() => hideLoader(epuserSubline));
        });
    }

    return true;
};

export default toggleDashboardMenu;

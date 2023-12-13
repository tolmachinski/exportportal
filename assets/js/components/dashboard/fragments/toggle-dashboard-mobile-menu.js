import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";

import showHeaderOverlay from "@src/components/navigation/fragments/show-header-overlay";
import hideSideNav from "@src/components/navigation/fragments/hide-side-nav";
import hideHeaderOverlay from "@src/components/navigation/fragments/hide-header-overlay";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import onResizeCallback from "@src/util/dom/on-resize-callback";

const menuBottomParams = {
    heightLineTopBottom: 100,
    heightUp: 426,
    contentHeight: "272px",
    type: "default",
    init: type => {
        menuBottomParams.type = type === "" ? "default" : type;
        menuBottomParams.calculate();
    },
    calculate: () => {
        menuBottomParams[menuBottomParams.type]();
        menuBottomParams.mobile();
        menuBottomParams.maxHeight();
    },
    maxHeight: () => {
        const maintenanceBanner = $("#js-maintenance-banner-container");
        const upgradeBanner = $(".js-upgrade-banner-top");
        const windowHeight = $(window).height();
        let fixedHeight = menuBottomParams.heightLineTopBottom;

        if (maintenanceBanner.length) {
            fixedHeight += maintenanceBanner.height();
        }

        if (upgradeBanner.length) {
            fixedHeight += upgradeBanner.height();
        }

        if (windowHeight < fixedHeight + menuBottomParams.heightUp) {
            menuBottomParams.heightUp = windowHeight - fixedHeight;
            menuBottomParams.contentHeight = "230px";
        }
    },
    mobile: () => {
        if ($(window).width() <= 767 && menuBottomParams.heightUp >= 426) {
            menuBottomParams.heightUp = 435;
            menuBottomParams.contentHeight = "281px";
        }
    },
    default: () => {
        menuBottomParams.heightUp = 426;
        menuBottomParams.contentHeight = "272px";
    },
    notLogged: () => {
        menuBottomParams.heightUp = 315;
    },
    shipper: () => {
        menuBottomParams.heightUp = 111;
    },
    seller: () => {
        menuBottomParams.heightUp = 524;
        menuBottomParams.contentHeight = "370px";
    },
};

const loadNavBottom = async () => {
    const mobileDashboard = $("#js-mep-header-bottom-toggle");

    try {
        showLoader(mobileDashboard);

        const { mess_type: messType, html, message } = await postRequest(`${SUBDOMAIN_URL}dashboard/ajax_view_dashboard_mob`);

        if (messType === "success") {
            $("#js-mep-header-dashboard").html(html).show();
        } else {
            systemMessages(message, messType);
        }

        lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
        $("#js-mep-user-content").css("max-height", menuBottomParams.contentHeight);
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(mobileDashboard);
    }
};

const headerBottomReinitHeight = () => {
    menuBottomParams.calculate();

    const bottomToggleBlock = $("#js-mep-header-bottom-toggle");
    bottomToggleBlock.css("height", menuBottomParams.heightUp);
    $("#js-mep-user-content").css("max-height", menuBottomParams.contentHeight);
};

const headerBottomShow = button => {
    const bottomToggleBlock = $("#js-mep-header-bottom-toggle");
    const mobileHeaderDashboard = $("#js-mep-header-dashboard");
    const headerSearchForm = $(".js-community-search-header");
    let delay = 0;

    if (headerSearchForm.is(":visible")) {
        delay = 500;
        headerSearchForm.slideToggle();
    }

    if ($("#js-ep-header-top").is(":visible")) {
        delay = 500;
        hideSideNav();
    }

    menuBottomParams.init(button.data("type"));

    if (button.hasClass("active")) {
        hideHeaderOverlay();

        bottomToggleBlock
            .stop(true)
            .delay(delay)
            .animate({ height: 0 }, 500, function animate() {
                $(this).removeClass("active");
                button.removeClass("active");
                mobileHeaderDashboard.hide().html("");
            });
    } else {
        showHeaderOverlay();

        if (bottomToggleBlock.height() > 0) {
            bottomToggleBlock
                .stop(true)
                .delay(delay)
                .animate({ height: 0 }, 500, function animate() {
                    $(this).removeClass("active");
                    $(".mep-header-bottom-nav__link.active").removeClass("active");
                    mobileHeaderDashboard.hide();

                    bottomToggleBlock
                        .stop(true)
                        .delay(delay)
                        .animate({ height: menuBottomParams.heightUp }, 500, function animateNext() {
                            $(this).addClass("active");
                            button.addClass("active");
                            loadNavBottom();
                        });
                });
        } else {
            bottomToggleBlock
                .stop(true)
                .delay(delay)
                .animate({ height: menuBottomParams.heightUp }, 500, function animate() {
                    $(this).addClass("active");
                    button.addClass("active");
                    loadNavBottom();
                });
        }
    }
};

const toggleDashboardMobileMenu = async button => {
    const buttonEl = $(button);
    const toggleTop = $("#js-ep-header-content-search");

    if (toggleTop.height() > 0) {
        toggleTop.stop(true).slideUp(200, function slideUp() {
            toggleTop.removeAttr("style");
            $(".js-ep-header-mobile-link-search.active").removeClass("active");
            headerBottomShow(buttonEl);
            lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
        });
    } else {
        headerBottomShow(buttonEl);
    }

    let windowInitSize = $(window).width();
    $(window).off("resize.navHeaderMenu");
    onResizeCallback(
        () => {
            const windowW = $(window).width();
            if ($(window).width() < 992 && windowInitSize !== windowW) {
                windowInitSize = windowW;
                const toggle = $("#js-mep-header-bottom-toggle");
                if (toggle.length && toggle.height() > 0) {
                    headerBottomReinitHeight();
                }
            }
        },
        window,
        "navHeaderMenu"
    );
};

export default toggleDashboardMobileMenu;

import $ from "jquery";

import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import { intval } from "@src/util/number";
import existCookie from "@src/util/cookies/exist-cookie";
import getCookie from "@src/util/cookies/get-cookie";
import setCookie from "@src/util/cookies/set-cookie";
import onResizeCallback from "@src/util/dom/on-resize-callback";

let lastNotify = 0;

export default () => {
    lastNotify = intval($("#js-popover-nav-hidden .notify-popover").data("notify"));
    const popoverMep = ".js-popover-mep";
    const popoverNav = $(".js-popover-nav");
    const popoverContent = $("#js-popover-nav-hidden").html();

    if (!BACKSTOP_TEST_MODE) {
        popoverNav.popover({
            trigger: "manual",
            html: true,
            placement: "bottom",
            template: '<div class="popover popover-notification" role="tooltip"><div class="arrow"></div><div class="js-notify-popover popover-body p-0"></div></div>',
            content: popoverContent,
        });

        $(popoverMep).popover({
            trigger: "click",
            html: true,
            placement: "top",
            template: '<div class="popover popover-notification" role="tooltip"><div class="arrow"></div><div class="js-notify-popover popover-body p-0"></div></div>',
            content: popoverContent,
        });

        $(".js-popover-messages").popover({
            trigger: "hover",
            html: true,
            placement: "bottom",
            template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body p-0"></div></div>',
            content() {
                return $("#js-popover-messages-hidden").html();
            },
        });
    }

    popoverNav.on("show.bs.popover", () => {
        $(".js-notify-popover").replaceWith(popoverContent);
    });

    const delayedPopover = (() => {
        let timeout = null;
        let showed = false;
        const popoverEl = $(".js-popover-nav");

        return type => {
            if (type === "show" && !showed) {
                clearTimeout(timeout);
                popoverEl.popover(type);
                showed = true;
            }
            if (type === "hide") {
                showed = false;
                timeout = setTimeout(() => {
                    popoverEl.popover(type);
                }, 300);
            }
        };
    })();

    const notifyCountNew = intval($("#js-popover-nav-count-new").text());
    const notifyCountTotal = intval($("#js-popover-nav-count-total").text());
    if (
        !BACKSTOP_TEST_MODE &&
        ((!existCookie("_ep_view_notify") && (notifyCountNew > 0 || notifyCountTotal > 0)) ||
            (lastNotify > 0 && intval(getCookie("_ep_view_notify")) !== lastNotify && (notifyCountNew > 0 || notifyCountTotal > 0)))
    ) {
        let typeNotify = ".js-popover-mep";

        if ($(globalThis).width() > 991) {
            typeNotify = ".js-popover-nav";
        }

        $(typeNotify).popover("show");

        setTimeout(() => {
            $(typeNotify).popover("hide");
            setCookie("_ep_view_notify", lastNotify, { expires: 7 });
        }, 5000);
    }

    $(document)
        .on("mouseenter", ".js-popover-nav, .popover", () => {
            if ($(globalThis).width() > 991) {
                delayedPopover("show");
            }
        })
        .on("mouseleave", ".js-popover-nav, .popover", () => delayedPopover("hide"));

    if ($(globalThis).width() < 992) {
        $("body").on("click", e => {
            const target = $(e.target);
            if (!target.closest(popoverMep).length && !target.closest(".popover").length) {
                $(popoverMep).popover("hide");
            }
        });

        let hideScroll;
        $(document).on("scroll", () => {
            clearTimeout(hideScroll);
            hideScroll = setTimeout(() => {
                if ($(".popover").is(":visible")) {
                    $(popoverMep).popover("hide");
                }
            }, 250);
        });
    }

    let clickState = false;
    onResizeCallback(() => {
        clickState = false;
        if ($(".popover").is(":visible")) {
            $(popoverMep).popover("hide");
        }
    }, globalThis);

    $(popoverMep).on("click", () => {
        if (clickState) {
            clickState = false;
            $(popoverMep).addClass("fancybox.ajax fancyboxMep").trigger("click").removeClass("fancybox.ajax fancyboxMep");
            setTimeout(() => {
                $(popoverMep).popover("hide");
                clickState = false;
            }, 100);
        } else {
            $(".js-notify-popover").replaceWith(popoverContent);
            clickState = true;
        }
    });

    $(document).on("click", ".js-popover-link", () => {
        clickState = false;
    });
};

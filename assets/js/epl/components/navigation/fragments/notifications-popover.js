import $ from "jquery";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import { intval } from "@src/util/number";
import createPopover, { hidePopover, showPopover } from "@src/epl/common/popover/index";
import existCookie from "@src/util/cookies/exist-cookie";
import getCookie from "@src/util/cookies/get-cookie";
import setCookie from "@src/util/cookies/set-cookie";
import onResizeCallback from "@src/util/dom/on-resize-callback";

let lastNotify = 0;

const triggerNotificationsPopover = async () => {
    lastNotify = intval($("#js-popover-nav-hidden .js-notify-popover").data("notify"));
    const notifyCountNew = intval($("#js-popover-nav-count-new").text());
    const notifyCountTotal = intval($("#js-popover-nav-count-total").text());
    const instance = await createPopover("#js-popover-notifications", "#js-tooltip-notifications");
    const instanceMobile = await createPopover("#js-popover-notifications-mep", "#js-tooltip-notifications-mep", { trigger: "click" });
    let tooltip = $("#js-tooltip-notifications")[0];
    const toggleNotificationsPopover = async () => {
        if (
            !BACKSTOP_TEST_MODE &&
            ((!existCookie("_ep_view_notify") && (notifyCountNew > 0 || notifyCountTotal > 0)) ||
                (lastNotify > 0 && intval(getCookie("_ep_view_notify")) !== lastNotify && (notifyCountNew > 0 || notifyCountTotal > 0)))
        ) {
            let typeInstance = instance;

            if ($(window).width() < 992) {
                typeInstance = instanceMobile;
                // eslint-disable-next-line prefer-destructuring
                tooltip = $("#js-tooltip-notifications-mep")[0];
            }

            if (!$(tooltip).hasClass("active")) {
                await showPopover(typeInstance, tooltip);
            }

            setTimeout(async () => {
                await hidePopover(typeInstance, tooltip);
                setCookie("_ep_view_notify", lastNotify, { expires: 7 });
            }, 5000);
        }
    };

    toggleNotificationsPopover();

    let hideScroll;
    $(document).on("scroll", () => {
        clearTimeout(hideScroll);
        hideScroll = setTimeout(() => {
            if ($(globalThis).width() < 992) {
                // eslint-disable-next-line prefer-destructuring
                tooltip = $("#js-tooltip-notifications-mep")[0];
            }

            if ($(".tooltip").is(":visible")) {
                hidePopover(instance, tooltip);
            }
        }, 250);
    });

    onResizeCallback(() => toggleNotificationsPopover());
};

export default triggerNotificationsPopover;

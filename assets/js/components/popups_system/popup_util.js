import $ from "jquery";

import onResizeCallback from "@src/util/dom/on-resize-callback";
import { DISABLE_POPUP_SYSTEM, SHIPPER_PAGE } from "@src/common/constants";
import EventHub from "@src/event-hub";

const namePopupBannerWrapper = "js-popup-system-banner";
const namePopupBannerItem = "js-popup-system-banner-item";

class TriggerPopup {
    constructor(name) {
        this.popupName = name;
        this.popupTriggeredShow = false;
        this.popupTriggered = false;

        this.init();
    }

    init() {
        const that = this;

        $(document).on("mouseleave", event => {
            if (event.pageY < 50 && !that.popupTriggered && !DISABLE_POPUP_SYSTEM) {
                EventHub.trigger("popup:call-popup", { detail: { name: that.popupName } });
            }
        });
    }

    clear() {
        const that = this;

        that.popupTriggered = true;
    }

    getStatus() {
        const that = this;

        return that.popupTriggered;
    }
}

const resizePopupBanner = () => {
    const block = $(`#${namePopupBannerWrapper}`);

    if (block.children().length === 0) {
        return;
    }

    const calcMinusMenu =
        $("#js-ep-header-fixed-top").height() -
        ($("#js-ep-header-content-search").css("display") === "none" ? 0 : $("#js-ep-header-content-search").height()) +
        ($("#js-mep-header-bottom").css("display") === "none" ? 0 : 50);
    const wrapperBodyHeight = $(window).height() - calcMinusMenu;
    let bannersWrapperHeight = 0;

    block.children().each(function eachBanners() {
        bannersWrapperHeight += $(this).outerHeight(true);
    });

    if (wrapperBodyHeight <= bannersWrapperHeight) {
        block.addClass("popup-system-banner--top");
    } else {
        block.removeClass("popup-system-banner--top");
    }
};

const removePopupBanner = $this => {
    $this.closest(`.${namePopupBannerItem}`).fadeOut("slow", function removeBanner() {
        $(this).remove();
    });

    resizePopupBanner();
};

const addPopupBanner = block => {
    const wrapper = $(block).wrapAll(`<div class="${namePopupBannerItem} popup-system-banner__item"></div>`).parent();
    $(`#${namePopupBannerWrapper}`).append(wrapper);

    resizePopupBanner();
};

const createPopupBannerWrapper = async () => {
    let additionalClass = "";

    if (SHIPPER_PAGE) {
        additionalClass = " popup-system-banner--epl";
    }
    // @ts-ignore
    await import("@scss/components/popups/index.scss");
    $(document.body).append(`<div id="${namePopupBannerWrapper}" class="popup-system-banner${additionalClass}"/>`);

    onResizeCallback(() => resizePopupBanner());
};

export { removePopupBanner, addPopupBanner, createPopupBannerWrapper, resizePopupBanner };
export default TriggerPopup;

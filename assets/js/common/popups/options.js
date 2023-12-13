import $ from "jquery";
import { TEMPLATES } from "@src/common/popups/templates";
import { beforeLoad } from "@src/common/popups/callbacks/onBeforeLoad";

export const BASE_WIDTH = "70%";
export const BASE_MAX_WIDTH = 700;
export const BASE_WIDTH_ALTERNATE = 0.7;
export const BASE_WIDTH_PERCENT = 0.7;
export const AUTO_WIDTH = "auto";
export const BASE_HEIGHT = "auto";
export const BASE_PADDING = 30;
export const BASE_CLOSE_BTN_WRAPPER = ".fancybox-skin .fancybox-title";

export const BASE_OPTIONS = {
    width: BASE_WIDTH,
    height: BASE_HEIGHT,
    maxWidth: BASE_MAX_WIDTH,
    closeBtnWrapper: BASE_CLOSE_BTN_WRAPPER,
    tpl: TEMPLATES,
    helpers: {
        title: { type: "inside", position: "top" },
        overlay: { locked: true, closeClick: false },
    },
    closeBtn: true,
    autoSize: false,
    padding: BASE_PADDING,
    closeClick: false,
    nextClick: false,
    arrows: false,
    keys: null,
    mouseWheel: false,
    loop: false,
    beforeLoad,
    afterLoad: () => {
        // добавлен для фикса переоткрытия модалок, возможно потом как-то лучше сделаем.
        const html = $("html");
        const htmlIsLocked = html.hasClass("fancybox-margin fancybox-lock");
        if (htmlIsLocked) return;

        // eslint-disable-next-line func-names
        const fixedElement = $("div").filter(function () {
            return $(this).css("position") === "fixed" && !$(this).hasClass("fancybox-overlay") && !$(this).hasClass("fancybox-wrap");
        });
        fixedElement.addClass("fancybox-margin fancybox-lock");
        html.addClass("fancybox-margin fancybox-lock");
    },
};

export const ITEM_MODAL_OPTIONS = {
    width: "100%",
    height: "100%",
    maxWidth: 770,
};

export const MEP_MODAL_OPTIONS = {
    width: "100%",
    maxWidth: 1090,
    maxHeight: 700,
};

export const SELECT_LANG_MODAL_OPTIONS = {
    width: AUTO_WIDTH,
};

export const GALLERY_MODAL_OPTIONS = {
    maxWidth: 900,
    preload: 0,
    arrows: true,
};

export const VIDEO_MODAL_OPTIONS = {
    maxWidth: 700,
};

export const SIDEBAR_MODAL_OPTIONS = {
    width: AUTO_WIDTH,
    height: "100%",
    openMethod: "slideIn",
    openSpeed: 250,
    padding: 5,
};

export const IFRAME_MODAL_OPTIONS = {
    width: 700,
    type: "iframe",
    iframe: {
        preload: false,
    },
};

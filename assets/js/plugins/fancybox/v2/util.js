import $ from "jquery";

const boot = async () => {
    const { default: Fancybox } = await import(/* webpackChunkName: "fancybox-bridge" */ "@src/plugins/fancybox/v2/index.js");

    return Fancybox;
};
/**
 * Closes current fancybox instance.
 */
const closeFancyboxPopup = async () => {
    const Fancybox = await boot();
    const instance = await Fancybox();
    instance.close(true);
};

const updateFancyboxPopup = async () => {
    const Fancybox = await boot();
    const instance = await Fancybox();
    instance.update(true);
};

const closeFancyBox = () => {
    if ($(".fancybox-skin .dtfilter-popup .nav-tabs").length) {
        const navTabs = $(".fancybox-skin .dtfilter-popup .nav-tabs");
        const firstLi = navTabs.find("li:first-child");
        const tabContent = $(".fancybox-skin .dtfilter-popup .tab-content");

        firstLi.find(".nav-link").addClass("active").end().siblings().find(".nav-link").removeClass("active");
        tabContent.find(".tab-pane:first-child").addClass("active").siblings().removeClass("active");
    }

    // @ts-ignore
    if ($.fn.validationEngine) {
        // @ts-ignore
        $(".validateModal").validationEngine("detach");
    }

    closeFancyboxPopup();
};

const closeFancyBoxConfirm = function () {
    if (!$(this).hasClass("js-confirm-dialog")) {
        // @ts-ignore
        if ($.fn.validationEngine) {
            // @ts-ignore
            $(".validateModal").validationEngine("detach");
        }

        closeFancyboxPopup();
    }
};

const onChangePopupContent = classConfirm => {
    let className = classConfirm;
    if (!className) {
        className = "js-confirm-dialog";
    }

    $(".fancybox-title a.js-close-fancybox").data("jsAction", "fancy-box:close").addClass(className);
};

const calculateModalBoxSizes = () => {
    const bodyWidth = $("body").width();
    const bodyHeight = $(globalThis).height();
    const adjustments = {};

    if (bodyWidth < 768 || bodyHeight < 636) {
        adjustments.width = "99%";
        adjustments.height = "100%";
        adjustments.gutter = 15;
    } else {
        adjustments.width = "79%";
        adjustments.height = "auto";
        adjustments.gutter = 30;
    }
    adjustments.margin = 5;

    return adjustments;
};

export { closeFancyBox };
export { closeFancyboxPopup };
export { updateFancyboxPopup };
export { closeFancyBoxConfirm };
export { onChangePopupContent };
export { calculateModalBoxSizes };

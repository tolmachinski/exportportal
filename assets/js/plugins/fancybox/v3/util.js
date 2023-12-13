import $ from "jquery";

const boot = async () => {
    const { default: Fancybox } = await import(/* webpackChunkName: "fancybox-bridge" */ "@src/plugins/fancybox/v3/index.js");

    return Fancybox;
};

/**
 * Closes current fancybox instance.
 */
const closeFancyboxPopup = async () => {
    const Fancybox = await boot();
    const fancybox = await Fancybox();
    const instance = fancybox.getInstance();

    if (instance) {
        // @ts-ignore
        instance.close();
    }
};

const updateFancyboxPopup3 = async () => {
    const Fancybox = await boot();
    const fancybox = await Fancybox();
    const instance = fancybox.getInstance();

    if (instance) {
        instance.update();
    }
};

const closeFancyBoxConfirm = function () {
    if (!$(this).hasClass("js-confirm-dialog")) {
        closeFancyboxPopup();
    }
};

const onChangePopupContent = function () {
    $(this).closest(".fancybox-content").find(".js-close-modal-btn").addClass("js-confirm-dialog").data("jsAction", "fancybox:close");
};

export { closeFancyboxPopup };
export { updateFancyboxPopup3 };
export { closeFancyBoxConfirm };
export { onChangePopupContent };

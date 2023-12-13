import { TEMPLATES } from "@src/epl/common/popups/templates";
import onInit from "@src/epl/common/popups/callbacks/onInit";
import afterLoad from "@src/epl/common/popups/callbacks/afterLoad";
import beforeClose from "@src/epl/common/popups/callbacks/beforeClose";
import afterClose from "@src/epl/common/popups/callbacks/afterClose";

// eslint-disable-next-line import/prefer-default-export
export const BASE_OPTIONS = {
    defaultType: "inline",
    closeExisting: true,
    modal: true,
    loop: false,
    infobar: false,
    arrows: false,
    smallBtn: false,
    closeBtn: true,
    autoFocus: false,
    closeBtnWrapper: ".fancybox-header",
    buttons: [],
    ajax: {
        settings: null,
    },
    ...TEMPLATES,
    onInit,
    afterLoad,
    beforeClose,
    afterClose,
};

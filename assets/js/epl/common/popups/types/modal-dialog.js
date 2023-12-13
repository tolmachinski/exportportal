import { getAdvancedOptions, openFancyboxPopup } from "@src/plugins/fancybox/v3/index";
import { BASE_OPTIONS } from "@src/epl/common/popups/options";

import EventHub from "@src/event-hub";

/**
 *
 * @param {any} params
 */
const openDialogModal = async function (params) {
    const {
        type = "inline",
        title = "",
        subTitle = "",
        content = "",
        contentFooter = "",
        delimiterClass = "fancybox-dialog--content-delimeter",
        buttons = [],
        classes = "",
        iconModal = undefined,
        isAjax = false,
        ajaxUrl = "",
        closable: closableModal = true,
        keepOtherModals: closeOtherModals = true,
        modal = false,
    } = params;

    let { category: typeModal = "info", iconModalType = "ok-stroke2", titleType = "" } = params;

    const modalData = {
        info: {
            icon: "info-character fs-25",
            title: "Info",
        },
        success: {
            icon: "ok-stroke2",
            title: "Success!",
        },
        warning: {
            icon: "warning-character",
            title: "Warning!",
        },
        error: {
            type: "danger",
            icon: "remove-stroke2 fs-20",
            title: "Error!",
        },
        question: {
            type: "info",
            icon: "question-character",
            title: "Question",
        },
    };

    iconModalType = modalData[typeModal].icon;
    titleType = modalData[typeModal].title;
    typeModal = modalData[typeModal].type ? modalData[typeModal].type : typeModal;

    const target = {
        type,
        title: title || titleType,
        src: content,
    };

    const typeModalClass = ` fancybox-dialog--${typeModal}${classes}`;
    const { adjustments, i18n, lang } = getAdvancedOptions();
    const options = {
        isAjax,
        ajaxUrl,
        iconModal,
        iconModalType,
        typeModal,
        typeModalClass,
        subTitle,
        buttons,
        delimiterClass,
        contentFooter,
        modal,
        touch: false,
        slideClass: `fancybox-dialog${typeModalClass}`,
        closeExisting: !closeOtherModals,
        closeBtn: Boolean(~~closableModal),
        isDialog: true,
        closeBtnWrapper: ".fancybox-dialog__header",
        bodyWrapper: ".fancybox-dialog__body",
    };

    return openFancyboxPopup(target, {
        ...BASE_OPTIONS,
        i18n: {
            [lang]: i18n,
        },
        ...adjustments,
        ...options,
    });
};

/**
 *
 * @param {JQuery.Event} e
 * @param {JQuery} openButton
 */
const openConfirmDialog = function (e, openButton) {
    const { title, message: subTitle, category: typeModal = "info", icon } = openButton.data();

    return openDialogModal({
        title,
        subTitle,
        icon,
        type: typeModal,
        keepOtherModals: true,
        buttons: [
            {
                label: "Ok",
                cssClass: "btn-primary",
                action(dialog) {
                    const action = openButton.data("jsAction") || null;

                    if (action) {
                        EventHub.trigger(action, [openButton, this, dialog, e]);
                        dialog.close();

                        return;
                    }

                    dialog.close();
                },
            },
            {
                label: "Cancel",
                cssClass: "btn-outline-primary",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

export { openDialogModal, openConfirmDialog };

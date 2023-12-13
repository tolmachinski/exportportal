import { openResultModal } from "@src/plugins/bootstrap-dialog/index";

/**
 * It opens a modal This seller is certified
 * @param {JQuery} btn
 */
const openCertifiedModal = btn => {
    const { title, subTitle, iconImage } = btn.data();

    openResultModal({
        type: "certified",
        title,
        subTitle,
        titleImage: iconImage,
        closable: true,
        closeByBg: true,
        classes: "bootstrap-dialog--results-certified",
    });
};

export default openCertifiedModal;

import $ from "jquery";

import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { SITE_URL } from "@src/common/constants";
import { translate } from "@src/i18n";
import onResizeCallback from "@src/util/dom/on-resize-callback";

import FileSaver from "file-saver";

import "@scss/user_pages/downloadable_materials/index_page.scss";

const showShareModal = async (e, element) => {
    await loadBootstrapDialog();
    openResultModal({
        title: "Share this with a friend",
        subTitle: "Share the downloadable material with a friend",
        content: $(element).data("link"),
        isAjax: true,
        closable: true,
        validate: true,
        classes: "bootstrap-dialog--unset-scroll",
        type: "info",
        buttons: [],
    });
};

const downloadMaterial = async (e, element, showPopup) => {
    FileSaver.saveAs(`${SITE_URL}downloadable_materials/download/${$(element).data("id")}`);

    if (showPopup) {
        await loadBootstrapDialog();
        openResultModal({
            title: "Success!",
            subTitle: "Thanks for downloading this page.",
            type: "success",
            closable: true,
            classes: "tac",
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "ok" }),
                    cssClass: "btn-light",
                    action: dialogRef => dialogRef.close(),
                },
            ],
        });
    }
};

const startAutoDownload = () => {
    const needDownload = $("#needDownload");
    if (needDownload.length) {
        globalThis.history.pushState({}, globalThis.document.title || "", $("#js-dwn-download").data("href"));
        downloadMaterial("", "#js-dwn-download", true);
        needDownload.remove();
    }
};

const callAdaptivePosition = () => {
    const cover = $("#js-dwn-materials-cover");
    if (globalThis.matchMedia("(min-width: 601px)").matches && $("#js-dwn-materials-info").outerHeight() > cover.outerHeight()) {
        if (!cover.hasClass("animated")) {
            cover.addClass("animated");
            cover.css("top", cover.css("top"));
            setTimeout(() => cover.css("top", "85px"), 100);
        }
    } else {
        cover.removeClass("animated");
    }
};

const adaptivePositionOfImage = () => {
    callAdaptivePosition();
    onResizeCallback(() => callAdaptivePosition());
};

export { showShareModal, downloadMaterial, startAutoDownload, adaptivePositionOfImage };

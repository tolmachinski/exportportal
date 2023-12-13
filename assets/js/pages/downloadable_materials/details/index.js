import $ from "jquery";

import showSuccessSubscribtionPopupIfNeeded from "@src/components/popups/subscribe/index";
import openSharePopup from "@src/util/share/open-share-popup";
import EventHub from "@src/event-hub";
import { showShareModal, downloadMaterial, startAutoDownload, adaptivePositionOfImage } from "@src/pages/downloadable_materials/details/fragments/index";

$(() => {
    // Functions
    startAutoDownload();
    showSuccessSubscribtionPopupIfNeeded();
    adaptivePositionOfImage();

    // Events
    EventHub.on("user_share:socials", (e, button) => openSharePopup(button));
    EventHub.on("user_action:download", (e, element) => downloadMaterial(e, element));
    EventHub.on("user_register:share_view", (e, element) => showShareModal(e, element));
});

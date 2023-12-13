import $ from "jquery";

import openSharePopup from "@src/util/share/open-share-popup";
import { exitExploreUser } from "@src/components/footer/fragments/explore-user/index";
import EventHub from "@src/event-hub";

import "@scss/user_pages/general/footer_styles.scss";

$(() => {
    EventHub.on("footer:share-popup", (e, button) => openSharePopup(button));
    EventHub.on("footer:exit-explore-user", () => exitExploreUser());
});

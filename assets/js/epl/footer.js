import $ from "jquery";
import { exitExploreUser } from "@src/components/footer/fragments/explore-user/index";
import EventHub from "@src/event-hub";

import "@scss/user_pages/general/footer_styles.scss";

$(() => {
    EventHub.on("footer:exit-explore-user", () => exitExploreUser());
});

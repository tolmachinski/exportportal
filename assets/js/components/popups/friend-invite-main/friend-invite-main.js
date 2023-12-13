import $ from "jquery";

import { GROUP_SITE_URL } from "@src/common/constants";
import EventHub from "@src/event-hub";
import openFriendInvitePopup from "@src/util/share/open-friend-invite-popup";

let inviteMessages = [];

const setInviteMessage = inviteMessagesKey => {
    const inviteBtns = $(".js-social-invite-button");
    const inviteEmailBtn = $(".js-email-invite-button");
    const emailTemplate = inviteEmailBtn.data("social-template");
    let socialTemplate = "";

    inviteBtns.each(function eachBtn() {
        socialTemplate = $(this).data("social-template");
        $(this).data("text", inviteMessages[socialTemplate][inviteMessagesKey]);
    });

    if (inviteEmailBtn.length) {
        inviteEmailBtn.attr({
            "data-fancybox-href": `${GROUP_SITE_URL}invite/popup_forms/invite_by_email?&template=${emailTemplate}&message_key=${inviteMessagesKey}`,
        });
    }
};

const initCheckboxes = async () => {
    $(".js-radio-blue").on("change", function () {
        setInviteMessage($(this).val().toString());
    });
};

export default inviteMessagesArray => {
    inviteMessages = JSON.parse(inviteMessagesArray);
    const [firstValue] = Object.values(inviteMessages);
    const [inviteMessagesKey] = Object.keys(firstValue);

    initCheckboxes();
    setInviteMessage(inviteMessagesKey);

    EventHub.off("navbar:friend-invite");
    EventHub.on("navbar:friend-invite", (e, button) => openFriendInvitePopup(button));
};

import $ from "jquery";

import { translate } from "@src/i18n";
import { addCounter } from "@src/plugins/textcounter/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

import { SUBDOMAIN_URL } from "@src/common/constants";

/**
 * Handles follow event.
 *
 * @param {JQuery} form
 * @param {JQuery} button
 */
const onFollow = async (form, button) => {
    const wrapper = form.closest(".js-modal-flex");
    const submitButton = form.find("button[type=submit]");
    const unfollowUserTxt = translate({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_unfollow_user" });

    showLoader(wrapper, translate({ plug: "general_i18n", text: "sending_message_form_loader" }));
    submitButton.addClass("disabled");

    try {
        const { mess_type: messageType, message, user = null } = await postRequest(
            `${SUBDOMAIN_URL}followers/ajax_followers_operation/follow_user`,
            form.serialize()
        );
        systemMessages(message, messageType);
        if (messageType === "success") {
            button
                .removeClass("fancybox.ajax fancyboxValidateModal")
                .attr("title", unfollowUserTxt)
                .addClass("call-function")
                .data("user", user)
                .data("callback", "unfollow_user")
                .data("title", unfollowUserTxt)
                .attr("href", "#");

            if (button.find("i").length) {
                button.find("i").toggleClass("ep-icon_reply-right-empty ep-icon_reply-left-empty");
                button.find("span").html(unfollowUserTxt);
            } else {
                button.toggleClass("ep-icon_reply-right-empty ep-icon_reply-left-empty");
            }

            closeFancyBox();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(wrapper);
        submitButton.removeClass("disabled");
    }
};

export default () => {
    addCounter($(".js-textcounter-follow-user-message"));

    EventHub.on("follow:user-popup-submit-form", (e, form, status, button) => onFollow(form, button));
};

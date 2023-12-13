import $ from "jquery";
import EventHub from "@src/event-hub";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import { open } from "@src/plugins/fancybox/v2/index";
import { systemMessages } from "@src/util/system-messages/index";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { removePopupBanner } from "@src/components/popups_system/popup_util";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const removeBlogPopup = $this => {
    removePopupBanner($this);
};

const closeBlogPopup = $this => {
    sentPopupViewed("hash_blog", "cancel");
    removeBlogPopup($this);
};

const submitBlogPopup = async $form => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/save/hash_blog`, $form.serialize())
        .then(response => {
            if (response.message && (!response.mess_type || response.mess_type !== "success")) {
                systemMessages(response.message, response.mess_type || null);
            }

            open(
                { content: $("#js-become-member").html() },
                {
                    width: "70%",
                    height: "auto",
                    maxWidth: 348,
                }
            );

            EventHub.off("fancy-box:close");
            EventHub.on("fancy-box:close", () => closeFancyboxPopup());

            removeBlogPopup($form);
            sentPopupViewed("hash_blog");
        })
        .catch(handleRequestError);
};

export default () => {
    enableFormValidation($("#js-popup-blog-appearing-form"));
    EventHub.off("form:submit-form-blog");
    EventHub.on("form:submit-form-blog", (e, button) => submitBlogPopup(button));
    EventHub.off("popup:close-hash-blog");
    EventHub.on("popup:close-hash-blog", (e, button) => closeBlogPopup(button));
};

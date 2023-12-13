import $ from "jquery";

import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { open } from "@src/plugins/fancybox/v2/index";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import { SUBDOMAIN_URL } from "@src/common/constants";

import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import loadingValidationEngine from "@src/plugins/validation-engine/lazy";
import winPopup from "@src/util/share/win-popup";
import EventHub from "@src/event-hub";

const shareWithFamilyFriends = function ($this) {
    const params = $this.closest(".js-share-with-family-friends");
    const type = $this.data("type");
    let popupUrl = "";
    let popupTitle = "Share with ";
    let popupOptions = "width=550,height=400,0,status=0";
    const socialUrl = params.data("url");
    const socialTitleText = params.data("type") === "item" ? "product" : "company";
    const socialTitle = `Hello! Check out this ${socialTitleText} on Export Portal!`;

    switch (type) {
        case "facebook":
            popupUrl = `https://www.facebook.com/share.php?u=${socialUrl}&quote=${socialTitle}`;
            popupTitle += "facebook";
            break;
        case "twitter":
            popupUrl = `https://twitter.com/intent/tweet?text=${socialTitle}&url=${socialUrl}`;
            popupTitle += "twitter";
            break;
        case "pinterest":
            popupUrl = `https://pinterest.com/pin/create/bookmarklet/?media=${params.data("img")}&url=${socialUrl}&is_video=false&description=${socialTitle}`;
            popupTitle += "pinterest";
            popupOptions = "width=750,height=400,0,status=0";
            break;
        case "linkedin":
            popupUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${socialUrl}&title=${socialTitle}`;
            popupTitle = "linkedin";
            break;
        default:
            break;
    }

    winPopup(popupUrl, popupTitle, popupOptions);
};

const shareSetStatistic = function ($this) {
    const params = $this.closest(".js-share-with-family-friends");
    const type = params.data("type");
    const id = params.data("id");
    const typeSharing = $this.data("type");

    return postRequest(`${SUBDOMAIN_URL}user/ajax_user_operation/share_statistic`, { type, id, typeSharing }).then().catch(handleRequestError);
};

const callShareWithFamilyFriends = function ($this) {
    if ($this.data("fancybox-href") !== undefined) {
        const adjustments = calculateModalBoxSizes();

        open(
            {
                title: $this.data("title"),
                type: "ajax",
                href: $this.data("fancybox-href"),
            },
            {
                padding: adjustments.gutter,
                beforeLoad: () => {
                    $(document).on("click focusout", ".validateModal input", loadingValidationEngine);
                    $(document).on("submit", ".validateModal", loadingValidationEngine);
                },
            }
        );
        closeAllDialogs();
    } else {
        shareSetStatistic($this);
        shareWithFamilyFriends($this);
    }
};

export default () => {
    EventHub.off("user:call-share-with-family-friends");
    EventHub.on("user:call-share-with-family-friends", (e, button) => callShareWithFamilyFriends(button));
};

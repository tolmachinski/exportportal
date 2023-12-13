import $ from "jquery";
import winPopup from "@src/util/share/win-popup";

const openFriendInvitePopup = function (btn) {
    let popupUrl;
    let popupTitle;
    const viewportwidth = document.documentElement.clientWidth;
    const left = viewportwidth;
    const popupOptions = `width=550,height=400,status=0,left=${left}`;
    const socialUrl = $(btn).data("url");
    const socialText = $(btn).data("text");
    switch ($(btn).data("social")) {
        case "facebook":
            popupUrl = `https://www.facebook.com/share.php?u=${socialUrl}&quote=${socialText}`;
            popupTitle = "Share with facebook";
            break;
        case "twitter":
            popupUrl = `https://twitter.com/intent/tweet?text=${socialText}`;
            popupTitle = "Share with twitter";
            break;
        case "linkedin":
            popupUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${socialUrl}`;
            popupTitle = "Share with linkedin";
            break;
        default:
            break;
    }

    winPopup(popupUrl, popupTitle, popupOptions);
};

export default openFriendInvitePopup;

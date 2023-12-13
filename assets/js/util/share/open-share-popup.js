import $ from "jquery";
import htmlEscape from "@src/util/common/html-escape";
import winPopup from "@src/util/share/win-popup";

const openSharePopup = function (btn) {
    let popupUrl;
    let popupTitle;
    let popupOptions = "width=550,height=400,0,status=0";
    const socialUrl = $(btn).data("url");
    const socialTitle = encodeURIComponent(htmlEscape($(btn).data("title")));
    switch ($(btn).data("social")) {
        case "facebook":
            popupUrl = `https://www.facebook.com/share.php?u=${socialUrl}&title=${socialTitle}`;
            popupTitle = "Share with facebook";

            break;
        case "twitter":
            popupUrl = `https://twitter.com/intent/tweet?text=${socialTitle}&url=${socialUrl}`;
            popupTitle = "Share with twitter";

            break;
        case "pinterest":
            popupUrl = `https://pinterest.com/pin/create/bookmarklet/?media=${$(btn).data("img")}&url=${socialUrl}&is_video=false&description=${socialTitle}`;
            popupTitle = "Share with pinterest";
            popupOptions = "width=750,height=400,0,status=0";

            break;
        case "linkedin":
            popupUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${socialUrl}&title=${socialTitle}`;
            popupTitle = "Share with linkedin";

            break;
        default:
            break;
    }

    winPopup(popupUrl, popupTitle, popupOptions);
};

export default openSharePopup;

import $ from "jquery";

import onResizeCallback from "@src/util/dom/on-resize-callback";

const placeComments = ({ desktopWrapper, otherBlockContainer, otherNewsContainer, mobileWrapper, commentsWrapper, isShared }) => {
    if (window.matchMedia("(max-width: 991px)").matches) {
        if (!desktopWrapper.hasClass("is_mobile")) {
            desktopWrapper.addClass("is_mobile");

            mobileWrapper.removeClass("is_shared").append(commentsWrapper, otherNewsContainer, otherBlockContainer);
        }
    } else if (!isShared) {
        desktopWrapper.removeClass("is_mobile");
        mobileWrapper.addClass("is_shared");
    }
};

const placeCommentsAndOtherNews = () => {
    const otherBlockContainer = $("#js-other-container");
    const otherNewsContainer = $("#js-other-news-container");

    const commentsWrapper = $("#js-comments-wrapper");
    const desktopWrapper = $("#js-comments-desktop-wrapper");
    const mobileWrapper = $("#js-comments-mobile-wrapper");
    let isShared = mobileWrapper.hasClass("is_shared");

    placeComments({ desktopWrapper, otherBlockContainer, otherNewsContainer, mobileWrapper, commentsWrapper, isShared });

    onResizeCallback(() => {
        const otherBlockWrapper = $("#js-other-wrapper");
        const otherNewsWrapper = $("#js-other-news-wrapper");
        isShared = mobileWrapper.hasClass("is_shared");

        placeComments({ desktopWrapper, otherBlockContainer, otherNewsContainer, mobileWrapper, commentsWrapper, isShared });

        if (!window.matchMedia("(max-width: 991px)").matches && !isShared) {
            desktopWrapper.append(commentsWrapper);
            otherNewsWrapper.append(otherNewsContainer);
            otherBlockWrapper.append(otherBlockContainer);
        }
    });
};

export default placeCommentsAndOtherNews;

import $ from "jquery";

const hideBannerNewAddItemProcess = () => {
    $("#js-sticky-banner__item").css({
        transform: "translateX(-294px)",
        opacity: 0,
    });
};

// eslint-disable-next-line import/prefer-default-export
export { hideBannerNewAddItemProcess };

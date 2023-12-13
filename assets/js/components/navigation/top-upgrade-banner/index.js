import $ from "jquery";
import setCookie from "@src/util/cookies/set-cookie";
import definePositionEpuserSubline from "@src/components/navigation/fragments/define-position-epuser-subline";

const setCookieTopBannerBecomeCertified = () => {
    setCookie("showTopBannerBecomeCertified", 1, { expires: 365 });
};

const hideTopBannerBecomeCertified = () => {
    const banner = $(".js-upgrade-banner-top");
    banner.addClass("animate");
    definePositionEpuserSubline($("#js-ep-header-bottom").height() - banner.height());
    setTimeout(() => {
        banner.remove();
    }, 250);
    $("#js-ep-header").removeClass("ep-header--banner");
    $("html").removeClass("html--banner");

    setCookieTopBannerBecomeCertified();
};

const linkTopBannerBecomeCertified = link => {
    setCookieTopBannerBecomeCertified();
    globalThis.location.href = link;
};

export { hideTopBannerBecomeCertified, linkTopBannerBecomeCertified };

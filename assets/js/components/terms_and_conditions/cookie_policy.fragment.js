import $ from "jquery";
import mobileDataTable from "@src/util/common/mobile-data-table";
import onResizeCallback from "@src/util/dom/on-resize-callback";

import "@scss/user_pages/terms/cookie-policy.scss";

let cookiesExplanationInfo;
let cookiesExplanationThirdPartyInfo;

const cookiesExplanationInfoInit = function () {
    if (cookiesExplanationInfo.length > 0 && $(window).width() < 1100) {
        cookiesExplanationInfo.addClass("main-data-table--mobile");
    }

    if (cookiesExplanationThirdPartyInfo.length > 0 && $(window).width() < 1100) {
        cookiesExplanationThirdPartyInfo.addClass("main-data-table--mobile");
    }
};

export default () => {
    cookiesExplanationInfo = $("#js-cookies-explanation-info");
    cookiesExplanationThirdPartyInfo = $("#js-3rd-party-cookies-explanation-info");
    mobileDataTable(cookiesExplanationInfo);
    mobileDataTable(cookiesExplanationThirdPartyInfo);
    cookiesExplanationInfoInit();

    onResizeCallback(() => {
        cookiesExplanationInfoInit();
    });
};

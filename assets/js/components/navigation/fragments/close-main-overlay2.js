import $ from "jquery";

import hideSideNav from "@src/components/navigation/fragments/hide-side-nav";
import hideHeaderOverlay from "@src/components/navigation/fragments/hide-header-overlay";

const closeMainOverlay2 = () => {
    if ($(window).width() < 991) {
        $("#js-ep-header-content-search")
            .stop(true)
            .slideUp(200, function slideSearchContent() {
                $(this).removeAttr("style");
                $(".js-ep-header-mobile-link-search.active").removeClass("active");
            });
    }

    $("#js-mep-header-bottom-toggle")
        .stop(true)
        .animate({ height: 0 }, 200, function animate() {
            $(this).removeClass("active");
            $(".mep-header-bottom-nav__link.active").removeClass("active");
            $("#js-mep-header-dashboard").hide();
        });

    hideSideNav();
    hideHeaderOverlay();
};

export default closeMainOverlay2;

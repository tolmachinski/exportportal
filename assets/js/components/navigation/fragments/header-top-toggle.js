import $ from "jquery";

import showHeaderOverlay from "@src/components/navigation/fragments/show-header-overlay";
import hideSideNav from "@src/components/navigation/fragments/hide-side-nav";
import hideHeaderOverlay from "@src/components/navigation/fragments/hide-header-overlay";

const headerTopShow = button => {
    const toggleTop = $("#js-ep-header-content-search");
    const windowHeight = $(window).height();
    const heightLineTopBottom = 100;
    let delay = 0;
    let heightUp = 320;

    if (windowHeight < heightUp + heightLineTopBottom) {
        heightUp = windowHeight - heightLineTopBottom;
    }

    if ($("#js-ep-header-top").is(":visible")) {
        delay = 500;
        hideSideNav();
    }

    if (button.hasClass("active")) {
        hideHeaderOverlay();
        button.removeClass("active");

        // @ts-ignore
        toggleTop
            .stop(true)
            .delay(delay)
            .slideUp(500, () => {
                toggleTop.removeAttr("style");
            });
    } else {
        showHeaderOverlay();
        button.addClass("active");

        // @ts-ignore
        toggleTop
            .stop(true)
            .delay(delay)
            .slideDown(500, () => {
                toggleTop.css({ height: "", display: "flex" });

                if (heightUp !== 320) {
                    toggleTop.css({ height: heightUp });
                }
            });
    }
};

const headerTopToggle = function (button) {
    const toggleBottom = $("#js-mep-header-bottom-toggle");
    const toggleBottomIitem = $("#js-mep-header-dashboard");

    // hide bottom menu
    if (toggleBottom.height() > 0) {
        // @ts-ignore
        toggleBottom.stop(1).animate({ height: 0 }, 200, function animate() {
            $(this).removeClass("active");
            $(".mep-header-bottom-nav__link.active").removeClass("active");
            toggleBottomIitem.hide();
            headerTopShow(button);
        });
    } else {
        headerTopShow(button);
    }
};

export default headerTopToggle;

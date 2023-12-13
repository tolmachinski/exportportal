import $ from "jquery";

import hideHeaderOverlay from "@src/components/navigation/fragments/hide-header-overlay";

const simpleHideHeaderBottom = () => {
    hideHeaderOverlay();

    // @ts-ignore
    $("#js-mep-header-bottom-toggle")
        .stop(true)
        .animate({ height: 0 }, 500, function animate() {
            $(this).removeClass("active");
            $(".js-mep-user-actions").removeClass("active");
            $("#js-mep-header-dashboard").hide().html("");
        });
};

export default simpleHideHeaderBottom;

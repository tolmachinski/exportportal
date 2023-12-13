import $ from "jquery";

import onResizeCallback from "@src/util/dom/on-resize-callback";
import hideDashboardMenu from "@src/components/dashboard/fragments/hide-dashboard-menu";
import closeMainOverlay2 from "@src/components/navigation/fragments/close-main-overlay2";

const closeMenuOnResize = () => {
    onResizeCallback(() => {
        if ($(window).width() > 991) {
            closeMainOverlay2();
        } else {
            hideDashboardMenu();
        }
    });
};

export default closeMenuOnResize;

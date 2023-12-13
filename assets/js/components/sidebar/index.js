import $ from "jquery";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import offResizeCallback from "@src/util/dom/off-resize-callback";

const toggleSidebar = () => {
    const body = $("body");
    const sidebar = $("#js-ep-sidebar");

    if (sidebar.hasClass("active")) {
        sidebar.addClass("animate").removeClass("active");
        body.removeClass("sidebar-opened");

        setTimeout(() => {
            sidebar.removeClass("animate");
        }, 250);

        offResizeCallback();
    } else {
        sidebar.addClass("active");
        body.addClass("sidebar-opened");

        onResizeCallback(() => {
            if (globalThis.matchMedia("(min-width: 992px)").matches) {
                body.removeClass("sidebar-opened");
            } else if (sidebar.hasClass("js-sidebar-empty-search") && globalThis.matchMedia("(min-width: 768px) and (max-width: 991px)").matches) {
                body.removeClass("sidebar-opened");
            } else {
                body.addClass("sidebar-opened");
            }
        });
    }
};

export default toggleSidebar;

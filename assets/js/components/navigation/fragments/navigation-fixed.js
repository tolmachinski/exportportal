import $ from "jquery";

const headerFixed = () => {
    if (!globalThis.scrollupState) {
        return true;
    }

    const epHeader = $("#js-ep-header");

    if ($("html").scrollTop() > 46) {
        epHeader.removeClass("ep-header-bottom--no-fixed").addClass("ep-header--fixed");
    } else {
        epHeader.removeClass("ep-header--fixed").addClass("ep-header-bottom--no-fixed");
    }

    return true;
};

export default headerFixed;

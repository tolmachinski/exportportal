import $ from "jquery";

const toggleShadowHeaderTop = (action = "hide") => {
    const shadowBlock = $("#js-shadow-header-top");

    if (action === "hide") {
        shadowBlock.hide();
    } else {
        shadowBlock.show();
    }
};

export default toggleShadowHeaderTop;

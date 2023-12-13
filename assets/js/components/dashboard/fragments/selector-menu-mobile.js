import $ from "jquery";

const selectorMenuMobile = button => {
    const { type, hide } = button.data();

    if (button.hasClass("active")) {
        return;
    }

    button.addClass("active").siblings().removeClass("active");
    $(`#js-mep-user-nav-${hide}`).fadeOut(300, () => $(`#js-mep-user-nav-${type}`).fadeIn(300));
};

export default selectorMenuMobile;

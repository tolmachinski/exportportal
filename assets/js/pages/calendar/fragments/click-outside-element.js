import $ from "jquery";

const clickOutsideElement = ({ target }, popupClass, overlayClass, btnClass = "") => {
    const targetEl = $(target);
    const popup = $(popupClass);
    if (targetEl.closest(".js-calendar-popup").length) {
        return;
    }

    if (targetEl.closest(btnClass).length) {
        popup.show();
    } else {
        popup.hide();
        $(overlayClass).hide();
    }
};

export default clickOutsideElement;

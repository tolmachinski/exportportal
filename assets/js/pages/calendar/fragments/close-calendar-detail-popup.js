import $ from "jquery";

const closeCalendarDetailPopup = () => {
    if ($(".js-calendar-popup").length === 1) {
        $(".js-calendar-overlay").remove();
    }
    $(".calendar-info-popup-wrapper").remove();
};

export default closeCalendarDetailPopup;

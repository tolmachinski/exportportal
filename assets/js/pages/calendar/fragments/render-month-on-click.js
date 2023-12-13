import $ from "jquery";

import getCalendarEvents from "@src/pages/calendar/fragments/get-calendar-events";
import getNavbarRange from "@src/pages/calendar/fragments/get-navbar-range";

const directions = {
    prev: calendar => calendar.prev(),
    next: calendar => calendar.next(),
    today: calendar => calendar.today(),
};
const defaultBackgroundColor = "#ffffff";
const calendarButtons = $(".js-calendar-button");

const renderMonthOnClick = async (calendar, direction = "today") => {
    calendarButtons.prop("disabled", true);

    $(".js-calendar-popup").hide();
    calendar.clear();
    directions[direction](calendar);

    if (calendar.getDate().getMonth() !== new Date().getMonth()) {
        $(".toastui-calendar-daygrid-cell").css("backgroundColor", defaultBackgroundColor);
    }

    const events = await getCalendarEvents(calendar);
    calendar.createEvents(events);
    getNavbarRange(".js-calendar-month-title", calendar.getDateRangeStart(), calendar.getDateRangeEnd(), calendar.getViewName());
    setTimeout(() => {
        calendarButtons.prop("disabled", false);
    }, 100);
};

export default renderMonthOnClick;

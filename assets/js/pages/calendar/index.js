import $ from "jquery";
import EventHub, { removeListeners } from "@src/event-hub";
import Calendar from "@toast-ui/calendar";
import offResizeCallback from "@src/util/dom/off-resize-callback";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import getCalendarEvents from "@src/pages/calendar/fragments/get-calendar-events";
import getNavbarRange from "@src/pages/calendar/fragments/get-navbar-range";
import clickOutsideElement from "@src/pages/calendar/fragments/click-outside-element";
import resposeCalendar from "@src/pages/calendar/fragments/response-calendar";

import "@toast-ui/calendar/dist/toastui-calendar.min.css";
import "@scss/user_pages/calendar/index.scss";

$(async () => {
    const curentDateTS = new Date().getTime();
    // TODO change SVG to import
    const svg = `
        <g transform="translate(-4.849 -3.85)">
            <path d="M125.018,45.849h-.786v-.786a.262.262,0,0,0-.524,0v.786h-3.143v-.786a.262.262,0,0,0-.524,0v.786H116.9v-.786a.262.262,0,0,0-.524,0v.786h-.786a.786.786,0,0,0-.786.786v8.381a.786.786,0,0,0,.786.786h9.429a.786.786,0,0,0,.786-.786V46.634a.786.786,0,0,0-.786-.786Zm-9.429.524h.786v.262a.262.262,0,0,0,.524,0v-.262h3.143v.262h0a.262.262,0,0,0,.524,0v-.262h3.143v.262h0a.262.262,0,1,0,.524,0v-.262h.786a.262.262,0,0,1,.262.262v1.31h-9.952v-1.31a.262.262,0,0,1,.262-.262Zm9.429,8.9H115.59a.262.262,0,0,1-.262-.262V48.468h9.952v6.548a.262.262,0,0,1-.262.262Z" transform="translate(-109.805 -40.801)"/>
            <path d="M116.637,44.8a.262.262,0,0,0-.262.262v.786h-.786a.786.786,0,0,0-.786.786v8.381a.786.786,0,0,0,.786.786h9.429a.786.786,0,0,0,.786-.786V46.634a.786.786,0,0,0-.786-.786h-.786v-.786a.262.262,0,0,0-.524,0v.786h-3.143v-.786a.262.262,0,0,0-.524,0v.786H116.9v-.786a.262.262,0,0,0-.262-.262m-1.048,1.571h.786v.262a.262.262,0,0,0,.524,0v-.262h3.143v.262a.262.262,0,0,0,.524,0v-.262h3.143v.262a.262.262,0,0,0,.524,0v-.262h.786a.262.262,0,0,1,.262.262v1.31h-9.952v-1.31a.262.262,0,0,1,.262-.262h0m9.429,8.9H115.59a.262.262,0,0,1-.262-.262V48.468h9.952v6.548a.262.262,0,0,1-.262.262m-8.381-10.626a.412.412,0,0,1,.412.412V45.7h2.843v-.636a.412.412,0,1,1,.824,0V45.7h2.843v-.636a.412.412,0,1,1,.824,0V45.7h.636a.936.936,0,0,1,.936.936v8.381a.936.936,0,0,1-.936.936H115.59a.936.936,0,0,1-.936-.936V46.634a.936.936,0,0,1,.936-.936h.636v-.636A.412.412,0,0,1,116.637,44.651Zm-.412,1.871h-.636a.112.112,0,0,0-.112.112v1.16h9.652v-1.16a.112.112,0,0,0-.033-.079.111.111,0,0,0-.079-.033h-.636v.112a.412.412,0,1,1-.824,0v-.112h-2.843v.112a.412.412,0,0,1-.824,0v-.112h-2.843v.112a.412.412,0,0,1-.824,0Zm8.9,2.1h-9.652v6.4a.112.112,0,0,0,.112.112h9.429a.112.112,0,0,0,.112-.112Z" transform="translate(-109.805 -40.801)"/>
        </g>
    `;
    let currentDayBackgroundColor = "inherit";

    if (!BACKSTOP_TEST_MODE) {
        currentDayBackgroundColor = "#FEF0CC";
    }

    const calendar = new Calendar("#calendar", {
        defaultView: "month",
        isReadOnly: true,
        useDetailPopup: false,
        gridSelection: true,
        theme: {
            common: {
                today: {
                    color: "#000000",
                },
                holiday: {
                    color: "#000000",
                },
                dayName: {
                    color: "#000000",
                },
                saturday: {
                    color: "#000000",
                },
            },
            month: {
                dayName: {
                    fontSize: "16px",
                    lineHeight: "22px",
                    fontWeight: "500",
                },
                gridCell: {
                    headerHeight: 34,
                    footerHeight: 21,
                },
                holidayExceptThisMonth: {
                    color: "#E0E0E0",
                },
                dayExceptThisMonth: {
                    color: "#E0E0E0",
                    backgroundColor: "black",
                },
            },
            week: {
                today: {
                    backgroundColor: "#000000",
                },
                dayGridLeft: {
                    borderRight: "1px solid red",
                    backgroundColor: "red",
                },
                dayName: {
                    borderLeft: "none",
                    borderTop: "1px dotted red",
                    borderBottom: "1px dotted red",
                    backgroundColor: "rgba(81, 92, 230, 0.05)",
                },
            },
        },
        template: {
            allday(event) {
                const startDate = event.getStarts();
                const endDate = event.getEnds();
                let backgroundColor = "#2181F8";
                let color = "#FFFFFF";

                if (endDate < curentDateTS) {
                    backgroundColor = "#F5F5F5";
                    color = "#9E9E9E";
                }

                if (startDate > curentDateTS) {
                    backgroundColor = "#D3E6FE";
                    color = "#000000";
                }

                return `<div
                    class="calendar__event-title call-action ${BACKSTOP_TEST_MODE ? "js-calendar-event" : ""}"
                    data-js-action="calendar-event:click"
                    data-event-id="${event.id}"
                    data-calendar-id="${event.calendarId}"
                    style="background-color: ${backgroundColor}; color: ${color}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="11.3" height="11.3" viewBox="0 0 11.3 11.3" fill="${color}">
                        ${svg}
                    </svg>
                    <span>
                        ${event.title}
                    </span>
                </div>
                `;
            },
            monthDayName(model) {
                return `<span class="calendar__month-day">${model.label}</span>`;
            },
            monthGridHeader(model) {
                const date = parseInt(model.date.split("-")[2], 10);
                setTimeout(() => {
                    if (calendar.getDate().getMonth() === new Date().getMonth()) {
                        $(".js-current-day-grid").closest(".toastui-calendar-daygrid-cell").css("backgroundColor", currentDayBackgroundColor);
                    }
                }, 0);
                return `<span class="calendar-date ${model.isToday ? "js-current-day-grid" : ""}">${date}</span>`;
            },
            monthGridHeaderExceed() {
                return "";
            },
            monthGridFooterExceed(hiddenEvents) {
                return `<span style="color: #2181F8; font-size:12px; line-height: 16px; font-weight: 400;">+${hiddenEvents}</span>`;
            },
            monthMoreClose() {
                // TODO change SVG to import
                return `
                    <span class="calendar-info-popup__close calendar-info-popup__close--static call-action" data-js-action="calendar-info:close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
                            <path d="M.81,15a.772.772,0,0,1-.545-1.318L13.722.225a.772.772,0,0,1,1.091,1.092L1.357,14.773A.774.774,0,0,1,.81,15Zm0,0" transform="translate(-0.039 0.001)" />
                            <path d="M14.268,15a.766.766,0,0,1-.545-.226L.265,1.317A.772.772,0,1,1,1.356.226L14.813,13.682A.772.772,0,0,1,14.268,15Zm0,0" transform="translate(-0.039 0)" />
                        </svg>
                    </span>
                `;
            },
        },
    });

    const calendarEvents = await getCalendarEvents(calendar);

    calendar.createEvents(calendarEvents);
    getNavbarRange(".js-calendar-month-title", calendar.getDateRangeStart(), calendar.getDateRangeEnd(), calendar.getViewName());

    $(document).on("click", e => clickOutsideElement(e, ".js-calendar-popup", ".js-calendar-overlay", ".toastui-calendar-grid-cell-more-events"));

    $(".js-calendar-info-notifications").on("click", e =>
        clickOutsideElement(e, ".js-calendar-popup", ".js-calendar-overlay", ".toastui-calendar-grid-cell-more-events")
    );

    if (BACKSTOP_TEST_MODE) {
        setTimeout(() => {
            $(".js-calendar-event").attr("atas", "calendar__event_detail");
        }, 500);
    }

    calendar.on("clickMoreEventsBtn", async ({ date, target }) => {
        const { setOverlayOnMobile } = await import("@src/pages/calendar/fragments/calendar-detail-popup");
        target.classList.add("js-calendar-popup");
        $(".js-calendar-popup").css("display", "block");
        setOverlayOnMobile();
        target.querySelectorAll(`[data-js-action="calendar-event:click"]`).forEach(event => {
            const { eventId, calendarId } = event.dataset;
            const day = new Date(date);
            const { end } = calendar.getEvent(Number(eventId), calendarId);
            event.classList.add("triangle-right");
            if (end.getDate() === day.getDate()) {
                if (end.getMonth() === day.getMonth()) {
                    event.classList.remove("triangle-right");
                }
            }
        });
    });

    removeListeners(
        "calendar-prev-month:click",
        "calendar-next-month:click",
        "calendar-today-month:click",
        "calendar-info:close",
        "calendar-info:delete",
        "calendar-event:click"
    );
    resposeCalendar(calendar);
    offResizeCallback();
    onResizeCallback(() => resposeCalendar(calendar));

    EventHub.on("calendar-prev-month:click", async () => {
        const { default: renderMonthOnClick } = await import("@src/pages/calendar/fragments/render-month-on-click");
        renderMonthOnClick(calendar, "prev");
    });
    EventHub.on("calendar-next-month:click", async () => {
        const { default: renderMonthOnClick } = await import("@src/pages/calendar/fragments/render-month-on-click");
        renderMonthOnClick(calendar, "next");
    });
    EventHub.on("calendar-today-month:click", async () => {
        const { default: renderMonthOnClick } = await import("@src/pages/calendar/fragments/render-month-on-click");
        if (calendar.getDate().getMonth() !== new Date().getMonth()) {
            renderMonthOnClick(calendar, "today");
        }
    });
    EventHub.on("calendar-info:close", async () => {
        const { default: closeCalendarDetailPopup } = await import("@src/pages/calendar/fragments/close-calendar-detail-popup");
        closeCalendarDetailPopup();
    });
    EventHub.on("calendar-info:delete", async (e, btn) => {
        const { default: deleteCalendarEvent } = await import("@src/pages/calendar/fragments/delete-calendar-event");
        deleteCalendarEvent(btn, calendar);
    });
    EventHub.on("calendar-event:click", async (e, btn, _b, event) => {
        $(".js-calendar-info-popup-wrapper").remove();
        const { renderEventPopup } = await import("@src/pages/calendar/fragments/calendar-detail-popup");
        const { eventId } = btn.data();
        setTimeout(() => {
            renderEventPopup(eventId, event);
        }, 0);
    });
});

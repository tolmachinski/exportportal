import postRequest from "@src/util/http/post-request";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";

const getCalendarEvents = async calendar => {
    const result = [];
    try {
        const { calendar: calendarEvents } = await postRequest(`${SITE_URL}calendar/ajax_get_calendar_events`, {
            startDate: new Date(calendar.getDateRangeStart()).toLocaleDateString("en-US", { year: "numeric", month: "numeric", day: "numeric" }),
        });
        if (calendarEvents && Array.from(calendarEvents)) {
            calendarEvents.forEach(item => {
                result.push({
                    id: item.id,
                    calendarId: item.event_type,
                    title: item.title,
                    start: item.start_date.date,
                    end: item.end_date.date,
                    category: "allday",
                    backgroundColor: "#D3E6FE",
                    raw: {
                        type: item.event_type,
                        sourceId: item.source_id,
                    },
                });
            });
        }
    } catch (error) {
        handleRequestError(error);
    }

    return result.sort((a, b) => {
        const e1Duration = new Date(a.end).getTime() - new Date(a.start).getTime();
        const e2Duration = new Date(b.end).getTime() - new Date(b.start).getTime();

        if (e1Duration < e2Duration) {
            return 1;
        }
        if (e1Duration > e2Duration) {
            return -1;
        }

        return 0;
    });
};

export default getCalendarEvents;

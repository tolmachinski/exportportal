import $ from "jquery";
import postRequest from "@src/util/http/post-request";
import { SUBDOMAIN_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";

const notificationEvent = async (e, btn) => {
    const itemId = btn.data("item");

    try {
        const { mess_type: type } = await postRequest(`${SUBDOMAIN_URL}calendar/remove`, { type: "ep_events", source: itemId })

        if (type === "success") {
            const calendarBtn = $(`[data-id="calendar-btn-${itemId}"]`);
            calendarBtn.find(".js-not-added-calendar").removeClass("display-n");
            calendarBtn.find(".js-added-calendar").addClass("display-n");
            calendarBtn.addClass("fancyboxMep fancybox.ajax").removeClass("js-confirm-dialog calendar-btn-success ep-events__calendar-hover");
        }
    } catch (error) {
        handleRequestError(error);
    }
};

export default notificationEvent;

import $ from "jquery";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import EventHub, { removeListeners } from "@src/event-hub";
import closeCalendarDetailPopup from "@src/pages/calendar/fragments/close-calendar-detail-popup";
import getElement from "@src/util/dom/get-element";
import checkNotificationsLength from "@src/components/popups/calendar/fragments/check-notification-length.fragment";

export default (wrapperSelector, maxDays) => {
    const wrapper = $(wrapperSelector);
    const formBody = getElement(wrapper.data("body"));
    const addNotificationBtn = getElement(wrapper.data("notificationBtn"));
    closeCalendarDetailPopup();
    checkNotificationsLength(formBody, addNotificationBtn);

    removeListeners(
        "calendar-notifications:form-submit",
        "calendar-notifications:form-close",
        "calendar-notifications:add-notification",
        "calendar-notifications:remove-notification"
    );

    EventHub.on("calendar-notifications:form-close", () => closeFancyBox());
    EventHub.on("calendar-notifications:add-notification", async () => {
        const { default: addNotificationInputs } = await import("@src/components/popups/calendar/fragments/add-notification-inputs.fragment");
        addNotificationInputs(formBody, addNotificationBtn, maxDays);
    });
    EventHub.on("calendar-notifications:remove-notification", async (e, btn) => {
        const { default: removeNotificationInputs } = await import("@src/components/popups/calendar/fragments/remove-notification-inputs.fragment");
        removeNotificationInputs(btn, formBody, addNotificationBtn);
    });
    EventHub.on("calendar-notifications:form-submit", async (e, form) => {
        const { default: formSubmit } = await import("@src/components/popups/calendar/fragments/notifications-form-submit.fragment");
        formSubmit(form);
    });
};

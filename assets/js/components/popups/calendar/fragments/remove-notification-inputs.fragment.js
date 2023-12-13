import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import checkNotificationsLength from "@src/components/popups/calendar/fragments/check-notification-length.fragment";

const removeNotificationInputs = (btn, wrapper, addNotificationBtn) => {
    updateFancyboxPopup();
    btn.parent().parent().remove();
    checkNotificationsLength(wrapper, addNotificationBtn);
};

export default removeNotificationInputs;

import { NUMBER_OF_NOTIFICATIONS } from "@src/common/constants";

const checkNotificationsLength = (formBody, addNotificationBtn) => {
    if (formBody.children().length >= NUMBER_OF_NOTIFICATIONS) {
        addNotificationBtn.hide();
    } else {
        addNotificationBtn.show();
    }
};

export default checkNotificationsLength;

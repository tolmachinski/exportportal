import $ from "jquery";

import checkNotifyAll from "@src/components/popups/notifications/fragments/check-notify-all";

const selectAllNotificationCheckboxes = function () {
    const inputStatus = $(".js-check-all2 input[type=checkbox]").prop("checked");

    $(".js-epuser-subline-list2 input[type=checkbox]").prop("checked", !!inputStatus);

    checkNotifyAll();
};

const ifCheckedAllNotificationCheckboxes = function () {
    const notify = $("#js-epuser-notifications2");
    const notifyAll = $(".js-check-all2 input[type=checkbox]");
    const totalNotify = notify.find(".js-epuser-subline-list2__item").length;
    let totalNotifyChecked = 0;
    // eslint-disable-next-line func-names
    notify.find(".js-epuser-subline-list2__item").each(function () {
        if ($(this).find("input[type=checkbox]").prop("checked")) {
            totalNotifyChecked += 1;
        }
    });

    notifyAll.prop("checked", totalNotify === totalNotifyChecked);

    checkNotifyAll();
};

export { selectAllNotificationCheckboxes };
export { ifCheckedAllNotificationCheckboxes };

import $ from "jquery";

const checkNotifyAll = () => {
    const notify = $("#js-epuser-notifications2");

    if (!notify.length) {
        return false;
    }

    let totalNotifyChecked = 0;
    const $btnRemoveNotification = $("#js-epuser-notifications2 .remove-notification");
    const $btnReadNotification = $("#js-epuser-notifications2 .read-notification");

    notify.find(".js-epuser-subline-list2__item").each(function () {
        if ($(this).find("input[type=checkbox]").prop("checked")) {
            totalNotifyChecked += 1;
        }
    });

    if (totalNotifyChecked > 0) {
        if ($btnRemoveNotification.length) {
            $btnRemoveNotification
                .data("callback", "remove_notification2")
                .removeClass("call-function")
                .removeClass("call-action")
                .addClass("js-confirm-dialog confirm-dialog");
        }

        if ($btnReadNotification.length) {
            $btnReadNotification
                .data("callback", "read_notification2")
                .removeClass("call-function")
                .removeClass("call-action")
                .addClass("js-confirm-dialog confirm-dialog");
        }

        if ($btnRemoveNotification.length) {
            $btnRemoveNotification
                .data("js-action", "notification:remove")
                .removeClass("call-function")
                .removeClass("call-action")
                .addClass("js-confirm-dialog confirm-dialog");
        }

        if ($btnReadNotification.length) {
            $btnReadNotification
                .data("js-action", "notification:read")
                .removeClass("call-function")
                .removeClass("call-action")
                .addClass("js-confirm-dialog confirm-dialog");
        }
    } else {
        if ($btnRemoveNotification.length) {
            $btnRemoveNotification
                .data("callback", "no_remove_notification2")
                .removeClass("js-confirm-dialog confirm-dialog")
                .addClass("call-function")
                .addClass("call-action");
        }

        if ($btnReadNotification.length) {
            $btnReadNotification
                .data("callback", "no_read_notification2")
                .removeClass("js-confirm-dialog confirm-dialog")
                .addClass("call-function")
                .addClass("call-action");
        }

        if ($btnRemoveNotification.length) {
            $btnRemoveNotification
                .data("callback", "notification:no-remove")
                .removeClass("js-confirm-dialog confirm-dialog")
                .addClass("call-function")
                .addClass("call-action");
        }

        if ($btnReadNotification.length) {
            $btnReadNotification
                .data("callback", "notification:no-read")
                .removeClass("js-confirm-dialog confirm-dialog")
                .addClass("call-function")
                .addClass("call-action");
        }
    }

    return true;
};

export default checkNotifyAll;

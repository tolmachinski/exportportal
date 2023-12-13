import $ from "jquery";

import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages";
import { ifCheckedAllNotificationCheckboxes, selectAllNotificationCheckboxes } from "@src/components/popups/notifications/fragments/checkboxes";
import { SHIPPER_PAGE, SUBDOMAIN_URL } from "@src/common/constants";
import { updateFancyboxPopup3 } from "@src/plugins/fancybox/v3/util";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import handleRequestError from "@src/util/http/handle-request-error";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const updateNotificationsCounter = ({ count_new: countNew, count_warning: countImportant }) => {
    const allNotificationsNode = $("#js-popover-nav-count-new");
    const importantNotificationsNode = $("#js-popover-nav-count-important");
    allNotificationsNode.text(countNew);
    importantNotificationsNode.text(countImportant);
};

const loadNotificationList2 = async function (status, page, type) {
    const pageParam = page === undefined ? 1 : page;
    const statusParam = status;
    const typeParam = type || false;
    const dataSend = { status: statusParam, page: pageParam };

    if (typeParam !== false) {
        dataSend.type = typeParam;
    }

    try {
        showLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"), "Loading...");
        const { mess_type: messType, block, count_notifications: notifications } = await postRequest(
            `${SUBDOMAIN_URL}systmess/ajax_systmess_operation/show_notification_block2`,
            dataSend,
            "JSON"
        );

        if (messType === "success") {
            $("#js-epuser-notifications2").html(block);

            const readNotification = $("#js-epuser-notifications2").find(".read-notification");
            if (statusParam === "deleted") {
                readNotification.hide();
            } else {
                readNotification.show();
            }

            if (SHIPPER_PAGE) {
                updateFancyboxPopup3();
            } else {
                updateFancyboxPopup();
            }

            updateNotificationsCounter(notifications);
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
    }
};

const btnNotificationList = btn => {
    const status = btn.data("status");

    loadNotificationList2(status);
};

const filterNotificationList = btn => {
    if (btn.hasClass("active")) {
        return false;
    }

    const status = btn.data("status");
    const type = btn.data("type") || false;

    btn.addClass("active").siblings().removeClass("active");

    loadNotificationList2(status, 1, type);
    return true;
};

const showNotificationDetail = async btn => {
    const closestListTitle = $(btn).closest(".epuser-subline-list2__ttl");

    const notify = closestListTitle.data("notify");
    const type = closestListTitle.find('input[type="checkbox"]').data("type");
    const liParent = closestListTitle.closest(".js-epuser-subline-list2__item");

    const active = $("#js-epuser-notifications2 .epuser-subline-nav2 .link.active").data("status");

    if (!liParent.hasClass("js-epuser-subline-list2__item--seen") && active !== "deleted") {
        try {
            const { mess_type: messType, count_notifications: notifications } = await postRequest(
                `${SUBDOMAIN_URL}systmess/ajax_systmess_operation/notification_seen`,
                { message: notify },
                "JSON"
            );

            if (messType === "success") {
                liParent.addClass("js-epuser-subline-list2__item--seen");
                const newAll = $("#js-epuser-notifications2 .epuser-subline-nav2 .link.active .count");
                const newCount = parseInt(newAll.text(), 10) - 1;
                newAll.text(newCount);

                const counterNew = $(`#js-epuser-notifications2 .epuser-subline-filter .link[data-type="${type}"] .count`);
                counterNew.text(parseInt(counterNew.text(), 10) - 1);

                if (newCount === 0) {
                    $("#js-epuser-notifications2 .epuser-subline-nav2 .link.active").addClass("disabled");
                }

                updateNotificationsCounter(notifications);
            }
        } catch (e) {
            handleRequestError(e);
        }
    }

    liParent.find(".epuser-subline-list2__desc").slideToggle("slow");
};

const noReadNotification2 = () => {
    systemMessages("You do not have any unread notifications.", "warning");
};

const readNotification2 = async () => {
    const notificationList = [];
    const activeFilter = $("#js-epuser-notifications2 .epuser-subline-filter .link.active");
    const status = activeFilter.data("status");
    let total = 0;
    let infoTotal = 0;
    let importantTotal = 0;

    if (status === "deleted") {
        systemMessages("You did not mark as read this notification(s).", "warning");

        return;
    }

    // eslint-disable-next-line func-names
    $(".js-epuser-subline-list2 input[type=checkbox]").each(function () {
        const checkbox = $(this);
        if (checkbox.prop("checked")) {
            const parentLi = checkbox.closest(".js-epuser-subline-list2__item");
            if (!parentLi.hasClass("js-epuser-subline-list2__item--seen")) {
                total += 1;
                if (checkbox.data("type") === "notice") {
                    infoTotal += 1;
                } else if (checkbox.data("type") === "warning") {
                    importantTotal += 1;
                }
                parentLi.addClass("js-epuser-subline-list2__item--seen");
                // @ts-ignore
                checkbox.prop("checked", false);
                notificationList.push(checkbox.val());
            }
        }
    });

    const nrNotificationList = notificationList.length;

    if (nrNotificationList !== 0) {
        try {
            showLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
            const { mess_type: messType, message, count_notifications: notifications } = await postRequest(
                `${SUBDOMAIN_URL}systmess/ajax_systmess_operation/notification_readed`,
                { messages: notificationList },
                "JSON"
            );
            systemMessages(message, messType);
            if (messType === "success") {
                const newAll = $("#js-epuser-notifications2 .epuser-subline-nav2 .link.active .count");
                const newCount = parseInt(newAll.text(), 10) - total;
                newAll.text(newCount);

                const subline = $("#js-epuser-notifications2 .epuser-subline-filter");
                const newByNotice = subline.find('.link[data-type="notice"] .count');
                newByNotice.text(parseInt(newByNotice.text(), 10) - infoTotal);
                const newByImportant = subline.find('.link[data-type="warning"] .count');
                newByImportant.text(parseInt(newByImportant.text(), 10) - importantTotal);

                if (newCount === 0) {
                    $("#js-epuser-notifications2 .epuser-subline-nav2 .link.active").addClass("disabled");
                }

                // @ts-ignore
                $("#js-epuser-notifications2 .js-check-all2 input[type=checkbox]").prop("checked", false);

                updateNotificationsCounter(notifications);
            }
        } catch (e) {
            handleRequestError(e);
        } finally {
            hideLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
        }
    } else {
        systemMessages("You have not selected any unread notification(s).", "warning");
    }
};

const noRemoveNotification2 = () => {
    systemMessages("You did not check any notification(s).", "warning");
};

const emptyTrashNotification2 = async () => {
    try {
        showLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
        const { mess_type: messType, message } = await postRequest(`${SUBDOMAIN_URL}systmess/ajax_systmess_operation/delete_all_from_trash`, {}, "JSON");
        systemMessages(message, messType);
        if (messType === "success") {
            loadNotificationList2("all", 1, "all");
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
    }
};

const removeNotification2 = async () => {
    const notificationList = [];
    const activeFilter = $("#js-epuser-notifications2 .epuser-subline-filter .link.active");
    const pageActive = parseInt($("#js-epuser-notifications2 .epuser-pagination .active").text(), 10);
    const status = activeFilter.data("status");
    const type = activeFilter.data("type");

    $("#js-epuser-notifications2 .js-epuser-subline-list2 input[type=checkbox]").each(function () {
        const checkbox = $(this);
        if (checkbox.prop("checked")) {
            notificationList.push(checkbox.val());
        }
    });

    const nrNotificationList = notificationList.length;

    if (nrNotificationList !== 0) {
        try {
            showLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
            const { mess_type: messType, message } = await postRequest(
                `${SUBDOMAIN_URL}systmess/ajax_systmess_operation/notification_deleted`,
                { messages: notificationList },
                "JSON"
            );
            systemMessages(message, messType);

            if (messType === "success") {
                loadNotificationList2(status, pageActive, type);
            }
        } catch (e) {
            handleRequestError(e);
        } finally {
            hideLoader($("#js-epuser-notifications2 .js-epuser-subline-list2"));
        }
    } else {
        systemMessages("You did not check any notification(s).", "warning");
    }
};

export default async () => {
    if (SHIPPER_PAGE) {
        // @ts-ignore
        await import(/*  webpackChunkName: "popup-notifications" */ "@scss/epl/components/notifications/index.scss");
    }

    $(".js-icon-circle-notification").find(".epuser-line__circle-sign").removeClass("pulse-shadow-animation");

    $("#js-epuser-notifications2").on("click", ".epuser-pagination a", function pagination(e) {
        e.preventDefault();
        const { status, page, type } = $(this).data();
        loadNotificationList2(status, page, type);
    });

    $("body").on("change", ".js-check-all2 input[type=checkbox]", () => {
        selectAllNotificationCheckboxes();
    });

    $("body").on("change", ".js-epuser-subline-list2__item input[type=checkbox]", () => {
        ifCheckedAllNotificationCheckboxes();
    });

    onResizeCallback(() => {
        $(".js-popover-mep").popover("hide");
    });

    EventHub.off("notification:list-all");
    EventHub.off("notification:no-remove");
    EventHub.off("notification:remove");
    EventHub.off("notification:read");
    EventHub.off("notification:no-read");
    EventHub.off("notification:filter-list");
    EventHub.off("notification:clear-all");
    EventHub.off("notification:show-detail");
    EventHub.on("notification:list-all", (e, button) => btnNotificationList(button));
    EventHub.on("notification:no-remove", () => noRemoveNotification2());
    EventHub.on("notification:remove", () => removeNotification2());
    EventHub.on("notification:read", () => readNotification2());
    EventHub.on("notification:no-read", () => noReadNotification2());
    EventHub.on("notification:filter-list", (e, button) => filterNotificationList(button));
    EventHub.on("notification:clear-all", () => emptyTrashNotification2());
    EventHub.on("notification:show-detail", (e, button) => showNotificationDetail(button));
};

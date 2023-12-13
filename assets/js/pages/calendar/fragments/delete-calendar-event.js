import { SITE_URL } from "@src/common/constants";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import closeCalendarDetailPopup from "@src/pages/calendar/fragments/close-calendar-detail-popup";
import { translate } from "@src/i18n";

const confirmDeleteEvent = async (id, type, sourceId, calendar) => {
    try {
        const { mess_type: messType } = await postRequest(`${SITE_URL}calendar/remove`, { type, source: sourceId });
        if (messType !== "success") {
            return;
        }
        calendar.deleteEvent(id, type);
    } catch (error) {
        handleRequestError(error);
    }
};

const onClickDeleteEvent = async (btn, calendar) => {
    const { id, type, sourceId } = btn.data();
    await loadBootstrapDialog();
    openResultModal({
        title: translate({ plug: "BootstrapDialog", text: "deleteFromCalendar" }),
        content: translate({ plug: "BootstrapDialog", text: "areYouSureToDeleteFromCalendar" }),
        closable: true,
        type: "info",
        delimeterClass: "",
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "yes" }),
                cssClass: "btn btn-primary",
                action(dialog) {
                    confirmDeleteEvent(id, type, sourceId, calendar);
                    closeCalendarDetailPopup();
                    dialog.close();
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "no" }),
                cssClass: "btn btn-light",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

export default onClickDeleteEvent;

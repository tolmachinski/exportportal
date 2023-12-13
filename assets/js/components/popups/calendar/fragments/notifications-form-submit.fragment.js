import { systemMessages } from "@src/util/system-messages";
import { translate } from "@src/i18n";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import $ from "jquery";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";

const checkingForUniqueness = (select, input) => {
    const inputs = document.querySelectorAll(input);
    const selects = document.querySelectorAll(select);
    const obj = Array.from(selects).reduce((acc, cur, index) => {
        const key = acc[cur.value];
        const { value } = inputs[index];
        acc[cur.value] = key ? [...key, value] : [value];
        return acc;
    }, {});

    const { email, system } = obj;
    if ((email?.length ?? 0) !== new Set(email)?.size) {
        return false;
    }
    return (system?.length ?? 0) === new Set(system)?.size;
};

const formSubmit = async function (form) {
    const { url, select, input } = form.data();
    const data = form.serialize();
    const btnSubmit = form.find("button[type=submit]");
    const unique = checkingForUniqueness(select, input);

    if (!unique) {
        await systemMessages(
            translate({
                plug: "general_i18n",
                text: "systmess_validation_edit_calendar_notifications_have_dauplicates",
            })
        );
        return;
    }

    btnSubmit.addClass("disabled");
    showLoader(form);

    try {
        const { message, mess_type: messType } = await postRequest(url, data);
        if (messType !== "success") {
            systemMessages(message, messType);
            return;
        }

        if (messType === "success" && form.data("event-id") !== "") {
            const calendarBtn = $(`[data-id="calendar-btn-${form.data("event-id")}"]`);
            calendarBtn.find(".js-not-added-calendar").addClass("display-n");
            calendarBtn.find(".js-added-calendar").removeClass("display-n");
            calendarBtn.removeClass("fancyboxMep fancybox.ajax").addClass("js-confirm-dialog calendar-btn-success ep-events__calendar-hover");
        }

        await loadBootstrapDialog();
        closeFancyBox();

        if (!url.includes("edit")) {
            openResultModal({
                subTitle: message,
                type: "success",
                closable: true,
                closeByBg: true,
                buttons: [
                    {
                        label: translate({ plug: "general_i18n", text: "ep_events_sidebar_go_to_calendar" }),
                        cssClass: "btn btn-primary",
                        action() {
                            globalThis.location.href = `${SITE_URL}calendar/my`;
                        },
                    },
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader($(form));
        setTimeout(() => {
            form.find("button[type=submit]").removeClass("disabled");
        }, 350);
    }
};

export default formSubmit;

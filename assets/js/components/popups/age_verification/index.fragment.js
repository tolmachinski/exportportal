import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { showLoader, hideLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import daysInMonth from "@src/util/common/days-in-month";
import EventHub from "@src/event-hub";

// function to update the days based on the current values of month and year
const updateNumberOfDays = () => {
    const month = $("#js-months-value").val();
    const year = $("#js-years-value").val();
    const days = daysInMonth(month, year);
    const lastDay = $("#js-days-value").val() || 0;

    if ($("#js-days-value").val() && $("#js-days-value").val() < 28) {
        return;
    }

    $("#js-days-value").html("<option>Day</option>");

    for (let i = 1; i <= days; i += 1) {
        if (lastDay === i) {
            $("#js-days-value").append($("<option />").val(i).html(`${i}`).attr("selected", "selected"));
        } else {
            $("#js-days-value").append($("<option />").val(i).html(`${i}`));
        }
    }
};

const ageVerification = async (e, form) => {
    try {
        showLoader(form);
        $(".js-submit-form").addClass("disabled");
        const { mess_type: messType } = await postRequest("categories/ajax_category_group_operation/check_age", form.serialize(), "JSON");
        if (messType === "success") {
            if ($(".js-submit-form").data("redirect")) {
                globalThis.location.href = $(".js-submit-form").data("redirect");
            } else {
                globalThis.location.reload();
            }
        }
    } catch (error) {
        const { mess_type: messType, message, date } = error.xhr.responseJSON;
        if (!date) {
            systemMessages(message, messType);
        } else {
            $(".js-show-warning").css("display", "block");
        }
    } finally {
        hideLoader(form);
        $(".js-submit-form").removeClass("disabled");
    }
};

export default () => {
    // "listen" for change events
    $("#js-years-value, #js-months-value").on("change", () => updateNumberOfDays());

    EventHub.on("popup:age-verification-submit", ageVerification);
};

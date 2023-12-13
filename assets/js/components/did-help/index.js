import { SUBDOMAIN_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

/**
 * It sends an AJAX request to the server, and change helpful data
 * @param {JQuery} btn
 */
const didHelp = async btn => {
    btn.addClass("disabled");

    try {
        const {
            mess_type: messageType,
            message,
            counter_plus: counterPlus,
            counter_minus: counterMinus,
            select_plus: selectPlus,
            remove_plus: removePlus,
            select_minus: selectMinus,
            remove_minus: removeMinus,
        } = await postRequest(`${SUBDOMAIN_URL + btn.data("page")}/ajax_${btn.data("type")}_operation/help`, {
            id: btn.data("item"),
            type: btn.data("action"),
        });

        if (messageType !== "success") {
            systemMessages(message, messageType);

            return;
        }

        const didHelpWrapper = btn.parent();

        if (typeof counterPlus !== "undefined") {
            didHelpWrapper.find(".js-counter-plus").text(counterPlus);
        }

        if (typeof counterMinus !== "undefined") {
            didHelpWrapper.find(".js-counter-minus").text(counterMinus);
        }

        const arrowUpElement = didHelpWrapper.find(".js-arrow-up");
        const arrowDownElement = didHelpWrapper.find(".js-arrow-down");
        const votedClass = "txt-blue2";

        if (selectPlus && !arrowUpElement.hasClass(votedClass)) {
            arrowUpElement.addClass(votedClass);
        }

        if (removePlus) {
            arrowUpElement.removeClass(votedClass);
        }

        if (selectMinus && !arrowDownElement.hasClass(votedClass)) {
            arrowDownElement.addClass(votedClass);
        }

        if (removeMinus) {
            arrowDownElement.removeClass(votedClass);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        btn.removeClass("disabled");
    }
};

export default didHelp;

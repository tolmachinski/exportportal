import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
/**
 * Handles the request errors. Default handler.
 *
 * @param {any} error
 */
const handleRequestError = function (error) {
    const genericError = error.isGeneric ? error : null;
    let requestError = typeof error.statusCode !== "undefined" ? error : null;
    if (error.isCustom) {
        requestError = error.xhr || null;
    }

    if (requestError !== null) {
        if (requestError.responseJSON && requestError.responseJSON.message) {
            systemMessages(requestError.responseJSON.message, requestError.responseJSON.mess_type || "error");
        } else {
            systemMessages(translate({ plug: "general_i18n", text: "system_message_server_error_text" }), "error");
        }
    } else if (genericError !== null) {
        systemMessages(
            genericError.message || translate({ plug: "general_i18n", text: "system_message_client_error_text" }),
            genericError.messageType || genericError.mess_type || "error"
        );
    } else {
        systemMessages(translate({ plug: "general_i18n", text: "system_message_client_error_text" }), "error");
    }

    // eslint-disable-next-line no-underscore-dangle
    if (globalThis.__debug_mode) {
        // eslint-disable-next-line no-console
        console.error(error);
    }
};

export default handleRequestError;

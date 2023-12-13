import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import postRequest from "@src/util/http/post-request";
import mix from "@src/util/common/mix";

const notifyOutOfStock = () => {
    if (!globalThis.notifyOutOfStock) {
        mix(globalThis, {
            notifyOutOfStock(element) {
                const url = $(element).data("href");
                const item = $(element).data("resource");

                return postRequest(url, { item }).then(response => {
                    systemMessages(response.message, response.mess_type);
                });
            },
        });
    }
};

export default notifyOutOfStock;

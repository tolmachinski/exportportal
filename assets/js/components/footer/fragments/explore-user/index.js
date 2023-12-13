import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const exitExploreUser = function () {
    return postRequest(`${SUBDOMAIN_URL}login/exit_explore_user`)
        .then(response => {
            if (response.mess_type === "success") {
                if (globalThis.matrixLogoutEmitter) {
                    globalThis.dispatchEvent(
                        new CustomEvent("matrixLogout", {
                            detail: {
                                callback() {
                                    globalThis.location.href = response.redirect;
                                },
                            },
                        })
                    );
                } else {
                    globalThis.location.href = response.redirect;
                }
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError);
};

// eslint-disable-next-line import/prefer-default-export
export { exitExploreUser };

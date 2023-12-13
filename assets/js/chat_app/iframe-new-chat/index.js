import { listenerOpenConfirm } from "@src/chat_app/listeners/index";

import Platform from "@src/chat_app/platform";
import { GROUP_SITE_URL, MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";

// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
import handleRequestError from "@src/util/http/handle-request-error";
import generateIframe from "@src/chat_app/generate-iframe/index";
import postRequest from "@src/util/http/post-request";
import lockBody from "@src/util/dom/lock-body";

let isLoad = false;
const closeModal = frame => {
    const [listener, emitter] = Platform.iframeNewChat;
    frame.classList.add("display-n");
    listener.off("closePopup");
    listener.off("openModalNewRoom:load");
    listener.off("generateNewRoom");
    listener.off("roomSearchUsers");
    listener.off("openConfirm");
    emitter.emit("openModalNewRoom:clear");
    lockBody(true);
};

const events = (frame, [listener, emitter], mainFrameEmitter, mainUser) => {
    lockBody();

    listener.off("closePopup");
    listener.on("closePopup", () => {
        closeModal(frame);
    });

    if (isLoad) {
        emitter.emit("openModalNewRoom:data", { mainUser });
    } else {
        setTimeout(() => {
            emitter.emit("openModalNewRoom:events");
        }, 100);

        listener.off("openModalNewRoom:load");
        listener.on("openModalNewRoom:load", () => {
            emitter.emit("openModalNewRoom:data", { mainUser });
            isLoad = true;
        });
    }

    listener.off("generateNewRoom");
    listener.on("generateNewRoom", ({ context: { data } }) => {
        const { subject, message, users } = data;

        // eslint-disable-next-line no-underscore-dangle
        const url = `${globalThis.__current_sub_domain_url}chats/ajax_chats_operations/validate_room`;
        postRequest(url, { users }, "json")
            .then(() => {
                mainFrameEmitter.emit("newRoom", {
                    name: subject,
                    message,
                    users,
                    // META DATA
                    // creation_content: {
                    // },
                });

                setTimeout(() => closeModal(frame), 2000);
            })
            .catch(error => {
                handleRequestError(error);
                emitter.emit("disableSetLoader");
            });
    });

    listener.off("roomSearchUsers");
    listener.on("roomSearchUsers", ({ context: { data } }) => {
        postRequest(`${GROUP_SITE_URL}matrix_chat/ajax_operation/find-users`, data, "json")
            .then(response => emitter.emit("roomSearchUsersResult", { response, typeLoad: data.typeLoad }))
            .catch(error => {
                emitter.emit("roomSearchUsersError");
                handleRequestError(error);
            });
    });

    listenerOpenConfirm(listener, emitter);
};

export default (mainFrameEmitter, mainUser) => {
    const frameId = "chat-app-new-chat";
    generateIframe(`${MATRIX_WEB_CLIENT_HOST}/new-room`, frameId, ["chat-app-modal"]).then(frame => {
        if (!Platform.iframeNewChat) {
            Platform.iframeNewChat = [new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]), new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST)];
        }
        events(frame, Platform.iframeNewChat, mainFrameEmitter, mainUser);
        frame.classList.remove("display-n");
    });
};

import $ from "jquery";
import Platform from "@src/chat_app/platform";
import { MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";

import handleRequestError from "@src/util/http/handle-request-error";
import generateIframe from "@src/chat_app/generate-iframe/index";
import openChatPopup from "@src/chat_app/fragments/openChatPopup";
import postRequest from "@src/util/http/post-request";
import lockBody from "@src/util/dom/lock-body";
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { listenerOpenConfirm } from "@src/chat_app/listeners/index";
import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";

let isLoad = false;

const closePopups = () => {
    // This functional is created to close all popups after close popup if this popup was open over another popup
    import("@src/plugins/bootstrap-dialog/index").then(({ closeAllDialogs }) => {
        closeAllDialogs();
    });
    import(/* webpackChunkName: "fancybox-util-chunk" */ "@src/plugins/fancybox/v2/util").then(({ closeFancyboxPopup }) => {
        closeFancyboxPopup();
    });

    if (!globalThis.ENCORE_MODE) {
        import("@src/util/events").then(({ dispatchEvent }) => {
            dispatchEvent("chat-client:close-all-popups", globalThis);
        });
    }
};

const closeModal = (frame, needClosePopups) => {
    const [listener, emitter] = Platform.iframeContact;
    frame.classList.add("display-n");
    listener.off("closePopup");
    listener.off("openConfirm");
    listener.off("contactUserInfo:load");
    listener.off("generateNewRoom");
    emitter.emit("contactUserInfo:clear");
    if (needClosePopups) {
        closePopups();
    }
    lockBody(true);
};

const emitNewRoom = async (subject, message, user, module, item, mxId, mxUserName, frame) => {
    const users = [{ userId: mxId, userName: mxUserName }];
    const data = module ? { user, module, item } : { user };

    lockBody();
    Platform.iframeRooms[1].emit("newRoom", {
        name: subject,
        message,
        users,
        ...data,
    });

    Platform.iframeRooms[0].off("closeUser:close");
    Platform.iframeRooms[0].on("closeUser:close", () => {
        closeModal(frame, true);
        Platform.iframeRooms[0].off("closeUser:close");
    });
};

const events = (frame, [listener, emitter], response) => {
    listener.off("closePopup");
    listener.on("closePopup", () => {
        closeModal(frame);
    });

    if (isLoad) {
        emitter.emit("contactUserInfo:data", {
            res: response,
        });
    } else {
        setTimeout(() => {
            emitter.emit("contactUserInfo:events");
        }, 100);
        listener.off("contactUserInfo:load");
        listener.on("contactUserInfo:load", () => {
            emitter.emit("contactUserInfo:data", {
                res: response,
            });
            isLoad = true;
        });
    }

    listener.off("generateNewRoom");
    listener.on(
        "generateNewRoom",
        ({
            context: {
                data: { subject, message, user, item, module, mxId, mxUserName },
            },
        }) => {
            // eslint-disable-next-line no-underscore-dangle
            const url = `${globalThis.__current_sub_domain_url}chats/ajax_chats_operations/insert_room`;
            postRequest(url, { user, item, module }, "json")
                .then(res => {
                    if (res.idRoom === undefined) {
                        if (res.subject !== undefined) {
                            emitNewRoom(res.subject, message, user, module, item, mxId, mxUserName, frame);
                        } else {
                            emitNewRoom(subject, message, user, null, null, mxId, mxUserName, frame);
                        }
                    } else {
                        openChatPopup(response.idRoom);
                        setTimeout(() => closeModal(frame), 2000);
                    }
                })
                .catch(error => {
                    handleRequestError(error);
                    emitter.emit("disableSetLoader");
                });
        }
    );

    listenerOpenConfirm(listener, emitter);
};

const openIframeContact = response => {
    const frameId = "chat-app-contact";
    generateIframe(`${MATRIX_WEB_CLIENT_HOST}/contact`, frameId, ["chat-app-modal"]).then(frame => {
        if (!Platform.iframeContact) {
            Platform.iframeContact = [new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]), new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST)];
        }
        events(frame, Platform.iframeContact, response);
        frame.classList.remove("display-n");
    });
};

export default button => {
    if (Platform.iframeRooms) {
        const [listener, emitter] = Platform.iframeRooms;
        emitter.emit("crypto.state:request");
        listener.off("crypto.state:response");
        listener.on("crypto.state:response", async ({ context: { data } }) => {
            const { state } = data;
            if (state) {
                const { user, item = null, module = null } = button.data();
                // eslint-disable-next-line no-underscore-dangle
                const url = `${globalThis.__current_sub_domain_url}chats/ajax_chats_operations/contact_user`;
                showLoader($("html"), "Start chat...", "fixed");
                try {
                    const response = await postRequest(url, { user, item, module }, "json");
                    const { idRoom, mess_type: messType, message } = response;
                    if (idRoom === undefined) {
                        if (messType === "success") {
                            openIframeContact(response);
                        } else {
                            systemMessages(message, messType);
                        }
                    } else {
                        closePopups();
                        openChatPopup(idRoom);
                    }
                } catch (error) {
                    handleRequestError(error);
                } finally {
                    hideLoader($("html"));
                }
            } else {
                systemMessages(translate({ plug: "general_i18n", text: "system_message_trying_to_start_chat_while_is_loaded" }), "warning");
                hideLoader($("html"));
            }
        });
    }
};

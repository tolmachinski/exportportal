import Platform from "@src/chat_app/platform";
import { MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";

// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
import openIframeRoom from "@src/chat_app/iframe-room/index";
import generateIframe from "@src/chat_app/generate-iframe/index";
import listenerMainLoad, {
    listenerSystemMessages,
    listenerUpdateChatCounter,
    listenerUpdateNewRoom,
    listenerGetSecurePassphrase,
    listenerSecretKeyCreate,
    listenerLogoutCloseIframes,
    listenerIdleWorkerResetTimeout,
} from "@src/chat_app/listeners/index";
import listenerNewRoom from "@src/chat_app/listeners/new-room";
import lockBody from "@src/util/dom/lock-body";
import emiterResizeParentFrame from "@src/chat_app/listeners/emiters";

const openChatBtn = document.querySelector(".js-open-chat");

const chatFrameToggleIncrease = target => {
    target.classList.toggle("minimized");
    /**
     * Тут используется проверка и remove/add вместо toggle, потому что чат можно открыть и с других мест кроме
     * кнопки, например в функции ниже chatFrameIncrease, а это значит что нужно убедиться в наличии класса
     */
    if (openChatBtn) {
        if (target.classList.contains("minimized")) {
            openChatBtn.classList.remove("chat-active");
        } else {
            openChatBtn.classList.add("chat-active");
        }

        if (globalThis.matchMedia("(max-width: 991px)").matches) {
            const targetRoom = "chat-app-room";
            if (document.getElementById(targetRoom) && !document.getElementById(targetRoom).classList.contains("display-n")) {
                Platform.iframeRooms[1].emit("closeRoom");
                document.getElementById(targetRoom).classList.add("display-n");
            }
        }

        if (globalThis.matchMedia("(max-width: 767px)").matches) {
            lockBody(!openChatBtn.classList.contains("chat-active"));
        }
    }
};

const chatFrameIncrease = target => {
    if (globalThis.matchMedia("(max-width: 767px)").matches) {
        lockBody();
    }
    target.classList.remove("minimized");
    openChatBtn?.classList.add("chat-active");
};

const events = ([listener, emitter, iframeRooms]) => {
    // ON LOAD MAIN CHAT IFRAME
    listenerMainLoad(listener, emitter);
    // Crypto key
    listenerGetSecurePassphrase(listener, emitter);
    listenerSecretKeyCreate(listener, emitter);
    // TOGGLE HEIGHT OF MAIN CHAT IFRAME
    listener.off("chatFrameToggleIncrease");
    listener.on("chatFrameToggleIncrease", () => chatFrameToggleIncrease(iframeRooms));
    listener.off("chatFrameIncrease");
    listener.on("chatFrameIncrease", () => chatFrameIncrease(iframeRooms));
    // OPEN ROOM WITH MESSAGES
    listener.off("openRoom");
    listener.on("openRoom", ({ context: { data } }) => openIframeRoom(data));
    // CLOSE ROOM ON DELETE ROOM
    listener.off("closeRoom");
    listener.on("closeRoom", () => {
        document.getElementById("chat-app-room").classList.add("display-n");
    });
    // Close iframe
    listener.off("closeMainIframe");
    listener.on("closeMainIframe", () => {
        if (globalThis.matchMedia("(max-width: 767px)").matches) {
            lockBody(true);
        }
        iframeRooms.classList.add("display-n");
        openChatBtn.classList.remove("chat-active");
    });
    // On last lazy loading timeline then need to disabled loader
    listener.off("roomLazyTimelineDisable");
    listener.on("roomLazyTimelineDisable", () => {
        if (Platform.iframeRoom) {
            const [, roomEmitter] = Platform.iframeRoom;
            roomEmitter.emit("roomLazyTimelineDisable");
        }
    });
    // Close all secondary iframes after logout session destroy
    listenerLogoutCloseIframes(listener);
    // Слушаем событие update комнаты в БД
    listenerUpdateNewRoom(listener);
    // SYSTEM MESSAGES
    listenerSystemMessages(listener);
    // Change location to /chats page
    listener.off("locationChangeToChats");
    listener.on("locationChangeToChats", ({ context: { data } }) => {
        // eslint-disable-next-line no-underscore-dangle
        globalThis.location.href = `${globalThis.__site_url}chats${data.roomId ? `?room=${data.roomId}` : ""}`;
    });
    listener.off("room.sendTyping");
    listener.on("room.sendTyping", ({ context: { data } }) => emitter.emit("room.sendTyping", data));
    // Update site chat counter
    listenerUpdateChatCounter(listener);
    // Idle Worker reset timeout
    listenerIdleWorkerResetTimeout(listener);
    // Отправляем размер окна
    emiterResizeParentFrame(emitter, "resizeParentFrameForRooms");

    if (!Platform.autorizationData?.exploreUser) {
        // OPEN NEW ROOM MODAL
        listenerNewRoom(listener, emitter);
    }
};

const iframeRoomsInitializationPromise = () =>
    new Promise(resolve => {
        const iframeRoomsId = "chat-app";
        if (document.getElementById(iframeRoomsId) && Platform.mayUseChat) {
            resolve(document.getElementById(iframeRoomsId));
        }

        generateIframe(`${MATRIX_WEB_CLIENT_HOST}/chat-frame`, iframeRoomsId, ["chat-app", "minimized"], null).then(frame => {
            if (!document.getElementById(iframeRoomsId).classList.contains("minimized")) {
                setTimeout(() => Platform.iframeRooms[1].emit("chatFrameIncrease"), 100);
            }
            if (!Platform.iframeRooms) {
                Platform.iframeRooms = [
                    new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]),
                    new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST),
                    document.getElementById(iframeRoomsId),
                ];
            }
            events(Platform.iframeRooms);
            Platform.mayUseChat = true;
            resolve(document.getElementById(iframeRoomsId));
        });
    });

export { iframeRoomsInitializationPromise };
export default iframeRoomsInitializationPromise;

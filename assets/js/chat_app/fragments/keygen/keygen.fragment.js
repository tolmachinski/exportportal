// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";

import Platform from "@src/chat_app/platform";
import generateIframe from "@src/chat_app/generate-iframe/index";
import openPopupUserInfo from "@src/chat_app/iframe-user-info/index";
import listenerMainLoad, {
    listenerDownloadFile,
    listenerGetSecurePassphrase,
    listenerOpenAttachFilesPopup,
    listenerOpenModalAddSampleOrder,
    listenerOpenModalAssignSampleOrder,
    listenerSystemMessages,
    listenerUpdateChatCounter,
    listenerUpdateNewRoom,
} from "@src/chat_app/listeners/index";
import listenerNewRoom from "@src/chat_app/listeners/new-room";
import { emitterLogout } from "@src/chat_app/listeners/emiters";
import { MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const attachEvents = ([listener, emitter], passPhrase, redirectUrl) => {
    // ON LOAD MAIN CHAT IFRAME
    listenerMainLoad(listener, emitter, passPhrase);
    // Слушаем событие открытие модалки загрузки файлов
    listenerOpenAttachFilesPopup(listener);
    // Слушаем событие открытие модалки добавление Sample Order
    listenerOpenModalAddSampleOrder(listener);
    // Слушаем событие открытие модалки подключение Sample Order
    listenerOpenModalAssignSampleOrder(listener);
    // OPEN NEW ROOM MODAL
    listenerNewRoom(listener, emitter);
    // Слушаем событие update комнаты в БД
    listenerUpdateNewRoom(listener);
    // SYSTEM MESSAGES
    listenerSystemMessages(listener);
    // DOWNLOAD FILE
    listenerDownloadFile(listener, emitter);
    // OPEN NEW ROOM MODAL
    listener.off("openUserInfo");
    listener.on("openUserInfo", ({ context: { data } }) => openPopupUserInfo(data));
    // Update site chat counter
    listenerUpdateChatCounter(listener);
    // Crypto key
    listenerGetSecurePassphrase(listener, emitter);
    listener.off("SecretStorage.created");
    listener.on("SecretStorage.created", async () => {
        try {
            await postRequest(redirectUrl);
        } catch (error) {
            emitter.emit("SecretKey.updateFailure", error);
        }
    });
    // Logout
    emitterLogout(listener, emitter);
    // OPEN NEW ROOM MODAL
    listener.off("openZohoChat");
    listener.on("openZohoChat", () => {
        // @ts-ignore
        document.querySelector(".js-btn-call-main-chat").click();
    });
};

/**
 * Handler the matrix chat logout event.
 *
 * @param {HTMLElement} [resultNode]
 */
const onLogout = resultNode => {
    if (!resultNode) {
        return;
    }

    const element = document.createElement("span");
    element.classList.add("badge");
    element.classList.add("badge-success");
    element.textContent = "Done";
    resultNode.insertAdjacentElement("afterbegin", element);
};

/**
 * Handler the matrix chat logout event.
 *
 * @param {HTMLElement} [resultNode]
 */
const showError = resultNode => {
    if (!resultNode) {
        return;
    }

    const element = document.createElement("span");
    element.classList.add("badge");
    element.classList.add("badge-danger");
    element.textContent = "Error";
    resultNode.insertAdjacentElement("afterbegin", element);
};

export default async (chatContainerSelector, resultSelector, userMxId, password, passPhrase, botId, redirectUrl, hasKeys = false) => {
    Platform.autorizationData = {
        user: { user: userMxId, password, hasKeys },
        bot: { userId: botId },
    };

    const frame = await generateIframe(`${MATRIX_WEB_CLIENT_HOST}/chat-page`, "chats-app", ["my-chats-app"], chatContainerSelector);
    const eventListener = new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]);
    const eventEmitter = new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST);
    if (!Platform.iframePage) {
        Platform.iframePage = [eventListener, eventEmitter];
    }
    attachEvents(Platform.iframePage, passPhrase, redirectUrl);
    eventListener.on("started", async () => {
        try {
            showLoader(document.body);
            eventListener.on("logoutDone", onLogout.bind(null, document.querySelector(resultSelector)));
            eventEmitter.emit("logout");
        } catch (error) {
            handleRequestError(error);
            showError(document.querySelector(resultSelector));
            eventEmitter.emit("logout");
        } finally {
            hideLoader(document.body);
        }
    });
};

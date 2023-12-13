import Platform from "@src/chat_app/platform";
import { MATRIX_WEB_CLIENT_HOST, SHIPPER_PAGE } from "@src/common/constants";

// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
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
    listenerSecretKeyCreate,
    listenerLogoutCloseIframes,
    listenerWindowLocationChange,
    listenerIdleWorkerResetTimeout,
} from "@src/chat_app/listeners/index";
import listenerNewRoom from "@src/chat_app/listeners/new-room";
import offResizeCallback from "@src/util/dom/off-resize-callback";
import onResizeCallback from "@src/util/dom/on-resize-callback";

const openRoomByGetInURL = () => {
    let roomData = null;
    const get = window.location.search;
    if (get.length > 0) {
        const decryptLinkKeys = {
            "%21": "!",
            "%3A": ":",
        };
        const url = new URL(globalThis.location.href);
        let roomId = url.searchParams.get("room");
        if (roomId) {
            Object.keys(decryptLinkKeys).forEach(decryptKey => {
                roomId = roomId.replace(decryptKey, decryptLinkKeys[decryptKey]);
            });
            window.history.pushState({}, document.title, window.location.pathname);
            roomData = {
                id: roomId,
                prioritySorting: true,
            };
        }
    }
    return roomData;
};

const events = ([listener, emitter]) => {
    // ON LOAD MAIN CHAT IFRAME
    listenerMainLoad(listener, emitter, null, openRoomByGetInURL());
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
    listenerSecretKeyCreate(listener, emitter);
    // Close all secondary iframes after logout session destroy
    listenerLogoutCloseIframes(listener);
    // Слушаем событие window change location
    listenerWindowLocationChange(listener);
    // OPEN NEW ROOM MODAL
    listener.off("openZohoChat");
    listener.on("openZohoChat", () => {
        // @ts-ignore
        document.querySelector(".js-btn-call-main-chat").click();
    });
    // Idle Worker reset timeout
    listenerIdleWorkerResetTimeout(listener);

    if (!Platform.autorizationData?.exploreUser) {
        // Слушаем событие открытие модалки загрузки файлов
        listenerOpenAttachFilesPopup(listener);
        // Слушаем событие открытие модалки добавление Sample Order
        listenerOpenModalAddSampleOrder(listener);
        // Слушаем событие открытие модалки подключение Sample Order
        listenerOpenModalAssignSampleOrder(listener);
        // OPEN NEW ROOM MODAL
        listenerNewRoom(listener, emitter);
    }
};

const calcResizePage = frame => {
    const maxWidth = 1024;
    const maxHeight = 850;
    const minHeight = 600;
    const mainHeight = window.innerHeight;
    const mainWidth = window.innerWidth;
    const iframeWr = document.getElementById("my-chats-app-wr");
    const iframeTopPosition = iframeWr.offsetTop;
    const iframePaddingtop = window.getComputedStyle(iframeWr, null).getPropertyValue("padding-top");
    const iframeBottomPositionIdName = SHIPPER_PAGE ? "js-epl-mobile-header-bottom" : "js-mep-header-bottom";
    const iframeBottomPosition = document.getElementById(iframeBottomPositionIdName).offsetHeight;
    const iframe = document.getElementById(frame);
    const minus = iframeTopPosition + iframeBottomPosition + parseInt(iframePaddingtop, 10);
    let plus;

    plus = "";
    if (mainWidth <= maxWidth) {
        if (mainHeight > minHeight && mainHeight < maxHeight) {
            plus = `${mainHeight - minus}px`;
        } else if (mainHeight < minHeight) {
            plus = `${minHeight - minus}px`;
        } else if (mainHeight > maxHeight) {
            plus = `${maxHeight - minus}px`;
        }
    }

    iframe.style.height = plus;
};

const resizePage = frame => {
    calcResizePage(frame);

    offResizeCallback();
    onResizeCallback(() => {
        calcResizePage(frame);
    });
};

export default () => {
    generateIframe(`${MATRIX_WEB_CLIENT_HOST}/chat-page`, "my-chats-app", ["my-chats-app"], "my-chats-app-wr").then(frame => {
        if (!Platform.iframePage) {
            Platform.iframePage = [new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]), new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST)];
        }

        events(Platform.iframePage);
        resizePage("my-chats-app");
    });
};

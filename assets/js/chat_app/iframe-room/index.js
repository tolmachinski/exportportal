import Platform from "@src/chat_app/platform";
import { MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";

// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
import generateIframe from "@src/chat_app/generate-iframe/index";
import {
    listenerDownloadFile,
    listenerOpenAttachFilesPopup,
    listenerOpenModalAddSampleOrder,
    listenerOpenModalAssignSampleOrder,
    listenerWindowLocationChange,
} from "@src/chat_app/listeners/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";

let isLoad = false;

const events = ({ target, room, type, mainUser, clientOnline, showOfflineStatus }, [listener, emitter]) => {
    // Отправка данных в комнату
    Platform.activeRoom = {
        room,
        type,
        mainUser,
        windowSize: {
            width: window.innerWidth,
            height: window.innerHeight,
        },
        clientOnline,
        showOfflineStatus,
    };

    if (isLoad) {
        emitter.emit("openRoom:data", Platform.activeRoom);
    } else {
        setTimeout(() => {
            emitter.emit("openRoom:events");
        }, 100);

        listener.off("openRoom:load");
        listener.on("openRoom:load", () => {
            emitter.emit("openRoom:data", Platform.activeRoom);
            delete Platform.activeRoom.room.firstSetUnread;
            isLoad = true;
        });

        onResizeCallback(() => {
            emitter.emit("openRoom:data", {
                ...Platform.activeRoom,
                type: "roomUserStatus",
                windowSize: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                },
            });
        }, window);
    }

    // Слушаем событие закрытия комнаты и скрывает её(чтоб не делать кучу инициализаций айфреймов
    listener.off("closeRoom");
    listener.on("closeRoom", () => {
        Platform.iframeRooms[1].emit("closeRoom");
        document.getElementById(target).classList.add("display-n");
    });

    listener.off("lazyLoadingTimeline");
    listener.on("lazyLoadingTimeline", ({ context: { data } }) => {
        Platform.iframeRooms[1].emit("lazyLoadingTimeline", { roomId: data.roomId });
    });

    // Слушаем событие установки сообщение непрочитаным
    listener.off("sendSetUnread");
    listener.on("sendSetUnread", ({ context: { data } }) => {
        const { roomId, idMessage } = data;
        Platform.iframeRooms[1].emit("sendSetUnread", { roomId, idMessage });
    });

    // Слушаем событие чтение непрочитанных сообщений
    listener.off("readSetUnread");
    listener.on("readSetUnread", ({ context: { data } }) => {
        const { roomId, idMessage } = data;
        Platform.iframeRooms[1].emit("readSetUnread", { roomId, idMessage });
    });

    // Слушаем событие отправки сообщений со статусом not_sent
    listener.off("resendNotSentMessage");
    listener.on("resendNotSentMessage", ({ context: { data } }) => {
        const { txnId, roomId } = data;
        Platform.iframeRooms[1].emit("resendNotSentMessage", { txnId, roomId });
    });

    // Слушаем событие удаления сообщений со статусом not_sent
    listener.off("removeNotSentMessage");
    listener.on("removeNotSentMessage", ({ context: { data } }) => {
        const { txnId, roomId } = data;
        Platform.iframeRooms[1].emit("removeNotSentMessage", { txnId, roomId });
    });

    listener.off("showOfflineStatus");
    listener.on("showOfflineStatus", () => {
        Platform.iframeRooms[1].emit("showOfflineStatus");
    });

    // Слушаем событие скачивания файла
    listenerDownloadFile(listener, emitter);
    // Слушаем событие window change location
    listenerWindowLocationChange(listener);
    if (!Platform.autorizationData?.exploreUser) {
        // Слушаем отправку сообщения
        listener.off("sendMessage");
        listener.on("sendMessage", ({ context: { data } }) => {
            Platform.iframeRooms[1].emit("sendMessage", { message: data.message });
        });
        // Слушаем отправку события прочитанности сообщений
        listener.off("sendReceipt");
        listener.on("sendReceipt", ({ context: { data } }) => {
            Platform.iframeRooms[1].emit("sendReceipt", { timelineEvent: data.timelineEvent });
        });
        // Слушаем событие пометить как непрочитанную
        listener.off("readMark");
        listener.on("readMark", ({ context: { data } }) => {
            Platform.iframeRooms[1].emit("readMark", { room: data.room });
        });
        // Слушаем событие удаления комнаты
        listener.off("deleteRoom");
        listener.on("deleteRoom", ({ context: { data } }) => {
            Platform.iframeRooms[1].emit("deleteRoom", { room: data.room });
        });
        // Слушаем событие открытие модалки загрузки файлов
        listenerOpenAttachFilesPopup(listener);
        // Слушаем событие открытие модалки добавление Sample Order
        listenerOpenModalAddSampleOrder(listener);
        // Слушаем событие открытие модалки подключение Sample Order
        listenerOpenModalAssignSampleOrder(listener);
    }
};

export default data => {
    const iframeRoomId = "chat-app-room";
    generateIframe(`${MATRIX_WEB_CLIENT_HOST}/chat-room`, iframeRoomId, ["chat-app"]).then(frame => {
        if (!Platform.iframeRoom) {
            Platform.iframeRoom = [new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]), new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST)];
        }
        events({ target: iframeRoomId, ...data }, Platform.iframeRoom);
        frame.classList.remove("display-n");
    });
};

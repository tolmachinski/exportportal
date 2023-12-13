import Platform from "@src/chat_app/platform";
import { MATRIX_WEB_CLIENT_HOST } from "@src/common/constants";

// @ts-ignore
import { EventListener, EventEmmiter } from "@ep-developers/frame-events";
import generateIframe from "@src/chat_app/generate-iframe/index";
import lockBody from "@src/util/dom/lock-body";

let isLoad = false;

const events = (frame, [listener, emitter], room) => {
    listener.off("closePopup");
    listener.on("closePopup", () => {
        listener.off("closePopup");
        listener.off("userProfilePopup:load");
        frame.classList.add("display-n");
        lockBody(true);
    });

    if (isLoad) {
        emitter.emit("userProfilePopup:data", {
            res: room,
        });
    } else {
        setTimeout(() => {
            emitter.emit("userProfilePopup:events");
        }, 100);
        listener.off("userProfilePopup:load");
        listener.on("userProfilePopup:load", () => {
            emitter.emit("userProfilePopup:data", {
                res: room,
            });
            isLoad = true;
        });
    }

    lockBody();
};

export default ({ room }) => {
    const frameId = "chat-app-info";
    generateIframe(`${MATRIX_WEB_CLIENT_HOST}/user-info`, frameId, ["chat-app-modal"]).then(frame => {
        if (!Platform.iframeUserInfo) {
            Platform.iframeUserInfo = [new EventListener(frame, [MATRIX_WEB_CLIENT_HOST]), new EventEmmiter(frame.contentWindow, MATRIX_WEB_CLIENT_HOST)];
        }
        events(frame, Platform.iframeUserInfo, room);
        frame.classList.remove("display-n");
    });
};

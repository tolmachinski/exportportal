import delay from "@src/util/async/delay";
import Platform from "@src/chat_app/platform";
import { iframeRoomsInitializationPromise } from "@src/chat_app/iframe-rooms/index";

const openChatPopup = async id => {
    if (!Platform.mayUseChat) {
        await iframeRoomsInitializationPromise();
        await delay(1000);
    }
    Platform.iframeRooms[1].emit("chatFrameIncrease");

    if (id) {
        Platform.iframeRooms[1].emit("openRoomById", { id, prioritySorting: true });
    }
};

export default openChatPopup;

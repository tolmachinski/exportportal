import openPopupNewChat from "@src/chat_app/iframe-new-chat/index";

// OPEN NEW ROOM MODAL
const listenerNewRoom = (listener, emitter) => {
    listener.off("newRoom");
    listener.on("newRoom", ({ context: { data } }) => {
        openPopupNewChat(emitter, data.mainUser);
    });
};

export default listenerNewRoom;

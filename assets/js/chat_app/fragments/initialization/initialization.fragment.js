import Platform from "@src/chat_app/platform";

export default async (user, password, botId, initIframe, hasKeys = false, hideChat = false, exploreUser = false) => {
    Platform.autorizationData = {
        user: {
            user,
            password,
            hasKeys,
        },
        bot: {
            userId: botId,
        },
        exploreUser,
    };

    if (hideChat) {
        document.body.classList.add("js-hidden-chat");
    }

    if (initIframe === "page") {
        const { default: initializationIframePage } = await import("@src/chat_app/iframe-page/index");
        initializationIframePage();
    } else {
        const { default: initializationIframeRooms } = await import("@src/chat_app/iframe-rooms/index");
        initializationIframeRooms();
    }
};

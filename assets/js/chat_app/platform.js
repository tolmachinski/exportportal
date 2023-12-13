const platformParams = new Map();

const Platform = {
    // Autorization data for user and bot
    set autorizationData(val) {
        platformParams.set("autorizationData", val);
    },

    get autorizationData() {
        return platformParams.get("autorizationData");
    },

    // Main chat frame with list of rooms
    set iframeRooms(val) {
        platformParams.set("iframeRooms", val);
    },

    get iframeRooms() {
        return platformParams.get("iframeRooms");
    },

    // Popup new chat
    set iframeNewChat(val) {
        platformParams.set("iframeNewChat", val);
    },

    get iframeNewChat() {
        return platformParams.get("iframeNewChat");
    },

    // Room frame
    set iframeRoom(val) {
        platformParams.set("iframeRoom", val);
    },

    get iframeRoom() {
        return platformParams.get("iframeRoom");
    },

    // Room frame
    set iframePage(val) {
        platformParams.set("iframePage", val);
    },

    get iframePage() {
        return platformParams.get("iframePage");
    },

    // Check if may use chat for some actions
    set mayUseChat(val) {
        platformParams.set("mayUseChat", val);
    },

    get mayUseChat() {
        return platformParams.get("mayUseChat");
    },

    // Room frame
    set iframeUserInfo(val) {
        platformParams.set("iframeUserInfo", val);
    },

    get iframeUserInfo() {
        return platformParams.get("iframeUserInfo");
    },

    // Iframe contact
    set iframeContact(val) {
        platformParams.set("iframeContact", val);
    },

    get iframeContact() {
        return platformParams.get("iframeContact");
    },

    set activeRoom(val) {
        platformParams.set("activeRoom", val);
    },

    get activeRoom() {
        return platformParams.get("activeRoom");
    },
};

export default Platform;

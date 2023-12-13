const platformParams = new Map();

const Platform = {
    // Autorization data for user and bot
    set eplPage(val) {
        platformParams.set("eplPage", val);
    },

    get eplPage() {
        return platformParams.get("eplPage");
    },
};

export default Platform;

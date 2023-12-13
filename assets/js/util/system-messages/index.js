import Platform from "@src/epl/platform";
import $ from "jquery";

let Message = null;

const initialize = async () => {
    if (Message === null) {
        const { Messenger } = await import(/* webpackChunkName: "system-messages" */ "@src/util/system-messages/messenger/index");

        if (Platform.eplPage) {
            // @ts-ignore
            await import(/* webpackChunkName: "epl-system-messages-styles" */ "@scss/epl/import/blocks/_system-messages.scss");
        } else {
            // @ts-ignore
            await import(/* webpackChunkName: "system-messages-styles" */ "@scss/user_pages/general/blocks/_system_messages.scss");
        }

        Message = new Messenger($(".system-messages"), $(".system-messages__cards"));
    }

    return Message;
};

const systemMessages = async (text, type) => {
    await initialize();

    Message.open(text, type);
};

const systemMessagesCardClose = async node => {
    await initialize();

    Message.findAndClose(node.closest("li").get(0));
};

const systemMessagesClose = async () => {
    await initialize();

    Message.closeAll();
};

export { systemMessages, systemMessagesCardClose, systemMessagesClose };
export default async () => initialize();

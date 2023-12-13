import { systemMessages } from "@src/util/system-messages/index";

export default messages => {
    messages.forEach(message => {
        systemMessages(message.message, message.type);
    });
};

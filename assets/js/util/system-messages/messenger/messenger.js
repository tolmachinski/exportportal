import $ from "jquery";
import Message from "@src/util/system-messages/messenger/message";
import Platform from "@src/epl/platform";

export default class Messenger {
    /**
     *
     * @param {JQuery} node
     * @param {HTMLElement} list
     */
    constructor(node, list) {
        this.messages = new Set();
        this.sysMesNode = node;
        this.messagesList = list;
    }

    open(text, type = "error") {
        this.sysMesNode = $(".system-messages");
        let preparedMessages = [];
        const messageType = type.replace(/^message-/, "");
        const variableType = typeof text;
        const messagesList = $(".system-messages__cards");
        const typeMetadata = {
            info: { text: Platform.eplPage ? "Notification" : "Info", class: "info" },
            error: { text: "Error", class: "error" },
            warning: { text: "Warning", class: "warning" },
            success: { text: "Success", class: "success" },
        };
        const hasType = Object.prototype.hasOwnProperty.call(typeMetadata, messageType);
        const builders = {
            string(messages) {
                return [messages];
            },
            object(messages) {
                return Object.values(messages);
            },
        };

        if (Object.prototype.hasOwnProperty.call(builders, variableType)) {
            preparedMessages = builders[variableType].call(null, text);
        } else {
            preparedMessages = Array.from(text);
        }

        if (this.sysMesNode.length && !this.sysMesNode.is(":visible")) {
            this.sysMesNode.fadeIn("fast");
        }

        preparedMessages.forEach(txt => {
            const message = new Message(
                txt,
                hasType ? typeMetadata[messageType].text : typeMetadata.error.text,
                typeMetadata[hasType ? messageType : "error"].class
            );

            messagesList.prepend(message.node);
            setTimeout(() => {
                if (this.messages.has(message)) {
                    message.close();
                    this.drop(message);
                }
            }, 20000);

            this.messages.add(message);
        });
    }

    find(node) {
        /** @type {Array<Message>} */
        const list = Array.from(this.messages.values());
        for (let i = 0; i < list.length; i += 1) {
            const message = list[i];
            if (message.node === node) {
                return message;
            }
        }

        return null;
    }

    findAndClose(node) {
        const message = this.find(node);
        if (message !== null) {
            message.close();
            this.drop(message);
        }
    }

    closeAll() {
        Array.from(this.messages.values()).forEach(message => {
            if (this.messages.has(message)) {
                message.close();
                this.drop(message);
            }
        });
    }

    drop(message) {
        this.messages.delete(message);
    }
}

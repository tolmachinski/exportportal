declare namespace SystemMessages {

    type MessageType = "error" | "success" | "warning" | "info";

    declare class Message {
        /**
         * Creates instance of message
         */
        constructor(text: string, type: MessageType, classList: string): Message;

        /**
         * Closes the message.
         */
        close(): void;

        /**
         * Renders the message.
         */
        render(): void;
    }

    declare class Messenger {
        /**
         * Creates instance of messenger
         * @param node messenger container
         * @param list list with messages
         */
        constructor(node: JQuery, list: JQuery);

        /**
         * Opens a message of specified type
         *
         * @param text text of the message
         * @param type type of the message
         */
        open(text: string, type: string);

        /**
         * Finds message by its DOM node
         *
         * @param node the message DOM node
         */
        find(node: HTMLElement): ?Message;

        /**
         * Finds and closes message by its DOM node
         *
         * @param node  the message DOM node
         */
        findAndClose(node: HTMLElement): void;

        /**
         * Closes all messages
         */
        closeAll(): void;

        /**
         * Drops the message from the list
         *
         * @param message the target message
         */
        drop(message: Message): void;
    }
}

export module "./index" {
    declare const Messenger = SystemMessages.Messenger
}

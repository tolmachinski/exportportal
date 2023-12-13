namespace CreateEnvelope {
    interface RecipientOptions {
        /**
         * The type of the recipient.
         */
        public type: string;

        /**
         * The assignee reference.
         */
        public assignee: string;

        /**
         * The assignee title.
         */
        public assigneeName: string;

        /**
         * The assignee group.
         */
        public assigneeGroup: string;

        /**
         * The assignee group color
         */
        public assigneeGroupColor: string;

        /**
         * The display type of the assignee.
         */
        public recipientType: string;

        /**
         * The display type of the due date.
         */
        public expiresAt: string;
    }

    class RecipientsHandler {
        /**
         * The list of recipients.
         */
        private recipients: RecipientOptions[];

        /**
         * The source list of types
         */
        private types: JQuery;

        /**
         * The source list of assignees.
         */
        private assignees: JQuery;

        /**
         * The source list of assignees.
         */
        private expiresAt: JQuery;

        /**
         * The HTML recipients list renderer
         */
        private renderer: RecipientsRenderer;

        /**
         * Indicates if validation is enabled or disabled.
         */
        private enabledValidation: boolean;

        /**
         * Creates the instance of the handler.
         */
        constructor(renderer: RecipientsRenderer, assigneesList: HTMLElement, typesList: HTMLElement);

        /**
         * Validates recipients source nodes.
         */
        validateNodes(formNode: JQuery): Promise<boolean>;

        /**
         * Enables validation for recipients source lists.
         */
        enableValidation(): Promise<void>;

        /**
         * Disables validation for recipients source lists.
         */
        disableValidation(): Promise<void>;

        /**
         * Resets the recipients source lists.
         */
        resetLists(): Promise<void>;

        /**
         * Adds one recipient to the list.
         */
        addRecipient(formNode: JQuery): Promise<void>;

        /**
         * Removes recipient from the list.
         */
        removeRecipient(index?: number): void;

        /**
         * Offsets the recipient in the list.
         */
        offsetRecipient(index?: number, direction?: number): void;

        /**
         * Chages recipient position in the list.
         */
        changeRecipientPosition(oldPosition: number, newPosition: number): void;
    }

    class RecipientsRenderer {
        /**
         * Create instance of the renderer.
         */
        constructor(container: HTMLElement, template: string);

        /**
         * Renders one recipient.
         */
        render(assignee: RecipientOptions, index?: number): void;

        /**
         * Renders the recipients list.
         */
        renderList(list: Array<RecipientOptions>): void;
    }
}

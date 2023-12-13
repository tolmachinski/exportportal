import $ from "jquery";

import { disableFormValidation, enableFormValidation, validateElement } from "@src/plugins/validation-engine/index";

/**
 * @type {CreateEnvelope.RecipientsHandler}
 */
export default class RecipientsHandler {
    /**
     * Create instance of recipients handler.
     *
     * @param {CreateEnvelope.RecipientsRenderer} renderer
     * @param {HTMLElement} addButton
     * @param {HTMLElement} assigneesList
     * @param {HTMLElement} typesList
     * @param {HTMLElement} expiresAt
     * @param {Array<CreateEnvelope.RecipientOptions>} recipients
     * @param {number} [limit]
     */
    constructor(renderer, addButton, assigneesList, typesList, expiresAt, recipients = [], limit = null) {
        this.assignees = $(assigneesList);
        this.button = $(addButton);
        this.types = $(typesList);
        this.expiresAt = $(expiresAt);
        this.limit = limit;
        this.renderer = renderer;
        this.recipients = recipients || [];
        this.enabledValidation = false;

        this.renderer.renderList(this.recipients);
    }

    /**
     * The current amount of the recipients.
     */
    currentAmount() {
        return this.recipients.length;
    }

    /**
     * Validates recipients source nodes.
     *
     * @param {JQuery} formNode
     */
    async validateNodes(formNode) {
        await this.enableValidation();
        await disableFormValidation(formNode);
        await enableFormValidation(formNode);

        const assigneesAreValid = await validateElement(this.assignees);
        const typesAreValid = await validateElement(this.types);

        return assigneesAreValid && typesAreValid;
    }

    /**
     * Enables validation for recipients source lists.
     */
    async enableValidation() {
        [this.assignees, this.types].forEach(node => {
            const dataset = node.data();
            if (dataset.validate) {
                node.addClass(dataset.validate);
            }
        });
    }

    /**
     * Disables validation for recipients source lists.
     */
    async disableValidation() {
        [this.assignees, this.types].forEach(node => {
            const dataset = node.data();
            if (dataset.validate) {
                node.removeClass(dataset.validate);
            }
        });
    }

    /**
     * Resets the recipients source lists.
     */
    async resetLists() {
        this.assignees.val(null);
        this.types.val(null);
        this.expiresAt.val(null);
    }

    /**
     * Adds one recipient to the list.
     *
     * @param {JQuery} formNode
     */
    async addRecipient(formNode) {
        if ((await this.validateNodes(formNode)) === false) {
            return;
        }

        /** @type {CreateEnvelope.RecipientOptions} */
        const recipient = {
            type: this.types.val().toString(),
            assignee: this.assignees.val().toString(),
            expiresAt: this.assignees.val().toString(),
            assigneeName: this.assignees.find(":selected").data('name'),
            assigneeGroup: this.assignees.find(":selected").data('group'),
            assigneeGroupColor: this.assignees.find(":selected").data('color'),
            recipientType: this.types.find(":selected").text(),
        };
        this.recipients.push(recipient);
        this.renderer.render(recipient, this.recipients.length - 1);
        this.resetLists();
        this.disableValidation();
        if (this.limit !== null && this.recipients.length >= this.limit) {
            this.button.addClass("disabled");
        }
    }

    /**
     * Removes recipient from the list.
     *
     * @param {number} [index=null]
     */
    removeRecipient(index = null) {
        if (index === null) {
            return;
        }

        if (!this.recipients[index]) {
            throw new ReferenceError(`The recipients with index "${index}" is not found in the list`);
        }

        this.recipients.splice(index, 1);
        this.renderer.renderList(this.recipients);
        if (this.limit !== null && this.recipients.length < this.limit) {
            this.button.removeClass("disabled");
        }
    }

    /**
     * Offsets the recipient in the list.
     *
     * @param {number} [index=null]
     * @param {string} [direction=null]
     */
    offsetRecipient(index = null, direction = null) {
        const applyOffset = value => {
            switch (direction) {
                case "up":
                    return value > 0 ? value - 1 : 0;
                case "down":
                    return value + 1;
                default:
                    return null;
            }
        };

        if (index === null || direction === null) {
            return;
        }

        if (!this.recipients[index]) {
            throw new ReferenceError(`The recipient with index "${index}" is not found in the list.`);
        }
        if (["up", "down"].indexOf(direction) === -1) {
            throw new TypeError(`The direction "${direction}" is not supported.`);
        }

        this.changeRecipientPosition(index, applyOffset(index));
    }

    /**
     * Chages recipient position in the list.
     *
     * @param {number} oldPosition
     * @param {number} newPosition
     */
    changeRecipientPosition(oldPosition, newPosition) {
        if ((oldPosition === newPosition && newPosition === 0) || newPosition === this.recipients.length) {
            return;
        }

        [this.recipients[oldPosition], this.recipients[newPosition]] = [this.recipients[newPosition], this.recipients[oldPosition]];
        this.renderer.renderList(this.recipients);
    }

    /**
     * Iterates over recipients.
     */
    [Symbol.iterator]() {
        /**
         * @param {Array<CreateEnvelope.RecipientOptions>} recipients
         */
        function* iterateRecipients(recipients) {
            for (let index = 0; index < recipients.length; index += 1) {
                const element = recipients[index];

                yield { name: `recipients[${index}][type]`, value: element.type };
                yield { name: `recipients[${index}][order]`, value: index + 1 };
                yield { name: `recipients[${index}][assignee]`, value: element.assignee };
                yield { name: `recipients[${index}][expiresAt]`, value: element.expiresAt };
            }
        }

        return iterateRecipients(this.recipients);
    }
}

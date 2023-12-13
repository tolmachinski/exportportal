import { renderTemplate } from "@src/util/templates";

/**
 * @type {CreateEnvelope.RecipientsRenderer}
 */
export default class RecipientsRenderer {
    /**
     * Create instance of the renderer.
     *
     * @param {HTMLElement} container
     * @param {string} template
     */
    constructor(container, template) {
        this.container = container;
        this.template = template;
    }

    /**
     * Renders one recipient.
     *
     * @param {CreateEnvelope.RecipientOptions} recipient
     * @param {number} index
     */
    render(recipient, index = 0) {
        this.container.insertAdjacentHTML(
            "beforeend",
            renderTemplate(this.template, {
                index,
                position: index + 1,
                assigneeName: recipient.assigneeName,
                assigneeGroup: recipient.assigneeGroup,
                assigneeGroupColor: recipient.assigneeGroupColor,
                recipientType: recipient.recipientType,
                expiresAt: recipient.expiresAt,
            })
        );
    }

    /**
     * Renders the recipients list.
     *
     * @param {CreateEnvelope.RecipientOptions[]} list
     */
    renderList(list) {
        while (this.container.firstChild) {
            this.container.removeChild(this.container.firstChild);
        }

        list.forEach((recipient, i) => this.render(recipient, i));
    }
}

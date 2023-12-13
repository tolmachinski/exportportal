import $ from "jquery";
import { renderTemplate } from "@src/util/templates";
import Platform from "@src/epl/platform";

export default class Message {
    /**
     * Creates instance of message
     *
     * @param {string} text
     * @param {string} type
     * @param {string} classList
     */
    constructor(text, type, classList) {
        this.type = type;
        this.text = text;
        this.classList = classList;
        this.node = null;

        const container = document.createElement("div");
        container.insertAdjacentHTML("afterbegin", this.render());

        this.node = container.children.item(0);
    }

    /**
     * Closes the message.
     */
    close() {
        const node = $(this.node);
        node.slideUp("slow", () => {
            if (this.node.parentElement && this.node.parentElement.childElementCount === 1) {
                $(this.node.parentElement.parentElement).slideUp();
            }
            node.remove();
            $(this.node.parentElement).hide();
        });
    }

    /**
     * Renders the message.
     */
    render() {
        const closeIcon = Platform.eplPage
            ? `<i class="ep-icon ep-icon_remove-stroke call-action" data-js-action="system-mesages:card-close"></i>`
            : `<i class="ep-icon ep-icon_remove-stroke call-action call-function" data-js-action="system-mesages:card-close" data-callback="systemMessagesCardClose"></i>`;

        return renderTemplate(
            `<li class="system-messages__card system-messages__card--{{class}} flipInX" style="display:block">
                <div class="system-messages__card-ttl">
                    <strong>{{type}}</strong>
                    ${closeIcon}
                </div>
                <div class="system-messages__card-txt">
                    {{message}}
                </div>
            </li>`,
            {
                type: this.type,
                class: this.classList,
                message: this.text,
            }
        );
    }
}

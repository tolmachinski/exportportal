import { addCounter } from "@src/plugins/textcounter/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { initialize as createDatepicker } from "@src/plugins/datepicker/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";
import RecipientsHandler from "@src/documents/process-envelope/RecipientsHandler";
import RecipientsRenderer from "@src/documents/process-envelope/RecipientsRenderer";

/**
 * Send request to create envelope.
 *
 * @param {URL} url
 * @param {number} orderId
 * @param {number} envelopeId
 * @param {RecipientsHandler} recipients
 * @param {number} maxRecipients
 * @param {HTMLElement} container
 * @param {JQuery} form
 */
const saveEnvelope = async (url, orderId, envelopeId, recipients, maxRecipients, container, form) => {
    const recipientsList = Array.from(recipients);
    const entities = [
        { name: "order", value: orderId },
        { name: "envelope", value: envelopeId },
    ].filter(f => f.value);
    if (recipientsList.length < 1) {
        systemMessages(translate({ plug: "general_i18n", text: "order_documents_process_form_recipient_empty_list_warning" }), "warning");

        return Promise.resolve();
    }
    if (maxRecipients !== null && recipients.currentAmount() > maxRecipients) {
        systemMessages(
            translate({
                plug: "general_i18n",
                text: "order_documents_process_form_recipient_list_too_much_warning",
                replaces: { "{{AMOUNT}}": maxRecipients },
            }),
            "warning"
        );

        return Promise.resolve();
    }

    showLoader(container);
    form.find("button[type=submit]").addClass("disabled");

    // @ts-ignore
    return postRequest(url, entities.concat(form.serializeArray()).concat(Array.from(recipients)), "json")
        .then(data => {
            systemMessages(data.message, data.mess_type);
            if (data.mess_type === "success") {
                closeFancyBox();

                EventHub.trigger("documents:envelope-processed", data.envelope || {});
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            form.find("button[type=submit]").removeClass("disabled");
            hideLoader(container);
        });
};

/**
 * Collects all <sctipt type="text/template"> from the provided container.
 *
 * @param {HTMLElement} containerElement
 */
const collectTemplates = containerElement => {
    /** @type {{[x: string]: string}} */
    const templates = {};
    Array.from(containerElement.querySelectorAll('script[type="text/template"]'))
        .filter(element => element instanceof HTMLElement && element.dataset.name)
        .forEach(element => {
            // @ts-ignore
            templates[element.dataset.name] = element.innerHTML.trim();
            element.remove();
        });

    return templates;
};

/**
 * Fragment entrypoint.
 *
 * @param {number} envelopeId
 * @param {number} orderId
 * @param {{[x: string]: string }} selectors
 * @param {any} recipients
 * @param {number} maxRecipients
 * @param {string} url
 */
export default async (envelopeId, orderId, selectors, recipients, maxRecipients, url) => {
    const {
        container: containerSelector,
        typesList: typesListSelector,
        description: descriptionSelector,
        datepicker: datepickerSelector,
        assigneesList: assigneesListSelector,
        expiresAt: expiresAtSelector,
        assigneesButton: assigneesButtonSelector,
        recipientsContainer: recipientsContainerSelector,
    } = selectors;
    const saveUrl = new URL(url);
    /** @type {HTMLElement} container */
    const container = document.querySelector(containerSelector);
    const templates = collectTemplates(container);
    const handler = new RecipientsHandler(
        new RecipientsRenderer(document.querySelector(recipientsContainerSelector), templates.recipientEntry || null),
        document.querySelector(assigneesButtonSelector),
        document.querySelector(assigneesListSelector),
        document.querySelector(typesListSelector),
        document.querySelector(expiresAtSelector),
        recipients,
        maxRecipients || null
    );

    await createDatepicker(datepickerSelector);

    addCounter(descriptionSelector);

    [
        "documents:process-envelope",
        "documents:process-envelope:add-recipient",
        "documents:process-envelope:remove-recipient",
        "documents:process-envelope:change-recipient-order",
        "documents:process-envelope:show-date-picker",
    ].forEach(e => EventHub.off(e));

    EventHub.on("documents:process-envelope", (e, form) => saveEnvelope(saveUrl, orderId, envelopeId, handler, maxRecipients || null, container, form));
    EventHub.on("documents:process-envelope:add-recipient", (e, button) => handler.addRecipient(button.closest("form")));
    EventHub.on("documents:process-envelope:remove-recipient", (e, button) => handler.removeRecipient(~~button.data("index")));
    EventHub.on("documents:process-envelope:show-date-picker", (e, button) => handler.removeRecipient(~~button.data("index")));
    EventHub.on("documents:process-envelope:change-recipient-order", (e, button) =>
        handler.offsetRecipient(~~button.data("index"), button.data("direction") || null)
    );
};

import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

/**
 * Send request to create envelope.
 *
 * @param {URL} url
 * @param {number} envelopeId
 * @param {HTMLElement} container
 * @param {JQuery} form
 */
const updateEnvelopeInfo = async (url, envelopeId, container, form) => {
    const entities = [{ name: "envelope", value: envelopeId }].filter(f => f.value);
    showLoader(container);
    form.find("button[type=submit]").addClass("disabled");

    // @ts-ignore
    return postRequest(url, entities.concat(form.serializeArray()), "json")
        .then(data => {
            systemMessages(data.message, data.mess_type);
            if (data.mess_type === "success") {
                closeFancyBox();

                EventHub.trigger("documents:envelope-info-updated", data.envelope || {});
            }
        })
        .catch(handleRequestError)
        .finally(() => {
            form.find("button[type=submit]").removeClass("disabled");
            hideLoader(container);
        });
};

/**
 * Fragment entrypoint.
 *
 * @param {number} envelopeId
 * @param {number} orderId
 * @param {{[x: string]: string }} selectors
 * @param {any} recipients
 * @param {string} saveUrl
 */
export default async (envelopeId, selectors, updateUrl) => {
    const { container: containerSelector } = selectors;
    /** @type {HTMLElement} container */
    const container = document.querySelector(containerSelector);

    EventHub.off("documents:update-envelope-info");
    EventHub.on("documents:update-envelope-info", (e, form) => updateEnvelopeInfo(new URL(updateUrl), envelopeId, container, form));
};

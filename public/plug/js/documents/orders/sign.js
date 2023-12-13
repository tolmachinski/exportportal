/* eslint-disable */
var SignEnvelopeModule = (function () {
    "use strict";

    /**
     * Send request to sign envelope.
     *
     * @param {URL} url
     * @param {number} envelopeId
     * @param {HTMLElement} container
     * @param {JQuery} form
     */
    function signEnvelope(url, envelopeId, container, form) {
        var entities = [{ name: "envelope", value: envelopeId }].filter(function (f) {
            return f.value;
        });
        showLoader(container);
        form.find("button[type=submit]").addClass("disabled");

        // @ts-ignore
        return postRequest(url, entities.concat(form.serializeArray()), "json")
            .then(function (data) {
                systemMessages(data.message, data.mess_type);
                if (data.mess_type === "success") {
                    closeFancyBox();
                    $(globalThis).trigger("documents:envelope-signed", data.envelope || {});
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                form.find("button[type=submit]").removeClass("disabled");
                hideLoader(container);
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {number} envelopeId
     * @param {{[x: string]: string }} selectors
     * @param {string} url
     */
    function entrypoint(envelopeId, selectors, sendUrl) {
        var containerSelector = selectors.container || null;
        /** @type {HTMLElement} container */
        var container = document.querySelector(containerSelector);

        mix(
            globalThis,
            {
                documentsOrdersSignFormCallBack: function (form) {
                    signEnvelope(new URL(sendUrl), envelopeId, container, form);
                },
            },
            false
        );
    }

    return { default: entrypoint };
})();

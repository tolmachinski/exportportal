/* eslint-disable */
var VoidEnvelopeModule = (function () {
    "use strict";

    /**
     * Send request to void envelope.
     *
     * @param {URL} url
     * @param {number} envelopeId
     * @param {HTMLElement} container
     * @param {JQuery} form
     */
    function voidEnvelope(url, envelopeId, container, form) {
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
                    $(globalThis).trigger("documents:envelope-voided", data.envelope || {});
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
        var reasonSelector = selectors.reason || null;
        var containerSelector = selectors.container || null;
        /** @type {HTMLElement} container */
        var container = document.querySelector(containerSelector);

        addCounter(reasonSelector);
        mix(
            globalThis,
            {
                documentsOrdersVoidFormCallBack: function (form) {
                    voidEnvelope(new URL(sendUrl), envelopeId, container, form);
                },
            },
            false
        );
    }

    return { default: entrypoint };
})();

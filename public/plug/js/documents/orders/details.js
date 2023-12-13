/* eslint-disable */
var EnvelopeDetailsModule = (function () {
    "use strict";
    /**
     * Sends envelope to the recipients.
     *
     * @param {number} envelopeId
     * @param {number} documentId
     * @param {string|URL} url
     */
    function downloadDocuments(envelopeId, documentId, url) {
        return postRequest(url, { envelope: envelopeId, document: documentId }, "json")
            .then(function (response) {
                var file = response.file || null;
                var message = response.message || null;
                var messageType = response.mess_type || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (!file) {
                    return;
                }

                saveAs(file.url, file.name);
            })
            .catch(function (e) {
                onRequestError(e);
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {number} envelopeId
     * @param {string} downloadDocumentUrl
     */
    function entrypoint(envelopeId, downloadDocumentUrl) {
        mix(
            globalThis,
            {
                downloadDocumentsFromDetails: function (button) {
                    downloadDocuments(envelopeId, button.data("document" || null), downloadDocumentUrl);
                },
            },
            false
        );
    }

    return { default: entrypoint };
})();

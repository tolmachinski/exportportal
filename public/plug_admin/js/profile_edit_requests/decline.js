var ProfileEditRequestDeclineModule = (function () {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {string} [declineUrl]
     * @property {string} [reasonField]
     * @property {string} [declineButton]
     * @property {string} [declineWrapper]
     */

    /**
     * Decline button handler.
     *
     * @param {string|URL} url
     * @param {JQuery} form
     * @param {JQuery} button
     *
     * @returns {Promise<void>}
     */
    function onDecline(url, form, button) {
        showLoader(form);
        button.prop("disabled", true).addClass("disabled");

        return postRequest(url, form.serializeArray(), "json")
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type);
                }

                if (response.mess_type === "success") {
                    $(globalThis).trigger("preview-profile-edit-request::declined");
                    closeFancyBox();
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                hideLoader(form);
                button.prop("disabled", false).removeClass("disabled");
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        var reasonField = $(params.reasonField || null);
        var declineButton = $(params.declineButton || null);
        var declineWrapper = $(params.declineWrapper || null);

        addCounter(reasonField);
        declineWrapper.data("callback", onDecline.bind(null, new URL(params.declineUrl || null), declineWrapper, declineButton));
    }

    return { default: entrypoint };
})();

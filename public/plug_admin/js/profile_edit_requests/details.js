var ProfileEditRequestDetailsModule = (function () {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {string} [acceptUrl]
     * @property {string} [acceptButton]
     * @property {string} [downloadButton]
     * @property {string} [detailsWrapper]
     */

    /**
     * Sends request to the server.
     *
     * @param {string|URL} [url]
     * @param {any} [data]
     * @param {JQuery|HTMLElement} [node]
     * @param {JQuery|HTMLElement} [button]
     *
     * @returns {Promise<any>}
     */
    function doSendRequest(url, data, wrapperNode, buttonNode) {
        var wrapper = $(wrapperNode || null);
        var button = $(buttonNode || null);
        if (!url) {
            return;
        }
        showLoader(wrapper);
        button.prop("disabled", true).addClass("disabled");

        return postRequest(url, data, "json")
            .then(function (response) {
                if (response.message) {
                    systemMessages(response.message, response.mess_type);
                }

                return response;
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                hideLoader(wrapper);
                button.prop("disabled", false).removeClass("disabled");
            });
    }

    /**
     * Accept button handler.
     *
     * @param {string|URL} url
     * @param {JQuery|HTMLElement} wrapper
     * @param {JQuery|HTMLElement} button
     *
     * @returns {Promise<void>}
     */
    function onAccept(url, wrapper, button) {
        return doSendRequest(url, null, wrapper, button).then(function (response) {
            if (!response) {
                return;
            }

            if (response.mess_type === "success") {
                $(globalThis).trigger("preview-profile-edit-request::accepted");
                closeFancyBox();
            }
        });
    }

    /**
     * Download button handler.
     *
     * @param {string|URL} url
     * @param {JQuery|HTMLElement} wrapper
     * @returns {Promise<void>}
     */
    function onDownload(wrapper) {
        return function () {
            var button = $(this);
            var url = button.data("url") || null;

            doSendRequest(url, null, wrapper, $(this)).then(function (response) {
                if (!response) {
                    return;
                }

                if (response.token && response.token.url) {
                    downloadFile(
                        response.token.url + (response.token.filename ? "?" + $.param({ name: response.token.filename }) : ""),
                        response.token.filename || response.token.name
                    );
                }
            });
        };
    }

    /**
     * Download original button handler.
     *
     * @param {string|URL} url
     * @param {JQuery|HTMLElement} wrapper
     * @returns {Promise<void>}
     */
    function onDownloadOriginal(wrapper) {
        return function () {
            var button = $(this);
            var url = button.data("url") || null;
            var userId = button.data("user") || null;
            var documentId = button.data("document") || null;

            doSendRequest(url, { document: documentId, user: userId }, wrapper, $(this)).then(function (response) {
                if (!response) {
                    return;
                }

                if (response.token && response.token.url) {
                    downloadFile(
                        response.token.url + (response.token.filename ? "?" + $.param({ name: response.token.filename }) : ""),
                        response.token.filename || response.token.name
                    );
                }
            });
        };
    }

    /**
     * Handles the tab toggle event.
     *
     * @param {JQuery.ClickEvent} e
     */
    function onToggleTab(e) {
        e.preventDefault();
        $(this).tab('show');
    }

    /**
     * Module entrypoint.
     *
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        var acceptButton = $(params.acceptButton || null);
        var downloadButton = $(params.downloadButton || null);
        var downloadOriginalButton = $(params.downloadOriginalButton || null);
        var detailsWrapper = $(params.detailsWrapper || null);

        acceptButton.on("click", onAccept.bind(null, new URL(params.acceptUrl || null), detailsWrapper, acceptButton));
        downloadButton.on("click", onDownload(detailsWrapper));
        downloadOriginalButton.on("click", onDownloadOriginal(detailsWrapper));
        detailsWrapper.find('a[data-toggle="tab"]').on('click', onToggleTab);
    }

    return { default: entrypoint };
})();

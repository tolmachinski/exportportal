var CommentModule = (function (global, $) {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {any} [links]
     * @property {any} [selectors]
     * @property {any} [author]
     */

    /**
     * @typedef {Object} CustomElements
     * @property {JQuery} form
     */

    /**
     * @typedef {Object} Selectors
     * @property {string} form
     */

    /**
     * @typedef {Object} Links
     * @property {string|URL} [add]
     */

    /**
     * @typedef {Object} Links
     * @property {string|URL} [edit]
     */

    /**
     * @typedef {Object} Author
     * @property {boolean} [authorized]
     */
    //#endregion Declarations

    //#region Variables
    /**
     * @type {CustomElements}
     */
    var defaultElements = { form: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null };

    /**
     * @type {Links}
     */
    var defaultLinks = {};

    /**
     * @type {Author}
     */
    var defaultAuthor = { authorized : false};

    var EventHub = $(global);
    //#endregion Variables

    //#region Actions


    /**
     * Save the comment.
     *
     * @param {jQuery} form
     * @param {String} url
     * @param {any} currentLocation
     *
     * @returns {Promise<void|boolean>}
     */
    function saveComment(form, url, is_autorized_author) {
        if (null === form || null === url) {
            return Promise.reject();
        }
        var submitButton = form.find('button[type=submit]');

        showLoader(form);
        submitButton.prop('disabled', true);

        var tokenPromise = Promise.resolve();

        if (!is_autorized_author) {
            tokenPromise = googleRecaptchaValidation(recaptcha_parameters, form);
        }

        return tokenPromise.then(function () {
            var onSave = function (response) {
                if (response.data) {
                    $('#js-count-comments').html(response.data.comment_counter);
                }
                if (response.message) {
                    open_result_modal({
                        content: response.message,
                        type: response.mess_type,
                        closable: true,
                        buttons: [{
                            label: translate_js({ plug: 'BootstrapDialog', text: 'close' }),
                            action: function(dialogRef){
                                dialogRef.close();
                            }
                        }]
                    });
                }

                return response || {};
            };

            return postRequest(url, form.serializeArray())
                .then(onSave)
                .catch(onRequestError)
                .finally(function () {
                    hideLoader(form);
                    submitButton.prop('disabled', false);
                });
        })
    }

    /**
     * Update the comment on the client.
     *
     * @param {jQuery} form
     *
     * @returns {void}
     */
    function updateComment(form) {
        var data = form.serializeArray();
        $('body').find('[data-comment-row="' + data[0].value + '"] .common-comments__message').text(data[1].value);
    }
    //#endregion Actions

    /**
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        /** @type {Links} links */
        var links = Object.assign({}, defaultLinks, params.links || {});
        /** @type {Selectors} selectors */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type {Author} author */
        var author = Object.assign({}, defaultAuthor, params.author || {});
        /** @type {CustomElements} elements */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));

        if (!elements.form) {
            throw new ReferenceError("The form is requred.");
        }
        if (!links.add && !links.edit) {
            throw new ReferenceError("The URL for saving comment must be defined.");
        }
        //#region Listeners
        //#endregion Listeners

        EventHub.off("comments:add");
        EventHub.on("comments:add", function (e, form) {
            if (form.get(0) !== elements.form.get(0)) {
                throw new ReferenceError(`The comments form is not found.`);
            }

            return saveComment(elements.form, links.add, author.authorized).then(function (result) {
                if (result) {
                    closeFancyboxPopup();
                    // Usign jQuery as EventHub
                    $(globalThis).trigger("comment:added", { comment: result.data.comment, data: result.data || {} });
                }
            });
        });

        EventHub.off("comments:edit");
        EventHub.on("comments:edit", function (e, form) {
            if (form.get(0) !== elements.form.get(0)) {
                throw new ReferenceError(`The comments form is not found.`);
            }

            updateComment(form);

            return saveComment(elements.form, links.edit, author.authorized).then(function (result) {
                if (result) {
                    closeFancyboxPopup();
                    // Usign jQuery as EventHub
                    $(globalThis).trigger("comment:edited", { comment: result.data.comment, data: result.data || {} });
                }
            });
        });
    }

    return {
        default: entrypoint,
    };
})(globalThis, jQuery);

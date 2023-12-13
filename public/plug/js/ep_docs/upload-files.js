/* eslint-disable vars-on-top, prefer-destructuring, prefer-template, no-var */
var UploadFilesModule = (function () {
    "use strict";

    /**
     * The emitter for JQuery element.
     *
     * @param {JQuery} element
     */
    function Emitter(element) {
        this.element = element;
    }
    Emitter.prototype.emit = function (name, args) {
        this.element.trigger("epd-uploader:" + name, args || []);
    };

    function UploadHandler(container, uploadButtonWrapper, loaderWrapper, templates, params, debug) {
        this.params = params || {};
        this.debug = Boolean(~~debug || null);
        this.group = params.group;
        this.isModal = params.isModal;
        this.filesAmount = params.filesAmount;
        this.filesAllowed = params.filesAllowed;
        this.currentAmount = 0;
        this.fileUploadMaxSize = String(params.fileUploadMaxSize);
        this.additionalPreviewClasses = params.additionalPreviewClasses;
        this.uploadButtonWrapper = uploadButtonWrapper;
        this.removeHandlerName = params.removeHandlerName;
        this.hiddenInputName = params.hiddenInputName;
        this.loaderWrapper = loaderWrapper;
        this.container = container;
        this.templates = templates;
        this.containerEmitter = new Emitter(this.container);
    }

    UploadHandler.prototype.getContainerEmitter = function () {
        return this.containerEmitter;
    };

    UploadHandler.prototype.addDocumentPreview = function (file) {
        var fileId = new Date().getTime();
        var imageContent = renderFilePreview(
            this.templates,
            {
                index: fileId,
                className: ["fileupload-image", this.additionalPreviewClasses]
                    .filter(function (f) {
                        return f;
                    })
                    .join(" "),
                icon: {
                    className: "icon-files-" + file.extension.toLowerCase(),
                },
            },
            {
                hiddenInput: {
                    name: this.hiddenInputName,
                    type: "hidden",
                    value: btoa(file.id),
                },
                deleteButton: {
                    group: this.group,
                    text: translate_js({ plug: "general_i18n", text: "form_button_delete_file_text" }),
                    title: translate_js({ plug: "general_i18n", text: "form_button_delete_file_title" }),
                    message: translate_js({ plug: "general_i18n", text: "form_button_delete_file_message" }),
                    callback: this.removeHandlerName,
                    className: "btn btn-dark confirm-dialog",
                },
            }
        );
        var preview = $(imageContent);
        this.container.append(preview);
        this.currentAmount++;

        return {
            emitter: new Emitter(preview),
            image: preview,
            file: file,
            id: fileId,
        };
    };
    UploadHandler.prototype.removeDocumentPreview = function (button) {
        button.closest(".item-wrapper").remove();
        this.currentAmount--;
    };
    UploadHandler.prototype.handleUploadError = function (error) {
        switch (error.type) {
            case "validation_error":
                var list = error.data || {};
                Object.keys(list).forEach(function (key) {
                    systemMessages(list[key], "error");
                });

                break;
            case "propagation_error":
                if (this.filesAmount > 0) {
                    systemMessages(translate_js({ plug: "fileUploader", text: "error_exceeded_limit_text" }).replace("[AMOUNT]", this.filesAmount), "warning");
                } else {
                    systemMessages(translate_js({ plug: "fileUploader", text: "error_no_more_files" }), "warning");
                }

                break;
            case "domain_error":
                if (this.debug) {
                    console.warn(error);
                }

                break;
            case "malware_error":
                systemMessages(translate_js({ plug: "fileUploader", text: "error_malware_text" }), "error");
                if (this.debug) {
                    console.error(error);
                }

                break;

            default:
                systemMessages(translate_js({ plug: "fileUploader", text: "error_default" }), "error");
                if (this.debug) {
                    console.error(error);
                }

                break;
        }
    };
    UploadHandler.prototype.showUploadLoader = function () {
        this.loaderWrapper.show();
    };
    UploadHandler.prototype.hideUploadLoader = function () {
        this.loaderWrapper.hide();
    };
    UploadHandler.prototype.showUploadButton = function () {
        this.uploadButtonWrapper.show();
    };
    UploadHandler.prototype.hideUploadButton = function () {
        this.uploadButtonWrapper.hide();
    };

    /**
     * Renders preview for file.
     *
     * @param {any} templates
     * @param {any} context
     * @param {any} embedded
     *
     * @returns {string}
     */
    function renderFilePreview(templates, context, embedded) {
        context = context || {};
        embedded = embedded || {};

        var partials = {};
        Object.keys(embedded).forEach(function (key) {
            if (!Object.prototype.hasOwnProperty.call(embedded, key) || !Object.prototype.hasOwnProperty.call(templates.preview.children, key)) {
                return;
            }

            partials[key] = { template: templates.preview.children[key], context: embedded[key] };
        });

        return renderTemplate(templates.preview.main, context, partials);
    }

    /**
     * Validation handler.
     *
     * @param {UploadHandler} handler
     *
     * @returns {boolean}
     */
    function onUploadValidate(handler) {
        handler.getContainerEmitter().emit("validate", [handler.currentAmount < handler.filesAllowed, handler.currentAmount, handler.filesAllowed]);

        return handler.currentAmount < handler.filesAllowed;
    }

    /**
     * File upload listener.
     *
     * @param {UploadHandler} handler
     * @param {any} file
     */
    function onUpload(handler, file) {
        var context = handler.addDocumentPreview(file);
        context.emitter.emit("upload", [context.id, context.file, handler.container.find(".item-wrapper").toArray()]);
        handler.hideUploadLoader();
        if (onUploadValidate(handler)) {
            handler.showUploadButton();
        }
        if (handler.isModal) {
            $.fancybox.update();
        }
    }

    /**
     * Plugin start handler
     *
     * @param {UploadHandler} handler
     */
    function onUploadStart(handler) {
        handler.getContainerEmitter().emit("start");
        handler.showUploadLoader();
        handler.hideUploadButton();
    }

    /**
     * Error handler
     *
     * @param {UploadHandler} handler
     * @param {any} error
     */
    function onUploadError(handler, error) {
        handler.handleUploadError(error);
        handler.getContainerEmitter().emit("error", [error, handler.container.find(".item-wrapper").toArray()]);
        handler.hideUploadLoader();
        handler.showUploadButton();
    }

    /**
     * Document delete handler.
     *
     * @param {UploadHandler} handler
     * @param {JQuery} button
     */
    function onDeleteDocument(handler, button) {
        if ((button.data("group") ?? null) !== handler.group) {
            return;
        }

        handler.removeDocumentPreview(button);
        handler.getContainerEmitter().emit("delete", [button, button.closest(".item-wrapper"), handler.container.find(".item-wrapper").toArray()]);
        if (onUploadValidate(handler)) {
            handler.showUploadButton();
        }
        if (handler.isModal) {
            $.fancybox.update();
        }
    }

    /**
     * @param {string} type
     * @param {string} uploadButtonWrapperId
     * @param {UploadHandler} handler
     */
    function attachUploader(type, uploadButtonWrapperId, handler) {
        globalThis.EPDocs[type](uploadButtonWrapperId, {
            start: onUploadStart.bind(null, handler),
            error: onUploadError.bind(null, handler),
            upload: onUpload.bind(null, handler),
            validate: onUploadValidate.bind(null, handler),
            maxFileSize: handler.fileUploadMaxSize,
        });
    }

    function defaultFunction(params) {
        var type = params.type;
        var scriptUrl = params.scriptUrl;
        var canUpload = params.canUpload;
        var removeHandlerName = params.removeHandlerName;
        var uploadButtonWrapperId = "#" + params.group + "--formfield--image";
        var uploadButtonWrapper = $(uploadButtonWrapperId);
        var loaderWrapper = $("#" + params.group + "--formfield--loader");
        var container = $("#" + params.group + "--formfield--image-container");
        var templatesData = params.templatesData || { mainId: null, hiddenInputId: null, deleteButtonId: null };
        var templates = {
            preview: {
                main: $(templatesData.mainId).text() || "",
                children: {
                    hiddenInput: $(templatesData.hiddenInputId).text() || "",
                    deleteButton: $(templatesData.deleteButtonId).text() || "",
                },
            },
        };
        var handler = new UploadHandler(container, uploadButtonWrapper, loaderWrapper, templates, params, __debug_mode);

        if (canUpload) {
            if (typeof globalThis.EPDocs !== "undefined" && globalThis.EPDocs) {
                attachUploader(type, uploadButtonWrapperId, handler);
            } else {
                showLoader(container, "");
                getScript(scriptUrl, true)
                    .then(function () {
                        attachUploader(type, uploadButtonWrapperId, handler);
                    })
                    .finally(function () {
                        hideLoader(container);
                    });
            }
        }

        if (handler.currentAmount >= handler.filesAllowed) {
            handler.hideUploadButton();
        }

        window[removeHandlerName] = onDeleteDocument.bind(null, handler);
    }

    return {
        default: defaultFunction,
    };
})();

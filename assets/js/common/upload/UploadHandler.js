import $ from "jquery";

import { translate } from "@src/i18n";
import { renderTemplate } from "@src/util/templates";
import { systemMessages } from "@src/util/system-messages/index";
import Emitter from "@src/common/upload/Emitter";

/**
 * Renders preview for file.
 *
 * @param {any} templates
 * @param {any} context
 * @param {any} embedded
 *
 * @returns {string}
 */
const renderFilePreview = (templates, context = {}, embedded = {}) => {
    const partials = {};
    Object.keys(embedded).forEach(key => {
        if (!Object.prototype.hasOwnProperty.call(embedded, key) || !Object.prototype.hasOwnProperty.call(templates.preview.children, key)) {
            return;
        }

        partials[key] = { template: templates.preview.children[key], context: embedded[key] };
    });

    return renderTemplate(templates.preview.main, context, partials);
};

export default class UploadHandler {
    constructor(container, uploadButtonWrapper, loaderWrapper, templates, params, debug) {
        this.params = params || {};
        this.debug = Boolean(~~debug || null);
        this.isModal = params.isModal;
        this.group = params.group;
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

    getContainerEmitter() {
        return this.containerEmitter;
    }

    addDocumentPreview(file) {
        const fileId = new Date().getTime();
        const imageContent = renderFilePreview(
            this.templates,
            {
                index: fileId,
                className: ["fileupload-image", this.additionalPreviewClasses].filter(f => f).join(" "),
                icon: {
                    className: `icon-files-${file.extension.toLowerCase()}`,
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
                    text: translate({ plug: "general_i18n", text: "form_button_delete_file_text" }),
                    title: translate({ plug: "general_i18n", text: "form_button_delete_file_title" }),
                    message: translate({ plug: "general_i18n", text: "form_button_delete_file_message" }),
                    callback: "",
                    className: "btn btn-dark js-confirm-dialog confirm-dialog",
                },
            }
        );

        const preview = $(imageContent);
        this.container.append(preview);
        this.currentAmount += 1;

        return {
            emitter: new Emitter(preview),
            image: preview,
            file,
            id: fileId,
        };
    }

    removeDocumentPreview(button) {
        button.closest(".item-wrapper").remove();
        this.currentAmount -= 1;
    }

    handleUploadError(error) {
        const list = error.data || {};
        switch (error.type) {
            case "validation_error":
                Object.keys(list).forEach(key => {
                    systemMessages(list[key], "error");
                });

                break;
            case "propagation_error":
                if (this.filesAmount > 0) {
                    systemMessages(
                        translate({ plug: "general_i18n", text: "fileuploader_error_exceeded_limit_text" }).toString().replace("[AMOUNT]", this.filesAmount),
                        "warning"
                    );
                } else {
                    systemMessages(translate({ plug: "general_i18n", text: "fileuploader_error_no_more_files" }).toString(), "warning");
                }

                break;
            case "malware_error":
                systemMessages(translate({ plug: "general_i18n", text: "fileuploader_error_malware_text" }), "error");
                if (this.debug) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }

                break;
            case "domain_error":
                if (this.debug) {
                    // eslint-disable-next-line no-console
                    console.warn(error);
                }

                break;

            default:
                systemMessages(translate({ plug: "general_i18n", text: "fileuploader_error_default" }).toString(), "error");
                if (this.debug) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }

                break;
        }
    }

    showUploadLoader() {
        this.loaderWrapper.show();
    }

    hideUploadLoader() {
        this.loaderWrapper.hide();
    }

    showUploadButton() {
        this.uploadButtonWrapper.show();
    }

    hideUploadButton() {
        this.uploadButtonWrapper.hide();
    }
}

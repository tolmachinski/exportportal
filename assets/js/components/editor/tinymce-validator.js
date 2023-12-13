import $ from "jquery";
import { validateElement } from "@src/plugins/validation-engine/index";
import showPrompt from "@src/util/dom/show-validation-prompt";

/**
 * It takes a container element and validates all the form elements within it
 * @param {JQuery} container
 */
const reValidate = async container => {
    await validateElement(container);
};

/**
 * It adds a validation class to the editor container, sets the validation hook type, and adds a blur
 * event listener to the editor container
 * @param {any} params - {validate, valHook}
 * @param editor - The TinyMCE editor instance
 */
const initTinymceValidator = async function ({ validate = "validate[required, maxSize[20000]]", valHook = undefined }, editor) {
    if (typeof $.fn.validationEngine === "undefined") {
        await import("@src/plugins/validation-engine/index").then(async ({ enableFormValidation }) => {
            await enableFormValidation(editor.formElement, {});
        });
    }

    const container = $(editor.editorContainer);
    container
        .addClass(validate)
        // @ts-ignore
        .setValHookType(valHook)
        .on("blur", () => {
            reValidate(container);
        });

    editor.on("blur", () => {
        reValidate(container);
    });

    const containerId = container.attr("id");
    editor.on("click", async function checkValidation() {
        if (this.getContent() === "" && container.siblings(`.${containerId}formError`).length) {
            await reValidate(container);
            showPrompt(container, containerId);
        }
    });
};

export default initTinymceValidator;

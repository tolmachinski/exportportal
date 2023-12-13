import $ from "jquery";
import inputmask from "inputmask";

import lazyLoadSelect from "@src/plugins/select2/lazy-load";
import makePhoneCodesList from "@src/components/phone/phone-codes-list";
import normalizeDomBoolean from "@src/util/dom/normalize-dom-boolean";
import EventHub from "@src/event-hub";

/**
 * Normalizes the phone mask value.
 *
 * @param {string} phoneMask
 * @returns {string}
 */
function normalizePhoneMask(phoneMask) {
    return phoneMask.replace(/_/g, "9").replace(/\*/g, "a");
}

/**
 * The handler for the number field 'paste' event.
 */
function pasteHandler() {
    const node = $(this);
    const mask = node.data("mask");
    if (!mask) {
        return;
    }

    node.data("maskIsCompleted", mask.isComplete());
}

/**
 * Ensures that the slelect2 is present.
 */
async function ensureSelectPlugin() {
    // The select2 is already defined, so we will leave.
    if ($.fn.select2) {
        return;
    }

    // Otherwise, let's load the of the plugin.
    await import("select2");
}

/**
 * Attach input mask to the element.
 *
 * @param {JQuery} element
 * @param {string} phoneMask
 */
function attachInputmask(element, phoneMask) {
    const oldMask = element.data("mask");
    if (oldMask) {
        oldMask.remove();
    }

    const newMask = inputmask({
        // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
        mask: normalizePhoneMask(phoneMask),
        keepStatic: true,
        oncomplete: () => element.data("maskIsCompleted", true),
        onincomplete: () => element.data("maskIsCompleted", false),
    });
    newMask.mask(element.get(0));
    element.data("mask", newMask);
    element.data("currentMask", phoneMask);
    element.data("maskIsCompleted", false);
    // Reset old handlers
    element.off("paste", pasteHandler);
    element.on("paste", pasteHandler);

    return newMask;
}

/**
 * Decorated initialization of the combintation of select2 + inputmask.
 *
 * @param {JQuery} node
 * @param {string} numberInputSelector
 * @param {string} parentSelector
 */
function makeWrappedPhoneCodesList(node, numberInputSelector, parentSelector) {
    makePhoneCodesList(node, node.closest(parentSelector));
    const overrideLocation = normalizeDomBoolean(node.data("overrideLocation") ?? null);
    node.on("change", function onChange() {
        const selected = $(this).find("option:selected");
        const phoneMask = selected.data("phoneMask") || null;
        const numberInput = $(numberInputSelector);

        // If the field with phone number exists we need to re-initialize the mask
        if (numberInput.length) {
            let isSelected = false;
            let isCompleted = false;
            if (selected.length) {
                isSelected = true;
            } else {
                isSelected = false;
            }

            if (numberInput.val() === "") {
                isCompleted = false;
            }

            numberInput.data("maskIsSelected", isSelected);
            numberInput.data("maskIsCompleted", isCompleted);
            if (numberInput.hasClass("validengine-border")) {
                numberInput.removeClass("validengine-border").prev(".formError").remove();
            }

            if (phoneMask) {
                attachInputmask(numberInput, phoneMask);
            }
        }

        // If location override flag is set we need to trigger override event
        if (overrideLocation) {
            const country = selected.data("country");
            setTimeout(() => {
                EventHub.trigger("locations:inline:override-coutry", { country });
                // Trigger event to preserve backward compatibility
                $(globalThis).trigger("locations:inline:override-coutry", { country });
            }, 500);
        }
    });
}

/**
 * Initializes the plugins for the list of plugins.
 *
 * @param {string|HTMLElement|JQuery<any>} selector
 */
export default function initializeBlock(selector) {
    const wrapperList = $(selector);
    if (!wrapperList.length) {
        return;
    }

    $(document).on("select2:open", () => {
        document.querySelector(".select2-search__field").focus();
    });

    wrapperList.toArray().forEach(async element => {
        const wrapper = $(element);
        const { listField, numberField, lazyPlaceholder, lazy, parent } = wrapper.data();

        if (!listField) {
            throw new ReferenceError("The selector for field is required.");
        }
        if (!numberField) {
            throw new ReferenceError("The selector for number is required.");
        }

        // If the block in lazy mode, then we need to process it in a
        // different way
        if (normalizeDomBoolean(lazy)) {
            if (!lazyPlaceholder) {
                throw new ReferenceError("In lazy mode selector for lazy placeholder is required.");
            }
            const placeholder = $(lazyPlaceholder);
            if (!placeholder.length) {
                throw new ReferenceError("The DOM element for placeholder is not found.");
            }
            // Given the way lazy loading for select2 works, we need to add `data-lazy-target` attribute
            // to the placeholder element.
            placeholder.data("lazyTarget", listField);
            // And finally, let's initialize the select2 in lazy mode
            lazyLoadSelect(lazyPlaceholder, (/** @type {JQuery} */ node) => makeWrappedPhoneCodesList(node, numberField, parent));
        } else {
            // Otherwise, we will initialize it right now
            const list = $(listField);
            if (!list.length) {
                throw new ReferenceError("The DOM element for list is not found.");
            }

            await ensureSelectPlugin();
            makeWrappedPhoneCodesList(list, numberField, parent);
        }

        // If we have the number field in place, we need to initialize
        // the phone mask for it.
        const numberInput = $(numberField);
        const phoneMask = numberInput.data("currentMask") ?? null;
        if (numberInput.length && phoneMask) {
            attachInputmask(numberInput, phoneMask);
        }
    });
}

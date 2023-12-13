import $ from "jquery";
import { dispatchEvent } from "@src/util/events";

const lazyV = {};

/**
 *
 * @param {JQuery.TriggeredEvent} e
 */
const loadingValidationEngine = async e => {
    if (e.target.type !== "checkbox" && e.target.type !== "file") {
        e.preventDefault();
    }

    if (lazyV.wasFocusOut !== 0 && e.type === "focusout") {
        lazyV.wasFocusOut = 1;
    }

    import(/* webpackChunkName: "validation-engine-index" */ "@src/plugins/validation-engine/index").then(async ({ enableFormValidation }) => {
        await enableFormValidation($(e.target).closest("form"));

        if (!lazyV.isDispatched) {
            setTimeout(() => {
                dispatchEvent(
                    e.target.type === "file" ||
                        (e.type === "focusout" && !$(e.target).attr("id")?.includes("_tag")) ||
                        (e.type === "click" && lazyV.wasFocusOut)
                        ? "blur"
                        : e.type,
                    e.target
                );
            }, 0);
            lazyV.wasFocusOut = 0;
            lazyV.isDispatched = 1;
            setTimeout(() => delete lazyV.isDispatched, 500);
        }
    });
};

export default loadingValidationEngine;

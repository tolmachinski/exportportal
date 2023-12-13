import $ from "jquery";
import { dispatchEvent } from "@src/util/events";

const lazyV = {};

/**
 *
 * @param {JQuery.TriggeredEvent} e
 * @param {String} selector
 * @param {any} [options]
 */
const loadingJqueryValidation = async (e, selector, submitCallBack, options) => {
    if (e.target.type !== "checkbox") {
        e.preventDefault();
    }

    if (lazyV.wasFocusOut !== 0 && e.type === "focusout") {
        lazyV.wasFocusOut = 1;
    }

    import(/* webpackChunkName: "jquery-validator-index" */ "@src/plugins/jquery-validation/index").then(async ({ enableFormValidation }) => {
        await enableFormValidation(selector, submitCallBack, options);

        if (!lazyV.isDispatched) {
            setTimeout(() => dispatchEvent(e.type === "focusout" || (e.type === "click" && lazyV.wasFocusOut) ? "blur" : e.type, e.target), 0);
            lazyV.wasFocusOut = 0;
            lazyV.isDispatched = 1;
            setTimeout(() => delete lazyV.isDispatched, 500);
        }
    });

    return false;
};

/**
 * @param {String} formSelector
 * @param {any} [options]
 */
export default (formSelector, submitCallBack, options) => {
    $(document).on("submit", formSelector, e => loadingJqueryValidation(e, formSelector, submitCallBack, options));
    $(document).on("click focusout", `${formSelector} input,textarea`, e => loadingJqueryValidation(e, formSelector, submitCallBack, options));
};

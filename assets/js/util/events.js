import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
import callFunction from "@src/util/common/call-function";
import EventHub from "@src/event-hub";

const requireLoggedSystmess = function (e) {
    e.preventDefault();
    systemMessages(translate({ plug: "general_i18n", text: "systmess_error_should_be_logged_in" }), "info");

    return false;
};

/**
 * Returns the handler for event with prevented deafult action.
 *
 * @param {Function} fn
 *
 * @returns {Function}
 */
const preventDefault = fn =>
    // eslint-disable-next-line func-names
    function (e, ...args) {
        e.preventDefault();

        return fn.call(this, e, ...args);
    };

/**
 * Handles click callback invocation.
 *
 * @param {Event|JQuery.Event} e
 *
 * @returns {boolean}
 */
const invokeCallback = function (e) {
    e.preventDefault();
    const node = $(this);
    const callBack = node.data("callback");
    callFunction(callBack, node);

    return false;
};

/**
 * Handles stopped event.
 *
 * @param {Event|JQuery.Event} e
 *
 * @returns {boolean}
 */
const stopInvocation = function (e) {
    e.preventDefault();

    return false;
};

/**
 * Dispatch event from EventHub
 *
 * @param {Event|JQuery.Event} e
 */
const dispatchHubEvent = function (e) {
    e.preventDefault();
    const node = $(this);
    const action = node.data("jsAction") ?? null;
    const params = node.data("jsActionParams") ?? null;

    if (action) {
        EventHub.trigger(action, [node, params, e]);
    }
};

const dispatchEvent = (type, element, params = {}) => {
    let event;
    const eventParams = { bubbles: true, cancelable: true, detail: undefined, ...params };

    try {
        event = new CustomEvent(type, eventParams);
    } catch (error) {
        event = document.createEvent("CustomEvent");
        event.initCustomEvent(type, eventParams.bubbles, eventParams.cancelable, eventParams.detail);
    }

    return element.dispatchEvent(event);
};

export { requireLoggedSystmess, preventDefault, invokeCallback, stopInvocation, dispatchHubEvent, dispatchEvent };

import $ from "jquery";

const fragment = document.createElement("div");
const EventHub = $(fragment);

/**
 * Removes listeners by names.
 *
 * @param {string|string[]} listener
 * @param  {...string} args
 */
const removeListeners = (listener, ...args) => {
    const listeners = [].concat(typeof listener === "string" ? [listener] : listener).concat(args);

    listeners.forEach(name => {
        EventHub.off(name);
    });
};

export default EventHub;
export { removeListeners };

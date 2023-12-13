import $ from "jquery";
/**
 * Shows loader
 *
 * @param {HTMLElement|JQuery} selector
 * @param {string} [text]
 * @param {string} [position]
 * @param {number|string} [zIndex]
 */
const showLoader = function (selector, text, position = "absolute", zIndex = 0) {
    const txt = text === "default" || text === undefined ? "Sending..." : text;
    const wrapper = $(selector);
    const positionWrapper = wrapper.css("position");
    const zIndexStyle = zIndex > 0 ? `style="z-index: ${zIndex}"` : "";
    const template = `
        <div class="ajax-loader ajax-loader__${position}" ${zIndexStyle}>
            <i class="ajax-loader__icon"></i>
            <span class="ajax-loader__text">${txt}</span>
        </div>
    `;
    let loader = wrapper.children(".ajax-loader");

    if (positionWrapper === "static") {
        wrapper.addClass("relative-b");
    }

    if (position === "fixed") {
        $("html").addClass("ajax-loader-lock");
    }

    if (loader.length === 0) {
        loader = $(template);
        wrapper.prepend(loader);
    }

    loader.css({ display: "flex" });
};

/**
 * Hides loader.
 *
 * @param {HTMLElement|JQuery} selector
 */
const hideLoader = function (selector) {
    const wrapper = $(selector);
    const loader = wrapper.children(".ajax-loader");
    $("html").removeClass("ajax-loader-lock");

    if (loader.length > 0) {
        loader.hide();
    }
};

export { showLoader, hideLoader };

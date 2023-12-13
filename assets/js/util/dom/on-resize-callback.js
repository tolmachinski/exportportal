import $ from "jquery";

const onResizeCallback = (callback, selector, namespace) => {
    const node = selector || globalThis;

    let resizeTimeout;
    const listener = namespace ? `resize.${namespace}` : "resize";

    $(node).on(listener, () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            callback();
        }, 300);
    });
};

export default onResizeCallback;

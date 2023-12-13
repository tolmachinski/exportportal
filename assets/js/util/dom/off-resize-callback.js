import $ from "jquery";

const offResizeCallback = (selector, namespace) => {
    const node = selector || globalThis;
    const listener = namespace ? `resize.${namespace}` : "resize";

    $(node).off(listener);
};

export default offResizeCallback;

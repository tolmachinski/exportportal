import $ from "jquery";

let isInitialized = false;

const initialize = async () => {
    if (!isInitialized) {
        const multipleSelect = await import("@src/plugins/multiple-select/multiple-select");
        const factory = multipleSelect.default;

        factory();
        isInitialized = true;
    }
};

const addMultipleSelect = async (selector, options = {}) => {
    await initialize();

    const elements = $(selector);
    elements.toArray().forEach(e => {
        const that = $(e);

        // @ts-ignore
        that.multipleEpSelect.call(that, { ...(options || {}) });
    });

    return elements;
};

export default addMultipleSelect;
export { addMultipleSelect };

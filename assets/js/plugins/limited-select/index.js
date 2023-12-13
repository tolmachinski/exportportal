let isInitialized = false;

const initialize = async () => {
    if (!isInitialized) {
        const limitedSelect = await import("@src/plugins/limited-select/limited-select");
        const factory = limitedSelect.default;

        factory();
        isInitialized = true;
    }
};

const addLimitedSelect = async (selector, options = {}) => {
    await initialize();

    const elements = $(selector);
    elements.toArray().forEach(e => {
        const that = $(e);

        that["limitedSelect"].call(that, Object.assign({}, options || {}));
    });

    return elements;
};

export default addLimitedSelect;
export { addLimitedSelect };

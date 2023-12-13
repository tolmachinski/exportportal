/**
 * It shows the validation engine error box
 * @param {JQuery} container - The container that the prompt is attached to.
 * @param {string} containerId - The id of the container that the prompt is attached to.
 */
const showPrompt = (container, containerId) => {
    const errorBox = container.siblings(`.${containerId}formError`);

    if (errorBox.length) {
        errorBox.show();
        errorBox.css("opacity", 1);
    }
};

export default showPrompt;

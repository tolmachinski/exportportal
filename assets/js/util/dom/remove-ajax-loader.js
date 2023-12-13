import delay from "@src/util/async/delay";
/**
 * It removes the ajax loader from the parent tag and then calls the fadeOutCallback function
 * @param {JQuery} parentTag - The parent tag that the loader will be appended to.
 * @callback fadeOutCallback - This is a function that will be called after the loader has been removed.
 */
const removeAjaxLoader = async (parentTag, fadeOutCallback = null) => {
    const loader = parentTag.find(".js-ajax-loader");
    await delay(200);

    loader.fadeOut(200, () => {
        loader.remove();

        if (fadeOutCallback && typeof fadeOutCallback === "function") {
            fadeOutCallback();
        }
    });
};

export default removeAjaxLoader;

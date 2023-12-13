import postRequest from "@src/util/http/post-request";

/**
 * It makes a request to the server to get the items for the slider, and if it gets them, it adds them
 * to the slider
 * @param {JQuery} slider - the slider element
 * @param {JQuery} sliderParent - The parent element of the slider.
 * @param {string} url - The URL to send the POST request to.
 * @returns The number of items.
 */
const getItemsForSlider = async (slider, sliderParent, url, data = {}) => {
    try {
        const { items, itemsCount = 0 } = await postRequest(url, data);

        if (!items || Array.isArray(items)) {
            sliderParent.remove();
            return false;
        }

        slider.prepend(items);

        return itemsCount ?? true;
    } catch (error) {
        sliderParent.remove();

        return false;
    }
};

export default getItemsForSlider;

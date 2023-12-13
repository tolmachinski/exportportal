/**
 * Transforms the blob object into data URL string.
 *
 * @param {Blob} blob
 * @returns {Promise<string>}
 */
const blobToDataUrl = async blob => {
    return new Promise(resolve => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result.toString());
        reader.readAsDataURL(blob);
    });
};

export default blobToDataUrl;

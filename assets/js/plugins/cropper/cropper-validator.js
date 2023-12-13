import $ from "jquery";
/**
 * It checks if the image is not the default image and if it's not, it appends a hidden input to the
 * form
 * @param {JQuery} imageWr - the wrapper of the image
 */
const checkIfExistRealImage = async imageWr => {
    const image = imageWr.find(".image").attr("src");

    if (image.match(/noimage/g) === null && image.match(/no-image-other.svg/g) === null && image.match(/main-image.svg/g) === null) {
        imageWr.append(`<input type="hidden" name="cropper_image_validate" value="${image}">`);
    }
};

/**
 * It adds a validation rule to the button that opens the cropper
 * @param {JQuery} btn - the button that will be validated
 * @param {JQuery} imageWr - the wrapper of the image
 * @returns The imageWr.find('input[type="hidden"]').length ? 1 : "";
 */
const initCropperValidator = async (btn, imageWr) => {
    await checkIfExistRealImage(imageWr);
    // @ts-ignore
    btn.addClass("validate[required]").setValHookType("cropperImage");
    $.valHooks.cropperImage = {
        get() {
            return imageWr.find('input[type="hidden"]').length ? 1 : "";
        },
    };
};

export default initCropperValidator;

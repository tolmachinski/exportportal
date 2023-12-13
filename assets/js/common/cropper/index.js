import $ from "jquery";

export default params => {
    $("#js-upload-file-crop").on("change", async function onInputFileLoaded() {
        const { default: editProfileCropper } = await import("@src/common/cropper/cropper");
        editProfileCropper(this, params);
    });
};

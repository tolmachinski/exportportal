import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import blobToDataUrl from "@src/util/dom/blob-to-data-url";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

let moduleData = null;
const onCropImage = async (cropper, imgOriginalName) => {
    const { imgValidationWidth, imgValidationHeight, fileUploadUrl, inputName = "logo" } = moduleData;
    const popup = $("#js-popup-croppper-wr");
    const cropParams = { type: "blob", size: { width: imgValidationWidth, height: imgValidationHeight } };
    const imageData = await cropper.result(cropParams);
    const extension = imgOriginalName.split(".").pop();
    const checkbox = $(".js-certified-checkbox");
    if (checkbox.length && checkbox.prop( "checked")) {
        checkbox.prop( "checked", false );
    }
    showLoader(popup);

    try {
        const formData = new FormData();
        formData.append("files", imageData, imgOriginalName.replace(new RegExp(`^(.+).${extension}$`), "$1.png"));
        const {
            image: { path },
            message,
            mess_type: messageType,
        } = await postRequest(fileUploadUrl, formData, "json", { processData: false, contentType: false });
        if (messageType !== "success") {
            systemMessages(message, messageType);

            return;
        }

        const mainImg = $("#js-view-main-photo");
        mainImg.find(".image").attr("src", await blobToDataUrl(imageData));
        mainImg.find('input[type="hidden"]').remove();
        $(".js-fileinput-button")?.removeClass("validengine-border").prev(".formError").remove();
        mainImg.append(`<input type="hidden" name="${inputName}" value="${path}">`);

        const { closeAllDialogs } = await import("@src/plugins/bootstrap-dialog/index");
        closeAllDialogs();
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(popup);
    }
};

const openCropperDialog = async (progressEvent, imgOriginalName) => {
    const { default: cropperDialog } = await import("@src/components/popups/cropper_popup/index");
    const cropper = await cropperDialog(progressEvent, moduleData);
    EventHub.off("crop-image:init");
    EventHub.on("crop-image:init", () => onCropImage(cropper, imgOriginalName));
};

const getFileSize = (progressEvent, imgOriginalName) => {
    const { imgValidationWidth, imgValidationHeight, imageValidationError } = moduleData;
    const image = new Image();
    image.src = progressEvent.target.result;
    image.onload = ({ target }) => {
        if (imgValidationWidth <= target.width && imgValidationHeight <= target.height) {
            openCropperDialog(progressEvent, imgOriginalName);
        } else {
            systemMessages(imageValidationError, "error");
        }
    };
};

const readFile = input => {
    const { rulesSize, rulesFormat, extensions } = moduleData;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const imgOriginalName = input.files[0].name || "avatar.png";
        if (input.files[0].size > parseInt(rulesSize, 10)) {
            systemMessages("The maximum file size was exceeded.", "error");

            return;
        }

        if (!extensions.includes(input.files[0].type) || input.files[0].type === "") {
            systemMessages(`Invalid file format. List of supported formats (${rulesFormat})`, "error");

            return;
        }

        reader.onload = function onload(progressEvent) {
            getFileSize(progressEvent, imgOriginalName);
        };
        reader.readAsDataURL(input.files[0]);
        $(input).val("");
    } else {
        systemMessages(input.files[0].error, "error");
    }
};

export default (input, params) => {
    if (!moduleData) {
        moduleData = {
            ...params,
        };
    }

    readFile(input);
};

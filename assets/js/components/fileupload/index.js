import $ from "jquery";
import { translate } from "@src/i18n";
import { initialize } from "@src/plugins/fileupload/index";
import { systemMessages } from "@src/util/system-messages/index";
import EventHub from "@src/event-hub";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import templateFileUpload from "@src/components/fileupload/template-file-upload";

let maxNumberOfFiles = 10;
let allowedFiles = 10;
let uploadBtn = null;
let loader = null;
let removeUrl = null;
let imageWrapper = null;

/**
 * If the user tries to upload more files than allowed, show an error message and abort the upload
 * @param e - The event object
 * @param files - The files that are being uploaded.
 */
const onUploadStart = (e, { files }) => {
    if (files.length <= allowedFiles) {
        loader.fadeIn();

        return true;
    }

    if (allowedFiles > 0) {
        systemMessages(
            translate({
                plug: "general_i18n",
                text: "fileuploader_error_exceeded_limit_text",
            }).replace("[AMOUNT]", maxNumberOfFiles),
            "warning"
        );
    } else {
        systemMessages(
            translate({
                plug: "general_i18n",
                text: "fileuploader_error_no_more_files",
            }),
            "warning"
        );
    }

    loader.fadeOut();
    e.abort();
    return false;
};

/**
 * If the file upload fails, display an error message
 * @param _e - The event object
 * @param data - The data object returned from the server.
 */
const onUploadFinished = (_e, data) => {
    if (!data.files.error) {
        return false;
    }

    systemMessages(data.files[0].error, "error");

    return true;
};

/**
 * It adds an image to the DOM.
 * @param file - The data returned from the server for file.
 */
const addImage = file => {
    const { idPhoto, fullPath, name, path } = file;
    allowedFiles -= 1;

    /* Creating a template for the image. */
    const imageContent = $(
        templateFileUpload({
            className: "fileupload-image",
            type: "imgnolink",
            index: idPhoto || name,
            image: `<img class="image" src="${fullPath}">`,
            imageLink: fullPath,
        })
    );

    imageContent.find(".js-fileupload-image").append(`<input type="hidden" name="images[]" data-name="${name}" value="${path}">`);
    imageContent.find(".js-fileupload-actions").append(`
        <button
            class="btn btn-light pl-10 pr-10 w-40 call-action"
            data-file="${idPhoto || name}"
            data-action="${removeUrl}"
            data-name="${name}"
            data-message="${translate({ plug: "general_i18n", text: "form_button_delete_file_message" })}"
            data-js-action="fileupload:remove-item-image"
        >
            <i class="ep-icon ep-icon_trash-stroke fs-17"></i>
        </button>
    `);

    imageWrapper.append(imageContent);
    uploadBtn.removeClass("validengine-border").prev(".formError").remove();
};

/**
 * It takes the response from the server, checks if it's a success, and if it is, it adds the image to
 * the page
 * @param _e - The event object
 * @param data - The data returned from the server.
 */
const onUploadDone = (_e, data) => {
    const { mess_type: messType, message, files } = data.result;

    if (messType === "success") {
        if (files && Array.isArray(files)) {
            files.forEach(addImage);
        } else {
            addImage(files);
        }
    } else {
        systemMessages(message, messType);
    }

    loader.fadeOut();
};

/**
 * It fades out the loader and displays a system message.
 */
const onUploadFail = () => {
    loader.fadeOut();
    systemMessages(translate({ plug: "general_i18n", text: "system_message_server_error_text" }), "error");
};

/**
 * It initializes the jQuery File Upload plugin and sets the options
 * @param {any} options
 */
const initFileUploader = async options => {
    const { uploaderSelector, uploaderOptions } = options;
    const uploaderInput = $(uploaderSelector);

    await initialize(uploaderSelector);

    // @ts-ignore
    uploaderInput.fileupload(uploaderOptions);
};

/**
 * It removes the image from the DOM and adds a hidden input to the form with the name of the image to
 * be removed
 * @param {JQuery} btn
 */
const fileuploadRemove = async btn => {
    const { file, name } = btn.data();
    const item = btn.closest(".js-fileupload-item");

    if (file !== name) {
        btn.closest(".js-fileupload-wrapper").append(`<input type="hidden" name="images_remove[]" value="${file}">`);
        item.remove();
        allowedFiles += 1;
    } else {
        item.remove();
        allowedFiles += 1;
    }
};

/**
 * It opens a confirm modal and call fileuploadRemove function if user confirm delete
 * @param {JQuery} btn
 */
const fileploadRemoveItemImage = async btn => {
    await loadBootstrapDialog();
    openResultModal({
        subTitle: btn.data("message"),
        type: "info",
        closable: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "ok" }),
                cssClass: "btn btn-success mnw-150",
                async action(dialogRef) {
                    await fileuploadRemove(btn);
                    dialogRef.close();
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn btn-light mnw-150",
                action(dialogRef) {
                    dialogRef.close();
                },
            },
        ],
    });
};

export default options => {
    const {
        filesAmount,
        filesAllowed,
        fileTypes,
        fileFormats,
        fileUploadMaxSize,
        fileUploadUrl,
        fileRemoveUrl,
        uploaderSelector,
        uploadBtnSelector,
        uploadLoaderSelector,
        imageWrapperSelector,
    } = options;

    maxNumberOfFiles = filesAmount;
    allowedFiles = filesAllowed;
    uploadBtn = $(uploadBtnSelector);
    loader = $(uploadLoaderSelector);
    removeUrl = fileRemoveUrl;
    imageWrapper = $(imageWrapperSelector);

    initFileUploader({
        uploaderSelector,
        uploaderOptions: {
            url: fileUploadUrl,
            dataType: "json",
            maxNumberOfFiles: filesAmount,
            maxFileSize: fileUploadMaxSize,
            acceptFileTypes: new RegExp(`(.|/)(${fileFormats})`, "i"),
            loadImageFileTypes: new RegExp(`(${fileTypes})`, "i"),
            beforeSend: onUploadStart,
            processalways: onUploadFinished,
            done: onUploadDone,
            sfail: onUploadFail,
        },
    });

    EventHub.on("fileupload:remove-item-image", (_e, btn) => fileploadRemoveItemImage(btn));
};

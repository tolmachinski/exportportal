import $ from "jquery";
import { hideLoader, showLoader } from "@src/util/common/loader";
import cropperInitialize from "@src/plugins/cropper/index";

const cropperDialog = (progressEvent, data) => {
    return new Promise(resolve => {
        const { modalBtnText, modalTitle, imgValidationWidth, imgValidationHeight, cropperImageHeight } = data;
        const btnFooter = `<button class="btn btn-primary mnw-150 pull-right call-action" data-js-action="crop-image:init" type="button">${modalBtnText}</button>`;
        const popup = $("#js-popup-croppper");
        const cropperTarget = $("#js-my-img-crop");
        let cropper = null;

        import("@src/plugins/bootstrap-dialog/index").then(({ openBootstrapDialog }) => {
            openBootstrapDialog({
                title: modalTitle,
                cssClass: "info-bootstrap-dialog inputs-40",
                onshow(dialog) {
                    showLoader(popup);
                    const modalDialog = dialog.getModalDialog();
                    modalDialog.addClass("modal-dialog-centered");
                    dialog.getModalBody().append(popup);
                    dialog.getModalFooter().html(btnFooter).show();
                },
                async onshown() {
                    cropper = await cropperInitialize(cropperTarget, progressEvent, imgValidationWidth, imgValidationHeight, cropperImageHeight);
                    hideLoader(popup);
                    cropperTarget.find(".js-cropper-rotate").on("click", e => {
                        e.preventDefault();
                        cropper.rotate(-90);
                    });
                    resolve(cropper);
                },
                onhide() {
                    cropper.destroy();
                },
                onhidden() {
                    $("#js-popup-croppper-wr").append(popup);
                },
            });
        });
    });
};

export default cropperDialog;

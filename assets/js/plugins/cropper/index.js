import $ from "jquery";

const boot = async () => {
    const { default: Croppie } = await import("croppie");
    await import("@scss/plug/croppier/croppier.scss");

    return Croppie;
};

const cropperInitialize = async (cropImage, img, winImgWidth, winImgHeight, cropperImageHeight) => {
    const Croppie = await boot();
    const wh = $(window).height();
    const cropWr = cropImage.get(0);
    let uploadImgCrop = new Croppie(cropWr, {
        boundary: {
            width: "100%",
            // eslint-disable-next-line no-nested-ternary
            height: wh > 539 ? cropperImageHeight : wh < 450 ? winImgHeight + 10 : winImgHeight + 30,
        },
        enableExif: true,
        enableOrientation: true,
    });

    const ratio = winImgWidth / winImgHeight;
    const sizes = {
        width: cropWr.clientWidth,
        height: cropWr.clientHeight,
    };
    const viewport = {
        width: winImgWidth,
        height: winImgHeight,
    };

    if (winImgWidth > sizes.width - 15 && winImgWidth >= winImgHeight) {
        viewport.width = sizes.width - 15;
        viewport.height = (sizes.width - 15) / ratio;
    }

    if (winImgHeight > sizes.height - 15 && winImgWidth < winImgHeight) {
        viewport.width = (sizes.height - 15) * ratio;
        viewport.height = sizes.height - 15;
    }

    uploadImgCrop.destroy();
    uploadImgCrop = new Croppie(cropWr, {
        boundary: {
            width: sizes.width,
            height: sizes.height,
        },
        viewport,
        enableExif: true,
        enableOrientation: true,
    });

    cropImage.find(".cr-slider-wrap").append('<a class="btn btn-dark js-cropper-rotate ml-15" href="#"><i class="ep-icon ep-icon_updates"></i></a>');
    cropImage.addClass("ready");

    await uploadImgCrop.bind({
        url: img.target.result,
        zoom: 0,
    });

    return uploadImgCrop;
};

export default cropperInitialize;

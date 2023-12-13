const templateFileUpload = ({ className = "", type, index, image, imageLink, iconClassName = null }) => {
    switch (type) {
        case "files":
            return `
                <div
                    id="fileupload-item-${index}"
                    class="fileupload2__item image-card3 js-fileupload-item ${className} icon"
                >
                    <div class="link js-fileupload-image icon-files-${iconClassName}-middle">
                        ${image}
                    </div>
                    <div class="js-fileupload-actions fileupload2__actions"></div>
                </div>`;
        case "img":
            return `
            <div
                id="fileupload-item-${index}"
                class="fileupload2__item image-card3 js-fileupload-item ${className}"
            >
                <a
                    class="link fancyboxGallery js-fileupload-image"
                    rel="fancybox-thumb"
                    href="${imageLink}"
                >
                    ${image}
                </a>
                <div class="js-fileupload-actions fileupload2__actions"></div>
            </div>`;
        case "imgnolink":
            return `
            <div
                id="fileupload-item-${index}"
                class="fileupload2__item js-fileupload-item image-card3 ${className}"
            >
                <span class="link js-fileupload-image">
                    ${image}
                </span>
                <div class="js-fileupload-actions fileupload2__actions inputs-40"></div>
            </div>`;
        default:
            return null;
    }
};

export default templateFileUpload;

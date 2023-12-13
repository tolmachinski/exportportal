module.exports = async page => {
    await page.waitForFunction(popups);
    await page.waitForTimeout(500);
    await page.waitForFunction(() => {
        return true;
    });
};

function popups () {
    return (async () => {
        let styleContent = "";
        const fancyboxWrapNode = ".fancybox-wrap"
        const fancyboxBackgroundNode = ".fancybox-overlay";
        const bootstrapModalNode = ".bootstrap-dialog";
        const modalBackgroundNode = ".modal-backdrop";
        const contentNode = ".modal-dialog";

        styleContent += `
            ${fancyboxWrapNode} {
                top: 30px!important;
                left: 50%!important;
                transform: translateX(-50%)!important;
            }
            ${fancyboxBackgroundNode} {
                height: ${document.body.scrollHeight}px!important;
            }
            ${contentNode} {
                justify-content: flex-start!important;
                align-items: flex-start!important;
            }
            ${bootstrapModalNode} {
                height: ${document.body.scrollHeight}px!important;
            }
            ${modalBackgroundNode} {
                height: ${document.body.scrollHeight}px!important;
            }
            @media(max-width: 767px) {
                ${fancyboxWrapNode} {
                    top: 5px!important;
                }
            }
        `;

        const style = document.createElement("style");
        style.textContent = styleContent;
        document.body.appendChild(style);

        return true;
    })()
}

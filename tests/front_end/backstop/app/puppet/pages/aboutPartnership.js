module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    await page.waitForFunction(replaceDataForPartners, {}, {
        img: variables.img(170, 170),
    });
}

function replaceDataForPartners(data) {
    return (async () => {
        document.querySelectorAll(`[atas="about-partnership__partner-image"]`).forEach(img => {
            img.dataset.src = data.img;
            img.src = data.img;
        });

        return true;
    })();
};
